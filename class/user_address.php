<?php

require_once 'db.php';

class UserAddress extends DB {
  protected $table = 'user_addresses';

  public function __construct() {
    parent::__construct();
  }

  /**
   * Lấy tất cả địa chỉ của user
   */
  public function getAddressesByUser($userId) {
    $userId = intval($userId);

    $sql = "SELECT * FROM {$this->table}
                WHERE user_id = $userId
                ORDER BY is_default DESC, id DESC";

    $result = $this->db_query($sql);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy địa chỉ mặc định
   */
  public function getDefaultAddress($userId) {
    $userId = intval($userId);

    $sql = "SELECT * FROM {$this->table}
                WHERE user_id = $userId AND is_default = 1
                LIMIT 1";

    $result = $this->db_query($sql);
    return $this->db_fetch($result);
  }

  /**
   * Lấy 1 địa chỉ theo id
   */
  public function getAddressById($id, $userId) {
    $id = intval($id);
    $userId = intval($userId);

    $sql = "SELECT * FROM {$this->table}
                WHERE id = $id AND user_id = $userId";

    $result = $this->db_query($sql);
    return $this->db_fetch($result);
  }

  /**
   * Thêm địa chỉ mới
   */
  public function addAddress($userId, $name, $recipient_name, $phone, $address, $isDefault = 0) {
    $userId = intval($userId);
    $isDefault = intval($isDefault);

    // Nếu đặt default → xóa default cũ
    if ($isDefault == 1) {
      $this->removeDefault($userId);
    }

    $data = [
      'user_id'    => $userId,
      'title'       => $name,
      'recipient_name' => $recipient_name,
      'phone'      => $phone,
      'address'    => $address,
      'is_default' => $isDefault,
    ];

    return $this->db_insert($this->table, $data);
  }

  /**
   * Cập nhật địa chỉ
   */
  public function updateAddress($id, $userId, $name, $phone, $address) {
    $id = intval($id);
    $userId = intval($userId);

    $data = [
      'name'    => $name,
      'phone'   => $phone,
      'address' => $address
    ];

    return $this->db_update($this->table, $data,
      "id = $id AND user_id = $userId"
    );
  }

  /**
   * Xóa địa chỉ
   */
  public function deleteAddress($id, $userId) {
    $id = intval($id);
    $userId = intval($userId);

    return $this->db_delete($this->table,
      "id = $id AND user_id = $userId"
    );
  }

  /**
   * Bỏ default tất cả địa chỉ của user
   */
  private function removeDefault($userId) {
    $userId = intval($userId);

    $sql = "UPDATE {$this->table}
                SET is_default = 0
                WHERE user_id = $userId";

    return $this->db_query($sql);
  }

  /**
   * Đặt địa chỉ làm mặc định
   */
  public function setDefault($id, $userId) {
    $id = intval($id);
    $userId = intval($userId);

    // Bỏ default cũ
    $this->removeDefault($userId);

    // Set default mới
    $sql = "UPDATE {$this->table}
                SET is_default = 1
                WHERE id = $id AND user_id = $userId";

    return $this->db_query($sql);
  }
}
