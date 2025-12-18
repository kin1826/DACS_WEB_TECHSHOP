<?php
require_once 'db.php';

class ProductVariant extends DB {
  protected $table = 'product_variants';

  public function __construct() {
    parent::__construct();
  }

  /**
   * Tạo SKU cho variant
   */
  public function generateSKU($productSku) {
    $timestamp = time();
    $random = mt_rand(100, 999);
    return $productSku . '-V' . $timestamp . $random;
  }

  /**
   * Tạo mới variant
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
   * Cập nhật variant
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
   * Xóa variant
   */
  public function delete($id) {
    $id = $this->db_escape($id);
    $query = "DELETE FROM {$this->table} WHERE id = '$id'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy variant theo ID
   */
  public function findById($id) {
    $id = $this->db_escape($id);
    $query = "SELECT v.*, i.image_url
              FROM {$this->table} v
              LEFT JOIN product_images i ON v.image_id = i.id
              WHERE v.id = '$id'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  // Lấy tất cả biến thể với đầy đủ thông tin attributes
  public function getVariantsWithAttributes($product_id) {
    // Lấy tất cả biến thể của sản phẩm
    $query = "SELECT * FROM product_variants
                  WHERE product_id = " . (int)$product_id . "
                  ORDER BY is_default DESC, id ASC";

    $result = $this->db_query($query);
    $variants = $this->db_fetch_all($result);

    // Thêm thông tin attributes vào mỗi variant
    $variantAttributeModel = new VariantAttribute();
    foreach ($variants as &$variant) {
      $variant['attributes'] = $variantAttributeModel->getByVariantId($variant['id']);
    }

    return $variants;
  }

  // Tìm biến thể dựa trên các attributes được chọn
  // Tìm biến thể dựa trên các attributes được chọn
  public function findVariantByAttributes($product_id, $selected_attributes) {
    if (empty($selected_attributes)) {
      return null;
    }

    // Tạo điều kiện WHERE
    $where_conditions = [];
    foreach ($selected_attributes as $attr_id => $value_id) {
      $where_conditions[] = "(va.attribute_id = " . (int)$attr_id . "
                                   AND va.value_id = " . (int)$value_id . ")";
    }

    $where_clause = implode(' AND ', $where_conditions);

    // Query tìm variant phù hợp
    $query = "SELECT pv.*, COUNT(DISTINCT va.attribute_id) as matched_attributes
                  FROM product_variants pv
                  INNER JOIN variant_attributes va ON pv.id = va.variant_id
                  WHERE pv.product_id = " . (int)$product_id . "
                  AND (" . $where_clause . ")
                  GROUP BY pv.id
                  HAVING matched_attributes = " . count($selected_attributes) . "
                  LIMIT 1";

    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  public function getVariantByAttributes($product_id, $selectedAttributes) {
    // Xây dựng query động dựa trên số thuộc tính
    $sql = "SELECT DISTINCT pv.*, pi.image_url
            FROM product_variants pv
            LEFT JOIN product_images pi ON pv.image_id = pi.id
            WHERE pv.product_id = ? ";

    $params = [$product_id];

    // Thêm điều kiện cho từng thuộc tính
    foreach ($selectedAttributes as $attrId => $valueId) {
      $sql .= " AND pv.id IN (
                    SELECT variant_id
                    FROM variant_attributes
                    WHERE attribute_id = ? AND value_id = ?
                )";
      $params[] = $attrId;
      $params[] = $valueId;
    }

    $stmt = $this->prepare($sql);
    $stmt->execute($params);

    // Nếu có nhiều kết quả, ưu tiên biến thể mặc định
    $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($variants) > 0) {
      // Tìm biến thể mặc định
      foreach ($variants as $variant) {
        if ($variant['is_default'] == 1) {
          return $variant;
        }
      }
      // Nếu không có mặc định, trả về cái đầu tiên
      return $variants[0];
    }

    return null;
  }

  /**
   * Lấy variants theo product_id
   */
  public function getByProductId($productId) {
    $productId = $this->db_escape($productId);
    $query = "SELECT v.*, i.image_url
              FROM {$this->table} v
              LEFT JOIN product_images i ON v.image_id = i.id
              WHERE v.product_id = '$productId'
              ORDER BY v.is_default DESC, v.created_at ASC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy variant mặc định
   */
//  public function getDefaultVariant($productId) {
//    $productId = $this->db_escape($productId);
//    $query = "SELECT * FROM {$this->table} WHERE product_id = '$productId' AND is_default = 1 LIMIT 1";
//    $result = $this->db_query($query);
//    return $this->db_fetch($result);
//  }

  /**
   * Đặt variant mặc định
   */
  public function setDefaultVariant($variantId, $productId) {
    // Bỏ tất cả variant mặc định cũ
    $productId = $this->db_escape($productId);
    $query = "UPDATE {$this->table} SET is_default = 0 WHERE product_id = '$productId'";
    $this->db_query($query);

    // Đặt variant mặc định mới
    $variantId = $this->db_escape($variantId);
    $query = "UPDATE {$this->table} SET is_default = 1 WHERE id = '$variantId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Cập nhật stock quantity
   */
  public function updateStock($variantId, $quantity) {
    $variantId = $this->db_escape($variantId);
    $quantity = (int)$quantity;
    $query = "UPDATE {$this->table} SET stock_quantity = '$quantity' WHERE id = '$variantId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Kiểm tra SKU đã tồn tại chưa
   */
  public function skuExists($sku) {
    $sku = $this->db_escape($sku);
    $query = "SELECT id FROM {$this->table} WHERE sku = '$sku'";
    $result = $this->db_query($query);
    return $this->db_fetch($result) !== false;
  }

  /**
   * Lấy số lượng variants của sản phẩm
   */
  public function countByProduct($productId) {
    $productId = $this->db_escape($productId);
    $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE product_id = '$productId'";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return isset($row['total']) ? $row['total'] : 0;
  }

  /**
   * Xóa tất cả variants của sản phẩm
   */
  public function deleteByProduct($productId) {
    $productId = $this->db_escape($productId);
    $query = "DELETE FROM {$this->table} WHERE product_id = '$productId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Tạo variant với attributes
   */
  public function createWithAttributes($variantData, $attributes = []) {
    // Tạo variant trước
    if ($this->create($variantData)) {
      $variantId = $this->db_insert_id();

      // Thêm attributes
      $variantAttributeModel = new VariantAttribute();
      foreach ($attributes as $attributeId => $valueId) {
        $variantAttributeModel->addAttributeToVariant($variantId, $attributeId, $valueId);
      }

      return $variantId;
    }

    return false;
  }

  /**
   * Cập nhật variant với attributes
   */
  public function updateWithAttributes($variantId, $variantData, $attributes = []) {
    // Cập nhật variant
    if ($this->update($variantId, $variantData)) {
      // Cập nhật attributes
      $variantAttributeModel = new VariantAttribute();

      // Xóa attributes cũ
      $variantAttributeModel->deleteByVariant($variantId);

      // Thêm attributes mới
      foreach ($attributes as $attributeId => $valueId) {
        $variantAttributeModel->addAttributeToVariant($variantId, $attributeId, $valueId);
      }

      return true;
    }

    return false;
  }

  /**
   * Lấy variants với đầy đủ attributes
   */
  public function getWithAttributes($productId) {
    $productId = $this->db_escape($productId);
    $query = "SELECT v.*
              FROM {$this->table} v
              WHERE v.product_id = '$productId'
              ORDER BY v.is_default DESC, v.created_at ASC";
    $result = $this->db_query($query);
    $variants = $this->db_fetch_all($result);

    // Lấy attributes cho từng variant
    $variantAttributeModel = new VariantAttribute();
    foreach ($variants as &$variant) {
      $variant['attributes'] = $variantAttributeModel->getByVariantId($variant['id']);
    }

    return $variants;
  }

  // ========== CÁC PHƯƠNG THỨC MỚI BỔ SUNG ==========

  /**
   * Lấy tất cả attribute combinations có thể có
   */
  public function getAvailableAttributeCombinations($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT
                va.attribute_id,
                va.value_id,
                a.name as attribute_name,
                av.value as attribute_value,
                av.color_code,
                COUNT(DISTINCT v.id) as variant_count
              FROM variant_attributes va
              LEFT JOIN product_attributes a ON va.attribute_id = a.id
              LEFT JOIN attribute_values av ON va.value_id = av.id
              LEFT JOIN product_variants v ON va.variant_id = v.id
              WHERE v.product_id = '$productId'
              GROUP BY va.attribute_id, va.value_id
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
          'values' => []
        ];
      }

      $combinations[$attributeId]['values'][] = [
        'value_id' => $row['value_id'],
        'value' => $row['attribute_value'],
        'color_code' => $row['color_code'],
        'variant_count' => $row['variant_count']
      ];
    }

    return array_values($combinations);
  }

  /**
   * Kiểm tra variant có tồn tại với attributes không
   */
  public function variantExistsWithAttributes($productId, $attributes) {
    return $this->getVariantByAttributes($productId, $attributes) !== false;
  }

  /**
   * Cập nhật giá cho tất cả variants
   */
  public function updatePriceForAll($productId, $price, $salePrice = null) {
    $productId = $this->db_escape($productId);
    $price = (float)$price;
    $salePrice = $salePrice !== null ? (float)$salePrice : 'NULL';

    $query = "UPDATE {$this->table}
              SET price = $price, sale_price = $salePrice
              WHERE product_id = '$productId'";

    return $this->db_query($query) !== false;
  }

  /**
   * Cập nhật stock cho tất cả variants
   */
  public function updateStockForAll($productId, $quantity) {
    $productId = $this->db_escape($productId);
    $quantity = (int)$quantity;

    $query = "UPDATE {$this->table}
              SET stock_quantity = $quantity
              WHERE product_id = '$productId'";

    return $this->db_query($query) !== false;
  }

  /**
   * Lấy variants với stock thấp
   */
  public function getLowStockVariants($threshold = 10) {
    $threshold = (int)$threshold;

    $query = "SELECT v.*, p.name_pr as product_name, p.sku as product_sku
              FROM {$this->table} v
              LEFT JOIN products p ON v.product_id = p.id
              WHERE v.stock_quantity <= $threshold AND v.stock_quantity > 0
              ORDER BY v.stock_quantity ASC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy variants hết hàng
   */
  public function getOutOfStockVariants() {
    $query = "SELECT v.*, p.name_pr as product_name, p.sku as product_sku
              FROM {$this->table} v
              LEFT JOIN products p ON v.product_id = p.id
              WHERE v.stock_quantity <= 0
              ORDER BY p.name_pr";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tăng/giảm stock quantity
   */
  public function adjustStock($variantId, $quantity) {
    $variant = $this->findById($variantId);
    if (!$variant) return false;

    $newQuantity = $variant['stock_quantity'] + $quantity;
    if ($newQuantity < 0) $newQuantity = 0;

    return $this->updateStock($variantId, $newQuantity);
  }

  /**
   * Lấy thống kê variants
   */
  public function getStats($productId = null) {
    $where = $productId ? "WHERE product_id = '" . $this->db_escape($productId) . "'" : "";

    $query = "SELECT
                COUNT(*) as total_variants,
                SUM(CASE WHEN is_default = 1 THEN 1 ELSE 0 END) as default_variants,
                SUM(CASE WHEN stock_quantity > 0 THEN 1 ELSE 0 END) as in_stock_variants,
                SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock_variants,
                SUM(CASE WHEN sale_price > 0 THEN 1 ELSE 0 END) as on_sale_variants,
                AVG(price) as avg_price,
                AVG(sale_price) as avg_sale_price
              FROM {$this->table}
              $where";

    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Validate variant data
   */
  public function validate($data) {
    $errors = [];

    if (empty($data['product_id'])) {
      $errors[] = 'Product ID không được để trống';
    }

    if (empty($data['sku'])) {
      $errors[] = 'SKU không được để trống';
    } elseif ($this->skuExists($data['sku'])) {
      $errors[] = 'SKU đã tồn tại';
    }

    if (!isset($data['price']) || $data['price'] < 0) {
      $errors[] = 'Giá không hợp lệ';
    }

    if (isset($data['sale_price']) && $data['sale_price'] < 0) {
      $errors[] = 'Giá khuyến mãi không hợp lệ';
    }

    if (isset($data['stock_quantity']) && $data['stock_quantity'] < 0) {
      $errors[] = 'Số lượng tồn kho không hợp lệ';
    }

    return $errors;
  }

  /**
   * Tìm kiếm variants
   */
  public function search($keyword, $productId = null) {
    $keyword = $this->db_escape($keyword);
    $where = "WHERE v.sku LIKE '%$keyword%'";

    if ($productId) {
      $productId = $this->db_escape($productId);
      $where .= " AND v.product_id = '$productId'";
    }

    $query = "SELECT v.*, p.name_pr as product_name
              FROM {$this->table} v
              LEFT JOIN products p ON v.product_id = p.id
              $where
              ORDER BY p.name_pr, v.sku";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy variants với phân trang
   */
  public function getWithPagination($productId = null, $page = 1, $perPage = 20) {
    $offset = ($page - 1) * $perPage;
    $where = $productId ? "WHERE product_id = '" . $this->db_escape($productId) . "'" : "";

    $query = "SELECT v.*, p.name_pr as product_name
              FROM {$this->table} v
              LEFT JOIN products p ON v.product_id = p.id
              $where
              ORDER BY p.name_pr, v.sku
              LIMIT $offset, $perPage";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy tổng số trang
   */
  public function getTotalPages($productId = null, $perPage = 20) {
    $where = $productId ? "WHERE product_id = '" . $this->db_escape($productId) . "'" : "";
    $query = "SELECT COUNT(*) as total FROM {$this->table} $where";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return ceil($row['total'] / $perPage);
  }

  /**
   * Lấy variants theo price range
   */
  public function getByPriceRange($minPrice, $maxPrice) {
    $minPrice = (float)$minPrice;
    $maxPrice = (float)$maxPrice;

    $query = "SELECT v.*, p.name_pr as product_name
              FROM {$this->table} v
              LEFT JOIN products p ON v.product_id = p.id
              WHERE (v.sale_price > 0 AND v.sale_price BETWEEN $minPrice AND $maxPrice)
                 OR (v.sale_price = 0 AND v.price BETWEEN $minPrice AND $maxPrice)
              ORDER BY v.price";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Cập nhật variant image
   */
  public function updateVariantImage($variantId, $imageId) {
    return $this->update($variantId, ['image_id' => $imageId]);
  }

  /**
   * Lấy variants có hình ảnh
   */
  public function getVariantsWithImages($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT v.*, i.image_url, i.alt_text
              FROM {$this->table} v
              INNER JOIN product_images i ON v.image_id = i.id
              WHERE v.product_id = '$productId'
              ORDER BY v.is_default DESC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tạo variants từ attribute matrix
   */
  public function createFromAttributeMatrix($productId, $attributeMatrix, $baseData) {
    $created = 0;
    $errors = 0;

    foreach ($attributeMatrix as $combination) {
      $variantData = array_merge($baseData, [
        'product_id' => $productId,
        'sku' => $this->generateSKU(isset($baseData['sku']) ? $baseData['sku'] : 'PROD')
      ]);

      if ($this->createWithAttributes($variantData, $combination)) {
        $created++;
      } else {
        $errors++;
      }
    }

    return ['created' => $created, 'errors' => $errors];
  }

  /**
   * Kiểm tra và sửa lỗi variants
   */
  public function fixVariants($productId) {
    $fixed = 0;

    // Kiểm tra nếu không có variant mặc định
    $defaultVariant = $this->getDefaultVariant($productId);
    if (!$defaultVariant) {
      $variants = $this->getByProductId($productId);
      if (!empty($variants)) {
        $this->setDefaultVariant($variants[0]['id'], $productId);
        $fixed++;
      }
    }

    // Kiểm tra SKU trùng
    $query = "SELECT sku, COUNT(*) as count
              FROM {$this->table}
              WHERE product_id = '" . $this->db_escape($productId) . "'
              GROUP BY sku
              HAVING count > 1";
    $result = $this->db_query($query);
    $duplicates = $this->db_fetch_all($result);

    foreach ($duplicates as $dup) {
      $variants = $this->search($dup['sku'], $productId);
      foreach ($variants as $index => $variant) {
        if ($index > 0) { // Giữ lại variant đầu tiên
          $newSku = $this->generateSKU($variant['sku']);
          $this->update($variant['id'], ['sku' => $newSku]);
          $fixed++;
        }
      }
    }

    return $fixed;
  }

  /**
   * Lấy variants cho order management
   */
  public function getForOrderManagement() {
    $query = "SELECT v.*, p.name_pr as product_name, p.sku as product_sku,
                     (SELECT COUNT(*) FROM order_items oi WHERE oi.variant_id = v.id) as order_count
              FROM {$this->table} v
              LEFT JOIN products p ON v.product_id = p.id
              ORDER BY p.name_pr, v.sku";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy best selling variants
   */
  public function getBestSellingVariants($limit = 10) {
    $limit = (int)$limit;

    $query = "SELECT v.*, p.name_pr as product_name,
                     SUM(oi.quantity) as total_sold,
                     SUM(oi.quantity * oi.price) as total_revenue
              FROM {$this->table} v
              LEFT JOIN products p ON v.product_id = p.id
              LEFT JOIN order_items oi ON v.id = oi.variant_id
              GROUP BY v.id
              ORDER BY total_sold DESC
              LIMIT $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  // ========== CÁC PHƯƠNG THỨC MỚI THÊM ==========

  /**
   * Kiểm tra variant đã tồn tại với attributes không (dành cho PHP code trước)
   */
//  public function variantExists($productId, $attributes = []) {
//    return $this->variantExistsWithAttributes($productId, $attributes);
//  }

  // ========== PHƯƠNG THỨC MỚI ==========

  /**
   * Kiểm tra variant đã tồn tại với attributes không - Phiên bản đơn giản và chắc chắn
   */
  public function checkIfVariantExists($productId, $attributes) {
    // Nếu không có attributes, không thể check
    if (empty($attributes)) {
      return false;
    }

    // 1. Kiểm tra xem có variant nào trong sản phẩm không
    $variantCount = $this->countByProduct($productId);
    if ($variantCount == 0) {
      return false; // Chưa có variant nào, chắc chắn chưa tồn tại
    }

    // 2. Lấy tất cả variants
    $variants = $this->getWithAttributes($productId);

    // 3. Kiểm tra từng variant
    foreach ($variants as $variant) {
      if (empty($variant['attributes'])) {
        continue; // Variant không có attributes
      }

      // Chuyển attributes của variant thành mảng [attribute_id => value_id]
      $variantAttrs = [];
      foreach ($variant['attributes'] as $attr) {
        $variantAttrs[$attr['attribute_id']] = $attr['value_id'];
      }

      // So sánh với attributes cần check
      if ($variantAttrs == $attributes) {
        return true; // Tìm thấy variant trùng
      }
    }

    return false; // Không tìm thấy
  }

  // Cập nhật hàm variantExists để dùng hàm mới
  public function variantExists($productId, $attributes = []) {
    return $this->checkIfVariantExists($productId, $attributes);
  }

  /**
   * Kiểm tra biến thể với attributes này đã tồn tại chưa (phiên bản nâng cao)
   */
  public function checkVariantExistence($productId, $attributes) {
    if (empty($attributes)) {
      return false;
    }

    $productId = $this->db_escape($productId);
    $attributeIds = [];
    $valueIds = [];

    foreach ($attributes as $attributeId => $valueId) {
      $attributeIds[] = $this->db_escape($attributeId);
      $valueIds[] = $this->db_escape($valueId);
    }

    $attributeCount = count($attributes);

    // Tạo subquery cho từng attribute
    $subQueries = [];
    for ($i = 0; $i < $attributeCount; $i++) {
      $subQueries[] = "EXISTS (
        SELECT 1 FROM variant_attributes va$i
        WHERE va$i.variant_id = v.id
        AND va$i.attribute_id = '{$attributeIds[$i]}'
        AND va$i.value_id = '{$valueIds[$i]}'
      )";
    }

    $subQueryStr = implode(' AND ', $subQueries);

    $query = "SELECT v.id, v.sku
              FROM {$this->table} v
              WHERE v.product_id = '$productId'
              AND $subQueryStr
              LIMIT 1";

    $result = $this->db_query($query);
    return $this->db_fetch($result) !== false;
  }

  /**
   * Lấy tất cả attribute combinations của một sản phẩm
   */
  public function getAllAttributeCombinations($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT
                GROUP_CONCAT(CONCAT(a.id, ':', av.id) ORDER BY a.id SEPARATOR '|') as combination_key,
                v.id as variant_id,
                v.sku,
                GROUP_CONCAT(CONCAT(a.name, ':', av.value) ORDER BY a.id SEPARATOR ', ') as combination_text,
                GROUP_CONCAT(av.color_code ORDER BY a.id SEPARATOR '|') as colors
              FROM variant_attributes va
              INNER JOIN product_variants v ON va.variant_id = v.id
              INNER JOIN product_attributes a ON va.attribute_id = a.id
              INNER JOIN attribute_values av ON va.value_id = av.id
              WHERE v.product_id = '$productId'
              GROUP BY v.id
              ORDER BY v.sku";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tạo variant từ các attribute được chọn
   */
  public function createFromAttributes($productId, $variantData, $selectedAttributes) {
    // Tạo variant
    if ($this->create($variantData)) {
      $variantId = $this->db_insert_id();

      // Thêm attributes
      $variantAttributeModel = new VariantAttribute();
      foreach ($selectedAttributes as $attributeId => $valueId) {
        $variantAttributeModel->addAttributeToVariant($variantId, $attributeId, $valueId);
      }

      return $variantId;
    }

    return false;
  }

  /**
   * Cập nhật attributes cho variant
   */
  public function updateVariantAttributes($variantId, $attributes) {
    $variantAttributeModel = new VariantAttribute();

    // Xóa attributes cũ
    $variantAttributeModel->deleteByVariant($variantId);

    // Thêm attributes mới
    $success = true;
    foreach ($attributes as $attributeId => $valueId) {
      if (!$variantAttributeModel->addAttributeToVariant($variantId, $attributeId, $valueId)) {
        $success = false;
      }
    }

    return $success;
  }

  /**
   * Lấy danh sách attributes có sẵn cho sản phẩm
   */
  public function getAvailableAttributesForProduct($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT DISTINCT
                a.id as attribute_id,
                a.name as attribute_name,
                a.type as attribute_type,
                a.is_visible,
                a.sort_order as attribute_order
              FROM variant_attributes va
              INNER JOIN product_variants v ON va.variant_id = v.id
              INNER JOIN product_attributes a ON va.attribute_id = a.id
              WHERE v.product_id = '$productId'
              ORDER BY a.sort_order, a.name";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy danh sách values có sẵn cho từng attribute của sản phẩm
   */
  public function getAvailableAttributeValues($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT
                a.id as attribute_id,
                a.name as attribute_name,
                a.type as attribute_type,
                av.id as value_id,
                av.value as value_name,
                av.color_code,
                av.sort_order as value_order,
                COUNT(v.id) as variant_count
              FROM variant_attributes va
              INNER JOIN product_variants v ON va.variant_id = v.id
              INNER JOIN product_attributes a ON va.attribute_id = a.id
              INNER JOIN attribute_values av ON va.value_id = av.id
              WHERE v.product_id = '$productId'
              GROUP BY a.id, av.id
              ORDER BY a.sort_order, av.sort_order, av.value";

    $result = $this->db_query($query);
    $rows = $this->db_fetch_all($result);

    // Nhóm theo attribute
    $grouped = [];
    foreach ($rows as $row) {
      $attributeId = $row['attribute_id'];
      if (!isset($grouped[$attributeId])) {
        $grouped[$attributeId] = [
          'attribute_id' => $row['attribute_id'],
          'attribute_name' => $row['attribute_name'],
          'attribute_type' => $row['attribute_type'],
          'values' => []
        ];
      }

      $grouped[$attributeId]['values'][] = [
        'value_id' => $row['value_id'],
        'value_name' => $row['value_name'],
        'color_code' => $row['color_code'],
        'variant_count' => $row['variant_count'],
        'value_order' => $row['value_order']
      ];
    }

    return array_values($grouped);
  }

  /**
   * Tạo SKU dựa trên attributes
   */
  public function generateSKUFromAttributes($productSku, $attributes = []) {
    $sku = $productSku;

    if (!empty($attributes)) {
      $variantAttributeModel = new VariantAttribute();
      $attributeCodes = [];

      foreach ($attributes as $attributeId => $valueId) {
        $attributeInfo = $variantAttributeModel->getAttributeValueInfo($attributeId, $valueId);
        if ($attributeInfo) {
          // Lấy ký tự đầu của attribute và value
          $attrCode = substr($attributeInfo['attribute_name'], 0, 1);
          $valCode = substr($attributeInfo['value_name'], 0, 3);
          $attributeCodes[] = strtoupper($attrCode . $valCode);
        }
      }

      if (!empty($attributeCodes)) {
        $sku .= '-' . implode('-', $attributeCodes);
      }
    }

    return $sku;
  }

  /**
   * Kiểm tra và tạo SKU tự động
   */
  public function generateAutoSKU($productId, $baseSku = null) {
    if (empty($baseSku)) {
      $product = $this->db_query("SELECT sku FROM products WHERE id = '" . $this->db_escape($productId) . "'");
      $productRow = $this->db_fetch($product);
      $baseSku = $productRow ? $productRow['sku'] : 'PROD';
    }

    $counter = 1;
    $sku = $baseSku . '-V' . str_pad($counter, 3, '0', STR_PAD_LEFT);

    while ($this->skuExists($sku)) {
      $counter++;
      $sku = $baseSku . '-V' . str_pad($counter, 3, '0', STR_PAD_LEFT);
    }

    return $sku;
  }

  /**
   * Lấy variants có thể kết hợp từ các attributes
   */
  public function getPossibleVariants($productId, $attributeValues) {
    // Đây là logic phức tạp để tìm tất cả các kết hợp có thể
    // Trả về mảng các variant có thể tạo từ attribute values
    $possibleVariants = [];

    if (empty($attributeValues)) {
      return $possibleVariants;
    }

    // Lấy tất cả variants hiện tại
    $existingVariants = $this->getWithAttributes($productId);

    // Tạo tất cả các kết hợp có thể từ attribute values
    $attributeIds = array_keys($attributeValues);
    $combinations = $this->generateAttributeCombinations($attributeValues);

    foreach ($combinations as $combination) {
      $exists = false;

      // Kiểm tra xem combination này đã tồn tại chưa
      foreach ($existingVariants as $variant) {
        if ($this->compareAttributes($variant['attributes'], $combination)) {
          $exists = true;
          break;
        }
      }

      if (!$exists) {
        $possibleVariants[] = [
          'attributes' => $combination,
          'sku' => $this->generateSKUFromAttributes('TEMP', $combination)
        ];
      }
    }

    return $possibleVariants;
  }

  /**
   * So sánh attributes
   */
  private function compareAttributes($variantAttributes, $combination) {
    if (count($variantAttributes) !== count($combination)) {
      return false;
    }

    foreach ($variantAttributes as $attr) {
      $found = false;
      foreach ($combination as $comboAttrId => $comboValueId) {
        if ($attr['attribute_id'] == $comboAttrId && $attr['value_id'] == $comboValueId) {
          $found = true;
          break;
        }
      }
      if (!$found) {
        return false;
      }
    }

    return true;
  }

  /**
   * Tạo tất cả các kết hợp có thể từ attribute values
   */
  private function generateAttributeCombinations($attributeValues) {
    $combinations = [];
    $attributeIds = array_keys($attributeValues);

    // Bắt đầu với combination rỗng
    $combinations[] = [];

    foreach ($attributeIds as $attributeId) {
      $newCombinations = [];
      $values = $attributeValues[$attributeId];

      foreach ($combinations as $combination) {
        foreach ($values as $valueId) {
          $newCombination = $combination;
          $newCombination[$attributeId] = $valueId;
          $newCombinations[] = $newCombination;
        }
      }

      $combinations = $newCombinations;
    }

    return $combinations;
  }

  /**
   * Lấy attributes summary cho sản phẩm
   */
  public function getAttributesSummary($productId) {
    $productId = $this->db_escape($productId);

    $query = "SELECT
                a.id as attribute_id,
                a.name as attribute_name,
                a.type as attribute_type,
                COUNT(DISTINCT va.value_id) as distinct_values,
                COUNT(DISTINCT v.id) as variants_count
              FROM variant_attributes va
              INNER JOIN product_variants v ON va.variant_id = v.id
              INNER JOIN product_attributes a ON va.attribute_id = a.id
              WHERE v.product_id = '$productId'
              GROUP BY a.id
              ORDER BY a.sort_order";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Kiểm tra variant có đầy đủ attributes không
   */
  public function validateVariantAttributes($productId, $variantId, $requiredAttributes = []) {
    $variant = $this->findById($variantId);
    if (!$variant || $variant['product_id'] != $productId) {
      return false;
    }

    $variantAttributes = (new VariantAttribute())->getByVariantId($variantId);

    if (empty($requiredAttributes)) {
      // Lấy tất cả attributes của sản phẩm
      $productAttributes = $this->getAvailableAttributesForProduct($productId);
      $requiredAttributes = array_column($productAttributes, 'attribute_id');
    }

    $variantAttributeIds = array_column($variantAttributes, 'attribute_id');

    foreach ($requiredAttributes as $requiredAttrId) {
      if (!in_array($requiredAttrId, $variantAttributeIds)) {
        return false;
      }
    }

    return true;
  }

  /**
   * Xóa tất cả variants và attributes của sản phẩm
   */
  public function deleteAllProductVariants($productId) {
    $productId = $this->db_escape($productId);

    // Xóa variant attributes trước
    $query = "DELETE va FROM variant_attributes va
              INNER JOIN product_variants v ON va.variant_id = v.id
              WHERE v.product_id = '$productId'";
    $this->db_query($query);

    // Xóa variants
    $query = "DELETE FROM {$this->table} WHERE product_id = '$productId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Sao chép variant từ variant khác
   */
  public function duplicateVariant($sourceVariantId, $newSku = null) {
    $sourceVariant = $this->findById($sourceVariantId);
    if (!$sourceVariant) {
      return false;
    }

    // Tạo SKU mới
    if (empty($newSku)) {
      $newSku = $this->generateAutoSKU($sourceVariant['product_id'], $sourceVariant['sku']);
    }

    // Tạo variant mới
    $newVariantData = [
      'product_id' => $sourceVariant['product_id'],
      'sku' => $newSku,
      'price' => $sourceVariant['price'],
      'sale_price' => $sourceVariant['sale_price'],
      'stock_quantity' => $sourceVariant['stock_quantity'],
      'weight' => $sourceVariant['weight'],
      'image_id' => $sourceVariant['image_id'],
      'is_default' => 0, // Không đặt làm mặc định khi sao chép
      'created_at' => date('Y-m-d H:i:s')
    ];

    if ($this->create($newVariantData)) {
      $newVariantId = $this->db_insert_id();

      // Sao chép attributes
      $variantAttributes = (new VariantAttribute())->getByVariantId($sourceVariantId);
      foreach ($variantAttributes as $attr) {
        (new VariantAttribute())->addAttributeToVariant(
          $newVariantId,
          $attr['attribute_id'],
          $attr['value_id']
        );
      }

      return $newVariantId;
    }

    return false;
  }

  /**
   * Lấy variants theo filter
   */
  public function getFilteredVariants($filters = []) {
    $where = [];
    $params = [];

    if (!empty($filters['product_id'])) {
      $where[] = "v.product_id = '" . $this->db_escape($filters['product_id']) . "'";
    }

    if (!empty($filters['sku'])) {
      $where[] = "v.sku LIKE '%" . $this->db_escape($filters['sku']) . "%'";
    }

    if (isset($filters['is_default'])) {
      $where[] = "v.is_default = " . ($filters['is_default'] ? 1 : 0);
    }

    if (isset($filters['min_price'])) {
      $where[] = "v.price >= " . (float)$filters['min_price'];
    }

    if (isset($filters['max_price'])) {
      $where[] = "v.price <= " . (float)$filters['max_price'];
    }

    if (isset($filters['in_stock'])) {
      if ($filters['in_stock']) {
        $where[] = "v.stock_quantity > 0";
      } else {
        $where[] = "v.stock_quantity <= 0";
      }
    }

    if (!empty($filters['attribute_id']) && !empty($filters['value_id'])) {
      $where[] = "EXISTS (
        SELECT 1 FROM variant_attributes va
        WHERE va.variant_id = v.id
        AND va.attribute_id = '" . $this->db_escape($filters['attribute_id']) . "'
        AND va.value_id = '" . $this->db_escape($filters['value_id']) . "'
      )";
    }

    $whereStr = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $query = "SELECT v.*, p.name_pr as product_name
              FROM {$this->table} v
              LEFT JOIN products p ON v.product_id = p.id
              $whereStr
              ORDER BY p.name_pr, v.sku";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Export variants data
   */
  public function exportVariants($productId = null) {
    $where = $productId ? "WHERE v.product_id = '" . $this->db_escape($productId) . "'" : "";

    $query = "SELECT
                v.sku,
                p.name_pr as product_name,
                v.price,
                v.sale_price,
                v.stock_quantity,
                v.weight,
                v.is_default,
                v.created_at,
                GROUP_CONCAT(CONCAT(a.name, ': ', av.value) SEPARATOR '; ') as attributes
              FROM {$this->table} v
              LEFT JOIN products p ON v.product_id = p.id
              LEFT JOIN variant_attributes va ON v.id = va.variant_id
              LEFT JOIN product_attributes a ON va.attribute_id = a.id
              LEFT JOIN attribute_values av ON va.value_id = av.id
              $where
              GROUP BY v.id
              ORDER BY p.name_pr, v.sku";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }




//  public function getVariantsWithAttributes($product_id) {
//    $product_id = $this->db_escape($product_id);
//
//    $query = "SELECT
//                    pv.*,
//                    GROUP_CONCAT(
//                        DISTINCT CONCAT(
//                            '{\"attribute_id\":\"', pa.id,
//                            '\",\"attribute_name\":\"', pa.name,
//                            '\",\"attribute_type\":\"', pa.type,
//                            '\",\"value_id\":\"', av.id,
//                            '\",\"value\":\"', av.value,
//                            '\",\"color_code\":\"', IFNULL(av.color_code, ''),
//                            '\"}'
//                        ) SEPARATOR '|'
//                    ) as attributes_json
//                  FROM {$this->table} pv
//                  LEFT JOIN variant_attributes va ON pv.id = va.variant_id
//                  LEFT JOIN attribute_values av ON va.value_id = av.id
//                  LEFT JOIN product_attributes pa ON av.attribute_id = pa.id
//                  WHERE pv.product_id = '$product_id'
//                  GROUP BY pv.id
//                  ORDER BY pv.is_default DESC, pv.id ASC";
//
//    $result = $this->db_query($query);
//    $variants = $this->db_fetch_all($result);
//
//    // Parse JSON attributes
//    foreach ($variants as &$variant) {
//      if (!empty($variant['attributes_json'])) {
//        $attributes = [];
//        $pairs = explode('|', $variant['attributes_json']);
//        foreach ($pairs as $pair) {
//          if (!empty($pair)) {
//            $attr = json_decode($pair, true);
//            if ($attr) {
//              $attributes[] = $attr;
//            }
//          }
//        }
//        $variant['attributes'] = $attributes;
//        unset($variant['attributes_json']);
//      } else {
//        $variant['attributes'] = [];
//      }
//    }
//
//    return $variants;
//  }

  // Lấy thông tin chi tiết của một biến thể
  public function getVariantDetails($variant_id) {
    $query = "SELECT pv.*, pi.image_url
                  FROM product_variants pv
                  LEFT JOIN product_images pi ON pv.image_id = pi.id
                  WHERE pv.id = " . (int)$variant_id;

    $result = $this->db_query($query);
    $variant = $this->db_fetch($result);

    if ($variant) {
      $variantAttributeModel = new VariantAttribute();
      $variant['attributes'] = $variantAttributeModel->getByVariantId($variant_id);
    }

    return $variant;
  }

  // Lấy biến thể mặc định của sản phẩm
  public function getDefaultVariant($product_id) {
    $query = "SELECT * FROM product_variants
                  WHERE product_id = " . (int)$product_id . "
                  AND is_default = 1
                  LIMIT 1";

    $result = $this->db_query($query);
    $variant = $this->db_fetch($result);

    if (!$variant) {
      // Nếu không có variant mặc định, lấy variant đầu tiên
      $query = "SELECT * FROM product_variants
                      WHERE product_id = " . (int)$product_id . "
                      ORDER BY id ASC
                      LIMIT 1";

      $result = $this->db_query($query);
      $variant = $this->db_fetch($result);
    }

    if ($variant) {
      $variantAttributeModel = new VariantAttribute();
      $variant['attributes'] = $variantAttributeModel->getByVariantId($variant['id']);
    }

    return $variant;
  }

  // Lấy variant theo ID
  public function getById($id) {
    return $this->db_select('product_variants', "id = " . (int)$id);
  }


}
?>
