<?php
session_start();

// Google OAuth Config
$configGG['GOOGLE_CLIENT_ID'] = '1091283087850-a453f1ll8q4p08pc45tb8g6dsdh96rr3.apps.googleusercontent.com';
$configGG['GOOGLE_CLIENT_SECRET'] = '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';
$configGG['GOOGLE_REDIRECT_URI'] = 'http://localhost:8000/login.php';

require_once 'class/user.php';

$user = new User();

// Hàm lấy Google Login URL với email hint
function getGoogleAuthUrl($email = '') {
  global $configGG;

  $params = [
    'client_id' => $configGG['GOOGLE_CLIENT_ID'],
    'redirect_uri' => $configGG['GOOGLE_REDIRECT_URI'],
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'offline',
    'prompt' => 'select_account'
  ];

  // Thêm email hint nếu có
  if (!empty($email)) {
    $params['login_hint'] = $email;
  }

  return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
}

// Hàm chuyển hướng
function redirect($url) {
  header("Location: $url");
  exit;
}

// Xử lý form - luôn redirect đến Google OAuth
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
  $email = trim($_POST['email']);

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Email không hợp lệ!";
  } else {
    // Luôn redirect đến Google OAuth với email hint
    $googleUrl = getGoogleAuthUrl($email);
    redirect($googleUrl);
  }
}

// Xử lý Google callback
if (isset($_GET['code'])) {
  global $configGG;

  try {
    // Lấy access token từ Google
    $tokenUrl = 'https://accounts.google.com/o/oauth2/token';
    $tokenData = [
      'code' => $_GET['code'],
      'client_id' => $configGG['GOOGLE_CLIENT_ID'],
      'client_secret' => $configGG['GOOGLE_CLIENT_SECRET'],
      'redirect_uri' => $configGG['GOOGLE_REDIRECT_URI'],
      'grant_type' => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $tokenResponse = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($tokenResponse, true);

    if (isset($tokenData['access_token'])) {
      // Lấy thông tin user từ Google
      $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $tokenData['access_token']
      ]);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

      $userInfoResponse = curl_exec($ch);
      curl_close($ch);

      $userInfo = json_decode($userInfoResponse, true);

      // Xử lý thông tin user
      if (isset($userInfo['id'])) {
        $googleUserData = [
          'id' => $userInfo['id'],
          'email' => $userInfo['email'],
          'name' => $userInfo['name'],
          'picture' => $userInfo['picture']
        ];

        // Sử dụng method handleGoogleLogin từ class User
        $userData = $user->handleGoogleLogin($googleUserData);

        if ($userData) {
          // Lưu session
          $_SESSION['user_id'] = $userData['id'];
          $_SESSION['user_name'] = $userData['username'] ?: $userData['name'];
          $_SESSION['user_email'] = $userData['email'];
          $_SESSION['user_avatar'] = $userData['avatar'] ?: $userInfo['picture'];
          $_SESSION['is_admin'] = isset($userData['is_admin']) ? $userData['is_admin'] : false;
          $_SESSION['login_method'] = 'google';

          // Chuyển hướng về trang chủ
          redirect('index.php');
          echo '<script>alert("ok");</script>';
        } else {
          $error = "Không thể đăng nhập với Google. Vui lòng thử lại.";
        }
      } else {
        $error = "Không thể lấy thông tin từ Google.";
      }
    } else {
      $error = "Không thể lấy access token từ Google.";
    }
  } catch (Exception $e) {
    $error = "Lỗi đăng nhập Google: " . $e->getMessage();
  }
}

// Hiển thị lỗi nếu có
if (isset($error)) {
  echo "<div class='error'>$error</div>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop Tech - Đăng Nhập</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .container {
      width: 100%;
      max-width: 400px;
    }

    .login-box {
      background: white;
      padding: 40px 30px;
      border-radius: 15px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .logo {
      margin-bottom: 30px;
    }

    .logo i {
      font-size: 50px;
      color: #667eea;
      margin-bottom: 15px;
    }

    .logo h2 {
      color: #333;
      margin-bottom: 10px;
      font-size: 28px;
    }

    .logo p {
      color: #666;
      font-size: 14px;
    }

    .form-group {
      margin-bottom: 20px;
      text-align: left;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #333;
      font-weight: 500;
    }

    .input-group {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-group i {
      position: absolute;
      left: 15px;
      color: #999;
      z-index: 2;
    }

    .input-group input {
      width: 100%;
      padding: 12px 15px 12px 45px;
      border: 2px solid #e1e1e1;
      border-radius: 8px;
      font-size: 16px;
      transition: all 0.3s ease;
    }

    .input-group input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .login-btn {
      width: 100%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 15px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-bottom: 20px;
    }

    .login-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .login-btn i {
      margin-right: 8px;
    }

    .divider {
      position: relative;
      margin: 25px 0;
      text-align: center;
    }

    .divider span {
      background: white;
      padding: 0 15px;
      color: #666;
      font-size: 14px;
    }

    .divider::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      height: 1px;
      background: #e1e1e1;
      z-index: -1;
    }

    .google-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      background: white;
      color: #333;
      border: 2px solid #e1e1e1;
      padding: 12px;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
      margin-bottom: 20px;
    }

    .google-btn:hover {
      border-color: #db4437;
      box-shadow: 0 2px 8px rgba(219, 68, 55, 0.2);
    }

    .google-btn img {
      width: 20px;
      height: 20px;
      margin-right: 10px;
    }

    .register-link {
      color: #666;
      font-size: 14px;
      margin-bottom: 25px;
    }

    .register-link a {
      color: #667eea;
      text-decoration: none;
      font-weight: 500;
    }

    .register-link a:hover {
      text-decoration: underline;
    }

    .features {
      display: flex;
      justify-content: space-around;
      border-top: 1px solid #e1e1e1;
      padding-top: 20px;
    }

    .feature {
      display: flex;
      flex-direction: column;
      align-items: center;
      color: #666;
      font-size: 12px;
    }

    .feature i {
      font-size: 16px;
      margin-bottom: 5px;
      color: #667eea;
    }

    .notification {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 15px 20px;
      border-radius: 8px;
      color: white;
      font-weight: 500;
      z-index: 1000;
      transform: translateX(400px);
      transition: transform 0.3s ease;
      max-width: 300px;
    }

    .notification.show {
      transform: translateX(0);
    }

    .notification.success {
      background: #4CAF50;
    }

    .notification.error {
      background: #f44336;
    }

    .notification.info {
      background: #2196F3;
    }

    .email-hint {
      background: #e3f2fd;
      border: 1px solid #bbdefb;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      text-align: center;
      font-size: 14px;
      color: #1976d2;
    }

    .email-hint strong {
      color: #0d47a1;
    }

    @media (max-width: 480px) {
      .login-box {
        padding: 30px 20px;
      }

      .logo h2 {
        font-size: 24px;
      }
    }
  </style>
</head>
<body>
<div class="container">
  <div class="login-box">
    <div class="logo">
      <i class="fas fa-user-shield"></i>
      <h2>Đăng Nhập</h2>
      <p>Nhập email để tiếp tục với Google</p>
    </div>

    <?php if (isset($_GET['email_hint']) && !empty($_GET['email_hint'])): ?>
      <div class="email-hint">
        <i class="fas fa-info-circle"></i>
        Đang sử dụng email: <strong><?php echo htmlspecialchars($_GET['email_hint']); ?></strong>
      </div>
    <?php endif; ?>

    <form class="login-form" method="POST" action="">
      <div class="form-group">
        <label for="email">Email của bạn</label>
        <div class="input-group">
          <i class="fas fa-envelope"></i>
          <input type="email" id="email" name="email" placeholder="nhập.email@example.com" required
                 value="<?php echo isset($_GET['email_hint']) ? htmlspecialchars($_GET['email_hint']) : ''; ?>">
        </div>
      </div>

      <button type="submit" class="login-btn">
        <i class="fas fa-arrow-right"></i>
        Tiếp tục với Google
      </button>
    </form>

    <div class="divider">
      <span>hoặc</span>
    </div>

    <a href="<?php echo getGoogleAuthUrl(); ?>" class="google-btn">
      <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google">
      Chọn tài khoản Google
    </a>

    <div class="register-link">
      Bằng cách tiếp tục, bạn đồng ý với <a href="#">Điều khoản sử dụng</a>
    </div>

    <div class="features">
      <div class="feature">
        <i class="fas fa-shield-alt"></i>
        <span>Bảo mật</span>
      </div>
      <div class="feature">
        <i class="fas fa-bolt"></i>
        <span>Nhanh chóng</span>
      </div>
      <div class="feature">
        <i class="fas fa-user-plus"></i>
        <span>Tự động tạo tài khoản</span>
      </div>
    </div>
  </div>

  <div class="notification" id="notification">
    <?php if(isset($error)): ?>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          showNotification('<?php echo $error; ?>', 'error');
        });
      </script>
    <?php endif; ?>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('.login-form');

    if (loginForm) {
      loginForm.addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;

        if (!validateEmail(email)) {
          e.preventDefault();
          showNotification('Vui lòng nhập email hợp lệ', 'error');
          return;
        }

        // Show loading state
        const submitBtn = this.querySelector('.login-btn');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang chuyển hướng...';
        submitBtn.disabled = true;
      });
    }

    // Email validation
    function validateEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
    }

    // Notification system
    function showNotification(message, type) {
      const notification = document.getElementById('notification');
      if (notification) {
        notification.textContent = message;
        notification.className = `notification ${type} show`;

        setTimeout(() => {
          notification.classList.remove('show');
        }, 5000);
      }
    }

    // Focus effect
    const emailInput = document.getElementById('email');
    if (emailInput) {
      emailInput.focus();

      emailInput.addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
      });

      emailInput.addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
      });
    }
  });
</script>
</body>
</html>
