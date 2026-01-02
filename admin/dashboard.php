<?php
// Thống kê tổng quan
require_once 'class/product.php';
require_once 'class/user.php';
require_once 'class/order.php';

$product = new Product();
$totalPro = $product->getTotalProducts();

global $userModel;
$totalUsers = $userModel->count();

$orderModel = new OrderModel();
$orderToday = $orderModel->countOrderNum("today");
$orderMonth = $orderModel->getMonthlyRevenue();
$orderYear = $orderModel->getRevenueByYear();

$chartData = [
  'labels' => array_map(fn($m) => 'T'.$m, range(1, 12)),
  'data'   => array_map(
    fn($v) => round($v / 1_000_000, 2), // đổi sang triệu
    array_values($orderYear)
  )
];

//$order = new Order();
//$todayOrders = $order->getTodayOrders();
//$monthlyRevenue = $order->getMonthlyRevenue();
//$topProducts = $product->getTopProducts(5);
//$recentOrders = $order->getRecentOrders(5);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
<div class="container">
  <!-- Header -->
  <div class="dashboard-header">
    <div class="dashboard-title">
      <h1><i class="fas fa-chart-line"></i> Dashboard Admin</h1>
      <span class="current-time" id="currentTime"></span>
    </div>
    <div class="dashboard-actions">
      <button class="refresh-btn" onclick="refreshDashboard()">
        <i class="fas fa-sync-alt"></i> Làm mới
      </button>
      <button class="export-btn" onclick="exportToExcel()">
        <i class="fas fa-file-excel"></i> Xuất Excel
      </button>
    </div>
  </div>

  <!-- Stats Grid -->
  <div class="stats-grid">
    <div class="stat-card">
      <h3>Tổng số Users</h3>
      <div class="number"><?php echo $totalUsers; ?></div>
      <div class="trend trend-up">
<!--        <i class="fas fa-arrow-up"></i> 12%-->
      </div>
    </div>
    <div class="stat-card">
      <h3>Tổng sản phẩm</h3>
      <div class="number"><?php echo $totalPro; ?></div>
      <div class="trend trend-up">
<!--        <i class="fas fa-arrow-up"></i> 5%-->
      </div>
    </div>
    <div class="stat-card">
      <h3>Đơn hàng hôm nay</h3>
      <div class="number"><?php echo $orderToday?></div>
      <div class="trend trend-up">
<!--        <i class="fas fa-arrow-up"></i> 8%-->
      </div>
    </div>
    <div class="stat-card">
      <h3>Doanh thu tháng</h3>
<!--      <div class="number">--><?php //echo number_format($monthlyRevenue, 0, ',', '.'); ?><!--đ</div>-->
      <div class="number"><?php echo number_format($orderMonth['revenue'], 0, ',', '.'); ?>đ</div>

      <div class="trend trend-up">
<!--        <i class="fas fa-arrow-up"></i> 15%-->
      </div>
    </div>
  </div>

  <!-- Charts Section -->
  <div class="charts-grid">
    <!-- Biểu đồ doanh thu -->
    <div class="chart-container">
      <div class="chart-header">
        <h3 class="chart-title">Doanh thu theo tháng</h3>
        <div class="chart-controls">
<!--          <select class="time-filter" onchange="updateRevenueChart(this.value)">-->
<!--            <option value="6">6 tháng</option>-->
<!--            <option value="12" selected>12 tháng</option>-->
<!--            <option value="24">24 tháng</option>-->
<!--          </select>-->
        </div>
      </div>
      <div class="chart-wrapper">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>

    <!-- Biểu đồ đơn hàng -->
    <div class="chart-container">
      <div class="chart-header">
        <h3 class="chart-title">Đơn hàng theo trạng thái</h3>
        <div class="chart-controls">
          <select class="time-filter" onchange="updateOrdersChart(this.value)">
            <option value="week">Tuần</option>
            <option value="month" selected>Tháng</option>
            <option value="quarter">Quý</option>
          </select>
        </div>
      </div>
      <div class="chart-wrapper">
        <canvas id="ordersChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Tables Section -->
  <div class="tables-grid">
    <!-- Top sản phẩm -->
    <div class="table-container">
      <div class="table-header">
        <h3 class="table-title">Sản phẩm bán chạy</h3>
        <a href="#" class="view-all">Xem tất cả</a>
      </div>
      <table>
        <thead>
        <tr>
          <th>Sản phẩm</th>
          <th>Doanh thu</th>
          <th>Đã bán</th>
        </tr>
        </thead>
        <tbody id="topProductsTable">
<!--        --><?php //foreach($topProducts as $product): ?>
<!--          <tr>-->
<!--            <td>--><?php //echo htmlspecialchars($product['name']); ?><!--</td>-->
<!--            <td>--><?php //echo number_format($product['revenue'], 0, ',', '.'); ?><!--đ</td>-->
<!--            <td>--><?php //echo $product['sold']; ?><!--</td>-->
<!--          </tr>-->
<!--        --><?php //endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Đơn hàng gần đây -->
    <div class="table-container">
      <div class="table-header">
        <h3 class="table-title">Đơn hàng gần đây</h3>
        <a href="#" class="view-all">Xem tất cả</a>
      </div>
      <table>
        <thead>
        <tr>
          <th>Mã đơn</th>
          <th>Khách hàng</th>
          <th>Tổng tiền</th>
          <th>Trạng thái</th>
        </tr>
        </thead>
        <tbody id="recentOrdersTable">
<!--        --><?php //foreach($recentOrders as $order): ?>
<!--          <tr>-->
<!--            <td>#--><?php //echo $order['id']; ?><!--</td>-->
<!--            <td>--><?php //echo htmlspecialchars($order['customer_name']); ?><!--</td>-->
<!--            <td>--><?php //echo number_format($order['total'], 0, ',', '.'); ?><!--đ</td>-->
<!--            <td>-->
<!--                                <span class="status status---><?php //echo $order['status']; ?><!--">-->
<!--                                    --><?php //echo $order['status']; ?>
<!--                                </span>-->
<!--            </td>-->
<!--          </tr>-->
<!--        --><?php //endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Activity -->
  <div class="activity-container">
    <h3 class="chart-title">Hoạt động gần đây</h3>
    <div class="activity-list" id="activityList">
      <!-- Sẽ được điền bằng JavaScript -->
    </div>
  </div>

  <!-- Footer -->
  <div class="dashboard-footer">
    <p>© 2024 Admin Dashboard. Cập nhật lần cuối: <span id="lastUpdate"></span></p>
  </div>
</div>

<script>
  // Dữ liệu mẫu cho Dashboard
  const revenueChartData = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE) ?>;

  console.log(revenueChartData);

  const dashboardData = {
    // Doanh thu 12 tháng gần đây
    revenueData: {
      labels: revenueChartData.labels,
      datasets: [{
        label: 'Doanh thu (triệu đồng)',
        data: revenueChartData.data,
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4
      }]
    },

    // Đơn hàng theo trạng thái
    ordersData: {
      labels: ['Hoàn thành', 'Đang xử lý', 'Chờ thanh toán', 'Đã hủy'],
      datasets: [{
        data: [45, 25, 20, 10],
        backgroundColor: [
          '#10b981',
          '#3b82f6',
          '#f59e0b',
          '#ef4444'
        ],
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },

    // Top sản phẩm
    topProducts: [

    ],

    // Đơn hàng gần đây
    recentOrders: [

    ],

    // Hoạt động gần đây
    recentActivities: [
    ]
  };

  // Biểu đồ doanh thu
  let revenueChart;
  // Biểu đồ đơn hàng
  let ordersChart;

  // Khởi tạo dashboard
  document.addEventListener('DOMContentLoaded', function() {
    // Hiển thị thời gian hiện tại
    updateCurrentTime();
    setInterval(updateCurrentTime, 60000); // Cập nhật mỗi phút

    // Hiển thị thời gian cập nhật
    document.getElementById('lastUpdate').textContent = new Date().toLocaleString('vi-VN');

    // Khởi tạo biểu đồ
    initCharts();

    // Điền dữ liệu vào bảng
    populateTables();

    // Hiển thị hoạt động gần đây
    populateActivities();
  });

  // Cập nhật thời gian hiện tại
  function updateCurrentTime() {
    const now = new Date();
    const options = {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    };
    document.getElementById('currentTime').textContent =
      now.toLocaleDateString('vi-VN', options);
  }

  // Khởi tạo biểu đồ
  function initCharts() {
    // Biểu đồ doanh thu (line chart)
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    revenueChart = new Chart(revenueCtx, {
      type: 'line',
      data: dashboardData.revenueData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return `Doanh thu: ${context.parsed.y.toLocaleString('vi-VN')} triệu`;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return value.toLocaleString('vi-VN') + 'tr';
              }
            }
          }
        }
      }
    });

    // Biểu đồ đơn hàng (doughnut chart)
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    ordersChart = new Chart(ordersCtx, {
      type: 'doughnut',
      data: dashboardData.ordersData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
            labels: {
              padding: 20,
              usePointStyle: true
            }
          }
        },
        cutout: '70%'
      }
    });
  }

  // Cập nhật biểu đồ doanh thu
  function updateRevenueChart(months) {
    // Trong thực tế, bạn sẽ gọi API để lấy dữ liệu
    console.log('Cập nhật biểu đồ doanh thu cho', months, 'tháng');
    // Tạm thời chỉ log, có thể thêm logic tải dữ liệu mới
  }

  // Cập nhật biểu đồ đơn hàng
  function updateOrdersChart(period) {
    console.log('Cập nhật biểu đồ đơn hàng cho', period);
    // Tạm thời chỉ log
  }

  // Điền dữ liệu vào bảng
  function populateTables() {
    // Top sản phẩm
    const topProductsTable = document.getElementById('topProductsTable');
    if (topProductsTable && topProductsTable.children.length === 0) {
      dashboardData.topProducts.forEach(product => {
        const row = document.createElement('tr');
        row.innerHTML = `
                        <td>${product.name}</td>
                        <td>${product.revenue.toLocaleString('vi-VN')}đ</td>
                        <td>${product.sold}</td>
                    `;
        topProductsTable.appendChild(row);
      });
    }

    // Đơn hàng gần đây
    const recentOrdersTable = document.getElementById('recentOrdersTable');
    if (recentOrdersTable && recentOrdersTable.children.length === 0) {
      dashboardData.recentOrders.forEach(order => {
        const row = document.createElement('tr');
        row.innerHTML = `
                        <td>${order.id}</td>
                        <td>${order.customer}</td>
                        <td>${order.amount.toLocaleString('vi-VN')}đ</td>
                        <td>
                            <span class="status status-${order.status}">
                                ${getStatusText(order.status)}
                            </span>
                        </td>
                    `;
        recentOrdersTable.appendChild(row);
      });
    }
  }

  // Điền hoạt động gần đây
  function populateActivities() {
    const activityList = document.getElementById('activityList');
    if (!activityList) return;

    activityList.innerHTML = '';

    dashboardData.recentActivities.forEach(activity => {
      const item = document.createElement('div');
      item.className = 'activity-item';

      let iconClass = '';
      let content = '';

      switch(activity.type) {
        case 'user':
          iconClass = 'icon-user';
          content = `<div class="activity-title">${activity.title}</div>
                                  <div class="activity-content">
                                      <span>${activity.user}</span>
                                  </div>`;
          break;
        case 'order':
          iconClass = 'icon-order';
          content = `<div class="activity-title">${activity.title} #${activity.orderId}</div>
                                  <div class="activity-content">
                                      <span class="activity-amount">${activity.amount.toLocaleString('vi-VN')}đ</span>
                                  </div>`;
          break;
        case 'product':
          iconClass = 'icon-product';
          content = `<div class="activity-title">${activity.title}</div>
                                  <div class="activity-content">
                                      <span>${activity.product}</span>
                                  </div>`;
          break;
        case 'payment':
          iconClass = 'icon-payment';
          content = `<div class="activity-title">${activity.title}</div>
                                  <div class="activity-content">
                                      <span class="activity-amount">${activity.amount.toLocaleString('vi-VN')}đ</span>
                                  </div>`;
          break;
      }

      item.innerHTML = `
                    <div class="activity-icon ${iconClass}">
                        <i class="fas fa-${activity.type === 'user' ? 'user' :
        activity.type === 'order' ? 'shopping-cart' :
          activity.type === 'product' ? 'box' : 'credit-card'}"></i>
                    </div>
                    <div class="activity-content">
                        ${content}
                        <div class="activity-time">${activity.time}</div>
                    </div>
                `;

      activityList.appendChild(item);
    });
  }

  // Làm mới dashboard
  function refreshDashboard() {
    // Hiển thị loading
    const refreshBtn = document.querySelector('.refresh-btn');
    const originalHtml = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tải...';
    refreshBtn.disabled = true;

    // Giả lập tải dữ liệu mới
    setTimeout(() => {
      // Cập nhật thời gian
      document.getElementById('lastUpdate').textContent = new Date().toLocaleString('vi-VN');

      // Khôi phục nút
      refreshBtn.innerHTML = originalHtml;
      refreshBtn.disabled = false;

      // Hiển thị thông báo
      alert('Dashboard đã được làm mới!');
    }, 1500);
  }

  // Xuất Excel
  function exportToExcel() {
    // Chuẩn bị dữ liệu
    const data = [
      ['Dashboard Báo Cáo', '', '', ''],
      ['Xuất ngày:', new Date().toLocaleDateString('vi-VN'), '', ''],
      ['', '', '', ''],
      ['THỐNG KÊ TỔNG QUAN', '', '', ''],
      ['Chỉ số', 'Giá trị', 'Thay đổi', ''],
      ['Tổng số Users', dashboardData.revenueData.datasets[0].data.reduce((a,b) => a+b, 0), '+12%'],
      ['Tổng sản phẩm', dashboardData.topProducts.length, '+5%'],
      ['Đơn hàng hôm nay', dashboardData.ordersData.datasets[0].data.reduce((a,b) => a+b, 0), '+8%'],
      ['Doanh thu tháng', '50,000,000đ', '+15%'],
      ['', '', '', ''],
      ['TOP SẢN PHẨM BÁN CHẠY', '', '', ''],
      ['Sản phẩm', 'Doanh thu', 'Đã bán', ''],
      ...dashboardData.topProducts.map(p => [p.name, p.revenue.toLocaleString('vi-VN') + 'đ', p.sold]),
      ['', '', '', ''],
      ['ĐƠN HÀNG GẦN ĐÂY', '', '', ''],
      ['Mã đơn', 'Khách hàng', 'Tổng tiền', 'Trạng thái'],
      ...dashboardData.recentOrders.map(o => [o.id, o.customer, o.amount.toLocaleString('vi-VN') + 'đ', getStatusText(o.status)])
    ];

    // Tạo worksheet
    const ws = XLSX.utils.aoa_to_sheet(data);

    // Tạo workbook
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Dashboard Report");

    // Xuất file
    const fileName = `dashboard_report_${new Date().toISOString().split('T')[0]}.xlsx`;
    XLSX.writeFile(wb, fileName);
  }

  // Hàm hỗ trợ
  function getStatusText(status) {
    const statusMap = {
      'completed': 'Hoàn thành',
      'pending': 'Chờ xử lý',
      'processing': 'Đang xử lý',
      'cancelled': 'Đã hủy'
    };
    return statusMap[status] || status;
  }
</script>

<style>
  /* Reset và Base */
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  }

  body {
    background: #f3f4f6;
    color: #1f2937;
    padding: 20px;
  }

  .container {
    max-width: 1400px;
    margin: 0 auto;
  }

  /* Header */
  .dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
  }

  .dashboard-title h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .dashboard-actions {
    display: flex;
    gap: 15px;
  }

  .export-btn, .refresh-btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    border: none;
  }

  .export-btn {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
  }

  .export-btn:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  }

  .refresh-btn {
    background: #3b82f6;
    color: white;
  }

  .refresh-btn:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  }

  /* Stats Grid */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
  }

  .stat-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #eef2f7;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }

  .stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
    border-color: #dbeafe;
  }

  .stat-card h3 {
    margin: 0 0 15px 0;
    font-size: 15px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .stat-card .number {
    font-size: 36px;
    font-weight: 800;
    color: #1f2937;
    line-height: 1;
    margin-bottom: 10px;
  }

  .stat-card .trend {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
  }

  .trend-up { background: rgba(16, 185, 129, 0.1); color: #10b981; }
  .trend-down { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

  /* Charts Grid */
  .charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
    margin-bottom: 30px;
  }

  @media (max-width: 1024px) {
    .charts-grid {
      grid-template-columns: 1fr;
    }
  }

  .chart-container {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #eef2f7;
  }

  .chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .chart-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
  }

  .chart-controls {
    display: flex;
    gap: 10px;
  }

  .time-filter {
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    background: white;
    font-size: 14px;
    cursor: pointer;
  }

  .chart-wrapper {
    position: relative;
    height: 300px;
  }

  /* Tables Grid */
  .tables-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
    margin-bottom: 30px;
  }

  @media (max-width: 1024px) {
    .tables-grid {
      grid-template-columns: 1fr;
    }
  }

  .table-container {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #eef2f7;
  }

  .table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .table-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
  }

  .view-all {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
  }

  .view-all:hover {
    text-decoration: underline;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  th {
    text-align: left;
    padding: 12px 16px;
    background: #f9fafb;
    color: #6b7280;
    font-weight: 600;
    font-size: 14px;
    border-bottom: 2px solid #e5e7eb;
  }

  td {
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
  }

  tr:hover {
    background: #f9fafb;
  }

  .status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
  }

  .status-completed { background: #d1fae5; color: #065f46; }
  .status-pending { background: #fef3c7; color: #92400e; }
  .status-cancelled { background: #fee2e2; color: #991b1b; }

  /* Recent Activity */
  .activity-container {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #eef2f7;
    margin-bottom: 30px;
  }

  .activity-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }

  .activity-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px;
    border-radius: 12px;
    transition: background 0.3s;
  }

  .activity-item:hover {
    background: #f9fafb;
  }

  .activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
  }

  .icon-user { background: #8b5cf6; }
  .icon-order { background: #10b981; }
  .icon-product { background: #f59e0b; }
  .icon-payment { background: #3b82f6; }

  .activity-content {
    flex-grow: 1;
  }

  .activity-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
  }

  .activity-time {
    font-size: 12px;
    color: #9ca3af;
  }

  .activity-amount {
    font-weight: 600;
    color: #10b981;
  }

  /* Footer */
  .dashboard-footer {
    text-align: center;
    padding: 20px;
    color: #6b7280;
    font-size: 14px;
    border-top: 1px solid #e5e7eb;
    margin-top: 30px;
  }
</style>
</body>
</html>
