<?php
// admin/products.php
require_once 'class/product.php';
require_once 'class/category.php';
require_once 'class/brand.php';

$productModel = new Product();
$categoryModel = new Category();
$brandModel = new Brand();

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Xử lý actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = [
    'sku' => trim($_POST['sku']),
    'name_pr' => trim($_POST['name_pr']),
    'slug' => trim($_POST['slug']),
    'description' => trim($_POST['description']),
    'short_description' => trim($_POST['short_description']),
    'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
    'brand_id' => !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null,
    'regular_price' => (float)$_POST['regular_price'],
    'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
    'cost_price' => !empty($_POST['cost_price']) ? (float)$_POST['cost_price'] : null,
    'stock_quantity' => (int)$_POST['stock_quantity'],
    'stock_status' => trim($_POST['stock_status']),
    'weight' => !empty($_POST['weight']) ? (float)$_POST['weight'] : null,
    'dimensions' => trim($_POST['dimensions']),
    'featured' => isset($_POST['featured']) ? 1 : 0,
    'status' => trim($_POST['status'])
  ];

  // Validate stock_status - chỉ cho phép các giá trị ENUM
  $allowedStockStatus = ['in_stock', 'out_of_stock', 'pre_order'];
  if (!in_array($data['stock_status'], $allowedStockStatus)) {
    $data['stock_status'] = 'in_stock'; // Giá trị mặc định
  }

  // Validate status - chỉ cho phép các giá trị hợp lệ
  $allowedStatus = ['published', 'draft', 'pending'];
  if (!in_array($data['status'], $allowedStatus)) {
    $data['status'] = 'draft'; // Giá trị mặc định
  }

  // Tính phần trăm giảm giá
  if ($data['sale_price'] > 0 && $data['regular_price'] > 0) {
    $data['percent_reduce'] = round((($data['regular_price'] - $data['sale_price']) / $data['regular_price']) * 100);
  } else {
    $data['percent_reduce'] = 0;
  }

  if ($action === 'add') {
    if (empty($data['sku'])) {
      $data['sku'] = $productModel->generateSKU();
    }
    if (empty($data['slug'])) {
      $data['slug'] = $productModel->generateSlug($data['name_pr']);
    }

    if ($productModel->create($data)) {
      echo '<script>alert("Thêm sản phẩm thành công!"); window.location.href="admin.php?page=products";</script>';
    } else {
      echo '<script>alert("Lỗi khi thêm sản phẩm!");</script>';
    }

  } elseif ($action === 'edit' && $id) {
    if (empty($data['slug'])) {
      $data['slug'] = $productModel->generateSlug($data['name_pr']);
    }

    if ($productModel->update($id, $data)) {
      echo '<script>alert("Cập nhật sản phẩm thành công!"); window.location.href="admin.php?page=products";</script>';
    } else {
      echo '<script>alert("Lỗi khi cập nhật sản phẩm!");</script>';
    }
  }
}

if ($action === 'delete' && $id) {
  $productModel->delete($id);
  echo '<script>alert("Xóa sản phẩm thành công!"); window.location.href="admin.php?page=products";</script>';
}

if ($action === 'toggle_status' && $id) {
  $product = $productModel->findById($id);
  $newStatus = $product['status'] === 'published' ? 'draft' : 'published';
  $productModel->update($id, ['status' => $newStatus]);
  header("Location: admin.php?page=products");
  exit();
}

if ($action === 'toggle_featured' && $id) {
  $product = $productModel->findById($id);
  $newFeatured = $product['featured'] ? 0 : 1;
  $productModel->update($id, ['featured' => $newFeatured]);
  header("Location: admin.php?page=products");
  exit();
}

$products = $productModel->getAll(false);
$categories = $categoryModel->getAll(false);
$brands = $brandModel->getAll(false);
?>

<div class="container">
  <div class="header">
    <div class="header-content">
      <h1><i class="fas fa-box"></i> Quản lý Sản phẩm</h1>
      <a href="admin.php?page=products&action=add" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm sản phẩm
      </a>
    </div>
  </div>

  <?php if (in_array($action, ['add', 'edit'])): ?>
    <!-- Form thêm/sửa sản phẩm -->
    <div class="card">
      <div class="card-header">
        <h2><?php echo $action === 'add' ? 'Thêm sản phẩm mới' : 'Sửa sản phẩm'; ?></h2>
      </div>
      <div class="card-body">
        <?php
        $product = [];
        if ($action === 'edit' && $id) {
          $product = $productModel->findById($id);
        }
        ?>

        <form method="POST" class="form">
          <div class="form-row">
            <div class="form-group">
              <label for="sku">SKU</label>
              <input type="text" id="sku" name="sku"
                     value="<?php echo htmlspecialchars(isset($product['sku']) ? $product['sku'] : ''); ?>">
              <small>Tự động tạo nếu để trống</small>
            </div>
            <div class="form-group">
              <label for="name_pr">Tên sản phẩm *</label>
              <input type="text" id="name_pr" name="name_pr"
                     value="<?php echo htmlspecialchars(isset($product['name_pr']) ? $product['name_pr'] : ''); ?>"
                     required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="slug">Slug</label>
              <input type="text" id="slug" name="slug"
                     value="<?php echo htmlspecialchars(isset($product['slug']) ? $product['slug'] : ''); ?>">
              <small>Tự động tạo nếu để trống</small>
            </div>
            <div class="form-group">
              <label for="category_id">Danh mục</label>
              <select id="category_id" name="category_id">
                <option value="">-- Chọn danh mục --</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo $cat['id']; ?>"
                    <?php echo (isset($product['category_id']) && $product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="brand_id">Thương hiệu</label>
              <select id="brand_id" name="brand_id">
                <option value="">-- Chọn thương hiệu --</option>
                <?php foreach ($brands as $brand): ?>
                  <option value="<?php echo $brand['id']; ?>"
                    <?php echo (isset($product['brand_id']) && $product['brand_id'] == $brand['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($brand['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="stock_status">Tình trạng kho</label>
              <select id="stock_status" name="stock_status">
                <option value="in_stock" <?php echo (isset($product['stock_status']) && $product['stock_status'] == 'in_stock') ? 'selected' : ''; ?>>Còn hàng</option>
                <option value="out_of_stock" <?php echo (isset($product['stock_status']) && $product['stock_status'] == 'out_of_stock') ? 'selected' : ''; ?>>Hết hàng</option>
                <option value="pre_order" <?php echo (isset($product['stock_status']) && $product['stock_status'] == 'pre_order') ? 'selected' : ''; ?>>Đặt trước</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="regular_price">Giá thường *</label>
              <input type="number" id="regular_price" name="regular_price" step="0.01" min="0"
                     value="<?php echo isset($product['regular_price']) ? $product['regular_price'] : 0; ?>" required>
            </div>
            <div class="form-group">
              <label for="sale_price">Giá khuyến mãi</label>
              <input type="number" id="sale_price" name="sale_price" step="0.01" min="0"
                     value="<?php echo isset($product['sale_price']) ? $product['sale_price'] : ''; ?>">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="cost_price">Giá vốn</label>
              <input type="number" id="cost_price" name="cost_price" step="0.01" min="0"
                     value="<?php echo isset($product['cost_price']) ? $product['cost_price'] : ''; ?>">
            </div>
            <div class="form-group">
              <label for="stock_quantity">Số lượng tồn kho</label>
              <input type="number" id="stock_quantity" name="stock_quantity" min="0"
                     value="<?php echo isset($product['stock_quantity']) ? $product['stock_quantity'] : 0; ?>">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="weight">Trọng lượng (kg)</label>
              <input type="number" id="weight" name="weight" step="0.01" min="0"
                     value="<?php echo isset($product['weight']) ? $product['weight'] : ''; ?>">
            </div>
            <div class="form-group">
              <label for="dimensions">Kích thước (Dài x Rộng x Cao)</label>
              <input type="text" id="dimensions" name="dimensions"
                     value="<?php echo htmlspecialchars(isset($product['dimensions']) ? $product['dimensions'] : ''); ?>">
            </div>
          </div>

          <div class="form-group">
            <label for="short_description">Mô tả ngắn</label>
            <textarea id="short_description" name="short_description" rows="3"><?php echo htmlspecialchars(isset($product['short_description']) ? $product['short_description'] : ''); ?></textarea>
          </div>

          <div class="form-group">
            <label for="description">Mô tả chi tiết</label>
            <textarea id="description" name="description" rows="6"><?php echo htmlspecialchars(isset($product['description']) ? $product['description'] : ''); ?></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="status">Trạng thái</label>
              <select id="status" name="status">
                <option value="published" <?php echo (isset($product['status']) && $product['status'] == 'published') ? 'selected' : ''; ?>>Xuất bản</option>
                <option value="draft" <?php echo (isset($product['status']) && $product['status'] == 'draft') ? 'selected' : ''; ?>>Bản nháp</option>
                <option value="pending" <?php echo (isset($product['status']) && $product['status'] == 'pending') ? 'selected' : ''; ?>>Chờ duyệt</option>
              </select>
            </div>
            <div class="form-group checkbox-group">
              <label class="checkbox-label">
                <input type="checkbox" name="featured"
                  <?php echo isset($product['featured']) && $product['featured'] ? 'checked' : ''; ?>>
                <span class="checkmark"></span>
                Sản phẩm nổi bật
              </label>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i>
              <?php echo $action === 'add' ? 'Thêm sản phẩm' : 'Cập nhật'; ?>
            </button>
            <a href="admin.php?page=products" class="btn btn-secondary">Hủy bỏ</a>
          </div>
        </form>
      </div>
    </div>

  <?php else: ?>

    <div class="stats">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
          <h3>Tổng sản phẩm</h3>
          <span class="stat-number"><?php echo count($products); ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
          <h3>Đang bán</h3>
          <span class="stat-number">
            <?php echo $productModel->countByStatus('published'); ?>
          </span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-star"></i>
        </div>
        <div class="stat-info">
          <h3>Nổi bật</h3>
          <span class="stat-number">
            <?php echo count(array_filter($products, function ($p) {
              return $p['featured'];
            })); ?>
          </span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-info">
          <h3>Hết hàng</h3>
          <span class="stat-number">
            <?php echo count(array_filter($products, function ($p) {
              return $p['stock_status'] === 'out_of_stock';
            })); ?>
          </span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h2>Danh sách sản phẩm</h2>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
            <tr>
              <th width="50px">ID</th>
              <th width="120px">SKU</th>
              <th>Tên sản phẩm</th>
              <th width="120px">Danh mục</th>
              <th width="120px">Thương hiệu</th>
              <th width="100px">Giá</th>
              <th width="80px">Tồn kho</th>
              <th width="100px">Trạng thái</th>
              <th width="120px">Ngày tạo</th>
              <th width="120px">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product): ?>
              <tr>
                <td><?php echo $product['id']; ?></td>
                <td><code><?php echo htmlspecialchars($product['sku']); ?></code></td>
                <td>
                  <div class="product-name">
                    <?php echo htmlspecialchars($product['name_pr']); ?>
                    <?php if ($product['featured']): ?>
                      <span class="badge featured">Nổi bật</span>
                    <?php endif; ?>
                  </div>
                  <small class="text-muted"><?php echo htmlspecialchars($product['slug']); ?></small>
                </td>
                <td><?php echo htmlspecialchars(isset($product['category_name']) ? $product['category_name'] : 'N/A'); ?></td>
                <td><?php echo htmlspecialchars(isset($product['brand_name']) ? $product['brand_name'] : 'N/A'); ?></td>
                <td>
                  <div class="price-info">
                    <?php if ($product['sale_price'] > 0): ?>
                      <span class="sale-price"><?php echo number_format($product['sale_price']); ?>₫</span>
                      <span class="regular-price"><?php echo number_format($product['regular_price']); ?>₫</span>
                    <?php else: ?>
                      <span class="regular-price"><?php echo number_format($product['regular_price']); ?>₫</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                    <span class="stock-info <?php echo $product['stock_status'] === 'out_of_stock' ? 'out-of-stock' : ''; ?>">
                      <?php echo $product['stock_quantity']; ?>
                    </span>
                </td>
                <td>
                    <span class="status-badge <?php echo $product['status'] === 'published' ? 'status-published' : 'status-draft'; ?>">
                      <?php
                      $statusText = [
                        'published' => 'Đang bán',
                        'draft' => 'Nháp',
                        'pending' => 'Chờ duyệt'
                      ];
                      echo isset($statusText[$product['status']]) ? $statusText[$product['status']] : $product['status'];
                      ?>
                    </span>
                </td>
                <td><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></td>
                <td>
                  <div class="action-buttons">
                    <a href="admin.php?page=detail_product&id=<?php echo $product['id']; ?>"
                       class="btn-action btn-view" title="Xem chi tiết">
                      <i class="fas fa-eye"></i>
                    </a>

                    <a href="admin.php?page=products&action=edit&id=<?php echo $product['id']; ?>"
                       class="btn-action btn-edit" title="Sửa">
                      <i class="fas fa-edit"></i>
                    </a>

                    <a href="admin.php?page=products&action=toggle_status&id=<?php echo $product['id']; ?>"
                       class="btn-action btn-status" title="Đổi trạng thái">
                      <i class="fas fa-power-off"></i>
                    </a>

                    <a href="admin.php?page=products&action=toggle_featured&id=<?php echo $product['id']; ?>"
                       class="btn-action btn-featured" title="Đổi nổi bật">
                      <i class="fas fa-star"></i>
                    </a>

                    <a href="admin.php?page=products&action=delete&id=<?php echo $product['id']; ?>"
                       class="btn-action btn-delete" title="Xóa"
                       onclick="return confirm('Xóa sản phẩm này?')">
                      <i class="fas fa-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  <?php endif; ?>
</div>

<style>
  .product-name {
    font-weight: 500;
    margin-bottom: 5px;
  }

  .badge.featured {
    background: #fff3e0;
    color: #f57c00;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
    margin-left: 5px;
  }

  .price-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .sale-price {
    color: #e53935;
    font-weight: 600;
    font-size: 14px;
  }

  .regular-price {
    color: #6c757d;
    text-decoration: line-through;
    font-size: 12px;
  }

  .stock-info {
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 500;
    background: #e8f5e8;
    color: #388e3c;
  }

  .stock-info.out-of-stock {
    background: #ffebee;
    color: #d32f2f;
  }

  .btn-featured {
    background: #fff3e0;
    color: #f57c00;
  }

  .btn-featured:hover {
    background: #ffe0b2;
  }

  /* Giữ nguyên các style khác từ categories/brands */
  .container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
  }

  .header {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }

  .header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .header h1 {
    color: #2c3e50;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
  }

  .card-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
  }

  .card-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.5rem;
  }

  .card-body {
    padding: 20px;
  }

  .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-primary {
    background: #3498db;
    color: white;
  }

  .btn-primary:hover {
    background: #2980b9;
  }

  .btn-secondary {
    background: #95a5a6;
    color: white;
  }

  .btn-secondary:hover {
    background: #7f8c8d;
  }

  .stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
  }

  .stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e3f2fd;
  }

  .stat-icon i {
    font-size: 24px;
    color: #1976d2;
  }

  .stat-info h3 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #7f8c8d;
    font-weight: 500;
  }

  .stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
  }

  .form {
    max-width: 1000px;
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
  }

  .form-group label {
    font-weight: 500;
    margin-bottom: 5px;
    color: #2c3e50;
  }

  .form-group input,
  .form-group select,
  .form-group textarea {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
  }

  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
  }

  .form-group small {
    color: #7f8c8d;
    font-size: 12px;
    margin-top: 5px;
  }

  .checkbox-group {
    margin: 20px 0;
  }

  .checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
  }

  .form-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
  }

  .table-responsive {
    overflow-x: auto;
  }

  .table {
    width: 100%;
    border-collapse: collapse;
  }

  .table th,
  .table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
  }

  .table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
  }

  .table tbody tr:hover {
    background: #f8f9fa;
  }

  .text-muted {
    color: #6c757d;
    font-size: 12px;
  }

  .status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
  }

  .status-published {
    background: #e8f5e8;
    color: #388e3c;
  }

  .status-draft {
    background: #fff3e0;
    color: #f57c00;
  }

  .action-buttons {
    display: flex;
    gap: 5px;
  }

  .btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 4px;
    text-decoration: none;
    transition: all 0.3s ease;
  }

  .btn-edit {
    background: #e3f2fd;
    color: #1976d2;
  }

  .btn-edit:hover {
    background: #bbdefb;
  }

  .btn-status {
    background: #fff3e0;
    color: #f57c00;
  }

  .btn-status:hover {
    background: #ffe0b2;
  }

  .btn-delete {
    background: #ffebee;
    color: #d32f2f;
  }

  .btn-delete:hover {
    background: #ffcdd2;
  }

  .btn-view {
    background: #e8f5e8;
    color: #388e3c;
  }

  .btn-view:hover {
    background: #c8e6c9;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Auto generate slug from name
    const nameInput = document.getElementById('name_pr');
    const slugInput = document.getElementById('slug');

    if (nameInput && slugInput) {
      nameInput.addEventListener('blur', function() {
        if (!slugInput.value) {
          const slug = this.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)+/g, '');
          slugInput.value = slug;
        }
      });
    }

    // Auto calculate sale price percentage
    const regularPriceInput = document.getElementById('regular_price');
    const salePriceInput = document.getElementById('sale_price');

    if (regularPriceInput && salePriceInput) {
      salePriceInput.addEventListener('blur', function() {
        const regularPrice = parseFloat(regularPriceInput.value) || 0;
        const salePrice = parseFloat(this.value) || 0;

        if (salePrice > 0 && regularPrice > 0) {
          const percentReduce = Math.round(((regularPrice - salePrice) / regularPrice) * 100);
          console.log('Phần trăm giảm giá:', percentReduce + '%');
        }
      });
    }
  });
</script>
