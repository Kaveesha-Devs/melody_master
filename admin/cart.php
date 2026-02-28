<?php
$pageTitle = 'Shopping Cart';
require_once 'includes/header.php';

$userId = isLoggedIn() ? $_SESSION['user_id'] : null;
$cartItems = getCartItems($userId);
$subtotal = getCartTotal($cartItems);
$shipping = calculateShipping($subtotal, $cartItems);
$total = $subtotal + $shipping;
?>

<div class="container my-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-shopping-cart me-2"></i>Shopping Cart</h2>

    <?php if(empty($cartItems)): ?>
    <div class="text-center py-5">
        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">Your cart is empty</h4>
        <p class="text-muted">Add some instruments to get started!</p>
        <a href="shop.php" class="btn btn-primary btn-lg"><i class="fas fa-store me-2"></i>Continue Shopping</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <!-- Cart Items -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Product</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cartBody">
                            <?php foreach($cartItems as $item): ?>
                            <?php $pid = $item['product_id'] ?? $item['id']; ?>
                            <tr id="cart-row-<?= $pid ?>">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light rounded" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">
                                            <?php if(!empty($item['image'])): ?>
                                            <img src="<?= SITE_URL ?>/images/products/<?= sanitize($item['image']) ?>" style="width:100%;height:100%;object-fit:cover" alt="">
                                            <?php else: ?>
                                            <i class="fas fa-guitar text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="<?= SITE_URL ?>/product.php?id=<?= $pid ?>" class="text-decoration-none text-dark fw-semibold"><?= sanitize($item['name']) ?></a>
                                            <?php if($item['product_type'] === 'digital'): ?>
                                            <div><span class="badge bg-primary small">Digital</span></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center align-middle"><?= formatPrice($item['effective_price']) ?></td>
                                <td class="text-center align-middle">
                                    <div class="qty-wrapper d-inline-flex align-items-center border rounded">
                                        <button class="btn btn-sm px-2" onclick="updateQty(<?= $pid ?>, -1)">-</button>
                                        <span id="qty-<?= $pid ?>" class="px-2"><?= $item['quantity'] ?></span>
                                        <button class="btn btn-sm px-2" onclick="updateQty(<?= $pid ?>, 1)">+</button>
                                    </div>
                                </td>
                                <td class="text-center align-middle fw-bold" id="subtotal-<?= $pid ?>"><?= formatPrice($item['effective_price'] * $item['quantity']) ?></td>
                                <td class="align-middle">
                                    <button class="btn btn-link text-danger p-0" onclick="removeItem(<?= $pid ?>)"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <a href="shop.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Continue Shopping</a>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Order Summary</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <strong id="summarySubtotal"><?= formatPrice($subtotal) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping</span>
                        <span id="summaryShipping">
                            <?php if($shipping == 0): ?>
                            <span class="text-success">Free</span>
                            <?php else: ?>
                            <?= formatPrice($shipping) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if($shipping > 0 && $subtotal < FREE_SHIPPING_THRESHOLD): ?>
                    <div class="alert alert-info py-2 small">
                        Add <?= formatPrice(FREE_SHIPPING_THRESHOLD - $subtotal) ?> more for free shipping!
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold">Total</span>
                        <strong class="fs-5 text-primary" id="summaryTotal"><?= formatPrice($total) ?></strong>
                    </div>
                    <?php if(isLoggedIn()): ?>
                    <a href="checkout.php" class="btn btn-primary w-100 btn-lg"><i class="fas fa-lock me-2"></i>Proceed to Checkout</a>
                    <?php else: ?>
                    <a href="login.php?redirect=checkout.php" class="btn btn-primary w-100 btn-lg mb-2"><i class="fas fa-sign-in-alt me-2"></i>Login to Checkout</a>
                    <a href="register.php" class="btn btn-outline-primary w-100">Create Account</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
const siteUrl = '<?= SITE_URL ?>';
function updateQty(productId, change) {
    const qtyEl = document.getElementById('qty-' + productId);
    let qty = parseInt(qtyEl.textContent) + change;
    if (qty < 1) return removeItem(productId);
    fetch(`${siteUrl}/cart-action.php`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=update&product_id=${productId}&quantity=${qty}`
    }).then(r => r.json()).then(d => {
        if(d.success) location.reload();
    });
}
function removeItem(productId) {
    fetch(`${siteUrl}/cart-action.php`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=remove&product_id=${productId}`
    }).then(r => r.json()).then(d => {
        if(d.success) location.reload();
    });
}
</script>
<?php require_once 'includes/footer.php'; ?>
