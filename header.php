<?php
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? ($_SESSION['user_name'] ?? 'User') : '';
$userAvatar = $isLoggedIn ? ($_SESSION['user_avatar'] ?? '') : '';
$userEmail = $isLoggedIn ? ($_SESSION['user_email'] ?? '') : '';
$isAdmin = $isLoggedIn ? ($_SESSION['is_admin'] ?? '') : '';
?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dynamic Draggable Header</title>
  <link rel="stylesheet" href="css/header.css">
</head>
<body>

<!-- Header component -->

<header id="dynamicHeader" class="dynamic-header" role="banner" aria-label="Thanh điều hướng động">
  <a href="index.php" class="dh__logo" style="text-decoration: none; color: black">
    <div class="dot">HB</div>
    <div>
      <div class="dh__title">Tech Shop</div>
    </div>
  </a>

  <div class="dh__spacer"></div>

  <nav class="dh__controls" aria-label="header controls">
    <ul class="dh__menu">
      <li>
        <a href="index.php" data-tooltip="Trang chủ" class="dh__inmenu">
          <i class="fa-solid fa-house"></i>
          <span class="hv_left_right">Trang chủ</span>
        </a>
      </li>
      <li>
        <a href="products.php" data-tooltip="Sản phẩm" class="dh__inmenu">
          <i class="fa-solid fa-shop"></i>
          <span class="hv_left_right">Sản phẩm</span>
        </a>
      </li>
      <li>
        <a href="about.php" data-tooltip="Giới thiệu" class="dh__inmenu">
          <i class="fa-solid fa-address-card"></i>
          <span class="hv_left_right">Giới thiệu</span>
        </a>
      </li>
      <li>
        <a href="contact.php" data-tooltip="Liên hệ" class="dh__inmenu">
          <i class="fa-solid fa-headset"></i>
          <span class="hv_left_right">Liên hệ</span>
        </a>
      </li>
      <li class="user-dropdown">
        <a href="login.php"
           id="userToggle"
           data-tooltip="<?php echo $isLoggedIn ? 'Tài khoản' : 'Đăng nhập'; ?>"
           data-loggedin="<?php echo $isLoggedIn ? 'true' : 'false'; ?>">

          <?php if ($isLoggedIn && !empty($userAvatar)): ?>
            <!-- Đã đăng nhập và có avatar - hiển thị hình tròn -->
            <img src="<?php echo $userAvatar; ?>"
                 alt="Avatar"
                 class="user-avatar-hd">
          <?php else: ?>
            <!-- Chưa đăng nhập hoặc không có avatar - hiển thị icon -->
            <i class="fa-solid fa-user"></i>
          <?php endif; ?>
        </a>
        <ul class="user-menu">
          <?php if ($isLoggedIn): ?>
            <!-- Đã đăng nhập - hiển thị thông tin user -->
            <li class="user-info">
              <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
              <?php if (!empty($userEmail)): ?>
                <div class="user-email"><?php echo htmlspecialchars($userEmail); ?></div>
              <?php endif; ?>
            </li>
            <li><a href="account.php" class="user-menu-text"><i class="fa-solid fa-address-card"></i>Trang cá nhân</a></li>
            <li><a href="account.php?tab=orders"><i class="fa-solid fa-box"></i>Đơn hàng</a></li>
            <li><a href="settings.php"><i class="fa-solid fa-gear"></i>Cài đặt</a></li>

            <?php if ($isAdmin === "1"): ?>
              <li><a href="admin.php"><i class="fa-solid fa-user"></i>Trang quản trị viên</a></li>
            <?php endif; ?>

            <li><a href="logout.php" style="color: red"><i class="fa-solid fa-right-to-bracket"></i>Đăng xuất</a></li>
          <?php else: ?>
            <!-- Chưa đăng nhập - hiển thị menu đăng nhập -->
            <li><a href="login.php">Đăng nhập</a></li>
            <li><a href="login.php">Đăng ký</a></li>
          <?php endif; ?>
        </ul>
      </li>

      <li>
<!--        <a href="cart.php" data-tooltip="Giỏ hàng"><i class="fa-solid fa-cart-shopping"></i></a>-->
        <!-- Trong header.php -->
        <a href="cart.php" data-tooltip="Giỏ hàng" class="cart-icon position-relative">
          <i class="fas fa-shopping-cart"></i>
        </a>
      </li>
    </ul>
  </nav>

  <div id="dhHandle" class="dh__handle" title="Kéo để di chuyển">
    <a href="javascript:void(0)" id="toggleMenu"><i class="fa-solid fa-bars"></i></a>
  </div>

  <div class="dh__search-float">
    <button class="search-btn-header">
      <i class="fa-solid fa-magnifying-glass"></i>
    </button>
  </div>

</header>

<script src="js/headerJS.js"></script>

</body>
</html>
