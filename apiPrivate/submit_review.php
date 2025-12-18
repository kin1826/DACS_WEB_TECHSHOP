<?php
// reviews_api.php
header('Content-Type: application/json');
require_once '../class/review.php'; // Điều chỉnh đường dẫn phù hợp

// Khởi tạo session
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

$user_id = $_SESSION['user_id'];
$reviews = new Reviews();

// Xác định action từ request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Xử lý các action
switch ($action) {
  case 'submit':
    handleSubmit($reviews, $user_id);
    break;

  case 'get':
    handleGet($reviews, $user_id);
    break;

  case 'delete':
    handleDelete($reviews, $user_id);
    break;

  case 'stats':
    handleStats($reviews);
    break;

  case 'check_purchase':
    handleCheckPurchase($reviews, $user_id);
    break;

  default:
    // Mặc định lấy tất cả reviews của user
    handleGetAll($reviews, $user_id);
    break;
}

/**
 * Xử lý submit review
 */
function handleSubmit($reviews, $user_id) {
  // Chỉ chấp nhận POST
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    return;
  }

  // Lấy dữ liệu
  $input = json_decode(file_get_contents('php://input'), true);

  // Fallback cho form data
  if (empty($input)) {
    $input = $_POST;
  }

  // Validate dữ liệu bắt buộc
  if (!isset($input['product_id']) || !isset($input['rating'])) {
    echo json_encode([
      'success' => false,
      'message' => 'Thiếu thông tin bắt buộc: product_id và rating'
    ]);
    return;
  }

  // Thêm user_id vào data
  $input['user_id'] = $user_id;

  // Lưu review
  $result = $reviews->saveReview($input);

  // Trả về kết quả
  if ($result['success']) {
    http_response_code(200);
  } else {
    http_response_code(400);
  }

  echo json_encode($result);
}

/**
 * Xử lý lấy reviews
 */
function handleGet($reviews, $user_id) {
  // GET request
  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    return;
  }

  // Lấy theo product_ids
  if (isset($_GET['product_ids'])) {
    $product_ids = explode(',', $_GET['product_ids']);
    $product_ids = array_filter($product_ids, 'is_numeric');

    if (empty($product_ids)) {
      echo json_encode(['success' => false, 'message' => 'Invalid product_ids']);
      return;
    }

    $reviews_data = $reviews->getReviewsForProducts($user_id, $product_ids);
    echo json_encode([
      'success' => true,
      'reviews' => $reviews_data
    ]);
    return;
  }

  // Lấy theo product_id đơn lẻ
  if (isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];

    if ($product_id <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid product_id']);
      return;
    }

    $review = $reviews->getUserReviewForProduct($user_id, $product_id);
    echo json_encode([
      'success' => true,
      'review' => $review
    ]);
    return;
  }

  // Mặc định: lấy tất cả reviews của user
  handleGetAll($reviews, $user_id);
}

/**
 * Lấy tất cả reviews của user
 */
function handleGetAll($reviews, $user_id) {
  $user_reviews = $reviews->getUserReviews($user_id);
  echo json_encode([
    'success' => true,
    'reviews' => $user_reviews
  ]);
}

/**
 * Xử lý xóa review
 */
function handleDelete($reviews, $user_id) {
  // POST hoặc DELETE method
  if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    return;
  }

  // Lấy review_id
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
  } else {
    $input = json_decode(file_get_contents('php://input'), true);
    $review_id = isset($input['review_id']) ? (int)$input['review_id'] : 0;
  }

  if ($review_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid review_id']);
    return;
  }

  // Xóa review (có kiểm tra ownership)
  $result = $reviews->deleteReview($review_id, $user_id);

  if ($result['success']) {
    http_response_code(200);
  } else {
    http_response_code(400);
  }

  echo json_encode($result);
}

/**
 * Lấy thống kê đánh giá sản phẩm
 */
function handleStats($reviews) {
  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    return;
  }

  if (!isset($_GET['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing product_id']);
    return;
  }

  $product_id = (int)$_GET['product_id'];

  if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product_id']);
    return;
  }

  $stats = $reviews->getProductReviewStats($product_id);

  echo json_encode([
    'success' => true,
    'stats' => $stats
  ]);
}

/**
 * Kiểm tra user đã mua sản phẩm chưa
 */
function handleCheckPurchase($reviews, $user_id) {
  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    return;
  }

  if (!isset($_GET['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing product_id']);
    return;
  }

  $product_id = (int)$_GET['product_id'];

  if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product_id']);
    return;
  }

  $hasPurchased = $reviews->hasUserPurchasedProduct($user_id, $product_id);

  echo json_encode([
    'success' => true,
    'has_purchased' => $hasPurchased
  ]);
}

// Đóng session (tùy chọn)
session_write_close();
?>
