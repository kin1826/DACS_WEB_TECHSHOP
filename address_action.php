<?php
require_once "class/user_address.php";
session_start();

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["success" => false, "message" => "Bạn chưa đăng nhập"]);
  exit;
}

$userId = $_SESSION['user_id'];
$addressModel = new UserAddress();

// ---- LẤY ACTION ----
$action = $_POST['action'] ?? '';

switch ($action) {

  // ================== THÊM ĐỊA CHỈ ==================
  case "add":
    $recipient_name = trim($_POST['recipient_name'] ?? '');
    $name           = trim($_POST['name'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $address        = trim($_POST['address'] ?? '');

    if (!$recipient_name || !$name || !$phone || !$address) {
      echo json_encode(["success" => false, "message" => "Thiếu dữ liệu"]);
      exit;
    }

    $newId = $addressModel->addAddress($userId, $name, $recipient_name,$phone, $address, 0);

    if ($newId) {
      echo json_encode([
        "success" => true,
        "address" => [
          "id"             => $newId,
          "recipient_name" => $recipient_name,
          "name"           => $name,
          "phone"          => $phone,
          "address"        => $address,
          "isDefault"      => 0
        ]
      ]);
    } else {
      echo json_encode(["success" => false, "message" => "Không thể thêm địa chỉ"]);
    }
    break;


  // ================== ĐẶT MẶC ĐỊNH ==================
  case "set_default":
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
      echo json_encode(["success" => false, "message" => "ID không hợp lệ"]);
      exit;
    }

    $ok = $addressModel->setDefault($id, $userId);

    echo json_encode([
      "success" => (bool)$ok,
      "message" => $ok ? "Đã cập nhật" : "Lỗi set default"
    ]);
    break;


  // ================== XOÁ ==================
  case "delete":
    $id = intval($_POST['id'] ?? 0);

    if (!$id) {
      echo json_encode(["success" => false, "message" => "ID không hợp lệ"]);
      exit;
    }

    $ok = $addressModel->deleteAddress($id, $userId);

    echo json_encode([
      "success" => $ok,
      "message" => $ok ? "Đã xoá" : "Không xoá được"
    ]);
    break;

  default:
    echo json_encode(["success" => false, "message" => "Hành động không hợp lệ"]);
}
