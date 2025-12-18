<?php
session_start();
require_once '../class/cart_product.php';
require_once '../cart.php';

header('Content-Type: application/json');

// Khởi tạo cart
$cart = new Cart();

// Lấy action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Xử lý CORS nếu cần
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Lấy user_id
$user_id = getUserId();

function getUserId() {
  if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
    return $_SESSION['user_id'];
  }

  if (!isset($_SESSION['guest_id'])) {
    $_SESSION['guest_id'] = 'guest_' . uniqid() . '_' . time();
  }

  return $_SESSION['guest_id'];
}

// Route các action
switch ($action) {
  case 'add':
    handleAddToCart($cart, $user_id);
    break;

  case 'remove':
    handleRemoveFromCart($cart, $user_id);
    break;

  case 'update':
    handleUpdateCart($cart, $user_id);
    break;

  case 'get_count':
    getCartCount($cart, $user_id);
    break;

  case 'get_items':
    getCartItems($cart, $user_id);
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function handleAddToCart($cart, $user_id) {
  $product_id = (int)($_POST['product_id'] ?? 0);
  $variant_id = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
  $quantity = (int)($_POST['quantity'] ?? 1);

  if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    return;
  }

  if ($quantity <= 0) {
    $quantity = 1;
  }

  // Kiểm tra tồn kho (thêm logic của bạn ở đây)
  if (!checkStock($product_id, $variant_id, $quantity)) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm đã hết hàng']);
    return;
  }

  // Thêm vào giỏ hàng
  $result = $cart->addItem($user_id, $product_id, $variant_id, $quantity);

  if ($result) {
    $cart_count = $cart->countItems($user_id);
    echo json_encode([
      'success' => true,
      'message' => 'Đã thêm vào giỏ hàng',
      'cart_count' => $cart_count
    ]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Không thể thêm vào giỏ hàng']);
  }
}

function handleRemoveFromCart($cart, $user_id) {
  $item_id = (int)($_POST['item_id'] ?? 0);

  if ($item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item']);
    return;
  }

  $result = $cart->removeItem($item_id);

  if ($result) {
    $cart_count = $cart->countItems($user_id);
    echo json_encode([
      'success' => true,
      'message' => 'Đã xóa khỏi giỏ hàng',
      'cart_count' => $cart_count
    ]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Không thể xóa']);
  }
}

function handleUpdateCart($cart, $user_id) {
  $item_id = (int)($_POST['item_id'] ?? 0);
  $quantity = (int)($_POST['quantity'] ?? 1);

  if ($item_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    return;
  }

  $result = $cart->updateQuantity($item_id, $quantity);

  if ($result) {
    $cart_count = $cart->countItems($user_id);
    echo json_encode([
      'success' => true,
      'message' => 'Đã cập nhật số lượng',
      'cart_count' => $cart_count
    ]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật']);
  }
}

function getCartCount($cart, $user_id) {
  $count = $cart->countItems($user_id);
  echo json_encode(['count' => $count]);
}

function getCartItems($cart, $user_id) {
  $items = $cart->getCartItems($user_id, true);
  echo json_encode(['items' => $items]);
}

function checkStock($product_id, $variant_id, $quantity) {
  // Thêm logic kiểm tra tồn kho của bạn ở đây
  // Trả về true nếu còn hàng, false nếu hết hàng
  return true; // Tạm thời luôn trả về true
}
