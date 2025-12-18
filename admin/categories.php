<?php
// admin/categories.php
require_once 'class/category.php';

$categoryModel = new Category();
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Xử lý actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = [
    'name' => trim($_POST['name']),
    'slug' => trim($_POST['slug']),
    'description' => trim($_POST['description']),
    'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
    'sort_order' => (int)$_POST['sort_order'],
    'is_active' => isset($_POST['is_active']) ? 1 : 0
  ];

  if ($action === 'add') {
    if (empty($data['slug'])) {
      $data['slug'] = $categoryModel->generateSlug($data['name']);
    }

    try {
      $imageFile = isset($_FILES['image']) ? $_FILES['image'] : null;
      $categoryModel->createWithImage($data, $imageFile);
      echo '<script>alert("Thêm danh mục thành công!"); window.location.href="admin.php?page=categories";</script>';
    } catch (Exception $e) {
      echo '<script>alert("Lỗi: ' . $e->getMessage() . '");</script>';
    }

  } elseif ($action === 'edit' && $id) {
    if (empty($data['slug'])) {
      $data['slug'] = $categoryModel->generateSlug($data['name']);
    }

    try {
      $imageFile = isset($_FILES['image']) ? $_FILES['image'] : null;
      $categoryModel->updateWithImage($id, $data, $imageFile);
      echo '<script>alert("Cập nhật danh mục thành công!"); window.location.href="admin.php?page=categories";</script>';
    } catch (Exception $e) {
      echo '<script>alert("Lỗi: ' . $e->getMessage() . '");</script>';
    }
  }
}

if ($action === 'delete' && $id) {
  $categoryModel->deleteWithImage($id);
  echo '<script>alert("Xóa danh mục thành công!"); window.location.href="admin.php?page=categories";</script>';
}

if ($action === 'toggle_status' && $id) {
  $category = $categoryModel->findById($id);
  $newStatus = $category['is_active'] ? 0 : 1;
  $categoryModel->update($id, ['is_active' => $newStatus]);
  header("Location: ../admin.php?page=categories");
  exit();
}

$categories = $categoryModel->getAll(false);
$hierarchicalCategories = $categoryModel->getHierarchical();
?>

<!-- Phần HTML giữ nguyên như trước -->
<div class="container">
  <!-- ... phần HTML không thay đổi ... -->
</div>

<div class="container">
  <div class="header">
    <div class="header-content">
      <h1><i class="fas fa-folder"></i> Quản lý Danh mục</h1>
      <a href="../admin.php?page=categories&action=add" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm danh mục
      </a>
    </div>
  </div>

  <?php if (in_array($action, ['add', 'edit'])): ?>
    <!-- Form thêm/sửa danh mục -->
    <div class="card">
      <div class="card-header">
        <h2><?php echo $action === 'add' ? 'Thêm danh mục mới' : 'Sửa danh mục'; ?></h2>
      </div>
      <div class="card-body">
        <?php
        $category = [];
        if ($action === 'edit' && $id) {
          $category = $categoryModel->findById($id);
        }
        ?>

        <form method="POST" enctype="multipart/form-data" class="form">
          <div class="form-row">
            <div class="form-group">
              <label for="name">Tên danh mục *</label>
              <input type="text" id="name" name="name" class="form-control"
                     value="<?php echo htmlspecialchars(isset($category['name']) ? $category['name'] : ''); ?>"
                     required>
            </div>
            <div class="form-group">
              <label for="slug">Slug</label>
              <input type="text" id="slug" name="slug" class="form-control"
                     value="<?php echo htmlspecialchars(isset($category['slug']) ? $category['slug'] : ''); ?>">
              <small>Tự động tạo nếu để trống</small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="parent_id">Danh mục cha</label>
              <select id="parent_id" name="parent_id"  class="form-control">
                <option value="">-- Không có --</option>
                <?php foreach ($categories as $cat): ?>
                  <?php if (!isset($category['id']) || $cat['id'] != $category['id']): ?>
                    <option value="<?php echo $cat['id']; ?>"
                      <?php echo (isset($category['parent_id']) && $category['parent_id'] == $cat['id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="sort_order">Thứ tự</label>
              <input type="number" id="sort_order" name="sort_order" class="form-control"
                     value="<?php echo isset($category['sort_order']) ? $category['sort_order'] : 0; ?>" min="0">
            </div>
          </div>

          <div class="form-group">
            <label for="description">Mô tả</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars(isset($category['description']) ? $category['description'] : ''); ?></textarea>
          </div>

          <div class="form-group">
            <label for="image">Hình ảnh</label>
            <input type="file" id="image" name="image" class="form-control" accept="image/*">
            <?php if (isset($category['image']) && $category['image']): ?>
              <div class="current-image">
                <p>Ảnh hiện tại:</p>
                <img src="../uploads/categories/<?php echo $category['image']; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" style="max-width: 200px; margin-top: 10px;">
              </div>
            <?php endif; ?>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" name="is_active" class="form-check-input"
                <?php echo !isset($category['is_active']) || $category['is_active'] ? 'checked' : ''; ?>>
              <span class="checkmark"></span>
              Kích hoạt
            </label>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i>
              <?php echo $action === 'add' ? 'Thêm danh mục' : 'Cập nhật'; ?>
            </button>
            <a href="admin.php?page=categories" class="btn btn-secondary">Hủy bỏ</a>
          </div>
        </form>
      </div>
    </div>

  <?php else: ?>

    <div class="stats">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-folder"></i>
        </div>
        <div class="stat-info">
          <h3>Tổng danh mục</h3>
          <span class="stat-number"><?php echo count($categories); ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
          <h3>Đang kích hoạt</h3>
          <span class="stat-number">
            <?php echo count(array_filter($categories, function ($c) {
              return $c['is_active'];
            })); ?>
          </span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-sitemap"></i>
        </div>
        <div class="stat-info">
          <h3>Danh mục chính</h3>
          <span class="stat-number">
            <?php echo count(array_filter($categories, function ($c) {
              return $c['parent_id'] === null;
            })); ?>
          </span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h2>Danh sách danh mục</h2>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
            <tr>
              <th width="50px">ID</th>
              <th>Tên danh mục</th>
              <th width="150px">Slug</th>
              <th width="120px">Danh mục cha</th>
              <th width="80px">Thứ tự</th>
              <th width="100px">Hình ảnh</th>
              <th width="100px">Số sản phẩm</th>
              <th width="100px">Trạng thái</th>
              <th width="120px">Ngày tạo</th>
              <th width="100px">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $category): ?>
              <tr>
                <td><?php echo $category['id']; ?></td>
                <td>
                  <div class="category-name">
                    <?php echo htmlspecialchars($category['name']); ?>
                    <?php if ($category['parent_id']): ?>
                      <small class="text-muted">(Danh mục con)</small>
                    <?php endif; ?>
                  </div>
                </td>
                <td><code><?php echo htmlspecialchars($category['slug']); ?></code></td>
                <td>
                  <?php
                  if ($category['parent_id']) {
                    $parent = $categoryModel->findById($category['parent_id']);
                    echo $parent ? htmlspecialchars($parent['name']) : 'N/A';
                  } else {
                    echo '<span class="badge primary">Danh mục chính</span>';
                  }
                  ?>
                </td>
                <td><?php echo $category['sort_order']; ?></td>
                <td>
                  <?php if ($category['image']): ?>
                    <img src="../uploads/categories/<?php echo $category['image']; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="thumbnail">
                  <?php else: ?>
                    <span class="no-image">No image</span>
                  <?php endif; ?>
                </td>
                <td>
                    <span class="product-count">
                      <?php echo $categoryModel->countProducts($category['id']); ?>
                    </span>
                </td>
                <td>
                    <span class="status-badge <?php echo $category['is_active'] ? 'status-published' : 'status-draft'; ?>">
                      <?php echo $category['is_active'] ? 'Đang kích hoạt' : 'Đã ẩn'; ?>
                    </span>
                </td>
                <td><?php echo date('d/m/Y', strtotime($category['created_at'])); ?></td>
                <td>
                  <div class="action-buttons">
                    <a href="admin.php?page=categories&action=edit&id=<?php echo $category['id']; ?>"
                       class="btn-action btn-edit" title="Sửa">
                      <i class="fas fa-edit"></i>
                    </a>

                    <a href="admin.php?page=categories&action=toggle_status&id=<?php echo $category['id']; ?>"
                       class="btn-action btn-status" title="Đổi trạng thái">
                      <i class="fas fa-power-off"></i>
                    </a>

                    <a href="admin.php?page=categories&action=delete&id=<?php echo $category['id']; ?>"
                       class="btn-action btn-delete" title="Xóa"
                       onclick="return confirm('Xóa danh mục này?')">
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



<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Auto generate slug from name
    const nameInput = document.getElementById('name');
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
  });
</script>

<style>
  /* Reset và Base */
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    background: #f5f7fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
    line-height: 1.6;
  }

  .container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
  }

  /* Header */
  .header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
  }

  .header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .header h1 {
    margin: 0;
    font-size: 32px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .header h1 i {
    background: rgba(255, 255, 255, 0.2);
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
  }

  .header-subtitle {
    margin-top: 10px;
    opacity: 0.9;
    font-size: 16px;
    font-weight: 400;
  }

  /* Buttons */
  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 28px;
    border: none;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }

  .btn::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 5px;
    height: 5px;
    background: rgba(255, 255, 255, 0.5);
    opacity: 0;
    border-radius: 100%;
    transform: scale(1, 1) translate(-50%);
    transform-origin: 50% 50%;
  }

  .btn:focus:not(:active)::after {
    animation: ripple 1s ease-out;
  }

  @keyframes ripple {
    0% {
      transform: scale(0, 0);
      opacity: 0.5;
    }
    100% {
      transform: scale(20, 20);
      opacity: 0;
    }
  }

  .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
  }

  .btn-secondary {
    background: #e4e6ef;
    color: #5e6278;
  }

  .btn-secondary:hover {
    background: #d8dae5;
    transform: translateY(-2px);
  }

  .btn-success {
    background: linear-gradient(135deg, #0abb87 0%, #009ef7 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(10, 187, 135, 0.3);
  }

  .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(10, 187, 135, 0.4);
  }

  .btn-danger {
    background: linear-gradient(135deg, #f1416c 0%, #ff8a65 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(241, 65, 108, 0.3);
  }

  .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(241, 65, 108, 0.4);
  }

  .btn-sm {
    padding: 10px 20px;
    font-size: 14px;
  }

  .btn-lg {
    padding: 16px 32px;
    font-size: 16px;
  }

  /* Cards */
  .card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
    margin-bottom: 30px;
    overflow: hidden;
    border: 1px solid #e4e6ef;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  }

  .card-header {
    background: #f5f7fa;
    padding: 24px 30px;
    border-bottom: 1px solid #e4e6ef;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .card-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 22px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .card-body {
    padding: 30px;
  }

  .card-footer {
    padding: 24px 30px;
    background: #fafbfc;
    border-top: 1px solid #e4e6ef;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  /* Stats Cards */
  .stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
  }

  .stat-card {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    gap: 20px;
    border: 1px solid #e4e6ef;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  }

  .stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    flex-shrink: 0;
  }

  .stat-icon i {
    font-size: 28px;
    color: white;
  }

  .stat-info {
    flex: 1;
  }

  .stat-info h3 {
    margin: 0 0 8px 0;
    font-size: 15px;
    color: #7f8c8d;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1;
  }

  .stat-trend {
    display: inline-block;
    margin-top: 8px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
  }

  .trend-up {
    background: rgba(10, 187, 135, 0.1);
    color: #0abb87;
  }

  .trend-down {
    background: rgba(241, 65, 108, 0.1);
    color: #f1416c;
  }

  /* Forms - Vertical Layout */
  .form {
    max-width: 100%;
  }

  .form-section {
    margin-bottom: 40px;
  }

  .form-section-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e4e6ef;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .form-group {
    margin-bottom: 25px;
    display: flex;
    flex-direction: column;
  }

  .form-group.full-width {
    grid-column: 1 / -1;
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 25px;
    margin-bottom: 25px;
  }

  @media (min-width: 768px) {
    .form-row {
      grid-template-columns: 1fr 1fr;
    }
  }

  .form-label {
    font-weight: 600;
    margin-bottom: 10px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
  }

  .form-label .required {
    color: #f1416c;
  }

  .form-control {
    padding: 14px 16px;
    border: 2px solid #e4e6ef;
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.3s ease;
    background: #fafbfc;
    width: 100%;
  }

  .form-control:focus {
    border-color: #667eea;
    background: white;
    outline: none;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
  }

  .form-control::placeholder {
    color: #a1a5b7;
  }

  .form-text {
    color: #7f8c8d;
    font-size: 13px;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .form-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%235e6278' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 16px;
    padding-right: 45px;
  }

  /* Checkbox and Radio */
  .form-check {
    margin: 20px 0;
  }

  .form-check-input {
    display: none;
  }

  .form-check-label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    padding: 10px;
    border-radius: 10px;
    transition: background 0.3s ease;
  }

  .form-check-label:hover {
    background: #f5f7fa;
  }

  .form-check-custom {
    width: 22px;
    height: 22px;
    border: 2px solid #e4e6ef;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
  }

  .form-check-input:checked + .form-check-label .form-check-custom {
    background: #667eea;
    border-color: #667eea;
  }

  .form-check-input:checked + .form-check-label .form-check-custom::after {
    content: '✓';
    color: white;
    font-size: 14px;
    font-weight: bold;
  }

  /* Switch Toggle */
  .form-switch {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
  }

  .switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }

  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #e4e6ef;
    transition: .4s;
    border-radius: 34px;
  }

  .slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
  }

  input:checked + .slider {
    background-color: #667eea;
  }

  input:checked + .slider:before {
    transform: translateX(26px);
  }

  /* Form Actions */
  .form-actions {
    display: flex;
    gap: 15px;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #e4e6ef;
  }

  .form-actions.sticky {
    position: sticky;
    bottom: 0;
    background: white;
    padding: 20px 0;
    margin-bottom: -20px;
    z-index: 10;
  }

  /* Tables */
  .table-responsive {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid #e4e6ef;
  }

  .table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 800px;
  }

  .table th,
  .table td {
    padding: 18px 20px;
    text-align: left;
    border-bottom: 1px solid #e4e6ef;
    vertical-align: middle;
  }

  .table th {
    background: #f5f7fa;
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
  }

  .table tbody tr {
    transition: background 0.3s ease;
  }

  .table tbody tr:hover {
    background: #f8f9fa;
  }

  .table tbody tr:last-child td {
    border-bottom: none;
  }

  /* Badges */
  .badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .badge-primary {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
  }

  .badge-success {
    background: rgba(10, 187, 135, 0.1);
    color: #0abb87;
  }

  .badge-warning {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
  }

  .badge-danger {
    background: rgba(241, 65, 108, 0.1);
    color: #f1416c;
  }

  .badge-info {
    background: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
  }

  /* Action Buttons */
  .action-buttons {
    display: flex;
    gap: 8px;
  }

  .btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
  }

  .btn-action i {
    font-size: 16px;
  }

  .btn-edit {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
  }

  .btn-edit:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
  }

  .btn-delete {
    background: rgba(241, 65, 108, 0.1);
    color: #f1416c;
  }

  .btn-delete:hover {
    background: #f1416c;
    color: white;
    transform: translateY(-2px);
  }

  .btn-view {
    background: rgba(10, 187, 135, 0.1);
    color: #0abb87;
  }

  .btn-view:hover {
    background: #0abb87;
    color: white;
    transform: translateY(-2px);
  }

  /* Status Indicators */
  .status-badge {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .status-published {
    background: rgba(10, 187, 135, 0.1);
    color: #0abb87;
    border: 1px solid rgba(10, 187, 135, 0.2);
  }

  .status-draft {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
    border: 1px solid rgba(255, 193, 7, 0.2);
  }

  .status-archived {
    background: rgba(108, 117, 125, 0.1);
    color: #6c757d;
    border: 1px solid rgba(108, 117, 125, 0.2);
  }

  /* Thumbnails */
  .thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #e4e6ef;
    transition: transform 0.3s ease;
  }

  .thumbnail:hover {
    transform: scale(1.1);
  }

  .no-image {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    background: #f5f7fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #a1a5b7;
    font-size: 12px;
    border: 2px dashed #e4e6ef;
  }

  /* Product Info */
  .product-info {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .product-details {
    flex: 1;
  }

  .product-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 4px;
  }

  .product-sku {
    color: #7f8c8d;
    font-size: 13px;
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #a1a5b7;
  }

  .empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
  }

  .empty-state h3 {
    margin: 0 0 10px 0;
    color: #5e6278;
  }

  .empty-state p {
    max-width: 400px;
    margin: 0 auto 20px;
  }

  /* Loading Skeleton */
  .skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 8px;
  }

  @keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
  }

  /* Utility Classes */
  .text-center { text-align: center; }
  .text-right { text-align: right; }
  .text-muted { color: #7f8c8d; }
  .font-bold { font-weight: 700; }
  .font-semibold { font-weight: 600; }
  .mb-0 { margin-bottom: 0 !important; }
  .mt-0 { margin-top: 0 !important; }
  .mb-20 { margin-bottom: 20px; }
  .mb-30 { margin-bottom: 30px; }
  .mt-20 { margin-top: 20px; }
  .mt-30 { margin-top: 30px; }
  .p-0 { padding: 0 !important; }
  .w-100 { width: 100%; }

  /* Responsive */
  @media (max-width: 768px) {
    .container {
      padding: 15px;
    }

    .header {
      padding: 20px;
      border-radius: 12px;
    }

    .header-content {
      flex-direction: column;
      gap: 15px;
      text-align: center;
    }

    .header h1 {
      font-size: 24px;
    }

    .stats {
      grid-template-columns: 1fr;
    }

    .card-header {
      flex-direction: column;
      gap: 15px;
      text-align: center;
    }

    .form-actions {
      flex-direction: column;
    }

    .btn {
      width: 100%;
      justify-content: center;
    }

    .action-buttons {
      justify-content: center;
    }
  }

  /* Animations */
  .fade-in {
    animation: fadeIn 0.5s ease;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .slide-in {
    animation: slideIn 0.4s ease;
  }

  @keyframes slideIn {
    from { transform: translateX(-20px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }
</style>
