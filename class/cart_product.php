<?php
require_once 'db.php';

class Cart extends DB {
  protected $table = 'cart';

  public function __construct() {
    parent::__construct();
  }

  // Lấy một cart theo user_id
  public function findCart($user_id) {
    $sql = "SELECT * FROM {$this->table} WHERE user_id = $user_id LIMIT 1";
    $res = $this->db_query($sql);
    return $this->db_fetch($res);
  }

  public function countItems($cart_id) {
    $sql = "SELECT SUM(quantity) AS total FROM cart_items WHERE cart_id = $cart_id";
    $row = $this->db_fetch($this->db_query($sql));
    return intval($row['total'] ?? 0);
  }

  // Trả về cart_id — nếu chưa có thì tạo mới
  public function getOrCreateCart($user_id) {
    $cart = $this->findCart($user_id);

    if ($cart) {
      return $cart['id'];
    }

    // tạo mới
    $sql = "INSERT INTO {$this->table} (user_id)
                VALUES ($user_id)";
    $this->db_query($sql);

    $cart = $this->findCart($user_id);
    return $cart['id'] ?? '0';
  }

  public function getCartByUser($user_id) {
    return $this->findCart($user_id);
  }

  // Lấy tất cả item trong cart
  public function getItems($cart_id) {
    $cart_id = intval($cart_id);
    $sql = "SELECT ci.id, ci.product_id, ci.variant_id, ci.price, ci.quantity, ci.created_at
                FROM cart_items ci
                WHERE ci.cart_id = $cart_id";
    $result = $this->db_query($sql);

    $items = [];
    if ($result) {
      while ($row = $this->db_fetch($result)) {
        $items[] = $row;
      }
    }

    return $items;
  }

  public function clearCart($cart_id) {
    $sql = "DELETE FROM cart_items WHERE cart_id = $cart_id";
    return $this->db_query($sql);
  }

  public function deleteCart($cart_id) {
    $this->db_query("DELETE FROM cart_items WHERE cart_id = $cart_id");
    return $this->db_query("DELETE FROM {$this->table} WHERE id = $cart_id");
  }

  public function touchCart($cart_id) {
    $sql = "UPDATE {$this->table} SET updated_at = NOW() WHERE id = $cart_id";
    return $this->db_query($sql);
  }
}
