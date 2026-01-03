<?php
require_once 'db.php';

class Product extends DB {
  protected $table = 'products';

  public function __construct() {
    parent::__construct();
  }

  /**
   * Tạo SKU tự động
   */
  public function generateSKU() {
    $prefix = 'PRD';
    $timestamp = time();
    $random = mt_rand(1000, 9999);
    return $prefix . $timestamp . $random;
  }

  /**
   * Tạo slug từ tên sản phẩm
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

  public function getTotalProducts() {
    $query = "SELECT COUNT(*) as total FROM {$this->table}";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return $row['total'] ?? 0;
  }

  /**
   * Kiểm tra slug đã tồn tại chưa
   */
  private function slugExists($slug) {
    $slug = $this->db_escape($slug);
    $query = "SELECT id FROM {$this->table} WHERE slug = '$slug'";
    $result = $this->db_query($query);
    return $this->db_fetch($result) !== false;
  }

  /**
   * Tạo mới sản phẩm
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
   * Cập nhật sản phẩm
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
   * Xóa sản phẩm
   */
  public function delete($id) {
    $id = $this->db_escape($id);
    $query = "DELETE FROM {$this->table} WHERE id = '$id'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy tất cả sản phẩm
   */
  public function getAll($onlyActive = true) {
    $where = $onlyActive ? "WHERE status = 'published'" : "";
    $query = "SELECT p.*,
                     c.name as category_name,
                     b.name as brand_name
              FROM {$this->table} p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN brands b ON p.brand_id = b.id
              $where
              ORDER BY p.created_at DESC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy sản phẩm theo ID
   */
  public function findById($id) {
    $id = $this->db_escape($id);
    $query = "SELECT p.*,
                     c.name as category_name,
                     b.name as brand_name
              FROM {$this->table} p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN brands b ON p.brand_id = b.id
              WHERE p.id = '$id'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy sản phẩm theo slug
   */
  public function findBySlug($slug) {
    $slug = $this->db_escape($slug);
    $query = "SELECT p.*,
                     c.name as category_name,
                     b.name as brand_name
              FROM {$this->table} p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN brands b ON p.brand_id = b.id
              WHERE p.slug = '$slug' AND p.status = 'published'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  //3 hàm của wishlist
  public function checkIsWishList($userId, $productId) {
    $userId = $this->db_escape($userId);
    $productId = $this->db_escape($productId);

    $query = "
        SELECT id FROM wishlists
        WHERE user_id = '$userId' AND product_id = '$productId'
        LIMIT 1
    ";

    $result = $this->db_query($query);

    return $result && $result->num_rows > 0; // true nếu có, false nếu không
  }

  public function toggleWishList($userId, $productId) {
    $userId = $this->db_escape($userId);
    $productId = $this->db_escape($productId);

    // Kiểm tra đã có chưa
    if ($this->checkIsWishList($userId, $productId)) {
      $this->removeFromWishList($userId, $productId);
      return "remove"; // Đã tồn tại → không thêm lại
    }

    $query = "
        INSERT INTO wishlists (user_id, product_id, created_at)
        VALUES ('$userId', '$productId', NOW())
    ";

    $this->db_query($query);
    return "add";
  }

  public function removeFromWishList($userId, $productId) {
    $userId = $this->db_escape($userId);
    $productId = $this->db_escape($productId);

    $query = "
        DELETE FROM wishlists
        WHERE user_id = '$userId' AND product_id = '$productId'
        LIMIT 1
    ";

    return $this->db_query($query) !== false; // true nếu xoá thành công
  }

  public function getWishList($user_id): array {
    $sql = "SELECT * FROM wishlists WHERE user_id = {$user_id}";
    $a = $this->db_query($sql);
    return $this->db_fetch_all($a);
  }

  public function countWishlist($user_id): int {
    $user_id = (int)$user_id;

    $sql = "SELECT COUNT(*) AS total
          FROM wishlists
          WHERE user_id = $user_id";

    $res = $this->db_query($sql);
    $row = $this->db_fetch($res);

    return (int)($row['total'] ?? 0);
  }

  public function getNameAndImageProductById($product_id) {
    $product_id = (int)$product_id;

    $sql = "
    SELECT
      p.name_pr,
      p.sale_price,
      pi.image_url,
      pi.alt_text
    FROM products p
    LEFT JOIN product_images pi
      ON p.id = pi.product_id
      AND pi.is_main = 1
    WHERE p.id = $product_id
    LIMIT 1
  ";

    $res = $this->db_query($sql);
    return $this->db_fetch($res);
  }



  /**
   * Cập nhật số lượt xem
   */
  public function incrementViewCount($id) {
    $id = $this->db_escape($id);
    $query = "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = '$id'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy sản phẩm nổi bật
   */
  public function getFeatured($limit = 10) {
    $limit = (int)$limit;
    $query = "SELECT p.*,
                     c.name as category_name,
                     b.name as brand_name
              FROM {$this->table} p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN brands b ON p.brand_id = b.id
              WHERE p.featured = 1 AND p.status = 'published'
              ORDER BY p.created_at DESC
              LIMIT $limit";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy sản phẩm theo danh mục
   */
  public function getByCategory($categoryId, $limit = null) {
    $categoryId = $this->db_escape($categoryId);
    $limitClause = $limit ? "LIMIT " . (int)$limit : "";

    $query = "SELECT p.*,
                     c.name as category_name,
                     b.name as brand_name
              FROM {$this->table} p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN brands b ON p.brand_id = b.id
              WHERE p.category_id = '$categoryId' AND p.status = 'published'
              ORDER BY p.created_at DESC
              $limitClause";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy sản phẩm theo thương hiệu
   */
  public function getByBrand($brandId, $limit = null) {
    $brandId = $this->db_escape($brandId);
    $limitClause = $limit ? "LIMIT " . (int)$limit : "";

    $query = "SELECT p.*,
                     c.name as category_name,
                     b.name as brand_name
              FROM {$this->table} p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN brands b ON p.brand_id = b.id
              WHERE p.brand_id = '$brandId' AND p.status = 'published'
              ORDER BY p.created_at DESC
              $limitClause";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tìm kiếm sản phẩm
   */
  public function search($keyword, $limit = null) {
    $keyword = $this->db_escape($keyword);
    $limitClause = $limit ? "LIMIT " . (int)$limit : "";

    $query = "SELECT p.*,
                     c.name as category_name,
                     b.name as brand_name
              FROM {$this->table} p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN brands b ON p.brand_id = b.id
              WHERE (p.name_pr LIKE '%$keyword%' OR p.description LIKE '%$keyword%')
                AND p.status = 'published'
              ORDER BY p.created_at DESC
              $limitClause";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  public function getBySearch($text) {
    $text = $this->db_escape($text);

    $sql = "

    -- PRODUCT
    SELECT
      p.id,
      p.name_pr AS title,
      'product' AS type,
      p.sale_price,
      (
        SELECT image_url
        FROM product_images
        WHERE product_id = p.id
        ORDER BY is_main DESC, sort_order ASC
        LIMIT 1
      ) AS image
    FROM products p
    WHERE p.name_pr LIKE '%$text%'

    UNION ALL

    -- BRAND
    SELECT
      b.id,
      b.name AS title,
      'brand' AS type,
      NULL AS price,
      NULL AS image
    FROM brands b
    WHERE b.name LIKE '%$text%'

    UNION ALL

    -- CATEGORY
    SELECT
      c.id,
      c.name AS title,
      'category' AS type,
      NULL AS price,
      NULL AS image
    FROM categories c
    WHERE c.name LIKE '%$text%'

    LIMIT 30
  ";

    $res = $this->db_query($sql);
    return $this->db_fetch_all($res);
  }



  /**
   * Lấy số lượng sản phẩm theo trạng thái
   */
  public function countByStatus($status = null) {
    $where = $status ? "WHERE status = '" . $this->db_escape($status) . "'" : "";
    $query = "SELECT COUNT(*) as total FROM {$this->table} $where";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return isset($row['total']) ? $row['total'] : 0;
  }

  /**
   * Cập nhật giá sale và phần trăm giảm giá
   */
  public function updateSalePrice($id, $salePrice) {
    $product = $this->findById($id);
    if (!$product) return false;

    $regularPrice = $product['regular_price'];
    $percentReduce = 0;

    if ($salePrice > 0 && $regularPrice > 0) {
      $percentReduce = round((($regularPrice - $salePrice) / $regularPrice) * 100);
    }

    return $this->update($id, [
      'sale_price' => $salePrice,
      'percent_reduce' => $percentReduce
    ]);
  }

  // ========== CÁC PHƯƠNG THỨC MỚI BỔ SUNG ==========

  /**
   * Lấy sản phẩm liên quan (cùng danh mục)
   */
  public function getRelatedProducts($productId, $limit = 6) {
    $product = $this->findById($productId);
    if (!$product) return [];

    $categoryId = $product['category_id'];
    $limit = (int)$limit;

    $query = "SELECT p.*,
                     c.name as category_name,
                     b.name as brand_name
              FROM {$this->table} p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN brands b ON p.brand_id = b.id
              WHERE p.category_id = '$categoryId'
                AND p.id != '$productId'
                AND p.status = 'published'
              ORDER BY p.created_at DESC
              LIMIT $limit";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy sản phẩm mới nhất
   */
  public function getNewestProducts($limit = 10) {
    $limit = (int)$limit;
    $query = "SELECT p.*,
                     c.name as category_name,
                     b.name as brand_name
              FROM {$this->table} p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN brands b ON p.brand_id = b.id
              WHERE p.status = 'published'
              ORDER BY p.created_at DESC
              LIMIT $limit";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy sản phẩm bán chạy (dựa trên đơn hàng)
   */
  public function getBestSellers($limit = 10) {
    $limit = (int)$limit;
    $query = "SELECT p.*,
                     c.name as category_name,
                     b.name as brand_name,
                     SUM(oi.quantity) as total_sold
              FROM {$this->table} p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN brands b ON p.brand_id = b.id
              LEFT JOIN order_items oi ON p.id = oi.product_id
              WHERE p.status = 'published' AND p.num_buy > 0
              GROUP BY p.id
              ORDER BY p.num_buy DESC
              LIMIT $limit";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy sản phẩm đang giảm giá
   */
  public function getOnSaleProducts($limit = 10) {
    $limit = (int)$limit;
    $query = "SELECT p.*,
                     c.name as category_name,
                     b.name as brand_name
              FROM {$this->table} p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN brands b ON p.brand_id = b.id
              WHERE p.sale_price > 0
                AND p.status = 'published'
              ORDER BY p.percent_reduce DESC
              LIMIT $limit";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Cập nhật trạng thái tồn kho
   */
  public function updateStockStatus($id) {
    $product = $this->findById($id);
    if (!$product) return false;

    $stockQuantity = $product['stock_quantity'];
    $stockStatus = 'in_stock';

    if ($stockQuantity <= 0) {
      $stockStatus = 'out_of_stock';
    }

    return $this->update($id, ['stock_status' => $stockStatus]);
  }

  /**
   * Cập nhật số lượng tồn kho
   */
  public function updateStockQuantity($id, $quantity) {
    $result = $this->update($id, ['stock_quantity' => $quantity]);
    if ($result) {
      $this->updateStockStatus($id);
    }
    return $result;
  }

  /**
   * Giảm số lượng tồn kho khi đặt hàng
   */
  public function decreaseStock($id, $quantity) {
    $product = $this->findById($id);
    if (!$product) return false;

    $newQuantity = $product['stock_quantity'] - $quantity;
    if ($newQuantity < 0) $newQuantity = 0;

    return $this->updateStockQuantity($id, $newQuantity);
  }

  /**
   * Tăng số lượng tồn kho
   */
  public function increaseStock($id, $quantity) {
    $product = $this->findById($id);
    if (!$product) return false;

    $newQuantity = $product['stock_quantity'] + $quantity;
    return $this->updateStockQuantity($id, $newQuantity);
  }

  /**
   * Lấy thống kê sản phẩm
   */
  public function getStats() {
    $query = "SELECT
                COUNT(*) as total_products,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_products,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_products,
                SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived_products,
                SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END) as featured_products,
                SUM(CASE WHEN sale_price > 0 THEN 1 ELSE 0 END) as on_sale_products,
                SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock_products
              FROM {$this->table}";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lọc sản phẩm theo nhiều tiêu chí
   */
  public function filterProducts($filters = []) {
    $whereConditions = ["p.status = 'published'"];
    $orderBy = "p.created_at DESC";
    $limit = "";

    // Lọc theo danh mục
    if (!empty($filters['category_id'])) {
      $categoryId = $this->db_escape($filters['category_id']);
      $whereConditions[] = "p.category_id = '$categoryId'";
    }

    // Lọc theo thương hiệu
    if (!empty($filters['brand_id'])) {
      $brandId = $this->db_escape($filters['brand_id']);
      $whereConditions[] = "p.brand_id = '$brandId'";
    }

    // Lọc theo khoảng giá
    if (!empty($filters['min_price'])) {
      $minPrice = $this->db_escape($filters['min_price']);
      $whereConditions[] = "(p.sale_price > 0 AND p.sale_price >= $minPrice) OR (p.sale_price = 0 AND p.regular_price >= $minPrice)";
    }

    if (!empty($filters['max_price'])) {
      $maxPrice = $this->db_escape($filters['max_price']);
      $whereConditions[] = "(p.sale_price > 0 AND p.sale_price <= $maxPrice) OR (p.sale_price = 0 AND p.regular_price <= $maxPrice)";
    }

    // Lọc theo trạng thái tồn kho
    if (!empty($filters['stock_status'])) {
      $stockStatus = $this->db_escape($filters['stock_status']);
      $whereConditions[] = "p.stock_status = '$stockStatus'";
    }

    // Lọc sản phẩm nổi bật
    if (isset($filters['featured']) && $filters['featured'] !== '') {
      $featured = $this->db_escape($filters['featured']);
      $whereConditions[] = "p.featured = '$featured'";
    }

    // Lọc sản phẩm giảm giá
    if (isset($filters['on_sale']) && $filters['on_sale']) {
      $whereConditions[] = "p.sale_price > 0";
    }

    // Sắp xếp
    if (!empty($filters['sort_by'])) {
      switch ($filters['sort_by']) {
        case 'price_asc':
          $orderBy = "(CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.regular_price END) ASC";
          break;
        case 'price_desc':
          $orderBy = "(CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.regular_price END) DESC";
          break;
        case 'name_asc':
          $orderBy = "p.name_pr ASC";
          break;
        case 'name_desc':
          $orderBy = "p.name_pr DESC";
          break;
        case 'newest':
          $orderBy = "p.created_at DESC";
          break;
        case 'oldest':
          $orderBy = "p.created_at ASC";
          break;
        case 'popular':
          $orderBy = "p.view_count DESC";
          break;
      }
    }

    // Giới hạn
    if (!empty($filters['limit'])) {
      $limit = "LIMIT " . (int)$filters['limit'];
    }

    $whereStr = implode(" AND ", $whereConditions);

    $query = "SELECT p.*,
                     c.name as category_name,
                     b.name as brand_name
              FROM {$this->table} p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN brands b ON p.brand_id = b.id
              WHERE $whereStr
              ORDER BY $orderBy
              $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy sản phẩm cho trang chủ (tổng hợp)
   */
  public function getHomepageProducts() {
    return [
      'featured' => $this->getFeatured(8),
      'newest' => $this->getNewestProducts(8),
      'on_sale' => $this->getOnSaleProducts(8),
      'best_sellers' => $this->getBestSellers(8)
    ];
  }
}
?>
