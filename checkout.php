<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

require_login(); // User must be logged in to checkout

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    set_flash_message('error', 'Your cart is empty.');
    header("Location: shop.php");
    exit();
}

$cart_items = $_SESSION['cart'];
$subtotal = 0;
$has_physical = false;

// Double check stock availability before processing
foreach ($cart_items as $product_id => $item) {
    $stmt = $pdo->prepare("SELECT stock_quantity, is_digital FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $db_product = $stmt->fetch();

    if (!$db_product) {
        unset($_SESSION['cart'][$product_id]);
        set_flash_message('error', 'Some items in your cart are no longer available.');
        header("Location: cart.php");
        exit();
    }

    if (!$db_product['is_digital'] && $item['quantity'] > $db_product['stock_quantity']) {
        $_SESSION['cart'][$product_id]['quantity'] = $db_product['stock_quantity'];
        set_flash_message('error', 'Items in your cart have been adjusted to available stock quantities.');
        header("Location: cart.php");
        exit();
    }

    $subtotal += $item['price'] * $item['quantity'];
    if (!$item['is_digital']) {
        $has_physical = true;
    }
}

$shipping = $has_physical ? calculate_shipping($subtotal) : 0.00;
$total = $subtotal + $shipping;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = '';

    if ($has_physical) {
        $address_line1 = trim($_POST['address_line1'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $postcode = trim($_POST['postcode'] ?? '');
        $country = trim($_POST['country'] ?? '');

        if (empty($address_line1) || empty($city) || empty($postcode)) {
            set_flash_message('error', 'Please fill in all required shipping address fields.');
            header("Location: checkout.php");
            exit();
        }
        $shipping_address = "$address_line1\n$city\n$postcode\n$country";
    }

    try {
        $pdo->beginTransaction();

        // Create order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_cost, shipping_address, status) VALUES (?, ?, ?, ?, ?)");
        // Initially set to processing if paid. Here we simulate immediate payment success.
        $stmt->execute([$_SESSION['user_id'], $total, $shipping, $shipping_address, 'processing']);
        $order_id = $pdo->lastInsertId();

        // Process order items
        foreach ($cart_items as $product_id => $item) {
            $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
            $stmt_item->execute([$order_id, $product_id, $item['quantity'], $item['price']]);

            // Reduce stock for physical items
            if (!$item['is_digital']) {
                $stmt_stock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $stmt_stock->execute([$item['quantity'], $product_id]);
            }
        }

        $pdo->commit();

        // Clear cart
        $_SESSION['cart'] = [];

        set_flash_message('success', 'Thank you! Your order has been placed successfully.');
        header("Location: dashboard.php");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash_message('error', 'An error occurred while processing your order. Please try again later.');
    }
}

require_once 'includes/header.php';
?>

<div style="max-width: 1000px; margin: 0 auto; margin-bottom: 4rem;">
    <h1 style="margin-bottom: 2rem;">Checkout</h1>

    <?php display_flash_message(); ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;">

        <div>
            <form action="checkout.php" method="POST" id="checkout-form">
                <?php if ($has_physical): ?>
                    <div
                        style="background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border); padding: 2rem; margin-bottom: 2rem;">
                        <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);">
                            Shipping Address</h3>
                        <div class="form-group">
                            <label for="address_line1">Address Line 1 *</label>
                            <input type="text" id="address_line1" name="address_line1" class="form-control" required
                                placeholder="Street address, P.O. box, company name, c/o">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="postcode">Postal Code / Zip *</label>
                                <input type="text" id="postcode" name="postcode" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select id="country" name="country" class="form-control">
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States">United States</option>
                                <option value="Canada">Canada</option>
                                <option value="Australia">Australia</option>
                                <option value="Europe">Europe</option>
                            </select>
                        </div>
                    </div>
                <?php else: ?>
                    <div
                        style="background: rgba(76, 175, 80, 0.1); border: 1px solid var(--success); border-radius: var(--border-radius); padding: 2rem; margin-bottom: 2rem;">
                        <h3 style="color: var(--success); margin-bottom: 0.5rem;">Digital Order Only</h3>
                        <p>No shipping address required. Your digital downloads will be available in your dashboard
                            immediately after purchase.</p>
                    </div>
                <?php endif; ?>

                <div
                    style="background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border); padding: 2rem;">
                    <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);">
                        Payment Details</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">This is a demonstration store. No
                        actual payment will be processed.</p>

                    <div
                        style="padding: 1rem; border: 1px dashed var(--primary-color); border-radius: 4px; background: rgba(0,0,0,0.2); text-align: center;">
                        <strong>Simulated Checkout</strong><br>
                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Clicking "Place Order" will
                            immediately create your order and update inventory.</span>
                    </div>
                </div>
            </form>
        </div>

        <div>
            <div
                style="background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border); padding: 1.5rem; position: sticky; top: 100px;">
                <h3 style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">Order
                    Summary</h3>

                <div style="margin-bottom: 1.5rem;">
                    <?php foreach ($cart_items as $item): ?>
                        <div
                            style="display: flex; justify-content: space-between; margin-bottom: 0.8rem; font-size: 0.9rem;">
                            <span>
                                <?php echo h($item['name']); ?> <strong>x
                                    <?php echo $item['quantity']; ?>
                                </strong>
                            </span>
                            <span>
                                <?php echo format_price($item['price'] * $item['quantity']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="border-top: 1px solid var(--border); padding-top: 1rem;">
                    <div
                        style="display: flex; justify-content: space-between; margin-bottom: 1rem; color: var(--text-secondary);">
                        <span>Subtotal</span>
                        <span>
                            <?php echo format_price($subtotal); ?>
                        </span>
                    </div>

                    <div
                        style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; color: var(--text-secondary);">
                        <span>Shipping</span>
                        <?php if ($has_physical): ?>
                            <span>
                                <?php echo $shipping > 0 ? format_price($shipping) : '<span style="color: var(--success);">Free</span>'; ?>
                            </span>
                        <?php else: ?>
                            <span style="color: var(--success);">N/A (Digital)</span>
                        <?php endif; ?>
                    </div>

                    <div
                        style="display: flex; justify-content: space-between; margin-bottom: 2rem; padding-top: 1rem; border-top: 1px solid var(--border); font-size: 1.25rem; font-weight: bold;">
                        <span>Total</span>
                        <span style="color: var(--primary-color);">
                            <?php echo format_price($total); ?>
                        </span>
                    </div>
                </div>

                <button type="submit" form="checkout-form" class="btn btn-primary btn-block"
                    style="font-size: 1.1rem; padding: 1rem;">Place Order &rarr;</button>
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="cart.php" style="color: var(--text-secondary); font-size: 0.9rem;">&larr; Return to
                        Cart</a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>