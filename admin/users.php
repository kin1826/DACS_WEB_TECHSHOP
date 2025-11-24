<?php
global $userModel;
$users = $userModel->getAll(50, 0);
?>

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
    <?php foreach ($users as $user): ?>
      <tr>
        <td><?php echo $user['id']; ?></td>
        <td><?php echo htmlspecialchars($user['username']); ?></td>
        <td><?php echo htmlspecialchars($user['email']); ?></td>
        <td><?php echo htmlspecialchars(isset($user['phone']) ? $user['phone'] : 'N/A'); ?></td>
        <td><?php echo htmlspecialchars($user['level_u']); ?></td>
        <td><?php echo $user['created_at']; ?></td>
        <td>
          <a href="../admin.php?page=users&action=edit&id=<?php echo $user['id']; ?>" class="btn btn-edit">
            <i class="fas fa-edit"></i>
          </a>
          <a href="../admin.php?page=users&action=delete_user&id=<?php echo $user['id']; ?>"
             class="btn btn-danger"
             onclick="return confirm('Xóa user này?')">
            <i class="fas fa-trash"></i>
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
