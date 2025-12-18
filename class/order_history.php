<?php

require_once 'db.php';

class OrderHistoryModel extends DB {
  protected $table = 'order_history';

  public function __construct() {
    parent::__construct();
  }

  public function createHistory($data) {
    return $this->db_insert($this->table, [
      'order_id'   => $data['order_id'],
      'status'     => $data['status'],
      'note'       => $data['note'],
      'created_at' => date('Y-m-d H:i:s')
    ]);
  }

  public function getByOrderId($orderId) {
    $orderId = (int)$orderId;
    $result = $this->db_query(
      "SELECT * FROM {$this->table}
       WHERE order_id = $orderId
       ORDER BY created_at ASC"
    );
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy lịch sử đơn hàng
   */
  public function getOrderHistory($order_id) {
    $sql = "SELECT * FROM order_history WHERE order_id = ? ORDER BY created_at DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
  }

  /**
   * Xóa lịch sử theo order_id
   */
  public function deleteHistoryByOrderId($order_id) {
    $order_id = (int)$order_id;
    return $this->db_delete($this->table, "order_id = {$order_id}");
  }

  /**
   * Lấy trạng thái cuối cùng
   */
  public function getLastStatus($order_id) {
    $order_id = (int)$order_id;

    $sql = "SELECT status FROM {$this->table}
                WHERE order_id = {$order_id}
                ORDER BY created_at DESC LIMIT 1";

    $result = $this->db_query($sql);
    $row = $this->db_fetch($result);

    return $row['status'] ?? null;
  }
}
