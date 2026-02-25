<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

    if ($product_id) {
        $stmt = $pdo->prepare("SELECT id, name, price, is_digital, stock_quantity, image_url FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product) {
            if ($action === 'add') {
                $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

                // For physical items, check stock
                if (!$product['is_digital'] && $quantity > $product['stock_quantity']) {
                    $quantity = $product['stock_quantity'];
                    set_flash_message('error', "Only $quantity available in stock.");
                }

                // For digital items, max quantity is 1
                if ($product['is_digital']) {
                    $quantity = 1;
                }

                if (isset($_SESSION['cart'][$product_id])) {
                    $new_qty = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
                    if (!$product['is_digital'] && $new_qty > $product['stock_quantity']) {
                        $_SESSION['cart'][$product_id]['quantity'] = $product['stock_quantity'];
                        set_flash_message('error', 'Cannot add more than available stock.');
                    } else if (!$product['is_digital']) {
                        $_SESSION['cart'][$product_id]['quantity'] = $new_qty;
                        set_flash_message('success', 'Cart updated.');
                    } else {
                        set_flash_message('error', 'Digital items can only be purchased once per order.');
                    }
                } else {
                    $_SESSION['cart'][$product_id] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'quantity' => $quantity,
                        'is_digital' => $product['is_digital'],
                        'image_url' => $product['image_url']
                    ];
                    set_flash_message('success', 'Item added to cart!');
                }
            } elseif ($action === 'update') {
                $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;
                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                } else {
                    if (!$product['is_digital'] && $quantity > $product['stock_quantity']) {
                        $_SESSION['cart'][$product_id]['quantity'] = $product['stock_quantity'];
                        set_flash_message('error', 'Quantity adjusted to maximum available stock.');
                    } else if ($product['is_digital']) {
                        $_SESSION['cart'][$product_id]['quantity'] = 1;
                    } else {
                        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                    }
                }
            } elseif ($action === 'remove') {
                unset($_SESSION['cart'][$product_id]);
                set_flash_message('success', 'Item removed from cart.');
            }
        }
    }
    header("Location: cart.php");
    exit();
}

$cart_items = $_SESSION['cart'];
$subtotal = 0;
$has_physical = false;

foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    if (!$item['is_digital']) {
        $has_physical = true;
    }
}

$shipping = $has_physical ? calculate_shipping($subtotal) : 0.00;
$total = $subtotal + $shipping;

require_once 'includes/header.php';
?>

<div style="max-width: 1000px; margin: 0 auto;">
    <h1 style="margin-bottom: 2rem;">Shopping Cart</h1>

    <?php display_flash_message(); ?>

    <?php if (empty($cart_items)): ?>
        <div
            style="text-align: center; padding: 4rem 2rem; background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border);">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"
                stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <h3>Your cart is empty</h3>
            <p style="color: var(--text-secondary); margin-top: 1rem; margin-bottom: 2rem;">Looks like you haven't added any
                instruments yet.</p>
            <a href="shop.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;">

            <!-- Cart Items -->
            <div>
                <div
                    style="background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border); overflow: hidden;">
                    <?php foreach ($cart_items as $item): ?>
                        <div
                            style="display: flex; padding: 1.5rem; border-bottom: 1px solid var(--border); gap: 1.5rem; align-items: center;">
                            <div
                                style="width: 80px; height: 80px; background: #222; border-radius: 4px; overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                <?php if ($item['image_url']): ?>
                                    <img src="<?php echo h($item['image_url']); ?>" alt="Product"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <span style="font-size: 0.6rem; color: var(--text-secondary);">No Img</span>
                                <?php endif; ?>
                            </div>

                            <div style="flex: 1;">
                                <h4 style="margin-bottom: 0.25rem;">
                                    <a href="product.php?id=<?php echo $item['id']; ?>" style="color: var(--text-primary);">
                                        <?php echo h($item['name']); ?>
                                    </a>
                                </h4>
                                <div style="font-weight: bold; color: var(--primary-color); margin-bottom: 0.5rem;">
                                    <?php echo format_price($item['price']); ?>
                                </div>
                                <?php if ($item['is_digital']): ?>
                                    <span
                                        style="font-size: 0.8rem; background: rgba(212, 175, 55, 0.2); color: var(--primary-color); padding: 0.2rem 0.4rem; border-radius: 4px;">Digital</span>
                                <?php endif; ?>
                            </div>

                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <form action="cart.php" method="POST" style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">

                                    <?php if ($item['is_digital']): ?>
                                        <input type="number" name="quantity" value="1" readonly class="form-control"
                                            style="width: 60px; text-align: center; padding: 0.5rem; background: #333; color: var(--text-secondary);">
                                    <?php else: ?>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1"
                                            class="form-control" style="width: 60px; text-align: center; padding: 0.5rem;"
                                            onchange="this.form.submit()">
                                    <?php endif; ?>
                                </form>

                                <form action="cart.php" method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit"
                                        style="background: none; border: none; color: var(--error); cursor: pointer; padding: 0.5rem;"
                                        title="Remove item">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path
                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                            </path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Order Summary -->
            <div>
                <div
                    style="background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border); padding: 1.5rem; position: sticky; top: 100px;">
                    <h3 style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">Order
                        Summary</h3>

                    <div
                        style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: var(--text-secondary);">
                        <span>Subtotal</span>
                        <span>
                            <?php echo format_price($subtotal); ?>
                        </span>
                    </div>

                    <?php if ($has_physical): ?>
                        <div
                            style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: var(--text-secondary);">
                            <span>Shipping</span>
                            <span>
                                <?php echo $shipping > 0 ? format_price($shipping) : '<span style="color: var(--success);">Free</span>'; ?>
                            </span>
                        </div>
                        <?php if ($shipping > 0): ?>
                            <div
                                style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1.5rem; text-align: right; font-style: italic;">
                                Spend
                                <?php echo format_price(100 - $subtotal); ?> more for free shipping!
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div
                            style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; color: var(--text-secondary);">
                            <span>Shipping</span>
                            <span style="color: var(--success);">N/A (Digital)</span>
                        </div>
                    <?php endif; ?>

                    <div
                        style="display: flex; justify-content: space-between; margin-bottom: 2rem; padding-top: 1rem; border-top: 1px solid var(--border); font-size: 1.25rem; font-weight: bold;">
                        <span>Total</span>
                        <span style="color: var(--primary-color);">
                            <?php echo format_price($total); ?>
                        </span>
                    </div>

                    <a href="checkout.php" class="btn btn-primary btn-block"
                        style="font-size: 1.1rem; padding: 1rem;">Proceed to Checkout</a>
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="shop.php" style="color: var(--text-secondary); font-size: 0.9rem;">Continue Shopping</a>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>