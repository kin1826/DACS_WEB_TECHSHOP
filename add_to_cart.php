<?php
session_start();
//header('Content-Type: application/json');
//
//// Nhận dữ liệu JSON từ fetch()
//$raw = file_get_contents("php://input");
//$data = json_decode($raw, true);

// Ưu tiên JSON, fallback sang $_POST
$product_id = intval($data['product_id'] ?? $_POST['product_id'] ?? 0);
$variant_id = intval($data['variant_id'] ?? $_POST['variant_id'] ?? 0);
$qty        = intval($data['quantity']   ?? $_POST['quantity']   ?? 1);

require_once "class/product.php";
require_once "class/cart_product.php";
require_once "class/CartItem.php";

$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
  echo json_encode([
    "status" => "error",
    "msg" => "Bạn chưa đăng nhập"
  ]);
  exit;
}

$productModel = new Product();
$cartModel = new Cart();
$cartItemModel = new CartItem();

// Kiểm tra sản phẩm
$product = $productModel->findById($product_id);

if (!$product) {
  echo json_encode([
    "status" => "error",
    "msg" => "Sản phẩm không tồn tại"
  ]);
  exit;
}

// Lấy giá theo variant
$price = ($product['sale_price'] > 0) ? $product['sale_price'] : $product['price'];

// Lấy giỏ hàng
$cart_id = $cartModel->getOrCreateCart($user_id);

// Thêm vào giỏ hàng
$ok = $cartItemModel->addItem($cart_id, $product_id, $variant_id, $qty, $price);

if (!$ok) {
  echo json_encode([
    "status" => "error",
    "msg" => "Không thể thêm giỏ hàng"
  ]);
  exit;
}

// Trả về tổng số lượng
$countItems = $cartModel->countItems($cart_id);

echo json_encode([
  "status" => "success",
  "msg" => "Đã thêm vào giỏ hàng",
  "count" => $countItems
]);
