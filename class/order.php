<?php

require_once 'db.php';
require_once 'order_history.php';

class OrderModel extends DB {
  protected $table = 'orders';

  public function __construct() {
    parent::__construct();
  }

  /**
   * Tạo order + order_items + order_history (transaction)
   */
  public function createOrderWithItems($orderData, $items = []) {

    // Start transaction
    $this->db_query("START TRANSACTION");

    try {

      // 1️⃣ Tạo order
      $orderId = $this->createOrder($orderData);
      if (!$orderId) {
        throw new Exception('Không thể tạo đơn hàng');
      }

      // 2️⃣ Tạo order items
      $orderItemModel = new OrderItemModel();

      foreach ($items as $item) {

        $orderItemData = [
          'order_id'     => $orderId,
          'product_id'   => (int)$item['product_id'],
          'variant_id'   => $item['variant_id'] ?: null,
          'product_name' => $item['product_name'],
          'product_sku'  => $item['product_sku'],
          'quantity'     => (int)$item['quantity'],
          'unit_price'   => (float)$item['unit_price']
        ];

        if (!$orderItemModel->createOrderItem($orderItemData)) {
          throw new Exception('Không thể thêm sản phẩm vào đơn hàng');
        }
      }

      // 3️⃣ Tạo lịch sử đơn hàng
      $orderHistoryModel = new OrderHistoryModel();
      $historyData = [
        'order_id' => $orderId,
        'status'   => 'pending',
        'note'     => 'Đơn hàng được tạo'
      ];

      if (!$orderHistoryModel->createHistory($historyData)) {
        throw new Exception('Không thể tạo lịch sử đơn hàng');
      }

      // 4️⃣ Commit
      $this->db_query("COMMIT");

      return [
        'order_id' => $orderId,
        'order_number' => $this->getOrderNumberById($orderId)
      ];

    } catch (Exception $e) {
      // Rollback nếu lỗi
      $this->db_query("ROLLBACK");
      $this->error = $e->getMessage();
      return false;

//      $this->db_query("ROLLBACK");
//
//      echo "<script>console.log(" . json_encode($e->getMessage()) . ");</script>";
//      exit;
    }
  }

  /**
   * Tạo order
   */
  public function createOrder($data) {
    return $this->db_insert($this->table, [
      'order_number'     => $data['order_number'] ?? $this->generateOrderNumber(),
      'user_id'          => $data['user_id'],
      'customer_name'    => $data['customer_name'],
      'customer_phone'   => $data['customer_phone'],
      'shipping_address' => $data['shipping_address'],
      'subtotal'         => $data['subtotal'],
      'shipping_fee'     => $data['shipping_fee'],
      'discount_amount'  => $data['discount_amount'],
      'total_amount'     => $data['total_amount'],
      'payment_method'   => $data['payment_method'],
      'payment_status'   => $data['payment_status'],
      'order_status'     => $data['order_status'],
      'notes'            => $data['notes']
    ]);
  }

  /**
   * Lấy order number
   */
  public function getOrderNumberById($id) {
    $id = (int)$id;
    $result = $this->db_query(
      "SELECT order_number FROM {$this->table} WHERE id = $id"
    );
    $row = $this->db_fetch($result);
    return $row['order_number'] ?? null;
  }

  /**
   * Sinh mã đơn hàng
   */
  private function generateOrderNumber() {
    return 'ORD' . date('YmdHis') . rand(100, 999);
  }

  public function countOrder($user_id): int {
    $sql = "SELECT COUNT(*) AS total FROM {$this->table} WHERE user_id = {$user_id}";
    $res = $this->db_query($sql);
    $row = $this->db_fetch($res);
    return (int)($row['total'] ?? 0);
  }


  public function getAllOrderByUserId($user_id): array {
    $sql = "SELECT * FROM {$this->table} WHERE user_id = {$user_id}";
    $res = $this->db_query($sql);
    return $this->db_fetch_all($res);
  }

  public function getOrdersWithItemsByUserId($user_id) {
    $user_id = (int)$user_id;

    // 1️⃣ Lấy orders
    $sqlOrders = "
    SELECT
      o.id,
      o.order_number,
      o.order_status,
      o.total_amount,
      o.created_at
    FROM {$this->table} o
    WHERE o.user_id = $user_id
    ORDER BY o.created_at DESC
  ";

    $orders = $this->db_fetch_all($this->db_query($sqlOrders));
    if (!$orders) return [];

    // 2️⃣ Lấy danh sách order_id
    $orderIds = array_column($orders, 'id');
    $orderIdList = implode(',', array_map('intval', $orderIds));

    // 3️⃣ Lấy items của các orders
    $sqlItems = "
    SELECT
      oi.product_id,
      oi.order_id,
      oi.product_name,
      oi.unit_price,
      oi.quantity,
      pi.image_url
    FROM order_items oi
    LEFT JOIN product_images pi
      ON oi.product_id = pi.product_id AND pi.is_main = 1
    WHERE oi.order_id IN ($orderIdList)
  ";

    $items = $this->db_fetch_all($this->db_query($sqlItems));

    // 4️⃣ Gom items theo order_id
    $itemsByOrder = [];
    foreach ($items as $item) {
      $itemsByOrder[$item['order_id']][] = [
        'product_id'   => (int)$item['product_id'],
        'product_name' => $item['product_name'],
        'price'        => (float)$item['unit_price'],
        'quantity'     => (int)$item['quantity'],
        'image'        => $item['image_url']
          ? 'img/adminUP/products/' . $item['image_url']
          : 'img/no-image.png'
      ];
    }

    // 5️⃣ Gộp orders + items (format đúng cho frontend)
    $result = [];
    foreach ($orders as $order) {
      $result[] = [
        'order_id'     => (int)$order['id'],
        'order_number' => $order['order_number'],
        'order_status' => $order['order_status'],
        'order_date'   => $order['created_at'],
        'total_amount' => (float)$order['total_amount'],
        'items'        => $itemsByOrder[$order['id']] ?? []
      ];
    }

    return $result;
  }

  /**
   * Lấy tất cả đơn hàng với tìm kiếm và lọc (cho admin)
   */
  public function getAllOrdersAdmin($search = '', $status = '', $payment_status = '', $start_date = '', $end_date = '', $limit = 50, $offset = 0) {
    $sql = "SELECT o.*, u.username as customer_username, u.email as customer_email
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE 1=1";

    $whereConditions = [];

    // Tìm kiếm
    if (!empty($search)) {
      $search = $this->db_escape($search);
      $whereConditions[] = "(
                o.order_number LIKE '%{$search}%' OR
                o.customer_name LIKE '%{$search}%' OR
                o.customer_phone LIKE '%{$search}%' OR
                u.username LIKE '%{$search}%' OR
                u.email LIKE '%{$search}%'
            )";
    }

    // Lọc theo trạng thái đơn hàng
    if (!empty($status) && $status !== 'all') {
      $status = $this->db_escape($status);
      $whereConditions[] = "o.order_status = '{$status}'";
    }

    // Lọc theo trạng thái thanh toán
    if (!empty($payment_status) && $payment_status !== 'all') {
      $payment_status = $this->db_escape($payment_status);
      $whereConditions[] = "o.payment_status = '{$payment_status}'";
    }

    // Lọc theo ngày
    if (!empty($start_date)) {
      $start_date = $this->db_escape($start_date);
      $whereConditions[] = "DATE(o.created_at) >= '{$start_date}'";
    }

    if (!empty($end_date)) {
      $end_date = $this->db_escape($end_date);
      $whereConditions[] = "DATE(o.created_at) <= '{$end_date}'";
    }

    if (!empty($whereConditions)) {
      $sql .= " AND " . implode(" AND ", $whereConditions);
    }

    $sql .= " ORDER BY o.created_at DESC LIMIT {$limit} OFFSET {$offset}";

    $result = $this->db_query($sql);
    return $this->db_fetch_all($result);
  }

  /**
   * Đếm tổng số đơn hàng (cho phân trang admin)
   */

  /**
   * @param 'today'|'week'|'month'|'precious' $type
   * @return int
   */
  public function countOrderNum(string $type): int
  {
    switch ($type) {

      case 'today':
        $sql = "
                SELECT COUNT(*) AS total
                FROM {$this->table}
                WHERE DATE(created_at) = CURDATE()
            ";
        break;

      case 'week':
        $sql = "
                SELECT COUNT(*) AS total
                FROM {$this->table}
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ";
        break;

      case 'month':
        $sql = "
                SELECT COUNT(*) AS total
                FROM {$this->table}
                WHERE YEAR(created_at) = YEAR(CURDATE())
                  AND MONTH(created_at) = MONTH(CURDATE())
            ";
        break;

      case 'precious': // quý hiện tại
        $sql = "
                SELECT COUNT(*) AS total
                FROM {$this->table}
                WHERE YEAR(created_at) = YEAR(CURDATE())
                  AND QUARTER(created_at) = QUARTER(CURDATE())
            ";
        break;

      default:
        return 0;
    }

    $result = $this->db_fetch($this->db_query($sql));
    return (int) ($result['total'] ?? 0);
  }




  public function countOrdersAdmin($search = '', $status = '', $payment_status = '') {
    $sql = "SELECT COUNT(*) as total FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE 1=1";

    $whereConditions = [];

    // Tìm kiếm
    if (!empty($search)) {
      $search = $this->db_escape($search);
      $whereConditions[] = "(
                o.order_number LIKE '%{$search}%' OR
                o.customer_name LIKE '%{$search}%' OR
                o.customer_phone LIKE '%{$search}%' OR
                u.username LIKE '%{$search}%' OR
                u.email LIKE '%{$search}%'
            )";
    }

    // Lọc theo trạng thái đơn hàng
    if (!empty($status) && $status !== 'all') {
      $status = $this->db_escape($status);
      $whereConditions[] = "o.order_status = '{$status}'";
    }

    // Lọc theo trạng thái thanh toán
    if (!empty($payment_status) && $payment_status !== 'all') {
      $payment_status = $this->db_escape($payment_status);
      $whereConditions[] = "o.payment_status = '{$payment_status}'";
    }

    if (!empty($whereConditions)) {
      $sql .= " AND " . implode(" AND ", $whereConditions);
    }

    $result = $this->db_query($sql);
    $row = $this->db_fetch($result);
    return (int)($row['total'] ?? 0);
  }

  /**
   * Lấy thông tin chi tiết đơn hàng (cho admin)
   */
  public function getOrderDetailAdmin($order_id) {
    $order_id = (int)$order_id;

    $sql = "SELECT *
                FROM {$this->table}
                WHERE id = {$order_id}";

    $result = $this->db_query($sql);
    $order = $this->db_fetch($result);

    if (!$order) {
      return null;
    }

    // Lấy danh sách sản phẩm trong đơn hàng
    $orderItemModel = new OrderItemModel();
    $order['items'] = $orderItemModel->getDetailedItemsByOrderId($order_id);

    // Lấy lịch sử đơn hàng
    $orderHistoryModel = new OrderHistoryModel();
    $order['history'] = $orderHistoryModel->getByOrderId($order_id);

    return $order;
  }

  /**
   * Cập nhật trạng thái đơn hàng
   */
  public function updateOrderStatus($order_id, $status, $note = '') {
    $order_id = (int)$order_id;
    $status = $this->db_escape($status);
    $note = $this->db_escape($note);

    // Start transaction
    $this->db_query("START TRANSACTION");

    try {
      // Cập nhật trạng thái đơn hàng
      $data = [
        'order_status' => $status,
        'updated_at' => date('Y-m-d H:i:s')
      ];

      if (!empty($note)) {
        $data['notes'] = $note;
      }

      $updateResult = $this->db_update($this->table, $data, "id = {$order_id}");

      if (!$updateResult) {
        throw new Exception('Không thể cập nhật trạng thái đơn hàng');
      }

      // Thêm vào lịch sử
      $orderHistoryModel = new OrderHistoryModel();
      $historyData = [
        'order_id' => $order_id,
        'status'   => $status,
        'note'     => $note ?: 'Cập nhật trạng thái'
      ];

      if (!$orderHistoryModel->createHistory($historyData)) {
        throw new Exception('Không thể thêm lịch sử đơn hàng');
      }

      // Commit
      $this->db_query("COMMIT");

      return true;

    } catch (Exception $e) {
      // Rollback
      $this->db_query("ROLLBACK");
      $this->error = $e->getMessage();
      return false;
    }
  }

  /**
   * Cập nhật trạng thái thanh toán
   */
  public function updatePaymentStatus($order_id, $payment_status, $note = '') {
    $order_id = (int)$order_id;
    $payment_status = $this->db_escape($payment_status);
    $note = $this->db_escape($note);

    $data = [
      'payment_status' => $payment_status,
      'updated_at' => date('Y-m-d H:i:s')
    ];

    if (!empty($note)) {
      $data['notes'] = $note;
    }

    return $this->db_update($this->table, $data, "id = {$order_id}");
  }

  public function insertToProduct($product_id) {
    // Lấy rate + num_buy hiện tại
    $product = $this->db_fetch(
      $this->db_query("SELECT rate, num_buy FROM products WHERE id = $product_id")
    );

    // Update product
    $this->db_update('products', [
      'num_buy' => $product['num_buy'] + 1
    ], "id = $product_id");
  }

  /**
   * Cập nhật thông tin đơn hàng
   */
  public function updateOrder($order_id, $data) {
    $order_id = (int)$order_id;

    $allowed_fields = [
      'customer_name', 'customer_phone', 'shipping_address',
      'shipping_fee', 'discount_amount', 'notes',
      'payment_method'
    ];

    $update_data = [];
    foreach ($allowed_fields as $field) {
      if (isset($data[$field])) {
        $update_data[$field] = $data[$field];
      }
    }

    if (empty($update_data)) {
      return false;
    }

    $update_data['updated_at'] = date('Y-m-d H:i:s');

    return $this->db_update($this->table, $update_data, "id = {$order_id}");
  }

  /**
   * Hủy đơn hàng
   */
  public function cancelOrder($order_id, $reason = '') {
    $order_id = (int)$order_id;
    $reason = $this->db_escape($reason);

    // Lấy thông tin đơn hàng hiện tại
    $order = $this->getById($order_id);
    if (!$order) {
      return false;
    }

    // Chỉ cho phép hủy nếu đơn hàng đang ở trạng thái pending hoặc processing
    if (!in_array($order['order_status'], ['pending', 'processing'])) {
      return false;
    }

    return $this->updateOrderStatus($order_id, 'cancelled', $reason);
  }

  /**
   * Lấy thống kê đơn hàng (cho admin dashboard)
   */
  public function getOrderStatistics() {
    $stats = [
      'total_orders' => 0,
      'pending_orders' => 0,
      'processing_orders' => 0,
      'shipped_orders' => 0,
      'delivered_orders' => 0,
      'cancelled_orders' => 0,
      'total_revenue' => 0,
      'avg_order_value' => 0
    ];

    $sql = "SELECT
                COUNT(*) as total_orders,
                SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                SUM(CASE WHEN order_status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN order_status = 'delivered' THEN total_amount ELSE 0 END) as total_revenue
                FROM {$this->table}";

    $result = $this->db_query($sql);
    $data = $this->db_fetch($result);

    if ($data) {
      $stats['total_orders'] = (int)$data['total_orders'];
      $stats['pending_orders'] = (int)$data['pending_orders'];
      $stats['processing_orders'] = (int)$data['processing_orders'];
      $stats['shipped_orders'] = (int)$data['shipped_orders'];
      $stats['delivered_orders'] = (int)$data['delivered_orders'];
      $stats['cancelled_orders'] = (int)$data['cancelled_orders'];
      $stats['total_revenue'] = (float)$data['total_revenue'];
      $stats['avg_order_value'] = $data['total_orders'] > 0 ?
        $data['total_revenue'] / $data['total_orders'] : 0;
    }

    // Thống kê theo ngày (7 ngày gần nhất)
    $sql_recent = "SELECT
                      DATE(created_at) as date,
                      COUNT(*) as order_count,
                      SUM(total_amount) as revenue
                      FROM {$this->table}
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                      GROUP BY DATE(created_at)
                      ORDER BY date";

    $result_recent = $this->db_query($sql_recent);
    $stats['recent_days'] = $this->db_fetch_all($result_recent);

    return $stats;
  }

  /**
   * Lấy đơn hàng theo ID
   */
  public function getById($id) {
    $id = (int)$id;
    $result = $this->db_query("SELECT * FROM {$this->table} WHERE id = {$id}");
    return $this->db_fetch($result);
  }

  /**
   * Lấy đơn hàng theo order number
   */
  public function getByOrderNumber($order_number) {
    $order_number = $this->db_escape($order_number);
    $result = $this->db_query("SELECT * FROM {$this->table} WHERE order_number = '{$order_number}'");
    return $this->db_fetch($result);
  }

  /**
   * Kiểm tra xem user có phải là chủ đơn hàng không
   */
  public function isOrderOwner($order_id, $user_id) {
    $order_id = (int)$order_id;
    $user_id = (int)$user_id;

    $sql = "SELECT COUNT(*) as count FROM {$this->table}
                WHERE id = {$order_id} AND user_id = {$user_id}";

    $result = $this->db_query($sql);
    $row = $this->db_fetch($result);

    return ($row['count'] ?? 0) > 0;
  }

  /**
   * Lấy tổng doanh thu theo tháng
   */
  public function getMonthlyRevenue($year = null, $month = null) {
    $year = $year ?? date('Y');
    $month = $month ?? date('m');

    $sql = "SELECT
                COALESCE(SUM(total_amount), 0) AS revenue,
                COUNT(*) as order_count
                FROM {$this->table}
                WHERE order_status = 'delivered' AND payment_status = 'paid'
                AND YEAR(created_at) = {$year}
                AND MONTH(created_at) = {$month}";

    $result = $this->db_query($sql);
    return $this->db_fetch($result);
  }

  /**
   * Lấy thống kê theo TG
   */
  public function getOrderStatusStats($period = 'month')
  {
    // Điều kiện thời gian
    $timeWhere = '';
    switch ($period) {
      case 'week':
        $timeWhere = "AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;

      case 'month':
        $timeWhere = "AND YEAR(created_at)=YEAR(CURDATE())
                          AND MONTH(created_at)=MONTH(CURDATE())";
        break;

      case 'year':
        $timeWhere = "AND YEAR(created_at)=YEAR(CURDATE())";
        break;
    }

    $sql = "
        SELECT
            SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) AS completed,
            SUM(CASE WHEN order_status IN ('pending','processing','shipped') THEN 1 ELSE 0 END) AS processing,
            SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) AS unpaid,
            SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
        FROM {$this->table}
        WHERE 1=1 {$timeWhere}
    ";

    return $this->db_fetch($this->db_query($sql));
  }


  /**
   * Lấy doanh thu theo 1 năm
   */
  public function getRevenueByYear($year = null)
  {
    $year = (int) ($year ?? date('Y'));

    $sql = "
        SELECT
            MONTH(created_at) AS month,
            COALESCE(SUM(total_amount), 0) AS revenue
        FROM {$this->table}
        WHERE order_status = 'delivered'
          AND payment_status = 'paid'
          AND YEAR(created_at) = {$year}
        GROUP BY MONTH(created_at)
        ORDER BY month
    ";

    $rows = $this->db_fetch_all($this->db_query($sql));

    // Chuẩn hóa đủ 12 tháng
    $result = array_fill(1, 12, 0);

    foreach ($rows as $row) {
      $result[(int)$row['month']] = (float)$row['revenue'];
    }

    return $result;
  }


  /**
   * Lấy đơn hàng mới nhất (cho dashboard)
   */
  public function getRecentOrders($limit = 10) {
    $limit = (int)$limit;

    $sql = "SELECT o.*, u.username as customer_username
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
                LIMIT {$limit}";

    $result = $this->db_query($sql);
    return $this->db_fetch_all($result);
  }

  /**
   * Xóa đơn hàng (chỉ admin)
   */
  public function deleteOrder($order_id) {
    $order_id = (int)$order_id;

    // Start transaction
    $this->db_query("START TRANSACTION");

    try {
      // Kiểm tra xem đơn hàng có thể xóa không (chỉ xóa đơn hàng đã hủy)
      $order = $this->getById($order_id);
      if (!$order || $order['order_status'] !== 'cancelled') {
        throw new Exception('Chỉ có thể xóa đơn hàng đã hủy');
      }

      // Xóa order items
      $orderItemModel = new OrderItemModel();
      if (!$orderItemModel->deleteItemsByOrderId($order_id)) {
        throw new Exception('Không thể xóa sản phẩm trong đơn hàng');
      }

      // Xóa lịch sử
      $orderHistoryModel = new OrderHistoryModel();
      if (!$orderHistoryModel->deleteHistoryByOrderId($order_id)) {
        throw new Exception('Không thể xóa lịch sử đơn hàng');
      }

      // Xóa đơn hàng
      $deleteResult = $this->db_delete($this->table, "id = {$order_id}");
      if (!$deleteResult) {
        throw new Exception('Không thể xóa đơn hàng');
      }

      // Commit
      $this->db_query("COMMIT");

      return true;

    } catch (Exception $e) {
      // Rollback
      $this->db_query("ROLLBACK");
      $this->error = $e->getMessage();
      return false;
    }
  }

  /**
   * Xuất dữ liệu đơn hàng (cho báo cáo)
   */
  public function exportOrders($start_date, $end_date, $status = '') {
    $start_date = $this->db_escape($start_date);
    $end_date = $this->db_escape($end_date);

    $sql = "SELECT
                o.order_number,
                o.customer_name,
                o.customer_phone,
                o.shipping_address,
                o.subtotal,
                o.shipping_fee,
                o.discount_amount,
                o.total_amount,
                o.payment_method,
                o.payment_status,
                o.order_status,
                o.created_at,
                u.username,
                u.email
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE DATE(o.created_at) BETWEEN '{$start_date}' AND '{$end_date}'";

    if (!empty($status) && $status !== 'all') {
      $status = $this->db_escape($status);
      $sql .= " AND o.order_status = '{$status}'";
    }

    $sql .= " ORDER BY o.created_at";

    $result = $this->db_query($sql);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy đơn hàng cần xử lý (cho notification)
   */
  public function getPendingProcessingOrders() {
    $sql = "SELECT COUNT(*) as count
                FROM {$this->table}
                WHERE order_status IN ('pending', 'processing')";

    $result = $this->db_query($sql);
    $row = $this->db_fetch($result);

    return (int)($row['count'] ?? 0);
  }
}
