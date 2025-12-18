<?php
// reviews.php
require_once 'db.php'; // File chứa class DB bạn đã cung cấp

class Reviews extends DB {
  protected $table = 'product_reviews';

  public function __construct() {
    parent::__construct();
  }

  /**
   * Lấy đánh giá của user cho một sản phẩm
   * @param int $user_id
   * @param int $product_id
   * @return array|null
   */
  public function getUserReviewForProduct($user_id, $product_id) {
    $user_id = (int)$user_id;
    $product_id = (int)$product_id;

    $query = "SELECT * FROM {$this->table}
                  WHERE user_id = $user_id
                  AND product_id = $product_id
                  LIMIT 1";

    $result = $this->db_query($query);
    if (!$result) return null;

    return $this->db_fetch($result);
  }

  /**
   * Lấy tất cả đánh giá của user
   * @param int $user_id
   * @return array
   */
  public function getUserReviews($user_id) {
    $user_id = (int)$user_id;

    $query = "SELECT * FROM {$this->table}
                  WHERE user_id = $user_id
                  ORDER BY created_at DESC";

    $result = $this->db_query($query);
    if (!$result) return [];

    return $this->db_fetch_all($result);
  }

  /**
   * Lấy đánh giá theo danh sách sản phẩm
   * @param int $user_id
   * @param array $product_ids
   * @return array
   */
  public function getReviewsForProducts($user_id, $product_ids) {
    $user_id = (int)$user_id;

    if (empty($product_ids)) return [];

    $escaped_ids = array_map([$this, 'db_escape'], $product_ids);
    $ids_string = implode(',', $escaped_ids);

    $query = "SELECT * FROM {$this->table}
                  WHERE user_id = $user_id
                  AND product_id IN ($ids_string)";

    $result = $this->db_query($query);
    if (!$result) return [];

    $reviews = $this->db_fetch_all($result);

    // Chuyển thành associative array với product_id là key
    $review_map = [];
    foreach ($reviews as $review) {
      $review_map[$review['product_id']] = $review;
    }

    return $review_map;
  }

  /**
   * Thêm hoặc cập nhật đánh giá
   * @param array $data
   * @return array
   */
  public function saveReview($data) {
    // Validate required fields
    $required = ['product_id', 'user_id', 'rating'];
    foreach ($required as $field) {
      if (!isset($data[$field]) || empty($data[$field])) {
        return [
          'success' => false,
          'message' => "Thiếu trường bắt buộc: $field"
        ];
      }
    }

    $product_id = (int)$data['product_id'];
    $user_id = (int)$data['user_id'];
    $rating = (int)$data['rating'];

    // Validate rating
    if ($rating < 1 || $rating > 5) {
      return [
        'success' => false,
        'message' => 'Đánh giá phải từ 1 đến 5 sao'
      ];
    }

    // Escape comment
    $comment = isset($data['comment']) ? $this->db_escape($data['comment']) : '';

    return $this->addReview($product_id, $user_id, $rating, $comment);
  }

  /**
   * Thêm review mới
   * @param int $product_id
   * @param int $user_id
   * @param int $rating
   * @param string $comment
   * @return array
   */
  private function addReview($product_id, $user_id, $rating, $comment) {
    $data = [
      'product_id' => $product_id,
      'user_id' => $user_id,
      'rating' => $rating,
      'comment' => $comment,
      'created_at' => date('Y-m-d H:i:s'),
      'updated_at' => date('Y-m-d H:i:s')
    ];

    $id = $this->db_insert($this->table, $data);

    if ($id) {
      $this->insertToProduct($data['product_id'], $data['rating']);
      return [
        'success' => true,
        'message' => 'Đánh giá đã được lưu thành công',
        'review_id' => $id,
        'review' => array_merge(['id' => $id], $data)
      ];
    } else {
      return [
        'success' => false,
        'message' => 'Không thể lưu đánh giá: ' . $this->get_error()
      ];
    }
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

  /**
   * Cập nhật review
   * @param int $review_id
   * @param int $rating
   * @param string $comment
   * @return array
   */
  private function updateReview($review_id, $rating, $comment) {
    $data = [
      'rating' => $rating,
      'comment' => $comment,
      'updated_at' => date('Y-m-d H:i:s')
    ];

    $success = $this->db_update($this->table, $data, "id = $review_id");

    if ($success) {
      return [
        'success' => true,
        'message' => 'Đánh giá đã được cập nhật',
        'review_id' => $review_id,
        'review' => array_merge(['id' => $review_id], $data)
      ];
    } else {
      return [
        'success' => false,
        'message' => 'Không thể cập nhật đánh giá: ' . $this->get_error()
      ];
    }
  }

  /**
   * Xóa đánh giá
   * @param int $review_id
   * @param int $user_id (optional - kiểm tra ownership)
   * @return array
   */
  public function deleteReview($review_id, $user_id = null) {
    $review_id = (int)$review_id;

    $where = "id = $review_id";
    if ($user_id !== null) {
      $user_id = (int)$user_id;
      $where .= " AND user_id = $user_id";
    }

    $success = $this->db_delete($this->table, $where);

    if ($success) {
      return [
        'success' => true,
        'message' => 'Đánh giá đã được xóa'
      ];
    } else {
      return [
        'success' => false,
        'message' => 'Không thể xóa đánh giá'
      ];
    }
  }

  public function getAllReviewOfProduct($product_id) {
    $sql = "SELECT * FROM {$this->table} WHERE product_id = {$product_id}";
    $re = $this->db_query($sql);
    return $this->db_fetch_all($re);
  }

  /**
   * Lấy thống kê đánh giá của sản phẩm
   * @param int $product_id
   * @return array
   */
  public function getProductReviewStats($product_id) {
    $product_id = (int)$product_id;

    $query = "SELECT
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                  FROM {$this->table}
                  WHERE product_id = $product_id";

    $result = $this->db_query($query);
    if (!$result) return null;

    return $this->db_fetch($result);
  }

  /**
   * Lấy tất cả reviews của một sản phẩm (cho product page)
   * @param int $product_id
   * @param int $limit
   * @param int $offset
   * @return array
   */
  public function getProductReviews($product_id, $limit = 10, $offset = 0) {
    $product_id = (int)$product_id;
    $limit = (int)$limit;
    $offset = (int)$offset;

    $query = "SELECT r.*, u.fullname, u.avatar
                  FROM {$this->table} r
                  LEFT JOIN users u ON r.user_id = u.id
                  WHERE r.product_id = $product_id
                  ORDER BY r.created_at DESC
                  LIMIT $offset, $limit";

    $result = $this->db_query($query);
    if (!$result) return [];

    return $this->db_fetch_all($result);
  }

  /**
   * Kiểm tra xem user đã mua sản phẩm chưa (để validate review)
   * @param int $user_id
   * @param int $product_id
   * @return bool
   */
  public function hasUserPurchasedProduct($user_id, $product_id) {
    $user_id = (int)$user_id;
    $product_id = (int)$product_id;

    $query = "SELECT 1
                  FROM orders o
                  JOIN order_items oi ON o.id = oi.order_id
                  WHERE o.user_id = $user_id
                  AND oi.product_id = $product_id
                  AND o.order_status = 'delivered'
                  LIMIT 1";

    $result = $this->db_query($query);
    if (!$result) return false;

    return $this->db_num_rows($result) > 0;
  }
}
