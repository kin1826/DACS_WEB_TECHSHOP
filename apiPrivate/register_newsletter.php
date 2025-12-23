<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode([
    'success' => false,
    'message' => 'Bạn chưa đăng nhập'
  ]);
  exit;
}

require_once '../class/User.php';
$userModel = new User();

$user_id = (int)$_SESSION['user_id'];

if ($userModel->check_register($user_id)) {
  echo json_encode([
    'success' => false,
    'message' => 'Bạn đã đăng ký trước đó'
  ]);
  exit;
}

$success = $userModel->update_register($user_id);

echo json_encode([
  'success' => $success,
  'message' => $success ? 'Đăng ký thành công' : 'Không thể đăng ký'
]);
