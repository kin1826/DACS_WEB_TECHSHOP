<?php
require_once 'class/order.php';
require_once 'class/user.php';

$orderModel = new OrderModel();
$userModel = new User();

// Xử lý xác nhận đơn hàng
if (isset($_POST['confirm_order']) && isset($_POST['order_id'])) {
  $order_id = (int)$_POST['order_id'];
  $orderModel->updateOrderStatus($order_id, 'processing'); // Thay 'confirmed' bằng 'processing'
  $success = "Đã xác nhận đơn hàng #{$order_id}";
}

// Xử lý cập nhật trạng thái
if (isset($_POST['update_status']) && isset($_POST['order_id'])) {
  $order_id = (int)$_POST['order_id'];
  $new_status = $_POST['status'];
  $note = $_POST['status_note'] ?? '';
  $orderModel->updateOrderStatus($order_id, $new_status, $note);
  $success = "Đã cập nhật trạng thái đơn hàng #{$order_id} thành " . getStatusText($new_status);
}

// Xử lý cập nhật trạng thái thanh toán
if (isset($_POST['update_payment_status']) && isset($_POST['order_id'])) {
  $order_id = (int)$_POST['order_id'];
  $new_payment_status = $_POST['payment_status'];
  $payment_note = $_POST['payment_note'] ?? '';

  if ($orderModel->updatePaymentStatus($order_id, $new_payment_status, '')) {
    $success = "Đã cập nhật trạng thái thanh toán đơn hàng #{$order_id} thành " . getPaymentStatusText($new_payment_status);
  } else {
    $error = "Có lỗi xảy ra khi cập nhật trạng thái thanh toán";
  }
}

// Xử lý tìm kiếm
$search_keyword = '';
if (isset($_GET['search'])) {
  $search_keyword = trim($_GET['search']);
}

// Xử lý lọc theo trạng thái
$filter_status = '';
if (isset($_GET['status'])) {
  $filter_status = $_GET['status'];
}

// Lấy danh sách đơn hàng
$orders = $orderModel->getAllOrdersAdmin($search_keyword, $filter_status);

// Thống kê
$stats = $orderModel->getOrderStatistics();

// Đảm bảo tất cả key đều tồn tại
$defaultStats = [
  'total_orders' => 0,
  'pending_orders' => 0,
  'processing_orders' => 0,
  'shipped_orders' => 0,
  'delivered_orders' => 0,
  'cancelled_orders' => 0,
  'total_revenue' => 0
];

// Merge với dữ liệu thực tế
$stats = array_merge($defaultStats, $stats);

// Chuyển đổi tên trạng thái
function getStatusText($status) {
  $statuses = [
    'pending' => 'Chờ xác nhận',
    'processing' => 'Đang xử lý',
    'shipped' => 'Đang giao hàng',
    'delivered' => 'Đã giao hàng',
    'cancelled' => 'Đã hủy'
  ];
  return $statuses[$status] ?? $status;
}

function getStatusClass($status) {
  $classes = [
    'pending' => 'status-pending',
    'processing' => 'status-processing',
    'shipped' => 'status-shipped',
    'delivered' => 'status-delivered',
    'cancelled' => 'status-cancelled'
  ];
  return $classes[$status] ?? 'status-pending';
}

// Chuyển đổi trạng thái thanh toán
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
?>

<!-- CSS cho trang quản lý đơn hàng -->
<style>
  /* Statistics Cards */
  .stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
  }

  .stat-card .number {
    font-size: 32px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 10px;
  }

  .stat-card .label {
    color: #7f8c8d;
    font-size: 14px;
  }

  .stat-card.total { border-top: 4px solid #3498db; }
  .stat-card.pending { border-top: 4px solid #f39c12; }
  .stat-card.processing { border-top: 4px solid #9b59b6; }
  .stat-card.shipped { border-top: 4px solid #1abc9c; }
  .stat-card.delivered { border-top: 4px solid #27ae60; }
  .stat-card.cancelled { border-top: 4px solid #e74c3c; }

  /* Filter Section */
  .filter-section {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
  }

  .filter-form {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
  }

  .search-box {
    flex: 1;
    min-width: 200px;
  }

  .search-box input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
  }

  .status-filter select {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    background: white;
  }

  .filter-actions {
    display: flex;
    gap: 10px;
  }

  .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: opacity 0.3s;
  }

  .btn-primary {
    background: #3498db;
    color: white;
  }

  .btn-secondary {
    background: #95a5a6;
    color: white;
  }

  .btn-success {
    background: #2ecc71;
    color: white;
  }

  .btn-warning {
    background: #f39c12;
    color: white;
  }

  .btn-danger {
    background: #e74c3c;
    color: white;
  }

  .btn:hover {
    opacity: 0.9;
  }

  /* Orders Table */
  .orders-table-container {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }

  .orders-table {
    width: 100%;
    border-collapse: collapse;
  }

  .orders-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #eee;
  }

  .orders-table td {
    padding: 15px;
    border-bottom: 1px solid #eee;
  }

  .orders-table tr:hover {
    background: #f8f9fa;
  }

  /* Status Badges */
  .status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
  }

  .status-pending { background: #fef3cd; color: #856404; }
  .status-processing { background: #e2d9f3; color: #6f42c1; }
  .status-shipped { background: #d1f2eb; color: #117864; }
  .status-delivered { background: #d4edda; color: #155724; }
  .status-cancelled { background: #f8d7da; color: #721c24; }
  .status-paid { background: #d4edda; color: #155724; }
  .status-failed { background: #f8d7da; color: #721c24; }
  .status-refunded { background: #d1ecf1; color: #0c5460; }

  /* Action Buttons */
  .action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
  }

  .action-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: opacity 0.3s;
  }

  .action-btn:hover {
    opacity: 0.9;
  }

  /* Modal */
  .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
  }

  .modal-content {
    background: white;
    border-radius: 10px;
    padding: 30px;
    max-width: 1000px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
  }

  .modal-header {
    margin-bottom: 20px;
    position: relative;
  }

  .modal-header h3 {
    margin: 0;
    color: #2c3e50;
  }

  .modal-body {
    margin-bottom: 20px;
  }

  .modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
  }

  /* Order Details */
  .order-detail-section {
    margin-bottom: 20px;
  }

  .order-detail-section h4 {
    color: #2c3e50;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 1px solid #eee;
  }

  .order-items {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  .order-items th,
  .order-items td {
    padding: 10px;
    border: 1px solid #eee;
    text-align: left;
  }

  .order-items th {
    background: #f8f9fa;
  }

  /* Messages */
  .message {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
  }

  .empty-state i {
    font-size: 48px;
    margin-bottom: 20px;
    color: #bdc3c7;
  }

  /* Status Options in Modal */
  .status-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .status-options label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 10px;
    border-radius: 5px;
    transition: background 0.3s;
  }

  .btn-info {
    background: #17a2b8;
    color: white;
  }

  .status-options label:hover {
    background: #f8f9fa;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .stats-cards {
      grid-template-columns: repeat(2, 1fr);
    }

    .filter-form {
      flex-direction: column;
      align-items: stretch;
    }

    .search-box,
    .status-filter,
    .filter-actions {
      width: 100%;
    }

    .orders-table {
      display: block;
      overflow-x: auto;
    }

    .action-buttons {
      flex-direction: column;
    }
  }
</style>

<div class="admin-orders-page">
  <!-- Success/Error Messages -->
  <?php if (isset($success)): ?>
    <div class="message success">
      <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
  <?php endif; ?>

  <!-- Statistics Cards -->
  <div class="stats-cards">
    <div class="stat-card total">
      <div class="number"><?php echo $stats['total_orders']; ?></div>
      <div class="label">Tổng đơn hàng</div>
    </div>
    <div class="stat-card pending">
      <div class="number"><?php echo $stats['pending_orders']; ?></div>
      <div class="label">Chờ xác nhận</div>
    </div>
    <div class="stat-card processing">
      <div class="number"><?php echo $stats['processing_orders']; ?></div>
      <div class="label">Đang xử lý</div>
    </div>
    <div class="stat-card delivered">
      <div class="number"><?php echo $stats['delivered_orders']; ?></div>
      <div class="label">Đã giao hàng</div>
    </div>
    <div class="stat-card cancelled">
      <div class="number"><?php echo $stats['cancelled_orders']; ?></div>
      <div class="label">Đã hủy</div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="filter-section">
    <form method="GET" class="filter-form">
      <input type="hidden" name="page" value="orders">

      <div class="search-box">
        <input type="text" name="search" placeholder="Tìm theo mã đơn, tên KH, số điện thoại..."
               value="<?php echo htmlspecialchars($search_keyword); ?>">
      </div>

      <div class="status-filter">
        <select name="status">
          <option value="">Tất cả trạng thái</option>
          <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
          <option value="processing" <?php echo $filter_status === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
          <option value="shipped" <?php echo $filter_status === 'shipped' ? 'selected' : ''; ?>>Đang giao hàng</option>
          <option value="delivered" <?php echo $filter_status === 'delivered' ? 'selected' : ''; ?>>Đã giao hàng</option>
          <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
        </select>
      </div>

      <div class="filter-actions">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-search"></i> Tìm kiếm
        </button>
        <a href="admin.php?page=orders" class="btn btn-secondary">
          <i class="fas fa-redo"></i> Reset
        </a>
      </div>
    </form>
  </div>

  <!-- Orders Table -->
  <div class="orders-table-container">
    <?php if (empty($orders)): ?>
      <div class="empty-state">
        <i class="fas fa-shopping-cart"></i>
        <h3>Không tìm thấy đơn hàng</h3>
        <p>Không có đơn hàng nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
      </div>
    <?php else: ?>
      <table class="orders-table">
        <thead>
        <tr>
          <th>Mã đơn</th>
          <th>Khách hàng</th>
          <th>Ngày đặt</th>
          <th>Tổng tiền</th>
          <th>Trạng thái</th>
          <th>Thanh toán</th>
          <th>Thao tác</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
          <tr>
            <td><strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong></td>
            <td>
              <?php if ($order['user_id']): ?>
                <div><?php echo htmlspecialchars($order['customer_username'] ?? $order['customer_name']); ?></div>
                <small><?php echo htmlspecialchars($order['customer_email'] ?? ''); ?></small>
              <?php else: ?>
                <div><?php echo htmlspecialchars($order['customer_name']); ?></div>
                <small>Khách vãng lai</small>
              <?php endif; ?>
            </td>
            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
            <td><strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>₫</strong></td>
            <td>
                            <span class="status-badge <?php echo getStatusClass($order['order_status']); ?>">
                                <?php echo getStatusText($order['order_status']); ?>
                            </span>
            </td>
            <td>
                            <span class="status-badge <?php echo getPaymentStatusClass($order['payment_status']); ?>">
                                <?php echo getPaymentStatusText($order['payment_status']); ?>
                            </span>
            </td>
            <td>
              <div class="action-buttons">
                <button class="action-btn btn-primary" onclick="showOrderDetails(<?php echo $order['id']; ?>)">
                  <i class="fas fa-eye"></i> Xem
                </button>

                <?php if ($order['order_status'] == 'pending'): ?>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <button type="submit" name="confirm_order" class="action-btn btn-success">
                      <i class="fas fa-check"></i> Xác nhận
                    </button>
                  </form>
                <?php endif; ?>

                <button class="action-btn btn-warning" onclick="showUpdateStatusModal(<?php echo $order['id']; ?>, '<?php echo $order['order_status']; ?>')">
                  <i class="fas fa-edit"></i> Cập nhật
                </button>

                <!-- Thêm nút cập nhật thanh toán -->
                <button class="action-btn btn-info" onclick="showUpdatePaymentModal(<?php echo $order['id']; ?>, '<?php echo $order['payment_status']; ?>')">
                  <i class="fas fa-money-bill-wave"></i> Thanh toán
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<!-- Order Details Modal -->
<div id="orderDetailsModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Chi tiết đơn hàng #<span id="modalOrderId"></span></h3>
      <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; position: absolute; right: 20px; top: 20px;">&times;</button>
    </div>
    <div class="modal-body" id="orderDetailsContent">
      <!-- Content will be loaded via AJAX -->
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal()">Đóng</button>
      <button type="button" class="btn btn-primary" onclick="printOrderDetails()">
        <i class="fas fa-print"></i> In đơn hàng
      </button>
    </div>
  </div>
</div>

<!-- Update Status Modal -->
<div id="updateStatusModal" class="modal">
  <div class="modal-content">
    <form method="POST">
      <div class="modal-header">
        <h3>Cập nhật trạng thái đơn hàng #<span id="updateOrderId"></span></h3>
        <button type="button" onclick="closeUpdateModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; position: absolute; right: 20px; top: 20px;">&times;</button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="order_id" id="orderIdInput">

        <div class="status-options">
          <label>
            <input type="radio" name="status" value="pending">
            <span class="status-badge status-pending">Chờ xác nhận</span>
          </label>
          <label>
            <input type="radio" name="status" value="processing">
            <span class="status-badge status-processing">Đang xử lý</span>
          </label>
          <label>
            <input type="radio" name="status" value="shipped">
            <span class="status-badge status-shipped">Đang giao hàng</span>
          </label>
          <label>
            <input type="radio" name="status" value="delivered">
            <span class="status-badge status-delivered">Đã giao hàng</span>
          </label>
          <label>
            <input type="radio" name="status" value="cancelled">
            <span class="status-badge status-cancelled">Đã hủy</span>
          </label>
        </div>

        <div class="form-group" style="margin-top: 20px;">
          <label for="status_note">Ghi chú (nếu có):</label>
          <textarea id="status_note" name="status_note" rows="3" placeholder="Nhập ghi chú về trạng thái đơn hàng..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeUpdateModal()">Hủy</button>
        <button type="submit" name="update_status" class="btn btn-primary">Cập nhật</button>
      </div>
    </form>
  </div>
</div>

<!-- Update Payment Status Modal -->
<div id="updatePaymentModal" class="modal">
  <div class="modal-content">
    <form method="POST">
      <div class="modal-header">
        <h3>Cập nhật trạng thái thanh toán #<span id="updatePaymentOrderId"></span></h3>
        <button type="button" onclick="closeUpdatePaymentModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; position: absolute; right: 20px; top: 20px;">&times;</button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="order_id" id="paymentOrderIdInput">

        <div class="status-options">
          <label>
            <input type="radio" name="payment_status" value="pending" required>
            <span class="status-badge status-pending">Chờ thanh toán</span>
          </label>
          <label>
            <input type="radio" name="payment_status" value="paid">
            <span class="status-badge status-paid">Đã thanh toán</span>
          </label>
          <label>
            <input type="radio" name="payment_status" value="failed">
            <span class="status-badge status-failed">Thanh toán thất bại</span>
          </label>
          <label>
            <input type="radio" name="payment_status" value="refunded">
            <span class="status-badge status-refunded">Đã hoàn tiền</span>
          </label>
        </div>

        <div class="form-group" style="margin-top: 20px;">
          <label for="payment_note">Ghi chú (nếu có):</label>
          <textarea id="payment_note" name="payment_note" rows="3" placeholder="Nhập ghi chú về thanh toán..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeUpdatePaymentModal()">Hủy</button>
        <button type="submit" name="update_payment_status" class="btn btn-primary">Cập nhật</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Modal functions
  function showModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
  }

  function closeModal() {
    document.getElementById('orderDetailsModal').style.display = 'none';
  }

  function closeUpdateModal() {
    document.getElementById('updateStatusModal').style.display = 'none';
  }

  // Show order details via AJAX
  function showOrderDetails(orderId, number_order) {
    // Hiển thị loading
    document.getElementById('orderDetailsContent').innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Đang tải dữ liệu...</p></div>';
    showModal('orderDetailsModal');

    fetch(`apiPrivate/get_order_details.php?order_id=${orderId}`)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.text();
      })
      .then(html => {
        document.getElementById('modalOrderId').textContent = orderId;
        document.getElementById('orderDetailsContent').innerHTML = html;
      })
      .catch(error => {
        console.error('Error:', error);
        document.getElementById('orderDetailsContent').innerHTML =
          '<div class="message error"><i class="fas fa-exclamation-circle"></i> Lỗi tải dữ liệu đơn hàng. Vui lòng thử lại.</div>';
      });
  }

  // Show update status modal với trạng thái hiện tại
  function showUpdateStatusModal(orderId, currentStatus) {
    document.getElementById('updateOrderId').textContent = orderId;
    document.getElementById('orderIdInput').value = orderId;

    // Check radio button tương ứng với trạng thái hiện tại
    const radioButtons = document.querySelectorAll('input[name="status"]');
    radioButtons.forEach(radio => {
      if (radio.value === currentStatus) {
        radio.checked = true;
      }
    });

    showModal('updateStatusModal');
  }

  // Print order details
  function printOrderDetails() {
    const printContent = document.getElementById('orderDetailsContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Đơn hàng #${document.getElementById('modalOrderId').textContent}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
                    h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
                    .order-info { margin-bottom: 20px; }
                    .section-title { background: #f0f0f0; padding: 10px; font-weight: bold; margin-top: 20px; }
                    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background: #f5f5f5; }
                    .total-row { font-weight: bold; background: #f9f9f9; }
                    .status-badge { padding: 3px 8px; border-radius: 10px; font-size: 11px; }
                    .status-delivered { background: #d4edda; color: #155724; }
                    .status-pending { background: #fef3cd; color: #856404; }
                    @media print {
                        body { font-size: 12px; }
                        .no-print { display: none !important; }
                    }
                    .print-header { text-align: center; margin-bottom: 30px; }
                    .print-header h1 { margin: 0; color: #2c3e50; }
                    .print-header p { margin: 5px 0; color: #7f8c8d; }
                    .print-footer { margin-top: 50px; text-align: center; font-size: 11px; color: #95a5a6; }
                </style>
            </head>
            <body>
                <div class="print-header">
                    <h1>Đơn hàng #${document.getElementById('modalOrderId').textContent}</h1>
                    <p>Ngày in: ${new Date().toLocaleDateString('vi-VN')}</p>
                </div>
                ${printContent}
                <div class="print-footer">
                    <p>--- Hết ---</p>
                    <p>TechShop - Địa chỉ: 123 Đường ABC, Quận XYZ, TP.HCM</p>
                    <p>Điện thoại: 0123 456 789 | Email: contact@techshop.com</p>
                </div>
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() {
                            window.close();
                        }, 500);
                    }
                <\/script>
            </body>
            </html>
        `);
    printWindow.document.close();
  }

  // Show update payment status modal
  function showUpdatePaymentModal(orderId, currentPaymentStatus) {
    document.getElementById('updatePaymentOrderId').textContent = orderId;
    document.getElementById('paymentOrderIdInput').value = orderId;

    // Check radio button tương ứng với trạng thái thanh toán hiện tại
    const radioButtons = document.querySelectorAll('input[name="payment_status"]');
    radioButtons.forEach(radio => {
      if (radio.value === currentPaymentStatus) {
        radio.checked = true;
      }
    });

    showModal('updatePaymentModal');
  }

  function closeUpdatePaymentModal() {
    document.getElementById('updatePaymentModal').style.display = 'none';
  }

  // Close modals when clicking outside
  window.onclick = function(event) {
    const orderModal = document.getElementById('orderDetailsModal');
    const statusModal = document.getElementById('updateStatusModal');
    const paymentModal = document.getElementById('updatePaymentModal');

    if (event.target === orderModal) {
      closeModal();
    }
    if (event.target === statusModal) {
      closeUpdateModal();
    }
    if (event.target === paymentModal) {
      closeUpdatePaymentModal();
    }
  }

  // Close modals with Escape key
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      closeModal();
      closeUpdateModal();
      closeUpdatePaymentModal();
    }
  });

  // Auto-refresh page every 60 seconds for new orders (chỉ khi không có modal mở)
  setInterval(() => {
    const orderModal = document.getElementById('orderDetailsModal');
    const statusModal = document.getElementById('updateStatusModal');
    const paymentModal = document.getElementById('updatePaymentModal');

    if (orderModal.style.display === 'none' &&
      statusModal.style.display === 'none' &&
      paymentModal.style.display === 'none') {
      window.location.reload();
    }
  }, 60000);
</script>
