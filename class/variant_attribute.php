<?php
require_once 'db.php';

class VariantAttribute extends DB {
  protected $table = 'variant_attributes';

  public function __construct() {
    parent::__construct();
  }

  /**
   * Thêm attribute cho variant
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
   * Cập nhật variant attribute
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
   * Xóa variant attribute
   */
  public function delete($id) {
    $id = $this->db_escape($id);
    $query = "DELETE FROM {$this->table} WHERE id = '$id'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy attributes của variant với đầy đủ thông tin
   */
  // Lấy attributes của variant
  public function getByVariantId($variant_id) {
    $query = "SELECT va.*, pa.name as attribute_name, pa.type as attribute_type,
                         av.value as attribute_value, av.color_code
                  FROM variant_attributes va
                  INNER JOIN product_attributes pa ON va.attribute_id = pa.id
                  INNER JOIN attribute_values av ON va.value_id = av.id
                  WHERE va.variant_id = " . (int)$variant_id . "
                  ORDER BY pa.sort_order ASC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  // Kiểm tra xem variant có attribute cụ thể không
  public function variantHasAttribute($variant_id, $attribute_id, $value_id) {
    $query = "SELECT COUNT(*) as count FROM variant_attributes
                  WHERE variant_id = " . (int)$variant_id . "
                  AND attribute_id = " . (int)$attribute_id . "
                  AND value_id = " . (int)$value_id;

    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return $row['count'] > 0;
  }

  /**
   * Thêm hoặc cập nhật attribute cho variant
   */
  public function addAttributeToVariant($variantId, $attributeId, $valueId) {
    $variantId = $this->db_escape($variantId);
    $attributeId = $this->db_escape($attributeId);
    $valueId = $this->db_escape($valueId);

    // Kiểm tra đã tồn tại chưa
    $query = "SELECT id FROM {$this->table} WHERE variant_id = '$variantId' AND attribute_id = '$attributeId'";
    $result = $this->db_query($query);
    $existing = $this->db_fetch($result);

    if ($existing) {
      // Cập nhật
      $query = "UPDATE {$this->table} SET value_id = '$valueId' WHERE id = '{$existing['id']}'";
    } else {
      // Thêm mới
      $query = "INSERT INTO {$this->table} (variant_id, attribute_id, value_id)
                VALUES ('$variantId', '$attributeId', '$valueId')";
    }

    return $this->db_query($query) !== false;
  }

  /**
   * Xóa attribute của variant
   */
  public function removeAttributeFromVariant($variantId, $attributeId) {
    $variantId = $this->db_escape($variantId);
    $attributeId = $this->db_escape($attributeId);
    $query = "DELETE FROM {$this->table} WHERE variant_id = '$variantId' AND attribute_id = '$attributeId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy variant theo combination của attributes
   */
  public function findVariantByAttributes($productId, $attributes) {
    $productId = $this->db_escape($productId);

    if (empty($attributes)) return null;

    $conditions = [];
    foreach ($attributes as $attributeId => $valueId) {
      $attrId = $this->db_escape($attributeId);
      $valId = $this->db_escape($valueId);
      $conditions[] = "va.attribute_id = '$attrId' AND va.value_id = '$valId'";
    }

    $where = implode(' AND ', $conditions);

    $query = "SELECT v.*
              FROM product_variants v
              INNER JOIN variant_attributes va ON v.id = va.variant_id
              WHERE v.product_id = '$productId' AND $where
              GROUP BY v.id
              HAVING COUNT(va.id) = " . count($attributes);

    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Xóa tất cả attributes của variant
   */
  public function deleteByVariant($variantId) {
    $variantId = $this->db_escape($variantId);
    $query = "DELETE FROM {$this->table} WHERE variant_id = '$variantId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Xóa tất cả attributes của product
   */
  public function deleteByProduct($productId) {
    $productId = $this->db_escape($productId);
    $query = "DELETE va FROM {$this->table} va
              INNER JOIN product_variants v ON va.variant_id = v.id
              WHERE v.product_id = '$productId'";
    return $this->db_query($query) !== false;
  }

  // ========== CÁC PHƯƠNG THỨC MỚI BỔ SUNG ==========

  /**
   * Lấy tất cả variant attributes của sản phẩm
   */
  public function getByProductId($productId) {
    $productId = $this->db_escape($productId);
    $query = "SELECT va.*,
                     v.sku as variant_sku,
                     a.name as attribute_name,
                     a.type as attribute_type,
                     av.value as attribute_value,
                     av.color_code
              FROM {$this->table} va
              LEFT JOIN product_variants v ON va.variant_id = v.id
              LEFT JOIN product_attributes a ON va.attribute_id = a.id
              LEFT JOIN attribute_values av ON va.value_id = av.id
              WHERE v.product_id = '$productId'
              ORDER BY v.id, a.sort_order";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Kiểm tra attribute combination đã tồn tại chưa
   */
  public function combinationExists($productId, $attributes, $excludeVariantId = null) {
    $productId = $this->db_escape($productId);

    if (empty($attributes)) return false;

    $conditions = [];
    foreach ($attributes as $attributeId => $valueId) {
      $attrId = $this->db_escape($attributeId);
      $valId = $this->db_escape($valueId);
      $conditions[] = "va.attribute_id = '$attrId' AND va.value_id = '$valId'";
    }

    $where = implode(' AND ', $conditions);
    $excludeClause = $excludeVariantId ? "AND v.id != '" . $this->db_escape($excludeVariantId) . "'" : "";

    $query = "SELECT v.id
              FROM product_variants v
              INNER JOIN variant_attributes va ON v.id = va.variant_id
              WHERE v.product_id = '$productId' AND $where $excludeClause
              GROUP BY v.id
              HAVING COUNT(va.id) = " . count($attributes);

    $result = $this->db_query($query);
    return $this->db_fetch($result) !== false;
  }

  /**
   * Lấy tất cả attribute combinations có sẵn cho sản phẩm
   */
  public function getProductAttributeCombinations($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT
                a.id as attribute_id,
                a.name as attribute_name,
                a.type as attribute_type,
                av.id as value_id,
                av.value as attribute_value,
                av.color_code,
                COUNT(DISTINCT va.variant_id) as variant_count
              FROM product_attributes a
              LEFT JOIN attribute_values av ON a.id = av.attribute_id
              LEFT JOIN variant_attributes va ON av.id = va.value_id
              LEFT JOIN product_variants v ON va.variant_id = v.id AND v.product_id = '$productId'
              WHERE a.is_visible = 1
              GROUP BY a.id, av.id
              ORDER BY a.sort_order, av.sort_order";

    $result = $this->db_query($query);
    $rows = $this->db_fetch_all($result);

    // Group by attribute
    $combinations = [];
    foreach ($rows as $row) {
      $attributeId = $row['attribute_id'];
      if (!isset($combinations[$attributeId])) {
        $combinations[$attributeId] = [
          'attribute_id' => $attributeId,
          'attribute_name' => $row['attribute_name'],
          'attribute_type' => $row['attribute_type'],
          'values' => []
        ];
      }

      if ($row['value_id']) {
        $combinations[$attributeId]['values'][] = [
          'value_id' => $row['value_id'],
          'value' => $row['attribute_value'],
          'color_code' => $row['color_code'],
          'variant_count' => $row['variant_count']
        ];
      }
    }

    return array_values($combinations);
  }

  /**
   * Lấy variants theo attribute value
   */
  public function getVariantsByAttributeValue($productId, $attributeId, $valueId) {
    $productId = $this->db_escape($productId);
    $attributeId = $this->db_escape($attributeId);
    $valueId = $this->db_escape($valueId);

    $query = "SELECT v.*
              FROM product_variants v
              INNER JOIN variant_attributes va ON v.id = va.variant_id
              WHERE v.product_id = '$productId'
                AND va.attribute_id = '$attributeId'
                AND va.value_id = '$valueId'
              ORDER BY v.is_default DESC, v.sku";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy thống kê attribute usage
   */
  public function getAttributeUsageStats($productId = null) {
    $where = $productId ? "WHERE v.product_id = '" . $this->db_escape($productId) . "'" : "";

    $query = "SELECT
                a.id as attribute_id,
                a.name as attribute_name,
                av.id as value_id,
                av.value as attribute_value,
                COUNT(va.id) as usage_count
              FROM variant_attributes va
              LEFT JOIN product_attributes a ON va.attribute_id = a.id
              LEFT JOIN attribute_values av ON va.value_id = av.id
              LEFT JOIN product_variants v ON va.variant_id = v.id
              $where
              GROUP BY a.id, av.id
              ORDER BY usage_count DESC, a.name, av.value";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Validate variant attribute data
   */
  public function validate($data) {
    $errors = [];

    if (empty($data['variant_id'])) {
      $errors[] = 'Variant ID không được để trống';
    }

    if (empty($data['attribute_id'])) {
      $errors[] = 'Attribute ID không được để trống';
    }

    if (empty($data['value_id'])) {
      $errors[] = 'Value ID không được để trống';
    }

    // Kiểm tra attribute và value có khớp không
    if (!empty($data['attribute_id']) && !empty($data['value_id'])) {
      $attributeValueModel = new AttributeValue();
      $values = $attributeValueModel->getByAttribute($data['attribute_id']);
      $valueIds = array_column($values, 'id');

      if (!in_array($data['value_id'], $valueIds)) {
        $errors[] = 'Giá trị không thuộc về thuộc tính này';
      }
    }

    return $errors;
  }

  /**
   * Import variant attributes từ mảng
   */
//  public function importFromArray($variantId, $attributes) {
//    $success = true;
//    $imported = 0;
//
//    foreach ($attributes as $attributeId => $valueId) {
//      if ($this->addAttributeToVariant($variantId, $attributeId, $valueId)) {
//        $imported++;
//      } else {
//        $success = false;
//      }
//    }
//
//    return ['success' => $success, 'imported' => $imported];
//  }

  /**
   * Export variant attributes ra mảng
   */
  public function exportToArray($variantId) {
    $attributes = $this->getByVariantId($variantId);
    $result = [];

    foreach ($attributes as $attr) {
      $result[$attr['attribute_id']] = $attr['value_id'];
    }

    return $result;
  }

  /**
   * Sao chép attributes từ variant này sang variant khác
   */
  public function copyAttributes($fromVariantId, $toVariantId) {
    $attributes = $this->getByVariantId($fromVariantId);
    $success = true;

    foreach ($attributes as $attr) {
      if (!$this->addAttributeToVariant($toVariantId, $attr['attribute_id'], $attr['value_id'])) {
        $success = false;
      }
    }

    return $success;
  }

  /**
   * Lấy tất cả variants có attribute cụ thể
   */
  public function getVariantsWithAttribute($attributeId, $valueId = null) {
    $attributeId = $this->db_escape($attributeId);
    $where = $valueId ? "AND va.value_id = '" . $this->db_escape($valueId) . "'" : "";

    $query = "SELECT v.*, p.name_pr as product_name
              FROM product_variants v
              LEFT JOIN products p ON v.product_id = p.id
              INNER JOIN variant_attributes va ON v.id = va.variant_id
              WHERE va.attribute_id = '$attributeId' $where
              ORDER BY p.name_pr, v.sku";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy attribute summary cho sản phẩm
   */
  public function getProductAttributeSummary($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT
                a.id as attribute_id,
                a.name as attribute_name,
                a.type as attribute_type,
                COUNT(DISTINCT va.value_id) as distinct_values,
                COUNT(DISTINCT va.variant_id) as variants_with_attribute
              FROM product_attributes a
              LEFT JOIN variant_attributes va ON a.id = va.attribute_id
              LEFT JOIN product_variants v ON va.variant_id = v.id AND v.product_id = '$productId'
              GROUP BY a.id
              ORDER BY a.sort_order";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Kiểm tra và sửa lỗi variant attributes
   */
  public function fixVariantAttributes($productId = null) {
    $fixed = 0;

    // Tìm các variant attributes không có variant tương ứng
    $where = $productId ? "AND v.product_id = '" . $this->db_escape($productId) . "'" : "";

    $query = "SELECT va.id
              FROM {$this->table} va
              LEFT JOIN product_variants v ON va.variant_id = v.id
              WHERE v.id IS NULL $where";

    $result = $this->db_query($query);
    $orphaned = $this->db_fetch_all($result);

    foreach ($orphaned as $orphan) {
      if ($this->delete($orphan['id'])) {
        $fixed++;
      }
    }

    // Tìm các variant attributes trùng lặp
    $query = "SELECT variant_id, attribute_id, COUNT(*) as count
              FROM {$this->table}
              GROUP BY variant_id, attribute_id
              HAVING count > 1";

    $result = $this->db_query($query);
    $duplicates = $this->db_fetch_all($result);

    foreach ($duplicates as $dup) {
      $query = "SELECT id FROM {$this->table}
                WHERE variant_id = '{$dup['variant_id']}' AND attribute_id = '{$dup['attribute_id']}'
                ORDER BY id DESC LIMIT 1";
      $result = $this->db_query($query);
      $keepId = $this->db_fetch($result)['id'];

      $query = "DELETE FROM {$this->table}
                WHERE variant_id = '{$dup['variant_id']}' AND attribute_id = '{$dup['attribute_id']}' AND id != '$keepId'";
      if ($this->db_query($query)) {
        $fixed++;
      }
    }

    return $fixed;
  }

  /**
   * Lấy variants với attribute filters
   */
  public function getVariantsWithFilters($productId, $filters = []) {
    $productId = $this->db_escape($productId);

    if (empty($filters)) {
      $variantModel = new ProductVariant();
      return $variantModel->getByProductId($productId);
    }

    $filterConditions = [];
    foreach ($filters as $attributeId => $valueId) {
      $attrId = $this->db_escape($attributeId);
      $valId = $this->db_escape($valueId);
      $filterConditions[] = "EXISTS (
        SELECT 1 FROM variant_attributes va
        WHERE va.variant_id = v.id
        AND va.attribute_id = '$attrId'
        AND va.value_id = '$valId'
      )";
    }

    $where = implode(' AND ', $filterConditions);

    $query = "SELECT DISTINCT v.*
              FROM product_variants v
              WHERE v.product_id = '$productId' AND $where
              ORDER BY v.is_default DESC, v.sku";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy attribute matrix cho sản phẩm
   */
  public function getAttributeMatrix($productId) {
    $combinations = $this->getProductAttributeCombinations($productId);
    $variants = $this->getByProductId($productId);

    $matrix = [];
    foreach ($variants as $variant) {
      $variantAttributes = [];
      foreach ($combinations as $attribute) {
        foreach ($attribute['values'] as $value) {
          // Kiểm tra nếu variant có attribute value này
          foreach ($variant['attributes'] as $va) {
            if ($va['attribute_id'] == $attribute['attribute_id'] && $va['value_id'] == $value['value_id']) {
              $variantAttributes[$attribute['attribute_id']] = $value['value_id'];
              break;
            }
          }
        }
      }
      $matrix[] = [
        'variant_id' => $variant['variant_id'],
        'variant_sku' => $variant['variant_sku'],
        'attributes' => $variantAttributes
      ];
    }

    return $matrix;
  }

  /**
   * Lấy các attribute values chưa được sử dụng
   */
  public function getUnusedAttributeValues($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT a.id as attribute_id, a.name as attribute_name,
                     av.id as value_id, av.value as attribute_value
              FROM product_attributes a
              LEFT JOIN attribute_values av ON a.id = av.attribute_id
              WHERE NOT EXISTS (
                SELECT 1 FROM variant_attributes va
                LEFT JOIN product_variants v ON va.variant_id = v.id
                WHERE va.attribute_id = a.id
                AND va.value_id = av.id
                AND v.product_id = '$productId'
              )
              AND a.is_visible = 1
              ORDER BY a.sort_order, av.sort_order";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  // ========== CÁC PHƯƠNG THỨC BỔ SUNG THÊM ==========

  /**
   * Cập nhật attributes cho variant (batch update)
   */
  public function updateVariantAttributes($variantId, $attributes) {
    // Xóa attributes cũ
    $this->deleteByVariant($variantId);

    // Thêm attributes mới
    $success = true;
    $added = 0;

    foreach ($attributes as $attributeId => $valueId) {
      if ($this->addAttributeToVariant($variantId, $attributeId, $valueId)) {
        $added++;
      } else {
        $success = false;
      }
    }

    return ['success' => $success, 'added' => $added];
  }

  /**
   * Lấy thông tin chi tiết của attribute và value
   */
  public function getAttributeValueInfo($attributeId, $valueId) {
    $attributeId = $this->db_escape($attributeId);
    $valueId = $this->db_escape($valueId);

    $query = "SELECT a.name as attribute_name, a.type as attribute_type,
                     av.value as value_name, av.color_code
              FROM product_attributes a
              LEFT JOIN attribute_values av ON a.id = av.attribute_id
              WHERE a.id = '$attributeId' AND av.id = '$valueId'
              LIMIT 1";

    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy tất cả attribute combinations không trùng
   */
  public function getUniqueAttributeCombinations($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT
                GROUP_CONCAT(CONCAT(va.attribute_id, ':', va.value_id) ORDER BY va.attribute_id SEPARATOR '|') as combination_key,
                COUNT(DISTINCT va.variant_id) as variant_count,
                GROUP_CONCAT(DISTINCT v.sku ORDER BY v.sku SEPARATOR ', ') as variant_skus
              FROM variant_attributes va
              LEFT JOIN product_variants v ON va.variant_id = v.id
              WHERE v.product_id = '$productId'
              GROUP BY va.variant_id
              ORDER BY variant_count DESC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Kiểm tra attribute value có được sử dụng trong product không
   */
  public function isAttributeValueUsed($productId, $attributeId, $valueId) {
    $productId = $this->db_escape($productId);
    $attributeId = $this->db_escape($attributeId);
    $valueId = $this->db_escape($valueId);

    $query = "SELECT COUNT(*) as count
              FROM variant_attributes va
              LEFT JOIN product_variants v ON va.variant_id = v.id
              WHERE v.product_id = '$productId'
                AND va.attribute_id = '$attributeId'
                AND va.value_id = '$valueId'";

    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return $row['count'] > 0;
  }

  /**
   * Lấy variants theo multiple attribute filters (OR condition)
   */
  public function getVariantsByMultipleAttributes($productId, $attributeFilters, $matchAll = false) {
    $productId = $this->db_escape($productId);

    if (empty($attributeFilters)) {
      $variantModel = new ProductVariant();
      return $variantModel->getByProductId($productId);
    }

    $conditions = [];
    foreach ($attributeFilters as $filter) {
      $attrId = $this->db_escape($filter['attribute_id']);
      $valId = $this->db_escape($filter['value_id']);
      $conditions[] = "(va.attribute_id = '$attrId' AND va.value_id = '$valId')";
    }

    $conditionStr = implode($matchAll ? ' AND ' : ' OR ', $conditions);

    $query = "SELECT DISTINCT v.*
              FROM product_variants v
              INNER JOIN variant_attributes va ON v.id = va.variant_id
              WHERE v.product_id = '$productId' AND ($conditionStr)
              ORDER BY v.is_default DESC, v.sku";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy tất cả attribute values được sử dụng trong product
   */
  public function getUsedAttributeValues($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT DISTINCT
                va.attribute_id,
                va.value_id,
                a.name as attribute_name,
                a.type as attribute_type,
                av.value as attribute_value,
                av.color_code
              FROM variant_attributes va
              LEFT JOIN product_variants v ON va.variant_id = v.id
              LEFT JOIN product_attributes a ON va.attribute_id = a.id
              LEFT JOIN attribute_values av ON va.value_id = av.id
              WHERE v.product_id = '$productId'
              ORDER BY a.sort_order, av.sort_order";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tạo attribute combination từ mảng
   */
  public function createAttributeCombination($productId, $combinationData) {
    // Tạo variant trước
    $variantModel = new ProductVariant();

    $variantData = [
      'product_id' => $productId,
      'sku' => $combinationData['sku'] ?? $variantModel->generateAutoSKU($productId),
      'price' => $combinationData['price'] ?? 0,
      'sale_price' => $combinationData['sale_price'] ?? null,
      'stock_quantity' => $combinationData['stock_quantity'] ?? 0,
      'weight' => $combinationData['weight'] ?? null,
      'is_default' => $combinationData['is_default'] ?? 0
    ];

    if ($variantModel->create($variantData)) {
      $variantId = $variantModel->db_insert_id();

      // Thêm attributes
      $attributes = $combinationData['attributes'] ?? [];
      $this->updateVariantAttributes($variantId, $attributes);

      return $variantId;
    }

    return false;
  }

  /**
   * Xóa attribute value khỏi tất cả variants
   */
  public function removeAttributeValueFromAllVariants($productId, $attributeId, $valueId) {
    $productId = $this->db_escape($productId);
    $attributeId = $this->db_escape($attributeId);
    $valueId = $this->db_escape($valueId);

    $query = "DELETE va FROM variant_attributes va
              LEFT JOIN product_variants v ON va.variant_id = v.id
              WHERE v.product_id = '$productId'
                AND va.attribute_id = '$attributeId'
                AND va.value_id = '$valueId'";

    return $this->db_query($query) !== false;
  }

  /**
   * Thay thế attribute value trong tất cả variants
   */
  public function replaceAttributeValue($productId, $attributeId, $oldValueId, $newValueId) {
    $productId = $this->db_escape($productId);
    $attributeId = $this->db_escape($attributeId);
    $oldValueId = $this->db_escape($oldValueId);
    $newValueId = $this->db_escape($newValueId);

    $query = "UPDATE variant_attributes va
              LEFT JOIN product_variants v ON va.variant_id = v.id
              SET va.value_id = '$newValueId'
              WHERE v.product_id = '$productId'
                AND va.attribute_id = '$attributeId'
                AND va.value_id = '$oldValueId'";

    return $this->db_query($query) !== false;
  }

  /**
   * Lấy attribute distribution statistics
   */
  public function getAttributeDistribution($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT
                a.id as attribute_id,
                a.name as attribute_name,
                a.type as attribute_type,
                av.id as value_id,
                av.value as attribute_value,
                av.color_code,
                COUNT(DISTINCT va.variant_id) as variant_count,
                COUNT(DISTINCT va.variant_id) * 100.0 /
                (SELECT COUNT(DISTINCT variant_id) FROM variant_attributes WHERE variant_id IN
                  (SELECT id FROM product_variants WHERE product_id = '$productId')) as percentage
              FROM product_attributes a
              LEFT JOIN attribute_values av ON a.id = av.attribute_id
              LEFT JOIN variant_attributes va ON av.id = va.value_id
              LEFT JOIN product_variants v ON va.variant_id = v.id AND v.product_id = '$productId'
              WHERE a.is_visible = 1
              GROUP BY a.id, av.id
              ORDER BY a.sort_order, variant_count DESC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Validate attribute combination
   */
  public function validateCombination($productId, $attributes) {
    $errors = [];

    if (empty($attributes)) {
      return $errors;
    }

    // Kiểm tra attribute có thuộc sản phẩm không
    $usedAttributes = $this->getUsedAttributeValues($productId);
    $usedAttributeIds = array_unique(array_column($usedAttributes, 'attribute_id'));

    foreach (array_keys($attributes) as $attributeId) {
      if (!in_array($attributeId, $usedAttributeIds)) {
        $errors[] = "Attribute ID $attributeId không thuộc sản phẩm này";
      }
    }

    // Kiểm tra combination đã tồn tại chưa
    if ($this->combinationExists($productId, $attributes)) {
      $errors[] = "Attribute combination đã tồn tại";
    }

    return $errors;
  }

  /**
   * Lấy variants không có attribute nào
   */
  public function getVariantsWithoutAttributes($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT v.*
              FROM product_variants v
              LEFT JOIN variant_attributes va ON v.id = va.variant_id
              WHERE v.product_id = '$productId'
                AND va.id IS NULL
              ORDER BY v.sku";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Thêm attribute cho tất cả variants của sản phẩm
   */
  public function addAttributeToAllVariants($productId, $attributeId, $valueId) {
    $variants = (new ProductVariant())->getByProductId($productId);
    $added = 0;

    foreach ($variants as $variant) {
      if ($this->addAttributeToVariant($variant['id'], $attributeId, $valueId)) {
        $added++;
      }
    }

    return $added;
  }

  /**
   * Export attribute combinations to CSV format
   */
  public function exportToCSV($productId) {
    $combinations = $this->getProductAttributeCombinations($productId);
    $variants = $this->getByProductId($productId);

    $csvData = [];

    // Header
    $headers = ['Variant SKU', 'Price', 'Stock'];
    foreach ($combinations as $attribute) {
      $headers[] = $attribute['attribute_name'];
    }
    $csvData[] = $headers;

    // Rows
    foreach ($variants as $variant) {
      $row = [
        $variant['variant_sku'],
        $variant['price'] ?? '',
        $variant['stock_quantity'] ?? ''
      ];

      foreach ($combinations as $attribute) {
        $value = '';
        foreach ($variant['attributes'] as $attr) {
          if ($attr['attribute_id'] == $attribute['attribute_id']) {
            $value = $attr['attribute_value'];
            if ($attr['color_code']) {
              $value .= " ({$attr['color_code']})";
            }
            break;
          }
        }
        $row[] = $value;
      }

      $csvData[] = $row;
    }

    return $csvData;
  }

  /**
   * Import attribute combinations from CSV/array
   */
  public function importFromArray($productId, $data) {
    $results = [
      'success' => 0,
      'failed' => 0,
      'skipped' => 0,
      'errors' => []
    ];

    foreach ($data as $row) {
      try {
        $sku = isset($row['sku']) ? $row['sku'] : '';
        $price = isset($row['price']) ? $row['price'] : 0;
        $stock = isset($row['stock_quantity']) ? $row['stock_quantity'] : 0;
        $attributes = isset($row['attributes']) ? $row['attributes'] : [];

        // Kiểm tra nếu variant đã tồn tại
        $existingVariant = null;
        if ($sku) {
          $variantModel = new ProductVariant();
          $existingVariants = $variantModel->search($sku, $productId);
          if (!empty($existingVariants)) {
            $existingVariant = $existingVariants[0];
          }
        }

        if ($existingVariant) {
          // Cập nhật variant hiện tại
          $variantId = $existingVariant['id'];
          $this->updateVariantAttributes($variantId, $attributes);
          $results['success']++;
        } else {
          // Tạo variant mới
          $combinationData = [
            'sku' => $sku,
            'price' => $price,
            'stock_quantity' => $stock,
            'attributes' => $attributes
          ];

          if ($this->createAttributeCombination($productId, $combinationData)) {
            $results['success']++;
          } else {
            $results['failed']++;
            $results['errors'][] = "Không thể tạo variant với SKU: $sku";
          }
        }
      } catch (Exception $e) {
        $results['failed']++;
        $results['errors'][] = $e->getMessage();
      }
    }

    return $results;
  }
}
?>
