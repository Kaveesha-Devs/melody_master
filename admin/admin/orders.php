<?php
require_once '../includes/functions.php';
requireStaff();

$id = (int)($_GET['id'] ?? 0);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $tracking = trim($_POST['tracking_number'] ?? '');
    dbQuery("UPDATE orders SET status=?, tracking_number=? WHERE id=?", [$status, $tracking, $orderId]);
    flash('success', 'Order updated!');
    redirect(SITE_URL . '/admin/orders.php?id=' . $orderId);
}

$statusFilter = $_GET['status'] ?? '';

if ($id) {
    // Single order view
    $order = dbFetch("SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?", [$id]);
    if (!$order) redirect(SITE_URL . '/admin/orders.php');
    $orderItems = dbFetchAll("SELECT * FROM order_items WHERE order_id = ?", [$id]);
    $pageTitle = 'Order ' . $order['order_number'];
} else {
    $orders = dbFetchAll(
        "SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id " .
        ($statusFilter ? "WHERE o.status = ? " : "") .
        "ORDER BY o.created_at DESC",
        $statusFilter ? [$statusFilter] : []
    );
    $pageTitle = 'Orders';
}

require_once 'includes/admin-header.php';
?>

<?php if($id && isset($order)): ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Order <?= sanitize($order['order_number']) ?></h4>
    <a href="orders.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>All Orders</a>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Order Items</h6>
                <table class="table">
                    <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        <?php foreach($orderItems as $item): ?>
                        <tr>
                            <td><?= sanitize($item['product_name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= formatPrice($item['price']) ?></td>
                            <td><?= formatPrice($item['subtotal']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-end">Subtotal:</td><td><?= formatPrice($order['subtotal']) ?></td></tr>
                        <tr><td colspan="3" class="text-end">Shipping:</td><td><?= $order['shipping_cost'] > 0 ? formatPrice($order['shipping_cost']) : 'Free' ?></td></tr>
                        <tr class="fw-bold"><td colspan="3" class="text-end">Total:</td><td><?= formatPrice($order['total']) ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Shipping Address</h6>
                <p class="mb-0">
                    <?= sanitize($order['shipping_name']) ?><br>
                    <?= sanitize($order['shipping_address']) ?><br>
                    <?= sanitize($order['shipping_city']) ?>, <?= sanitize($order['shipping_postal']) ?><br>
                    <?= sanitize($order['shipping_country']) ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Customer</h6>
                <p class="mb-1"><strong><?= sanitize($order['full_name']) ?></strong></p>
                <p class="text-muted mb-0 small"><?= sanitize($order['email']) ?></p>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Update Order</h6>
                <form method="POST">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <div class="mb-3"><label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach(['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-3"><label class="form-label">Tracking Number</label>
                        <input type="text" name="tracking_number" class="form-control" value="<?= sanitize($order['tracking_number'] ?? '') ?>"></div>
                    <button type="submit" name="update_order" class="btn btn-primary w-100">Update Order</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Orders</h4>
    <div class="d-flex gap-2">
        <?php foreach([''=>'All','pending'=>'Pending','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered','cancelled'=>'Cancelled'] as $s => $label): ?>
        <a href="orders.php<?= $s ? '?status='.$s : '' ?>" class="btn btn-sm <?= $statusFilter === $s ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-light"><tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach($orders as $order): ?>
                <tr>
                    <td><strong><?= sanitize($order['order_number']) ?></strong></td>
                    <td><?= sanitize($order['full_name']) ?></td>
                    <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                    <td><?= formatPrice($order['total']) ?></td>
                    <td><span class="badge bg-<?= $order['payment_status']==='paid'?'success':'warning text-dark' ?>"><?= ucfirst($order['payment_status']) ?></span></td>
                    <td><span class="badge bg-<?= ['pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'][$order['status']] ?? 'secondary' ?> text-<?= $order['status']==='pending'?'dark':'white' ?>"><?= ucfirst($order['status']) ?></span></td>
                    <td><a href="?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($orders)): ?><tr><td colspan="7" class="text-center text-muted py-4">No orders found</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/admin-footer.php'; ?>
