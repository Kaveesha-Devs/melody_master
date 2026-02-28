<?php
require_once 'includes/functions.php';
requireLogin();

$orderId = (int)($_GET['id'] ?? 0);
$order = dbFetch("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, $_SESSION['user_id']]);
if (!$order) redirect(SITE_URL . '/account.php?tab=orders');

$orderItems = dbFetchAll("SELECT oi.*, p.slug FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?", [$orderId]);
$downloads = dbFetchAll("SELECT dd.* FROM digital_downloads dd WHERE dd.order_id = ? AND dd.user_id = ?", [$orderId, $_SESSION['user_id']]);

$pageTitle = 'Order ' . $order['order_number'];
require_once 'includes/header.php';

$statusColors = ['pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'];
?>

<div class="container my-4" style="max-width:800px">
    <a href="account.php?tab=orders" class="btn btn-outline-secondary mb-3"><i class="fas fa-arrow-left me-1"></i>Back to Orders</a>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Order <?= sanitize($order['order_number']) ?></h4>
                    <small class="text-muted">Placed on <?= date('d M Y H:i', strtotime($order['created_at'])) ?></small>
                </div>
                <span class="badge bg-<?= $statusColors[$order['status']] ?? 'secondary' ?> fs-6 text-<?= $order['status'] === 'pending' ? 'dark' : 'white' ?>">
                    <?= ucfirst($order['status']) ?>
                </span>
            </div>

            <?php if($order['tracking_number']): ?>
            <div class="alert alert-success">
                <i class="fas fa-truck me-2"></i><strong>Tracking Number:</strong> <?= sanitize($order['tracking_number']) ?>
            </div>
            <?php endif; ?>

            <h6 class="fw-bold mb-3">Items Ordered</h6>
            <?php foreach($orderItems as $item): ?>
            <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                <div>
                    <?php if($item['slug']): ?>
                    <a href="<?= SITE_URL ?>/product.php?slug=<?= sanitize($item['slug']) ?>" class="text-decoration-none fw-semibold"><?= sanitize($item['product_name']) ?></a>
                    <?php else: ?><span class="fw-semibold"><?= sanitize($item['product_name']) ?></span><?php endif; ?>
                    <div class="text-muted small">Qty: <?= $item['quantity'] ?> × <?= formatPrice($item['price']) ?></div>
                </div>
                <strong><?= formatPrice($item['subtotal']) ?></strong>
            </div>
            <?php endforeach; ?>

            <div class="mt-3 text-end">
                <div class="text-muted">Subtotal: <?= formatPrice($order['subtotal']) ?></div>
                <div class="text-muted">Shipping: <?= $order['shipping_cost'] > 0 ? formatPrice($order['shipping_cost']) : 'Free' ?></div>
                <div class="fw-bold fs-5 text-primary">Total: <?= formatPrice($order['total']) ?></div>
            </div>

            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold">Shipping Address</h6>
                    <p class="text-muted small mb-0">
                        <?= sanitize($order['shipping_name']) ?><br>
                        <?= sanitize($order['shipping_address']) ?><br>
                        <?= sanitize($order['shipping_city']) ?> <?= sanitize($order['shipping_postal']) ?><br>
                        <?= sanitize($order['shipping_country']) ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">Payment</h6>
                    <p class="text-muted small"><?= ucfirst($order['payment_method']) ?> — <span class="badge bg-success"><?= ucfirst($order['payment_status']) ?></span></p>
                </div>
            </div>

            <?php if(!empty($downloads)): ?>
            <hr>
            <h6 class="fw-bold"><i class="fas fa-download me-2 text-primary"></i>Digital Downloads</h6>
            <?php foreach($downloads as $dl): 
                $productName = dbFetch("SELECT name FROM products WHERE id = ?", [$dl['product_id']])['name'] ?? 'Product';
            ?>
            <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                <span><i class="fas fa-file-pdf text-danger me-2"></i><?= sanitize($productName) ?> (<?= $dl['download_count'] ?>/<?= $dl['max_downloads'] ?>)</span>
                <?php if($dl['download_count'] < $dl['max_downloads']): ?>
                <a href="download.php?id=<?= $dl['id'] ?>" class="btn btn-sm btn-primary">Download</a>
                <?php else: ?><span class="badge bg-secondary">Limit Reached</span><?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>const siteUrl = '<?= SITE_URL ?>';</script>
<?php require_once 'includes/footer.php'; ?>
