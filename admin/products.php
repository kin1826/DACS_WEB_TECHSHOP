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
switch ($action) {
  case 'delete':
    if ($id) {
      $productModel->delete($id);
      echo '<script>alert("Xóa sản phẩm thành công!"); window.location.href="admin.php?page=products";</script>';
    }
    break;
  case 'toggle_status':
    if ($id) {
      $product = $productModel->findById($id);
      $newStatus = $product['status'] === 'published' ? 'draft' : 'published';
      $productModel->update($id, ['status' => $newStatus]);
      header("Location: admin.php?page=products");
      exit();
    }
    break;
}

$products = $productModel->getAllWithDetails();
$categories = $categoryModel->getAll();
$brands = $brandModel->getAll();
?>

<div class="products-header">
  <div class="header-actions">
    <h2>Quản lý Sản phẩm</h2>
    <a href="admin.php?page=products&action=add" class="btn btn-primary">
      <i class="fas fa-plus"></i> Thêm sản phẩm mới
    </a>
  </div>

  <div class="filters">
    <select id="categoryFilter" class="filter-select">
      <option value="">Tất cả danh mục</option>
      <?php foreach ($categories as $category): ?>
        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
      <?php endforeach; ?>
    </select>

    <select id="statusFilter" class="filter-select">
      <option value="">Tất cả trạng thái</option>
      <option value="published">Đang bán</option>
      <option value="draft">Bản nháp</option>
      <option value="archived">Lưu trữ</option>
    </select>

    <div class="search-box">
      <input type="text" id="productSearch" placeholder="Tìm kiếm sản phẩm...">
      <i class="fas fa-search"></i>
    </div>
  </div>
</div>

<div class="stats-cards">
  <div class="stat-card">
    <div class="stat-icon" style="background: #e3f2fd;">
      <i class="fas fa-box" style="color: #1976d2;"></i>
    </div>
    <div class="stat-info">
      <h3>Tổng sản phẩm</h3>
      <span class="stat-number"><?php echo count($products); ?></span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background: #e8f5e8;">
      <i class="fas fa-check-circle" style="color: #388e3c;"></i>
    </div>
    <div class="stat-info">
      <h3>Đang bán</h3>
      <span class="stat-number">
                <?php echo count(array_filter($products, function ($p) {
                  return $p['status'] === 'published';
                })); ?>
            </span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background: #fff3e0;">
      <i class="fas fa-fire" style="color: #f57c00;"></i>
    </div>
    <div class="stat-info">
      <h3>Sản phẩm nổi bật</h3>
      <span class="stat-number">
                <?php echo count(array_filter($products, function ($p) {
                  return $p['featured'];
                })); ?>
            </span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background: #ffebee;">
      <i class="fas fa-exclamation-triangle" style="color: #d32f2f;"></i>
    </div>
    <div class="stat-info">
      <h3>Hết hàng</h3>
      <span class="stat-number">
                <?php echo count(array_filter($products, function ($p) {
                  return $p['stock_quantity'] <= 0;
                })); ?>
            </span>
    </div>
  </div>
</div>

<div class="data-table-container">
  <div class="table-responsive">
    <table class="data-table">
      <thead>
      <tr>
        <th width="60px">Hình ảnh</th>
        <th>Tên sản phẩm</th>
        <th width="120px">SKU</th>
        <th width="100px">Giá</th>
        <th width="80px">Tồn kho</th>
        <th width="120px">Danh mục</th>
        <th width="100px">Thương hiệu</th>
        <th width="100px">Trạng thái</th>
        <th width="120px">Ngày tạo</th>
        <th width="100px">Thao tác</th>
      </tr>
      </thead>
      <tbody>
      <?php foreach ($products as $product): ?>
        <tr data-category="<?php echo $product['category_id']; ?>" data-status="<?php echo $product['status']; ?>">
          <td>
            <?php if (!empty($product['main_image'])): ?>
              <img src="<?php echo $product['main_image']; ?>" alt="<?php echo htmlspecialchars($product['name_pr']); ?>" class="product-thumb">
            <?php else: ?>
              <div class="no-image">No Image</div>
            <?php endif; ?>
          </td>
          <td>
            <div class="product-name"><?php echo htmlspecialchars($product['name_pr']); ?></div>
            <?php if ($product['featured']): ?>
              <span class="badge featured">Nổi bật</span>
            <?php endif; ?>
          </td>
          <td><code><?php echo htmlspecialchars($product['sku']); ?></code></td>
          <td>
            <div class="price-info">
              <?php if ($product['sale_price']): ?>
                <span class="sale-price"><?php echo number_format($product['sale_price']); ?>đ</span>
                <span class="regular-price"><?php echo number_format($product['regular_price']); ?>đ</span>
              <?php else: ?>
                <span class="current-price"><?php echo number_format($product['regular_price']); ?>đ</span>
              <?php endif; ?>
            </div>
          </td>
          <td>
            <div class="stock-info <?php echo $product['stock_quantity'] <= 0 ? 'out-of-stock' : ''; ?>">
              <?php echo $product['stock_quantity']; ?>
            </div>
          </td>
          <td><?php echo htmlspecialchars($product['category_name']); ?></td>
          <td><?php echo htmlspecialchars($product['brand_name']); ?></td>
          <td>
                        <span class="status-badge status-<?php echo $product['status']; ?>">
                            <?php
                            $statusText = [
                              'published' => 'Đang bán',
                              'draft' => 'Bản nháp',
                              'archived' => 'Lưu trữ'
                            ];
                            echo $statusText[$product['status']];
                            ?>
                        </span>
          </td>
          <td><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></td>
          <td>
            <div class="action-buttons">
              <a href="admin.php?page=products&action=edit&id=<?php echo $product['id']; ?>" class="btn-action btn-edit" title="Sửa">
                <i class="fas fa-edit"></i>
              </a>

              <a href="admin.php?page=products&action=toggle_status&id=<?php echo $product['id']; ?>" class="btn-action btn-status" title="Đổi trạng thái">
                <i class="fas fa-power-off"></i>
              </a>

              <a href="admin.php?page=products&action=delete&id=<?php echo $product['id']; ?>" class="btn-action btn-delete" title="Xóa" onclick="return confirm('Xóa sản phẩm này?')">
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

<style>
  .products-header {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }

  .header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .header-actions h2 {
    margin: 0;
    color: #2c3e50;
  }

  .filters {
    display: flex;
    gap: 15px;
    align-items: center;
  }

  .filter-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: white;
  }

  .search-box {
    position: relative;
  }

  .search-box input {
    padding: 8px 35px 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    width: 250px;
  }

  .search-box i {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #7f8c8d;
  }

  .stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
  }

  .stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
  }

  .stat-info h3 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #7f8c8d;
  }

  .stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
  }

  .data-table-container {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }

  .table-responsive {
    overflow-x: auto;
  }

  .product-thumb {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
  }

  .no-image {
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border: 1px dashed #dee2e6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: #6c757d;
  }

  .product-name {
    font-weight: 500;
    margin-bottom: 5px;
  }

  .badge {
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 500;
  }

  .badge.featured {
    background: #fff3cd;
    color: #856404;
  }

  .price-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .sale-price {
    color: #e74c3c;
    font-weight: bold;
  }

  .regular-price {
    text-decoration: line-through;
    color: #7f8c8d;
    font-size: 12px;
  }

  .current-price {
    font-weight: bold;
    color: #2c3e50;
  }

  .stock-info {
    padding: 4px 8px;
    border-radius: 4px;
    text-align: center;
    font-weight: 500;
  }

  .stock-info.out-of-stock {
    background: #f8d7da;
    color: #721c24;
  }

  .status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
  }

  .status-published {
    background: #d1edff;
    color: #0d6efd;
  }

  .status-draft {
    background: #fff3cd;
    color: #856404;
  }

  .status-archived {
    background: #f8d7da;
    color: #721c24;
  }

  .action-buttons {
    display: flex;
    gap: 5px;
  }

  .btn-action {
    padding: 6px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
  }

  .btn-edit {
    background: #e3f2fd;
    color: #1976d2;
  }

  .btn-status {
    background: #fff3cd;
    color: #856404;
  }

  .btn-delete {
    background: #f8d7da;
    color: #dc3545;
  }

  .btn-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const productSearch = document.getElementById('productSearch');
    const tableRows = document.querySelectorAll('.data-table tbody tr');

    function filterProducts() {
      const categoryValue = categoryFilter.value;
      const statusValue = statusFilter.value;
      const searchValue = productSearch.value.toLowerCase();

      tableRows.forEach(row => {
        const category = row.getAttribute('data-category');
        const status = row.getAttribute('data-status');
        const productName = row.querySelector('.product-name').textContent.toLowerCase();

        const categoryMatch = !categoryValue || category === categoryValue;
        const statusMatch = !statusValue || status === statusValue;
        const searchMatch = !searchValue || productName.includes(searchValue);

        row.style.display = categoryMatch && statusMatch && searchMatch ? '' : 'none';
      });
    }

    categoryFilter.addEventListener('change', filterProducts);
    statusFilter.addEventListener('change', filterProducts);
    productSearch.addEventListener('input', filterProducts);
  });
</script>
