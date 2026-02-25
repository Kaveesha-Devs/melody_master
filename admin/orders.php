<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_staff();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int) $_POST['order_id'];
    $status = $_POST['status'];

    $valid_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

    if (in_array($status, $valid_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        set_flash_message('success', "Order #$order_id status updated to $status.");
    }
    header("Location: orders.php");
    exit();
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id";
$params = [];

if ($status_filter) {
    $sql .= " WHERE o.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div style="display: flex; gap: 2rem;">
    <!-- Sidebar navigation for admin -->
    <div style="width: 250px; flex-shrink: 0;">
        <div
            style="background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border); padding: 1.5rem; position: sticky; top: 100px;">
            <h3 style="margin-bottom: 1rem;">Admin Menu</h3>
            <ul style="list-style: none;">
                <li style="margin-bottom: 0.5rem;"><a href="index.php">Dashboard</a></li>
                <li style="margin-bottom: 0.5rem;"><a href="categories.php">Categories</a></li>
                <li style="margin-bottom: 0.5rem;"><a href="products.php">Products</a></li>
                <li style="margin-bottom: 0.5rem;"><a href="orders.php" style="color: var(--primary-color);">Orders</a>
                </li>
            </ul>
        </div>
    </div>

    <div style="flex: 1;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Manage Orders</h2>

            <form action="orders.php" method="GET" style="display: flex; align-items: center; gap: 0.5rem;">
                <label for="status">Filter by Status:</label>
                <select name="status" id="status" class="form-control" style="width: auto; padding: 0.4rem;"
                    onchange="this.form.submit()">
                    <option value="">All Orders</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending
                    </option>
                    <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>
                        >Processing</option>
                    <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped
                    </option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed
                    </option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled
                    </option>
                </select>
            </form>
        </div>

        <?php display_flash_message(); ?>

        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <?php if (empty($orders)): ?>
                <div
                    style="padding: 2rem; background: var(--surface); border-radius: var(--border-radius); text-align: center; color: var(--text-secondary); border: 1px solid var(--border);">
                    No orders found.
                </div>
            <?php else: ?>
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
                        style="border: 1px solid var(--border); border-radius: var(--border-radius); overflow: hidden; background: var(--surface);">
                        <div
                            style="background: rgba(0,0,0,0.2); padding: 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border);">
                            <div>
                                <h3 style="margin-bottom: 0.5rem;">Order #
                                    <?php echo $order['id']; ?>
                                </h3>
                                <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                    <strong>Customer:</strong>
                                    <?php echo h($order['username']); ?> (<a href="mailto:<?php echo h($order['email']); ?>">
                                        <?php echo h($order['email']); ?>
                                    </a>)<br>
                                    <strong>Date:</strong>
                                    <?php echo date('F d, Y H:i:s', strtotime($order['created_at'])); ?>
                                </div>
                            </div>

                            <div
                                style="text-align: right; background: var(--background); padding: 1rem; border-radius: var(--border-radius); border: 1px solid var(--border);">
                                <form action="orders.php" method="POST"
                                    style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <label style="font-size: 0.8rem; color: var(--text-secondary); text-align: left;">Update
                                        Status:</label>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <select name="status" class="form-control"
                                            style="padding: 0.4rem; color: <?php echo $status_color; ?>; font-weight: bold; text-transform: capitalize;">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn btn-secondary"
                                            style="padding: 0.4rem 0.8rem;">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; padding: 1.5rem;">
                            <!-- Items -->
                            <div>
                                <h4 style="margin-bottom: 1rem;">Order Items</h4>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead style="border-bottom: 1px solid var(--border);">
                                        <tr>
                                            <th style="padding: 0.5rem; text-align: left; color: var(--text-secondary);">Item
                                            </th>
                                            <th style="padding: 0.5rem; text-align: center; color: var(--text-secondary);">Qty
                                            </th>
                                            <th style="padding: 0.5rem; text-align: right; color: var(--text-secondary);">Price
                                            </th>
                                            <th style="padding: 0.5rem; text-align: right; color: var(--text-secondary);">Total
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                                <td style="padding: 0.8rem 0.5rem;">
                                                    <?php echo h($item['name']); ?>
                                                </td>
                                                <td style="padding: 0.8rem 0.5rem; text-align: center;">
                                                    <?php echo $item['quantity']; ?>
                                                </td>
                                                <td style="padding: 0.8rem 0.5rem; text-align: right;">
                                                    <?php echo format_price($item['price_at_purchase']); ?>
                                                </td>
                                                <td style="padding: 0.8rem 0.5rem; text-align: right; font-weight: bold;">
                                                    <?php echo format_price($item['price_at_purchase'] * $item['quantity']); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <?php if ($order['shipping_cost'] > 0): ?>
                                            <tr>
                                                <td colspan="3"
                                                    style="padding: 0.8rem 0.5rem; text-align: right; color: var(--text-secondary);">
                                                    Shipping:</td>
                                                <td style="padding: 0.8rem 0.5rem; text-align: right; font-weight: bold;">
                                                    <?php echo format_price($order['shipping_cost']); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td colspan="3"
                                                style="padding: 0.8rem 0.5rem; text-align: right; font-size: 1.1rem; color: var(--primary-color);">
                                                Order Total:</td>
                                            <td
                                                style="padding: 0.8rem 0.5rem; text-align: right; font-size: 1.1rem; font-weight: bold; color: var(--primary-color);">
                                                <?php echo format_price($order['total_amount']); ?>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Shipping Addr -->
                            <div>
                                <h4 style="margin-bottom: 1rem;">Shipping Address</h4>
                                <?php if ($order['shipping_address']): ?>
                                    <div
                                        style="background: rgba(0,0,0,0.2); padding: 1rem; border-radius: var(--border-radius); border: 1px solid var(--border); font-size: 0.95rem; line-height: 1.6; white-space: pre-line;">
                                        <?php echo h($order['shipping_address']); ?>
                                    </div>
                                <?php else: ?>
                                    <div
                                        style="background: rgba(76, 175, 80, 0.1); color: var(--success); padding: 1rem; border-radius: var(--border-radius); border: 1px solid var(--success); font-size: 0.95rem;">
                                        Digital Order - No Shipping Required
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>