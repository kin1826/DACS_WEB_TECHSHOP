<?php
require 'class/CartItem.php';

if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
  echo json_encode(['success' => false, 'message' => 'Missing params']);
  exit;
}

$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

$cartItemModel = new CartItem();
$cartItemModel->updateQuantity($product_id, $quantity);

echo json_encode(['success' => true]);
