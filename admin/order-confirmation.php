<?php
require_once 'includes/functions.php';
requireLogin();

$orderId = (int)($_GET['id'] ?? 0);
$order = dbFetch("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, $_SESSION['user_id']]);

if (!$order) {
    redirect(SITE_URL . '/account.php');
}

$orderItems = dbFetchAll("SELECT * FROM order_items WHERE order_id = ?", [$orderId]);
$digitalDownloads = dbFetchAll("SELECT dd.*, p.name FROM digital_downloads dd JOIN products p ON dd.product_id = p.id WHERE dd.order_id = ? AND dd.user_id = ?", [$orderId, $_SESSION['user_id']]);

$pageTitle = 'Order Confirmation';
require_once 'includes/header.php';
?>

<div class="container my-5" style="max-width:700px">
    <div class="text-center mb-5">
        <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px">
            <i class="fas fa-check text-white fa-2x"></i>
        </div>
        <h2 class="fw-bold">Order Confirmed!</h2>
        <p class="text-muted">Thank you for your purchase. Your order has been placed successfully.</p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-6"><strong>Order Number:</strong><br><span class="text-primary"><?= sanitize($order['order_number']) ?></span></div>
                <div class="col-6"><strong>Order Date:</strong><br><?= date('d M Y', strtotime($order['created_at'])) ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-6"><strong>Payment:</strong><br><?= ucfirst($order['payment_method']) ?></div>
                <div class="col-6"><strong>Status:</strong><br><span class="badge bg-warning text-dark"><?= ucfirst($order['status']) ?></span></div>
            </div>
            <hr>
            <h6 class="fw-bold mb-3">Order Items</h6>
            <?php foreach($orderItems as $item): ?>
            <div class="d-flex justify-content-between mb-2">
                <span><?= sanitize($item['product_name']) ?> × <?= $item['quantity'] ?></span>
                <strong><?= formatPrice($item['subtotal']) ?></strong>
            </div>
            <?php endforeach; ?>
            <hr>
            <div class="d-flex justify-content-between"><span>Subtotal</span><strong><?= formatPrice($order['subtotal']) ?></strong></div>
            <div class="d-flex justify-content-between"><span>Shipping</span><strong><?= $order['shipping_cost'] > 0 ? formatPrice($order['shipping_cost']) : 'Free' ?></strong></div>
            <div class="d-flex justify-content-between mt-2"><span class="fw-bold">Total</span><strong class="text-primary fs-5"><?= formatPrice($order['total']) ?></strong></div>
        </div>
    </div>

    <?php if(!empty($digitalDownloads)): ?>
    <div class="card border-0 shadow-sm border-primary mb-4">
        <div class="card-body">
            <h5 class="fw-bold"><i class="fas fa-download me-2 text-primary"></i>Your Digital Downloads</h5>
            <p class="text-muted small">These files are available for immediate download.</p>
            <?php foreach($digitalDownloads as $dl): ?>
            <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2">
                <span><i class="fas fa-file-pdf text-danger me-2"></i><?= sanitize($dl['name']) ?></span>
                <a href="download.php?id=<?= $dl['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Download</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="text-center">
        <a href="account.php?tab=orders" class="btn btn-outline-primary me-2"><i class="fas fa-list me-1"></i>View All Orders</a>
        <a href="shop.php" class="btn btn-primary"><i class="fas fa-store me-1"></i>Continue Shopping</a>
    </div>
</div>

<script>const siteUrl = '<?= SITE_URL ?>';</script>
<?php require_once 'includes/footer.php'; ?>
