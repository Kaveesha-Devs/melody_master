<?php
require_once '../includes/functions.php';
requireAdmin();

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $userId = (int)$_POST['user_id'];
    $role = $_POST['role'];
    if ($userId !== $_SESSION['user_id']) {
        dbQuery("UPDATE users SET role=? WHERE id=?", [$role, $userId]);
        flash('success', 'User role updated!');
    }
    redirect(SITE_URL . '/admin/users.php');
}

$users = dbFetchAll("SELECT *, (SELECT COUNT(*) FROM orders WHERE user_id = users.id) as order_count FROM users ORDER BY created_at DESC");
$pageTitle = 'Users';
require_once 'includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Users</h4>
    <span class="badge bg-secondary"><?= count($users) ?> total</span>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-light"><tr><th>Name</th><th>Email</th><th>Username</th><th>Role</th><th>Orders</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td><strong><?= sanitize($u['full_name']) ?></strong></td>
                    <td><?= sanitize($u['email']) ?></td>
                    <td><?= sanitize($u['username']) ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <select name="role" class="form-select form-select-sm d-inline-block" style="width:120px" onchange="if(confirm('Change this user role?')) this.form.submit()">
                                <?php foreach(['customer','staff','admin'] as $r): ?>
                                <option value="<?= $r ?>" <?= $u['role']===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="update_user" value="1">
                        </form>
                    </td>
                    <td><?= $u['order_count'] ?></td>
                    <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if($u['id'] !== $_SESSION['user_id']): ?>
                        <span class="badge bg-light text-muted">-</span>
                        <?php else: ?>
                        <span class="badge bg-primary">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
