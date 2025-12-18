<?php
// admin/attributes.php
require_once 'class/product_attribute.php';
require_once 'class/attribute_value.php';

$attributeModel = new ProductAttribute();
$valueModel = new AttributeValue();

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$currentAttributeId = isset($_GET['attribute_id']) ? (int)$_GET['attribute_id'] : 0;

// Hiển thị thông báo nếu có
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$messages = [
  'attribute_added' => 'Thêm thuộc tính thành công!',
  'attribute_updated' => 'Cập nhật thuộc tính thành công!',
  'attribute_deleted' => 'Xóa thuộc tính thành công!',
  'value_added' => 'Thêm giá trị thành công!',
  'value_updated' => 'Cập nhật giá trị thành công!',
  'value_deleted' => 'Xóa giá trị thành công!',
  'visibility_toggled' => 'Thay đổi trạng thái thành công!'
];

if ($msg && isset($messages[$msg])) {
  echo '<div class="alert alert-success" style="margin: 20px; padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
          <i class="fas fa-check-circle"></i> ' . $messages[$msg] . '
        </div>';
}

// Xử lý actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sửa các điều kiện ở đây
  if (isset($_POST['action']) && $_POST['action'] === 'add_attribute') {
    $data = [
      'name' => trim($_POST['name']),
      'slug' => trim($_POST['slug']),
      'type' => $_POST['type'],
      'is_visible' => isset($_POST['is_visible']) ? 1 : 0,
      'sort_order' => (int)$_POST['sort_order']
    ];

    if (empty($data['slug'])) {
      $data['slug'] = $attributeModel->generateSlug($data['name']);
    }

    if ($attributeModel->create($data)) {
      header("Location: admin.php?page=attributes&msg=attribute_added");
      exit();
    } else {
      $error = "Có lỗi xảy ra khi thêm thuộc tính!";
    }
  }
  elseif (isset($_POST['action']) && $_POST['action'] === 'edit_attribute' && $id) {
    $data = [
      'name' => trim($_POST['name']),
      'slug' => trim($_POST['slug']),
      'type' => $_POST['type'],
      'is_visible' => isset($_POST['is_visible']) ? 1 : 0,
      'sort_order' => (int)$_POST['sort_order']
    ];

    if ($attributeModel->update($id, $data)) {
      header("Location: admin.php?page=attributes&msg=attribute_updated");
      exit();
    } else {
      $error = "Có lỗi xảy ra khi cập nhật!";
    }
  }
  elseif (isset($_POST['action']) && $_POST['action'] === 'add_value' && $currentAttributeId) {
    $data = [
      'attribute_id' => $currentAttributeId,
      'value' => trim($_POST['value']),
      'color_code' => !empty($_POST['color_code']) ? $_POST['color_code'] : null,
      'sort_order' => (int)$_POST['sort_order']
    ];

    if ($valueModel->create($data)) {
      header("Location: admin.php?page=attributes&attribute_id=" . $currentAttributeId . "&msg=value_added");
      exit();
    }
  }
  elseif (isset($_POST['action']) && $_POST['action'] === 'edit_value' && isset($_POST['value_id'])) {
    $valueId = (int)$_POST['value_id'];
    $data = [
      'value' => trim($_POST['value']),
      'color_code' => !empty($_POST['color_code']) ? $_POST['color_code'] : null,
      'sort_order' => (int)$_POST['sort_order']
    ];

    if ($valueModel->update($valueId, $data)) {
      header("Location: admin.php?page=attributes&attribute_id=" . $currentAttributeId . "&msg=value_updated");
      exit();
    }
  }
}

// Thêm phần xử lý GET actions
if ($action === 'delete_attribute' && $id) {
  $attributeModel->delete($id);
  header("Location: admin.php?page=attributes&msg=attribute_deleted");
  exit();
}

if ($action === 'toggle_visibility' && $id) {
  $attribute = $attributeModel->findById($id);
  $newVisibility = $attribute['is_visible'] ? 0 : 1;
  $attributeModel->update($id, ['is_visible' => $newVisibility]);
  header("Location: admin.php?page=attributes&msg=visibility_toggled");
  exit();
}

if ($action === 'delete_value' && $id) {
  $valueModel->delete($id);
  header("Location: admin.php?page=attributes&attribute_id=" . $currentAttributeId . "&msg=value_deleted");
  exit();
}

$attributes = $attributeModel->getAll(false);
$currentAttribute = $currentAttributeId ? $attributeModel->findById($currentAttributeId) : null;
$attributeValues = $currentAttributeId ? $valueModel->getByAttribute($currentAttributeId) : [];
?>

<div class="container">
  <div class="header">
    <div class="header-content">
      <h1><i class="fas fa-tags"></i> Quản lý Thuộc tính</h1>
      <a href="admin.php?page=attributes&action=add" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm thuộc tính
      </a>
    </div>
  </div>

  <?php if (in_array($action, ['add', 'edit'])): ?>
    <!-- Form thêm/sửa thuộc tính -->
    <div class="card">
      <div class="card-header">
        <h2><?php echo $action === 'add' ? 'Thêm thuộc tính mới' : 'Sửa thuộc tính'; ?></h2>
      </div>
      <div class="card-body">
        <?php
        $attribute = [];
        if ($action === 'edit' && $id) {
          $attribute = $attributeModel->findById($id);
        }
        ?>

        <form method="POST" class="form">
          <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'add_attribute' : 'edit_attribute'; ?>">

          <?php if ($action === 'edit' && $id): ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
          <?php endif; ?>

          <div class="form-row">
            <div class="form-group">
              <label for="name">Tên thuộc tính *</label>
              <input type="text" id="name" name="name"
                     value="<?php echo htmlspecialchars(isset($attribute['name']) ? $attribute['name'] : ''); ?>"
                     required>
            </div>
            <div class="form-group">
              <label for="slug">Slug</label>
              <input type="text" id="slug" name="slug"
                     value="<?php echo htmlspecialchars(isset($attribute['slug']) ? $attribute['slug'] : ''); ?>">
              <small>Tự động tạo nếu để trống</small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="type">Loại thuộc tính</label>
              <select id="type" name="type">
                <option value="select" <?php echo (isset($attribute['type']) && $attribute['type'] == 'select') ? 'selected' : ''; ?>>Select (Dropdown)</option>
                <option value="color" <?php echo (isset($attribute['type']) && $attribute['type'] == 'color') ? 'selected' : ''; ?>>Color (Màu sắc)</option>
                <option value="text" <?php echo (isset($attribute['type']) && $attribute['type'] == 'text') ? 'selected' : ''; ?>>Text (Nhập liệu)</option>
              </select>
            </div>
            <div class="form-group">
              <label for="sort_order">Thứ tự</label>
              <input type="number" id="sort_order" name="sort_order"
                     value="<?php echo isset($attribute['sort_order']) ? $attribute['sort_order'] : 0; ?>" min="0">
            </div>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" name="is_visible"
                <?php echo !isset($attribute['is_visible']) || $attribute['is_visible'] ? 'checked' : ''; ?>>
              <span class="checkmark"></span>
              Hiển thị
            </label>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i>
              <?php echo $action === 'add' ? 'Thêm thuộc tính' : 'Cập nhật'; ?>
            </button>
            <a href="admin.php?page=attributes" class="btn btn-secondary">Hủy bỏ</a>
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
          <h3>Tổng thuộc tính</h3>
          <span class="stat-number"><?php echo count($attributes); ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-eye"></i>
        </div>
        <div class="stat-info">
          <h3>Đang hiển thị</h3>
          <span class="stat-number">
                        <?php echo count(array_filter($attributes, function ($a) {
                          return $a['is_visible'];
                        })); ?>
                    </span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-palette"></i>
        </div>
        <div class="stat-info">
          <h3>Thuộc tính màu</h3>
          <span class="stat-number">
                        <?php echo count(array_filter($attributes, function ($a) {
                          return $a['type'] == 'color';
                        })); ?>
                    </span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-list"></i>
        </div>
        <div class="stat-info">
          <h3>Thuộc tính select</h3>
          <span class="stat-number">
                        <?php echo count(array_filter($attributes, function ($a) {
                          return $a['type'] == 'select';
                        })); ?>
                    </span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h2>Danh sách thuộc tính</h2>
      </div>
      <div class="card-body">
        <?php if ($currentAttributeId && $currentAttribute): ?>
          <!-- Quản lý giá trị của thuộc tính -->
          <div class="attribute-values-management">
            <div class="values-header">
              <h3>Quản lý giá trị: <?php echo htmlspecialchars($currentAttribute['name']); ?></h3>
              <a href="admin.php?page=attributes" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
              </a>
            </div>

            <!-- Form thêm giá trị -->
            <!-- Form thêm giá trị -->
            <div class="card" style="margin-top: 20px;">
              <div class="card-header">
                <h4>Thêm giá trị mới</h4>
              </div>
              <div class="card-body">
                <form method="POST" class="form" id="addValueForm">
                  <input type="hidden" name="action" value="add_value">
                  <div class="form-row">
                    <div class="form-group">
                      <label for="value">Giá trị *</label>
                      <input type="text" id="value" name="value" required>
                    </div>
                    <?php if ($currentAttribute['type'] == 'color'): ?>
                      <div class="form-group">
                        <label for="color_code">Mã màu</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                          <input type="color" id="color_code" name="color_code" value="#000000" style="width: 40px; height: 40px;">
                          <input type="text" id="color_code_text" name="color_code_text" value="#000000" style="flex: 1;">
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="sort_order">Thứ tự</label>
                      <input type="number" id="sort_order" name="sort_order" value="0" min="0">
                    </div>
                    <div class="form-group">
                      <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm giá trị
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- Danh sách giá trị -->
            <div class="card" style="margin-top: 20px;">
              <div class="card-header">
                <h4>Giá trị hiện có (<?php echo count($attributeValues); ?>)</h4>
              </div>
              <div class="card-body">
                <?php if ($attributeValues): ?>
                  <div class="table-responsive">
                    <table class="table">
                      <thead>
                      <tr>
                        <th width="50">ID</th>
                        <th>Giá trị</th>
                        <?php if ($currentAttribute['type'] == 'color'): ?>
                          <th width="100">Mã màu</th>
                        <?php endif; ?>
                        <th width="80">Thứ tự</th>
                        <th width="100">Thao tác</th>
                      </tr>
                      </thead>
                      <tbody>
                      <?php foreach ($attributeValues as $value): ?>
                        <tr id="value-<?php echo $value['id']; ?>">
                          <td><?php echo $value['id']; ?></td>
                          <td class="view-mode"><?php echo htmlspecialchars($value['value']); ?></td>
                          <?php if ($currentAttribute['type'] == 'color'): ?>
                            <td class="view-mode">
                              <?php if ($value['color_code']): ?>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                  <span class="color-dot" style="background: <?php echo $value['color_code']; ?>; width: 20px; height: 20px; border-radius: 4px;"></span>
                                  <?php echo $value['color_code']; ?>
                                </div>
                              <?php endif; ?>
                            </td>
                          <?php endif; ?>
                          <td class="view-mode"><?php echo $value['sort_order']; ?></td>
                          <td class="view-mode">
                            <div class="action-buttons">
                              <button type="button" class="btn-action btn-edit" title="Sửa"
                                      onclick="toggleEditValue(<?php echo $value['id']; ?>)">
                                <i class="fas fa-edit"></i>
                              </button>
<!--                              <form method="POST" onsubmit="return confirm('Xóa giá trị này?')" style="display: inline;">-->
<!--                                <input type="hidden" name="action" value="delete_value">-->
<!--                                <input type="hidden" name="value_id" value="--><?php //echo $value['id']; ?><!--">-->
<!--                                <button type="submit" class="btn-action btn-delete" title="Xóa">-->
<!--                                  <i class="fas fa-trash"></i>-->
<!--                                </button>-->
<!--                              </form>-->

                              <a href="admin.php?page=attributes&action=delete_value&id=<?php echo $value['id']; ?>&attribute_id=<?php echo $currentAttributeId; ?>"
                                 class="btn-action btn-delete" title="Xóa"
                                 onclick="return confirm('Xóa giá trị này?')">
                                <i class="fas fa-trash"></i>
                              </a>
                            </div>
                          </td>

                          <!-- Form sửa giá trị (ẩn) -->
                          <td colspan="<?php echo $currentAttribute['type'] == 'color' ? '5' : '4'; ?>" class="edit-mode" style="display: none;">
                            <form method="POST" class="edit-value-form">
                              <input type="hidden" name="action" value="edit_value">
                              <input type="hidden" name="value_id" value="<?php echo $value['id']; ?>">
                              <div class="form-row">
                                <div class="form-group">
                                  <input type="text" name="value" value="<?php echo htmlspecialchars($value['value']); ?>" required class="form-control-sm">
                                </div>
                                <?php if ($currentAttribute['type'] == 'color'): ?>
                                  <div class="form-group">
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                      <input type="color" name="color_code" value="<?php echo $value['color_code'] ?: '#000000'; ?>" style="width: 40px; height: 40px;">
                                      <input type="text" name="color_code_text" value="<?php echo $value['color_code'] ?: '#000000'; ?>" class="form-control-sm">
                                    </div>
                                  </div>
                                <?php endif; ?>
                                <div class="form-group">
                                  <input type="number" name="sort_order" value="<?php echo $value['sort_order']; ?>" min="0" class="form-control-sm">
                                </div>
                                <div class="form-group">
                                  <div class="action-buttons">
                                    <button type="submit" class="btn-action btn-success">
                                      <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn-action btn-secondary"
                                            onclick="toggleEditValue(<?php echo $value['id']; ?>)">
                                      <i class="fas fa-times"></i>
                                    </button>
                                  </div>
                                </div>
                              </div>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <p class="no-data" style="text-align: center; color: #6c757d; padding: 20px;">Chưa có giá trị nào</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php else: ?>
          <!-- Danh sách thuộc tính -->
          <div class="table-responsive">
            <table class="table">
              <thead>
              <tr>
                <th width="50px">ID</th>
                <th>Tên thuộc tính</th>
                <th width="100px">Slug</th>
                <th width="100px">Loại</th>
                <th width="80px">Thứ tự</th>
                <th width="100px">Số giá trị</th>
                <th width="100px">Trạng thái</th>
                <th width="120px">Thao tác</th>
              </tr>
              </thead>
              <tbody>
              <?php foreach ($attributes as $attr): ?>
                <tr>
                  <td><?php echo $attr['id']; ?></td>
                  <td>
                    <div class="attribute-name">
                      <?php echo htmlspecialchars($attr['name']); ?>
                    </div>
                  </td>
                  <td><code><?php echo htmlspecialchars($attr['slug']); ?></code></td>
                  <td>
                                            <span class="type-badge <?php echo $attr['type']; ?>">
                                                <?php
                                                $typeText = [
                                                  'select' => 'Select',
                                                  'color' => 'Màu sắc',
                                                  'text' => 'Text'
                                                ];
                                                echo isset($typeText[$attr['type']]) ? $typeText[$attr['type']] : $attr['type'];
                                                ?>
                                            </span>
                  </td>
                  <td><?php echo $attr['sort_order']; ?></td>
                  <td>
                                            <span class="value-count">
                                                <?php
                                                $values = $valueModel->getByAttribute($attr['id']);
                                                echo count($values);
                                                ?>
                                            </span>
                  </td>
                  <td>
                                            <span class="status-badge <?php echo $attr['is_visible'] ? 'status-published' : 'status-draft'; ?>">
                                                <?php echo $attr['is_visible'] ? 'Hiển thị' : 'Ẩn'; ?>
                                            </span>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <a href="admin.php?page=attributes&attribute_id=<?php echo $attr['id']; ?>"
                         class="btn-action btn-view" title="Quản lý giá trị">
                        <i class="fas fa-list"></i>
                      </a>

                      <a href="admin.php?page=attributes&action=edit&id=<?php echo $attr['id']; ?>"
                         class="btn-action btn-edit" title="Sửa">
                        <i class="fas fa-edit"></i>
                      </a>

                      <a href="admin.php?page=attributes&action=toggle_visibility&id=<?php echo $attr['id']; ?>"
                         class="btn-action btn-status" title="Đổi trạng thái">
                        <i class="fas fa-eye"></i>
                      </a>

                      <!-- Thay thế form xóa bằng link -->
                      <a href="admin.php?page=attributes&action=delete_attribute&id=<?php echo $attr['id']; ?>"
                         class="btn-action btn-delete" title="Xóa"
                         onclick="return confirm('Xóa thuộc tính này?')">
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

  <?php endif; ?>
</div>

<style>
  /* ====== ATTRIBUTE VALUES MANAGEMENT ====== */
  .attribute-values-management .values-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e4e6ef;
  }

  .attribute-values-management .values-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .attribute-name {
    font-weight: 600;
    color: #667eea;
    background: rgba(102, 126, 234, 0.1);
    padding: 6px 12px;
    border-radius: 8px;
    display: inline-block;
  }

  /* ====== TYPE BADGES ====== */
  .type-badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .type-badge.select {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    border: 1px solid rgba(102, 126, 234, 0.2);
  }

  .type-badge.color {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
  }

  .type-badge.text {
    background: rgba(10, 187, 135, 0.1);
    color: #0abb87;
    border: 1px solid rgba(10, 187, 135, 0.2);
  }

  .value-count {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  /* ====== COLOR DISPLAY ====== */
  .color-dot {
    display: inline-block;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    border: 2px solid #e4e6ef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
  }

  .color-dot:hover {
    transform: scale(1.2);
  }

  /* ====== BUTTON SUCCESS ====== */
  .btn-success {
    background: linear-gradient(135deg, #0abb87 0%, #009ef7 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(10, 187, 135, 0.3);
  }

  .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(10, 187, 135, 0.4);
    background: linear-gradient(135deg, #09a876 0%, #008ee6 100%);
  }

  /* ====== EDIT VALUE FORM ====== */
  .edit-value-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr 100px 120px;
    gap: 15px;
    margin-bottom: 0;
    align-items: end;
  }

  .edit-mode {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    border: 2px solid rgba(102, 126, 234, 0.2);
    border-radius: 12px;
    padding: 20px;
    margin: 10px 0;
  }

  /* ====== NO DATA STATE ====== */
  .no-data {
    text-align: center;
    color: #a1a5b7;
    padding: 60px 20px;
    font-style: normal;
    background: #fafbfc;
    border-radius: 12px;
    border: 2px dashed #e4e6ef;
    margin: 20px 0;
  }

  .no-data i {
    font-size: 48px;
    margin-bottom: 20px;
    opacity: 0.5;
  }

  .no-data h4 {
    margin: 0 0 10px 0;
    color: #5e6278;
    font-weight: 600;
  }

  .no-data p {
    margin: 0;
    max-width: 400px;
    margin: 0 auto 20px;
    line-height: 1.6;
  }

  /* ====== PRODUCT INFO ====== */
  .product-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .badge.featured {
    background: linear-gradient(135deg, #ffc107 0%, #ff8a65 100%);
    color: #212529;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 5px;
  }

  /* ====== PRICE INFO ====== */
  .price-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .sale-price {
    color: #ef4444;
    font-weight: 700;
    font-size: 16px;
  }

  .regular-price {
    color: #a1a5b7;
    text-decoration: line-through;
    font-size: 13px;
  }

  /* ====== STOCK INFO ====== */
  .stock-info {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .stock-info.in-stock {
    background: rgba(10, 187, 135, 0.1);
    color: #0abb87;
    border: 1px solid rgba(10, 187, 135, 0.2);
  }

  .stock-info.out-of-stock {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
  }

  /* ====== FEATURED BUTTON ====== */
  .btn-featured {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
  }

  .btn-featured:hover {
    background: #ffc107;
    color: #212529;
    transform: translateY(-2px);
  }

  /* ====== MAIN LAYOUT (giữ nguyên) ====== */
  .container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px;
  }

  .header {
    background: white;
    border-radius: 16px;
    padding: 25px 30px;
    margin-bottom: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e4e6ef;
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
    gap: 15px;
    font-size: 28px;
    font-weight: 700;
  }

  /* ====== CARDS ====== */
  .card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 25px;
    overflow: hidden;
    border: 1px solid #e4e6ef;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
  }

  .card-header {
    background: #f5f7fa;
    padding: 20px 25px;
    border-bottom: 1px solid #e4e6ef;
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
    padding: 25px;
  }

  /* ====== BUTTONS ====== */
  .btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
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

  /* ====== STATS ====== */
  .stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 25px;
  }

  .stat-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 20px;
    border: 1px solid #e4e6ef;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  }

  .stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .stat-icon i {
    font-size: 28px;
    color: white;
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

  /* ====== FORMS ====== */
  .form {
    max-width: 1000px;
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin-bottom: 25px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 20px;
  }

  .form-group label {
    font-weight: 600;
    margin-bottom: 10px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
  }

  .form-group input,
  .form-group select,
  .form-group textarea {
    padding: 14px 16px;
    border: 2px solid #e4e6ef;
    border-radius: 10px;
    font-size: 15px;
    transition: all 0.3s ease;
    background: #fafbfc;
  }

  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus {
    border-color: #667eea;
    background: white;
    outline: none;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
  }

  .form-group small {
    color: #7f8c8d;
    font-size: 13px;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .checkbox-group {
    margin: 20px 0;
  }

  .checkbox-label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    padding: 10px;
    border-radius: 10px;
    transition: background 0.3s ease;
  }

  .checkbox-label:hover {
    background: #f5f7fa;
  }

  .form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 25px;
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
    padding: 16px 20px;
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

  .text-muted {
    color: #7f8c8d;
    font-size: 13px;
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

  .btn-status {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
  }

  .btn-status:hover {
    background: #ffc107;
    color: #212529;
    transform: translateY(-2px);
  }

  .btn-delete {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
  }

  .btn-delete:hover {
    background: #ef4444;
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

    .edit-value-form .form-row {
      grid-template-columns: 1fr;
      gap: 15px;
    }

    .attribute-values-management .values-header {
      flex-direction: column;
      gap: 15px;
      text-align: center;
    }
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

    // Color picker sync
    const colorInput = document.getElementById('color_code');
    const colorTextInput = document.getElementById('color_code_text');

    if (colorInput && colorTextInput) {
      colorInput.addEventListener('input', function() {
        colorTextInput.value = this.value;
      });

      colorTextInput.addEventListener('input', function() {
        if (this.value.match(/^#[0-9A-F]{6}$/i)) {
          colorInput.value = this.value;
        }
      });
    }
  });

  // Toggle edit form cho giá trị
  function toggleEditValue(valueId) {
    const row = document.getElementById('value-' + valueId);
    const viewCells = row.querySelectorAll('.view-mode');
    const editCell = row.querySelector('.edit-mode');

    if (editCell.style.display === 'none') {
      viewCells.forEach(cell => cell.style.display = 'none');
      editCell.style.display = 'table-cell';

      // Set số cột cho colspan
      const colspan = editCell.parentElement.querySelectorAll('.view-mode').length;
      editCell.colSpan = colspan;
    } else {
      viewCells.forEach(cell => cell.style.display = 'table-cell');
      editCell.style.display = 'none';
    }
  }
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Xử lý đồng bộ input màu
    const colorInput = document.getElementById('color_code');
    const colorText = document.getElementById('color_code_text');

    if (colorInput && colorText) {
      colorInput.addEventListener('input', function() {
        colorText.value = this.value;
      });

      colorText.addEventListener('input', function() {
        if (this.value.match(/^#[0-9A-F]{6}$/i)) {
          colorInput.value = this.value;
        }
      });
    }

    // Toggle edit mode cho giá trị
    window.toggleEditValue = function(valueId) {
      const row = document.getElementById('value-' + valueId);
      if (row) {
        const viewCells = row.querySelectorAll('.view-mode');
        const editCell = row.querySelector('.edit-mode');

        if (editCell.style.display === 'none' || editCell.style.display === '') {
          // Switch to edit mode
          viewCells.forEach(cell => cell.style.display = 'none');
          editCell.style.display = 'table-cell';

          // Update color picker in edit mode
          const editColorInput = editCell.querySelector('input[type="color"]');
          const editColorText = editCell.querySelector('input[name="color_code_text"]');
          if (editColorInput && editColorText) {
            editColorInput.addEventListener('input', function() {
              editColorText.value = this.value;
            });
            editColorText.addEventListener('input', function() {
              if (this.value.match(/^#[0-9A-F]{6}$/i)) {
                editColorInput.value = this.value;
              }
            });
          }
        } else {
          // Switch to view mode
          viewCells.forEach(cell => cell.style.display = '');
          editCell.style.display = 'none';
        }
      }
    };

    // Tự động ẩn thông báo sau 5 giây
    setTimeout(function() {
      const alerts = document.querySelectorAll('.alert');
      alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
      });
    }, 5000);
  });
</script>
