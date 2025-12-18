<?php
require_once 'db.php';

class Category extends DB {
  protected $table = 'categories';
  private $uploadPath = 'img/adminUP/categorys';

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

    // Kiểm tra loại file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
      $result['error'] = 'Chỉ chấp nhận file ảnh (JPEG, PNG, GIF, WebP)';
      return $result;
    }

    // Tạo tên file mới
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
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
   * Tạo mới category với xử lý ảnh
   */
  public function createWithImage($data, $imageFile = null) {
    // Xử lý upload ảnh nếu có
    if ($imageFile && $imageFile['error'] === 0) {
      $uploadResult = $this->uploadImage($imageFile);
      if ($uploadResult['success']) {
        $data['image'] = $uploadResult['file_name'];
      } else {
        throw new Exception("Lỗi upload ảnh: " . $uploadResult['error']);
      }
    }

    return $this->create($data);
  }

  /**
   * Cập nhật category với xử lý ảnh
   */
  public function updateWithImage($id, $data, $imageFile = null) {
    // Xử lý upload ảnh nếu có
    if ($imageFile && $imageFile['error'] === 0) {
      $uploadResult = $this->uploadImage($imageFile);
      if ($uploadResult['success']) {
        $data['image'] = $uploadResult['file_name'];

        // Xóa ảnh cũ nếu có
        $oldCategory = $this->findById($id);
        if ($oldCategory && $oldCategory['image']) {
          $this->deleteImage($oldCategory['image']);
        }
      } else {
        throw new Exception("Lỗi upload ảnh: " . $uploadResult['error']);
      }
    }

    return $this->update($id, $data);
  }

  /**
   * Xóa category và ảnh
   */
  public function deleteWithImage($id) {
    $category = $this->findById($id);
    if ($category) {
      // Xóa ảnh nếu có
      if ($category['image']) {
        $this->deleteImage($category['image']);
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
   * Lấy tất cả danh mục
   */
  public function getAll($onlyActive = true) {
    $where = $onlyActive ? "WHERE is_active = 1" : "";
    $query = "SELECT * FROM {$this->table} $where ORDER BY sort_order, name ASC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy danh mục theo parent_id
   */
  public function getByParent($parentId = null) {
    if ($parentId === null) {
      $query = "SELECT * FROM {$this->table} WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order, name ASC";
    } else {
      $parentId = $this->db_escape($parentId);
      $query = "SELECT * FROM {$this->table} WHERE parent_id = '$parentId' AND is_active = 1 ORDER BY sort_order, name ASC";
    }

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy danh mục có phân cấp
   */
//  public function getHierarchical() {
//    $categories = $this->getAll();
//    $hierarchical = [];
//
//    foreach ($categories as $category) {
//      if ($category['parent_id'] === null) {
//        $hierarchical[$category['id']] = $category;
//        $hierarchical[$category['id']]['children'] = [];
//      }
//    }
//
//    foreach ($categories as $category) {
//      if ($category['parent_id'] !== null && isset($hierarchical[$category['parent_id']])) {
//        $hierarchical[$category['parent_id']]['children'][] = $category;
//      }
//    }
//
//    return $hierarchical;
//  }

  public function getHierarchical()
  {
    $categories = $this->getAll();
    $byParent = [];

    // Gom danh mục theo parent_id
    foreach ($categories as $cat) {
      $byParent[$cat['parent_id']][] = $cat;
    }

    // Hàm đệ quy xây cây
    $buildTree = function($parentId) use (&$buildTree, &$byParent) {
      $branch = [];

      if (!isset($byParent[$parentId])) return $branch;

      foreach ($byParent[$parentId] as $cat) {
        $children = $buildTree($cat['id']);
        if (!empty($children)) {
          $cat['children'] = $children;
        }
        $branch[] = $cat;
      }
      return $branch;
    };

    return $buildTree(null);
  }


  /**
   * Lấy danh mục theo ID
   */
  public function findById($id) {
    $id = $this->db_escape($id);
    $query = "SELECT * FROM {$this->table} WHERE id = '$id'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy danh mục theo slug
   */
  public function findBySlug($slug) {
    $slug = $this->db_escape($slug);
    $query = "SELECT * FROM {$this->table} WHERE slug = '$slug' AND is_active = 1";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Đếm số sản phẩm trong danh mục
   */
  public function countProducts($categoryId) {
    $categoryId = $this->db_escape($categoryId);
    $query = "SELECT COUNT(*) as total FROM products WHERE category_id = '$categoryId' AND status = 'published'";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return isset($row['total']) ? $row['total'] : 0;
  }

  /**
   * Tạo slug từ tên danh mục
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
   * Lấy breadcrumb cho danh mục
   */
  public function getBreadcrumb($categoryId) {
    $breadcrumb = [];
    $current = $this->findById($categoryId);

    while ($current) {
      $breadcrumb[] = $current;
      if ($current['parent_id']) {
        $current = $this->findById($current['parent_id']);
      } else {
        $current = null;
      }
    }

    return array_reverse($breadcrumb);
  }
}
?>
