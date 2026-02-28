<?php
require_once 'includes/functions.php';
requireLogin();

$user = getCurrentUser();
$tab = $_GET['tab'] ?? 'profile';
$errors = [];
$success = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal = trim($_POST['postal_code'] ?? '');
    $country = trim($_POST['country'] ?? '');
    
    if (empty($fullName)) { $errors[] = 'Full name is required.'; }
    
    if (empty($errors)) {
        dbQuery("UPDATE users SET full_name=?, phone=?, address=?, city=?, postal_code=?, country=? WHERE id=?",
            [$fullName, $phone, $address, $city, $postal, $country, $user['id']]);
        flash('success', 'Profile updated successfully!');
        redirect(SITE_URL . '/account.php?tab=profile');
    }
    $tab = 'profile';
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (!password_verify($current, $user['password'])) {
        $errors[] = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $errors[] = 'New passwords do not match.';
    }
    
    if (empty($errors)) {
        dbQuery("UPDATE users SET password=? WHERE id=?", [password_hash($new, PASSWORD_BCRYPT), $user['id']]);
        flash('success', 'Password changed successfully!');
        redirect(SITE_URL . '/account.php?tab=security');
    }
    $tab = 'security';
}

$orders = dbFetchAll("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC", [$user['id']]);
$downloads = dbFetchAll("SELECT dd.*, p.name as product_name, p.slug as product_slug FROM digital_downloads dd JOIN products p ON dd.product_id = p.id WHERE dd.user_id = ?", [$user['id']]);

$pageTitle = 'My Account';
require_once 'includes/header.php';
?>

<div class="container my-4">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px">
                        <i class="fas fa-user text-white fa-2x"></i>
                    </div>
                    <h6 class="fw-bold mb-0"><?= sanitize($user['full_name']) ?></h6>
                    <small class="text-muted"><?= sanitize($user['email']) ?></small>
                    <div class="mt-2"><span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'staff' ? 'warning' : 'primary') ?>"><?= ucfirst($user['role']) ?></span></div>
                </div>
                <div class="list-group list-group-flush">
                    <a href="?tab=profile" class="list-group-item list-group-item-action border-0 py-3 <?= $tab === 'profile' ? 'active' : '' ?>">
                        <i class="fas fa-user me-2"></i>Profile
                    </a>
                    <a href="?tab=orders" class="list-group-item list-group-item-action border-0 py-3 <?= $tab === 'orders' ? 'active' : '' ?>">
                        <i class="fas fa-box me-2"></i>My Orders <span class="badge bg-secondary float-end"><?= count($orders) ?></span>
                    </a>
                    <a href="?tab=downloads" class="list-group-item list-group-item-action border-0 py-3 <?= $tab === 'downloads' ? 'active' : '' ?>">
                        <i class="fas fa-download me-2"></i>Downloads
                    </a>
                    <a href="?tab=security" class="list-group-item list-group-item-action border-0 py-3 <?= $tab === 'security' ? 'active' : '' ?>">
                        <i class="fas fa-shield-alt me-2"></i>Security
                    </a>
                    <?php if(isStaff()): ?>
                    <a href="admin/" class="list-group-item list-group-item-action border-0 py-3 text-warning">
                        <i class="fas fa-cog me-2"></i>Admin Panel
                    </a>
                    <?php endif; ?>
                    <a href="logout.php" class="list-group-item list-group-item-action border-0 py-3 text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="col-md-9">
            <?php if(!empty($errors)): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>

            <?php if($tab === 'profile'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="fas fa-user me-2 text-primary"></i>Profile Information</h5>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" required value="<?= sanitize($user['full_name']) ?>"></div>
                            <div class="col-md-6"><label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?= sanitize($user['username']) ?>" disabled></div>
                            <div class="col-md-6"><label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled></div>
                            <div class="col-md-6"><label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control" value="<?= sanitize($user['phone'] ?? '') ?>"></div>
                            <div class="col-12"><label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?= sanitize($user['address'] ?? '') ?></textarea></div>
                            <div class="col-md-6"><label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="<?= sanitize($user['city'] ?? '') ?>"></div>
                            <div class="col-md-6"><label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control" value="<?= sanitize($user['postal_code'] ?? '') ?>"></div>
                            <div class="col-12"><label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" value="<?= sanitize($user['country'] ?? '') ?>"></div>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary mt-4">Save Changes</button>
                    </form>
                </div>
            </div>

            <?php elseif($tab === 'orders'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="fas fa-box me-2 text-primary"></i>My Orders</h5>
                    <?php if(empty($orders)): ?>
                    <div class="text-center py-5"><i class="fas fa-box fa-3x text-muted mb-3"></i><p class="text-muted">No orders yet. <a href="shop.php">Start shopping!</a></p></div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="bg-light"><tr><th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach($orders as $order): 
                                    $itemCount = dbFetch("SELECT SUM(quantity) as cnt FROM order_items WHERE order_id = ?", [$order['id']])['cnt'];
                                ?>
                                <tr>
                                    <td><strong><?= sanitize($order['order_number']) ?></strong></td>
                                    <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                    <td><?= $itemCount ?> item(s)</td>
                                    <td><strong><?= formatPrice($order['total']) ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?= ['pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'][$order['status']] ?? 'secondary' ?> text-<?= $order['status'] === 'pending' ? 'dark' : 'white' ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td><a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif($tab === 'downloads'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="fas fa-download me-2 text-primary"></i>Digital Downloads</h5>
                    <?php if(empty($downloads)): ?>
                    <div class="text-center py-5"><i class="fas fa-download fa-3x text-muted mb-3"></i><p class="text-muted">No digital products purchased yet. <a href="shop.php?category=digital-sheet-music">Browse Sheet Music</a></p></div>
                    <?php else: ?>
                    <?php foreach($downloads as $dl): ?>
                    <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-3">
                        <div>
                            <i class="fas fa-file-pdf text-danger me-2"></i>
                            <strong><?= sanitize($dl['product_name']) ?></strong>
                            <div class="small text-muted">Downloads: <?= $dl['download_count'] ?>/<?= $dl['max_downloads'] ?></div>
                        </div>
                        <?php if($dl['download_count'] < $dl['max_downloads']): ?>
                        <a href="download.php?id=<?= $dl['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Download</a>
                        <?php else: ?>
                        <span class="badge bg-secondary">Limit Reached</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif($tab === 'security'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="fas fa-shield-alt me-2 text-primary"></i>Change Password</h5>
                    <form method="POST" style="max-width:400px">
                        <div class="mb-3"><label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6"></div>
                        <div class="mb-3"><label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required></div>
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>const siteUrl = '<?= SITE_URL ?>';</script>
<?php require_once 'includes/footer.php'; ?>
