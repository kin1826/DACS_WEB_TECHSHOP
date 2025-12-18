<?php
session_start();
include_once 'class/user.php';
require_once 'class/user_address.php';
require_once 'class/order.php';
require_once 'class/order_item.php';
require_once 'class/order_history.php';
require_once 'class/product.php';

$addressModel = new UserAddress();
$orderModel = new OrderModel();
$orderItemModel = new OrderItemModel();
$orderHistoryModel = new OrderHistoryModel();
$productModel = new Product();

$isLoggedIn = isset($_SESSION['user_id']);

$userFullInfo = null;
$message = '';

$user_id = $_SESSION['user_id'];
$addressList = [];

$countOrder = $orderModel->countOrder($user_id);
$countWishlist = $productModel->countWishlist($user_id);

if ($isLoggedIn) {
  $userModel = new User();
  $userFullInfo = $userModel->findById($_SESSION['user_id']);

  $addressList = $addressModel->getAddressesByUser($user_id);

  // Order setup
  $orderList = $orderModel->getOrdersWithItemsByUserId($user_id);
  $wishlist = $productModel->getWishList($user_id);

  $wishlistProduct = [];
  foreach ($wishlist as $wish) {
    $productId = (int)$wish['product_id'];

    $product = $productModel->getNameAndImageProductById($productId);

    if ($product) {
      $wishlistProduct[] = [
        'id'   => $productId,
        'name' => $product['name_pr'],
        'price' => $product['sale_price'],
        'image'        => $product['image_url']
          ? 'img/adminUP/products/' . $product['image_url']
          : 'img/no-image.png',
        'alt'          => $product['alt_text'] ?? $product['product_name']
      ];
    }
  }

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
<?php include 'cornerButton.php'?>

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
            <span class="badge"><?= $countOrder ?? 0 ?></span>
          </a>
          <a href="#wishlist" class="menu-item" data-tab="wishlist">
            <i class="fas fa-heart"></i>
            Sản phẩm yêu thích
            <span class="badge"><?= $countWishlist ?? 0 ?></span>
          </a>
          <a href="#address" class="menu-item" data-tab="address">
            <i class="fas fa-map-marker-alt"></i>
            Sổ địa chỉ
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
          <div class="section">
            <h2 class="section-title">
              <i class="fas fa-map-marker-alt"></i>
              Địa chỉ giao hàng
            </h2>

            <div class="address-section">
              <!-- Địa chỉ sẽ được thêm bằng JavaScript -->
              <div id="address-list"></div>

              <button type="button" class="add-address-btn" id="show-address-form">
                <i class="fas fa-plus"></i> Thêm địa chỉ mới
              </button>

              <button type="button" class="location-btn" id="use-location">
                <i class="fas fa-location-arrow"></i> Sử dụng vị trí hiện tại
              </button>

              <div class="location-loading" id="location-loading">
                <i class="fas fa-spinner fa-spin"></i> Đang lấy vị trí...
              </div>
              <div class="location-error" id="location-error"></div>
              <div class="location-success" id="location-success"></div>

              <div class="address-form" id="address-form" style="display: none;">
                <div class="form-group">
                  <label for="recipient_name">Tên người nhận</label>
                  <input type="text" id="recipient_name" name="recipient_name" required>
                </div>

                <div class="form-group">
                  <label for="address_name">Tên địa chỉ (ví dụ: Nhà, Công ty)</label>
                  <input type="text" id="address_name" name="address_name" required>
                </div>

                <div class="form-group">
                  <label for="address_phone">Số điện thoại</label>
                  <input type="tel" id="address_phone" name="address_phone" required>
                </div>

                <div class="form-group">
                  <label for="address_input">Địa chỉ chi tiết</label>
                  <input type="text" id="address_input" name="address_input" placeholder="Nhập địa chỉ đầy đủ" required>
                </div>

                <div class="form-actions">
                  <button type="button" class="btn btn-primary" id="save-address">Lưu địa chỉ</button>
                  <button type="button" class="btn btn-secondary" id="cancel-address-form">Hủy</button>
                </div>
              </div>
            </div>
          </div>

          <div class="history-actions">
            <button class="clear-history" id="clearHistory">
              <i class="fas fa-trash"></i>
              Xóa lịch sử
            </button>
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

<div id="orderDetailModal" class="modal_item hidden">
  <div class="modal_item-content">
    <span class="close-btn"><i class="fa-solid fa-xmark" style="color: #ff0000;"></i></span>
    <div id="orderDetailBody"></div>
  </div>
</div>
<!-- Modal đánh giá sản phẩm -->
<div id="reviewModal" class="modal_item hidden">
  <div class="modal_item-content">
    <span class="close-btn review-close-btn"><i class="fa-solid fa-xmark" style="color: #ff0000;"></i></span>
    <div id="reviewModalBody"></div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded - Starting account page initialization');

    // Sample data
    const wishlistItems = <?php echo json_encode($wishlistProduct, JSON_UNESCAPED_UNICODE); ?>;
    console.log(wishlistItems);

    const historyItems = [

    ];

    const recommendedProducts = [

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
                <a class="product-btn add-cart-btn" href="product_detail.php?id=${item.id}" style="text-decoration: none">
                    <i class="fas fa-shopping-cart"></i>
                    Xem chi tiết
                </a>
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

<script>
  // ========== DỮ LIỆU ĐỊA CHỈ MẪU ==========
  const sampleAddresses = <?php echo json_encode($addressList, JSON_UNESCAPED_UNICODE); ?>;

  // ========== BIẾN TOÀN CỤC ==========
  let addresses = [...sampleAddresses];
  let selectedAddress = addresses.find(addr => addr.is_default) || addresses[0];

  // ========== HÀM HIỂN THỊ DANH SÁCH ĐỊA CHỈ ==========
  function renderAddressList() {
    const addressListContainer = document.getElementById('address-list');
    addressListContainer.innerHTML = '';

    addresses.forEach(address => {
      console.log(address);
      const addressElement = document.createElement('div');
      addressElement.className = `address-option ${address.is_default === "1" ? 'selected' : ''}`;
      addressElement.innerHTML = `
            <div class="address-radio">
                <input type="radio" name="address_id" value="${address.id}"
                       ${address.is_default === "1" ? 'checked' : ''}
                       onchange="selectAddress(${address.id})">
            </div>
            <div class="address-details">
                <h4>${address.recipient_name}</h4>
                <h5>${address.title}</h5>
                <p>${address.address}</p>
                <p>Điện thoại: ${address.phone}</p>
            </div>
            <div class="address-actions">
                <button class="set-default-btn" onclick="setDefaultAddress(${address.id})" ${address.isDefault ? 'disabled' : ''} title="Đặt làm mặc định" >
                    <i class="fas fa-star"></i>
                </button>
                <button class="delete-address-btn" onclick="deleteAddress(${address.id})"
                        title="Xóa địa chỉ">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
      addressListContainer.appendChild(addressElement);
    });

    // Cập nhật địa chỉ đã chọn vào form
    if (selectedAddress) {
      document.getElementById('selected-address').value =
        `${selectedAddress.name}: ${selectedAddress.address} - ${selectedAddress.phone}`;
    }
  }

  // ========== HÀM CHỌN ĐỊA CHỈ ==========
  function selectAddress(addressId) {
    addresses.forEach(address => {
      address.isDefault = (address.id === addressId);
    });

    selectedAddress = addresses.find(addr => addr.id === addressId);
    renderAddressList();
  }

  // ========== HÀM THÊM ĐỊA CHỈ MỚI ==========
  async function addNewAddress() {
    const recipient_name = document.getElementById('recipient_name').value.trim();
    const name = document.getElementById('address_name').value.trim();
    const phone = document.getElementById('address_phone').value.trim();
    const address = document.getElementById('address_input').value.trim();

    if (!recipient_name || !name || !phone || !address) {
      alert('Vui lòng điền đầy đủ thông tin');
      return;
    }

    // Kiểm tra số điện thoại
    if (!/^\d{10,11}$/.test(phone)) {
      alert('Số điện thoại không hợp lệ');
      return;
    }

    // const newAddress = {
    //   id: addresses.length + 1,
    //   recipient_name,
    //   name,
    //   phone,
    //   address,
    //   isDefault: false
    // };
    //
    // addresses.push(newAddress);

    const formData = new FormData();
    formData.append("action", "add");
    formData.append("recipient_name", recipient_name);
    formData.append("name", name);
    formData.append("phone", phone);
    formData.append("address", address);

    const res = await fetch("address_action.php", {
      method: "POST",
      body: formData
    });

    const data = await res.json();

    if (data.success) {
      addresses.push(data.address);   // nhận address vừa tạo từ PHP
      selectedAddress = data.address;
      renderAddressList();
      document.getElementById('address-form').style.display = 'none';
      alert("Đã thêm địa chỉ");
    } else {
      alert(data.message);
    }

    // Ẩn form
    document.getElementById('address-form').style.display = 'none';

    // Reset form
    document.getElementById('address_name').value = '';
    document.getElementById('address_phone').value = '';
    document.getElementById('address_input').value = '';

    // Chọn địa chỉ mới

    alert('Đã thêm địa chỉ mới thành công!');
  }

  async function setDefaultAddress(id) {
    const formData = new FormData();
    formData.append("action", "set_default");
    formData.append("id", id);

    const res = await fetch("address_action.php", {
      method: "POST",
      body: formData
    });

    const data = await res.json();

    if (data.success) {
      const nid = Number(id);

      addresses.forEach(a => {
        a.is_default = (Number(a.id) === nid ? "1" : "0"); // GIỮ KIỂU CHUỖI CHO ĐỒNG BỘ
      });

      selectedAddress = addresses.find(a => Number(a.is_default) === 1)
        || addresses[0] || null;

      renderAddressList();
    } else {
      alert(data.message);
    }
  }

  async function deleteAddress(id) {
    if (!confirm("Xóa địa chỉ này?")) return;

    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("id", id);

    const res = await fetch("address_action.php", {
      method: "POST",
      body: formData
    });

    const data = await res.json();

    if (data.success) {
      const nid = Number(id);

      addresses = addresses.filter(a => Number(a.id) !== nid);

      if (selectedAddress && Number(selectedAddress.id) === nid) {
        selectedAddress = addresses.find(a => Number(a.is_default) === 1)
          || addresses[0] || null;
      }

      renderAddressList();
    } else {
      alert(data.message);
    }
  }


  // ========== HÀM SỬ DỤNG VỊ TRÍ HIỆN TẠI ==========
  function useCurrentLocation() {
    const loadingElement = document.getElementById('location-loading');
    const errorElement = document.getElementById('location-error');
    const successElement = document.getElementById('location-success');

    loadingElement.style.display = 'block';
    errorElement.textContent = '';
    successElement.textContent = '';

    if (!navigator.geolocation) {
      loadingElement.style.display = 'none';
      errorElement.textContent = 'Trình duyệt của bạn không hỗ trợ định vị';
      return;
    }

    navigator.geolocation.getCurrentPosition(
      async (position) => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        try {
          // Sử dụng OpenStreetMap Nominatim API để lấy địa chỉ
          const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
          const data = await response.json();

          if (data && data.display_name) {
            const address = data.display_name;

            // Tự động điền vào form địa chỉ
            document.getElementById('address_input').value = address;
            document.getElementById('address_name').value = 'Vị trí hiện tại';

            loadingElement.style.display = 'none';
            successElement.textContent = 'Đã lấy địa chỉ từ vị trí của bạn';

            // Hiển thị form địa chỉ
            document.getElementById('address-form').style.display = 'block';
          } else {
            throw new Error('Không thể lấy địa chỉ');
          }
        } catch (error) {
          loadingElement.style.display = 'none';
          errorElement.textContent = 'Không thể lấy địa chỉ từ vị trí của bạn';
        }
      },
      (error) => {
        loadingElement.style.display = 'none';

        switch(error.code) {
          case error.PERMISSION_DENIED:
            errorElement.textContent = 'Bạn đã từ chối quyền truy cập vị trí';
            break;
          case error.POSITION_UNAVAILABLE:
            errorElement.textContent = 'Thông tin vị trí không khả dụng';
            break;
          case error.TIMEOUT:
            errorElement.textContent = 'Yêu cầu vị trí đã hết thời gian';
            break;
          default:
            errorElement.textContent = 'Đã xảy ra lỗi khi lấy vị trí';
        }
      }
    );
  }

  // ========== XỬ LÝ SỰ KIỆN KHI TRANG TẢI XONG ==========
  document.addEventListener('DOMContentLoaded', function() {
    // Hiển thị danh sách địa chỉ ban đầu
    renderAddressList();

    // Xử lý hiển thị/ẩn form địa chỉ
    document.getElementById('show-address-form').addEventListener('click', function() {
      document.getElementById('address-form').style.display = 'block';
    });

    document.getElementById('cancel-address-form').addEventListener('click', function() {
      document.getElementById('address-form').style.display = 'none';
    });

    // Xử lý sử dụng vị trí
    document.getElementById('use-location').addEventListener('click', useCurrentLocation);
  });

  // Lấy các phần tử DOM
  const showAddressFormBtn = document.getElementById('show-address-form');
  const addressForm = document.getElementById('address-form');
  const cancelAddressFormBtn = document.getElementById('cancel-address-form');

  // DEBUG: Kiểm tra xem có tìm thấy các phần tử không
  console.log('Show button:', showAddressFormBtn);
  console.log('Address form:', addressForm);
  console.log('Cancel button:', cancelAddressFormBtn);

  // Xử lý hiển thị form địa chỉ
  if (showAddressFormBtn) {
    showAddressFormBtn.addEventListener('click', function() {
      console.log('Show address form clicked');
      if (addressForm) {
        addressForm.style.display = 'block';
        console.log('Form displayed');

        // Focus vào ô đầu tiên
        const firstInput = document.getElementById('address_name');
        if (firstInput) {
          firstInput.focus();
        }
      }
    });
  }

  // Xử lý ẩn form địa chỉ
  if (cancelAddressFormBtn) {
    cancelAddressFormBtn.addEventListener('click', function() {
      console.log('Cancel address form clicked');
      if (addressForm) {
        addressForm.style.display = 'none';
        console.log('Form hidden');
      }
    });
  }

  // Xử lý lưu địa chỉ
  const saveAddressBtn = document.getElementById('save-address');
  if (saveAddressBtn) {
    saveAddressBtn.addEventListener('click', function(e) {
      e.preventDefault();
      console.log('Save address clicked');
      // Gọi hàm addNewAddress đã có
      if (typeof addNewAddress === 'function') {
        addNewAddress();
      }
    });
  }

  // Xử lý sử dụng vị trí
  const useLocationBtn = document.getElementById('use-location');
  if (useLocationBtn) {
    useLocationBtn.addEventListener('click', function() {
      console.log('Use location clicked');
      // Gọi hàm useCurrentLocation đã có
      if (typeof useCurrentLocation === 'function') {
        useCurrentLocation();
      }
    });
  }

  // Kiểm tra xem form có đang ẩn không và hiển thị thông báo
  if (addressForm && addressForm.style.display === 'none') {
    console.log('Address form is initially hidden');
  }
</script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    renderOrders(ordersData);

    document.addEventListener('click', e => {
      if (e.target.closest('.view-detail-btn')) {
        const orderId = e.target.closest('.view-detail-btn').dataset.id;
        const order = ordersData.find(o => o.order_id === orderId);
        showOrderDetail(order);
      }
    });

    document.addEventListener('click', e => {
      if (e.target.closest('.close-btn')) {
        document.getElementById('orderDetailModal').classList.add('hidden');
      }
    });


    document.getElementById('orderDetailModal').addEventListener('click', e => {
      if (e.target.id === 'orderDetailModal') {
        e.target.classList.add('hidden');
      }
    });

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        document.getElementById('orderDetailModal').classList.add('hidden');
      }
    });
    openModal();
    closeModal();
  });

  function showOrderDetail(order) {
    const body = document.getElementById('orderDetailBody');

    body.innerHTML = `
    <h3>Đơn hàng #${order.order_number}</h3>
    <p>Trạng thái: <strong>${order.order_status}</strong></p>
    <p>Ngày đặt: ${new Date(order.order_date).toLocaleString('vi-VN')}</p>

    <hr>

    ${order.items.map(item => `
      <div class="detail-item">
        <img src="${item.image}" width="60">
        <div>
          <div>${item.product_name}</div>
          <div>${formatCurrency(item.price)} × ${item.quantity}</div>
        </div>
      </div>
    `).join('')}

    <hr>
    <h4>Tổng tiền: ${formatCurrency(order.total_amount)}</h4>
  `;

    document.getElementById('orderDetailModal').classList.remove('hidden');
  }

  const ordersData = <?php echo json_encode($orderList, JSON_UNESCAPED_UNICODE); ?>;

  function getStatusInfo(status) {
    switch (status) {
      case 'pending':
        return { text: 'Chờ xác nhận', class: 'pending', icon: 'fa-clock' };
      case 'processing':
      case 'shipping':
        return { text: 'Đang giao hàng', class: 'shipping', icon: 'fa-shipping-fast' };
      case 'delivered':
        return { text: 'Đã giao hàng', class: 'delivered', icon: 'fa-check-circle' };
      case 'cancelled':
        return { text: 'Đã hủy', class: 'cancelled', icon: 'fa-times-circle' };
      default:
        return { text: 'Không xác định', class: 'pending', icon: 'fa-question-circle' };
    }
  }

  const ordersListEl = document.querySelector('.orders-list');

  function formatCurrency(number) {
    return number.toLocaleString('vi-VN') + '₫';
  }

  function renderOrders(orders) {
    ordersListEl.innerHTML = '';

    if (!orders.length) {
      ordersListEl.innerHTML = '<p>Chưa có đơn hàng nào.</p>';
      return;
    }

    orders.forEach(order => {
      const statusInfo = getStatusInfo(order.order_status);

      const itemsHTML = order.items.map(item => `
      <div class="order-item">
        <img src="${item.image}" alt="${item.product_name}">
        <div class="item-info">
          <h4>${item.product_name}</h4>
          <span class="item-price">${formatCurrency(item.price)}</span>
          <span class="item-quantity">x${item.quantity}</span>
        </div>
      </div>
    `).join('');

      const reviewButton = order.order_status === 'delivered'
        ? `
          <button class="action-btn review-btn" data-order-id="${order.order_id}">
            <i class="fas fa-star"></i>
            Đánh giá
          </button>
        `
        : '';

      const orderHTML = `
      <div class="order-card" data-status="${order.order_status}">
        <div class="order-header">
          <div class="order-info">
            <span class="order-id">#${order.order_number}</span>
            <span class="order-date">${new Date(order.order_date).toLocaleDateString('vi-VN')}</span>
          </div>
          <div class="order-status ${statusInfo.class}">
            <i class="fas ${statusInfo.icon}"></i>
            ${statusInfo.text}
          </div>
        </div>

        <div class="order-items">
          ${itemsHTML}
        </div>

        <div class="order-footer">
          <div class="order-total">
            Tổng cộng: <strong>${formatCurrency(order.total_amount)}</strong>
          </div>
          <div class="order-actions">
            ${reviewButton}
            <button class="action-btn view-detail-btn" data-id="${order.order_id}">
              <i class="fas fa-eye"></i>
              Xem chi tiết
            </button>
          </div>
        </div>
      </div>
    `;

      ordersListEl.insertAdjacentHTML('beforeend', orderHTML);
    });
  }

  const filterButtons = document.querySelectorAll('.filter-btn');

  filterButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      filterButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const label = btn.textContent.trim();

      if (label === 'Tất cả') {
        renderOrders(ordersData);
        return;
      }

      const statusMap = {
        'Chờ xác nhận': 'pending',
        'Đang giao hàng': 'shipping',
        'Đã giao': 'delivered',
        'Đã hủy': 'cancelled'
      };

      const status = statusMap[label];
      renderOrders(ordersData.filter(o => o.order_status === status));
    });
  });

  renderOrders(ordersData);
</script>

<script>
  // Lấy dữ liệu từ PHP
  //const ordersData = <?php //echo json_encode($orderList, JSON_UNESCAPED_UNICODE); ?>//;
  let existingReviews = {}; // Lưu reviews đã có

  document.addEventListener('DOMContentLoaded', () => {
    // Load đánh giá đã có
    loadExistingReviews();

    // Xử lý click đánh giá từ danh sách
    document.addEventListener('click', e => {
      // Đánh giá đơn hàng
      if (e.target.closest('.review-btn')) {
        const orderId = e.target.closest('.review-btn').dataset.orderId;
        showOrderReviewModal(orderId);
      }

      // Đánh giá sản phẩm cụ thể
      if (e.target.closest('.review-product-btn')) {
        const productId = e.target.closest('.review-product-btn').dataset.productId;
        showProductReviewModal(productId);
      }
    });

    // Đóng modal
    document.addEventListener('click', e => {
      if (e.target.closest('.review-close-btn')) {
        closeReviewModal();
      }

      if (e.target.id === 'reviewModal') {
        closeReviewModal();
      }
    });

    // ESC để đóng modal
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        closeReviewModal();
      }
    });
  });

  // Load đánh giá đã có
  async function loadExistingReviews() {
    try {
      const userId = <?php echo $_SESSION['user_id'] ?? 0; ?>;
      if (!userId) return;

      const response = await fetch(`apiPrivate/get_user_review.php?user_id=${userId}`);
      const result = await response.json();

      console.log(response);

      if (result.success) {
        result.reviews.forEach(review => {
          existingReviews[review.product_id] = review;
        });
      }
    } catch (error) {
      console.error('Lỗi load đánh giá:', error);
    }
  }

  // Hiển thị modal đánh giá cho đơn hàng
  function showOrderReviewModal(orderId) {
    const order = ordersData.find(o => o.order_id == orderId);
    if (!order) return;

    if (order.order_status !== 'delivered') {
      alert('Chỉ có thể đánh giá đơn hàng đã giao!');
      return;
    }

    const modalBody = document.getElementById('reviewModalBody');
    modalBody.innerHTML = `
      <div class="order-review-modal">
        <h3>Đánh giá đơn hàng #${order.order_number}</h3>
        <p class="text-muted">Đã giao ngày: ${new Date(order.order_date).toLocaleDateString('vi-VN')}</p>

        <div class="products-to-review">
          ${order.items.map(item => {
      const hasReviewed = existingReviews[item.product_id];
      const reviewBtnClass = hasReviewed ? 'btn-outline-secondary' : 'btn-primary';
      const reviewBtnText = hasReviewed ? 'Xem đánh giá' : 'Đánh giá ngay';
      const reviewBtnIcon = hasReviewed ? 'far fa-star' : 'far fa-star';

      return `
            <div class="product-review-card">
              <div class="product-review-info">
                <img src="${item.image}" alt="${item.product_name}" width="70">
                <div>
                  <h5>${item.product_name}</h5>
                  <p>${formatCurrency(item.price)} × ${item.quantity}</p>
                </div>
              </div>
              <button class="btn ${reviewBtnClass} review-product-btn"
                      data-product-id="${item.product_id}"
                      data-order-id="${order.order_id}">
                <i class="${reviewBtnIcon}"></i>
                ${reviewBtnText}
              </button>
            </div>
            `;
    }).join('')}
        </div>

        <div class="text-center mt-4">
          <button class="btn btn-secondary close-review-btn">Đóng</button>
        </div>
      </div>
    `;

    document.getElementById('reviewModal').classList.remove('hidden');

    // Gắn sự kiện cho nút đóng
    document.querySelector('.close-review-btn')?.addEventListener('click', closeReviewModal);
  }

  // Hiển thị modal đánh giá sản phẩm
  function showProductReviewModal(productId) {
    // Tìm sản phẩm trong đơn hàng
    let productInfo = null;
    let orderInfo = null;

    for (const order of ordersData) {
      for (const item of order.items) {
        if (item.product_id == productId) {
          productInfo = item;
          orderInfo = order;
          break;
        }
      }
      if (productInfo) break;
    }

    if (!productInfo) {
      alert('Không tìm thấy sản phẩm!');
      return;
    }

    const hasReviewed = existingReviews[productId];
    const modalBody = document.getElementById('reviewModalBody');

    if (hasReviewed) {
      // Hiển thị đánh giá đã có
      const review = existingReviews[productId];
      modalBody.innerHTML = `
        <div class="product-review-modal">
          <h3><i class="fas fa-star text-warning"></i> Đánh giá của bạn</h3>

          <div class="product-review-header">
            <img src="${productInfo.image}" alt="${productInfo.product_name}" width="80">
            <div>
              <h4>${productInfo.product_name}</h4>
              <p class="text-muted">
                Đơn hàng #${orderInfo.order_number}<br>
                Đã giao: ${new Date(orderInfo.order_date).toLocaleDateString('vi-VN')}
              </p>
            </div>
          </div>

          <div class="review-details">
            <div class="rating-display">
              <div class="stars-large">
                ${renderStars(review.rating)}
              </div>
              <p class="review-date">Đánh giá ngày: ${new Date(review.created_at).toLocaleDateString('vi-VN')}</p>
            </div>

            ${review.comment ? `
            <div class="review-comment">
              <h5>Nhận xét của bạn:</h5>
              <div class="comment-box">
                ${review.comment}
              </div>
            </div>
            ` : '<p class="text-muted">Không có nhận xét</p>'}
          </div>

          <div class="text-center mt-4">
            <button class="btn btn-secondary close-review-btn">Đóng</button>
          </div>
        </div>
      `;
    } else {
      // Hiển thị form đánh giá mới
      modalBody.innerHTML = `
        <div class="product-review-modal">
          <h3>Đánh giá sản phẩm</h3>

          <div class="product-review-header">
            <img src="${productInfo.image}" alt="${productInfo.product_name}" width="80">
            <div>
              <h4>${productInfo.product_name}</h4>
              <p class="text-muted">
                Đơn hàng #${orderInfo.order_number}<br>
                Đã giao: ${new Date(orderInfo.order_date).toLocaleDateString('vi-VN')}
              </p>
            </div>
          </div>

          <form id="reviewForm" class="review-form">
            <input type="hidden" id="productId" value="${productId}">

            <div class="rating-input mb-4">
              <p><strong>Bạn đánh giá sản phẩm này thế nào?</strong></p>
              <div class="stars-selector">
                ${renderStarsSelector(0)}
              </div>
              <input type="hidden" id="ratingValue" name="rating" value="0" required>
              <div id="ratingText" class="rating-text">Chọn số sao</div>
            </div>

            <div class="comment-input mb-4">
              <label for="reviewComment" class="form-label">Nhận xét (không bắt buộc):</label>
              <textarea
                id="reviewComment"
                name="comment"
                class="form-control"
                rows="4"
                placeholder="Chia sẻ trải nghiệm của bạn với sản phẩm này..."
                maxlength="500"></textarea>
              <div class="text-end text-muted small mt-1">
                <span id="charCount">0</span>/500 ký tự
              </div>
            </div>

            <div class="review-buttons">
              <button type="submit" class="btn btn-primary submit-review">
                <i class="fas fa-paper-plane me-2"></i>
                Gửi đánh giá
              </button>
              <button type="button" class="btn btn-secondary close-review-btn">Hủy</button>
            </div>
          </form>
        </div>
      `;

      // Setup form events
      setTimeout(() => {
        setupReviewForm(productId);
      }, 100);
    }

    document.getElementById('reviewModal').classList.remove('hidden');
    document.querySelector('.close-review-btn')?.addEventListener('click', closeReviewModal);
  }

  // Cài đặt form đánh giá
  function setupReviewForm(productId) {
    const form = document.getElementById('reviewForm');
    if (!form) return;

    // Sao đánh giá
    const stars = document.querySelectorAll('.star-select');
    stars.forEach(star => {
      star.addEventListener('click', (e) => {
        const value = parseInt(e.target.dataset.value);
        setStarRating(value);
      });
    });

    // Đếm ký tự comment
    const commentTextarea = document.getElementById('reviewComment');
    const charCount = document.getElementById('charCount');
    if (commentTextarea && charCount) {
      commentTextarea.addEventListener('input', () => {
        charCount.textContent = commentTextarea.value.length;
      });
    }

    // Submit form
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      await submitReview(productId);
    });
  }

  // Set sao đánh giá
  function setStarRating(rating) {
    // Cập nhật sao
    const stars = document.querySelectorAll('.star-select');
    stars.forEach((star, index) => {
      if (index < rating) {
        star.classList.remove('far');
        star.classList.add('fas', 'text-warning');
      } else {
        star.classList.remove('fas', 'text-warning');
        star.classList.add('far');
      }
    });

    // Cập nhật giá trị
    const ratingInput = document.getElementById('ratingValue');
    if (ratingInput) {
      ratingInput.value = rating;
    }

    // Cập nhật text
    const ratingText = document.getElementById('ratingText');
    if (ratingText) {
      const texts = ['Chọn số sao', 'Rất tệ', 'Không hài lòng', 'Tạm được', 'Hài lòng', 'Tuyệt vời'];
      ratingText.textContent = texts[rating];
      ratingText.className = 'rating-text ' + (rating >= 4 ? 'text-success' : rating >= 3 ? 'text-warning' : 'text-danger');
    }
  }

  // Gửi đánh giá
  async function submitReview(productId) {
    const rating = document.getElementById('ratingValue').value;
    const comment = document.getElementById('reviewComment').value;

    // Validate
    if (rating < 1 || rating > 5) {
      alert('Vui lòng chọn số sao đánh giá!');
      return;
    }

    const reviewData = {
      product_id: productId,
      user_id: <?php echo $_SESSION['user_id'] ?? 0; ?>,
      rating: parseInt(rating),
      comment: comment || ''
    };

    try {
      const response = await fetch('apiPrivate/save_review.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(reviewData)
      });

      const result = await response.json();

      if (result.success) {
        alert('Cảm ơn bạn đã đánh giá sản phẩm!');
        existingReviews[productId] = result.review;
        closeReviewModal();

        // Refresh lại trang hoặc cập nhật UI
        setTimeout(() => {
          location.reload();
        }, 1000);
      } else {
        alert('Lỗi: ' + result.message);
      }
    } catch (error) {
      console.error('Lỗi gửi đánh giá:', error);
      alert('Có lỗi xảy ra khi gửi đánh giá');
    }
  }

  // Đóng modal
  function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
    document.getElementById('reviewModalBody').innerHTML = '';
  }

  // Render sao (chỉ hiển thị)
  function renderStars(rating) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
      html += `<i class="fas fa-star ${i <= rating ? 'text-warning' : 'text-light'}"></i>`;
    }
    return html;
  }

  // Render sao selector (có thể chọn)
  function renderStarsSelector(rating) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
      const isActive = i <= rating;
      html += `
        <i class="star-select ${isActive ? 'fas text-warning' : 'far'} fa-star"
           data-value="${i}"
           style="font-size: 2rem; cursor: pointer; margin: 0 3px;"></i>
      `;
    }
    return html;
  }

  // Format tiền
  function formatCurrency(number) {
    return number.toLocaleString('vi-VN') + '₫';
  }

  // Thêm nút đánh giá vào HTML đơn hàng (chạy sau khi load)
  function addReviewButtons() {
    ordersData.forEach(order => {
      if (order.order_status === 'delivered') {
        const orderCard = document.querySelector(`.order-card[data-id="${order.order_id}"]`);
        if (orderCard) {
          const actionsDiv = orderCard.querySelector('.order-actions');
          if (actionsDiv) {
            const hasAnyUnreviewed = order.items.some(item => !existingReviews[item.product_id]);

            if (hasAnyUnreviewed) {
              const reviewBtn = document.createElement('button');
              reviewBtn.className = 'action-btn review-order-btn';
              reviewBtn.dataset.orderId = order.order_id;
              reviewBtn.innerHTML = '<i class="far fa-star"></i> Đánh giá đơn hàng';
              actionsDiv.prepend(reviewBtn);
            }
          }

          // Thêm nút đánh giá cho từng sản phẩm
          order.items.forEach(item => {
            const itemDiv = orderCard.querySelector(`.order-item img[src="${item.image}"]`)?.closest('.order-item');
            if (itemDiv) {
              const reviewBtn = document.createElement('button');
              reviewBtn.className = `btn btn-sm ${existingReviews[item.product_id] ? 'btn-outline-secondary' : 'btn-outline-primary'} review-product-btn`;
              reviewBtn.dataset.productId = item.product_id;
              reviewBtn.innerHTML = existingReviews[item.product_id]
                ? '<i class="fas fa-star"></i> Đã đánh giá'
                : '<i class="far fa-star"></i> Đánh giá';
              itemDiv.querySelector('.item-info')?.appendChild(reviewBtn);
            }
          });
        }
      }
    });
  }

  // Chạy sau khi DOM loaded
  setTimeout(() => {
    addReviewButtons();
  }, 500);
</script>

<style>
  /* ===== MODAL REVIEW ===== */
  #reviewModal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
    animation: fadeIn 0.3s ease;
  }

  #reviewModal.hidden {
    display: none;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  #reviewModal .modal_item-content {
    background: white;
    width: 100%;
    max-width: 700px;
    max-height: 85vh;
    overflow-y: auto;
    border-radius: 12px;
    padding: 25px;
    position: relative;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    animation: slideUp 0.3s ease;
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .review-close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    cursor: pointer;
    font-size: 1.8rem;
    background: none;
    border: none;
    padding: 5px;
    line-height: 1;
    z-index: 1;
    transition: transform 0.2s;
  }

  .review-close-btn:hover {
    transform: scale(1.1);
  }

  /* ===== ORDER REVIEW MODAL ===== */
  .order-review-modal h3 {
    margin-bottom: 8px;
    color: #333;
    font-size: 1.5rem;
    font-weight: 600;
  }

  .order-review-modal .text-muted {
    color: #6c757d;
    margin-bottom: 25px;
    font-size: 0.95rem;
  }

  .products-to-review {
    margin: 25px 0;
  }

  .product-review-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 12px;
    background: #f8fafc;
    transition: all 0.3s ease;
  }

  .product-review-card:hover {
    border-color: #cbd5e0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  }

  .product-review-info {
    display: flex;
    align-items: center;
    gap: 18px;
    flex: 1;
  }

  .product-review-info img {
    width: 75px;
    height: 75px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
  }

  .product-review-info div {
    flex: 1;
  }

  .product-review-info h5 {
    margin: 0 0 6px 0;
    color: #2d3748;
    font-size: 1.05rem;
    font-weight: 600;
  }

  .product-review-info p {
    margin: 0;
    color: #4a5568;
    font-size: 0.9rem;
  }

  /* ===== PRODUCT REVIEW MODAL ===== */
  .product-review-modal h3 {
    margin-bottom: 20px;
    color: #333;
    font-size: 1.6rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .product-review-modal h3 i {
    color: #ffc107;
  }

  .product-review-header {
    display: flex;
    align-items: center;
    gap: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f1f5f9;
    margin-bottom: 25px;
  }

  .product-review-header img {
    width: 90px;
    height: 90px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
  }

  .product-review-header div {
    flex: 1;
  }

  .product-review-header h4 {
    margin: 0 0 8px 0;
    color: #2d3748;
    font-size: 1.3rem;
    font-weight: 600;
  }

  .product-review-header .text-muted {
    color: #718096;
    font-size: 0.9rem;
    line-height: 1.5;
  }

  /* ===== REVIEW FORM ===== */
  .review-form {
    margin-top: 20px;
  }

  .rating-input {
    margin-bottom: 30px;
  }

  .rating-input p {
    margin-bottom: 15px;
    color: #2d3748;
    font-size: 1.1rem;
    font-weight: 500;
  }

  .stars-selector {
    display: flex;
    gap: 5px;
    margin: 15px 0;
  }

  .star-select {
    font-size: 2.5rem;
    cursor: pointer;
    margin: 0 3px;
    color: #e2e8f0;
    transition: all 0.2s ease;
  }

  .star-select:hover {
    transform: scale(1.1);
  }

  .star-select.fas.text-warning {
    color: #ffc107 !important;
  }

  .star-select.far {
    color: #cbd5e0;
  }

  .rating-text {
    font-size: 1rem;
    font-weight: 500;
    margin-top: 10px;
    padding: 8px 15px;
    background: #f7fafc;
    border-radius: 8px;
    display: inline-block;
    border: 1px solid #e2e8f0;
    min-width: 120px;
    text-align: center;
  }

  .rating-text.text-success {
    background: #f0fff4;
    color: #38a169;
    border-color: #c6f6d5;
  }

  .rating-text.text-warning {
    background: #fffaf0;
    color: #dd6b20;
    border-color: #fed7aa;
  }

  .rating-text.text-danger {
    background: #fff5f5;
    color: #e53e3e;
    border-color: #fed7d7;
  }

  /* ===== COMMENT INPUT ===== */
  .comment-input {
    margin-bottom: 30px;
  }

  .form-label {
    display: block;
    margin-bottom: 10px;
    color: #2d3748;
    font-weight: 500;
    font-size: 1rem;
  }

  .form-control {
    width: 100%;
    padding: 14px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: inherit;
    resize: vertical;
    min-height: 120px;
  }

  .form-control:focus {
    outline: none;
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
  }

  .form-control::placeholder {
    color: #a0aec0;
  }

  .text-end.text-muted.small {
    font-size: 0.85rem;
    margin-top: 8px;
  }

  #charCount {
    font-weight: 600;
    color: #4a5568;
  }

  /* ===== REVIEW BUTTONS ===== */
  .review-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
    padding-top: 25px;
    border-top: 2px solid #f1f5f9;
  }

  .review-buttons .btn {
    padding: 12px 30px;
    font-size: 1rem;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
    min-width: 140px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid transparent;
  }

  .btn-primary {
    background: #4299e1;
    color: white;
    border-color: #4299e1;
  }

  .btn-primary:hover {
    background: #3182ce;
    border-color: #3182ce;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(66, 153, 225, 0.2);
  }

  .btn-secondary {
    background: #a0aec0;
    color: white;
    border-color: #a0aec0;
  }

  .btn-secondary:hover {
    background: #718096;
    border-color: #718096;
    transform: translateY(-2px);
  }

  .submit-review i {
    font-size: 0.9rem;
  }

  /* ===== REVIEW DISPLAY ===== */
  .review-details {
    margin-top: 20px;
  }

  .rating-display {
    text-align: center;
    margin-bottom: 25px;
  }

  .stars-large {
    font-size: 2.2rem;
    color: #ffc107;
    margin: 10px 0;
    letter-spacing: 2px;
  }

  .review-date {
    color: #718096;
    font-size: 0.9rem;
    margin-top: 5px;
  }

  .review-comment {
    margin-top: 25px;
  }

  .review-comment h5 {
    margin-bottom: 12px;
    color: #2d3748;
    font-size: 1.1rem;
    font-weight: 600;
  }

  .comment-box {
    background: #f8fafc;
    padding: 20px;
    border-radius: 10px;
    margin-top: 10px;
    line-height: 1.6;
    color: #4a5568;
    border-left: 4px solid #4299e1;
    font-size: 1rem;
    word-wrap: break-word;
  }

  /* ===== BUTTONS IN ORDER LIST ===== */
  .review-order-btn {
    background: #38a169;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    margin-right: 12px;
  }

  .review-order-btn:hover {
    background: #2f855a;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(56, 161, 105, 0.2);
  }

  .review-order-btn i {
    font-size: 1rem;
  }

  .btn-review-item {
    margin-top: 10px;
    padding: 8px 16px;
    font-size: 0.9rem;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    font-weight: 500;
  }

  .btn-review-item.btn-primary {
    background: #4299e1;
    color: white;
    border-color: #4299e1;
  }

  .btn-review-item.btn-primary:hover {
    background: #3182ce;
    border-color: #3182ce;
    transform: translateY(-1px);
  }

  .btn-review-item.btn-secondary {
    background: #a0aec0;
    color: white;
    border-color: #a0aec0;
  }

  .btn-review-item.btn-secondary:hover {
    background: #718096;
    border-color: #718096;
  }

  /* ===== RESPONSIVE ===== */
  @media (max-width: 768px) {
    #reviewModal .modal_item-content {
      padding: 20px;
      max-height: 90vh;
    }

    .product-review-card {
      flex-direction: column;
      align-items: stretch;
      gap: 15px;
    }

    .product-review-info {
      gap: 12px;
    }

    .product-review-info img {
      width: 60px;
      height: 60px;
    }

    .product-review-header {
      flex-direction: column;
      text-align: center;
      gap: 15px;
    }

    .product-review-header img {
      width: 80px;
      height: 80px;
    }

    .star-select {
      font-size: 2rem;
    }

    .review-buttons {
      flex-direction: column;
    }

    .review-buttons .btn {
      width: 100%;
      padding: 14px;
    }

    .review-order-btn {
      padding: 8px 16px;
      font-size: 0.9rem;
      margin-right: 8px;
    }
  }

  @media (max-width: 480px) {
    #reviewModal .modal_item-content {
      padding: 15px;
      border-radius: 8px;
    }

    .product-review-modal h3 {
      font-size: 1.3rem;
    }

    .stars-selector {
      gap: 2px;
    }

    .star-select {
      font-size: 1.8rem;
      margin: 0 1px;
    }

    .form-control {
      padding: 12px;
    }
  }

  /* ===== UTILITIES ===== */
  .text-center {
    text-align: center;
  }

  .text-muted {
    color: #6c757d;
  }

  .mt-1 { margin-top: 0.25rem; }
  .mt-3 { margin-top: 1rem; }
  .mt-4 { margin-top: 1.5rem; }
  .mb-3 { margin-bottom: 1rem; }
  .mb-4 { margin-bottom: 1.5rem; }
  .me-2 { margin-right: 0.5rem; }
</style>

<?php include 'footer.php'?>

</body>
</html>
