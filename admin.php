<?php
session_start();
require_once 'class/user.php';

// Check admin permission
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
  header("Location: login.php");
  exit();
}

$userModel = new User();
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Xử lý các action
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($action === 'delete_user' && $id) {
  // Xóa user
  $userModel->delete($id);
  header("Location: admin.php?page=users");
  exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - TechShop</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .admin-container {
      display: flex;
      min-height: 100vh;
    }

    .sidebar {
      width: 250px;
      background: #2c3e50;
      color: white;
      padding: 20px 0;
    }

    .sidebar-header {
      padding: 0 20px 20px;
      border-bottom: 1px solid #34495e;
    }

    .sidebar-menu {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .sidebar-menu li a {
      display: block;
      padding: 12px 20px;
      color: #bdc3c7;
      text-decoration: none;
      transition: all 0.3s;
    }

    .sidebar-menu li a:hover,
    .sidebar-menu li a.active {
      background: #34495e;
      color: white;
    }

    .sidebar-menu li a i {
      width: 20px;
      margin-right: 10px;
    }

    .main-content {
      flex: 1;
      background: #ecf0f1;
      padding: 20px;
    }

    .admin-header {
      background: white;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 20px;
    }

    .stat-card {
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .stat-card h3 {
      margin: 0 0 10px 0;
      color: #7f8c8d;
      font-size: 14px;
    }

    .stat-card .number {
      font-size: 32px;
      font-weight: bold;
      color: #2c3e50;
    }

    .data-table {
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .data-table table {
      width: 100%;
      border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ecf0f1;
    }

    .data-table th {
      background: #34495e;
      color: white;
      font-weight: 600;
    }

    .data-table tr:hover {
      background: #f8f9fa;
    }

    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      font-size: 14px;
    }

    .btn-danger {
      background: #e74c3c;
      color: white;
    }

    .btn-edit {
      background: #3498db;
      color: white;
    }

    .pagination {
      display: flex;
      justify-content: center;
      padding: 20px;
      gap: 5px;
    }

    .page-link {
      padding: 8px 12px;
      border: 1px solid #bdc3c7;
      text-decoration: none;
      color: #2c3e50;
      border-radius: 4px;
    }

    .page-link.active {
      background: #3498db;
      color: white;
      border-color: #3498db;
    }
  </style>
</head>
<body>
<div class="admin-container">
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-header">
      <h2><i class="fas fa-cogs"></i> Admin Panel</h2>
    </div>
    <ul class="sidebar-menu">
      <li><a href="admin.php?page=dashboard" class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a></li>
      <li><a href="admin.php?page=users" class="<?php echo $currentPage === 'users' ? 'active' : ''; ?>">
          <i class="fas fa-users"></i> Quản lý Users
        </a></li>
      <li><a href="admin.php?page=brands" class="<?php echo $currentPage === 'users' ? 'active' : ''; ?>">
          <i class="fas fa-users"></i> Quản lý Hãng
        </a></li>
      <li><a href="admin.php?page=categories" class="<?php echo $currentPage === 'categories' ? 'active' : ''; ?>">
          <i class="fas fa-tags"></i> Danh mục
        </a></li>
      <li><a href="admin.php?page=products" class="<?php echo $currentPage === 'products' ? 'active' : ''; ?>">
          <i class="fas fa-box"></i> Quản lý Sản phẩm
        </a></li>
      <li><a href="admin.php?page=orders" class="<?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
          <i class="fas fa-shopping-cart"></i> Quản lý Đơn hàng
        </a></li>

      <li><a href="index.php">
          <i class="fas fa-home"></i> Về trang chủ
        </a></li>
      <li><a href="logout.php">
          <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="admin-header">
      <h1>
        <?php
        $pageTitles = [
          'dashboard' => 'Dashboard',
          'users' => 'Quản lý Người dùng',
          'brands' => 'Quản lý hãng',
          'categories' => 'Quản lý Danh mục',
          'products' => 'Quản lý Sản phẩm',
          'orders' => 'Quản lý Đơn hàng',
        ];
        echo isset($pageTitles[$currentPage]) ? $pageTitles[$currentPage] : 'Dashboard';
        ?>
      </h1>
    </div>

    <?php
    // Include page content
    $pageFile = "admin/{$currentPage}.php";
    if (file_exists($pageFile)) {
      include $pageFile;
    } else {
      include 'admin/dashboard.php';
    }
    ?>
  </div>
</div>
</body>
</html>
