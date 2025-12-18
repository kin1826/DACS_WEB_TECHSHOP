<?php
require_once 'db.php';

class CartItem extends DB {
  protected $table = 'cart_items';

  public function __construct() {
    parent::__construct();
  }

  public function countItems($cart_id) {
    $sql = "SELECT SUM(quantity) AS total_qty
                FROM {$this->table}
                WHERE cart_id = $cart_id";

    $res = $this->db_query($sql);
    $row = $this->db_fetch($res);

    return $row && $row['total_qty'] !== null ? (int)$row['total_qty'] : 0;
  }

  public function addItem($cart_id, $product_id, $variant_id, $qty, $price) {

    $sqlCheck = "SELECT id, quantity FROM cart_items
                     WHERE cart_id = $cart_id AND product_id = $product_id AND variant_id = $variant_id
                     LIMIT 1";

    $item = $this->db_fetch($this->db_query($sqlCheck));

    if ($item) {
      $newQty = $item['quantity'] + $qty;
      $sql = "UPDATE cart_items SET quantity = $newQty WHERE id = {$item['id']}";
      return $this->db_query($sql);
    }

    $sql = "INSERT INTO cart_items (cart_id, product_id, variant_id, price, quantity)
                VALUES ($cart_id, $product_id, $variant_id, $price, $qty)";

    return $this->db_query($sql);
  }

  // Tùy chọn: đếm số dòng item khác nhau (distinct products/variants)
  public function countDistinctItems($cart_id) {
    $sql = "SELECT COUNT(*) AS cnt
                FROM {$this->table}
                WHERE cart_id = $cart_id";

    $res = $this->db_query($sql);
    $row = $this->db_fetch($res);

    return $row && isset($row['cnt']) ? (int)$row['cnt'] : 0;
  }

  // Lấy 1 item theo cart + product + variant
  public function findItem($cart_id, $product_id, $variant_id) {
    $sql = "SELECT * FROM {$this->table}
                WHERE cart_id = $cart_id
                AND product_id = $product_id
                AND variant_id = $variant_id
                LIMIT 1";

    $res = $this->db_query($sql);
    return $this->db_fetch($res);
  }

  // Update số lượng item
  public function updateQuantity($item_id, $quantity) {
    $sql = "UPDATE {$this->table}
                SET quantity = $quantity
                WHERE id = $item_id";

    return $this->db_query($sql);
  }

  public function removeItem($item_id) {
    $sql = "DELETE FROM {$this->table} WHERE id = $item_id";
    return $this->db_query($sql);
  }

  // Lấy tất cả item trong cart
  public function getCartItems($cart_id) {
    $sql = "SELECT ci.*,
                       p.name AS product_name,
                       p.image AS product_image,
                       v.sku,
                       v.image_id AS variant_image
                FROM cart_items ci
                LEFT JOIN products p ON p.id = ci.product_id
                LEFT JOIN product_variants v ON v.id = ci.variant_id
                WHERE ci.cart_id = $cart_id
                ORDER BY ci.id DESC";

    $result = $this->db_query($sql);
    $items = [];

    if ($result) {
      while ($row = $this->db_fetch($result)) {
        $items[] = $row;
      }
    }

    return $items;
  }

  // Tổng tiền
  public function getCartTotal($cart_id) {
    $sql = "SELECT SUM(price * quantity) AS total
                FROM {$this->table}
                WHERE cart_id = $cart_id";

    $res = $this->db_query($sql);
    $row = $this->db_fetch($res);

    return $row && $row['total'] !== null ? $row['total'] : 0;
  }
}
