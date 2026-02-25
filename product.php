<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$id) {
    header("Location: shop.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash_message('error', 'Product not found.');
    header("Location: shop.php");
    exit();
}

// Fetch reviews
$stmt_rev = $pdo->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$stmt_rev->execute([$id]);
$reviews = $stmt_rev->fetchAll();

$avg_rating = 0;
if (count($reviews) > 0) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $avg_rating = $sum / count($reviews);
}

// Handle review submission (only for verified buyers - checking if they really bought it)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'review') {
    if (!isset($_SESSION['user_id'])) {
        set_flash_message('error', 'You must be logged in to leave a review.');
    } else {
        $user_id = $_SESSION['user_id'];

        // Check if user has purchased this item
        $check_purchase = $pdo->prepare("
            SELECT oi.id 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.id 
            WHERE oi.product_id = ? AND o.user_id = ? AND o.status IN ('completed', 'shipped')
            LIMIT 1
        ");
        $check_purchase->execute([$id, $user_id]);

        if ($check_purchase->fetch()) {
            $rating = (int) $_POST['rating'];
            $comment = trim($_POST['comment']);

            if ($rating >= 1 && $rating <= 5) {
                // Check if already reviewed
                $check_rev = $pdo->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
                $check_rev->execute([$id, $user_id]);

                if ($check_rev->fetch()) {
                    set_flash_message('error', 'You have already reviewed this product.');
                } else {
                    $insert_rev = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                    $insert_rev->execute([$id, $user_id, $rating, $comment]);
                    set_flash_message('success', 'Thank you for your review!');
                    header("Location: product.php?id=" . $id);
                    exit();
                }
            } else {
                set_flash_message('error', 'Invalid rating.');
            }
        } else {
            set_flash_message('error', 'You can only review products you have purchased.');
        }
    }
}

require_once 'includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="shop.php" style="color: var(--text-secondary);">&larr; Back to Shop</a>
</div>

<?php display_flash_message(); ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; margin-bottom: 4rem;">
    <!-- Product Image -->
    <div
        style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--border-radius); overflow: hidden; display: flex; align-items: center; justify-content: center; min-height: 400px; position: relative;">
        <?php if ($product['image_url']): ?>
            <img src="<?php echo h($product['image_url']); ?>" alt="<?php echo h($product['name']); ?>"
                style="width: 100%; height: auto; object-fit: contain; max-height: 600px;">
        <?php else: ?>
            <div
                style="color: var(--text-secondary); font-family: 'Playfair Display', serif; font-size: 2rem; font-style: italic;">
                Melody Masters</div>
        <?php endif; ?>

        <?php if ($product['is_digital']): ?>
            <span
                style="position: absolute; top: 1rem; right: 1rem; background: rgba(0,0,0,0.7); color: var(--primary-color); padding: 0.5rem 1rem; border-radius: 4px; font-weight: bold; border: 1px solid var(--primary-color);">Digital
                Product</span>
        <?php endif; ?>
    </div>

    <!-- Product Info -->
    <div>
        <div
            style="font-size: 0.9rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">
            <?php echo h($product['category_name']); ?>
            <?php echo $product['brand'] ? ' &bull; ' . h($product['brand']) : ''; ?>
        </div>

        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">
            <?php echo h($product['name']); ?>
        </h1>

        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="font-size: 2rem; font-weight: bold; color: var(--primary-color);">
                <?php echo format_price($product['price']); ?>
            </div>

            <?php if (count($reviews) > 0): ?>
                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-secondary);">
                    <div style="color: #ffd700;">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= round($avg_rating) ? '&#9733;' : '&#9734;';
                        }
                        ?>
                    </div>
                    <span>(
                        <?php echo count($reviews); ?> reviews)
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <div
            style="margin-bottom: 2rem; padding: 1rem 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <strong style="color: var(--text-primary);">Availability:</strong>
                <?php if ($product['is_digital']): ?>
                    <span style="color: var(--success);">Instant Download</span>
                <?php else: ?>
                    <?php if ($product['stock_quantity'] > 10): ?>
                        <span style="color: var(--success);">In Stock</span>
                    <?php elseif ($product['stock_quantity'] > 0): ?>
                        <span style="color: var(--primary-color);">Low Stock (
                            <?php echo $product['stock_quantity']; ?> left)
                        </span>
                    <?php else: ?>
                        <span style="color: var(--error); font-weight: bold;">Out of Stock</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if (!$product['is_digital']): ?>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">Free shipping on orders over
                    £100.</p>
            <?php endif; ?>
        </div>

        <form action="cart.php" method="POST" style="margin-bottom: 3rem; display: flex; gap: 1rem;">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

            <?php if (!$product['is_digital']): ?>
                <input type="number" name="quantity" value="1" min="1"
                    max="<?php echo $product['stock_quantity'] > 0 ? $product['stock_quantity'] : 1; ?>"
                    class="form-control" style="width: 80px; text-align: center;" <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
            <?php else: ?>
                <input type="hidden" name="quantity" value="1">
            <?php endif; ?>

            <button type="submit" class="btn btn-primary" style="flex: 1; font-size: 1.1rem; padding: 1rem;" <?php echo (!$product['is_digital'] && $product['stock_quantity'] <= 0) ? 'disabled style="background: #555; cursor: not-allowed;"' : ''; ?>>
                <?php echo (!$product['is_digital'] && $product['stock_quantity'] <= 0) ? 'Out of Stock' : 'Add to Cart'; ?>
            </button>
        </form>

        <div>
            <h3 style="margin-bottom: 1rem;">Description</h3>
            <div style="color: var(--text-secondary); line-height: 1.8; white-space: pre-line;">
                <?php echo h($product['description']); ?>
            </div>
        </div>
    </div>
</div>

<!-- Reviews Section -->
<div style="border-top: 1px solid var(--border); padding-top: 3rem;">
    <h2 style="margin-bottom: 2rem;">Customer Reviews</h2>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 4rem;">
        <div>
            <div
                style="background: var(--surface); padding: 2rem; border-radius: var(--border-radius); border: 1px solid var(--border);">
                <h3 style="margin-bottom: 1rem; text-align: center;">Write a Review</h3>
                <p style="color: var(--text-secondary); text-align: center; margin-bottom: 1.5rem; font-size: 0.9rem;">
                    Share your thoughts with other musicians. Only verified purchasers can leave reviews.</p>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="product.php?id=<?php echo $id; ?>" method="POST">
                        <input type="hidden" name="action" value="review">
                        <div class="form-group">
                            <label for="rating">Rating</label>
                            <select name="rating" id="rating" class="form-control" required style="font-family: inherit;">
                                <option value="5">&#9733;&#9733;&#9733;&#9733;&#9733; (5/5) Excellent</option>
                                <option value="4">&#9733;&#9733;&#9733;&#9733;&#9734; (4/5) Very Good</option>
                                <option value="3">&#9733;&#9733;&#9733;&#9734;&#9734; (3/5) Average</option>
                                <option value="2">&#9733;&#9733;&#9734;&#9734;&#9734; (2/5) Poor</option>
                                <option value="1">&#9733;&#9734;&#9734;&#9734;&#9734; (1/5) Terrible</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="comment">Your Review</label>
                            <textarea name="comment" id="comment" rows="4" class="form-control" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-secondary btn-block">Submit Review</button>
                    </form>
                <?php else: ?>
                    <div style="text-align: center;">
                        <a href="login.php" class="btn btn-secondary">Log in to Review</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <?php if (empty($reviews)): ?>
                <div
                    style="padding: 2rem; background: var(--surface); border-radius: var(--border-radius); text-align: center; color: var(--text-secondary);">
                    No reviews yet. Be the first to review this instrument!
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <?php foreach ($reviews as $review): ?>
                        <div
                            style="padding: 1.5rem; background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <strong style="color: var(--text-primary);">
                                    <?php echo h($review['username']); ?>
                                </strong>
                                <span style="color: var(--text-secondary); font-size: 0.85rem;">
                                    <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                </span>
                            </div>
                            <div style="color: #ffd700; margin-bottom: 1rem;">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $review['rating'] ? '&#9733;' : '&#9734;';
                                }
                                ?>
                            </div>
                            <p style="color: var(--text-secondary); line-height: 1.6;">
                                <?php echo nl2br(h($review['comment'])); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>