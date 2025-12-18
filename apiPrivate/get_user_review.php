<?php
require_once '../class/review.php';

header('Content-Type: application/json');

$reviewsModel = new Reviews();

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($user_id > 0) {
  $reviews = $reviewsModel->getUserReviews($user_id);
  echo json_encode([
    'success' => true,
    'reviews' => $reviews
  ]);
} else {
  echo json_encode([
    'success' => false,
    'message' => 'Thiếu thông tin user'
  ]);
}
?><?php
