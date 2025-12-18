<?php
session_start();
require_once '../class/order.php';
require_once '../class/order_item.php';
require_once '../class/user_address.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
  die('<div class="message error"><i class="fas fa-exclamation-circle"></i> Access denied</div>');
}

if (!isset($_GET['order_id'])) {
  die('<div class="message error"><i class="fas fa-exclamation-circle"></i> Order ID not specified</div>');
}

$order_id = (int)$_GET['order_id'];
$orderModel = new OrderModel();
$orderItemModel = new OrderItemModel();


$order = $orderModel->getOrderDetailAdmin($order_id);
if (!$order) {
  die('<div class="message error"><i class="fas fa-exclamation-circle"></i> Order not found</div>');
}

// Helper functions
function getOrderStatusText($status) {
  $statuses = [
    'pending' => 'Chờ xác nhận',
    'processing' => 'Đang xử lý',
    'shipped' => 'Đang giao hàng',
    'delivered' => 'Đã giao hàng',
    'cancelled' => 'Đã hủy'
  ];
  return $statuses[$status] ?? $status;
}

function getOrderStatusClass($status) {
  $classes = [
    'pending' => 'status-pending',
    'processing' => 'status-processing',
    'shipped' => 'status-shipped',
    'delivered' => 'status-delivered',
    'cancelled' => 'status-cancelled'
  ];
  return $classes[$status] ?? 'status-pending';
}

function getPaymentStatusText($status) {
  $statuses = [
    'pending' => 'Chờ thanh toán',
    'paid' => 'Đã thanh toán',
    'failed' => 'Thanh toán thất bại',
    'refunded' => 'Đã hoàn tiền'
  ];
  return $statuses[$status] ?? $status;
}

function getPaymentStatusClass($status) {
  $classes = [
    'pending' => 'status-pending',
    'paid' => 'status-paid',
    'failed' => 'status-failed',
    'refunded' => 'status-refunded'
  ];
  return $classes[$status] ?? 'status-pending';
}

function formatCurrency($amount) {
  return number_format($amount, 0, ',', '.') . '₫';
}

function formatDate($date, $format = 'd/m/Y H:i') {
  if (empty($date) || $date == '0000-00-00 00:00:00') {
    return '';
  }
  return date($format, strtotime($date));
}
?>

<div class="order-details">
  <!-- Order Info -->
  <div class="order-detail-section">
    <h4><i class="fas fa-info-circle"></i> Thông tin đơn hàng</h4>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
      <div>
        <p><strong>Mã đơn hàng:</strong> #<?php echo htmlspecialchars($order['order_number']); ?></p>
        <p><strong>Ngày đặt:</strong> <?php echo formatDate($order['created_at']); ?></p>
        <p><strong>Trạng thái:</strong>
          <span class="status-badge <?php echo getOrderStatusClass($order['order_status']); ?>">
                        <?php echo getOrderStatusText($order['order_status']); ?>
                    </span>
        </p>
      </div>
      <div>
        <p><strong>Phương thức thanh toán:</strong>
          <?php
          $paymentMethods = [
            'cod' => 'Thanh toán khi nhận hàng',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'credit_card' => 'Thẻ tín dụng',
            'momo' => 'Ví MoMo',
            'zalopay' => 'ZaloPay'
          ];
          echo $paymentMethods[$order['payment_method']] ?? $order['payment_method'];
          ?>
        </p>
        <p><strong>Trạng thái thanh toán:</strong>
          <span class="status-badge <?php echo getPaymentStatusClass($order['payment_status']); ?>">
                        <?php echo getPaymentStatusText($order['payment_status']); ?>
                    </span>
        </p>
        <p><strong>Tổng tiền:</strong>
          <span style="color: #e74c3c; font-weight: bold;">
                        <?php echo formatCurrency($order['total_amount']); ?>
                    </span>
        </p>
      </div>
    </div>
  </div>

  <!-- Customer Info -->
  <div class="order-detail-section">
    <h4><i class="fas fa-user"></i> Thông tin khách hàng</h4>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
      <div>
        <?php if ($order['user_id']): ?>
          <p><strong>Tên:</strong> <?php echo htmlspecialchars($order['customer_username'] ?? $order['customer_name']); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email'] ?? ''); ?></p>
          <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
        <?php else: ?>
          <p><strong>Khách vãng lai</strong></p>
          <p><strong>Tên:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email'] ?? ''); ?></p>
        <?php endif; ?>
      </div>
      <div>
        <p><strong>Địa chỉ giao hàng:</strong></p>
        <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
      </div>
    </div>
  </div>

  <!-- Order Items -->
  <div class="order-detail-section">
    <h4><i class="fas fa-shopping-cart"></i> Sản phẩm đã đặt</h4>
    <table class="order-items">
      <thead>
      <tr>
        <th>Sản phẩm</th>
        <th>SKU</th>
        <th>Đơn giá</th>
        <th>Số lượng</th>
        <th>Thành tiền</th>
      </tr>
      </thead>
      <tbody>
      <?php
      $subtotal = 0;
      if (isset($order['items']) && is_array($order['items'])):
        foreach ($order['items'] as $item):
          $subtotal += $item['total_price'];
          ?>
          <tr>
            <td>
              <div style="display: flex; align-items: center; gap: 10px;">
                <div>
                  <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                </div>
              </div>
            </td>
            <td><?php echo htmlspecialchars($item['product_sku']); ?></td>
            <td><?php echo formatCurrency($item['unit_price']); ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td><?php echo formatCurrency($item['total_price']); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" style="text-align: center;">Không có sản phẩm</td>
        </tr>
      <?php endif; ?>
      </tbody>
      <tfoot>
      <tr class="total-row">
        <td colspan="4" style="text-align: right;"><strong>Tạm tính:</strong></td>
        <td><?php echo formatCurrency($subtotal); ?></td>
      </tr>
      <tr class="total-row">
        <td colspan="4" style="text-align: right;"><strong>Phí vận chuyển:</strong></td>
        <td><?php echo formatCurrency($order['shipping_fee'] ?? 0); ?></td>
      </tr>
      <?php if ($order['discount_amount'] > 0): ?>
        <tr class="total-row">
          <td colspan="4" style="text-align: right;"><strong>Giảm giá:</strong></td>
          <td style="color: #27ae60;">-<?php echo formatCurrency($order['discount_amount']); ?></td>
        </tr>
      <?php endif; ?>
      <tr class="total-row">
        <td colspan="4" style="text-align: right;"><strong>Tổng cộng:</strong></td>
        <td style="color: #e74c3c; font-weight: bold;">
          <?php echo formatCurrency($order['total_amount']); ?>
        </td>
      </tr>
      </tfoot>
    </table>
  </div>

  <!-- Order History -->
  <?php if (isset($order['history']) && is_array($order['history']) && count($order['history']) > 0): ?>
    <div class="order-detail-section">
      <h4><i class="fas fa-history"></i> Lịch sử đơn hàng</h4>
      <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
        <?php foreach ($order['history'] as $history): ?>
          <div style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; <?php if ($history === end($order['history'])) echo 'border-bottom: none; margin-bottom: 0; padding-bottom: 0;'; ?>">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="status-badge <?php echo getOrderStatusClass($history['status']); ?>">
                            <?php echo getOrderStatusText($history['status']); ?>
                        </span>
              <small style="color: #7f8c8d;"><?php echo formatDate($history['created_at']); ?></small>
            </div>
            <?php if (!empty($history['note'])): ?>
              <p style="margin-top: 5px; margin-bottom: 0; color: #555;"><?php echo htmlspecialchars($history['note']); ?></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Order Notes -->
  <?php if (!empty($order['notes'])): ?>
    <div class="order-detail-section">
      <h4><i class="fas fa-sticky-note"></i> Ghi chú</h4>
      <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; white-space: pre-line;">
        <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
      </div>
    </div>
  <?php endif; ?>
</div>
