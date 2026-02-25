<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

require_login();
$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch orders
$stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt_orders->execute([$user_id]);
$orders = $stmt_orders->fetchAll();

// Fetch digital downloads directly accessible
$stmt_downloads = $pdo->prepare("
    SELECT o.id as order_id, o.status as order_status, p.name, p.id as product_id, dp.file_path, dp.download_limit 
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN digital_products dp ON p.id = dp.product_id
    WHERE o.user_id = ? AND o.status IN ('processing', 'shipped', 'completed')
");
$stmt_downloads->execute([$user_id]);
$downloads = $stmt_downloads->fetchAll();

require_once 'includes/header.php';
?>

<div style="max-width: 1000px; margin: 0 auto; margin-bottom: 4rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <h1 style="margin-bottom: 0.5rem;">My Account</h1>
            <p style="color: var(--text-secondary);">Welcome,
                <?php echo h($user['username']); ?>.
            </p>
        </div>
        <div>
            <?php if (is_staff()): ?>
                <a href="admin/index.php" class="btn btn-secondary" style="margin-right: 1rem;">Admin Dashboard</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-secondary">Sign Out</a>
        </div>
    </div>

    <?php display_flash_message(); ?>

    <div style="display: grid; grid-template-columns: 1fr 3fr; gap: 3rem;">

        <!-- Sidebar profile -->
        <div>
            <div
                style="background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border); padding: 1.5rem; position: sticky; top: 100px;">
                <div
                    style="width: 80px; height: 80px; background: var(--primary-color); color: var(--background); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold; margin: 0 auto 1.5rem;">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>

                <div style="text-align: center; margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 0.5rem;">
                        <?php echo h($user['username']); ?>
                    </h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.2rem;">
                        <?php echo h($user['email']); ?>
                    </p>
                    <p style="color: var(--text-secondary); font-size: 0.8rem;">Member since
                        <?php echo date('M Y', strtotime($user['created_at'])); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Content area -->
        <div>
            <!-- Digital Downloads Section -->
            <?php if (!empty($downloads)): ?>
                <div
                    style="background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border); padding: 2rem; margin-bottom: 3rem;">
                    <h3 style="margin-bottom: 1.5rem;">Digital Downloads</h3>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                        <?php foreach ($downloads as $download): ?>
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2); padding: 1rem 1.5rem; border-radius: var(--border-radius); border: 1px solid var(--border);">
                                <div>
                                    <div style="font-weight: bold; margin-bottom: 0.25rem;">
                                        <a href="product.php?id=<?php echo $download['product_id']; ?>"
                                            style="color: var(--text-primary);">
                                            <?php echo h($download['name']); ?>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">From Order #
                                        <?php echo $download['order_id']; ?>
                                    </div>
                                </div>
                                <a href="download.php?id=<?php echo $download['product_id']; ?>&order=<?php echo $download['order_id']; ?>"
                                    class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                    Download File
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Order History Section -->
            <div
                style="background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border); padding: 2rem;">
                <h3 style="margin-bottom: 1.5rem;">Order History</h3>

                <?php if (empty($orders)): ?>
                    <p style="color: var(--text-secondary);">You haven't placed any orders yet. <a href="shop.php"
                            style="color: var(--primary-color);">Start shopping!</a></p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <?php foreach ($orders as $order): ?>
                            <?php
                            // Fetch items for this order
                            $stmt_items = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                            $stmt_items->execute([$order['id']]);
                            $items = $stmt_items->fetchAll();

                            $status_color = 'var(--text-secondary)';
                            if ($order['status'] === 'completed')
                                $status_color = 'var(--success)';
                            if ($order['status'] === 'processing')
                                $status_color = 'var(--primary-color)';
                            if ($order['status'] === 'shipped')
                                $status_color = '#64b5f6';
                            if ($order['status'] === 'cancelled')
                                $status_color = 'var(--error)';
                            ?>

                            <div
                                style="border: 1px solid var(--border); border-radius: var(--border-radius); overflow: hidden;">
                                <div
                                    style="background: rgba(0,0,0,0.2); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border);">
                                    <div>
                                        <strong style="display: block; margin-bottom: 0.25rem;">Order #
                                            <?php echo $order['id']; ?>
                                        </strong>
                                        <span style="font-size: 0.85rem; color: var(--text-secondary);">
                                            <?php echo date('F d, Y', strtotime($order['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div style="text-align: right;">
                                        <div
                                            style="text-transform: capitalize; font-weight: bold; color: <?php echo $status_color; ?>; margin-bottom: 0.25rem;">
                                            <?php echo h($order['status']); ?>
                                        </div>
                                        <div style="font-weight: bold;">
                                            <?php echo format_price($order['total_amount']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div style="padding: 1.5rem;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tbody>
                                            <?php foreach ($items as $item): ?>
                                                <tr>
                                                    <td style="padding: 0.5rem 0; color: var(--text-primary);">
                                                        <?php echo h($item['name']); ?>
                                                        <span style="color: var(--text-secondary); font-size: 0.9rem;">x
                                                            <?php echo $item['quantity']; ?>
                                                        </span>
                                                    </td>
                                                    <td style="padding: 0.5rem 0; text-align: right; color: var(--text-secondary);">
                                                        <?php echo format_price($item['price_at_purchase'] * $item['quantity']); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <?php if ($order['shipping_cost'] > 0): ?>
                                            <tfoot>
                                                <tr>
                                                    <td
                                                        style="padding: 0.5rem 0; padding-top: 1rem; border-top: 1px solid var(--border); color: var(--text-secondary);">
                                                        Shipping</td>
                                                    <td
                                                        style="padding: 0.5rem 0; padding-top: 1rem; border-top: 1px solid var(--border); text-align: right; color: var(--text-secondary);">
                                                        <?php echo format_price($order['shipping_cost']); ?>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>