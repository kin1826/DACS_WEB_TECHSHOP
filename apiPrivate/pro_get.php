<?php
require_once '../class/product.php'; // nơi include productModel + db

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$productModel = new Product();

function getProduct($id): ?array
{
  global $productModel;

  if (!$id) return null;

  $productGet = $productModel->getNameAndImageProductById($id);
  if (!$productGet) return null;

  return [
    'product_id' => (int)$id,
    'name_pr' => $productGet['name_pr'],
    'sale_price' => $productGet['sale_price'],
    'image_url' => $productGet['image_url'],
    'alt_text' => $productGet['alt_text']
  ];
}

$product = getProduct($id);

if (!$product) {
  echo json_encode([
    'success' => false,
    'message' => 'Không tìm thấy sản phẩm'
  ]);
  exit;
}

echo json_encode([
  'success' => true,
  'product' => $product
]);
