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

  if ($action === 'add') {
    if (empty($data['slug'])) {
      $data['slug'] = $brandModel->generateSlug($data['name']);
    }
    $brandModel->create($data);
    echo '<script>alert("Thêm thương hiệu thành công!"); window.location.href="admin.php?page=brands";</script>';
  } elseif ($action === 'edit' && $id) {
    $brandModel->update($id, $data);
    echo '<script>alert("Cập nhật thương hiệu thành công!"); window.location.href="admin.php?page=brands";</script>';
  }
}

if ($action === 'delete' && $id) {
  $brandModel->delete($id);
  echo '<script>alert("Xóa thương hiệu thành công!"); window.location.href="admin.php?page=brands";</script>';
}

if ($action === 'toggle_status' && $id) {
  $brand = $brandModel->findById($id);
  $newStatus = $brand['is_active'] ? 0 : 1;
  $brandModel->update($id, ['is_active' => $newStatus]);
  header("Location: admin.php?page=brands");
  exit();
}

$brands = $brandModel->getAll(false);
?>

<div class="brands-header">
  <div class="header-actions">
    <h2>Quản lý Thương hiệu</h2>
    <a href="admin.php?page=brands&action=add" class="btn btn-primary">
      <i class="fas fa-plus"></i> Thêm thương hiệu
    </a>
  </div>
</div>

<?php if (in_array($action, ['add', 'edit'])): ?>
  <!-- Form thêm/sửa thương hiệu -->
  <div class="form-container">
    <div class="form-card">
      <h3><?php echo $action === 'add' ? 'Thêm thương hiệu mới' : 'Sửa thương hiệu'; ?></h3>

      <?php
      $brand = [];
      if ($action === 'edit' && $id) {
        $brand = $brandModel->findById($id);
      }
      ?>

      <form method="POST" class="brand-form" enctype="multipart/form-data">
        <div class="form-row">
          <div class="form-group">
            <label for="name">Tên thương hiệu *</label>
            <input type="text" id="name" name="name"
                   value="<?php echo htmlspecialchars(isset($brand['name']) ? $brand['name'] : ''); ?>"
                   required>
          </div>
          <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug"
                   value="<?php echo htmlspecialchars(isset($brand['slug']) ? $brand['slug'] : ''); ?>">
            <small>Tự động tạo nếu để trống</small>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="website">Website</label>
            <input type="url" id="website" name="website"
                   value="<?php echo htmlspecialchars(isset($brand['website']) ? $brand['website'] : ''); ?>">
          </div>
          <div class="form-group">
            <label for="logo">Logo</label>
            <input type="file" id="logo" name="logo" accept="image/*">
            <?php if (!empty($brand['logo'])): ?>
              <div class="current-logo">
                <img src="<?php echo $brand['logo']; ?>" alt="Current logo" style="max-width: 100px; margin-top: 5px;">
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="form-group">
          <label for="description">Mô tả</label>
          <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars(isset($brand['description']) ? $brand['description'] : ''); ?></textarea>
        </div>

        <div class="form-group checkbox-group">
          <label class="checkbox-label">
            <input type="checkbox" name="is_active"
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

  <div class="stats-cards">
    <div class="stat-card">
      <div class="stat-icon" style="background: #e3f2fd;">
        <i class="fas fa-tags" style="color: #1976d2;"></i>
      </div>
      <div class="stat-info">
        <h3>Tổng thương hiệu</h3>
        <span class="stat-number"><?php echo count($brands); ?></span>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon" style="background: #e8f5e8;">
        <i class="fas fa-check-circle" style="color: #388e3c;"></i>
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
      <div class="stat-icon" style="background: #fff3e0;">
        <i class="fas fa-star" style="color: #f57c00;"></i>
      </div>
      <div class="stat-info">
        <h3>Có logo</h3>
        <span class="stat-number">
                <?php echo count(array_filter($brands, function ($b) {
                  return !empty($b['logo']);
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
          <th width="60px">Logo</th>
          <th>Tên thương hiệu</th>
          <th width="150px">Slug</th>
          <th width="150px">Website</th>
          <th width="100px">Số sản phẩm</th>
          <th width="100px">Trạng thái</th>
          <th width="120px">Ngày tạo</th>
          <th width="100px">Thao tác</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($brands as $brand): ?>
          <tr>
            <td>
              <?php if (!empty($brand['logo'])): ?>
                <img src="<?php echo $brand['logo']; ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>"
                     class="brand-logo">
              <?php else: ?>
                <div class="no-logo">
                  <i class="fas fa-image"></i>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <div class="brand-name">
                <?php echo htmlspecialchars($brand['name']); ?>
              </div>
            </td>
            <td><code><?php echo htmlspecialchars($brand['slug']); ?></code></td>
            <td>
              <?php if (!empty($brand['website'])): ?>
                <a href="<?php echo htmlspecialchars($brand['website']); ?>" target="_blank" class="website-link">
                  <i class="fas fa-external-link-alt"></i>
                  Website
                </a>
              <?php else: ?>
                <span class="text-muted">N/A</span>
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

<?php endif; ?>

<style>
  .brand-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 4px;
    border: 1px solid #ddd;
    padding: 2px;
  }

  .no-logo {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border: 1px dashed #dee2e6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
  }

  .brand-name {
    font-weight: 500;
  }

  .website-link {
    color: #3498db;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
  }

  .website-link:hover {
    color: #2980b9;
  }

  .current-logo {
    margin-top: 5px;
  }

  .current-logo img {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 2px;
  }
</style>

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

    // Preview logo image
    const logoInput = document.getElementById('logo');
    if (logoInput) {
      logoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            // Hiển thị preview ảnh mới
            const preview = document.createElement('div');
            preview.className = 'current-logo';
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 100px; margin-top: 5px;">`;

            const existingPreview = document.querySelector('.current-logo');
            if (existingPreview) {
              existingPreview.remove();
            }

            logoInput.parentNode.appendChild(preview);
          }
          reader.readAsDataURL(file);
        }
      });
    }
  });
</script>
