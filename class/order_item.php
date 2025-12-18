<?php

require_once 'db.php';

class OrderItemModel extends DB {
  protected $table = 'order_items';

  public function __construct() {
    parent::__construct();
  }

  public function createOrderItem($data) {
    return $this->db_insert($this->table, [
      'order_id'     => $data['order_id'],
      'product_id'   => $data['product_id'],
      'variant_id'   => $data['variant_id'],
      'product_name' => $data['product_name'],
      'product_sku'  => $data['product_sku'],
      'quantity'     => $data['quantity'],
      'unit_price'   => $data['unit_price'],
      'total_price'  => $data['unit_price'] * $data['quantity']
    ]);
  }

  public function getByOrderId($orderId) {
    $orderId = (int)$orderId;
    $result = $this->db_query("SELECT * FROM {$this->table} WHERE order_id = $orderId");
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy chi tiết items với thông tin sản phẩm và ảnh
   */
  public function getDetailedItemsByOrderId($orderId) {
    $orderId = (int)$orderId;

    $sql = "SELECT
                oi.*,
                p.name_pr as product_name_original,
                p.description as product_description,
                pv.sku as variant_sku,
                pv.stock_quantity as variant_stock,
                pi.image_url as product_image,
                pi.alt_text as image_alt
                FROM {$this->table} oi
                LEFT JOIN products p ON oi.product_id = p.id
                LEFT JOIN product_variants pv ON oi.variant_id = pv.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                WHERE oi.order_id = $orderId
                ORDER BY oi.id ASC";

    $result = $this->db_query($sql);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy item theo ID
   */
  public function getById($id) {
    $id = (int)$id;
    $result = $this->db_query("SELECT * FROM {$this->table} WHERE id = $id");
    return $this->db_fetch($result);
  }

  /**
   * Cập nhật số lượng item
   */
  public function updateQuantity($id, $quantity) {
    $id = (int)$id;
    $quantity = (int)$quantity;

    // Lấy thông tin item để lấy unit_price
    $item = $this->getById($id);
    if (!$item) {
      return false;
    }

    $total_price = $item['unit_price'] * $quantity;

    return $this->db_update($this->table, [
      'quantity' => $quantity,
      'total_price' => $total_price
    ], "id = $id");
  }

  /**
   * Xóa item theo ID
   */
  public function deleteItem($id) {
    $id = (int)$id;
    return $this->db_delete($this->table, "id = $id");
  }

  /**
   * Xóa tất cả items của một order
   */
  public function deleteAllByOrderId($orderId) {
    $orderId = (int)$orderId;
    return $this->db_delete($this->table, "order_id = $orderId");
  }

  /**
   * Tính tổng tiền của một order
   */
  public function calculateOrderTotal($orderId) {
    $orderId = (int)$orderId;

    $sql = "SELECT SUM(total_price) as total FROM {$this->table} WHERE order_id = $orderId";
    $result = $this->db_query($sql);
    $row = $this->db_fetch($result);

    return $row['total'] ?? 0;
  }

  /**
   * Đếm số lượng sản phẩm trong order
   */
  public function countItemsInOrder($orderId) {
    $orderId = (int)$orderId;

    $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE order_id = $orderId";
    $result = $this->db_query($sql);
    $row = $this->db_fetch($result);

    return $row['count'] ?? 0;
  }

  /**
   * Kiểm tra xem sản phẩm đã có trong order chưa
   */
  public function isProductInOrder($orderId, $productId, $variantId = null) {
    $orderId = (int)$orderId;
    $productId = (int)$productId;

    $sql = "SELECT COUNT(*) as count FROM {$this->table}
                WHERE order_id = $orderId AND product_id = $productId";

    if ($variantId !== null) {
      $variantId = (int)$variantId;
      $sql .= " AND variant_id = $variantId";
    }

    $result = $this->db_query($sql);
    $row = $this->db_fetch($result);

    return ($row['count'] ?? 0) > 0;
  }

  /**
   * Lấy sản phẩm bán chạy nhất (top sellers)
   */
  public function getTopSellingProducts($limit = 10, $period = 'all') {
    $limit = (int)$limit;

    $dateCondition = "";
    if ($period !== 'all') {
      switch ($period) {
        case 'today':
          $dateCondition = "AND DATE(o.created_at) = CURDATE()";
          break;
        case 'week':
          $dateCondition = "AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
          break;
        case 'month':
          $dateCondition = "AND MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())";
          break;
        case 'year':
          $dateCondition = "AND YEAR(o.created_at) = YEAR(CURDATE())";
          break;
      }
    }

    $sql = "SELECT
                oi.product_id,
                oi.product_name,
                SUM(oi.quantity) as total_sold,
                SUM(oi.total_price) as total_revenue,
                COUNT(DISTINCT oi.order_id) as order_count,
                p.name_pr as product_name_full,
                pi.image_url as product_image
                FROM {$this->table} oi
                INNER JOIN orders o ON oi.order_id = o.id
                LEFT JOIN products p ON oi.product_id = p.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE o.order_status = 'delivered' $dateCondition
                GROUP BY oi.product_id, oi.product_name
                ORDER BY total_sold DESC
                LIMIT $limit";

    $result = $this->db_query($sql);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy số lượng sản phẩm đã bán theo product_id
   */
  public function getProductSalesCount($productId, $variantId = null) {
    $productId = (int)$productId;

    $sql = "SELECT SUM(oi.quantity) as total_sold
                FROM {$this->table} oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE oi.product_id = $productId
                AND o.order_status = 'delivered'";

    if ($variantId !== null) {
      $variantId = (int)$variantId;
      $sql .= " AND oi.variant_id = $variantId";
    }

    $result = $this->db_query($sql);
    $row = $this->db_fetch($result);

    return $row['total_sold'] ?? 0;
  }

  /**
   * Lấy doanh thu theo product_id
   */
  public function getProductRevenue($productId, $variantId = null) {
    $productId = (int)$productId;

    $sql = "SELECT SUM(oi.total_price) as total_revenue
                FROM {$this->table} oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE oi.product_id = $productId
                AND o.order_status = 'delivered'";

    if ($variantId !== null) {
      $variantId = (int)$variantId;
      $sql .= " AND oi.variant_id = $variantId";
    }

    $result = $this->db_query($sql);
    $row = $this->db_fetch($result);

    return $row['total_revenue'] ?? 0;
  }

  /**
   * Lấy tất cả orders chứa một sản phẩm cụ thể
   */
  public function getOrdersContainingProduct($productId, $variantId = null) {
    $productId = (int)$productId;

    $sql = "SELECT DISTINCT oi.order_id, o.order_number, o.order_status, o.created_at
                FROM {$this->table} oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE oi.product_id = $productId";

    if ($variantId !== null) {
      $variantId = (int)$variantId;
      $sql .= " AND oi.variant_id = $variantId";
    }

    $sql .= " ORDER BY o.created_at DESC";

    $result = $this->db_query($sql);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy thống kê sản phẩm theo category
   */
  public function getSalesByCategory($limit = 10) {
    $limit = (int)$limit;

    $sql = "SELECT
                c.id as category_id,
                c.name as category_name,
                SUM(oi.quantity) as total_sold,
                SUM(oi.total_price) as total_revenue,
                COUNT(DISTINCT oi.order_id) as order_count
                FROM {$this->table} oi
                INNER JOIN orders o ON oi.order_id = o.id
                INNER JOIN products p ON oi.product_id = p.id
                INNER JOIN categories c ON p.id_cate = c.id
                WHERE o.order_status = 'delivered'
                GROUP BY c.id, c.name
                ORDER BY total_revenue DESC
                LIMIT $limit";

    $result = $this->db_query($sql);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy các sản phẩm thường được mua cùng nhau
   */
  public function getFrequentlyBoughtTogether($productId, $limit = 5) {
    $productId = (int)$productId;
    $limit = (int)$limit;

    $sql = "SELECT
                oi2.product_id,
                oi2.product_name,
                COUNT(*) as times_bought_together,
                SUM(oi2.quantity) as total_quantity,
                p.name_pr as product_name_full,
                pi.image_url as product_image
                FROM {$this->table} oi1
                INNER JOIN {$this->table} oi2 ON oi1.order_id = oi2.order_id
                LEFT JOIN products p ON oi2.product_id = p.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE oi1.product_id = $productId
                AND oi2.product_id != $productId
                GROUP BY oi2.product_id, oi2.product_name
                ORDER BY times_bought_together DESC
                LIMIT $limit";

    $result = $this->db_query($sql);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy lịch sử mua hàng của user với sản phẩm
   */
  public function getUserPurchaseHistory($userId, $productId, $limit = 10) {
    $userId = (int)$userId;
    $productId = (int)$productId;
    $limit = (int)$limit;

    $sql = "SELECT
                oi.*,
                o.order_number,
                o.order_status,
                o.created_at as order_date
                FROM {$this->table} oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE o.user_id = $userId
                AND oi.product_id = $productId
                ORDER BY o.created_at DESC
                LIMIT $limit";

    $result = $this->db_query($sql);
    return $this->db_fetch_all($result);
  }

  /**
   * Lấy tổng doanh thu theo khoảng thời gian
   */
  public function getRevenueByDateRange($startDate, $endDate) {
    $startDate = $this->db_escape($startDate);
    $endDate = $this->db_escape($endDate);

    $sql = "SELECT
                DATE(o.created_at) as date,
                SUM(oi.total_price) as daily_revenue,
                COUNT(DISTINCT o.id) as order_count,
                SUM(oi.quantity) as item_count
                FROM {$this->table} oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE o.order_status = 'delivered'
                AND DATE(o.created_at) BETWEEN '$startDate' AND '$endDate'
                GROUP BY DATE(o.created_at)
                ORDER BY date";

    $result = $this->db_query($sql);
    return $this->db_fetch_all($result);
  }
}
