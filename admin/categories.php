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
    $categoryModel->create($data);
    echo '<script>alert("Thêm danh mục thành công!"); window.location.href="admin.php?page=categories";</script>';
  } elseif ($action === 'edit' && $id) {
    $categoryModel->update($id, $data);
    echo '<script>alert("Cập nhật danh mục thành công!"); window.location.href="admin.php?page=categories";</script>';
  }
}

if ($action === 'delete' && $id) {
  $categoryModel->delete($id);
  echo '<script>alert("Xóa danh mục thành công!"); window.location.href="admin.php?page=categories";</script>';
}

if ($action === 'toggle_status' && $id) {
  $category = $categoryModel->findById($id);
  $newStatus = $category['is_active'] ? 0 : 1;
  $categoryModel->update($id, ['is_active' => $newStatus]);
  header("Location: admin.php?page=categories");
  exit();
}

$categories = $categoryModel->getAll(false);
$hierarchicalCategories = $categoryModel->getHierarchical();
?>

<div class="categories-header">
  <div class="header-actions">
    <h2>Quản lý Danh mục</h2>
    <a href="admin.php?page=categories&action=add" class="btn btn-primary">
      <i class="fas fa-plus"></i> Thêm danh mục
    </a>
  </div>
</div>

<?php if (in_array($action, ['add', 'edit'])): ?>
  <!-- Form thêm/sửa danh mục -->
  <div class="form-container">
    <div class="form-card">
      <h3><?php echo $action === 'add' ? 'Thêm danh mục mới' : 'Sửa danh mục'; ?></h3>

      <?php
      $category = [];
      if ($action === 'edit' && $id) {
        $category = $categoryModel->findById($id);
      }
      ?>

      <form method="POST" class="category-form">
        <div class="form-row">
          <div class="form-group">
            <label for="name">Tên danh mục *</label>
            <input type="text" id="name" name="name"
                   value="<?php echo htmlspecialchars(isset($category['name']) ? $category['name'] : ''); ?>"
                   required>
          </div>
          <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug"
                   value="<?php echo htmlspecialchars(isset($category['slug']) ? $category['slug'] : ''); ?>">
            <small>Tự động tạo nếu để trống</small>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="parent_id">Danh mục cha</label>
            <select id="parent_id" name="parent_id">
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
            <input type="number" id="sort_order" name="sort_order"
                   value="<?php echo isset($category['sort_order']) ? $category['sort_order'] : 0; ?>" min="0">
          </div>
        </div>

        <div class="form-group">
          <label for="description">Mô tả</label>
          <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars(isset($category['description']) ? $category['description'] : ''); ?></textarea>
        </div>

        <div class="form-group checkbox-group">
          <label class="checkbox-label">
            <input type="checkbox" name="is_active"
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

  <div class="stats-cards">
    <div class="stat-card">
      <div class="stat-icon" style="background: #e3f2fd;">
        <i class="fas fa-folder" style="color: #1976d2;"></i>
      </div>
      <div class="stat-info">
        <h3>Tổng danh mục</h3>
        <span class="stat-number"><?php echo count($categories); ?></span>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon" style="background: #e8f5e8;">
        <i class="fas fa-check-circle" style="color: #388e3c;"></i>
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
      <div class="stat-icon" style="background: #fff3e0;">
        <i class="fas fa-sitemap" style="color: #f57c00;"></i>
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

  <div class="data-table-container">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
        <tr>
          <th width="50px">ID</th>
          <th>Tên danh mục</th>
          <th width="150px">Slug</th>
          <th width="120px">Danh mục cha</th>
          <th width="80px">Thứ tự</th>
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

<?php endif; ?>

<style>
  .category-name {
    font-weight: 500;
  }

  .text-muted {
    color: #6c757d;
    font-size: 12px;
  }

  .badge.primary {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
  }

  .product-count {
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 500;
  }

  .form-container {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }

  .form-card h3 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
  }

  .category-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
  }

  .category-form .form-group {
    display: flex;
    flex-direction: column;
  }

  .category-form label {
    font-weight: 500;
    margin-bottom: 5px;
    color: #2c3e50;
  }

  .category-form input,
  .category-form select,
  .category-form textarea {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
  }

  .category-form input:focus,
  .category-form select:focus,
  .category-form textarea:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
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

  .btn-secondary {
    background: #6c757d;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }

  .btn-secondary:hover {
    background: #5a6268;
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
  });
</script>
