<?php
require_once 'db.php';

class ProductImage extends DB {
  protected $table = 'product_images';
  private $uploadPath = 'img/adminUP/products/';

  public function __construct() {
    parent::__construct();
    // Tạo thư mục nếu chưa tồn tại
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

    if ($file['error'] !== UPLOAD_ERR_OK) {
      $result['error'] = 'Lỗi upload file: ' . $file['error'];
      return $result;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
      $result['error'] = 'File quá lớn (tối đa 2MB)';
      return $result;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions)) {
      $result['error'] = 'Chỉ chấp nhận file ảnh (JPG, JPEG, PNG, GIF, WebP)';
      return $result;
    }

    $fileName = uniqid() . '_' . time() . '.' . $extension;
    $filePath = $this->uploadPath . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
      $result['success'] = true;
      $result['file_name'] = $fileName;
    } else {
      $result['error'] = 'Không thể lưu file';
    }

    return $result;
  }

  /**
   * Xóa ảnh vật lý
   */
  public function deleteImageFile($fileName) {
    $filePath = $this->uploadPath . $fileName;
    if (file_exists($filePath) && is_file($filePath)) {
      return unlink($filePath);
    }
    return false;
  }

  /**
   * Tạo mới ảnh
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
   * Cập nhật ảnh
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
   * Xóa ảnh (cả database và file vật lý)
   */
  public function delete($id) {
    // Lấy thông tin ảnh trước khi xóa
    $image = $this->findById($id);
    if (!$image) return false;

    // Xóa trong database
    $id = $this->db_escape($id);
    $query = "DELETE FROM {$this->table} WHERE id = '$id'";
    $result = $this->db_query($query) !== false;

    // Nếu xóa database thành công, xóa file vật lý
    if ($result && !empty($image['image_url'])) {
      $this->deleteImageFile($image['image_url']);
    }

    return $result;
  }

  /**
   * Lấy ảnh theo ID
   */
  public function findById($id) {
    $id = $this->db_escape($id);
    $query = "SELECT * FROM {$this->table} WHERE id = '$id'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy ảnh theo product_id
   */
  public function getByProductId($productId) {
    $productId = $this->db_escape($productId);
    $query = "SELECT * FROM {$this->table} WHERE product_id = '$productId' ORDER BY sort_order, is_main DESC, id ASC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Đặt ảnh chính
   */
  public function setMainImage($imageId, $productId) {
    // Bỏ tất cả ảnh chính cũ
    $productId = $this->db_escape($productId);
    $query = "UPDATE {$this->table} SET is_main = 0 WHERE product_id = '$productId'";
    $this->db_query($query);

    // Đặt ảnh chính mới
    $imageId = $this->db_escape($imageId);
    $query = "UPDATE {$this->table} SET is_main = 1 WHERE id = '$imageId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy ảnh chính của sản phẩm
   */
  public function getMainImage($productId) {
    $productId = $this->db_escape($productId);
    $query = "SELECT * FROM {$this->table} WHERE product_id = '$productId' AND is_main = 1 LIMIT 1";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy số lượng ảnh của sản phẩm
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
   * Upload nhiều ảnh cùng lúc
   */
  public function uploadMultipleImages($files, $productId, $altText = '') {
    $results = [];

    foreach ($files['tmp_name'] as $key => $tmpName) {
      if ($files['error'][$key] === UPLOAD_ERR_OK) {
        $file = [
          'name' => $files['name'][$key],
          'type' => $files['type'][$key],
          'tmp_name' => $tmpName,
          'error' => $files['error'][$key],
          'size' => $files['size'][$key]
        ];

        $uploadResult = $this->uploadImage($file);

        if ($uploadResult['success']) {
          // Tạo bản ghi trong database
          $imageData = [
            'product_id' => $productId,
            'image_url' => $uploadResult['file_name'],
            'alt_text' => $altText,
            'sort_order' => $key,
            'is_main' => ($key === 0 && $this->countByProduct($productId) === 0) ? 1 : 0
          ];

          if ($this->create($imageData)) {
            $results[] = [
              'success' => true,
              'file_name' => $uploadResult['file_name'],
              'image_id' => $this->db_insert_id()
            ];
          } else {
            $results[] = [
              'success' => false,
              'error' => 'Không thể lưu thông tin ảnh vào database'
            ];
            // Xóa file đã upload nếu không lưu được database
            $this->deleteImageFile($uploadResult['file_name']);
          }
        } else {
          $results[] = [
            'success' => false,
            'error' => $uploadResult['error']
          ];
        }
      } else {
        $results[] = [
          'success' => false,
          'error' => 'Lỗi upload file: ' . $files['error'][$key]
        ];
      }
    }

    return $results;
  }

  /**
   * Xóa tất cả ảnh của sản phẩm
   */
  public function deleteAllByProduct($productId) {
    $images = $this->getByProductId($productId);
    $success = true;

    foreach ($images as $image) {
      if (!$this->delete($image['id'])) {
        $success = false;
      }
    }

    return $success;
  }

  /**
   * Cập nhật thứ tự ảnh
   */
  public function updateSortOrder($imageId, $sortOrder) {
    return $this->update($imageId, ['sort_order' => $sortOrder]);
  }

  /**
   * Sắp xếp lại thứ tự ảnh
   */
  public function reorderImages($productId, $imageIds) {
    $success = true;

    foreach ($imageIds as $index => $imageId) {
      if (!$this->update($imageId, ['sort_order' => $index])) {
        $success = false;
      }
    }

    return $success;
  }

  /**
   * Lấy ảnh tiếp theo theo thứ tự
   */
  public function getNextImage($productId, $currentSortOrder) {
    $productId = $this->db_escape($productId);
    $currentSortOrder = (int)$currentSortOrder;

    $query = "SELECT * FROM {$this->table}
              WHERE product_id = '$productId' AND sort_order > $currentSortOrder
              ORDER BY sort_order ASC
              LIMIT 1";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy ảnh trước đó theo thứ tự
   */
  public function getPreviousImage($productId, $currentSortOrder) {
    $productId = $this->db_escape($productId);
    $currentSortOrder = (int)$currentSortOrder;

    $query = "SELECT * FROM {$this->table}
              WHERE product_id = '$productId' AND sort_order < $currentSortOrder
              ORDER BY sort_order DESC
              LIMIT 1";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Kiểm tra xem ảnh có tồn tại không
   */
  public function imageExists($productId, $imageUrl) {
    $productId = $this->db_escape($productId);
    $imageUrl = $this->db_escape($imageUrl);

    $query = "SELECT id FROM {$this->table}
              WHERE product_id = '$productId' AND image_url = '$imageUrl'";
    $result = $this->db_query($query);
    return $this->db_fetch($result) !== false;
  }

  /**
   * Lấy danh sách ảnh với phân trang
   */
  public function getImagesWithPagination($productId, $page = 1, $perPage = 12) {
    $productId = $this->db_escape($productId);
    $offset = ($page - 1) * $perPage;

    $query = "SELECT * FROM {$this->table}
              WHERE product_id = '$productId'
              ORDER BY sort_order, is_main DESC, id ASC
              LIMIT $offset, $perPage";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy tổng số trang cho phân trang
   */
  public function getTotalPages($productId, $perPage = 12) {
    $total = $this->countByProduct($productId);
    return ceil($total / $perPage);
  }

  /**
   * Resize ảnh (đơn giản - cần GD library)
   */
  public function resizeImage($filePath, $maxWidth = 800, $maxHeight = 600) {
    if (!file_exists($filePath)) {
      return false;
    }

    $imageInfo = getimagesize($filePath);
    if (!$imageInfo) {
      return false;
    }

    $mime = $imageInfo['mime'];
    $width = $imageInfo[0];
    $height = $imageInfo[1];

    // Tính toán kích thước mới
    $ratio = $width / $height;
    if ($width > $maxWidth || $height > $maxHeight) {
      if ($ratio > 1) {
        $newWidth = $maxWidth;
        $newHeight = $maxWidth / $ratio;
      } else {
        $newHeight = $maxHeight;
        $newWidth = $maxHeight * $ratio;
      }
    } else {
      // Không cần resize
      return true;
    }

    // Tạo image resource
    switch ($mime) {
      case 'image/jpeg':
        $source = imagecreatefromjpeg($filePath);
        break;
      case 'image/png':
        $source = imagecreatefrompng($filePath);
        break;
      case 'image/gif':
        $source = imagecreatefromgif($filePath);
        break;
      case 'image/webp':
        $source = imagecreatefromwebp($filePath);
        break;
      default:
        return false;
    }

    if (!$source) {
      return false;
    }

    // Tạo image mới
    $destination = imagecreatetruecolor($newWidth, $newHeight);

    // Giữ transparency cho PNG và GIF
    if ($mime == 'image/png' || $mime == 'image/gif') {
      imagecolortransparent($destination, imagecolorallocatealpha($destination, 0, 0, 0, 127));
      imagealphablending($destination, false);
      imagesavealpha($destination, true);
    }

    // Resize
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Lưu ảnh
    switch ($mime) {
      case 'image/jpeg':
        imagejpeg($destination, $filePath, 90);
        break;
      case 'image/png':
        imagepng($destination, $filePath, 9);
        break;
      case 'image/gif':
        imagegif($destination, $filePath);
        break;
      case 'image/webp':
        imagewebp($destination, $filePath, 90);
        break;
    }

    // Giải phóng memory
    imagedestroy($source);
    imagedestroy($destination);

    return true;
  }

  /**
   * Tạo thumbnail từ ảnh gốc
   */
  public function createThumbnail($sourcePath, $thumbPath, $thumbWidth = 200, $thumbHeight = 200) {
    if (!file_exists($sourcePath)) {
      return false;
    }

    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
      return false;
    }

    $mime = $imageInfo['mime'];
    $width = $imageInfo[0];
    $height = $imageInfo[1];

    // Tạo image resource
    switch ($mime) {
      case 'image/jpeg':
        $source = imagecreatefromjpeg($sourcePath);
        break;
      case 'image/png':
        $source = imagecreatefrompng($sourcePath);
        break;
      case 'image/gif':
        $source = imagecreatefromgif($sourcePath);
        break;
      case 'image/webp':
        $source = imagecreatefromwebp($sourcePath);
        break;
      default:
        return false;
    }

    if (!$source) {
      return false;
    }

    // Tính toán crop center
    $src_x = $src_y = 0;
    $src_w = $width;
    $src_h = $height;

    if ($width / $height > $thumbWidth / $thumbHeight) {
      $src_w = $height * $thumbWidth / $thumbHeight;
      $src_x = ($width - $src_w) / 2;
    } else {
      $src_h = $width * $thumbHeight / $thumbWidth;
      $src_y = ($height - $src_h) / 2;
    }

    // Tạo thumbnail
    $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

    // Giữ transparency cho PNG và GIF
    if ($mime == 'image/png' || $mime == 'image/gif') {
      imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
      imagealphablending($thumb, false);
      imagesavealpha($thumb, true);
    }

    imagecopyresampled($thumb, $source, 0, 0, $src_x, $src_y, $thumbWidth, $thumbHeight, $src_w, $src_h);

    // Lưu thumbnail
    switch ($mime) {
      case 'image/jpeg':
        imagejpeg($thumb, $thumbPath, 90);
        break;
      case 'image/png':
        imagepng($thumb, $thumbPath, 9);
        break;
      case 'image/gif':
        imagegif($thumb, $thumbPath);
        break;
      case 'image/webp':
        imagewebp($thumb, $thumbPath, 90);
        break;
    }

    // Giải phóng memory
    imagedestroy($source);
    imagedestroy($thumb);

    return true;
  }

  /**
   * Lấy đường dẫn đầy đủ của ảnh
   */
  public function getImagePath($fileName) {
    return $this->uploadPath . $fileName;
  }

  /**
   * Lấy URL của ảnh (cho frontend)
   */
  public function getImageUrl($fileName) {
    return str_replace('../', '', $this->uploadPath) . $fileName;
  }

  /**
   * Kiểm tra và sửa lỗi ảnh chính
   */
  public function fixMainImage($productId) {
    $mainImage = $this->getMainImage($productId);

    // Nếu không có ảnh chính, đặt ảnh đầu tiên làm ảnh chính
    if (!$mainImage) {
      $images = $this->getByProductId($productId);
      if (!empty($images)) {
        return $this->setMainImage($images[0]['id'], $productId);
      }
    }

    return true;
  }
}
?>
