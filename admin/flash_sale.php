<?php
// flash_sale.php
require_once 'class/flash_sale_manager.php';

$flashSaleManager = new FlashSaleManager();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$sale_id = isset($_GET['sale_id']) ? intval($_GET['sale_id']) : 0;
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$message = '';

$flashSaleManager->autoUpdateFlashSaleStatus();

// Xử lý các hành động POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $action = $_POST['action'] ?? $action;

  switch ($action) {
    case 'create':
      $name = $_POST['name_fl'] ?? '';
      $time_start = $_POST['time_start'] ?? '';
      $time_end = $_POST['time_end'] ?? '';
      $percent = $_POST['percent'] ?? 0;
      $is_activity = $_POST['is_activity'] ?? 1;

      $result = $flashSaleManager->createFlashSale($name, $time_start, $time_end, $percent, $is_activity);
      if ($result !== false) {
        $message = '<div class="alert alert-success">Tạo flash sale thành công!</div>';
        $action = 'list';
      } else {
        $message = '<div class="alert alert-danger">Lỗi: ' . $flashSaleManager->get_error() . '</div>';
      }
      break;

    case 'add_products':
      $sale_id = $_POST['sale_id'] ?? 0;

      if (isset($_POST['products']) && is_array($_POST['products'])) {
        $success_count = 0;
        $error_count = 0;

        foreach ($_POST['products'] as $product_data) {
          if (!empty($product_data['product_id']) && !empty($product_data['quantity'])) {
            $product_id = intval($product_data['product_id']);
            $limit_buy = isset($product_data['limit_buy']) ? intval($product_data['limit_buy']) : 1;
            $quantity = intval($product_data['quantity']);

            $result = $flashSaleManager->addProductToSale($sale_id, $product_id, $limit_buy, $quantity);
            if ($result['success']) {
              $success_count++;
            } else {
              $error_count++;
            }
          }
        }

        if ($success_count > 0) {
          $message = '<div class="alert alert-success">Đã thêm ' . $success_count . ' sản phẩm vào flash sale!</div>';
          if ($error_count > 0) {
            $message .= '<div class="alert alert-warning">' . $error_count . ' sản phẩm không thể thêm.</div>';
          }
        } else {
          $message = '<div class="alert alert-danger">Không thể thêm sản phẩm nào.</div>';
        }
        $action = 'products';
      }
      break;

    case 'add_product':
      $sale_id = $_POST['sale_id'] ?? 0;
      $product_id = $_POST['product_id'] ?? 0;
      $limit_buy = $_POST['limit_buy'] ?? 1;
      $quantity = $_POST['quantity'] ?? 1;

      $result = $flashSaleManager->addProductToSale($sale_id, $product_id, $limit_buy, $quantity);
      if ($result['success']) {
        $message = '<div class="alert alert-success">' . $result['message'] . '</div>';
        $action = 'products';
      } else {
        $message = '<div class="alert alert-danger">' . $result['message'] . '</div>';
      }
      break;

    case 'update':
      $id = $_POST['id'] ?? 0;
      $name = $_POST['name_fl'] ?? '';
      $time_start = $_POST['time_start'] ?? '';
      $time_end = $_POST['time_end'] ?? '';
      $percent = $_POST['percent'] ?? 0;
      $is_activity = $_POST['is_activity'] ?? 1;

      if ($flashSaleManager->updateFlashSale($id, $name, $time_start, $time_end, $percent, $is_activity)) {
        $message = '<div class="alert alert-success">Cập nhật thành công!</div>';
        $action = 'list';
      } else {
        $message = '<div class="alert alert-danger">Lỗi: ' . $flashSaleManager->get_error() . '</div>';
      }
      break;
  }
}

// Xử lý các hành động GET
if (isset($_GET['delete'])) {
  if ($flashSaleManager->deleteFlashSale($_GET['delete'])) {
    $message = '<div class="alert alert-success">Xóa flash sale thành công!</div>';
  } else {
    $message = '<div class="alert alert-danger">Lỗi: ' . $flashSaleManager->get_error() . '</div>';
  }
  $action = 'list';
}

if (isset($_GET['remove_product'])) {
  if ($flashSaleManager->removeProductFromSale($_GET['remove_product'])) {
    $message = '<div class="alert alert-success">Xóa sản phẩm khỏi flash sale thành công!</div>';
  } else {
    $message = '<div class="alert alert-danger">Lỗi: ' . $flashSaleManager->get_error() . '</div>';
  }
  $action = 'products';
}

// Hiển thị message nếu có
if ($message) {
  echo $message;
}

// Render các view dựa trên action
switch ($action) {
  case 'create':
    renderCreateForm();
    break;

  case 'edit':
    if ($sale_id > 0) {
      renderEditForm($sale_id);
    } else {
      renderFlashSaleList();
    }
    break;

  case 'products':
    if ($sale_id > 0) {
      renderProductsList($sale_id);
    } else {
      renderFlashSaleList();
    }
    break;

  case 'add_product':
    if ($sale_id > 0) {
      renderAddProductForm($sale_id, $search);
    } else {
      renderFlashSaleList();
    }
    break;

  case 'add_products_bulk':
    if ($sale_id > 0) {
      renderAddProductsBulkForm($sale_id, $search);
    } else {
      renderFlashSaleList();
    }
    break;

  case 'list':
  default:
    renderFlashSaleList();
    break;
}

// ==================== FUNCTION RENDER VIEWS ====================

function renderFlashSaleList() {
  global $flashSaleManager;
  $sales = $flashSaleManager->getAllFlashSales();
  ?>
  <div class="flash-sale-container">
    <div class="fs-header">
      <div class="fs-header-left">
        <h1><i class="fas fa-bolt"></i> Quản lý Flash Sale</h1>
        <p class="fs-subtitle">Quản lý các chương trình giảm giá nhanh</p>
      </div>
      <div class="fs-header-right">
        <a href="admin.php?page=flash_sale&action=create" class="fs-btn fs-btn-primary">
          <i class="fas fa-plus-circle"></i> Tạo Flash Sale
        </a>
      </div>
    </div>

    <div class="fs-card">
      <div class="fs-card-header">
        <h2><i class="fas fa-list"></i> Danh sách Flash Sale</h2>
        <div class="fs-total-badge"><?php echo count($sales); ?> chương trình</div>
      </div>

      <div class="fs-card-body">
        <?php if (empty($sales)): ?>
          <div class="fs-empty-state">
            <i class="fas fa-inbox fa-3x"></i>
            <h3>Chưa có flash sale nào</h3>
            <p>Tạo flash sale đầu tiên để bắt đầu bán hàng với giá sốc!</p>
            <a href="admin.php?page=flash_sale&action=create" class="fs-btn fs-btn-primary">
              <i class="fas fa-plus"></i> Tạo Flash Sale đầu tiên
            </a>
          </div>
        <?php else: ?>
          <div class="fs-table-responsive">
            <table class="fs-table">
              <thead>
              <tr>
                <th class="fs-col-id">ID</th>
                <th class="fs-col-name">Tên chương trình</th>
                <th class="fs-col-time">Thời gian</th>
                <th class="fs-col-discount">Giảm giá</th>
                <th class="fs-col-status">Trạng thái</th>
                <th class="fs-col-products">Sản phẩm</th>
                <th class="fs-col-actions">Hành động</th>
              </tr>
              </thead>
              <tbody>
              <?php foreach ($sales as $sale):
                $products = $flashSaleManager->getProductsInSale($sale['id']);
                $stats = $flashSaleManager->getSaleStatistics($sale['id']);
                ?>
                <tr>
                  <td class="fs-col-id">#<?php echo $sale['id']; ?></td>
                  <td class="fs-col-name">
                    <div class="fs-product-name">
                      <strong><?php echo htmlspecialchars($sale['name_fl']); ?></strong>
                      <div class="fs-product-meta">
                        <span class="fs-badge fs-badge-light">ID: <?php echo $sale['id']; ?></span>
                      </div>
                    </div>
                  </td>
                  <td class="fs-col-time">
                    <div class="fs-time-range">
                                        <span class="fs-time-start">
                                            <i class="fas fa-play-circle fs-text-success"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($sale['time_start'])); ?>
                                        </span>
                      <span class="fs-time-end">
                                            <i class="fas fa-stop-circle fs-text-danger"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($sale['time_end'])); ?>
                                        </span>
                    </div>
                  </td>
                  <td class="fs-col-discount">
                    <span class="fs-discount-badge"><?php echo $sale['percent']; ?>%</span>
                  </td>
                  <td class="fs-col-status">
                    <?php
                    $now = time();
                    $start = strtotime($sale['time_start']);
                    $end = strtotime($sale['time_end']);

                    $status_badge = '';
                    if ($now < $start) {
                      $status_badge = '<span class="fs-badge fs-badge-warning">Sắp diễn ra</span>';
                    } elseif ($now >= $start && $now <= $end) {
                      $status_badge = '<span class="fs-badge fs-badge-success">Đang diễn ra</span>';
                    } else {
                      $status_badge = '<span class="fs-badge fs-badge-secondary">Đã kết thúc</span>';
                    }

                    echo $status_badge;
                    ?>
                    <div class="fs-status-meta">
                      <?php echo $sale['is_activity'] ? 'Kích hoạt' : 'Tạm ẩn'; ?>
                    </div>
                  </td>
                  <td class="fs-col-products">
                    <div class="fs-products-count">
                      <span class="fs-badge fs-badge-info"><?php echo count($products); ?> SP</span>
                    </div>
                    <div class="fs-products-stats">
                      <small>Tổng: <?php echo $stats['total_quantity'] ?? 0; ?></small>
                    </div>
                  </td>
                  <td class="fs-col-actions">
                    <div class="fs-action-buttons">
                      <a href="admin.php?page=flash_sale&action=products&sale_id=<?php echo $sale['id']; ?>"
                         class="fs-btn fs-btn-sm fs-btn-info" title="Xem sản phẩm">
                        <i class="fas fa-eye"></i>
                      </a>
                      <a href="admin.php?page=flash_sale&action=add_products_bulk&sale_id=<?php echo $sale['id']; ?>"
                         class="fs-btn fs-btn-sm fs-btn-success" title="Thêm nhiều sản phẩm">
                        <i class="fas fa-layer-group"></i>
                      </a>
                      <a href="admin.php?page=flash_sale&action=edit&sale_id=<?php echo $sale['id']; ?>"
                         class="fs-btn fs-btn-sm fs-btn-warning" title="Sửa">
                        <i class="fas fa-edit"></i>
                      </a>
                      <a href="admin.php?page=flash_sale&action=list&delete=<?php echo $sale['id']; ?>"
                         class="fs-btn fs-btn-sm fs-btn-danger"
                         onclick="return confirm('Xóa flash sale này?\nTất cả sản phẩm trong flash sale cũng sẽ bị xóa.')"
                         title="Xóa">
                        <i class="fas fa-trash"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php
}

function renderCreateForm() {
  ?>
  <div class="flash-sale-container">
    <div class="fs-header">
      <div class="fs-header-left">
        <h1><i class="fas fa-plus-circle"></i> Tạo Flash Sale Mới</h1>
        <p class="fs-subtitle">Tạo chương trình giảm giá mới</p>
      </div>
      <div class="fs-header-right">
        <a href="admin.php?page=flash_sale&action=list" class="fs-btn fs-btn-secondary">
          <i class="fas fa-arrow-left"></i> Quay lại
        </a>
      </div>
    </div>

    <div class="fs-card">
      <div class="fs-card-header">
        <h2><i class="fas fa-cogs"></i> Thông tin chương trình</h2>
      </div>
      <div class="fs-card-body">
        <form method="POST" action="admin.php?page=flash_sale" class="fs-form">
          <input type="hidden" name="action" value="create">

          <div class="fs-form-grid">
            <div class="fs-form-group">
              <label class="fs-form-label">
                <i class="fas fa-tag"></i> Tên chương trình <span class="fs-required">*</span>
              </label>
              <input type="text" name="name_fl" class="fs-form-control" required
                     placeholder="VD: Flash Sale Black Friday">
              <div class="fs-form-help">Tên hiển thị cho khách hàng</div>
            </div>

            <div class="fs-form-group">
              <label class="fs-form-label">
                <i class="fas fa-percentage"></i> Phần trăm giảm giá <span class="fs-required">*</span>
              </label>
              <div class="fs-input-group">
                <input type="number" name="percent" class="fs-form-control"
                       step="0.01" min="0" max="100" required
                       placeholder="VD: 30.5">
                <span class="fs-input-group-text">%</span>
              </div>
              <div class="fs-form-help">Từ 0% đến 100%</div>
            </div>

            <div class="fs-form-group">
              <label class="fs-form-label">
                <i class="fas fa-play-circle"></i> Thời gian bắt đầu <span class="fs-required">*</span>
              </label>
              <input type="datetime-local" name="time_start" class="fs-form-control" required>
            </div>

            <div class="fs-form-group">
              <label class="fs-form-label">
                <i class="fas fa-stop-circle"></i> Thời gian kết thúc <span class="fs-required">*</span>
              </label>
              <input type="datetime-local" name="time_end" class="fs-form-control" required>
            </div>

            <div class="fs-form-group">
              <label class="fs-form-label">
                <i class="fas fa-toggle-on"></i> Trạng thái
              </label>
              <select name="is_activity" class="fs-form-control">
                <option value="1">Kích hoạt</option>
                <option value="0">Tạm ẩn</option>
              </select>
            </div>
          </div>

          <div class="fs-form-actions">
            <button type="submit" class="fs-btn fs-btn-primary fs-btn-lg">
              <i class="fas fa-save"></i> Tạo Flash Sale
            </button>
            <a href="admin.php?page=flash_sale&action=list" class="fs-btn fs-btn-secondary">
              <i class="fas fa-times"></i> Hủy bỏ
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php
}

function renderEditForm($sale_id) {
  global $flashSaleManager;
  $sale = $flashSaleManager->getFlashSale($sale_id);

  if (!$sale) {
    echo '<div class="alert alert-danger">Flash sale không tồn tại!</div>';
    renderFlashSaleList();
    return;
  }
  ?>
  <div class="flash-sale-container">
    <div class="fs-header">
      <div class="fs-header-left">
        <h1><i class="fas fa-edit"></i> Chỉnh sửa Flash Sale</h1>
        <p class="fs-subtitle">Chỉnh sửa chương trình: <?php echo htmlspecialchars($sale['name_fl']); ?></p>
      </div>
      <div class="fs-header-right">
        <a href="admin.php?page=flash_sale&action=list" class="fs-btn fs-btn-secondary">
          <i class="fas fa-arrow-left"></i> Quay lại
        </a>
      </div>
    </div>

    <div class="fs-card">
      <div class="fs-card-header">
        <h2><i class="fas fa-cogs"></i> Thông tin chương trình</h2>
      </div>
      <div class="fs-card-body">
        <form method="POST" action="admin.php?page=flash_sale" class="fs-form">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?php echo $sale['id']; ?>">

          <div class="fs-form-grid">
            <div class="fs-form-group">
              <label class="fs-form-label">
                <i class="fas fa-tag"></i> Tên chương trình <span class="fs-required">*</span>
              </label>
              <input type="text" name="name_fl" class="fs-form-control" required
                     value="<?php echo htmlspecialchars($sale['name_fl']); ?>">
            </div>

            <div class="fs-form-group">
              <label class="fs-form-label">
                <i class="fas fa-percentage"></i> Phần trăm giảm giá <span class="fs-required">*</span>
              </label>
              <div class="fs-input-group">
                <input type="number" name="percent" class="fs-form-control"
                       step="0.01" min="0" max="100" required
                       value="<?php echo $sale['percent']; ?>">
                <span class="fs-input-group-text">%</span>
              </div>
            </div>

            <div class="fs-form-group">
              <label class="fs-form-label">
                <i class="fas fa-play-circle"></i> Thời gian bắt đầu <span class="fs-required">*</span>
              </label>
              <input type="datetime-local" name="time_start" class="fs-form-control" required
                     value="<?php echo date('Y-m-d\TH:i', strtotime($sale['time_start'])); ?>">
            </div>

            <div class="fs-form-group">
              <label class="fs-form-label">
                <i class="fas fa-stop-circle"></i> Thời gian kết thúc <span class="fs-required">*</span>
              </label>
              <input type="datetime-local" name="time_end" class="fs-form-control" required
                     value="<?php echo date('Y-m-d\TH:i', strtotime($sale['time_end'])); ?>">
            </div>

            <div class="fs-form-group">
              <label class="fs-form-label">
                <i class="fas fa-toggle-on"></i> Trạng thái
              </label>
              <select name="is_activity" class="fs-form-control">
                <option value="1" <?php echo $sale['is_activity'] == 1 ? 'selected' : ''; ?>>Kích hoạt</option>
                <option value="0" <?php echo $sale['is_activity'] == 0 ? 'selected' : ''; ?>>Tạm ẩn</option>
              </select>
            </div>
          </div>

          <div class="fs-form-actions">
            <button type="submit" class="fs-btn fs-btn-primary fs-btn-lg">
              <i class="fas fa-save"></i> Cập nhật
            </button>
            <a href="admin.php?page=flash_sale&action=list" class="fs-btn fs-btn-secondary">
              <i class="fas fa-times"></i> Hủy bỏ
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php
}

function renderProductsList($sale_id) {
  global $flashSaleManager;
  $sale = $flashSaleManager->getFlashSale($sale_id);

  if (!$sale) {
    echo '<div class="alert alert-danger">Flash sale không tồn tại!</div>';
    renderFlashSaleList();
    return;
  }

  $products = $flashSaleManager->getProductsInSale($sale_id);
  $stats = $flashSaleManager->getSaleStatistics($sale_id);
  ?>
  <div class="flash-sale-container">
    <div class="fs-header">
      <div class="fs-header-left">
        <h1><i class="fas fa-boxes"></i> Sản phẩm trong Flash Sale</h1>
        <p class="fs-subtitle"><?php echo htmlspecialchars($sale['name_fl']); ?></p>
      </div>
      <div class="fs-header-right">
        <div class="fs-header-actions">
          <a href="admin.php?page=flash_sale&action=add_products_bulk&sale_id=<?php echo $sale_id; ?>"
             class="fs-btn fs-btn-success">
            <i class="fas fa-layer-group"></i> Thêm nhiều sản phẩm
          </a>
          <a href="admin.php?page=flash_sale&action=list" class="fs-btn fs-btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
          </a>
        </div>
      </div>
    </div>

    <div class="fs-stats-grid">
      <div class="fs-stat-card">
        <div class="fs-stat-icon fs-bg-primary">
          <i class="fas fa-cube"></i>
        </div>
        <div class="fs-stat-content">
          <div class="fs-stat-value"><?php echo $stats['total_products'] ?? 0; ?></div>
          <div class="fs-stat-label">Tổng sản phẩm</div>
        </div>
      </div>
      <div class="fs-stat-card">
        <div class="fs-stat-icon fs-bg-success">
          <i class="fas fa-box"></i>
        </div>
        <div class="fs-stat-content">
          <div class="fs-stat-value"><?php echo $stats['total_quantity'] ?? 0; ?></div>
          <div class="fs-stat-label">Tổng số lượng</div>
        </div>
      </div>
      <div class="fs-stat-card">
        <div class="fs-stat-icon fs-bg-info">
          <i class="fas fa-percentage"></i>
        </div>
        <div class="fs-stat-content">
          <div class="fs-stat-value"><?php echo $sale['percent']; ?>%</div>
          <div class="fs-stat-label">Giảm giá</div>
        </div>
      </div>
      <div class="fs-stat-card">
        <div class="fs-stat-icon fs-bg-warning">
          <i class="fas fa-clock"></i>
        </div>
        <div class="fs-stat-content">
          <div class="fs-stat-value">
            <?php
            $now = time();
            $start = strtotime($sale['time_start']);
            $end = strtotime($sale['time_end']);

            if ($now < $start) {
              echo 'Sắp diễn ra';
            } elseif ($now >= $start && $now <= $end) {
              echo 'Đang diễn ra';
            } else {
              echo 'Đã kết thúc';
            }
            ?>
          </div>
          <div class="fs-stat-label">Trạng thái</div>
        </div>
      </div>
    </div>

    <div class="fs-card">
      <div class="fs-card-header">
        <h2><i class="fas fa-list"></i> Danh sách sản phẩm</h2>
        <div class="fs-total-badge"><?php echo count($products); ?> sản phẩm</div>
      </div>

      <div class="fs-card-body">
        <?php if (empty($products)): ?>
          <div class="fs-empty-state">
            <i class="fas fa-box-open fa-3x"></i>
            <h3>Chưa có sản phẩm nào</h3>
            <p>Thêm sản phẩm vào flash sale để bắt đầu bán hàng</p>
            <a href="admin.php?page=flash_sale&action=add_products_bulk&sale_id=<?php echo $sale_id; ?>"
               class="fs-btn fs-btn-primary">
              <i class="fas fa-plus"></i> Thêm sản phẩm ngay
            </a>
          </div>
        <?php else: ?>
          <div class="fs-table-responsive">
            <table class="fs-table">
              <thead>
              <tr>
                <th class="fs-col-no">STT</th>
                <th class="fs-col-product">Sản phẩm</th>
                <th class="fs-col-price">Giá</th>
                <th class="fs-col-quantity">Số lượng</th>
                <th class="fs-col-limit">Giới hạn</th>
                <th class="fs-col-actions">Hành động</th>
              </tr>
              </thead>
              <tbody>
              <?php foreach ($products as $index => $product):
                $original_price = $product['original_price'] ?? 0;
                $discount_percent = $product['discount_percent'] ?? $sale['percent'];
                $sale_price = $original_price * (1 - $discount_percent/100);
                ?>
                <tr>
                  <td class="fs-col-no"><?php echo $index + 1; ?></td>
                  <td class="fs-col-product">
                    <div class="fs-product-info">
                      <div class="fs-product-name">
                        <strong><?php echo htmlspecialchars($product['product_name'] ?? ''); ?></strong>
                      </div>
                      <div class="fs-product-meta">
                        <span class="fs-badge fs-badge-light">SKU: <?php echo $product['sku'] ?? 'N/A'; ?></span>
                        <?php if ($product['stock_status'] == 'out_of_stock'): ?>
                          <span class="fs-badge fs-badge-danger">Hết hàng</span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td class="fs-col-price">
                    <div class="fs-price-display">
                      <div class="fs-price-old"><?php echo number_format($original_price); ?>đ</div>
                      <div class="fs-price-new"><?php echo number_format(round($sale_price, 2)); ?>đ</div>
                      <div class="fs-price-discount">-<?php echo $discount_percent; ?>%</div>
                    </div>
                  </td>
                  <td class="fs-col-quantity">
                    <div class="fs-quantity-display">
                      <span class="fs-quantity-badge"><?php echo $product['quantity']; ?></span>
                      <div class="fs-quantity-meta">
                        <small>Kho: <?php echo $product['stock_quantity'] ?? 0; ?></small>
                      </div>
                    </div>
                  </td>
                  <td class="fs-col-limit">
                    <span class="fs-badge fs-badge-warning"><?php echo $product['limit_buy']; ?>/khách</span>
                  </td>
                  <td class="fs-col-actions">
                    <div class="fs-action-buttons">
                      <a href="admin.php?page=flash_sale&action=products&sale_id=<?php echo $sale_id; ?>&remove_product=<?php echo $product['id']; ?>"
                         class="fs-btn fs-btn-sm fs-btn-danger"
                         onclick="return confirm('Xóa sản phẩm này khỏi flash sale?')"
                         title="Xóa">
                        <i class="fas fa-trash"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php
}

function renderAddProductsBulkForm($sale_id, $search = '') {
  global $flashSaleManager;
  $sale = $flashSaleManager->getFlashSale($sale_id);

  if (!$sale) {
    echo '<div class="alert alert-danger">Flash sale không tồn tại!</div>';
    renderFlashSaleList();
    return;
  }

  // Lấy danh sách sản phẩm với tìm kiếm
  $availableProducts = $flashSaleManager->searchAvailableProducts($sale_id, $search);
  ?>
  <div class="flash-sale-container">
    <div class="fs-header">
      <div class="fs-header-left">
        <h1><i class="fas fa-layer-group"></i> Thêm nhiều sản phẩm</h1>
        <p class="fs-subtitle"><?php echo htmlspecialchars($sale['name_fl']); ?></p>
      </div>
      <div class="fs-header-right">
        <a href="admin.php?page=flash_sale&action=products&sale_id=<?php echo $sale_id; ?>"
           class="fs-btn fs-btn-secondary">
          <i class="fas fa-arrow-left"></i> Quay lại
        </a>
      </div>
    </div>

    <div class="fs-card">
      <div class="fs-card-header">
        <h2><i class="fas fa-search"></i> Tìm và chọn sản phẩm</h2>
      </div>
      <div class="fs-card-body">
        <form method="GET" action="admin.php" class="fs-search-form">
          <input type="hidden" name="page" value="flash_sale">
          <input type="hidden" name="action" value="add_products_bulk">
          <input type="hidden" name="sale_id" value="<?php echo $sale_id; ?>">

          <div class="fs-search-box">
            <div class="fs-input-group">
                            <span class="fs-input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
              <input type="text" name="search" class="fs-form-control"
                     placeholder="Tìm theo tên, SKU, mã sản phẩm..."
                     value="<?php echo htmlspecialchars($search); ?>">
              <button type="submit" class="fs-btn fs-btn-primary">
                Tìm kiếm
              </button>
            </div>
            <div class="fs-search-help">
              <small><i class="fas fa-info-circle"></i> Tìm kiếm theo tên, SKU hoặc mô tả sản phẩm</small>
            </div>
          </div>
        </form>
      </div>
    </div>

    <form method="POST" action="admin.php?page=flash_sale" id="bulkAddForm">
      <input type="hidden" name="action" value="add_products">
      <input type="hidden" name="sale_id" value="<?php echo $sale_id; ?>">

      <div class="fs-card">
        <div class="fs-card-header">
          <h2><i class="fas fa-list-check"></i> Chọn sản phẩm để thêm</h2>
          <div class="fs-total-badge"><?php echo count($availableProducts); ?> sản phẩm</div>
        </div>

        <div class="fs-card-body">
          <?php if (empty($availableProducts)): ?>
            <div class="fs-empty-state">
              <i class="fas fa-search fa-3x"></i>
              <h3>Không tìm thấy sản phẩm</h3>
              <p><?php echo $search ? 'Không có sản phẩm nào phù hợp với tìm kiếm' : 'Không còn sản phẩm nào để thêm'; ?></p>
              <?php if ($search): ?>
                <a href="admin.php?page=flash_sale&action=add_products_bulk&sale_id=<?php echo $sale_id; ?>"
                   class="fs-btn fs-btn-secondary">
                  <i class="fas fa-times"></i> Xóa tìm kiếm
                </a>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="fs-bulk-select-actions">
              <div class="fs-bulk-checkbox">
                <input type="checkbox" id="selectAll" class="fs-checkbox">
                <label for="selectAll" class="fs-checkbox-label">Chọn tất cả</label>
              </div>
              <div class="fs-bulk-actions">
                <button type="button" class="fs-btn fs-btn-sm fs-btn-secondary" id="setDefaultValues">
                  <i class="fas fa-magic"></i> Đặt giá trị mặc định
                </button>
              </div>
            </div>

            <div class="fs-table-responsive">
              <table class="fs-table fs-table-bulk">
                <thead>
                <tr>
                  <th class="fs-col-select">Chọn</th>
                  <th class="fs-col-product">Sản phẩm</th>
                  <th class="fs-col-price">Giá</th>
                  <th class="fs-col-stock">Kho</th>
                  <th class="fs-col-quantity">Số lượng FS</th>
                  <th class="fs-col-limit">Giới hạn/khách</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($availableProducts as $product):
                  $discount_price = $product['regular_price'] * (1 - $sale['percent']/100);
                  ?>
                  <tr class="fs-product-row">
                    <td class="fs-col-select">
                      <input type="checkbox" name="products[<?php echo $product['id']; ?>][selected]"
                             class="fs-checkbox product-select"
                             data-product-id="<?php echo $product['id']; ?>">
                    </td>
                    <td class="fs-col-product">
                      <div class="fs-product-info">
                        <div class="fs-product-name">
                          <strong><?php echo htmlspecialchars($product['name_pr'] ?? ''); ?></strong>
                        </div>
                        <div class="fs-product-meta">
                          <span class="fs-badge fs-badge-light">SKU: <?php echo $product['sku'] ?? 'N/A'; ?></span>
                          <span class="fs-badge fs-badge-light">ID: <?php echo $product['id']; ?></span>
                        </div>
                      </div>
                    </td>
                    <td class="fs-col-price">
                      <div class="fs-price-display">
                        <div class="fs-price-old"><?php echo number_format($product['regular_price'] ?? 0); ?>đ</div>
                        <div class="fs-price-new"><?php echo number_format(round($discount_price, 2)); ?>đ</div>
                        <div class="fs-price-discount">-<?php echo $sale['percent']; ?>%</div>
                      </div>
                    </td>
                    <td class="fs-col-stock">
                      <div class="fs-stock-display">
                                            <span class="fs-stock-badge <?php echo $product['stock_status'] == 'in_stock' ? 'fs-badge-success' : 'fs-badge-danger'; ?>">
                                                <?php echo $product['stock_quantity'] ?? 0; ?>
                                            </span>
                        <div class="fs-stock-meta">
                          <small><?php echo $product['stock_status'] == 'in_stock' ? 'Còn hàng' : 'Hết hàng'; ?></small>
                        </div>
                      </div>
                    </td>
                    <td class="fs-col-quantity">
                      <input type="number"
                             name="products[<?php echo $product['id']; ?>][quantity]"
                             class="fs-form-control fs-form-control-sm fs-quantity-input"
                             min="1"
                             max="<?php echo $product['stock_quantity'] ?? 100; ?>"
                             value="<?php echo min(10, $product['stock_quantity'] ?? 10); ?>"
                             disabled>
                    </td>
                    <td class="fs-col-limit">
                      <input type="number"
                             name="products[<?php echo $product['id']; ?>][limit_buy]"
                             class="fs-form-control fs-form-control-sm fs-limit-input"
                             min="1"
                             value="1"
                             disabled>
                    </td>
                    <input type="hidden"
                           name="products[<?php echo $product['id']; ?>][product_id]"
                           value="<?php echo $product['id']; ?>">
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="fs-bulk-submit">
              <button type="submit" class="fs-btn fs-btn-primary fs-btn-lg">
                <i class="fas fa-plus-circle"></i> Thêm sản phẩm đã chọn
              </button>
              <span class="fs-selected-count">Đã chọn: <span id="selectedCount">0</span> sản phẩm</span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Select all checkbox
      const selectAll = document.getElementById('selectAll');
      const productSelects = document.querySelectorAll('.product-select');

      selectAll.addEventListener('change', function() {
        const isChecked = this.checked;
        productSelects.forEach(checkbox => {
          checkbox.checked = isChecked;
          toggleProductInputs(checkbox);
        });
        updateSelectedCount();
      });

      // Individual product select
      productSelects.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
          toggleProductInputs(this);
          updateSelectedCount();
          updateSelectAllState();
        });
      });

      // Set default values button
      document.getElementById('setDefaultValues').addEventListener('click', function() {
        productSelects.forEach(checkbox => {
          if (checkbox.checked) {
            const row = checkbox.closest('.fs-product-row');
            const quantityInput = row.querySelector('.fs-quantity-input');
            const limitInput = row.querySelector('.fs-limit-input');

            // Set default quantity (min between 10 and stock)
            const maxQuantity = parseInt(quantityInput.getAttribute('max')) || 100;
            quantityInput.value = Math.min(10, maxQuantity);

            // Set default limit
            limitInput.value = 1;
          }
        });
      });

      // Form submission validation
      document.getElementById('bulkAddForm').addEventListener('submit', function(e) {
        const selectedProducts = Array.from(productSelects).filter(cb => cb.checked);
        if (selectedProducts.length === 0) {
          e.preventDefault();
          alert('Vui lòng chọn ít nhất 1 sản phẩm để thêm!');
          return false;
        }

        // Remove unselected products from form data
        productSelects.forEach(checkbox => {
          if (!checkbox.checked) {
            const productId = checkbox.getAttribute('data-product-id');
            const inputs = document.querySelectorAll(`[name*="products[${productId}]"]`);
            inputs.forEach(input => input.disabled = true);
          }
        });
      });

      function toggleProductInputs(checkbox) {
        const row = checkbox.closest('.fs-product-row');
        const inputs = row.querySelectorAll('.fs-quantity-input, .fs-limit-input');
        inputs.forEach(input => {
          input.disabled = !checkbox.checked;
          if (!checkbox.checked) {
            input.value = '';
          }
        });
      }

      function updateSelectedCount() {
        const selected = Array.from(productSelects).filter(cb => cb.checked).length;
        document.getElementById('selectedCount').textContent = selected;
      }

      function updateSelectAllState() {
        const allChecked = Array.from(productSelects).every(cb => cb.checked);
        const someChecked = Array.from(productSelects).some(cb => cb.checked);

        selectAll.checked = allChecked;
        selectAll.indeterminate = someChecked && !allChecked;
      }

      // Initialize
      updateSelectedCount();
    });
  </script>
  <?php
}

// Thêm hàm search vào FlashSaleManager trước khi sử dụng
?>

<style>
  /* ====== MAIN CONTAINER ====== */
  .flash-sale-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
  }

  /* ====== HEADER ====== */
  .fs-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    color: white;
  }

  .fs-header-left h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .fs-subtitle {
    margin: 5px 0 0 0;
    opacity: 0.9;
    font-size: 14px;
  }

  .fs-header-actions {
    display: flex;
    gap: 10px;
  }

  /* ====== BUTTONS ====== */
  .fs-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
  }

  .fs-btn-sm {
    padding: 6px 12px;
    font-size: 12px;
  }

  .fs-btn-lg {
    padding: 12px 24px;
    font-size: 16px;
  }

  .fs-btn-primary {
    background: #667eea;
    color: white;
  }

  .fs-btn-primary:hover {
    background: #5a67d8;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  }

  .fs-btn-secondary {
    background: #6c757d;
    color: white;
  }

  .fs-btn-secondary:hover {
    background: #5a6268;
  }

  .fs-btn-success {
    background: #28a745;
    color: white;
  }

  .fs-btn-success:hover {
    background: #218838;
  }

  .fs-btn-info {
    background: #17a2b8;
    color: white;
  }

  .fs-btn-info:hover {
    background: #138496;
  }

  .fs-btn-warning {
    background: #ffc107;
    color: #212529;
  }

  .fs-btn-warning:hover {
    background: #e0a800;
  }

  .fs-btn-danger {
    background: #dc3545;
    color: white;
  }

  .fs-btn-danger:hover {
    background: #c82333;
  }

  /* ====== CARDS ====== */
  .fs-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    overflow: hidden;
  }

  .fs-card-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .fs-card-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .fs-card-body {
    padding: 20px;
  }

  /* ====== BADGES ====== */
  .fs-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
  }

  .fs-badge-primary { background: #667eea; color: white; }
  .fs-badge-success { background: #28a745; color: white; }
  .fs-badge-info { background: #17a2b8; color: white; }
  .fs-badge-warning { background: #ffc107; color: #212529; }
  .fs-badge-danger { background: #dc3545; color: white; }
  .fs-badge-secondary { background: #6c757d; color: white; }
  .fs-badge-light { background: #f8f9fa; color: #495057; border: 1px solid #dee2e6; }

  .fs-total-badge {
    background: #667eea;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
  }

  /* ====== STATS CARDS ====== */
  .fs-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .fs-stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
  }

  .fs-stat-card:hover {
    transform: translateY(-5px);
  }

  .fs-stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
  }

  .fs-bg-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
  .fs-bg-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
  .fs-bg-info { background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%); }
  .fs-bg-warning { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); }

  .fs-stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #333;
  }

  .fs-stat-label {
    font-size: 14px;
    color: #6c757d;
  }

  /* ====== TABLES ====== */
  .fs-table-responsive {
    overflow-x: auto;
  }

  .fs-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }

  .fs-table thead {
    background: #f8f9fa;
  }

  .fs-table th {
    padding: 16px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
  }

  .fs-table td {
    padding: 16px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
  }

  .fs-table tbody tr {
    transition: background-color 0.2s ease;
  }

  .fs-table tbody tr:hover {
    background-color: #f8f9fa;
  }

  /* ====== TABLE COLUMNS ====== */
  .fs-col-id { width: 80px; }
  .fs-col-no { width: 60px; }
  .fs-col-select { width: 60px; text-align: center; }
  .fs-col-name { min-width: 200px; }
  .fs-col-product { min-width: 250px; }
  .fs-col-time { width: 180px; }
  .fs-col-discount { width: 100px; }
  .fs-col-status { width: 150px; }
  .fs-col-products { width: 120px; }
  .fs-col-price { width: 150px; }
  .fs-col-quantity { width: 120px; }
  .fs-col-limit { width: 120px; }
  .fs-col-stock { width: 100px; }
  .fs-col-actions { width: 100px; }

  /* ====== PRODUCT INFO ====== */
  .fs-product-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }

  .fs-product-name {
    font-weight: 500;
    color: #333;
  }

  .fs-product-meta {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
  }

  /* ====== TIME DISPLAY ====== */
  .fs-time-range {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }

  .fs-time-start, .fs-time-end {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
  }

  .fs-text-success { color: #28a745; }
  .fs-text-danger { color: #dc3545; }

  /* ====== DISCOUNT BADGE ====== */
  .fs-discount-badge {
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 14px;
    display: inline-block;
  }

  /* ====== STATUS ====== */
  .fs-status-meta {
    margin-top: 5px;
    font-size: 12px;
    color: #6c757d;
  }

  /* ====== ACTION BUTTONS ====== */
  .fs-action-buttons {
    display: flex;
    gap: 5px;
  }

  /* ====== PRICE DISPLAY ====== */
  .fs-price-display {
    display: flex;
    flex-direction: column;
    gap: 3px;
  }

  .fs-price-old {
    text-decoration: line-through;
    color: #6c757d;
    font-size: 13px;
  }

  .fs-price-new {
    color: #dc3545;
    font-weight: 700;
    font-size: 16px;
  }

  .fs-price-discount {
    color: #28a745;
    font-size: 12px;
    font-weight: 600;
  }

  /* ====== QUANTITY DISPLAY ====== */
  .fs-quantity-display {
    display: flex;
    flex-direction: column;
    gap: 3px;
  }

  .fs-quantity-badge {
    background: #667eea;
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 600;
    display: inline-block;
    width: fit-content;
  }

  .fs-quantity-meta {
    font-size: 12px;
    color: #6c757d;
  }

  /* ====== STOCK DISPLAY ====== */
  .fs-stock-display {
    display: flex;
    flex-direction: column;
    gap: 3px;
  }

  .fs-stock-badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 600;
    display: inline-block;
    width: fit-content;
  }

  .fs-stock-meta {
    font-size: 12px;
  }

  /* ====== EMPTY STATE ====== */
  .fs-empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
  }

  .fs-empty-state i {
    margin-bottom: 20px;
    color: #dee2e6;
  }

  .fs-empty-state h3 {
    margin: 0 0 10px 0;
    color: #495057;
  }

  .fs-empty-state p {
    margin-bottom: 20px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
  }

  /* ====== FORM STYLES ====== */
  .fs-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
  }

  .fs-form-group {
    margin-bottom: 20px;
  }

  .fs-form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .fs-required {
    color: #dc3545;
  }

  .fs-form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ced4da;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s ease;
  }

  .fs-form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .fs-form-control-sm {
    padding: 6px 8px;
    font-size: 13px;
    max-width: 100px;
  }

  .fs-input-group {
    display: flex;
  }

  .fs-input-group-text {
    padding: 10px 12px;
    background: #f8f9fa;
    border: 1px solid #ced4da;
    border-left: none;
    border-radius: 0 8px 8px 0;
    color: #6c757d;
  }

  .fs-input-group .fs-form-control {
    border-radius: 8px 0 0 8px;
    border-right: none;
  }

  .fs-form-help {
    margin-top: 5px;
    font-size: 12px;
    color: #6c757d;
  }

  .fs-form-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
  }

  /* ====== SEARCH FORM ====== */
  .fs-search-form {
    margin-bottom: 20px;
  }

  .fs-search-box {
    max-width: 600px;
  }

  .fs-search-help {
    margin-top: 8px;
    padding-left: 40px;
  }

  /* ====== BULK SELECT ====== */
  .fs-bulk-select-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
  }

  .fs-bulk-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .fs-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }

  .fs-checkbox-label {
    cursor: pointer;
    font-weight: 500;
  }

  .fs-bulk-actions {
    display: flex;
    gap: 10px;
  }

  /* ====== BULK TABLE ====== */
  .fs-table-bulk tbody tr {
    transition: all 0.3s ease;
  }

  .fs-table-bulk tbody tr:hover {
    background: #f0f7ff;
  }

  .fs-table-bulk .fs-quantity-input,
  .fs-table-bulk .fs-limit-input {
    transition: all 0.3s ease;
  }

  .fs-table-bulk .fs-quantity-input:disabled,
  .fs-table-bulk .fs-limit-input:disabled {
    background: #f8f9fa;
    border-color: #dee2e6;
    cursor: not-allowed;
  }

  /* ====== BULK SUBMIT ====== */
  .fs-bulk-submit {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
  }

  .fs-selected-count {
    font-weight: 500;
    color: #667eea;
  }

  #selectedCount {
    font-weight: 700;
    font-size: 18px;
  }

  /* ====== RESPONSIVE ====== */
  @media (max-width: 768px) {
    .flash-sale-container {
      padding: 10px;
    }

    .fs-header {
      flex-direction: column;
      gap: 15px;
      text-align: center;
    }

    .fs-stats-grid {
      grid-template-columns: 1fr;
    }

    .fs-form-grid {
      grid-template-columns: 1fr;
    }

    .fs-table-responsive {
      font-size: 14px;
    }

    .fs-table th,
    .fs-table td {
      padding: 10px;
    }
  }
</style>
