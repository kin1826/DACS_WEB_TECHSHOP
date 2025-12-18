<?php
// admin/settings.php

// Xử lý upload/xóa slide
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Xử lý upload slide mới
  if (isset($_FILES['new_slide']) && $_FILES['new_slide']['error'] === 0) {
    $uploadDir = 'img/slideintro/';

    // Tạo thư mục nếu chưa tồn tại
    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['new_slide']['name']);
    $fileTmp = $_FILES['new_slide']['tmp_name'];
    $fileSize = $_FILES['new_slide']['size'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Kiểm tra định dạng file
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($fileType, $allowedTypes)) {
      $error_message = "Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP)";
    }
    // Kiểm tra kích thước (max 5MB)
    elseif ($fileSize > 5 * 1024 * 1024) {
      $error_message = "File quá lớn. Tối đa 5MB";
    } else {
      // Tạo tên file mới để tránh trùng
      $newFileName = time() . '_' . uniqid() . '.' . $fileType;
      $uploadPath = $uploadDir . $newFileName;

      if (move_uploaded_file($fileTmp, $uploadPath)) {
        $success_message = "Upload slide thành công!";
      } else {
        $error_message = "Upload thất bại. Vui lòng thử lại";
      }
    }
  }

  // Xử lý xóa slide
  if (isset($_POST['delete_slide'])) {
    $slideToDelete = $_POST['slide_name'] ?? '';
    if ($slideToDelete) {
      $filePath = 'img/slideintro/' . $slideToDelete;

      if (file_exists($filePath) && is_file($filePath)) {
        if (unlink($filePath)) {
          $success_message = "Đã xóa slide thành công!";
        } else {
          $error_message = "Không thể xóa file. Vui lòng thử lại";
        }
      } else {
        $error_message = "File không tồn tại";
      }
    }
  }

  // Xử lý cập nhật thứ tự slide (nếu cần)
  if (isset($_POST['update_slide_order'])) {
    // Có thể thêm logic sắp xếp slide ở đây
    $slideOrder = $_POST['slide_order'] ?? [];
    // Lưu thứ tự vào file hoặc database nếu cần
  }
}

// Lấy danh sách slide từ thư mục
$slideDir = 'img/slideintro/';
$slides = [];

if (file_exists($slideDir)) {
  $files = scandir($slideDir);
  foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
      $filePath = $slideDir . $file;
      if (is_file($filePath)) {
        $fileInfo = pathinfo($filePath);
        if (in_array(strtolower($fileInfo['extension']), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
          $slides[] = [
            'name' => $file,
            'path' => 'img/slideintro/' . $file,
            'size' => filesize($filePath),
            'modified' => filemtime($filePath)
          ];
        }
      }
    }
  }

  // Sắp xếp theo thời gian sửa đổi (mới nhất trước)
  usort($slides, function($a, $b) {
    return $b['modified'] - $a['modified'];
  });
}
?>

<div class="settings-container">
  <h2><i class="fas fa-cog"></i> Cài đặt trang web</h2>

  <?php if (isset($success_message)): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
    </div>
  <?php endif; ?>

  <?php if (isset($error_message)): ?>
    <div class="alert alert-error">
      <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
    </div>
  <?php endif; ?>

  <!-- Tabs -->
  <div class="settings-tabs">
    <button class="tab-btn active" data-tab="slides">Slide giới thiệu</button>
    <button class="tab-btn" data-tab="general">Thông tin chung</button>
    <button class="tab-btn" data-tab="social">Mạng xã hội</button>
  </div>

  <!-- Tab Content -->
  <div class="tab-content">
    <!-- Tab 1: Quản lý Slide -->
    <div class="tab-pane active" id="slides">
      <div class="card">
        <div class="card-header">
          <h3><i class="fas fa-images"></i> Quản lý Slide giới thiệu</h3>
          <p>Quản lý hình ảnh slide trên trang chủ</p>
        </div>

        <div class="card-body">
          <!-- Form upload slide mới -->
          <div class="upload-section">
            <h4><i class="fas fa-upload"></i> Thêm slide mới</h4>
            <form method="POST" enctype="multipart/form-data" class="upload-form">
              <div class="form-group">
                <label for="new_slide">Chọn file ảnh (JPG, PNG, GIF, WebP - Tối đa 5MB)</label>
                <input type="file" id="new_slide" name="new_slide" accept=".jpg,.jpeg,.png,.gif,.webp" required>
                <p class="form-help">Kích thước khuyến nghị: 1920x600px</p>
              </div>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-cloud-upload-alt"></i> Upload Slide
              </button>
            </form>
          </div>

          <!-- Danh sách slide hiện có -->
          <div class="slides-list">
            <h4><i class="fas fa-list"></i> Slide hiện tại (<?php echo count($slides); ?>)</h4>

            <?php if (empty($slides)): ?>
              <div class="empty-state">
                <i class="fas fa-images fa-3x"></i>
                <p>Chưa có slide nào</p>
              </div>
            <?php else: ?>
              <div class="slides-grid">
                <?php foreach ($slides as $slide): ?>
                  <div class="slide-item">
                    <div class="slide-image">
                      <img src="<?php echo $slide['path']; ?>"
                           alt="<?php echo $slide['name']; ?>"
                           onerror="this.src='img/no-image.png'">
                    </div>
                    <div class="slide-info">
                      <div class="slide-name"><?php echo $slide['name']; ?></div>
                      <div class="slide-details">
                        <span><i class="fas fa-file"></i> <?php echo round($slide['size'] / 1024, 1); ?> KB</span>
                        <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', $slide['modified']); ?></span>
                      </div>
                      <div class="slide-actions">
                        <button type="button" class="btn btn-sm btn-danger delete-slide-btn"
                                data-filename="<?php echo htmlspecialchars($slide['name']); ?>">
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                        <a href="<?php echo $slide['path']; ?>"
                           target="_blank"
                           class="btn btn-sm btn-secondary">
                          <i class="fas fa-external-link-alt"></i> Xem
                        </a>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab 2: Thông tin chung -->
    <div class="tab-pane" id="general">
      <div class="card">
        <div class="card-header">
          <h3><i class="fas fa-info-circle"></i> Thông tin trang web</h3>
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="form-group">
              <label for="site_title">Tên trang web *</label>
              <input type="text" id="site_title" name="site_title"
                     value="<?php echo $settings['site_title'] ?? 'TechShop'; ?>" required>
            </div>

            <div class="form-group">
              <label for="site_description">Mô tả trang web</label>
              <textarea id="site_description" name="site_description" rows="3"><?php echo $settings['site_description'] ?? ''; ?></textarea>
            </div>

            <div class="form-group">
              <label for="contact_email">Email liên hệ *</label>
              <input type="email" id="contact_email" name="contact_email"
                     value="<?php echo $settings['contact_email'] ?? 'contact@techshop.vn'; ?>" required>
            </div>

            <div class="form-group">
              <label for="contact_phone">Số điện thoại</label>
              <input type="tel" id="contact_phone" name="contact_phone"
                     value="<?php echo $settings['contact_phone'] ?? ''; ?>">
            </div>

            <div class="form-group">
              <label for="contact_address">Địa chỉ</label>
              <textarea id="contact_address" name="contact_address" rows="2"><?php echo $settings['contact_address'] ?? ''; ?></textarea>
            </div>

            <button type="submit" name="update_settings" class="btn btn-primary">
              <i class="fas fa-save"></i> Lưu thay đổi
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Tab 3: Mạng xã hội -->
    <div class="tab-pane" id="social">
      <div class="card">
        <div class="card-header">
          <h3><i class="fas fa-share-alt"></i> Mạng xã hội</h3>
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="form-group">
              <label for="facebook_url">
                <i class="fab fa-facebook" style="color: #1877F2;"></i> Facebook
              </label>
              <input type="url" id="facebook_url" name="facebook_url"
                     value="<?php echo $settings['facebook_url'] ?? ''; ?>"
                     placeholder="https://facebook.com/ten-trang">
            </div>

            <div class="form-group">
              <label for="instagram_url">
                <i class="fab fa-instagram" style="color: #E4405F;"></i> Instagram
              </label>
              <input type="url" id="instagram_url" name="instagram_url"
                     value="<?php echo $settings['instagram_url'] ?? ''; ?>"
                     placeholder="https://instagram.com/ten-trang">
            </div>

            <div class="form-group">
              <label for="twitter_url">
                <i class="fab fa-twitter" style="color: #1DA1F2;"></i> Twitter/X
              </label>
              <input type="url" id="twitter_url" name="twitter_url"
                     value="<?php echo $settings['twitter_url'] ?? ''; ?>"
                     placeholder="https://twitter.com/ten-trang">
            </div>

            <div class="form-group">
              <label for="youtube_url">
                <i class="fab fa-youtube" style="color: #FF0000;"></i> YouTube
              </label>
              <input type="url" id="youtube_url" name="youtube_url"
                     value="<?php echo $settings['youtube_url'] ?? ''; ?>"
                     placeholder="https://youtube.com/c/ten-kenh">
            </div>

            <button type="submit" name="update_settings" class="btn btn-primary">
              <i class="fas fa-save"></i> Lưu thay đổi
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal xác nhận xóa slide -->
<div id="deleteSlideModal" class="modal_item hidden">
  <div class="modal_item-content">
    <span class="close-btn">&times;</span>
    <h3><i class="fas fa-exclamation-triangle text-warning"></i> Xác nhận xóa</h3>
    <p>Bạn có chắc chắn muốn xóa slide <strong id="slideToDeleteName"></strong> không?</p>
    <p class="text-muted">Hành động này không thể hoàn tác.</p>
    <div class="modal-actions">
      <form id="deleteSlideForm" method="POST">
        <input type="hidden" name="slide_name" id="deleteSlideInput">
        <button type="button" class="btn btn-secondary cancel-delete">Hủy</button>
        <button type="submit" name="delete_slide" class="btn btn-danger">
          <i class="fas fa-trash"></i> Xóa
        </button>
      </form>
    </div>
  </div>
</div>

<style>
  .settings-container {
    padding: 20px;
  }

  .settings-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
  }

  .tab-btn {
    padding: 12px 24px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    font-size: 1rem;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.3s;
  }

  .tab-btn:hover {
    color: #4299e1;
  }

  .tab-btn.active {
    color: #4299e1;
    border-bottom-color: #4299e1;
  }

  .tab-pane {
    display: none;
  }

  .tab-pane.active {
    display: block;
  }

  .card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
  }

  .card-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    background: #f8fafc;
  }

  .card-header h3 {
    margin: 0;
    color: #333;
  }

  .card-header p {
    margin: 5px 0 0;
    color: #666;
  }

  .card-body {
    padding: 20px;
  }

  .upload-section {
    background: #f8fafc;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
  }

  .upload-form {
    margin-top: 15px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
  }

  .form-group input[type="text"],
  .form-group input[type="email"],
  .form-group input[type="tel"],
  .form-group input[type="url"],
  .form-group textarea,
  .form-group input[type="file"] {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    font-size: 1rem;
    transition: all 0.3s;
  }

  .form-group input:focus,
  .form-group textarea:focus {
    outline: none;
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
  }

  .form-help {
    font-size: 0.85rem;
    color: #666;
    margin-top: 5px;
  }

  .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-primary {
    background: #4299e1;
    color: white;
  }

  .btn-primary:hover {
    background: #3182ce;
  }

  .btn-secondary {
    background: #a0aec0;
    color: white;
  }

  .btn-secondary:hover {
    background: #718096;
  }

  .btn-danger {
    background: #f56565;
    color: white;
  }

  .btn-danger:hover {
    background: #e53e3e;
  }

  .btn-sm {
    padding: 6px 12px;
    font-size: 0.85rem;
  }

  .slides-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
  }

  .slide-item {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    background: white;
    transition: transform 0.3s, box-shadow 0.3s;
  }

  .slide-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }

  .slide-image {
    height: 180px;
    overflow: hidden;
  }

  .slide-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .slide-info {
    padding: 15px;
  }

  .slide-name {
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
    word-break: break-all;
  }

  .slide-details {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 12px;
  }

  .slide-actions {
    display: flex;
    gap: 8px;
  }

  .empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #666;
  }

  .empty-state i {
    margin-bottom: 15px;
    opacity: 0.5;
  }

  .alert {
    padding: 12px 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .alert-success {
    background: #c6f6d5;
    color: #22543d;
    border: 1px solid #9ae6b4;
  }

  .alert-error {
    background: #fed7d7;
    color: #742a2a;
    border: 1px solid #fc8181;
  }

  /* Modal */
  .modal_item {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }

  .modal_item.hidden {
    display: none;
  }

  .modal_item-content {
    background: white;
    width: 90%;
    max-width: 500px;
    padding: 25px;
    border-radius: 10px;
    position: relative;
  }

  .modal_item .close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 1.5rem;
    cursor: pointer;
    background: none;
    border: none;
  }

  .modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const tabId = this.dataset.tab;

        // Update active tab button
        tabBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // Show active tab pane
        tabPanes.forEach(pane => {
          pane.classList.remove('active');
          if (pane.id === tabId) {
            pane.classList.add('active');
          }
        });
      });
    });

    // Xử lý xóa slide
    const deleteSlideBtns = document.querySelectorAll('.delete-slide-btn');
    const deleteSlideModal = document.getElementById('deleteSlideModal');
    const slideToDeleteName = document.getElementById('slideToDeleteName');
    const deleteSlideInput = document.getElementById('deleteSlideInput');
    const cancelDeleteBtn = document.querySelector('.cancel-delete');
    const closeModalBtn = document.querySelector('#deleteSlideModal .close-btn');

    deleteSlideBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const fileName = this.dataset.filename;
        slideToDeleteName.textContent = fileName;
        deleteSlideInput.value = fileName;
        deleteSlideModal.classList.remove('hidden');
      });
    });

    // Đóng modal
    function closeDeleteModal() {
      deleteSlideModal.classList.add('hidden');
    }

    cancelDeleteBtn.addEventListener('click', closeDeleteModal);
    closeModalBtn.addEventListener('click', closeDeleteModal);

    // Đóng modal khi click bên ngoài
    deleteSlideModal.addEventListener('click', function(e) {
      if (e.target === this) {
        closeDeleteModal();
      }
    });

    // ESC để đóng modal
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && !deleteSlideModal.classList.contains('hidden')) {
        closeDeleteModal();
      }
    });

    // Preview file trước khi upload
    const fileInput = document.getElementById('new_slide');
    if (fileInput) {
      fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          // Hiển thị preview nếu muốn
          console.log('File selected:', file.name, file.size);
        }
      });
    }
  });
</script>
