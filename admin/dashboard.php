<?php
// Thống kê tổng quan
global $userModel;
$totalUsers = $userModel->count();
// Cần thêm các model khác: Product, Order, Category
?>

<div class="stats-grid">
  <div class="stat-card">
    <h3>Tổng số Users</h3>
    <div class="number"><?php echo $totalUsers; ?></div>
  </div>
  <div class="stat-card">
    <h3>Tổng sản phẩm</h3>
    <div class="number">150</div>
  </div>
  <div class="stat-card">
    <h3>Đơn hàng hôm nay</h3>
    <div class="number">25</div>
  </div>
  <div class="stat-card">
    <h3>Doanh thu tháng</h3>
    <div class="number">50.000.000đ</div>
  </div>
</div>
