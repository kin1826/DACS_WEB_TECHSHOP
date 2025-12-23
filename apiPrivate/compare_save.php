<?php
session_start();

$data = json_decode(file_get_contents('php://input'), true);

$productId  = (int)$data['productId'];
$categoryId = (int)$data['categoryId'];

$_SESSION['compare'] ??= [
  'category_id' => null,
  'products' => []
];

// CHƯA CÓ SP 1
if (count($_SESSION['compare']['products']) === 0) {
  $_SESSION['compare']['category_id'] = $categoryId;
  $_SESSION['compare']['products'][] = $productId;
  echo json_encode(['success'=>true]);
  exit;
}

// SP 2 → CHECK CATEGORY
if ($_SESSION['compare']['category_id'] !== $categoryId) {
  echo json_encode([
    'success'=>false,
    'message'=>'Chỉ so sánh sản phẩm cùng danh mục'
  ]);
  exit;
}

if (count($_SESSION['compare']['products']) >= 2) {
  echo json_encode([
    'success'=>false,
    'message'=>'Chỉ so sánh tối đa 2 sản phẩm'
  ]);
  exit;
}

$_SESSION['compare']['products'][] = $productId;
echo json_encode(['success'=>true]);
