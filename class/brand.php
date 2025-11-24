<?php
require_once 'db.php';

class Brand extends DB {
  protected $table = 'brands';

  public function __construct() {
    parent::__construct();
  }

  /**
   * Tạo mới
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
   * Cập nhật
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
   * Xóa
   */
  public function delete($id) {
    $id = $this->db_escape($id);
    $query = "DELETE FROM {$this->table} WHERE id = '$id'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy tất cả thương hiệu
   */
  public function getAll($onlyActive = true) {
    $where = $onlyActive ? "WHERE is_active = 1" : "";
    $query = "SELECT * FROM {$this->table} $where ORDER BY name ASC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy thương hiệu theo ID
   */
  public function findById($id) {
    $id = $this->db_escape($id);
    $query = "SELECT * FROM {$this->table} WHERE id = '$id'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy thương hiệu theo slug
   */
  public function findBySlug($slug) {
    $slug = $this->db_escape($slug);
    $query = "SELECT * FROM {$this->table} WHERE slug = '$slug' AND is_active = 1";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Đếm số sản phẩm theo thương hiệu
   */
  public function countProducts($brandId) {
    $brandId = $this->db_escape($brandId);
    $query = "SELECT COUNT(*) as total FROM products WHERE brand_id = '$brandId' AND status = 'published'";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return isset($row['total']) ? $row['total'] : 0;
  }

  /**
   * Lấy sản phẩm theo thương hiệu
   */
  public function getProducts($brandId, $limit = 20) {
    $brandId = $this->db_escape($brandId);
    $limit = (int)$limit;

    $query = "SELECT p.*, pi.image_url as main_image
                  FROM products p
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                  WHERE p.brand_id = '$brandId' AND p.status = 'published'
                  ORDER BY p.featured DESC, p.created_at DESC
                  LIMIT $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tạo slug từ tên thương hiệu
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

  /**
   * Lấy thương hiệu nổi bật (có nhiều sản phẩm nhất)
   */
  public function getFeaturedBrands($limit = 10) {
    $limit = (int)$limit;

    $query = "SELECT b.*, COUNT(p.id) as product_count
                  FROM {$this->table} b
                  LEFT JOIN products p ON b.id = p.brand_id AND p.status = 'published'
                  WHERE b.is_active = 1
                  GROUP BY b.id
                  ORDER BY product_count DESC, b.name ASC
                  LIMIT $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Cập nhật logo thương hiệu
   */
  public function updateLogo($brandId, $logoUrl) {
    $brandId = $this->db_escape($brandId);
    $logoUrl = $this->db_escape($logoUrl);

    $query = "UPDATE {$this->table} SET logo = '$logoUrl' WHERE id = '$brandId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Đếm tổng số thương hiệu
   */
  public function count($onlyActive = true) {
    $where = $onlyActive ? "WHERE is_active = 1" : "";
    $query = "SELECT COUNT(*) as total FROM {$this->table} $where";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return isset($row['total']) ? $row['total'] : 0;
  }
}
?>
