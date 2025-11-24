<?php

$config['DB_HOST'] = 'localhost';
$config['DB_USER'] = 'root';
$config['DB_PASS'] = '';
$config['DB_NAME'] = 'techstore_db';

//// Google OAuth Config
//$configGG['GOOGLE_CLIENT_ID'] = '1091283087850-a453f1ll8q4p08pc45tb8g6dsdh96rr3.apps.googleusercontent.com';
//$configGG['GOOGLE_CLIENT_SECRET'] = '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';
//$configGG['GOOGLE_REDIRECT_URI'] = 'http://yourdomain.com/login.php';

//define('GOOGLE_CLIENT_ID', '1091283087850-a453f1ll8q4p08pc45tb8g6dsdh96rr3.apps.googleusercontent.com');
//define('GOOGLE_CLIENT_SECRET', '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');
//define('GOOGLE_REDIRECT_URI', 'http://yourdomain.com/login.php');



// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Hàm chuyển hướng
function redirect($url) {
    header("Location: $url");
    exit();
}

?>
