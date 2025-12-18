<?php
require_once 'db.php';

class ProductSpecification extends DB {
  protected $table = 'product_specifications';

  public function __construct() {
    parent::__construct();
  }

  /**
   * Tạo mới specification
   */
  public function create($data) {
    $fields = [];
    $values = [];

    foreach ($data as $field => $value) {
      $fields[] = "`" . $this->db_escape($field) . "`";
      if ($value === null) {
        $values[] = "NULL";
      } else {
        $values[] = "'" . $this->db_escape($value) . "'";
      }
    }

    $fields_str = implode(", ", $fields);
    $values_str = implode(", ", $values);

    $query = "INSERT INTO {$this->table} ($fields_str) VALUES ($values_str)";
    return $this->db_query($query) !== false;
  }

  /**
   * Cập nhật specification
   */
  public function update($id, $data) {
    if (!is_array($data) || empty($data)) return false;

    $set_parts = [];
    foreach ($data as $field => $value) {
      if ($value === null) {
        $set_parts[] = "`" . $this->db_escape($field) . "` = NULL";
      } else {
        $set_parts[] = "`" . $this->db_escape($field) . "` = '" . $this->db_escape($value) . "'";
      }
    }

    $set_str = implode(", ", $set_parts);
    $id = $this->db_escape($id);
    $query = "UPDATE {$this->table} SET $set_str WHERE id = '$id'";

    return $this->db_query($query) !== false;
  }

  /**
   * Xóa specification
   */
  public function delete($id) {
    $id = $this->db_escape($id);
    $query = "DELETE FROM {$this->table} WHERE id = '$id'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy specification theo ID
   */
  public function findById($id) {
    $id = $this->db_escape($id);
    $query = "SELECT * FROM {$this->table} WHERE id = '$id'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy specifications theo product_id
   */
  public function getByProductId($productId) {
    $productId = $this->db_escape($productId);
    $query = "SELECT * FROM {$this->table} WHERE product_id = '$productId' ORDER BY sort_order, spec_name ASC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Thêm hoặc cập nhật specification
   */
  public function saveSpecification($productId, $specName, $specValue, $sortOrder = 0) {
    $productId = $this->db_escape($productId);
    $specName = $this->db_escape($specName);
    $specValue = $this->db_escape($specValue);
    $sortOrder = (int)$sortOrder;

    // Kiểm tra đã tồn tại chưa
    $query = "SELECT id FROM {$this->table} WHERE product_id = '$productId' AND spec_name = '$specName'";
    $result = $this->db_query($query);
    $existing = $this->db_fetch($result);

    if ($existing) {
      // Cập nhật
      $query = "UPDATE {$this->table} SET spec_value = '$specValue', sort_order = '$sortOrder' WHERE id = '{$existing['id']}'";
    } else {
      // Thêm mới
      $query = "INSERT INTO {$this->table} (product_id, spec_name, spec_value, sort_order)
                VALUES ('$productId', '$specName', '$specValue', '$sortOrder')";
    }

    return $this->db_query($query) !== false;
  }

  /**
   * Xóa tất cả specifications của sản phẩm
   */
  public function deleteByProduct($productId) {
    $productId = $this->db_escape($productId);
    $query = "DELETE FROM {$this->table} WHERE product_id = '$productId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy số lượng specifications của sản phẩm
   */
  public function countByProduct($productId) {
    $productId = $this->db_escape($productId);
    $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE product_id = '$productId'";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return isset($row['total']) ? $row['total'] : 0;
  }

  // ========== CÁC PHƯƠNG THỨC MỚI BỔ SUNG ==========

  /**
   * Import specifications từ mảng
   */
  public function importFromArray($productId, $specifications) {
    $success = true;

    foreach ($specifications as $spec) {
      if (!empty($spec['name']) && !empty($spec['value'])) {
        $result = $this->saveSpecification(
          $productId,
          $spec['name'],
          $spec['value'],
          isset($spec['sort_order']) ? $spec['sort_order'] : 0
        );

        if (!$result) {
          $success = false;
        }
      }
    }

    return $success;
  }

  /**
   * Export specifications ra mảng
   */
  public function exportToArray($productId) {
    $specs = $this->getByProductId($productId);
    $result = [];

    foreach ($specs as $spec) {
      $result[] = [
        'name' => $spec['spec_name'],
        'value' => $spec['spec_value'],
        'sort_order' => $spec['sort_order']
      ];
    }

    return $result;
  }

  /**
   * Sao chép specifications từ sản phẩm này sang sản phẩm khác
   */
  public function copySpecifications($fromProductId, $toProductId) {
    $specs = $this->getByProductId($fromProductId);
    $success = true;

    foreach ($specs as $spec) {
      $result = $this->saveSpecification(
        $toProductId,
        $spec['spec_name'],
        $spec['spec_value'],
        $spec['sort_order']
      );

      if (!$result) {
        $success = false;
      }
    }

    return $success;
  }

  /**
   * Lấy specifications theo nhóm (group by spec_name)
   */
  public function getGroupedByProductId($productId) {
    $productId = $this->db_escape($productId);
    $query = "SELECT spec_name, GROUP_CONCAT(spec_value SEPARATOR '|') as values_list
              FROM {$this->table}
              WHERE product_id = '$productId'
              GROUP BY spec_name
              ORDER BY sort_order, spec_name";
    $result = $this->db_query($query);
    $grouped = $this->db_fetch_all($result);

    // Format kết quả
    foreach ($grouped as &$group) {
      $group['values'] = explode('|', $group['values_list']);
      unset($group['values_list']);
    }

    return $grouped;
  }

  /**
   * Tìm kiếm specifications theo tên hoặc giá trị
   */
  public function search($keyword, $productId = null) {
    $keyword = $this->db_escape($keyword);
    $where = "WHERE (spec_name LIKE '%$keyword%' OR spec_value LIKE '%$keyword%')";

    if ($productId) {
      $productId = $this->db_escape($productId);
      $where .= " AND product_id = '$productId'";
    }

    $query = "SELECT ps.*, p.name_pr as product_name
              FROM {$this->table} ps
              LEFT JOIN products p ON ps.product_id = p.id
              $where
              ORDER BY ps.product_id, ps.sort_order";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy tất cả tên specifications duy nhất
   */
  public function getUniqueSpecNames() {
    $query = "SELECT DISTINCT spec_name
              FROM {$this->table}
              ORDER BY spec_name";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Kiểm tra specification đã tồn tại chưa
   */
  public function exists($productId, $specName) {
    $productId = $this->db_escape($productId);
    $specName = $this->db_escape($specName);

    $query = "SELECT id FROM {$this->table}
              WHERE product_id = '$productId' AND spec_name = '$specName'";
    $result = $this->db_query($query);
    return $this->db_fetch($result) !== false;
  }

  /**
   * Cập nhật nhiều specifications cùng lúc
   */
  public function batchUpdate($specifications) {
    $success = true;

    foreach ($specifications as $spec) {
      if (isset($spec['id']) && isset($spec['spec_name']) && isset($spec['spec_value'])) {
        $data = [
          'spec_name' => $spec['spec_name'],
          'spec_value' => $spec['spec_value'],
          'sort_order' => isset($spec['sort_order']) ? $spec['sort_order'] : 0
        ];

        if (!$this->update($spec['id'], $data)) {
          $success = false;
        }
      }
    }

    return $success;
  }

  /**
   * Lấy specifications với phân trang
   */
  public function getWithPagination($productId, $page = 1, $perPage = 20) {
    $productId = $this->db_escape($productId);
    $offset = ($page - 1) * $perPage;

    $query = "SELECT * FROM {$this->table}
              WHERE product_id = '$productId'
              ORDER BY sort_order, spec_name
              LIMIT $offset, $perPage";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy thống kê specifications
   */
  public function getStats($productId = null) {
    $where = $productId ? "WHERE product_id = '" . $this->db_escape($productId) . "'" : "";

    $query = "SELECT
                COUNT(*) as total_specs,
                COUNT(DISTINCT spec_name) as unique_names,
                AVG(LENGTH(spec_value)) as avg_value_length
              FROM {$this->table}
              $where";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Tự động sắp xếp specifications theo alphabet
   */
  public function autoSort($productId) {
    $specs = $this->getByProductId($productId);
    $success = true;

    // Sắp xếp theo tên
    usort($specs, function($a, $b) {
      return strcmp($a['spec_name'], $b['spec_name']);
    });

    // Cập nhật sort_order
    foreach ($specs as $index => $spec) {
      if (!$this->update($spec['id'], ['sort_order' => $index])) {
        $success = false;
      }
    }

    return $success;
  }

  /**
   * Merge các specifications trùng lặp
   */
  public function mergeDuplicates($productId) {
    $productId = $this->db_escape($productId);

    // Tìm các specifications trùng tên
    $query = "SELECT spec_name, GROUP_CONCAT(id) as ids, GROUP_CONCAT(spec_value) as values
              FROM {$this->table}
              WHERE product_id = '$productId'
              GROUP BY spec_name
              HAVING COUNT(*) > 1";
    $result = $this->db_query($query);
    $duplicates = $this->db_fetch_all($result);

    $mergedCount = 0;

    foreach ($duplicates as $dup) {
      $ids = explode(',', $dup['ids']);
      $values = explode(',', $dup['values']);

      // Giữ lại bản ghi đầu tiên, xóa các bản ghi còn lại
      $firstId = array_shift($ids);
      $firstValue = array_shift($values);

      // Cập nhật giá trị nếu có nhiều giá trị khác nhau
      if (count(array_unique($values)) > 1) {
        $mergedValue = $firstValue . '; ' . implode('; ', $values);
        $this->update($firstId, ['spec_value' => $mergedValue]);
      }

      // Xóa các bản ghi trùng
      foreach ($ids as $id) {
        $this->delete($id);
        $mergedCount++;
      }
    }

    return $mergedCount;
  }

  /**
   * Validate specification data
   */
  public function validate($data) {
    $errors = [];

    if (empty($data['spec_name'])) {
      $errors[] = 'Tên thông số không được để trống';
    }

    if (empty($data['spec_value'])) {
      $errors[] = 'Giá trị thông số không được để trống';
    }

    if (strlen($data['spec_name']) > 100) {
      $errors[] = 'Tên thông số không được vượt quá 100 ký tự';
    }

    if (strlen($data['spec_value']) > 500) {
      $errors[] = 'Giá trị thông số không được vượt quá 500 ký tự';
    }

    return $errors;
  }

  /**
   * Lấy specifications dạng key-value cho dễ sử dụng
   */
  public function getKeyValuePairs($productId) {
    $specs = $this->getByProductId($productId);
    $pairs = [];

    foreach ($specs as $spec) {
      $pairs[$spec['spec_name']] = $spec['spec_value'];
    }

    return $pairs;
  }

  /**
   * Tạo HTML cho specifications
   */
  public function generateHTML($productId, $template = 'table') {
    $specs = $this->getByProductId($productId);

    if (empty($specs)) {
      return '';
    }

    $html = '';

    switch ($template) {
      case 'table':
        $html = '<table class="specifications-table">';
        foreach ($specs as $spec) {
          $html .= '<tr>';
          $html .= '<td><strong>' . htmlspecialchars($spec['spec_name']) . '</strong></td>';
          $html .= '<td>' . htmlspecialchars($spec['spec_value']) . '</td>';
          $html .= '</tr>';
        }
        $html .= '</table>';
        break;

      case 'list':
        $html = '<ul class="specifications-list">';
        foreach ($specs as $spec) {
          $html .= '<li>';
          $html .= '<strong>' . htmlspecialchars($spec['spec_name']) . ':</strong> ';
          $html .= htmlspecialchars($spec['spec_value']);
          $html .= '</li>';
        }
        $html .= '</ul>';
        break;

      case 'div':
        foreach ($specs as $spec) {
          $html .= '<div class="spec-item">';
          $html .= '<span class="spec-name">' . htmlspecialchars($spec['spec_name']) . '</span>';
          $html .= '<span class="spec-value">' . htmlspecialchars($spec['spec_value']) . '</span>';
          $html .= '</div>';
        }
        break;
    }

    return $html;
  }
}
?>
