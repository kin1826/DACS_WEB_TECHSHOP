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

// Xử lý các action CHỈ cho trang users
// Để các trang khác tự xử lý action của mình
if ($currentPage === 'users') {
  $action = isset($_GET['action']) ? $_GET['action'] : '';
  $id = isset($_GET['id']) ? $_GET['id'] : 0;

  if ($action === 'delete_user' && $id) {
    // Xóa user
    $userModel->delete($id);
    header("Location: admin.php?page=users");
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - TechShop</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/admin.css">
  <!-- Thêm vào head hoặc trước khi dùng -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body style="padding: 0 !important;">
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
      <li><a href="admin.php?page=brands" class="<?php echo $currentPage === 'brands' ? 'active' : ''; ?>">
          <i class="fa-solid fa-copyright"></i> Quản lý Hãng
        </a></li>
      <li><a href="admin.php?page=categories" class="<?php echo $currentPage === 'categories' ? 'active' : ''; ?>">
          <i class="fas fa-tags"></i> Quản lý Danh mục
        </a></li>
      <li><a href="admin.php?page=attributes" class="<?php echo $currentPage === 'attributes' ? 'active' : ''; ?>">
          <i class="fa-solid fa-layer-group"></i> Quản lý Thuộc tính
        </a></li>
      <li><a href="admin.php?page=products" class="<?php echo $currentPage === 'products' ? 'active' : ''; ?>">
          <i class="fas fa-box"></i> Quản lý Sản phẩm
        </a></li>
      <li><a href="admin.php?page=flash_sale" class="<?php echo $currentPage === 'flash_sale' ? 'active' : ''; ?>">
          <i class="fa-solid fa-bolt"></i> Quản lý Flash Sale
        </a></li>
      <li><a href="admin.php?page=orders" class="<?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
          <i class="fas fa-shopping-cart"></i> Quản lý Đơn hàng
        </a></li>
      <li><a href="admin.php?page=chat" class="<?php echo $currentPage === 'chat' ? 'active' : ''; ?>">
          <i class="fa-solid fa-comment"></i> Tin nhắn
        </a></li>

      <li><a href="index.php">
          <i class="fas fa-home"></i> Về trang chủ
        </a></li>
      <li><a href="admin.php?page=settings" class="<?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
          <i class="fa-solid fa-gear"></i> Cài đặt trang web
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
          'attributes' => 'Quản lý Thuộc tính',
          'products' => 'Quản lý Sản phẩm',
          'flash_sale' => 'Quản lý Flash Sale',
          'orders' => 'Quản lý Đơn hàng',
          'chat' => "Tin nhắn",
          'settings' => "Cài đặt trang web"
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

<style>
  /* ====== ADMIN CONTAINER ====== */
  .admin-container {
    display: flex;
    min-height: 100vh;
    background: #f5f7fa;
  }

  /* ====== SIDEBAR ====== */
  .sidebar {
    width: 280px;
    background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
    color: white;
    box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
    position: fixed;
    height: 100vh;
    z-index: 100;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .sidebar-header {
    padding: 25px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.2);
  }

  .sidebar-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
  }

  .sidebar-header h2 i {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
  }

  /* ====== SIDEBAR MENU ====== */
  .sidebar-menu {
    list-style: none;
    padding: 20px 0;
    margin: 0;
  }

  .sidebar-menu li {
    margin: 5px 15px;
  }

  .sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    position: relative;
    overflow: hidden;
  }

  .sidebar-menu a i {
    font-size: 18px;
    width: 24px;
    text-align: center;
    color: rgba(255, 255, 255, 0.6);
    transition: color 0.3s ease;
  }

  .sidebar-menu a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    border-radius: 0 4px 4px 0;
  }

  .sidebar-menu a:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    transform: translateX(5px);
  }

  .sidebar-menu a:hover i {
    color: #667eea;
  }

  .sidebar-menu a:hover::before {
    transform: translateX(0);
  }

  .sidebar-menu a.active {
    background: linear-gradient(90deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
    border-left: 4px solid #667eea;
  }

  .sidebar-menu a.active i {
    color: #667eea;
  }

  .sidebar-menu a.active::before {
    display: none;
  }

  /* Logout và về trang chủ */
  .sidebar-menu li:last-child {
    margin-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 20px;
  }

  .sidebar-menu a[href="index.php"] {
    background: rgba(255, 255, 255, 0.05);
  }

  .sidebar-menu a[href="index.php"]:hover {
    background: rgba(255, 255, 255, 0.1);
  }

  .sidebar-menu a[href="logout.php"] {
    background: rgba(239, 68, 68, 0.1);
    color: rgba(239, 68, 68, 0.8);
  }

  .sidebar-menu a[href="logout.php"]:hover {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
  }

  .sidebar-menu a[href="logout.php"] i {
    color: rgba(239, 68, 68, 0.8);
  }

  .sidebar-menu a[href="logout.php"]:hover i {
    color: #ef4444;
  }

  /* ====== MAIN CONTENT ====== */
  .main-content {
    flex: 1;
    margin-left: 280px;
    min-height: 100vh;
  }

  /* ====== ADMIN HEADER ====== */
  .admin-header {
    background: white;
    padding: 25px 35px;
    border-bottom: 1px solid #e4e6ef;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 90;
  }

  .admin-header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .admin-header h1::before {
    content: '';
    width: 4px;
    height: 40px;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    border-radius: 4px;
  }

  /* ====== RESPONSIVE ====== */
  @media (max-width: 1024px) {
    .sidebar {
      transform: translateX(-100%);
      width: 280px;
    }

    .sidebar.show {
      transform: translateX(0);
    }

    .main-content {
      margin-left: 0;
    }

    .mobile-menu-toggle {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 50px;
      height: 50px;
      background: #667eea;
      color: white;
      border-radius: 10px;
      position: fixed;
      bottom: 30px;
      right: 30px;
      z-index: 1000;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
      cursor: pointer;
    }
  }

  @media (max-width: 768px) {
    .admin-header {
      padding: 20px;
    }

    .admin-header h1 {
      font-size: 24px;
    }
  }

  /* ====== ANIMATIONS ====== */
  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateX(-20px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  .sidebar-menu li {
    animation: slideIn 0.3s ease forwards;
  }

  .sidebar-menu li:nth-child(1) { animation-delay: 0.1s; }
  .sidebar-menu li:nth-child(2) { animation-delay: 0.2s; }
  .sidebar-menu li:nth-child(3) { animation-delay: 0.3s; }
  .sidebar-menu li:nth-child(4) { animation-delay: 0.4s; }
  .sidebar-menu li:nth-child(5) { animation-delay: 0.5s; }
  .sidebar-menu li:nth-child(6) { animation-delay: 0.6s; }
  .sidebar-menu li:nth-child(7) { animation-delay: 0.7s; }
  .sidebar-menu li:nth-child(8) { animation-delay: 0.8s; }
  .sidebar-menu li:nth-child(9) { animation-delay: 0.9s; }
  .sidebar-menu li:nth-child(10) { animation-delay: 1.0s; }
</style>

<!-- Thêm nút mobile toggle vào body -->
<div class="mobile-menu-toggle" id="mobileMenuToggle">
  <i class="fas fa-bars"></i>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.sidebar');

    if (mobileMenuToggle && sidebar) {
      mobileMenuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('show');

        // Đổi icon
        const icon = this.querySelector('i');
        if (sidebar.classList.contains('show')) {
          icon.className = 'fas fa-times';
        } else {
          icon.className = 'fas fa-bars';
        }
      });

      // Đóng menu khi click bên ngoài
      document.addEventListener('click', function(event) {
        if (!sidebar.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
          sidebar.classList.remove('show');
          mobileMenuToggle.querySelector('i').className = 'fas fa-bars';
        }
      });
    }

    // Thêm active class cho trang hiện tại
    const currentPage = '<?php echo $currentPage; ?>';
    const menuLinks = document.querySelectorAll('.sidebar-menu a');

    menuLinks.forEach(link => {
      const href = link.getAttribute('href');
      if (href.includes(`page=${currentPage}`)) {
        link.classList.add('active');
      }
    });
  });
</script>
