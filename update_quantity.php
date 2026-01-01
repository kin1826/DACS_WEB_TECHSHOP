<?php
//require 'class/CartItem.php';
//
//if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
//  echo json_encode(['success' => false, 'message' => 'Missing params']);
//  exit;
//}
//
//$product_id = intval($_POST['product_id']);
//$quantity = intval($_POST['quantity']);
//
//$cartItemModel = new CartItem();
//$cartItemModel->updateQuantity($product_id, $quantity);
//
//echo json_encode(['success' => true]);


require 'class/CartItem.php';

header('Content-Type: application/json');

if (!isset($_POST['action'], $_POST['product_id'])) {
  echo json_encode([
    'success' => false,
    'message' => 'Missing params'
  ]);
  exit;
}

$action = $_POST['action'];
$product_id = (int)$_POST['product_id'];

$cartItemModel = new CartItem();

try {
  switch ($action) {
    case 'update':
      if (!isset($_POST['quantity'])) {
        throw new Exception('Missing quantity');
      }

      $quantity = (int)$_POST['quantity'];

      if ($quantity <= 0) {
        throw new Exception('Invalid quantity');
      }

      $cartItemModel->updateQuantity($product_id, $quantity);
      break;

    case 'delete':
      $cartItemModel->removeItem($product_id);
      break;

    default:
      throw new Exception('Invalid action');
  }

  echo json_encode([
    'success' => true,
    'action' => $action
  ]);

} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
}
