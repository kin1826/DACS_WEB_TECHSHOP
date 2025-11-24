<?php
session_start();
include_once 'class/user.php';

$isLoggedIn = isset($_SESSION['user_id']);

$userFullInfo = null;
$message = '';

if ($isLoggedIn) {
  $userModel = new User();
  $userFullInfo = $userModel->findById($_SESSION['user_id']);

  // Xử lý form submit
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullName'])) {
    $updateData = [
      'username' => trim($_POST['fullName']),
      'phone' => trim($_POST['phone']),
      'date_of_birth' => !empty($_POST['birthday']) ? $_POST['birthday'] : null,
      'gender' => !empty($_POST['gender']) ? $_POST['gender'] : null
      // KHÔNG cập nhật email
    ];

    // Validate dữ liệu

    if (empty($updateData['username'])) {
      $message = '<div class="error-message">Họ tên không được để trống</div>';
    } else {
      // Thực hiện update
//      var_dump($updateData, $_SESSION['user_id']);
//      exit;
      if ($userModel->update($_SESSION['user_id'], $updateData)) {
        $message = '<div class="success-message">Cập nhật thông tin thành công!</div>';
        // Cập nhật session
        $_SESSION['user_name'] = $updateData['username'];
        // Reload thông tin mới
        $userFullInfo = $userModel->findById($_SESSION['user_id']);
      } else {
        $message = '<div class="error-message">Có lỗi xảy ra khi cập nhật</div>';
      }
    }
  }
} else {
  header("Location: login.php");
  exit();
}
?>


<!doctype html>
<html class="no-js" lang="">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Shop Tech</title>
  <meta name="description" content="">

  <meta property="og:title" content="">
  <meta property="og:type" content="">
  <meta property="og:url" content="">
  <meta property="og:image" content="">
  <meta property="og:image:alt" content="">

  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/icon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="icon.png">

  <link rel="manifest" href="site.webmanifest">
  <!--  css-->
  <link rel="stylesheet" href="css/account.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <meta name="theme-color" content="#fafafa">

</head>

<?php include 'header.php'?>

<body>

<!-- account.php -->
<div class="account-page">
  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <div class="container">
      <nav>
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="account.php" class="active">Tài khoản</a>
      </nav>
    </div>
  </div>

  <div class="container">
    <div class="account-layout">
      <!-- Sidebar -->
      <aside class="account-sidebar">
        <div class="user-profile-card">
          <div class="user-avatar">
            <img src="<?php echo $userFullInfo['avatar']; ?>" alt="User Avatar" id="userAvatar">
            <button class="change-avatar-btn" id="changeAvatarBtn">
              <i class="fas fa-camera"></i>
            </button>
          </div>
          <div class="user-info">
            <h3 id="userName"><?php echo $userFullInfo['username']; ?></h3>
            <p class="user-email" id="userEmail"><?php echo $userFullInfo['email']; ?></p>
            <div class="user-level">
              <span class="level-badge gold"><?php echo $userFullInfo['level_u']; ?></span>
              <span class="points"><?php echo $userFullInfo['points']; ?> điểm</span>
            </div>
          </div>
        </div>

        <nav class="account-menu">
          <a href="#profile" class="menu-item active" data-tab="profile">
            <i class="fas fa-user"></i>
            Thông tin cá nhân
          </a>
          <a href="#orders" class="menu-item" data-tab="orders">
            <i class="fas fa-shopping-bag"></i>
            Đơn hàng của tôi
            <span class="badge">3</span>
          </a>
          <a href="#wishlist" class="menu-item" data-tab="wishlist">
            <i class="fas fa-heart"></i>
            Sản phẩm yêu thích
            <span class="badge">12</span>
          </a>
          <a href="#history" class="menu-item" data-tab="history">
            <i class="fas fa-history"></i>
            Lịch sử xem
          </a>
          <a href="#address" class="menu-item" data-tab="address">
            <i class="fas fa-map-marker-alt"></i>
            Sổ địa chỉ
          </a>
          <a href="#security" class="menu-item" data-tab="security">
            <i class="fas fa-shield-alt"></i>
            Bảo mật
          </a>
        </nav>

        <!-- Quick Stats -->
        <div class="quick-stats">
          <div class="stat-item">
            <i class="fas fa-shopping-cart"></i>
            <div>
              <span class="stat-number">15</span>
              <span class="stat-label">Đơn hàng</span>
            </div>
          </div>
          <div class="stat-item">
            <i class="fas fa-star"></i>
            <div>
              <span class="stat-number">4.8</span>
              <span class="stat-label">Đánh giá</span>
            </div>
          </div>
          <div class="stat-item">
            <i class="fas fa-coins"></i>
            <div>
              <span class="stat-number">1.2M</span>
              <span class="stat-label">Đã chi tiêu</span>
            </div>
          </div>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="account-content">
        <!-- Profile Tab -->
        <div class="tab-content active" id="profile">
          <div class="tab-header">
            <h2>Thông Tin Cá Nhân</h2>
            <p>Quản lý thông tin cá nhân và tài khoản của bạn</p>
          </div>

          <?php if (!empty($message)) echo $message; ?>

          <form class="profile-form" id="profileForm" method="POST" action="">
            <div class="form-section">
              <h3>Thông tin cơ bản</h3>
              <div class="form-row">
                <div class="form-group">
                  <label for="fullName">Họ và tên *</label>
                  <input type="text" id="fullName" name="fullName"
                         value="<?php echo htmlspecialchars(isset($userFullInfo['username']) ? $userFullInfo['username'] : ''); ?>"
                         required>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label for="phone">Số điện thoại *</label>
                  <input type="tel" id="phone" name="phone"
                         value="<?php echo htmlspecialchars(isset($userFullInfo['phone']) ? $userFullInfo['phone'] : ''); ?>"
                         required>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label for="email">Email *</label>
                  <input type="email" id="email" name="email"
                         value="<?php echo htmlspecialchars(isset($userFullInfo['email']) ? $userFullInfo['email'] : ''); ?>"
                         readonly
                         style="background-color: #f8f9fa; cursor: not-allowed;">
                  <small style="color: #666; font-size: 12px;">Email không thể thay đổi</small>
                </div>

              </div>
              <div class="form-row">

                <div class="form-group">
                  <label for="birthday">Ngày sinh</label>
                  <input type="date" id="birthday" name="birthday"
                         value="<?php echo htmlspecialchars(isset($userFullInfo['date_of_birth']) ? $userFullInfo['date_of_birth'] : ''); ?>">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="gender">Giới tính</label>
                  <select id="gender" name="gender" class="gender-select">
                    <option value="">Chọn giới tính</option>
                    <option value="male" <?php echo (isset($userFullInfo['gender']) && $userFullInfo['gender'] == 'male') ? 'selected' : ''; ?>>Nam</option>
                    <option value="female" <?php echo (isset($userFullInfo['gender']) && $userFullInfo['gender'] == 'female') ? 'selected' : ''; ?>>Nữ</option>
                    <option value="other" <?php echo (isset($userFullInfo['gender']) && $userFullInfo['gender'] == 'other') ? 'selected' : ''; ?>>Khác</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h3>Tùy chỉnh tài khoản</h3>
              <div class="form-row">
                <div class="form-group">
                  <label>Ngôn ngữ ưa thích</label>
                  <select name="language">
                    <option value="vi" selected>Tiếng Việt</option>
                    <option value="en">English</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Đơn vị tiền tệ</label>
                  <select name="currency">
                    <option value="vnd" selected>VND - Việt Nam Đồng</option>
                    <option value="usd">USD - Đô la Mỹ</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="checkbox-label">
                  <input type="checkbox" name="newsletter" checked>
                  <span class="checkmark"></span>
                  Nhận bản tin khuyến mãi qua email
                </label>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="save-btn">
                <i class="fas fa-save"></i>
                Lưu thay đổi
              </button>
              <button type="button" class="cancel-btn" onclick="window.location.reload()">
                Hủy bỏ
              </button>
            </div>
          </form>
        </div>

        <!-- Orders Tab -->
        <div class="tab-content" id="orders">
          <div class="tab-header">
            <h2>Đơn Hàng Của Tôi</h2>
            <p>Theo dõi và quản lý đơn hàng của bạn</p>
          </div>

          <div class="orders-timeline">
            <div class="timeline-filter">
              <button class="filter-btn active">Tất cả</button>
              <button class="filter-btn">Chờ xác nhận</button>
              <button class="filter-btn">Đang giao hàng</button>
              <button class="filter-btn">Đã giao</button>
              <button class="filter-btn">Đã hủy</button>
            </div>

            <div class="orders-list">
              <!-- Order 1 -->
              <div class="order-card">
                <div class="order-header">
                  <div class="order-info">
                    <span class="order-id">#DH202412001</span>
                    <span class="order-date">15/12/2024</span>
                  </div>
                  <div class="order-status delivered">
                    <i class="fas fa-check-circle"></i>
                    Đã giao hàng
                  </div>
                </div>
                <div class="order-items">
                  <div class="order-item">
                    <img src="https://images.unsplash.com/photo-1592750475338-74b7b21085ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="Product">
                    <div class="item-info">
                      <h4>iPhone 15 Pro Max 256GB</h4>
                      <span class="item-price">32.990.000₫</span>
                      <span class="item-quantity">x1</span>
                    </div>
                  </div>
                </div>
                <div class="order-footer">
                  <div class="order-total">
                    Tổng cộng: <strong>32.990.000₫</strong>
                  </div>
                  <div class="order-actions">
                    <button class="action-btn">
                      <i class="fas fa-eye"></i>
                      Xem chi tiết
                    </button>
                    <button class="action-btn">
                      <i class="fas fa-redo"></i>
                      Mua lại
                    </button>
                    <button class="action-btn">
                      <i class="fas fa-star"></i>
                      Đánh giá
                    </button>
                  </div>
                </div>
              </div>

              <!-- Order 2 -->
              <div class="order-card">
                <div class="order-header">
                  <div class="order-info">
                    <span class="order-id">#DH202411156</span>
                    <span class="order-date">02/12/2024</span>
                  </div>
                  <div class="order-status shipping">
                    <i class="fas fa-shipping-fast"></i>
                    Đang giao hàng
                  </div>
                </div>
                <div class="order-items">
                  <div class="order-item">
                    <img src="https://images.unsplash.com/photo-1541807084-5c52b6b3adef?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="Product">
                    <div class="item-info">
                      <h4>MacBook Air M2 2023</h4>
                      <span class="item-price">28.990.000₫</span>
                      <span class="item-quantity">x1</span>
                    </div>
                  </div>
                  <div class="order-item">
                    <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="Product">
                    <div class="item-info">
                      <h4>Tai nghe Sony WH-1000XM5</h4>
                      <span class="item-price">7.990.000₫</span>
                      <span class="item-quantity">x1</span>
                    </div>
                  </div>
                </div>
                <div class="order-footer">
                  <div class="order-total">
                    Tổng cộng: <strong>36.980.000₫</strong>
                  </div>
                  <div class="order-actions">
                    <button class="action-btn">
                      <i class="fas fa-eye"></i>
                      Theo dõi
                    </button>
                    <button class="action-btn">
                      <i class="fas fa-headset"></i>
                      Hỗ trợ
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Wishlist Tab -->
        <div class="tab-content" id="wishlist">
          <div class="tab-header">
            <h2>Sản Phẩm Yêu Thích</h2>
            <p>Danh sách các sản phẩm bạn đã thích</p>
          </div>

          <div class="wishlist-grid">
            <!-- Wishlist items will be populated by JavaScript -->
          </div>
        </div>

        <!-- History Tab -->
        <div class="tab-content" id="history">
          <div class="tab-header">
            <h2>Lịch Sử Xem Sản Phẩm</h2>
            <p>Các sản phẩm bạn đã xem gần đây</p>
          </div>

          <div class="history-actions">
            <button class="clear-history" id="clearHistory">
              <i class="fas fa-trash"></i>
              Xóa lịch sử
            </button>
          </div>

          <div class="history-grid">
            <!-- History items will be populated by JavaScript -->
          </div>
        </div>

        <!-- Address Tab -->
        <div class="tab-content" id="address">
          <div class="tab-header">
            <h2>Địa chỉ giao hàng</h2>
            <p>Các địa chỉ của bạn.</p>
          </div>

          <div class="history-actions">
            <button class="clear-history" id="clearHistory">
              <i class="fas fa-trash"></i>
              Xóa lịch sử
            </button>
          </div>

          <div class="history-grid">
            <!-- History items will be populated by JavaScript -->
          </div>
        </div>

        <!-- Recommended Products -->
        <section class="recommended-section">
          <h3>Gợi ý dành riêng cho bạn</h3>
          <p>Dựa trên lịch sử mua hàng và sở thích của bạn</p>
          <div class="recommended-products" id="recommendedProducts">
            <!-- Recommended products will be populated by JavaScript -->
          </div>
        </section>
      </main>
    </div>
  </div>
</div>

<!-- Change Avatar Modal -->
<div class="modal" id="avatarModal">
  <div class="modal-content">
    <button class="modal-close" id="closeAvatarModal">
      <i class="fas fa-times"></i>
    </button>
    <h3>Thay đổi ảnh đại diện</h3>
    <div class="avatar-options">
      <div class="avatar-option">
        <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
        <label for="avatarUpload" class="upload-btn">
          <i class="fas fa-upload"></i>
          Tải ảnh lên
        </label>
      </div>
      <div class="avatar-option">
        <div class="default-avatars">
          <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="Avatar 1">
          <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="Avatar 2">
          <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="Avatar 3">
        </div>
      </div>
    </div>
    <div class="modal-actions">
      <button class="save-btn" id="saveAvatar">Lưu thay đổi</button>
      <button class="cancel-btn" id="cancelAvatar">Hủy bỏ</button>
    </div>
  </div>
</div>

<style>

</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded - Starting account page initialization');

    // Sample data
    const wishlistItems = [
      {
        id: 1,
        name: "Samsung Galaxy S24 Ultra",
        image: "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80",
        price: 24990000
      },
      {
        id: 2,
        name: "iPad Pro 12.9 M2",
        image: "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80",
        price: 27990000
      }
    ];

    const historyItems = [
      {
        id: 1,
        name: "Loa Bluetooth JBL Charge 5",
        image: "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80",
        price: 2399000,
        viewedAt: "2 giờ trước"
      },
      {
        id: 2,
        name: "Bàn phím cơ Keychron K8",
        image: "https://images.unsplash.com-1541140532154-b024d705b90a?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80",
        price: 1890000,
        viewedAt: "1 ngày trước"
      }
    ];

    const recommendedProducts = [
      {
        id: 1,
        name: "AirPods Pro 2",
        image: "https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80",
        price: 6990000
      },
      {
        id: 2,
        name: "MacBook Pro 14",
        image: "https://images.unsplash.com/photo-1517336714731-489689fd1ca8?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80",
        price: 42990000
      }
    ];

    // Get DOM elements
    const menuItems = document.querySelectorAll('.menu-item');
    const tabContents = document.querySelectorAll('.tab-content');

    console.log('Found menu items:', menuItems.length);
    console.log('Found tab contents:', tabContents.length);

    // Check URL parameter on page load
    function checkUrlTab() {
      console.log('Checking URL parameters...');
      const urlParams = new URLSearchParams(window.location.search);
      const tabParam = urlParams.get('tab');

      console.log('URL tab parameter:', tabParam);

      if (tabParam) {
        console.log('Tab parameter found:', tabParam);

        // Remove active class from all items
        menuItems.forEach(i => i.classList.remove('active'));
        tabContents.forEach(tab => tab.classList.remove('active'));

        // Add active class to specified tab
        const targetItem = document.querySelector(`.menu-item[data-tab="${tabParam}"]`);
        const targetTab = document.getElementById(tabParam);

        console.log('Target item:', targetItem);
        console.log('Target tab:', targetTab);

        if (targetItem && targetTab) {
          targetItem.classList.add('active');
          targetTab.classList.add('active');
          console.log('Successfully activated tab:', tabParam);

          // Load tab data if needed
          switch(tabParam) {
            case 'orders':
              loadOrders();
              break;
            case 'wishlist':
              loadWishlist();
              break;
            case 'history':
              loadHistory();
              break;
            case 'profile':
              console.log('Profile tab activated');
              break;
          }
        } else {
          console.log('Target not found, using default tab');
          activateDefaultTab();
        }
      } else {
        console.log('No tab parameter, using default');
        activateDefaultTab();
      }
    }

    // Activate default tab (profile)
    function activateDefaultTab() {
      const defaultItem = document.querySelector('.menu-item[data-tab="profile"]');
      const defaultTab = document.getElementById('profile');

      if (defaultItem && defaultTab) {
        defaultItem.classList.add('active');
        defaultTab.classList.add('active');
        console.log('Default tab activated: profile');
      }
    }

    // Tab switching with URL update
    menuItems.forEach(item => {
      item.addEventListener('click', function(e) {
        e.preventDefault();

        const tabId = this.getAttribute('data-tab');
        console.log('Menu item clicked:', tabId);

        // Update URL without reloading page
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.pushState({}, '', url);

        switchTab(tabId);
      });
    });

    // Switch tab function
    function switchTab(tabId) {
      console.log('Switching to tab:', tabId);

      // Remove active class from all items
      menuItems.forEach(i => i.classList.remove('active'));
      tabContents.forEach(tab => tab.classList.remove('active'));

      // Add active class to clicked item
      const targetItem = document.querySelector(`.menu-item[data-tab="${tabId}"]`);
      const targetTab = document.getElementById(tabId);

      if (targetItem && targetTab) {
        targetItem.classList.add('active');
        targetTab.classList.add('active');
        console.log('Successfully switched to tab:', tabId);

        // Load tab data if needed
        switch(tabId) {
          case 'orders':
            loadOrders();
            break;
          case 'wishlist':
            loadWishlist();
            break;
          case 'history':
            loadHistory();
            break;
          case 'profile':
            console.log('Profile tab loaded');
            break;
        }
      } else {
        console.error('Tab not found:', tabId);
      }
    }

    // Load wishlist
    function loadWishlist() {
      console.log('Loading wishlist...');
      const wishlistGrid = document.querySelector('.wishlist-grid');
      if (!wishlistGrid) {
        console.error('Wishlist grid not found!');
        return;
      }

      wishlistGrid.innerHTML = '';

      wishlistItems.forEach(item => {
        const productCard = createProductCard(item, true);
        wishlistGrid.appendChild(productCard);
      });
      console.log('Wishlist loaded with', wishlistItems.length, 'items');
    }

    // Load history
    function loadHistory() {
      console.log('Loading history...');
      const historyGrid = document.querySelector('.history-grid');
      if (!historyGrid) {
        console.error('History grid not found!');
        return;
      }

      historyGrid.innerHTML = '';

      historyItems.forEach(item => {
        const productCard = createProductCard(item, false, true);
        historyGrid.appendChild(productCard);
      });
      console.log('History loaded with', historyItems.length, 'items');
    }

    // Load orders (dummy function)
    function loadOrders() {
      console.log('Loading orders...');
      // Orders are already in HTML, just log
      const ordersList = document.querySelector('.orders-list');
      if (ordersList) {
        console.log('Orders list found with', ordersList.children.length, 'orders');
      }
    }

    // Load recommended products
    function loadRecommendedProducts() {
      console.log('Loading recommended products...');
      const recommendedGrid = document.getElementById('recommendedProducts');
      if (!recommendedGrid) {
        console.error('Recommended products grid not found!');
        return;
      }

      recommendedGrid.innerHTML = '';

      recommendedProducts.forEach(item => {
        const productCard = createProductCard(item, false);
        recommendedGrid.appendChild(productCard);
      });
      console.log('Recommended products loaded with', recommendedProducts.length, 'items');
    }

    // Create product card
    function createProductCard(item, isWishlist = false, isHistory = false) {
      const div = document.createElement('div');
      div.className = 'product-card';

      let extraInfo = '';
      if (isHistory) {
        extraInfo = `<span class="viewed-time">${item.viewedAt}</span>`;
      }

      div.innerHTML = `
            <img src="${item.image}" alt="${item.name}" onerror="this.src='https://via.placeholder.com/200x120?text=Product+Image'">
            <h4>${item.name}</h4>
            <span class="product-price">${formatPrice(item.price)}</span>
            ${extraInfo}
            <div class="product-actions">
                <button class="product-btn add-cart-btn" data-id="${item.id}">
                    <i class="fas fa-shopping-cart"></i>
                    Thêm giỏ
                </button>
                <button class="product-btn remove-btn" data-id="${item.id}">
                    <i class="fas fa-trash"></i>
                    ${isWishlist ? 'Xóa' : 'Xóa'}
                </button>
            </div>
        `;
      return div;
    }

    // Format price
    function formatPrice(price) {
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
      }).format(price);
    }

    // Avatar modal
    const avatarModal = document.getElementById('avatarModal');
    const changeAvatarBtn = document.getElementById('changeAvatarBtn');
    const closeAvatarModal = document.getElementById('closeAvatarModal');
    const saveAvatar = document.getElementById('saveAvatar');
    const cancelAvatar = document.getElementById('cancelAvatar');
    const avatarUpload = document.getElementById('avatarUpload');
    const userAvatar = document.getElementById('userAvatar');

    if (changeAvatarBtn) {
      changeAvatarBtn.addEventListener('click', function() {
        avatarModal.classList.add('show');
      });
    }

    if (closeAvatarModal) {
      closeAvatarModal.addEventListener('click', function() {
        avatarModal.classList.remove('show');
      });
    }

    if (cancelAvatar) {
      cancelAvatar.addEventListener('click', function() {
        avatarModal.classList.remove('show');
      });
    }

    if (saveAvatar) {
      saveAvatar.addEventListener('click', function() {
        alert('Ảnh đại diện đã được cập nhật!');
        avatarModal.classList.remove('show');
      });
    }

    if (avatarUpload) {
      avatarUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            userAvatar.src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      });
    }

    // Default avatar selection
    document.querySelectorAll('.default-avatars img').forEach(img => {
      img.addEventListener('click', function() {
        userAvatar.src = this.src;
      });
    });

    // Clear history
    const clearHistoryBtn = document.getElementById('clearHistory');
    if (clearHistoryBtn) {
      clearHistoryBtn.addEventListener('click', function() {
        if (confirm('Bạn có chắc muốn xóa toàn bộ lịch sử xem?')) {
          const historyGrid = document.querySelector('.history-grid');
          if (historyGrid) {
            historyGrid.innerHTML = '<p class="empty-message">Lịch sử xem đã được xóa</p>';
          }
        }
      });
    }

    // Profile form submission
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
      profileForm.addEventListener('submit', function(e) {
        const saveBtn = this.querySelector('.save-btn');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
        saveBtn.disabled = true;

        setTimeout(() => {
          saveBtn.innerHTML = originalText;
          saveBtn.disabled = false;
        }, 2000);
      });
    }

    // Product actions
    document.addEventListener('click', function(e) {
      if (e.target.closest('.add-cart-btn')) {
        const productId = e.target.closest('.add-cart-btn').dataset.id;
        alert(`Sản phẩm đã được thêm vào giỏ hàng! (ID: ${productId})`);
      }

      if (e.target.closest('.remove-btn')) {
        const productId = e.target.closest('.remove-btn').dataset.id;
        const productCard = e.target.closest('.product-card');

        if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
          productCard.style.opacity = '0';
          setTimeout(() => {
            productCard.remove();
          }, 300);
        }
      }
    });

    // Order filter
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const status = this.textContent.toLowerCase();
        if (status !== 'tất cả') {
          console.log('Filtering orders by:', status);
        }
      });
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
      console.log('Popstate event - checking URL');
      checkUrlTab();
    });

    // Initialize everything
    console.log('Starting initialization...');
    checkUrlTab();
    loadRecommendedProducts();
    console.log('Account page initialization complete!');
  });
</script>

<?php include 'footer.php'?>

</body>
</html>
