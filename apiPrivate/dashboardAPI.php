<?php
require_once '../class/order.php';

$orderModel = new OrderModel();

$type = $_GET['type'] ?? '';

switch ($type) {

  // ===== BIỂU ĐỒ DOANH THU =====
  case 'revenue':
    $year = $_GET['year'] ?? date('Y');
    echo json_encode(
      $orderModel->getRevenueByYear($year),
      JSON_UNESCAPED_UNICODE
    );
    break;

  // ===== BIỂU ĐỒ TRẠNG THÁI ĐƠN HÀNG =====
  case 'orders':
    $period = $_GET['period'] ?? 'month';

    $row = $orderModel->getOrderStatusStats($period);

    echo json_encode([
      'labels' => [
        'Hoàn thành',
        'Đang xử lý',
        'Chờ thanh toán',
        'Đã hủy'
      ],
      'data' => [
        (int)$row['completed'],
        (int)$row['processing'],
        (int)$row['unpaid'],
        (int)$row['cancelled']
      ]
    ], JSON_UNESCAPED_UNICODE);
    break;

  default:
    http_response_code(400);
    echo json_encode([
      'error' => 'Invalid dashboard type'
    ]);
}
