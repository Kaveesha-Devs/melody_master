<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_staff();

// Fetch metrics
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0.00;

// Fetch recent orders
$recent_orders = $pdo->query("
    SELECT o.id, u.username, o.total_amount, o.status, o.created_at 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC LIMIT 5
")->fetchAll();

// Fetch low stock products
$low_stock = $pdo->query("SELECT id, name, stock_quantity FROM products WHERE stock_quantity <= 5 AND is_digital = 0 ORDER BY stock_quantity ASC LIMIT 5")->fetchAll();

require_once '../includes/header.php';
?>

<div class="admin-dashboard">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Staff Dashboard</h2>
        <div style="display: flex; gap: 1rem;">
            <a href="categories.php" class="btn btn-secondary">Manage Categories</a>
            <a href="products.php" class="btn btn-secondary">Manage Products</a>
            <a href="orders.php" class="btn btn-secondary">Manage Orders</a>
        </div>
    </div>

    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <div class="stat-card"
            style="background: var(--surface); padding: 1.5rem; border-radius: var(--border-radius); border-left: 4px solid var(--primary-color);">
            <h4 style="color: var(--text-secondary); margin-bottom: 0.5rem;">Total Customers</h4>
            <div style="font-size: 2rem; font-weight: bold;">
                <?php echo $total_users; ?>
            </div>
        </div>

        <div class="stat-card"
            style="background: var(--surface); padding: 1.5rem; border-radius: var(--border-radius); border-left: 4px solid var(--primary-color);">
            <h4 style="color: var(--text-secondary); margin-bottom: 0.5rem;">Active Products</h4>
            <div style="font-size: 2rem; font-weight: bold;">
                <?php echo $total_products; ?>
            </div>
        </div>

        <div class="stat-card"
            style="background: var(--surface); padding: 1.5rem; border-radius: var(--border-radius); border-left: 4px solid var(--primary-color);">
            <h4 style="color: var(--text-secondary); margin-bottom: 0.5rem;">Total Orders</h4>
            <div style="font-size: 2rem; font-weight: bold;">
                <?php echo $total_orders; ?>
            </div>
        </div>

        <div class="stat-card"
            style="background: var(--surface); padding: 1.5rem; border-radius: var(--border-radius); border-left: 4px solid var(--primary-color);">
            <h4 style="color: var(--text-secondary); margin-bottom: 0.5rem;">Total Revenue</h4>
            <div style="font-size: 2rem; font-weight: bold;">
                <?php echo format_price($revenue); ?>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <div>
            <h3>Recent Orders</h3>
            <div
                style="background: var(--surface); border-radius: var(--border-radius); overflow: hidden; border: 1px solid var(--border);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: rgba(0,0,0,0.2);">
                        <tr>
                            <th style="padding: 1rem; text-align: left;">Order ID</th>
                            <th style="padding: 1rem; text-align: left;">Customer</th>
                            <th style="padding: 1rem; text-align: left;">Date</th>
                            <th style="padding: 1rem; text-align: left;">Status</th>
                            <th style="padding: 1rem; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                            <tr>
                                <td colspan="5" style="padding: 1rem; text-align: center; color: var(--text-secondary);">No
                                    recent orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr style="border-top: 1px solid var(--border);">
                                    <td style="padding: 1rem;">#
                                        <?php echo $order['id']; ?>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <?php echo h($order['username']); ?>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <span
                                            style="padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; background: rgba(255,255,255,0.1); text-transform: capitalize;">
                                            <?php echo h($order['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; text-align: right;">
                                        <?php echo format_price($order['total_amount']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 1rem; text-align: right;">
                <a href="orders.php">View All Orders &rarr;</a>
            </div>
        </div>

        <div>
            <h3>Low Stock Alerts</h3>
            <div
                style="background: var(--surface); border-radius: var(--border-radius); overflow: hidden; border: 1px solid var(--border);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: rgba(0,0,0,0.2);">
                        <tr>
                            <th style="padding: 1rem; text-align: left;">Product</th>
                            <th style="padding: 1rem; text-align: right;">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($low_stock)): ?>
                            <tr>
                                <td colspan="2" style="padding: 1rem; text-align: center; color: var(--text-secondary);">
                                    Inventory is well stocked.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($low_stock as $item): ?>
                                <tr style="border-top: 1px solid var(--border);">
                                    <td style="padding: 1rem;">
                                        <?php echo h($item['name']); ?>
                                    </td>
                                    <td
                                        style="padding: 1rem; text-align: right; color: <?php echo $item['stock_quantity'] === 0 ? 'var(--error)' : 'var(--primary-color)'; ?>; font-weight: bold;">
                                        <?php echo $item['stock_quantity']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 1rem; text-align: right;">
                <a href="products.php">Manage Inventory &rarr;</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>