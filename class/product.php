<?php
require_once 'db.php';

class Product extends DB {
  protected $table = 'products';

  public function __construct() {
    parent::__construct();
  }

  /**
   * Lấy tất cả sản phẩm với thông tin chi tiết
   */
  public function getAllWithDetails($limit = 50, $offset = 0) {
    $limit = (int)$limit;
    $offset = (int)$offset;

    $query = "SELECT p.*,
                         c.name as category_name,
                         b.name as brand_name,
                         pi.image_url as main_image
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN brands b ON p.brand_id = b.id
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                  ORDER BY p.created_at DESC
                  LIMIT $limit OFFSET $offset";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tìm sản phẩm theo ID với đầy đủ thông tin
   */
  public function findById($id) {
    $id = $this->db_escape($id);
    $query = "SELECT p.*,
                         c.name as category_name,
                         c.id as category_id,
                         b.name as brand_name,
                         b.id as brand_id
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN brands b ON p.brand_id = b.id
                  WHERE p.id = '$id'";

    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy hình ảnh sản phẩm
   */
  public function getProductImages($productId) {
    $productId = $this->db_escape($productId);
    $query = "SELECT * FROM product_images
                  WHERE product_id = '$productId'
                  ORDER BY sort_order, is_main DESC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tìm sản phẩm theo SKU
   */
  public function findBySKU($sku) {
    $sku = $this->db_escape($sku);
    $query = "SELECT * FROM {$this->table} WHERE sku = '$sku'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy sản phẩm theo danh mục
   */
  public function getByCategory($categoryId, $limit = 20) {
    $categoryId = $this->db_escape($categoryId);
    $limit = (int)$limit;

    $query = "SELECT p.*, pi.image_url as main_image
                  FROM {$this->table} p
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                  WHERE p.category_id = '$categoryId' AND p.status = 'published'
                  ORDER BY p.featured DESC, p.created_at DESC
                  LIMIT $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy sản phẩm nổi bật
   */
  public function getFeaturedProducts($limit = 10) {
    $limit = (int)$limit;

    $query = "SELECT p.*, pi.image_url as main_image
                  FROM {$this->table} p
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                  WHERE p.featured = 1 AND p.status = 'published'
                  ORDER BY p.created_at DESC
                  LIMIT $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tìm kiếm sản phẩm
   */
  public function search($keyword, $limit = 20) {
    $keyword = $this->db_escape($keyword);
    $limit = (int)$limit;

    $query = "SELECT p.*, pi.image_url as main_image
                  FROM {$this->table} p
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                  WHERE (p.name_pr LIKE '%$keyword%' OR p.sku LIKE '%$keyword%' OR p.description LIKE '%$keyword%')
                  AND p.status = 'published'
                  ORDER BY p.featured DESC, p.created_at DESC
                  LIMIT $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Cập nhật số lượng tồn kho
   */
  public function updateStock($productId, $quantity) {
    $productId = $this->db_escape($productId);
    $quantity = (int)$quantity;

    $stockStatus = $quantity > 0 ? 'in_stock' : 'out_of_stock';

    $query = "UPDATE {$this->table}
                  SET stock_quantity = $quantity, stock_status = '$stockStatus'
                  WHERE id = '$productId'";

    return $this->db_query($query) !== false;
  }

  /**
   * Tăng view count
   */
  public function incrementViewCount($productId) {
    $productId = $this->db_escape($productId);
    $query = "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = '$productId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy sản phẩm đang giảm giá
   */
  public function getSaleProducts($limit = 10) {
    $limit = (int)$limit;

    $query = "SELECT p.*, pi.image_url as main_image
                  FROM {$this->table} p
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                  WHERE p.sale_price IS NOT NULL AND p.sale_price > 0 AND p.status = 'published'
                  ORDER BY p.percent_reduce DESC
                  LIMIT $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Đếm tổng số sản phẩm
   */
  public function count($conditions = "") {
    $where = $conditions ? "WHERE $conditions" : "";
    $query = "SELECT COUNT(*) as total FROM {$this->table} $where";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return isset($row['total']) ? $row['total'] : 0;
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

  /**
   * Kiểm tra slug đã tồn tại chưa
   */
  private function slugExists($slug) {
    $slug = $this->db_escape($slug);
    $query = "SELECT id FROM {$this->table} WHERE slug = '$slug'";
    $result = $this->db_query($query);
    return $this->db_fetch($result) !== false;
  }
}
?>
