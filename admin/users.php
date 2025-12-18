<?php
global $userModel;
$totalUsers = $userModel->count();
$users = $userModel->getAll(50, 0);
?>

<!-- PHẦN HEADER MỚI -->
<div class="table-header-section">
  <!-- Header với thông tin -->
  <div class="table-header-main">
    <div class="header-left">
      <h2><i class="fas fa-users"></i> Quản lý người dùng</h2>
      <div class="header-stats">
                <span class="stat-item">
                    <i class="fas fa-user-check"></i>
                    <strong><?php echo $totalUsers; ?></strong> người dùng
                </span>
        <span class="stat-item">
                    <i class="fas fa-user-shield"></i>
                    <span id="adminCount">0</span> admin
                </span>
        <span class="stat-item">
                    <i class="fas fa-user-clock"></i>
                    <span id="modCount">0</span> moderator
                </span>
        <span class="stat-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="todayUsers">0</span> hôm nay
                </span>
      </div>
    </div>

    <div class="header-right">
      <!-- Search -->
      <div class="search-container">
        <input type="text"
               id="userSearch"
               placeholder="Tìm theo tên, email, số điện thoại..."
               onkeyup="searchUsers()">
        <button class="search-btn">
          <i class="fas fa-search"></i>
        </button>
      </div>

      <!-- Filter buttons -->
      <div class="filter-group">
        <button class="filter-btn active" onclick="filterUsers('all')">
          <i class="fas fa-users"></i> Tất cả
        </button>
        <button class="filter-btn" onclick="filterUsers('admin')">
          <i class="fas fa-user-shield"></i> Admin
        </button>
        <button class="filter-btn" onclick="filterUsers('moderator')">
          <i class="fas fa-user-cog"></i> Moderator
        </button>
        <button class="filter-btn" onclick="filterUsers('user')">
          <i class="fas fa-user"></i> User
        </button>
      </div>

      <!-- Action buttons -->
      <div class="action-group">
        <button class="action-btn primary" onclick="exportUsers()">
          <i class="fas fa-file-export"></i> Xuất Excel
        </button>
        <button class="action-btn info" onclick="refreshUsers()">
          <i class="fas fa-sync-alt"></i> Làm mới
        </button>
      </div>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="quick-stats">
    <div class="stats-cards">
      <div class="stat-card small">
        <div class="stat-icon online">
          <i class="fas fa-circle"></i>
        </div>
        <div class="stat-info">
          <span class="stat-label">Đang hoạt động</span>
          <span class="stat-value" id="activeUsers">0</span>
        </div>
      </div>

      <div class="stat-card small">
        <div class="stat-icon verified">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
          <span class="stat-label">Đã xác thực</span>
          <span class="stat-value" id="verifiedUsers">0</span>
        </div>
      </div>

      <div class="stat-card small">
        <div class="stat-icon pending">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
          <span class="stat-label">Chờ duyệt</span>
          <span class="stat-value" id="pendingUsers">0</span>
        </div>
      </div>

      <div class="stat-card small">
        <div class="stat-icon inactive">
          <i class="fas fa-moon"></i>
        </div>
        <div class="stat-info">
          <span class="stat-label">Không hoạt động</span>
          <span class="stat-value" id="inactiveUsers">0</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal thêm người dùng mới -->
<div id="addUserModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3><i class="fas fa-user-plus"></i> Thêm người dùng mới</h3>
      <button class="close-modal" onclick="closeAddUserModal()">&times;</button>
    </div>
    <div class="modal-body">
      <form id="addUserForm">
        <div class="form-row">
          <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username" required
                   placeholder="Nhập username">
          </div>
          <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required
                   placeholder="example@email.com">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="password">Mật khẩu *</label>
            <input type="password" id="password" name="password" required
                   placeholder="Nhập mật khẩu">
          </div>
          <div class="form-group">
            <label for="confirm_password">Xác nhận mật khẩu *</label>
            <input type="password" id="confirm_password" name="confirm_password" required
                   placeholder="Nhập lại mật khẩu">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="phone">Số điện thoại</label>
            <input type="tel" id="phone" name="phone"
                   placeholder="0987 654 321">
          </div>
          <div class="form-group">
            <label for="level">Vai trò *</label>
            <select id="level" name="level" required>
              <option value="user">User</option>
              <option value="moderator">Moderator</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" class="btn-secondary" onclick="closeAddUserModal()">
            Hủy
          </button>
          <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i> Thêm người dùng
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- PHẦN BẢNG NHƯ CŨ -->
<div class="data-table">
  <table>
    <thead>
    <tr>
      <th>ID</th>
      <th>Username</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Level</th>
      <th>Ngày tạo</th>
      <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($users)): ?>
      <tr>
        <td colspan="7" class="empty-state">
          <i class="fas fa-users-slash"></i>
          <h3>Không có người dùng nào</h3>
          <p>Chưa có người dùng nào trong hệ thống</p>
        </td>
      </tr>
    <?php else: ?>
      <?php foreach ($users as $user):
        // Map level to badge class
        $levelClass = 'user';
        if ($user['level_u'] === 'admin') $levelClass = 'admin';
        if ($user['level_u'] === 'moderator') $levelClass = 'moderator';
        ?>
        <tr>
          <td><?php echo $user['id']; ?></td>
          <td>
            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
          </td>
          <td>
            <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" class="text-primary">
              <?php echo htmlspecialchars($user['email']); ?>
            </a>
          </td>
          <td>
          <span class="phone-cell">
            <?php echo htmlspecialchars(isset($user['phone']) ? $user['phone'] : 'N/A'); ?>
          </span>
          </td>
          <td>
          <span class="level-badge <?php echo $levelClass; ?>" data-level="<?php echo htmlspecialchars($user['level_u']); ?>">
            <i class="fas fa-user-<?php echo $levelClass === 'admin' ? 'shield' : ($levelClass === 'moderator' ? 'cog' : 'circle'); ?>"></i>
            <?php echo htmlspecialchars($user['level_u']); ?>
          </span>
          </td>
          <td>
          <span class="date-cell">
            <?php
            echo '<p>' . date('d/m/Y', strtotime($user['created_at'])) . '</p>';
            echo '<br><small class="text-muted">' . date('H:i:s', strtotime($user['created_at'])) . '</small>';
            ?>
          </span>
          </td>
          <td>
            <div class="action-buttons">
              <a href="admin.php?page=users&action=edit&id=<?php echo $user['id']; ?>"
                 class="btn btn-edit" title="Chỉnh sửa">
                <i class="fas fa-edit"></i>
              </a>
              <a href="admin.php?page=users&action=delete_user&id=<?php echo $user['id']; ?>"
                 class="btn btn-danger"
                 onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')"
                 title="Xóa người dùng">
                <i class="fas fa-trash"></i>
              </a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<style>
  /* ========== HEADER SECTION STYLES ========== */


  .table-header-section {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #eef2f7;
    margin-bottom: 20px;
  }

  /* Main Header */
  .table-header-main {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 20px;
  }

  .header-left h2 {
    margin: 0 0 15px 0;
    color: #1f2937;
    font-size: 24px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .header-left h2 i {
    color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .header-stats {
    display: flex;
    gap: 25px;
    flex-wrap: wrap;
  }

  .stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #f8fafc;
    border-radius: 10px;
    color: #4b5563;
    font-size: 14px;
    border: 1px solid #e5e7eb;
  }

  .stat-item i {
    color: #667eea;
    font-size: 16px;
  }

  .stat-item strong {
    color: #1f2937;
    font-weight: 700;
  }

  /* Header Right */
  .header-right {
    display: flex;
    flex-direction: column;
    gap: 15px;
    min-width: 300px;
  }

  .search-container {
    position: relative;
    display: flex;
    align-items: center;
  }

  #userSearch {
    flex: 1;
    padding: 12px 20px 12px 45px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 14px;
    transition: all 0.3s;
    background: #f8fafc;
  }

  #userSearch:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .search-btn {
    position: absolute;
    left: 15px;
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    font-size: 16px;
  }

  .filter-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .filter-btn {
    padding: 8px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .filter-btn:hover {
    border-color: #d1d5db;
    background: #f9fafb;
  }

  .filter-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
  }

  .action-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .action-btn {
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    justify-content: center;
  }

  .action-btn.primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
  }

  .action-btn.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
  }

  .action-btn.info {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
  }

  .action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  /* Quick Stats */
  .quick-stats {
    padding-top: 20px;
    border-top: 2px solid #f3f4f6;
  }

  .stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
  }

  .stat-card.small {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s;
  }

  .stat-card.small:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
    transform: translateY(-3px);
  }

  .stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
  }

  .stat-icon.online {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
  }

  .stat-icon.verified {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
  }

  .stat-icon.pending {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
  }

  .stat-icon.inactive {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
  }

  .stat-info {
    flex: 1;
  }

  .stat-label {
    display: block;
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 4px;
  }

  .stat-value {
    display: block;
    font-size: 22px;
    font-weight: 700;
    color: #1f2937;
  }

  /* Modal Styles */
  .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  .modal-content {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease;
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .close-modal {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6b7280;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
  }

  .close-modal:hover {
    background: #f3f4f6;
    color: #ef4444;
  }

  .modal-body {
    padding: 25px;
  }

  .form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
  }

  @media (max-width: 768px) {
    .form-row {
      grid-template-columns: 1fr;
    }
  }

  .form-group {
    margin-bottom: 15px;
  }

  .form-group label {
    display: block;
    margin-bottom: 8px;
    color: #4b5563;
    font-weight: 500;
    font-size: 14px;
  }

  .form-group input,
  .form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
    background: white;
  }

  .form-group input:focus,
  .form-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
  }

  .btn-primary, .btn-secondary {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
  }

  .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
  }

  .btn-secondary {
    background: #f3f4f6;
    color: #4b5563;
  }

  .btn-secondary:hover {
    background: #e5e7eb;
  }

  /* Responsive */
  @media (max-width: 1024px) {
    .table-header-main {
      flex-direction: column;
    }

    .header-right {
      width: 100%;
    }

    .stats-cards {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 768px) {
    .header-stats {
      gap: 10px;
    }

    .stat-item {
      padding: 6px 12px;
      font-size: 13px;
    }

    .stats-cards {
      grid-template-columns: 1fr;
    }

    .action-btn {
      padding: 8px 12px;
      font-size: 13px;
    }
  }

  @media (max-width: 480px) {
    .table-header-section {
      padding: 15px;
    }

    .filter-group {
      justify-content: center;
    }

    .filter-btn {
      flex: 1;
      justify-content: center;
    }

    .action-group {
      flex-direction: column;
    }
  }

  /* ========== PHẦN CSS CỦA BẢNG CŨ (GIỮ NGUYÊN) ========== */
  .data-table {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    border: 1px solid #e4e6ef;
    margin-top: 25px;
  }

  .data-table table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 800px;
  }

  .data-table thead {
    background: linear-gradient(135deg, #f5f7fa 0%, #f0f4f8 100%);
  }

  .data-table th {
    padding: 20px 25px;
    text-align: left;
    font-weight: 600;
    color: #ffffff;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    border-bottom: 2px solid #e4e6ef;
    position: relative;
  }

  .data-table th::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -2px;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 0.3s ease;
  }

  .data-table th:hover::after {
    width: 100%;
  }

  .data-table td {
    padding: 18px 25px;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
    color: #5e6278;
    font-size: 14px;
  }

  .data-table tbody tr {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
  }

  .data-table tbody tr:hover {
    background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  }

  .data-table tbody tr:last-child td {
    border-bottom: none;
  }

  /* User info styling */
  .data-table td:nth-child(2) { /* Username column */
    font-weight: 600;
    color: #2c3e50;
  }

  .data-table td:nth-child(3) { /* Email column */
    color: #667eea;
    font-weight: 500;
  }

  .data-table td:nth-child(4) { /* Phone column */
    font-family: 'Consolas', monospace;
    background: #f8f9fa;
    padding: 6px 12px;
    border-radius: 8px;
    display: inline-block;
  }

  .data-table td:nth-child(5) { /* Level column */
    position: relative;
    padding-left: 30px;
  }

  .data-table td:nth-child(5)::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #10b981;
  }

  .data-table td:nth-child(5)[data-level="admin"]::before {
    background: #ef4444;
  }

  .data-table td:nth-child(5)[data-level="moderator"]::before {
    background: #f59e0b;
  }

  .data-table td:nth-child(5)[data-level="user"]::before {
    background: #10b981;
  }

  .data-table td:nth-child(6) { /* Created at column */
    color: #7f8c8d;
    font-size: 13px;
    white-space: nowrap;
  }

  /* Action buttons */
  .data-table .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    cursor: pointer;
    font-size: 14px;
    margin: 0 4px;
  }

  .data-table .btn-edit {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    color: #667eea;
    border: 1px solid rgba(102, 126, 234, 0.2);
  }

  .data-table .btn-edit:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
  }

  .data-table .btn-danger {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(255, 138, 101, 0.1) 100%);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
  }

  .data-table .btn-danger:hover {
    background: linear-gradient(135deg, #ef4444 0%, #ff8a65 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
  }

  /* Empty state */
  .data-table .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #a1a5b7;
  }

  .data-table .empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
  }

  .data-table .empty-state h3 {
    margin: 0 0 10px 0;
    color: #5e6278;
    font-size: 24px;
    font-weight: 600;
  }

  .data-table .empty-state p {
    max-width: 400px;
    margin: 0 auto 20px;
    font-size: 16px;
    line-height: 1.6;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .data-table {
      border-radius: 12px;
      margin: 15px;
      overflow-x: auto;
    }

    .data-table table {
      min-width: 700px;
    }

    .data-table th,
    .data-table td {
      padding: 15px 20px;
      font-size: 13px;
    }

    .data-table .btn {
      width: 36px;
      height: 36px;
      font-size: 13px;
      margin: 2px;
    }

    .data-table tbody tr:hover {
      transform: none;
    }
  }

  /* Animation for table rows */
  @keyframes fadeInRow {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .data-table tbody tr {
    animation: fadeInRow 0.4s ease forwards;
  }

  .data-table tbody tr:nth-child(1) { animation-delay: 0.1s; }
  .data-table tbody tr:nth-child(2) { animation-delay: 0.2s; }
  .data-table tbody tr:nth-child(3) { animation-delay: 0.3s; }
  .data-table tbody tr:nth-child(4) { animation-delay: 0.4s; }
  .data-table tbody tr:nth-child(5) { animation-delay: 0.5s; }
  .data-table tbody tr:nth-child(6) { animation-delay: 0.6s; }
  .data-table tbody tr:nth-child(7) { animation-delay: 0.7s; }
  .data-table tbody tr:nth-child(8) { animation-delay: 0.8s; }
  .data-table tbody tr:nth-child(9) { animation-delay: 0.9s; }
  .data-table tbody tr:nth-child(10) { animation-delay: 1.0s; }

  /* Level badges styling */
  .level-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .level-badge.admin {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(255, 138, 101, 0.1) 100%);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
  }

  .level-badge.moderator {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(251, 191, 36, 0.1) 100%);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.2);
  }

  .level-badge.user {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(34, 197, 94, 0.1) 100%);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.2);
  }

  /* Date formatting */
  .date-cell {
    position: relative;
    padding-left: 30px;
  }

  /* Tooltip for actions */
  .btn[title]:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #2c3e50;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    white-space: nowrap;
    margin-bottom: 8px;
    z-index: 100;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .btn[title]::after {
    display: none;
  }

  .btn[title]:hover::after {
    display: block;
  }
</style>

<script>
  // Dữ liệu mẫu để demo
  const usersData = {
    stats: {
      total: <?php echo $totalUsers; ?>,
      admin: 0,
      moderator: 0,
      user: 0,
      active: 0,
      verified: 0,
      pending: 0,
      inactive: 0,
      today: 0
    }
  };

  // Khởi tạo và tính toán thống kê
  document.addEventListener('DOMContentLoaded', function() {
    calculateUserStats();
    updateStatsDisplay();
  });

  // Tính toán thống kê từ bảng
  function calculateUserStats() {
    const table = document.querySelector('.data-table tbody');
    if (!table) return;

    const rows = table.querySelectorAll('tr');
    let adminCount = 0, modCount = 0, userCount = 0;
    let todayCount = 0;

    const today = new Date().toLocaleDateString('vi-VN');

    rows.forEach(row => {
      if (row.classList.contains('empty-state')) return;

      // Đếm theo level
      const levelBadge = row.querySelector('.level-badge');
      if (levelBadge) {
        const level = levelBadge.getAttribute('data-level');
        if (level === 'admin') adminCount++;
        else if (level === 'moderator') modCount++;
        else if (level === 'user') userCount++;
      }

      // Đếm user tạo hôm nay
      const dateCell = row.querySelector('.date-cell p');
      if (dateCell && dateCell.textContent.trim() === today) {
        todayCount++;
      }
    });

    // Cập nhật dữ liệu
    usersData.stats.admin = adminCount;
    usersData.stats.moderator = modCount;
    usersData.stats.user = userCount;
    usersData.stats.today = todayCount;

    // Tạm thời set các giá trị khác (trong thực tế sẽ lấy từ CSDL)
    usersData.stats.active = Math.floor(usersData.stats.total * 0.7);
    usersData.stats.verified = Math.floor(usersData.stats.total * 0.8);
    usersData.stats.pending = Math.floor(usersData.stats.total * 0.1);
    usersData.stats.inactive = Math.floor(usersData.stats.total * 0.2);
  }

  // Hiển thị thống kê
  function updateStatsDisplay() {
    document.getElementById('adminCount').textContent = usersData.stats.admin;
    document.getElementById('modCount').textContent = usersData.stats.moderator;
    document.getElementById('todayUsers').textContent = usersData.stats.today;
    document.getElementById('activeUsers').textContent = usersData.stats.active;
    document.getElementById('verifiedUsers').textContent = usersData.stats.verified;
    document.getElementById('pendingUsers').textContent = usersData.stats.pending;
    document.getElementById('inactiveUsers').textContent = usersData.stats.inactive;
  }

  // Tìm kiếm người dùng
  function searchUsers() {
    const searchTerm = document.getElementById('userSearch').value.toLowerCase();
    const table = document.querySelector('.data-table tbody');

    if (!table) return;

    const rows = table.querySelectorAll('tr');
    let visibleCount = 0;

    rows.forEach(row => {
      if (row.classList.contains('empty-state')) return;

      const cells = row.querySelectorAll('td');
      let match = false;

      cells.forEach((cell, index) => {
        if (index === 1 || index === 2 || index === 3) { // Username, Email, Phone columns
          if (cell.textContent.toLowerCase().includes(searchTerm)) {
            match = true;
          }
        }
      });

      if (searchTerm === '' || match) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    // Nếu không có kết quả nào
    const noResults = document.createElement('tr');
    noResults.className = 'no-results-row';
    noResults.innerHTML = `
        <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
            <i class="fas fa-search" style="font-size: 40px; margin-bottom: 15px;"></i>
            <h3 style="margin: 0 0 10px 0;">Không tìm thấy kết quả</h3>
            <p>Không có người dùng nào phù hợp với từ khóa "${searchTerm}"</p>
        </td>
    `;

    // Xóa thông báo cũ nếu có
    const oldNoResults = table.querySelector('.no-results-row');
    if (oldNoResults) oldNoResults.remove();

    if (visibleCount === 0 && searchTerm !== '') {
      table.appendChild(noResults);
    }
  }

  // Lọc người dùng theo level
  function filterUsers(level) {
    // Cập nhật nút active
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.classList.remove('active');
    });
    event.target.classList.add('active');

    const table = document.querySelector('.data-table tbody');
    if (!table) return;

    const rows = table.querySelectorAll('tr');
    let visibleCount = 0;

    rows.forEach(row => {
      if (row.classList.contains('empty-state')) return;

      if (level === 'all') {
        row.style.display = '';
        visibleCount++;
      } else {
        const levelBadge = row.querySelector('.level-badge');
        if (levelBadge && levelBadge.getAttribute('data-level') === level) {
          row.style.display = '';
          visibleCount++;
        } else {
          row.style.display = 'none';
        }
      }
    });

    // Cập nhật URL để chia sẻ filter
    updateURLParams(level);
  }

  // Cập nhật URL parameters
  function updateURLParams(level) {
    const url = new URL(window.location.href);
    if (level === 'all') {
      url.searchParams.delete('filter');
    } else {
      url.searchParams.set('filter', level);
    }
    window.history.pushState({}, '', url);
  }

  // Xuất Excel
  function exportUsers() {
    // Tạo dữ liệu Excel
    const data = [
      ['Danh sách Người dùng', '', '', '', '', '', ''],
      ['Xuất ngày:', new Date().toLocaleDateString('vi-VN'), '', '', '', '', ''],
      ['', '', '', '', '', '', ''],
      ['ID', 'Username', 'Email', 'Phone', 'Level', 'Ngày tạo', 'Trạng thái'],
    ];

    // Lấy dữ liệu từ bảng
    const rows = document.querySelectorAll('.data-table tbody tr:not(.empty-state)');
    rows.forEach(row => {
      if (row.style.display !== 'none') {
        const cells = row.querySelectorAll('td');
        const rowData = [];
        cells.forEach((cell, index) => {
          if (index !== 6) { // Bỏ qua cột Actions
            rowData.push(cell.textContent.trim());
          }
        });
        data.push(rowData);
      }
    });

    // Xuất file Excel
    const ws = XLSX.utils.aoa_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Users");

    const fileName = `users_export_${new Date().toISOString().split('T')[0]}.xlsx`;
    XLSX.writeFile(wb, fileName);
  }

  // Làm mới danh sách
  function refreshUsers() {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tải...';
    btn.disabled = true;

    // Giả lập load dữ liệu mới
    setTimeout(() => {
      calculateUserStats();
      updateStatsDisplay();

      btn.innerHTML = originalHtml;
      btn.disabled = false;

      // Hiển thị thông báo
      showToast('Danh sách người dùng đã được làm mới!', 'success');
    }, 1500);
  }

  // Modal thêm người dùng
  function showAddUserModal() {
    document.getElementById('addUserModal').style.display = 'flex';
    document.getElementById('username').focus();
  }

  function closeAddUserModal() {
    document.getElementById('addUserModal').style.display = 'none';
    document.getElementById('addUserForm').reset();
  }

  // Xử lý form thêm người dùng
  document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Lấy dữ liệu form
    const formData = {
      username: document.getElementById('username').value,
      email: document.getElementById('email').value,
      password: document.getElementById('password').value,
      confirm_password: document.getElementById('confirm_password').value,
      phone: document.getElementById('phone').value,
      level: document.getElementById('level').value
    };

    // Validate
    if (formData.password !== formData.confirm_password) {
      showToast('Mật khẩu xác nhận không khớp!', 'error');
      return;
    }

    // Giả lập gửi dữ liệu lên server
    console.log('Thêm người dùng mới:', formData);

    // Hiển thị thông báo
    showToast('Đã thêm người dùng mới thành công!', 'success');

    // Đóng modal
    closeAddUserModal();

    // Làm mới danh sách
    setTimeout(refreshUsers, 500);
  });

  // Hiển thị toast message
  function showToast(message, type = 'info') {
    // Tạo toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">&times;</button>
    `;

    // Thêm vào body
    document.body.appendChild(toast);

    // Tự động xóa sau 5 giây
    setTimeout(() => {
      if (toast.parentElement) {
        toast.remove();
      }
    }, 5000);
  }

  // Thêm CSS cho toast
  const toastCSS = document.createElement('style');
  toastCSS.textContent = `
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 10px;
    background: white;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 10000;
    animation: slideInRight 0.3s ease;
    min-width: 300px;
    max-width: 400px;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.toast-success {
    border-left: 4px solid #10b981;
}

.toast-error {
    border-left: 4px solid #ef4444;
}

.toast-info {
    border-left: 4px solid #3b82f6;
}

.toast i {
    font-size: 18px;
}

.toast-success i { color: #10b981; }
.toast-error i { color: #ef4444; }
.toast-info i { color: #3b82f6; }

.toast span {
    flex: 1;
    color: #1f2937;
    font-size: 14px;
}

.toast button {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    font-size: 18px;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.toast button:hover {
    background: #f3f4f6;
    color: #ef4444;
}
`;
  document.head.appendChild(toastCSS);

  // Load filter từ URL khi trang tải
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter');
    if (filter) {
      filterUsers(filter);
    }
  });

  // Đóng modal khi click bên ngoài
  window.addEventListener('click', function(e) {
    const modal = document.getElementById('addUserModal');
    if (e.target === modal) {
      closeAddUserModal();
    }
  });

  // Đóng modal bằng phím ESC
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeAddUserModal();
    }
  });
</script>
