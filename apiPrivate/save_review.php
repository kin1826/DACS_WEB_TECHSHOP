<?php
header('Content-Type: application/json');
session_start();

require_once '../class/review.php';

// 1️⃣ Lấy user_id từ session (KHÔNG lấy từ frontend)
$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id <= 0) {
  echo json_encode([
    'success' => false,
    'message' => 'Chưa đăng nhập'
  ]);
  exit;
}

// 2️⃣ Đọc JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
  echo json_encode([
    'success' => false,
    'message' => 'Dữ liệu gửi lên không hợp lệ'
  ]);
  exit;
}

// 3️⃣ Chuẩn hoá data để truyền vào saveReview()
$reviewData = [
  'product_id' => (int)($data['product_id'] ?? 0),
  'user_id'    => $user_id, // luôn lấy từ session
  'rating'     => (int)($data['rating'] ?? 0),
  'comment'    => $data['comment'] ?? ''
];

// 4️⃣ Gọi model
$reviewsModel = new Reviews();

try {
  $result = $reviewsModel->saveReview($reviewData);

  echo json_encode($result);
} catch (Throwable $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Lỗi server: ' . $e->getMessage()
  ]);
}
