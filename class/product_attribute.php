<?php
require_once 'db.php';

class ProductAttribute extends DB {
  protected $table = 'product_attributes';

  public function __construct() {
    parent::__construct();
  }

  /**
   * Tạo slug từ tên attribute
   */
  public function generateSlug($name) {
    $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($name));
    $slug = trim($slug, '-');

    // Kiểm tra slug trùng
    $counter = 1;
    $baseSlug = $slug;
    while ($this->slugExists($slug)) {
      $slug = $baseSlug . '-' . $counter;
      $counter++;
    }

    return $slug;
  }

  /**
   * Tạo mới attribute
   */
  public function create($data) {
    if (empty($data['slug'])) {
      $data['slug'] = $this->generateSlug($data['name']);
    }

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
   * Cập nhật attribute
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
   * Xóa attribute
   */
  public function delete($id) {
    $id = $this->db_escape($id);
    $query = "DELETE FROM {$this->table} WHERE id = '$id'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy tất cả attributes
   */
  public function getAll($onlyVisible = true) {
    $where = $onlyVisible ? "WHERE is_visible = 1" : "";
    $query = "SELECT * FROM {$this->table} $where ORDER BY sort_order, name ASC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy attribute theo ID
   */
  public function findById($id) {
    $id = $this->db_escape($id);
    $query = "SELECT * FROM {$this->table} WHERE id = '$id'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy attribute theo slug
   */
  public function findBySlug($slug) {
    $slug = $this->db_escape($slug);
    $query = "SELECT * FROM {$this->table} WHERE slug = '$slug'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy attributes theo type
   */
  public function getByType($type) {
    $type = $this->db_escape($type);
    $query = "SELECT * FROM {$this->table} WHERE type = '$type' AND is_visible = 1 ORDER BY sort_order, name ASC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Kiểm tra slug đã tồn tại chưa
   */
  public function slugExists($slug) {
    $slug = $this->db_escape($slug);
    $query = "SELECT id FROM {$this->table} WHERE slug = '$slug'";
    $result = $this->db_query($query);
    return $this->db_fetch($result) !== false;
  }

  // ========== CÁC PHƯƠNG THỨC MỚI BỔ SUNG ==========

  /**
   * Lấy attributes với số lượng giá trị
   */
  public function getAllWithValueCount() {
    $query = "SELECT pa.*, COUNT(av.id) as value_count
              FROM {$this->table} pa
              LEFT JOIN attribute_values av ON pa.id = av.attribute_id
              WHERE pa.is_visible = 1
              GROUP BY pa.id
              ORDER BY pa.sort_order, pa.name ASC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy attributes được sử dụng nhiều nhất
   */
  public function getMostUsedAttributes($limit = 10) {
    $limit = (int)$limit;
    $query = "SELECT pa.*, COUNT(va.id) as usage_count
              FROM {$this->table} pa
              LEFT JOIN variant_attributes va ON pa.id = va.attribute_id
              WHERE pa.is_visible = 1
              GROUP BY pa.id
              ORDER BY usage_count DESC
              LIMIT $limit";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Toggle trạng thái hiển thị
   */
  public function toggleVisibility($id) {
    $attribute = $this->findById($id);
    if (!$attribute) return false;

    $newVisibility = $attribute['is_visible'] ? 0 : 1;
    return $this->update($id, ['is_visible' => $newVisibility]);
  }

  /**
   * Lấy attributes cho filter
   */
  public function getAttributesForFilter() {
    $query = "SELECT pa.*,
                     GROUP_CONCAT(av.value ORDER BY av.sort_order SEPARATOR '|') as values_list,
                     GROUP_CONCAT(av.color_code ORDER BY av.sort_order SEPARATOR '|') as color_codes
              FROM {$this->table} pa
              LEFT JOIN attribute_values av ON pa.id = av.attribute_id
              WHERE pa.is_visible = 1
              GROUP BY pa.id
              ORDER BY pa.sort_order, pa.name";
    $result = $this->db_query($query);
    $attributes = $this->db_fetch_all($result);

    // Format kết quả
    foreach ($attributes as &$attribute) {
      if ($attribute['values_list']) {
        $values = explode('|', $attribute['values_list']);
        $colorCodes = $attribute['color_codes'] ? explode('|', $attribute['color_codes']) : [];

        $attribute['values'] = [];
        foreach ($values as $index => $value) {
          $attribute['values'][] = [
            'value' => $value,
            'color_code' => isset($colorCodes[$index]) ? $colorCodes[$index] : null
          ];
        }
      } else {
        $attribute['values'] = [];
      }
      unset($attribute['values_list'], $attribute['color_codes']);
    }

    return $attributes;
  }

  /**
   * Tìm kiếm attributes
   */
  public function search($keyword, $onlyVisible = true) {
    $keyword = $this->db_escape($keyword);
    $where = $onlyVisible ? "AND is_visible = 1" : "";

    $query = "SELECT * FROM {$this->table}
              WHERE (name LIKE '%$keyword%' OR slug LIKE '%$keyword%')
              $where
              ORDER BY sort_order, name";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy thống kê attributes
   */
  public function getStats() {
    $query = "SELECT
                COUNT(*) as total_attributes,
                SUM(CASE WHEN is_visible = 1 THEN 1 ELSE 0 END) as visible_attributes,
                SUM(CASE WHEN type = 'color' THEN 1 ELSE 0 END) as color_attributes,
                SUM(CASE WHEN type = 'text' THEN 1 ELSE 0 END) as text_attributes,
                SUM(CASE WHEN type = 'select' THEN 1 ELSE 0 END) as select_attributes
              FROM {$this->table}";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Kiểm tra attribute có đang được sử dụng không
   */
  public function isUsed($id) {
    $id = $this->db_escape($id);

    // Kiểm tra trong variant_attributes
    $query = "SELECT COUNT(*) as count FROM variant_attributes WHERE attribute_id = '$id'";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);

    return $row['count'] > 0;
  }

  /**
   * Lấy attributes với phân trang
   */
  public function getWithPagination($page = 1, $perPage = 20, $onlyVisible = true) {
    $offset = ($page - 1) * $perPage;
    $where = $onlyVisible ? "WHERE is_visible = 1" : "";

    $query = "SELECT * FROM {$this->table}
              $where
              ORDER BY sort_order, name
              LIMIT $offset, $perPage";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy tổng số trang
   */
  public function getTotalPages($perPage = 20, $onlyVisible = true) {
    $where = $onlyVisible ? "WHERE is_visible = 1" : "";
    $query = "SELECT COUNT(*) as total FROM {$this->table} $where";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return ceil($row['total'] / $perPage);
  }

  /**
   * Cập nhật thứ tự attributes
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
   * Validate attribute data
   */
  public function validate($data) {
    $errors = [];

    if (empty($data['name'])) {
      $errors[] = 'Tên thuộc tính không được để trống';
    }

    if (empty($data['type'])) {
      $errors[] = 'Loại thuộc tính không được để trống';
    }

    if (!in_array($data['type'], ['color', 'text', 'select'])) {
      $errors[] = 'Loại thuộc tính không hợp lệ';
    }

    if (strlen($data['name']) > 100) {
      $errors[] = 'Tên thuộc tính không được vượt quá 100 ký tự';
    }

    return $errors;
  }

  /**
   * Import attributes từ mảng
   */
  public function importFromArray($attributes) {
    $success = true;
    $imported = 0;

    foreach ($attributes as $attr) {
      if (!empty($attr['name']) && !empty($attr['type'])) {
        $data = [
          'name' => $attr['name'],
          'type' => $attr['type'],
          'is_visible' => isset($attr['is_visible']) ? $attr['is_visible'] : 1,
          'sort_order' => isset($attr['sort_order']) ? $attr['sort_order'] : 0
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
   * Export attributes ra mảng
   */
  public function exportToArray() {
    $attributes = $this->getAll(false);
    $result = [];

    foreach ($attributes as $attr) {
      $result[] = [
        'name' => $attr['name'],
        'slug' => $attr['slug'],
        'type' => $attr['type'],
        'is_visible' => $attr['is_visible'],
        'sort_order' => $attr['sort_order']
      ];
    }

    return $result;
  }

  /**
   * Lấy attributes cho form select
   */
  public function getForSelect($onlyVisible = true) {
    $attributes = $this->getAll($onlyVisible);
    $result = [];

    foreach ($attributes as $attr) {
      $result[$attr['id']] = $attr['name'];
    }

    return $result;
  }

  /**
   * Tạo attribute mẫu
   */
  public function createSampleAttributes() {
    $sampleAttributes = [
      [
        'name' => 'Màu sắc',
        'type' => 'color',
        'is_visible' => 1,
        'sort_order' => 1
      ],
      [
        'name' => 'Kích thước',
        'type' => 'select',
        'is_visible' => 1,
        'sort_order' => 2
      ],
      [
        'name' => 'Dung lượng',
        'type' => 'select',
        'is_visible' => 1,
        'sort_order' => 3
      ],
      [
        'name' => 'Chất liệu',
        'type' => 'text',
        'is_visible' => 1,
        'sort_order' => 4
      ]
    ];

    return $this->importFromArray($sampleAttributes);
  }

  /**
   * Lấy attributes kèm theo values
   */
  public function getWithValues($onlyVisible = true) {
    $where = $onlyVisible ? "WHERE pa.is_visible = 1" : "";

    $query = "SELECT pa.*,
                     av.id as value_id,
                     av.value as attribute_value,
                     av.color_code,
                     av.sort_order as value_sort_order
              FROM {$this->table} pa
              LEFT JOIN attribute_values av ON pa.id = av.attribute_id
              $where
              ORDER BY pa.sort_order, pa.name, av.sort_order, av.value";
    $result = $this->db_query($query);
    $rows = $this->db_fetch_all($result);

    // Group by attribute
    $attributes = [];
    foreach ($rows as $row) {
      $attributeId = $row['id'];

      if (!isset($attributes[$attributeId])) {
        $attributes[$attributeId] = [
          'id' => $row['id'],
          'name' => $row['name'],
          'slug' => $row['slug'],
          'type' => $row['type'],
          'is_visible' => $row['is_visible'],
          'sort_order' => $row['sort_order'],
          'values' => []
        ];
      }

      if ($row['value_id']) {
        $attributes[$attributeId]['values'][] = [
          'id' => $row['value_id'],
          'value' => $row['attribute_value'],
          'color_code' => $row['color_code'],
          'sort_order' => $row['value_sort_order']
        ];
      }
    }

    return array_values($attributes);
  }

  /**
   * Kiểm tra và sửa lỗi attributes
   */
  public function fixAttributes() {
    $fixed = 0;

    // Sửa các attributes không có slug
    $query = "SELECT id, name FROM {$this->table} WHERE slug = '' OR slug IS NULL";
    $result = $this->db_query($query);
    $attributes = $this->db_fetch_all($result);

    foreach ($attributes as $attr) {
      $slug = $this->generateSlug($attr['name']);
      if ($this->update($attr['id'], ['slug' => $slug])) {
        $fixed++;
      }
    }

    return $fixed;
  }

  public function getAttributesByProductId($product_id) {
    $sql = "SELECT DISTINCT pa.*
            FROM product_attributes pa
            INNER JOIN variant_attributes va ON pa.id = va.attribute_id
            INNER JOIN product_variants pv ON va.variant_id = pv.id
            WHERE pv.product_id = ? AND pa.is_visible = 1
            ORDER BY pa.sort_order";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getProductAttributesWithValues($product_id) {
    // Lấy tất cả thuộc tính của sản phẩm
    $attributes = $this->getAttributesByProductId($product_id);

    foreach ($attributes as &$attribute) {
      // Lấy tất cả giá trị có trong các biến thể của sản phẩm
      $sql = "SELECT DISTINCT av.*
                FROM attribute_values av
                INNER JOIN variant_attributes va ON av.id = va.value_id
                INNER JOIN product_variants pv ON va.variant_id = pv.id
                WHERE pv.product_id = ? AND va.attribute_id = ?
                ORDER BY av.sort_order";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([$product_id, $attribute['id']]);
      $attribute['values'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $attributes;
  }





  // Lấy tất cả attributes của sản phẩm kèm thông tin variant
  public function getProductAttributesWithVariantInfo($product_id) {
    $query = "SELECT DISTINCT
                    pa.id,
                    pa.name,
                    pa.type,
                    pa.sort_order
                  FROM product_attributes pa
                  INNER JOIN variant_attributes va ON pa.id = va.attribute_id
                  INNER JOIN product_variants pv ON va.variant_id = pv.id
                  WHERE pv.product_id = " . (int)$product_id . "
                  AND pa.is_visible = 1
                  ORDER BY pa.sort_order ASC";

    $result = $this->db_query($query);
    $attributes = $this->db_fetch_all($result);

    foreach ($attributes as &$attribute) {
      $attribute['values'] = $this->getAttributeValuesWithVariantInfo($product_id, $attribute['id']);
    }

    return $attributes;
  }

  // Lấy giá trị của attribute kèm thông tin variant
  private function getAttributeValuesWithVariantInfo($product_id, $attribute_id) {
    $query = "SELECT DISTINCT
                    av.id,
                    av.value,
                    av.color_code,
                    av.sort_order,
                    CASE WHEN EXISTS (
                        SELECT 1 FROM variant_attributes va2
                        INNER JOIN product_variants pv2 ON va2.variant_id = pv2.id
                        WHERE pv2.product_id = " . (int)$product_id . "
                        AND va2.attribute_id = " . (int)$attribute_id . "
                        AND va2.value_id = av.id
                    ) THEN 1 ELSE 0 END as has_variant
                  FROM attribute_values av
                  WHERE av.attribute_id = " . (int)$attribute_id . "
                  ORDER BY av.sort_order ASC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  // Lấy attribute theo ID
  public function getById($id) {
    return $this->db_select('product_attributes', "id = " . (int)$id);
  }
}
?>
