<?php
class FlashSaleManager extends DB {
  protected $table = 'flash_sale';

  public function __construct() {
    parent::__construct();
  }

  // ==================== FLASH SALE CRUD ====================

  public function createFlashSale($name, $time_start, $time_end, $percent, $is_activity = 1) {
    $data = [
      'name_fl' => $name,
      'time_start' => $time_start,
      'time_end' => $time_end,
      'percent' => $percent,
      'is_activity' => $is_activity,
      'created_at' => date('Y-m-d H:i:s')
    ];

    return $this->db_insert($this->table, $data);
  }

  public function updateFlashSale($id, $name, $time_start, $time_end, $percent, $is_activity) {
    $data = [
      'name_fl' => $name,
      'time_start' => $time_start,
      'time_end' => $time_end,
      'percent' => $percent,
      'is_activity' => $is_activity,
      'updated_at' => date('Y-m-d H:i:s')
    ];

    $where = "id = " . $this->db_escape($id);
    return $this->db_update($this->table, $data, $where);
  }

  public function deleteFlashSale($id) {
    $where = "id = " . $this->db_escape($id);
    return $this->db_delete($this->table, $where);
  }

  public function getFlashSale($id) {
    $where = "id = " . $this->db_escape($id);
    $result = $this->db_select($this->table, $where);
    return !empty($result) ? $result[0] : null;
  }

  public function getAllFlashSales($status = 'all') {
    $where = "";

    if ($status == 'active') {
      $where = "is_activity = 1 AND time_start <= NOW() AND time_end >= NOW()";
      $order = "ORDER BY time_start DESC";
    } elseif ($status == 'upcoming') {
      $where = "time_start > NOW()";
      $order = "ORDER BY time_start ASC";
    } elseif ($status == 'ended') {
      $where = "time_end < NOW()";
      $order = "ORDER BY time_end DESC";
    } else {
      $order = "ORDER BY created_at DESC";
    }

    $query = "SELECT * FROM {$this->table}";
    if (!empty($where)) {
      $query .= " WHERE $where";
    }
    $query .= " $order";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  // ==================== PRODUCTS IN SALE ====================

  public function addProductToSale($sale_id, $product_id, $limit_buy, $quantity) {
    // Kiểm tra xem sản phẩm đã có trong sale chưa
    $check_query = "SELECT id FROM detail_sale
                       WHERE id_sale = " . $this->db_escape($sale_id) .
      " AND id_product = " . $this->db_escape($product_id);
    $check_result = $this->db_query($check_query);

    if ($this->db_num_rows($check_result) > 0) {
      return ['success' => false, 'message' => 'Sản phẩm đã có trong flash sale'];
    }

    $data = [
      'id_sale' => $sale_id,
      'id_product' => $product_id,
      'limit_buy' => $limit_buy,
      'quantity' => $quantity
    ];

    $result = $this->db_insert('detail_sale', $data);

    return [
      'success' => $result !== false,
      'message' => $result !== false ? 'Thêm thành công' : 'Có lỗi xảy ra',
      'insert_id' => $result
    ];
  }

  public function updateProductInSale($id, $limit_buy, $quantity) {
    $data = [
      'limit_buy' => $limit_buy,
      'quantity' => $quantity
    ];

    $where = "id = " . $this->db_escape($id);
    return $this->db_update('detail_sale', $data, $where);
  }

  public function removeProductFromSale($id) {
    $where = "id = " . $this->db_escape($id);
    return $this->db_delete('detail_sale', $where);
  }

  // Thêm phương thức này vào class FlashSaleManager
  public function searchAvailableProducts($sale_id = null, $search_term = '') {
    $query = "SELECT p.id, p.name_pr, p.sku, p.regular_price, p.stock_quantity, p.stock_status
             FROM products p
             WHERE p.status = 'published'";

    if ($sale_id) {
      $query .= " AND p.id NOT IN (
                    SELECT id_product FROM detail_sale
                    WHERE id_sale = " . $this->db_escape($sale_id) . "
                 )";
    }

    if (!empty($search_term)) {
      $escaped_search = $this->db_escape($search_term);
      $query .= " AND (p.name_pr LIKE '%{$escaped_search}%'
                    OR p.sku LIKE '%{$escaped_search}%'
                    OR p.slug LIKE '%{$escaped_search}%'
                    OR p.description LIKE '%{$escaped_search}%')";
    }

    $query .= " ORDER BY p.name_pr ASC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  public function autoUpdateFlashSaleStatus() {
    $result = [
      'activated' => 0,
      'deactivated' => 0,
      'errors' => []
    ];

    try {
      // 1. Kích hoạt các flash sale đã đến giờ
      $activate_query = "UPDATE {$this->table}
                          SET is_activity = 1,
                              updated_at = NOW()
                          WHERE is_activity = 0
                          AND time_start <= NOW()
                          AND time_end >= NOW()";

      $activate_result = $this->db_query($activate_query);
      if ($activate_result) {
        $result['activated'] = $this->db_affected_rows();
      }

      // 2. Tắt các flash sale đã hết hạn
      $deactivate_query = "UPDATE {$this->table}
                            SET is_activity = 0,
                                updated_at = NOW()
                            WHERE is_activity = 1
                            AND time_end < NOW()";

      $deactivate_result = $this->db_query($deactivate_query);
      if ($deactivate_result) {
        $result['deactivated'] = $this->db_affected_rows();
      }

      // 3. Tắt các flash sale chưa bắt đầu nhưng đang active (sai trạng thái)
      $fix_query = "UPDATE {$this->table}
                     SET is_activity = 0,
                         updated_at = NOW()
                     WHERE is_activity = 1
                     AND time_start > NOW()";

      $this->db_query($fix_query);

      return $result;

    } catch (Exception $e) {
      $result['errors'][] = $e->getMessage();
      return $result;
    }
  }

  public function getProductsInSale($sale_id) {
    $query = "SELECT ds.*,
             p.id as product_id,
             p.name_pr as product_name,
             p.slug as product_slug,
             p.regular_price as original_price,
             p.stock_quantity,
             p.stock_status,
             p.featured,
             p.status as product_status,
             fs.percent as discount_percent,
             ROUND(p.regular_price * (1 - fs.percent/100), 2) as calculated_sale_price
             FROM detail_sale ds
             JOIN products p ON ds.id_product = p.id
             JOIN {$this->table} fs ON ds.id_sale = fs.id
             WHERE ds.id_sale = " . $this->db_escape($sale_id) . "
             AND p.status = 'published'
             ORDER BY ds.id ASC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  public function getMainProductImage($product_id) {
    $product_id = $this->db_escape($product_id);

    $query = "SELECT image_url, alt_text
              FROM product_images
              WHERE product_id = '{$product_id}'
              AND is_main = 1
              LIMIT 1";

    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  public function getDetailSale($product_id) {
    $product_id = $this->db_escape($product_id);

    $query = "SELECT ds.*,
                     fs.name as sale_name,
                     fs.percent as discount_percent,
                     fs.time_start,
                     fs.time_end,
                     (SELECT COUNT(*) FROM order_items WHERE product_id = ds.id_product) as sold
              FROM detail_sale ds
              LEFT JOIN flash_sales fs ON ds.id_sale = fs.id
              WHERE ds.id_product = '$product_id'
              AND fs.is_activity = 1
              AND fs.time_start <= NOW()
              AND fs.time_end >= NOW()
              LIMIT 1";

    $result = $this->db_query($query);
    $detail = $this->db_fetch($result);

    // Đảm bảo trả về mảng rỗng nếu không có dữ liệu
    if (!$detail) {
      return [
        'quantity' => 0,
        'limit_buy' => 0,
        'sold' => 0
      ];
    }

    return $detail;
  }

  public function getProductsInSaleIndex($sale_id) {
    $sale_id = $this->db_escape($sale_id);

    $query = "SELECT ds.*,
             p.id as product_id,
             p.name_pr as product_name,
             p.slug as product_slug,
             p.regular_price as original_price,
             p.stock_quantity,
             p.stock_status,
             p.featured,
             p.status as product_status,
             fs.percent as discount_percent,
             ROUND(p.regular_price * (1 - fs.percent/100), 2) as calculated_sale_price
             FROM detail_sale ds
             INNER JOIN products p ON ds.id_product = p.id
             INNER JOIN " . $this->table . " fs ON ds.id_sale = fs.id
             WHERE ds.id_sale = '{$sale_id}'
             AND p.status = 'published'
             ORDER BY ds.id ASC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  // ==================== UTILITY FUNCTIONS ====================

  public function getAvailableProducts($sale_id = null) {
    $query = "SELECT p.* FROM products p WHERE p.status = 'published'";

    if ($sale_id) {
      // Loại bỏ sản phẩm đã có trong sale
      $query .= " AND p.id NOT IN (
                        SELECT id_product FROM detail_sale
                        WHERE id_sale = " . $this->db_escape($sale_id) . "
                     )";
    }

    $query .= " ORDER BY p.name_pr ASC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }

  public function getSaleStatistics($sale_id) {
    $query = "SELECT
                 COUNT(ds.id) as total_products,
                 SUM(ds.quantity) as total_quantity
                 FROM detail_sale ds
                 WHERE ds.id_sale = " . $this->db_escape($sale_id);

    $result = $this->db_query($query);
    $stats = $this->db_fetch($result);

    // Đảm bảo luôn có giá trị mặc định
    if (!$stats) {
      $stats = [
        'total_products' => 0,
        'total_quantity' => 0
      ];
    }

    return $stats;
  }

  // Kiểm tra xem flash sale có đang diễn ra không
  public function isSaleActive($sale_id) {
    $query = "SELECT is_activity FROM {$this->table}
                 WHERE id = " . $this->db_escape($sale_id) . "
                 AND is_activity = 1
                 AND time_start <= NOW()
                 AND time_end >= NOW()";

    $result = $this->db_query($query);
    $row = $this->db_fetch($result);

    return $row && $row['is_activity'] == 1;
  }

  // Lấy flash sale đang hoạt động
  public function getActiveFlashSale($product_id = null) {
    $query = "SELECT fs.* FROM {$this->table} fs";

    if ($product_id) {
      $query .= " JOIN detail_sale ds ON fs.id = ds.id_sale
                       WHERE fs.is_activity = 1
                       AND fs.time_start <= NOW()
                       AND fs.time_end >= NOW()
                       AND ds.id_product = " . $this->db_escape($product_id) . "
                       LIMIT 1";
    } else {
      $query .= " WHERE fs.is_activity = 1
                       AND fs.time_start <= NOW()
                       AND fs.time_end >= NOW()
                       ORDER BY fs.time_start DESC
                       LIMIT 1";
    }

    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  // Lấy giá flash sale cho sản phẩm
  public function getFlashSalePrice($sale_id, $product_id) {
    $query = "SELECT fs.percent, p.regular_price
                 FROM {$this->table} fs
                 JOIN products p ON p.id = " . $this->db_escape($product_id) . "
                 WHERE fs.id = " . $this->db_escape($sale_id);

    $result = $this->db_query($query);
    $data = $this->db_fetch($result);

    if ($data) {
      $sale_price = $data['regular_price'] * (1 - $data['percent']/100);
      return round($sale_price, 2);
    }

    return null;
  }

  // Kiểm tra xem sản phẩm có đang trong flash sale không
  public function isProductInFlashSale($product_id) {
    $query = "SELECT fs.id, fs.name_fl, fs.percent, fs.time_start, fs.time_end,
                 ds.limit_buy, ds.quantity
                 FROM {$this->table} fs
                 JOIN detail_sale ds ON fs.id = ds.id_sale
                 WHERE ds.id_product = " . $this->db_escape($product_id) . "
                 AND fs.is_activity = 1
                 AND fs.time_start <= NOW()
                 AND fs.time_end >= NOW()
                 LIMIT 1";

    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  // Kiểm tra số lượng còn lại trong flash sale
  public function checkFlashSaleStock($sale_id, $product_id) {
    $query = "SELECT ds.quantity, ds.limit_buy,
                 (SELECT COUNT(*) FROM order_items oi
                  JOIN orders o ON oi.order_id = o.id
                  WHERE oi.product_id = ds.id_product
                  AND o.flash_sale_id = ds.id_sale) as sold_count
                 FROM detail_sale ds
                 WHERE ds.id_sale = " . $this->db_escape($sale_id) . "
                 AND ds.id_product = " . $this->db_escape($product_id);

    $result = $this->db_query($query);
    return $this->db_fetch($result);
  }

  // Lấy tất cả sản phẩm đang trong flash sale
  public function getAllProductsInFlashSale($status = 'active') {
    $query = "SELECT p.*, fs.id as flash_sale_id, fs.percent, fs.time_start, fs.time_end,
                 ds.limit_buy, ds.quantity
                 FROM products p
                 JOIN detail_sale ds ON p.id = ds.id_product
                 JOIN {$this->table} fs ON ds.id_sale = fs.id
                 WHERE p.status = 'published'";

    if ($status == 'active') {
      $query .= " AND fs.is_activity = 1
                       AND fs.time_start <= NOW()
                       AND fs.time_end >= NOW()";
    } elseif ($status == 'upcoming') {
      $query .= " AND fs.time_start > NOW()";
    } elseif ($status == 'ended') {
      $query .= " AND fs.time_end < NOW()";
    }

    $query .= " ORDER BY fs.time_start DESC";

    $result = $this->db_query($query);
    return $this->db_fetch_all($result);
  }
}
?>
