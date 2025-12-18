<?php
// admin/brands.php
require_once 'class/brand.php';

$brandModel = new Brand();
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Xử lý actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = [
    'name' => trim($_POST['name']),
    'slug' => trim($_POST['slug']),
    'description' => trim($_POST['description']),
    'website' => trim($_POST['website']),
    'is_active' => isset($_POST['is_active']) ? 1 : 0
  ];
  // ĐÃ BỎ sort_order

  if ($action === 'add') {
    if (empty($data['slug'])) {
      $data['slug'] = $brandModel->generateSlug($data['name']);
    }

    try {
      $imageFile = isset($_FILES['image']) ? $_FILES['image'] : null;
      $brandModel->createWithImage($data, $imageFile);
      echo '<script>alert("Thêm thương hiệu thành công!"); window.location.href="admin.php?page=brands";</script>';
    } catch (Exception $e) {
      echo '<script>alert("Lỗi: ' . $e->getMessage() . '");</script>';
    }

  } elseif ($action === 'edit' && $id) {
    if (empty($data['slug'])) {
      $data['slug'] = $brandModel->generateSlug($data['name']);
    }

    try {
      $imageFile = isset($_FILES['image']) ? $_FILES['image'] : null;
      $brandModel->updateWithImage($id, $data, $imageFile);
      echo '<script>alert("Cập nhật thương hiệu thành công!"); window.location.href="admin.php?page=brands";</script>';
    } catch (Exception $e) {
      echo '<script>alert("Lỗi: ' . $e->getMessage() . '");</script>';
    }
  }
}

if ($action === 'delete' && $id) {
  $brandModel->deleteWithImage($id);
  echo '<script>alert("Xóa thương hiệu thành công!"); window.location.href="admin.php?page=brands";</script>';
}

if ($action === 'toggle_status' && $id) {
  $brand = $brandModel->findById($id);
  $newStatus = $brand['is_active'] ? 0 : 1;
  $brandModel->update($id, ['is_active' => $newStatus]);
  header("Location: ../admin.php?page=brands");
  exit();
}

$brands = $brandModel->getAll(false);
?>

<div class="container">
  <div class="header">
    <div class="header-content">
      <h1><i class="fas fa-tags"></i> Quản lý Thương hiệu</h1>
      <a href="admin.php?page=brands&action=add" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm thương hiệu
      </a>
    </div>
  </div>

  <?php if (in_array($action, ['add', 'edit'])): ?>
    <!-- Form thêm/sửa thương hiệu -->
    <div class="card">
      <div class="card-header">
        <h2><?php echo $action === 'add' ? 'Thêm thương hiệu mới' : 'Sửa thương hiệu'; ?></h2>
      </div>
      <div class="card-body">
        <?php
        $brand = [];
        if ($action === 'edit' && $id) {
          $brand = $brandModel->findById($id);
        }
        ?>

        <form method="POST" enctype="multipart/form-data" class="form">
          <div class="form-row form-section">
            <div class="form-group ">
              <label for="name">Tên thương hiệu *</label>
              <input type="text" id="name" name="name" class="form-control"
                     value="<?php echo htmlspecialchars(isset($brand['name']) ? $brand['name'] : ''); ?>"
                     required>
            </div>
          </div>

          <div class="form-row form-section">
            <div class="form-group">
              <label for="slug">Slug</label>
              <input type="text" id="slug" name="slug" class="form-control"
                     value="<?php echo htmlspecialchars(isset($brand['slug']) ? $brand['slug'] : ''); ?>">
              <small>Tự động tạo nếu để trống</small>
            </div>
          </div>

          <div class="form-row form-section">
            <div class="form-group">
              <label for="website">Website</label>
              <input type="url" id="website" name="website" class="form-control"
                     value="<?php echo htmlspecialchars(isset($brand['website']) ? $brand['website'] : ''); ?>">
            </div>
          </div>

          <div class="form-row form-section">
            <div class="form-group">
              <label for="description">Mô tả</label>
              <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars(isset($brand['description']) ? $brand['description'] : ''); ?></textarea>
            </div>
          </div>

          <div class="form-row form-section">
            <div class="form-group">
              <label for="image">Logo thương hiệu</label>
              <input type="file" id="image" name="image" class="form-control" accept="image/*">
              <?php if (isset($brand['logo']) && $brand['logo']): ?>
                <div class="current-image">
                  <p>Logo hiện tại:</p>
                  <img src="../img/adminUP/brands/<?php echo $brand['logo']; ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>" style="max-width: 200px; margin-top: 10px;">
                </div>
              <?php endif; ?>
            </div>
          </div>


          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" name="is_active" class="form-check-input"
                <?php echo !isset($brand['is_active']) || $brand['is_active'] ? 'checked' : ''; ?>>
              <span class="checkmark"></span>
              Kích hoạt
            </label>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i>
              <?php echo $action === 'add' ? 'Thêm thương hiệu' : 'Cập nhật'; ?>
            </button>
            <a href="admin.php?page=brands" class="btn btn-secondary">Hủy bỏ</a>
          </div>
        </form>
      </div>
    </div>

  <?php else: ?>

    <div class="stats">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-tags"></i>
        </div>
        <div class="stat-info">
          <h3>Tổng thương hiệu</h3>
          <span class="stat-number"><?php echo count($brands); ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
          <h3>Đang kích hoạt</h3>
          <span class="stat-number">
            <?php echo count(array_filter($brands, function ($b) {
              return $b['is_active'];
            })); ?>
          </span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-globe"></i>
        </div>
        <div class="stat-info">
          <h3>Có website</h3>
          <span class="stat-number">
            <?php echo count(array_filter($brands, function ($b) {
              return !empty($b['website']);
            })); ?>
          </span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h2>Danh sách thương hiệu</h2>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
            <tr>
              <th width="50px">ID</th>
              <th>Tên thương hiệu</th>
              <th width="150px">Slug</th>
              <th width="120px">Website</th>
              <th width="100px">Logo</th>
              <th width="100px">Số sản phẩm</th>
              <th width="100px">Trạng thái</th>
              <th width="120px">Ngày tạo</th>
              <th width="100px">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($brands as $brand): ?>
              <tr>
                <td><?php echo $brand['id']; ?></td>
                <td>
                  <div class="brand-name">
                    <?php echo htmlspecialchars($brand['name']); ?>
                  </div>
                </td>
                <td><code><?php echo htmlspecialchars($brand['slug']); ?></code></td>
                <td>
                  <?php if ($brand['website']): ?>
                    <a href="<?php echo htmlspecialchars($brand['website']); ?>" target="_blank" class="website-link">
                      <i class="fas fa-external-link-alt"></i> Website
                    </a>
                  <?php else: ?>
                    <span class="text-muted">Không có</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($brand['logo']): ?>
                    <img src="../img/adminUP/brands/<?php echo $brand['logo']; ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>" class="thumbnail">
                  <?php else: ?>
                    <span class="no-image">No logo</span>
                  <?php endif; ?>
                </td>
                <td>
                    <span class="product-count">
                      <?php echo $brandModel->countProducts($brand['id']); ?>
                    </span>
                </td>
                <td>
                    <span class="status-badge <?php echo $brand['is_active'] ? 'status-published' : 'status-draft'; ?>">
                      <?php echo $brand['is_active'] ? 'Đang kích hoạt' : 'Đã ẩn'; ?>
                    </span>
                </td>
                <td><?php echo date('d/m/Y', strtotime($brand['created_at'])); ?></td>
                <td>
                  <div class="action-buttons">
                    <a href="admin.php?page=brands&action=edit&id=<?php echo $brand['id']; ?>"
                       class="btn-action btn-edit" title="Sửa">
                      <i class="fas fa-edit"></i>
                    </a>

                    <a href="admin.php?page=brands&action=toggle_status&id=<?php echo $brand['id']; ?>"
                       class="btn-action btn-status" title="Đổi trạng thái">
                      <i class="fas fa-power-off"></i>
                    </a>

                    <a href="admin.php?page=brands&action=delete&id=<?php echo $brand['id']; ?>"
                       class="btn-action btn-delete" title="Xóa"
                       onclick="return confirm('Xóa thương hiệu này?')">
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
  /* ====== BASE STYLES ====== */
  .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px;
  }

  /* ====== HEADER ====== */
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
    margin-top: 15px;
    opacity: 0.9;
    font-size: 16px;
    font-weight: 400;
  }

  /* ====== BUTTONS ====== */
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

  .btn-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff8a65 100%);
    color: #212529;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
  }

  .btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
  }

  .btn-sm {
    padding: 10px 20px;
    font-size: 14px;
  }

  .btn-lg {
    padding: 16px 32px;
    font-size: 16px;
  }

  /* ====== CARDS ====== */
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
  }

  /* ====== STATS CARDS ====== */
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
    flex-shrink: 0;
  }

  .stat-icon.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .stat-icon.success {
    background: linear-gradient(135deg, #0abb87 0%, #009ef7 100%);
  }

  .stat-icon.warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff8a65 100%);
  }

  .stat-icon.info {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
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

  /* ====== FORMS - VERTICAL LAYOUT ====== */
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

  .form-textarea {
    min-height: 120px;
    resize: vertical;
    font-family: inherit;
  }

  /* ====== CHECKBOX AND RADIO ====== */
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

  /* ====== SWITCH TOGGLE ====== */
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

  /* ====== FORM ACTIONS ====== */
  .form-actions {
    display: flex;
    gap: 15px;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #e4e6ef;
  }

  /* ====== TABLES ====== */
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

  /* ====== BADGES ====== */
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

  .badge-secondary {
    background: rgba(108, 117, 125, 0.1);
    color: #6c757d;
  }

  /* ====== STATUS BADGES ====== */
  .status-badge {
    padding: 8px 16px;
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

  /* ====== ACTION BUTTONS ====== */
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

  .btn-status {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
  }

  .btn-status:hover {
    background: #ffc107;
    color: #212529;
    transform: translateY(-2px);
  }

  /* ====== THUMBNAILS ====== */
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

  /* ====== BRAND INFO ====== */
  .brand-info {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .brand-details {
    flex: 1;
  }

  .brand-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 4px;
  }

  .brand-meta {
    color: #7f8c8d;
    font-size: 13px;
  }

  .website-link {
    color: #667eea;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    font-weight: 500;
    transition: color 0.3s ease;
  }

  .website-link:hover {
    color: #764ba2;
  }

  .product-count {
    background: #f5f7fa;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    color: #5e6278;
  }

  /* ====== EMPTY STATE ====== */
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
    font-size: 24px;
    font-weight: 600;
  }

  .empty-state p {
    max-width: 400px;
    margin: 0 auto 30px;
    font-size: 16px;
    line-height: 1.6;
  }

  /* ====== CURRENT IMAGE PREVIEW ====== */
  .current-image {
    margin-top: 20px;
    padding: 20px;
    background: #fafbfc;
    border-radius: 12px;
    border: 2px dashed #e4e6ef;
  }

  .current-image p {
    margin: 0 0 15px 0;
    font-size: 14px;
    color: #7f8c8d;
    font-weight: 500;
  }

  .image-preview {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .image-preview img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #e4e6ef;
  }

  .image-info {
    flex: 1;
  }

  .image-info small {
    display: block;
    color: #7f8c8d;
    margin-top: 5px;
    font-size: 12px;
  }

  /* ====== ALERTS ====== */
  .alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    border: 1px solid transparent;
  }

  .alert-success {
    background: rgba(10, 187, 135, 0.1);
    border-color: rgba(10, 187, 135, 0.2);
    color: #0abb87;
  }

  .alert-danger {
    background: rgba(241, 65, 108, 0.1);
    border-color: rgba(241, 65, 108, 0.2);
    color: #f1416c;
  }

  .alert-warning {
    background: rgba(255, 193, 7, 0.1);
    border-color: rgba(255, 193, 7, 0.2);
    color: #ffc107;
  }

  .alert-info {
    background: rgba(23, 162, 184, 0.1);
    border-color: rgba(23, 162, 184, 0.2);
    color: #17a2b8;
  }

  .alert i {
    font-size: 18px;
    flex-shrink: 0;
  }

  /* ====== PAGINATION ====== */
  .pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 30px;
  }

  .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #f5f7fa;
    color: #5e6278;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .page-link:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
  }

  .page-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  .page-link.disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  /* ====== UTILITY CLASSES ====== */
  .text-center { text-align: center; }
  .text-right { text-align: right; }
  .text-left { text-align: left; }
  .text-muted { color: #7f8c8d; }
  .text-primary { color: #667eea; }
  .text-success { color: #0abb87; }
  .text-danger { color: #f1416c; }
  .text-warning { color: #ffc107; }
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
  .d-flex { display: flex; }
  .align-items-center { align-items: center; }
  .justify-content-between { justify-content: space-between; }
  .gap-10 { gap: 10px; }
  .gap-20 { gap: 20px; }

  /* ====== RESPONSIVE ====== */
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
      flex-wrap: wrap;
    }

    .form-row {
      grid-template-columns: 1fr;
    }

    .brand-info {
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
    }

    .image-preview {
      flex-direction: column;
      align-items: flex-start;
    }
  }

  /* ====== ANIMATIONS ====== */
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  @keyframes slideIn {
    from { transform: translateX(-20px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }

  .fade-in {
    animation: fadeIn 0.5s ease;
  }

  .slide-in {
    animation: slideIn 0.4s ease;
  }

  /* ====== LOADING SKELETON ====== */
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
</style>
