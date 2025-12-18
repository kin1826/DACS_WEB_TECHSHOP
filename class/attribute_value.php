<?php
require_once 'db.php';

class AttributeValue extends DB {
  protected $table = 'attribute_values';

  public function __construct() {
    parent::__construct();
  }

  /**
   * Tạo mới attribute value
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
   * Cập nhật attribute value
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
   * Xóa attribute value
   */
  public function delete($id) {
    $id = $this->db_escape($id);
    $query = "DELETE FROM {$this->table} WHERE id = '$id'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy value theo ID
   */
  public function findById($id) {
    $id = $this->db_escape($id);
    $query = "SELECT * FROM {$this->table} WHERE id = '$id'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy giá trị theo attribute_id
   */
  public function getByAttribute($attributeId) {
    $attributeId = $this->db_escape($attributeId);
    $query = "SELECT * FROM {$this->table} WHERE attribute_id = '$attributeId' ORDER BY sort_order, value ASC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy tất cả giá trị với thông tin attribute
   */
  public function getAllWithAttributes() {
    $query = "SELECT av.*, a.name as attribute_name, a.type as attribute_type
              FROM {$this->table} av
              LEFT JOIN product_attributes a ON av.attribute_id = a.id
              ORDER BY a.sort_order, av.sort_order";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy giá trị theo attribute slug
   */
  public function getByAttributeSlug($slug) {
    $slug = $this->db_escape($slug);
    $query = "SELECT av.*
              FROM {$this->table} av
              LEFT JOIN product_attributes a ON av.attribute_id = a.id
              WHERE a.slug = '$slug'
              ORDER BY av.sort_order, av.value ASC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Xóa tất cả giá trị của attribute
   */
  public function deleteByAttribute($attributeId) {
    $attributeId = $this->db_escape($attributeId);
    $query = "DELETE FROM {$this->table} WHERE attribute_id = '$attributeId'";
    return $this->db_query($query) !== false;
  }

  // ========== CÁC PHƯƠNG THỨC MỚI BỔ SUNG ==========

  /**
   * Lấy giá trị được sử dụng nhiều nhất
   */
  public function getMostUsedValues($limit = 10) {
    $limit = (int)$limit;
    $query = "SELECT av.*, a.name as attribute_name, COUNT(va.id) as usage_count
              FROM {$this->table} av
              LEFT JOIN variant_attributes va ON av.id = va.value_id
              LEFT JOIN product_attributes a ON av.attribute_id = a.id
              GROUP BY av.id
              ORDER BY usage_count DESC
              LIMIT $limit";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Kiểm tra giá trị đã tồn tại trong attribute chưa
   */
  public function valueExists($attributeId, $value) {
    $attributeId = $this->db_escape($attributeId);
    $value = $this->db_escape($value);

    $query = "SELECT id FROM {$this->table}
              WHERE attribute_id = '$attributeId' AND value = '$value'";
    $result = $this->db_query($query);
    return $this->db_fetch($result) !== false;
  }

  /**
   * Lấy giá trị theo color code
   */
  public function getByColorCode($colorCode) {
    $colorCode = $this->db_escape($colorCode);
    $query = "SELECT av.*, a.name as attribute_name
              FROM {$this->table} av
              LEFT JOIN product_attributes a ON av.attribute_id = a.id
              WHERE av.color_code = '$colorCode' AND a.type = 'color'
              ORDER BY av.sort_order";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tìm kiếm giá trị
   */
  public function search($keyword, $attributeId = null) {
    $keyword = $this->db_escape($keyword);
    $where = "WHERE (av.value LIKE '%$keyword%' OR av.color_code LIKE '%$keyword%')";

    if ($attributeId) {
      $attributeId = $this->db_escape($attributeId);
      $where .= " AND av.attribute_id = '$attributeId'";
    }

    $query = "SELECT av.*, a.name as attribute_name, a.type as attribute_type
              FROM {$this->table} av
              LEFT JOIN product_attributes a ON av.attribute_id = a.id
              $where
              ORDER BY a.sort_order, av.sort_order";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy giá trị với phân trang
   */
  public function getWithPagination($attributeId = null, $page = 1, $perPage = 20) {
    $offset = ($page - 1) * $perPage;
    $where = $attributeId ? "WHERE attribute_id = '" . $this->db_escape($attributeId) . "'" : "";

    $query = "SELECT av.*, a.name as attribute_name, a.type as attribute_type
              FROM {$this->table} av
              LEFT JOIN product_attributes a ON av.attribute_id = a.id
              $where
              ORDER BY a.sort_order, av.sort_order
              LIMIT $offset, $perPage";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }


  // Lấy giá trị theo attribute_id
  public function getByAttributeId($attribute_id) {
    $query = "SELECT * FROM attribute_values
                  WHERE attribute_id = " . (int)$attribute_id . "
                  ORDER BY sort_order ASC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  // Lấy giá trị theo ID
  public function getById($id) {
    return $this->db_select('attribute_values', "id = " . (int)$id);
  }

  /**
   * Cập nhật thứ tự giá trị
   */
  public function updateSortOrder($ids) {
    $success = true;

    foreach ($ids as $index => $id) {
      if (!$this->update($id, ['sort_order' => $index])) {
        $success = false;
      }
    }

    return $success;
  }

  /**
   * Import giá trị từ mảng
   */
  public function importFromArray($attributeId, $values) {
    $success = true;
    $imported = 0;

    foreach ($values as $valueData) {
      if (!empty($valueData['value'])) {
        $data = [
          'attribute_id' => $attributeId,
          'value' => $valueData['value'],
          'color_code' => isset($valueData['color_code']) ? $valueData['color_code'] : null,
          'sort_order' => isset($valueData['sort_order']) ? $valueData['sort_order'] : 0
        ];

        if ($this->create($data)) {
          $imported++;
        } else {
          $success = false;
        }
      }
    }

    return ['success' => $success, 'imported' => $imported];
  }

  /**
   * Export giá trị ra mảng
   */
  public function exportToArray($attributeId = null) {
    $where = $attributeId ? "WHERE attribute_id = " . (int)$attributeId : "";
    $query = "SELECT * FROM {$this->table} $where ORDER BY sort_order, value";
    $result = $this->db_query($query);
    $values = $this->db_fetch_all($result);

    $result = [];
    foreach ($values as $value) {
      $result[] = [
        'value' => $value['value'],
        'color_code' => $value['color_code'],
        'sort_order' => $value['sort_order']
      ];
    }

    return $result;
  }

  /**
   * Lấy giá trị cho form select
   */
  public function getForSelect($attributeId) {
    $values = $this->getByAttribute($attributeId);
    $result = [];

    foreach ($values as $value) {
      $result[$value['id']] = $value['value'];
    }

    return $result;
  }

  /**
   * Lấy giá trị với color code cho form select
   */
  public function getForSelectWithColors($attributeId) {
    $values = $this->getByAttribute($attributeId);
    $result = [];

    foreach ($values as $value) {
      $result[$value['id']] = [
        'text' => $value['value'],
        'color' => $value['color_code']
      ];
    }

    return $result;
  }

  /**
   * Validate value data
   */
  public function validate($data) {
    $errors = [];

    if (empty($data['value'])) {
      $errors[] = 'Giá trị không được để trống';
    }

    if (empty($data['attribute_id'])) {
      $errors[] = 'Attribute ID không được để trống';
    }

    if (strlen($data['value']) > 100) {
      $errors[] = 'Giá trị không được vượt quá 100 ký tự';
    }

    if (!empty($data['color_code']) && !preg_match('/^#[0-9A-F]{6}$/i', $data['color_code'])) {
      $errors[] = 'Mã màu không hợp lệ (phải là mã hex: #FFFFFF)';
    }

    return $errors;
  }

  /**
   * Tạo giá trị mẫu
   */
  public function createSampleValues($attributeId, $attributeType) {
    $sampleValues = [];

    switch ($attributeType) {
      case 'color':
        $sampleValues = [
          ['value' => 'Đỏ', 'color_code' => '#FF0000'],
          ['value' => 'Xanh lá', 'color_code' => '#00FF00'],
          ['value' => 'Xanh dương', 'color_code' => '#0000FF'],
          ['value' => 'Đen', 'color_code' => '#000000'],
          ['value' => 'Trắng', 'color_code' => '#FFFFFF']
        ];
        break;

      case 'select':
        $sampleValues = [
          ['value' => 'Nhỏ'],
          ['value' => 'Vừa'],
          ['value' => 'Lớn'],
          ['value' => 'Rất lớn']
        ];
        break;

      case 'text':
        $sampleValues = [
          ['value' => 'Cotton'],
          ['value' => 'Polyester'],
          ['value' => 'Silk'],
          ['value' => 'Wool']
        ];
        break;
    }

    return $this->importFromArray($attributeId, $sampleValues);
  }

  /**
   * Lấy thống kê giá trị
   */
  public function getStats($attributeId = null) {
    $where = $attributeId ? "WHERE attribute_id = " . (int)$attributeId : "";

    $query = "SELECT
                COUNT(*) as total_values,
                SUM(CASE WHEN color_code IS NOT NULL THEN 1 ELSE 0 END) as values_with_color,
                AVG(LENGTH(value)) as avg_value_length
              FROM {$this->table}
              $where";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Kiểm tra giá trị có đang được sử dụng không
   */
  public function isUsed($id) {
    $id = $this->db_escape($id);

    $query = "SELECT COUNT(*) as count FROM variant_attributes WHERE value_id = '$id'";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);

    return $row['count'] > 0;
  }

  /**
   * Lấy giá trị theo variant
   */
  public function getByVariant($variantId) {
    $variantId = $this->db_escape($variantId);

    $query = "SELECT av.*, a.name as attribute_name, a.type as attribute_type
              FROM {$this->table} av
              LEFT JOIN variant_attributes va ON av.id = va.value_id
              LEFT JOIN product_attributes a ON av.attribute_id = a.id
              WHERE va.variant_id = '$variantId'
              ORDER BY a.sort_order, av.sort_order";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy giá trị theo product
   */
  public function getByProduct($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT DISTINCT av.*, a.name as attribute_name, a.type as attribute_type
              FROM {$this->table} av
              LEFT JOIN variant_attributes va ON av.id = va.value_id
              LEFT JOIN product_variants pv ON va.variant_id = pv.id
              LEFT JOIN product_attributes a ON av.attribute_id = a.id
              WHERE pv.product_id = '$productId'
              ORDER BY a.sort_order, av.sort_order";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy tất cả color values
   */
  public function getAllColorValues() {
    $query = "SELECT av.*, a.name as attribute_name
              FROM {$this->table} av
              LEFT JOIN product_attributes a ON av.attribute_id = a.id
              WHERE a.type = 'color' AND av.color_code IS NOT NULL
              ORDER BY a.sort_order, av.sort_order";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tìm giá trị theo color code gần đúng
   */
  public function findSimilarColors($colorCode, $threshold = 20) {
    if (!preg_match('/^#[0-9A-F]{6}$/i', $colorCode)) {
      return [];
    }

    // Chuyển hex sang RGB
    $r1 = hexdec(substr($colorCode, 1, 2));
    $g1 = hexdec(substr($colorCode, 3, 2));
    $b1 = hexdec(substr($colorCode, 5, 2));

    $allColors = $this->getAllColorValues();
    $similar = [];

    foreach ($allColors as $color) {
      if (!empty($color['color_code']) && preg_match('/^#[0-9A-F]{6}$/i', $color['color_code'])) {
        $r2 = hexdec(substr($color['color_code'], 1, 2));
        $g2 = hexdec(substr($color['color_code'], 3, 2));
        $b2 = hexdec(substr($color['color_code'], 5, 2));

        // Tính khoảng cách màu
        $distance = sqrt(pow($r1 - $r2, 2) + pow($g1 - $g2, 2) + pow($b1 - $b2, 2));

        if ($distance <= $threshold) {
          $similar[] = $color;
        }
      }
    }

    return $similar;
  }

  /**
   * Merge các giá trị trùng lặp
   */
  public function mergeDuplicates($attributeId) {
    $attributeId = $this->db_escape($attributeId);

    // Tìm các giá trị trùng
    $query = "SELECT value, GROUP_CONCAT(id) as ids, COUNT(*) as count
              FROM {$this->table}
              WHERE attribute_id = '$attributeId'
              GROUP BY value
              HAVING count > 1";
    $result = $this->db_query($query);
    $duplicates = $this->db_fetch_all($result);

    $mergedCount = 0;

    foreach ($duplicates as $dup) {
      $ids = explode(',', $dup['ids']);

      // Giữ lại bản ghi đầu tiên, xóa các bản ghi còn lại
      $firstId = array_shift($ids);

      // Xóa các bản ghi trùng
      foreach ($ids as $id) {
        $this->delete($id);
        $mergedCount++;
      }
    }

    return $mergedCount;
  }

  /**
   * Tự động sắp xếp giá trị theo alphabet
   */
  public function autoSort($attributeId) {
    $values = $this->getByAttribute($attributeId);
    $success = true;

    // Sắp xếp theo giá trị
    usort($values, function($a, $b) {
      return strcmp($a['value'], $b['value']);
    });

    // Cập nhật sort_order
    foreach ($values as $index => $value) {
      if (!$this->update($value['id'], ['sort_order' => $index])) {
        $success = false;
      }
    }

    return $success;
  }
}
?>
