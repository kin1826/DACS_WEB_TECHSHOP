<?php
// test-config.php
$configGG['GOOGLE_CLIENT_ID'] = '1091283087850-a453f1ll8q4p08pc45tb8g6dsdh96rr3.apps.googleusercontent.com';
$configGG['GOOGLE_REDIRECT_URI'] = 'http://localhost:8000/login.php';

echo "<h2>🔍 GOOGLE OAUTH CONFIGURATION TEST</h2>";

// Hiển thị thông tin config
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<p><strong>Client ID:</strong> " . $configGG['GOOGLE_CLIENT_ID'] . "</p>";
echo "<p><strong>Redirect URI trong CODE:</strong> <code style='background: yellow;'>" . $configGG['GOOGLE_REDIRECT_URI'] . "</code></p>";
echo "</div>";

// Tạo OAuth URL
$params = [
  'client_id' => $configGG['GOOGLE_CLIENT_ID'],
  'redirect_uri' => $configGG['GOOGLE_REDIRECT_URI'],
  'response_type' => 'code',
  'scope' => 'email profile',
  'access_type' => 'offline',
  'prompt' => 'select_account'
];

$url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
echo "<p><strong>OAuth URL:</strong> <a href='$url' target='_blank' style='color: blue; text-decoration: underline;'>CLICK ĐỂ TEST</a></p>";

// Phân tích URL
echo "<h3>📊 PHÂN TÍCH URL OAUTH:</h3>";
echo "<div style='background: #e8f4fd; padding: 10px; border-left: 4px solid #2196F3;'>";
foreach ($params as $key => $value) {
  echo "<p><strong>$key:</strong> <code>$value</code></p>";
}
echo "</div>";

// KIỂM TRA SỰ KHỚP NHAU
echo "<h3>✅ KIỂM TRA SỰ KHỚP NHAU:</h3>";

// Giả sử đây là URI bạn đã nhập trong Google Console
$google_console_uri = 'http://localhost:8000/login.php'; // THAY BẰNG URI BẠN ĐÃ NHẬP TRONG GOOGLE CONSOLE

echo "<p>Redirect URI trong <strong>CODE</strong>: <code style='background: " . ($configGG['GOOGLE_REDIRECT_URI'] === $google_console_uri ? '#90EE90' : '#FFB6C1') . ";'>" . $configGG['GOOGLE_REDIRECT_URI'] . "</code></p>";
echo "<p>Redirect URI trong <strong>GOOGLE CONSOLE</strong>: <code style='background: " . ($configGG['GOOGLE_REDIRECT_URI'] === $google_console_uri ? '#90EE90' : '#FFB6C1') . ";'>" . $google_console_uri . "</code></p>";

if ($configGG['GOOGLE_REDIRECT_URI'] === $google_console_uri) {
  echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 HOÀN TOÀN KHỚP NHAU!</p>";
  echo "<p style='color: green;'>Mọi thứ đã được cấu hình đúng. Vấn đề có thể ở chỗ khác.</p>";
} else {
  echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ KHÔNG KHỚP NHAU!</p>";
  echo "<p style='color: red;'>Sửa Google Console để khớp với: <code>" . $configGG['GOOGLE_REDIRECT_URI'] . "</code></p>";
}

// Hướng dẫn sửa
echo "<h3>🔧 HƯỚNG DẪN SỬA:</h3>";
echo "<ol>
<li>Vào <strong>Google Cloud Console</strong> → <strong>APIs & Services</strong> → <strong>Credentials</strong></li>
<li>Click vào <strong>OAuth 2.0 Client ID</strong> của bạn</li>
<li>Trong mục <strong>Authorized redirect URIs</strong>, THÊM:</li>
</ol>";
echo "<p><code style='background: #e8f4fd; padding: 5px; display: inline-block;'>" . $configGG['GOOGLE_REDIRECT_URI'] . "</code></p>";
echo "<p>Sau đó <strong>SAVE</strong> và đợi 2-3 phút.</p>";

// Test link
echo "<h3>🧪 TEST NGAY:</h3>";
echo "<p><a href='$url' target='_blank' style='background: #4285f4; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block;'>CLICK ĐỂ TEST ĐĂNG NHẬP GOOGLE</a></p>";
echo "<p><small>Mở link này trong <strong>Incognito window</strong> để test</small></p>";

// Debug thêm
echo "<h3>🐛 DEBUG INFO:</h3>";
echo "<p><strong>Current URL:</strong> " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]</p>";
?>
