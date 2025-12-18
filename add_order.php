<?php
session_start();

require_once 'class/order.php';
require_once 'class/order_item.php';
require_once 'class/order_history.php';
require_once 'class/cart_product.php';
require_once 'class/user_address.php';

header('Content-Type: application/json');

$response = [
  'success' => false,
  'message' => '',
  'order_id' => 0,
  'order_number' => ''
];

try {

  // 1️⃣ Check login
  if (!isset($_SESSION['user_id'])) {
    throw new Exception('Vui lòng đăng nhập');
  }

  $user_id = (int)$_SESSION['user_id'];

  // 2️⃣ Nhận JSON
  $rawData = file_get_contents('php://input');
  $data = json_decode($rawData, true);

  if (empty($data)) {
    throw new Exception('Dữ liệu không hợp lệ');
  }

  // 3️⃣ Validate address
  $userAddressModel = new UserAddress();
  $addressChoice = $userAddressModel->getAddressById(
    (int)$data['address_id'],
    $user_id
  );

  if (!$addressChoice) {
    throw new Exception('Địa chỉ không hợp lệ');
  }

  // 4️⃣ Validate items
  if (empty($data['items']) || !is_array($data['items'])) {
    throw new Exception('Giỏ hàng trống');
  }

  foreach ($data['items'] as $item) {
    if (
      empty($item['product_id']) ||
      empty($item['quantity']) ||
      empty($item['unit_price'])
    ) {
      throw new Exception('Sản phẩm không hợp lệ');
    }
  }

  // 5️⃣ Chuẩn bị order data
  $orderData = [
    'user_id'          => $user_id,
    'customer_name'    => $addressChoice['recipient_name'],
    'customer_phone'   => $addressChoice['phone'],
    'shipping_address' => $addressChoice['address'],

    'subtotal'         => (float)($data['totals']['subtotal'] ?? 0),
    'shipping_fee'     => (float)($data['totals']['shipping_fee'] ?? 0),
    'discount_amount'  => (float)($data['totals']['discount'] ?? 0),
    'total_amount'     => (float)($data['totals']['total_amount'] ?? 0),

    'payment_method'   => $data['payment_method'],
    'payment_status'   => $data['payment_method'] === 'cod' ? 'pending' : 'paid',
    'order_status'     => 'pending',
    'notes'            => $data['note'] ?? '',
    'coupon_code'      => $data['coupon_code'] ?? ''
  ];

  // 6️⃣ Tạo order + items (transaction)
  $orderModel = new OrderModel();
  $result = $orderModel->createOrderWithItems($orderData, $data['items']);

  if (!$result) {
    throw new Exception('Không thể tạo đơn hàng');
  }

  // 7️⃣ Xóa giỏ hàng
  $cartModel = new Cart();
  $cart = $cartModel->findCart($user_id);
  if ($cart) {
    $cartModel->clearCart($cart['id']);
  }

  // 8️⃣ Response OK
  $response['success'] = true;
  $response['order_id'] = $result['order_id'];
  $response['order_number'] = $result['order_number'];
  $response['message'] = 'Đặt hàng thành công';

} catch (Exception $e) {
  $response['message'] = $e->getMessage();
}

echo json_encode($response);
