<?php

session_start();
require_once "class/product.php";
$productModel = new Product();

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

$result = $productModel->toggleWishList($user_id, $product_id);

echo json_encode([
  "status" => $result // "add" hoặc "remove"
]);
?>
