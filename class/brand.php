<?php
require_once 'db.php';

class Brand extends DB {
  protected $table = 'brands';
  private $uploadPath = 'img/adminUP/brands/';

  public function __construct() {
    parent::__construct();
    // Tạo thư mục upload nếu chưa tồn tại
    if (!file_exists($this->uploadPath)) {
      mkdir($this->uploadPath, 0777, true);
    }
  }

  /**
   * Upload ảnh
   */
  public function uploadImage($file) {
    $result = [
      'success' => false,
      'file_name' => '',
      'error' => ''
    ];

    // Kiểm tra file
    if ($file['error'] !== UPLOAD_ERR_OK) {
      $result['error'] = 'Lỗi upload file: ' . $file['error'];
      return $result;
    }

    // Kiểm tra kích thước file (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
      $result['error'] = 'File quá lớn (tối đa 2MB)';
      return $result;
    }

    // Kiểm tra loại file bằng extension thay vì mime_content_type
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions)) {
      $result['error'] = 'Chỉ chấp nhận file ảnh (JPG, JPEG, PNG, GIF, WebP)';
      return $result;
    }

    // Tạo tên file mới
    $fileName = uniqid() . '_' . time() . '.' . $extension;
    $filePath = $this->uploadPath . $fileName;

    // Di chuyển file
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
      $result['success'] = true;
      $result['file_name'] = $fileName;
    } else {
      $result['error'] = 'Không thể lưu file';
    }

    return $result;
  }

  /**
   * Xóa ảnh
   */
  public function deleteImage($fileName) {
    $filePath = $this->uploadPath . $fileName;
    if (file_exists($filePath) && is_file($filePath)) {
      return unlink($filePath);
    }
    return false;
  }

  /**
   * Lấy đường dẫn ảnh
   */
  public function getImagePath($fileName) {
    if (empty($fileName)) {
      return null;
    }
    return $this->uploadPath . $fileName;
  }

  /**
   * Tạo mới brand với xử lý ảnh
   */
  public function createWithImage($data, $imageFile = null) {
    // Xử lý upload ảnh nếu có
    if ($imageFile && $imageFile['error'] === 0) {
      $uploadResult = $this->uploadImage($imageFile);
      if ($uploadResult['success']) {
        $data['logo'] = $uploadResult['file_name'];
      } else {
        throw new Exception("Lỗi upload ảnh: " . $uploadResult['error']);
      }
    }

    return $this->create($data);
  }

  /**
   * Cập nhật brand với xử lý ảnh
   */
  public function updateWithImage($id, $data, $imageFile = null) {
    // Xử lý upload ảnh nếu có
    if ($imageFile && $imageFile['error'] === 0) {
      $uploadResult = $this->uploadImage($imageFile);
      if ($uploadResult['success']) {
        $data['logo'] = $uploadResult['file_name'];

        // Xóa ảnh cũ nếu có
        $oldBrand = $this->findById($id);
        if ($oldBrand && $oldBrand['logo']) {
          $this->deleteImage($oldBrand['logo']);
        }
      } else {
        throw new Exception("Lỗi upload ảnh: " . $uploadResult['error']);
      }
    }

    return $this->update($id, $data);
  }

  /**
   * Xóa brand và ảnh
   */
  public function deleteWithImage($id) {
    $brand = $this->findById($id);
    if ($brand) {
      // Xóa ảnh nếu có
      if ($brand['logo']) {
        $this->deleteImage($brand['logo']);
      }
      return $this->delete($id);
    }
    return false;
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
   * Lấy tất cả brands
   */
  public function getAll($onlyActive = true) {
    $where = $onlyActive ? "WHERE is_active = 1" : "";
    $query = "SELECT * FROM {$this->table} $where ORDER BY name ASC"; // Bỏ sort_order
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy brand theo ID
   */
  public function findById($id) {
    $id = $this->db_escape($id);
    $query = "SELECT * FROM {$this->table} WHERE id = '$id'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy brand theo slug
   */
  public function findBySlug($slug) {
    $slug = $this->db_escape($slug);
    $query = "SELECT * FROM {$this->table} WHERE slug = '$slug' AND is_active = 1";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Đếm số sản phẩm trong brand
   */
  public function countProducts($brandId) {
    $brandId = $this->db_escape($brandId);
    $query = "SELECT COUNT(*) as total FROM products WHERE brand_id = '$brandId' AND status = 'published'";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return isset($row['total']) ? $row['total'] : 0;
  }

  /**
   * Tạo slug từ tên brand
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
