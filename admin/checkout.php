<?php
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$cartItems = getCartItems($userId);

if (empty($cartItems)) {
    flash('error', 'Your cart is empty.');
    redirect(SITE_URL . '/cart.php');
}

$user = getCurrentUser();
$subtotal = getCartTotal($cartItems);
$shipping = calculateShipping($subtotal, $cartItems);
$total = $subtotal + $shipping;

$errors = [];
$success = false;
$orderId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingData = [
        'name'    => trim($_POST['shipping_name'] ?? ''),
        'address' => trim($_POST['shipping_address'] ?? ''),
        'city'    => trim($_POST['shipping_city'] ?? ''),
        'postal'  => trim($_POST['shipping_postal'] ?? ''),
        'country' => trim($_POST['shipping_country'] ?? ''),
    ];
    $paymentMethod = $_POST['payment_method'] ?? 'cod';

    foreach (['name','address','city','country'] as $field) {
        if (empty($shippingData[$field])) $errors[] = ucfirst(str_replace('_',' ', $field)) . ' is required.';
    }

    if (empty($errors)) {
        $orderId = createOrder($userId, $cartItems, $shippingData, $paymentMethod);
        flash('success', 'Order placed successfully!');
        redirect(SITE_URL . '/order-confirmation.php?id=' . $orderId);
    }
}

$pageTitle = 'Checkout';
require_once 'includes/header.php';
?>

<div class="container my-4" style="max-width:900px">
    <h2 class="fw-bold mb-4"><i class="fas fa-lock me-2 text-primary"></i>Checkout</h2>

    <?php if(!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="POST">
    <div class="row g-4">
        <!-- Shipping Info -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-4"><i class="fas fa-truck me-2 text-primary"></i>Shipping Information</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="shipping_name" class="form-control" required value="<?= sanitize($_POST['shipping_name'] ?? $user['full_name']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address *</label>
                            <textarea name="shipping_address" class="form-control" rows="2" required><?= sanitize($_POST['shipping_address'] ?? $user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City *</label>
                            <input type="text" name="shipping_city" class="form-control" required value="<?= sanitize($_POST['shipping_city'] ?? $user['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="shipping_postal" class="form-control" value="<?= sanitize($_POST['shipping_postal'] ?? $user['postal_code'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Country *</label>
                            <select name="shipping_country" class="form-select" required>
                                <?php $countries = ['United Kingdom','United States','Canada','Australia','Sri Lanka','India','Germany','France'];
                                $selected = $_POST['shipping_country'] ?? $user['country'] ?? 'United Kingdom';
                                foreach($countries as $c): ?>
                                <option value="<?= $c ?>" <?= $selected === $c ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-4"><i class="fas fa-credit-card me-2 text-primary"></i>Payment Method</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check border rounded p-3 mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" value="cod" id="cod" checked>
                                <label class="form-check-label" for="cod"><i class="fas fa-money-bill-wave text-success me-2"></i><strong>Cash on Delivery</strong><br><small class="text-muted">Pay when you receive your order</small></label>
                            </div>
                            <div class="form-check border rounded p-3 mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" value="card" id="card">
                                <label class="form-check-label" for="card"><i class="fas fa-credit-card text-primary me-2"></i><strong>Credit/Debit Card</strong><br><small class="text-muted">Visa, Mastercard, Amex (demo)</small></label>
                            </div>
                            <div class="form-check border rounded p-3">
                                <input class="form-check-input" type="radio" name="payment_method" value="paypal" id="paypal">
                                <label class="form-check-label" for="paypal"><i class="fab fa-paypal text-primary me-2"></i><strong>PayPal</strong><br><small class="text-muted">Pay via PayPal (demo)</small></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm sticky-top" style="top:80px">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Order Summary</h5>
                    <?php foreach($cartItems as $item): ?>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span><?= sanitize($item['name']) ?> × <?= $item['quantity'] ?></span>
                        <strong><?= formatPrice($item['effective_price'] * $item['quantity']) ?></strong>
                    </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span><strong><?= formatPrice($subtotal) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping</span>
                        <strong><?= $shipping > 0 ? formatPrice($shipping) : '<span class="text-success">Free</span>' ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Total</span>
                        <strong class="fs-5 text-primary"><?= formatPrice($total) ?></strong>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="fas fa-check-circle me-2"></i>Place Order
                    </button>
                    <div class="text-center mt-3">
                        <small class="text-muted"><i class="fas fa-shield-alt me-1"></i>Secure SSL Encrypted Checkout</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>

<script>const siteUrl = '<?= SITE_URL ?>';</script>
<?php require_once 'includes/footer.php'; ?>
