<?php
require_once '../includes/functions.php';
requireStaff();

// Dashboard stats
$totalOrders = dbFetch("SELECT COUNT(*) as c FROM orders")['c'];
$totalRevenue = dbFetch("SELECT COALESCE(SUM(total),0) as s FROM orders WHERE payment_status='paid'")['s'];
$totalProducts = dbFetch("SELECT COUNT(*) as c FROM products WHERE status='active'")['c'];
$totalUsers = dbFetch("SELECT COUNT(*) as c FROM users WHERE role='customer'")['c'];
$pendingOrders = dbFetch("SELECT COUNT(*) as c FROM orders WHERE status='pending'")['c'];
$lowStockProducts = dbFetchAll("SELECT * FROM products WHERE stock_quantity <= 5 AND stock_quantity > 0 AND product_type='physical' ORDER BY stock_quantity ASC LIMIT 5");
$recentOrders = dbFetchAll("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 8");
$pendingReviews = dbFetch("SELECT COUNT(*) as c FROM reviews WHERE status='pending'")['c'];

$pageTitle = 'Admin Dashboard';
require_once 'includes/admin-header.php';
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm stat-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div><div class="fs-2 fw-bold"><?= number_format($totalOrders) ?></div><div>Total Orders</div></div>
                    <i class="fas fa-shopping-bag fa-2x opacity-50"></i>
                </div>
                <?php if($pendingOrders > 0): ?><small class="opacity-75"><?= $pendingOrders ?> pending</small><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm stat-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div><div class="fs-2 fw-bold"><?= formatPrice($totalRevenue) ?></div><div>Total Revenue</div></div>
                    <i class="fas fa-pound-sign fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm stat-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div><div class="fs-2 fw-bold"><?= number_format($totalProducts) ?></div><div>Products</div></div>
                    <i class="fas fa-guitar fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm stat-card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div><div class="fs-2 fw-bold"><?= number_format($totalUsers) ?></div><div>Customers</div></div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="fw-bold mb-0">Recent Orders</h6>
                <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light"><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach($recentOrders as $order): ?>
                        <tr>
                            <td><strong><?= sanitize($order['order_number']) ?></strong></td>
                            <td><?= sanitize($order['full_name']) ?></td>
                            <td><?= formatPrice($order['total']) ?></td>
                            <td>
                                <span class="badge bg-<?= ['pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'][$order['status']] ?? 'secondary' ?> text-<?= $order['status'] === 'pending' ? 'dark' : 'white' ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                            <td><a href="orders.php?id=<?= $order['id'] ?>" class="btn btn-xs btn-outline-secondary btn-sm">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <div class="col-lg-4">
        <?php if(!empty($lowStockProducts)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alerts</h6>
            </div>
            <div class="card-body p-0">
                <?php foreach($lowStockProducts as $p): ?>
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <span class="small"><?= sanitize($p['name']) ?></span>
                    <span class="badge bg-<?= $p['stock_quantity'] <= 2 ? 'danger' : 'warning text-dark' ?>"><?= $p['stock_quantity'] ?> left</span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="card-footer bg-white"><a href="products.php" class="btn btn-sm btn-warning w-100">Manage Products</a></div>
        </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Quick Actions</h6></div>
            <div class="card-body">
                <a href="products.php?action=add" class="btn btn-primary w-100 mb-2"><i class="fas fa-plus me-2"></i>Add New Product</a>
                <a href="orders.php?status=pending" class="btn btn-warning w-100 mb-2 text-dark"><i class="fas fa-clock me-2"></i>Pending Orders (<?= $pendingOrders ?>)</a>
                <a href="reviews.php" class="btn btn-info w-100 text-white"><i class="fas fa-star me-2"></i>Pending Reviews (<?= $pendingReviews ?>)</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
