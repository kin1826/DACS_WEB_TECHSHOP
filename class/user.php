<?php

include_once 'db.php';

class User extends DB {
  protected $table = 'users';

  public function __construct() {
    parent::__construct(); // Gọi constructor của class cha (DB)
  }

  /**
   * Tìm user theo ID
   */
  public function findById($id) {
    $id = $this->db_escape($id);
    $result = $this->db_query("SELECT * FROM {$this->table} WHERE id = '$id'");
    return $this->db_fetch($result);
  }

  /**
   * Tìm user theo username
   */
  public function findByUsername($username) {
    $username = $this->db_escape($username);
    $result = $this->db_query("SELECT * FROM {$this->table} WHERE username = '$username'");
    return $this->db_fetch($result);
  }

  /**
   * Tìm user theo email
   */
  public function findByEmail($email) {
    $email = $this->db_escape($email);
    $result = $this->db_query("SELECT * FROM {$this->table} WHERE email = '$email'");
    return $this->db_fetch($result);
  }

  /**
   * Tìm user theo Google ID
   */
  public function findByGoogleId($googleId) {
    $googleId = $this->db_escape($googleId);
    $result = $this->db_query("SELECT * FROM {$this->table} WHERE id_google = '$googleId'");
    return $this->db_fetch($result);
  }

  /**
   * Tìm user theo Facebook ID
   */
  public function findByFacebookId($facebookId) {
    $facebookId = $this->db_escape($facebookId);
    $result = $this->db_query("SELECT * FROM {$this->table} WHERE id_facebook = '$facebookId'");
    return $this->db_fetch($result);
  }

  /**
   * Tạo user mới
   */
  public function create($userData) {
    $fields = [];
    $values = [];

    foreach ($userData as $field => $value) {
      $fields[] = "`" . $this->db_escape($field) . "`";
      $values[] = "'" . $this->db_escape($value) . "'";
    }

    $fields_str = implode(", ", $fields);
    $values_str = implode(", ", $values);

    $query = "INSERT INTO {$this->table} ($fields_str) VALUES ($values_str)";
    $result = $this->db_query($query);

    if ($result) {
      return $this->db_insert_id();
    }

    return false;
  }

  /**
   * Cập nhật user
   */
  public function update($id, $userData) {
    if (!is_array($userData) || empty($userData)) return false;

    $set_parts = [];
    foreach ($userData as $field => $value) {
      if ($value === null || $value === '') {
        // Xử lý giá trị NULL hoặc rỗng
        $set_parts[] = "`" . $this->db_escape($field) . "` = NULL";
      } else {
        $set_parts[] = "`" . $this->db_escape($field) . "` = '" . $this->db_escape($value) . "'";
      }
    }

    $set_str = implode(", ", $set_parts);
    $id = $this->db_escape($id);
    $query = "UPDATE {$this->table} SET $set_str WHERE id = '$id'";

    return $this->db_query($query) !== false;
  }

  /**
   * Xóa user
   */
  public function delete($id) {
    $id = $this->db_escape($id);
    $query = "DELETE FROM {$this->table} WHERE id = '$id'";
    return $this->db_query($query) !== false;
  }

  /**
   * Đăng ký user mới (thông thường)
   */
  public function register($username, $email, $password, $additionalData = []) {
    // Kiểm tra username và email đã tồn tại chưa
    if ($this->findByUsername($username)) {
      throw new Exception("Username already exists");
    }

    if ($this->findByEmail($email)) {
      throw new Exception("Email already exists");
    }

    $userData = [
      'username' => $username,
      'email' => $email,
      'password_hash' => password_hash($password, PASSWORD_DEFAULT),
      'created_at' => date('Y-m-d H:i:s')
    ];

    // Thêm các thông tin bổ sung
    $userData = array_merge($userData, $additionalData);

    return $this->create($userData);
  }

  /**
   * Đăng nhập (username/email + password)
   */
  public function login($identifier, $password) {
    // Kiểm tra xem là username hay email
    $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    $identifier = $this->db_escape($identifier);

    $result = $this->db_query("SELECT * FROM {$this->table} WHERE $field = '$identifier'");
    $user = $this->db_fetch($result);

    if ($user && password_verify($password, $user['password_hash'])) {
      return $user;
    }

    return false;
  }

  public function check_register($user_id): bool
  {
    $user_id = (int)$user_id;

    $sql = "SELECT is_register_inf
          FROM {$this->table}
          WHERE id = $user_id
          LIMIT 1";

    $result = $this->db_query($sql);
    if (!$result) return false;

    $row = $this->db_fetch($result);

    return isset($row['is_register_inf']) && (bool)$row['is_register_inf'];
  }

  public function update_register($user_id): bool
  {
    $sql = "UPDATE {$this->table} SET is_register_inf = 1 WHERE id = {$user_id}";
    return $this->db_query($sql) !== false;
  }


  /**
   * Đăng ký/login với Google
   */
  public function handleGoogleLogin($googleUser) {
    // Tìm user theo Google ID
    $user = $this->findByGoogleId($googleUser['id']);

    if ($user) {
      return $user; // User đã tồn tại
    }

    // Tìm theo email (nếu user đã đăng ký trước đó bằng email)
    $user = $this->findByEmail($googleUser['email']);
    if ($user) {
      // Cập nhật thêm Google ID
      $this->update($user['id'], ['id_google' => $googleUser['id']]);
      return $user;
    }

    // Tạo user mới
    $userData = [
      'id_google' => $googleUser['id'],
      'username' => $this->generateUsername($googleUser['email']),
      'email' => $googleUser['email'],
      'avatar' => isset($googleUser['picture']) ? $googleUser['picture'] : null,
      'created_at' => date('Y-m-d H:i:s')
    ];

    $userId = $this->create($userData);
    return $this->findById($userId);
  }

  /**
   * Đăng ký/login với Facebook
   */
  public function handleFacebookLogin($facebookUser) {
    // Tìm user theo Facebook ID
    $user = $this->findByFacebookId($facebookUser['id']);

    if ($user) {
      return $user;
    }

    // Tìm theo email
    $user = $this->findByEmail($facebookUser['email']);
    if ($user) {
      $this->update($user['id'], ['id_facebook' => $facebookUser['id']]);
      return $user;
    }

    // Tạo user mới
    $userData = [
      'id_facebook' => $facebookUser['id'],
      'username' => $this->generateUsername($facebookUser['email']),
      'email' => $facebookUser['email'],
      'avatar' => isset($facebookUser['picture']['url']) ? $facebookUser['picture']['url'] : null,
      'created_at' => date('Y-m-d H:i:s')
    ];

    $userId = $this->create($userData);
    return $this->findById($userId);
  }

  /**
   * Tạo username từ email
   */
  private function generateUsername($email) {
    $username = strtok($email, '@');
    $baseUsername = $username;
    $counter = 1;

    // Đảm bảo username là duy nhất
    while ($this->findByUsername($username)) {
      $username = $baseUsername . $counter;
      $counter++;
    }

    return $username;
  }

  /**
   * Cập nhật điểm số
   */
  public function updatePoints($userId, $points) {
    $userId = $this->db_escape($userId);
    $points = (int)$points;

    $query = "UPDATE {$this->table} SET points = points + $points WHERE id = '$userId'";
    return $this->db_query($query) !== false;
  }

  /**
   * Nâng cấp level user
   */
  public function upgradeLevel($userId, $level) {
    $allowedLevels = ['normal', 'vip', 'premium'];
    if (!in_array($level, $allowedLevels)) {
      return false;
    }

    return $this->update($userId, ['level_u' => $level]);
  }

  /**
   * Lấy tất cả users (có phân trang)
   */
  public function getAll($limit = 50, $offset = 0) {
    $limit = (int)$limit;
    $offset = (int)$offset;

    $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $result = $this->db_query($query);

    $users = [];
    while ($row = $this->db_fetch($result)) {
      $users[] = $row;
    }

    return $users;
  }

  /**
   * Đếm tổng số users
   */
  public function count() {
    $result = $this->db_query("SELECT COUNT(*) as total FROM {$this->table}");
    $row = $this->db_fetch($result);
    return isset($row['total']) ? $row['total'] : 0;
  }

  /**
   * Kiểm tra user có phải admin không
   */
  public function isAdmin($userId) {
    $user = $this->findById($userId);
    return $user && $user['is_admin'] == 1;
  }
}
?>
