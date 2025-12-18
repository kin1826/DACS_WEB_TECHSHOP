<?php
require_once 'db.php';

class ProductReview extends DB {
  protected $table = 'product_reviews';

  public function __construct() {
    parent::__construct();
  }

  /**
   * Tạo mới review
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
   * Cập nhật review
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
   * Xóa review
   */
  public function delete($id) {
    $id = $this->db_escape($id);
    $query = "DELETE FROM {$this->table} WHERE id = '$id'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy review theo ID
   */
  public function findById($id) {
    $id = $this->db_escape($id);
    $query = "SELECT r.*, u.username, u.email, p.name_pr as product_name
              FROM {$this->table} r
              LEFT JOIN users u ON r.user_id = u.id
              LEFT JOIN products p ON r.product_id = p.id
              WHERE r.id = '$id'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Lấy reviews theo product_id
   */
  public function getByProductId($productId, $onlyApproved = true) {
    $productId = $this->db_escape($productId);
    $where = $onlyApproved ? "AND is_approved = 1" : "";
    $query = "SELECT r.*, u.username, u.email
              FROM {$this->table} r
              LEFT JOIN users u ON r.user_id = u.id
              WHERE r.product_id = '$productId' $where
              ORDER BY r.created_at DESC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy reviews theo user_id
   */
  public function getByUserId($userId) {
    $userId = $this->db_escape($userId);
    $query = "SELECT r.*, p.name_pr as product_name
              FROM {$this->table} r
              LEFT JOIN products p ON r.product_id = p.id
              WHERE r.user_id = '$userId'
              ORDER BY r.created_at DESC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tính rating trung bình
   */
  public function getAverageRating($productId) {
    $productId = $this->db_escape($productId);
    $query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews
              FROM {$this->table}
              WHERE product_id = '$productId'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Phê duyệt review
   */
  public function approveReview($reviewId) {
    $reviewId = $this->db_escape($reviewId);
    $query = "UPDATE {$this->table} SET is_approved = 1 WHERE id = '$reviewId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Hủy phê duyệt review
   */
  public function unapproveReview($reviewId) {
    $reviewId = $this->db_escape($reviewId);
    $query = "UPDATE {$this->table} SET is_approved = 0 WHERE id = '$reviewId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Tăng số lượt helpful
   */
  public function incrementHelpful($reviewId) {
    $reviewId = $this->db_escape($reviewId);
    $query = "UPDATE {$this->table} SET helpful_count = helpful_count + 1 WHERE id = '$reviewId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Lấy số lượng reviews chờ duyệt
   */
  public function countPendingReviews() {
    $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE is_approved = 0";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return isset($row['total']) ? $row['total'] : 0;
  }

  /**
   * Xóa tất cả reviews của sản phẩm
   */
  public function deleteByProduct($productId) {
    $productId = $this->db_escape($productId);
    $query = "DELETE FROM {$this->table} WHERE product_id = '$productId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Xóa tất cả reviews của user
   */
  public function deleteByUser($userId) {
    $userId = $this->db_escape($userId);
    $query = "DELETE FROM {$this->table} WHERE user_id = '$userId'";
    return $this->db_query($query) !== false;
  }

  public function insertToProduct($product_id, $rating) {
    // Lấy rate + num_buy hiện tại
    $product = $this->db_fetch(
      $this->db_query("SELECT rate, num_buy FROM products WHERE id = $product_id")
    );

    $newRate = (
        $product['rate'] * $product['num_buy'] + $rating
      ) / ($product['num_buy'] + 1);

// Update product
    $this->db_update('products', [
      'rate'    => $newRate,
      'num_buy' => $product['num_buy'] + 1
    ], "id = $product_id");
  }

  // ========== CÁC PHƯƠNG THỨC MỚI BỔ SUNG ==========

  /**
   * Lấy chi tiết rating distribution
   */
  public function getRatingDistribution($productId) {
    $productId = $this->db_escape($productId);
    $query = "SELECT
                rating,
                COUNT(*) as count,
                ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM {$this->table} WHERE product_id = '$productId' AND is_approved = 1)), 2) as percentage
              FROM {$this->table}
              WHERE product_id = '$productId' AND is_approved = 1
              GROUP BY rating
              ORDER BY rating DESC";
    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy reviews với phân trang
   */
  public function getWithPagination($productId = null, $onlyApproved = true, $page = 1, $perPage = 10) {
    $offset = ($page - 1) * $perPage;
    $where = "WHERE 1=1";

    if ($productId) {
      $productId = $this->db_escape($productId);
      $where .= " AND r.product_id = '$productId'";
    }

    if ($onlyApproved) {
      $where .= " AND r.is_approved = 1";
    }

    $query = "SELECT r.*, u.username, u.email, p.name_pr as product_name
              FROM {$this->table} r
              LEFT JOIN users u ON r.user_id = u.id
              LEFT JOIN products p ON r.product_id = p.id
              $where
              ORDER BY r.created_at DESC
              LIMIT $offset, $perPage";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy tổng số trang
   */
  public function getTotalPages($productId = null, $onlyApproved = true, $perPage = 10) {
    $where = "WHERE 1=1";

    if ($productId) {
      $productId = $this->db_escape($productId);
      $where .= " AND product_id = '$productId'";
    }

    if ($onlyApproved) {
      $where .= " AND is_approved = 1";
    }

    $query = "SELECT COUNT(*) as total FROM {$this->table} $where";
    $result = $this->db_query($query);
    $row = $this->db_fetch($result);
    return ceil($row['total'] / $perPage);
  }

  /**
   * Lấy reviews mới nhất
   */
  public function getLatestReviews($limit = 10, $onlyApproved = true) {
    $limit = (int)$limit;
    $where = $onlyApproved ? "WHERE r.is_approved = 1" : "";

    $query = "SELECT r.*, u.username, p.name_pr as product_name, p.slug as product_slug
              FROM {$this->table} r
              LEFT JOIN users u ON r.user_id = u.id
              LEFT JOIN products p ON r.product_id = p.id
              $where
              ORDER BY r.created_at DESC
              LIMIT $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy reviews được đánh giá helpful nhất
   */
  public function getMostHelpfulReviews($limit = 10) {
    $limit = (int)$limit;

    $query = "SELECT r.*, u.username, p.name_pr as product_name
              FROM {$this->table} r
              LEFT JOIN users u ON r.user_id = u.id
              LEFT JOIN products p ON r.product_id = p.id
              WHERE r.is_approved = 1
              ORDER BY r.helpful_count DESC, r.created_at DESC
              LIMIT $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy reviews có rating cao nhất
   */
  public function getHighestRatedReviews($limit = 10) {
    $limit = (int)$limit;

    $query = "SELECT r.*, u.username, p.name_pr as product_name
              FROM {$this->table} r
              LEFT JOIN users u ON r.user_id = u.id
              LEFT JOIN products p ON r.product_id = p.id
              WHERE r.is_approved = 1
              ORDER BY r.rating DESC, r.created_at DESC
              LIMIT $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tìm kiếm reviews
   */
  public function search($keyword, $onlyApproved = true) {
    $keyword = $this->db_escape($keyword);
    $where = $onlyApproved ? "AND r.is_approved = 1" : "";

    $query = "SELECT r.*, u.username, p.name_pr as product_name
              FROM {$this->table} r
              LEFT JOIN users u ON r.user_id = u.id
              LEFT JOIN products p ON r.product_id = p.id
              WHERE (r.title LIKE '%$keyword%' OR r.comment LIKE '%$keyword%' OR p.name_pr LIKE '%$keyword%')
              $where
              ORDER BY r.created_at DESC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy thống kê reviews
   */
  public function getStats($productId = null) {
    $where = $productId ? "WHERE product_id = '" . $this->db_escape($productId) . "'" : "";

    $query = "SELECT
                COUNT(*) as total_reviews,
                SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved_reviews,
                SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending_reviews,
                AVG(CASE WHEN is_approved = 1 THEN rating ELSE NULL END) as avg_rating,
                SUM(helpful_count) as total_helpful,
                MAX(created_at) as latest_review
              FROM {$this->table}
              $where";

    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Kiểm tra user đã review sản phẩm chưa
   */
  public function hasUserReviewed($userId, $productId) {
    $userId = $this->db_escape($userId);
    $productId = $this->db_escape($productId);

    $query = "SELECT id FROM {$this->table}
              WHERE user_id = '$userId' AND product_id = '$productId'";
    $result = $this->db_query($query);
    return $this->db_fetch($result) !== false;
  }

  /**
   * Lấy review của user cho sản phẩm
   */
  public function getUserReviewForProduct($userId, $productId) {
    $userId = $this->db_escape($userId);
    $productId = $this->db_escape($productId);

    $query = "SELECT r.*, p.name_pr as product_name
              FROM {$this->table} r
              LEFT JOIN products p ON r.product_id = p.id
              WHERE r.user_id = '$userId' AND r.product_id = '$productId'";
    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  /**
   * Validate review data
   */
  public function validate($data) {
    $errors = [];

    if (empty($data['product_id'])) {
      $errors[] = 'Product ID không được để trống';
    }

    if (empty($data['user_id'])) {
      $errors[] = 'User ID không được để trống';
    }

    if (empty($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
      $errors[] = 'Rating phải từ 1 đến 5 sao';
    }

    if (!empty($data['title']) && strlen($data['title']) > 255) {
      $errors[] = 'Tiêu đề không được vượt quá 255 ký tự';
    }

    if (!empty($data['comment']) && strlen($data['comment']) > 2000) {
      $errors[] = 'Bình luận không được vượt quá 2000 ký tự';
    }

    return $errors;
  }

  /**
   * Tạo review với validation
   */
  public function createReview($data) {
    $errors = $this->validate($data);
    if (!empty($errors)) {
      return ['success' => false, 'errors' => $errors];
    }

    // Kiểm tra user đã review chưa
    if ($this->hasUserReviewed($data['user_id'], $data['product_id'])) {
      return ['success' => false, 'errors' => ['Bạn đã đánh giá sản phẩm này rồi']];
    }

    if ($this->create($data)) {
      return ['success' => true, 'review_id' => $this->db_insert_id()];
    }

    return ['success' => false, 'errors' => ['Không thể tạo đánh giá']];
  }

  /**
   * Phê duyệt nhiều reviews cùng lúc
   */
  public function batchApprove($reviewIds) {
    if (empty($reviewIds)) return 0;

    $ids = array_map([$this, 'db_escape'], $reviewIds);
    $idsStr = implode("', '", $ids);

    $query = "UPDATE {$this->table} SET is_approved = 1 WHERE id IN ('$idsStr')";
    $result = $this->db_query($query);

    return $result ? $this->db_affected_rows() : 0;
  }

  /**
   * Xóa nhiều reviews cùng lúc
   */
  public function batchDelete($reviewIds) {
    if (empty($reviewIds)) return 0;

    $ids = array_map([$this, 'db_escape'], $reviewIds);
    $idsStr = implode("', '", $ids);

    $query = "DELETE FROM {$this->table} WHERE id IN ('$idsStr')";
    $result = $this->db_query($query);

    return $result ? $this->db_affected_rows() : 0;
  }

  /**
   * Lấy reviews theo rating
   */
  public function getByRating($rating, $productId = null, $onlyApproved = true) {
    $rating = (int)$rating;
    $where = "WHERE rating = $rating";

    if ($productId) {
      $productId = $this->db_escape($productId);
      $where .= " AND product_id = '$productId'";
    }

    if ($onlyApproved) {
      $where .= " AND is_approved = 1";
    }

    $query = "SELECT r.*, u.username, p.name_pr as product_name
              FROM {$this->table} r
              LEFT JOIN users u ON r.user_id = u.id
              LEFT JOIN products p ON r.product_id = p.id
              $where
              ORDER BY r.created_at DESC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy reviews có hình ảnh (nếu có field image)
   */
  public function getReviewsWithImages($productId = null, $onlyApproved = true) {
    $where = "WHERE image IS NOT NULL AND image != ''";

    if ($productId) {
      $productId = $this->db_escape($productId);
      $where .= " AND product_id = '$productId'";
    }

    if ($onlyApproved) {
      $where .= " AND is_approved = 1";
    }

    $query = "SELECT r.*, u.username, p.name_pr as product_name
              FROM {$this->table} r
              LEFT JOIN users u ON r.user_id = u.id
              LEFT JOIN products p ON r.product_id = p.id
              $where
              ORDER BY r.created_at DESC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy reviews theo khoảng thời gian
   */
  public function getByDateRange($startDate, $endDate, $onlyApproved = true) {
    $startDate = $this->db_escape($startDate);
    $endDate = $this->db_escape($endDate);
    $where = $onlyApproved ? "AND is_approved = 1" : "";

    $query = "SELECT r.*, u.username, p.name_pr as product_name
              FROM {$this->table} r
              LEFT JOIN users u ON r.user_id = u.id
              LEFT JOIN products p ON r.product_id = p.id
              WHERE r.created_at BETWEEN '$startDate' AND '$endDate' $where
              ORDER BY r.created_at DESC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy top sản phẩm được đánh giá cao nhất
   */
  public function getTopRatedProducts($limit = 10) {
    $limit = (int)$limit;

    $query = "SELECT
                p.id,
                p.name_pr,
                p.slug,
                p.regular_price,
                p.sale_price,
                AVG(r.rating) as avg_rating,
                COUNT(r.id) as review_count
              FROM products p
              LEFT JOIN {$this->table} r ON p.id = r.product_id AND r.is_approved = 1
              WHERE p.status = 'published'
              GROUP BY p.id
              HAVING avg_rating >= 4 AND review_count >= 5
              ORDER BY avg_rating DESC, review_count DESC
              LIMIT $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy sản phẩm có nhiều reviews nhất
   */
  public function getMostReviewedProducts($limit = 10) {
    $limit = (int)$limit;

    $query = "SELECT
                p.id,
                p.name_pr,
                p.slug,
                p.regular_price,
                p.sale_price,
                COUNT(r.id) as review_count,
                AVG(r.rating) as avg_rating
              FROM products p
              LEFT JOIN {$this->table} r ON p.id = r.product_id AND r.is_approved = 1
              WHERE p.status = 'published'
              GROUP BY p.id
              HAVING review_count > 0
              ORDER BY review_count DESC, avg_rating DESC
              LIMIT $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Tính điểm trust score cho review
   */
  public function calculateTrustScore($reviewId) {
    $review = $this->findById($reviewId);
    if (!$review) return 0;

    $score = 0;

    // Điểm cơ bản từ rating
    $score += $review['rating'] * 10;

    // Điểm từ helpful count
    $score += min($review['helpful_count'] * 2, 20);

    // Điểm từ độ dài comment (nếu có)
    if (!empty($review['comment'])) {
      $commentLength = strlen($review['comment']);
      $score += min($commentLength / 10, 20);
    }

    // Điểm từ title (nếu có)
    if (!empty($review['title'])) {
      $score += 10;
    }

    return min($score, 100);
  }

  /**
   * Gửi email thông báo khi có review mới
   */
  public function notifyNewReview($reviewId) {
    $review = $this->findById($reviewId);
    if (!$review) return false;

    // TODO: Implement email notification logic
    // This would typically send an email to admin or product owner
    // about the new review

    return true;
  }

  /**
   * Kiểm tra và sửa lỗi reviews
   */
  public function fixReviews() {
    $fixed = 0;

    // Tìm reviews không có product
    $query = "SELECT r.id
              FROM {$this->table} r
              LEFT JOIN products p ON r.product_id = p.id
              WHERE p.id IS NULL";
    $result = $this->db_query($query);
    $orphaned = $this->db_fetch_all($result);

    foreach ($orphaned as $orphan) {
      if ($this->delete($orphan['id'])) {
        $fixed++;
      }
    }

    // Tìm reviews không có user
    $query = "SELECT r.id
              FROM {$this->table} r
              LEFT JOIN users u ON r.user_id = u.id
              WHERE u.id IS NULL";
    $result = $this->db_query($query);
    $orphaned = $this->db_fetch_all($result);

    foreach ($orphaned as $orphan) {
      if ($this->delete($orphan['id'])) {
        $fixed++;
      }
    }

    return $fixed;
  }

  /**
   * Lấy reviews cho dashboard
   */
  public function getDashboardReviews($limit = 10) {
    $limit = (int)$limit;

    $query = "SELECT r.*, u.username, p.name_pr as product_name
              FROM {$this->table} r
              LEFT JOIN users u ON r.user_id = u.id
              LEFT JOIN products p ON r.product_id = p.id
              ORDER BY r.created_at DESC
              LIMIT $limit";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy review summary cho sản phẩm
   */
  public function getProductReviewSummary($productId) {
    $stats = $this->getStats($productId);
    $distribution = $this->getRatingDistribution($productId);
    $latestReviews = $this->getLatestReviews(5, true);

    return [
      'stats' => $stats,
      'distribution' => $distribution,
      'latest_reviews' => $latestReviews
    ];
  }
}
?>
