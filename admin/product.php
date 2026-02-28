<?php
require_once 'includes/functions.php';
$slug = $_GET['slug'] ?? '';
$id = $_GET['id'] ?? '';

$product = $slug ? getProduct(null, $slug) : getProduct($id);
if (!$product) {
    header('Location: shop.php');
    exit;
}

$pageTitle = $product['name'];
require_once 'includes/header.php';

$specs = json_decode($product['specifications'] ?? '{}', true);
$reviews = getProductReviews($product['id']);
$avgRating = count($reviews) > 0 ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;
$relatedProducts = getProducts(['category_id' => $product['category_id']], 4);
$relatedProducts = array_filter($relatedProducts, fn($p) => $p['id'] !== $product['id']);
$digitalInfo = dbFetch("SELECT * FROM digital_products WHERE product_id = ?", [$product['id']]);
$effectivePrice = $product['sale_price'] ?? $product['price'];
$canReview = isLoggedIn() && canReview($_SESSION['user_id'] ?? 0, $product['id']);

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        flash('error', 'Please login to submit a review.');
    } elseif (!$canReview) {
        flash('error', 'You can only review products you have purchased.');
    } else {
        $rating = (int)$_POST['rating'];
        $title = trim($_POST['title'] ?? '');
        $comment = trim($_POST['comment'] ?? '');
        if ($rating < 1 || $rating > 5) {
            flash('error', 'Please select a valid rating.');
        } elseif (empty($comment)) {
            flash('error', 'Please write a review comment.');
        } else {
            // Get an order to link review to
            $order = dbFetch("SELECT o.id FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered' LIMIT 1", [$_SESSION['user_id'], $product['id']]);
            dbInsert("INSERT INTO reviews (product_id, user_id, order_id, rating, title, comment) VALUES (?,?,?,?,?,?)",
                [$product['id'], $_SESSION['user_id'], $order['id'], $rating, $title, $comment]);
            flash('success', 'Your review has been submitted and is pending approval.');
            redirect(SITE_URL . '/product.php?slug=' . $product['slug']);
        }
    }
}
?>

<div class="container my-4">
    <!-- Breadcrumb -->
    <nav><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/shop.php">Shop</a></li>
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/shop.php?category=<?= sanitize($product['category_slug']) ?>"><?= sanitize($product['category_name']) ?></a></li>
        <li class="breadcrumb-item active"><?= sanitize($product['name']) ?></li>
    </ol></nav>

    <div class="row g-5">
        <!-- Product Image -->
        <div class="col-md-5">
            <div class="product-detail-image bg-light rounded-3 d-flex align-items-center justify-content-center" style="min-height:400px;">
                <?php if(!empty($product['image'])): ?>
                <img src="<?= SITE_URL ?>/images/products/<?= sanitize($product['image']) ?>" 
                     class="img-fluid rounded-3" alt="<?= sanitize($product['name']) ?>"
                     onerror="this.style.display='none';document.getElementById('imgFallback').style.display='flex'">
                <?php endif; ?>
                <div id="imgFallback" class="text-center text-muted" <?= !empty($product['image']) ? 'style="display:none"' : '' ?> style="padding:60px">
                    <i class="fas fa-guitar fa-5x mb-3"></i><br>
                    <span><?= sanitize($product['brand'] ?? '') ?></span>
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-md-7">
            <div class="text-muted small mb-1"><?= sanitize($product['brand'] ?? '') ?></div>
            <h1 class="h2 fw-bold mb-2"><?= sanitize($product['name']) ?></h1>
            
            <!-- Rating -->
            <?php if(count($reviews) > 0): ?>
            <div class="mb-3"><?= renderStars($avgRating) ?> <span class="text-muted small ms-1"><?= number_format($avgRating,1) ?>/5 (<?= count($reviews) ?> reviews)</span></div>
            <?php endif; ?>

            <!-- Price -->
            <div class="mb-4">
                <?php if($product['sale_price']): ?>
                <span class="fs-2 fw-bold text-danger"><?= formatPrice($product['sale_price']) ?></span>
                <span class="fs-4 text-muted text-decoration-line-through ms-2"><?= formatPrice($product['price']) ?></span>
                <span class="badge bg-danger ms-2">Save <?= formatPrice($product['price'] - $product['sale_price']) ?></span>
                <?php else: ?>
                <span class="fs-2 fw-bold text-primary"><?= formatPrice($product['price']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Stock / Type -->
            <div class="mb-3">
                <?php if($product['product_type'] === 'digital'): ?>
                <span class="badge bg-primary fs-6"><i class="fas fa-download me-1"></i>Digital Download</span>
                <small class="text-muted ms-2">Instant access after purchase</small>
                <?php elseif($product['stock_quantity'] > 0): ?>
                <span class="badge bg-success fs-6"><i class="fas fa-check me-1"></i>In Stock</span>
                <small class="text-muted ms-2"><?= $product['stock_quantity'] ?> units available</small>
                <?php else: ?>
                <span class="badge bg-danger fs-6"><i class="fas fa-times me-1"></i>Out of Stock</span>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <p class="text-muted"><?= nl2br(sanitize($product['description'] ?? '')) ?></p>

            <!-- Shipping note -->
            <?php if($product['product_type'] === 'physical'): ?>
            <div class="alert alert-info py-2 small">
                <i class="fas fa-truck me-1"></i>
                <?php if($effectivePrice >= FREE_SHIPPING_THRESHOLD): ?>
                    <strong>Free shipping</strong> on this item!
                <?php else: ?>
                    Shipping: <strong><?= formatPrice(SHIPPING_COST) ?></strong>. Free shipping on orders over <strong><?= formatPrice(FREE_SHIPPING_THRESHOLD) ?></strong>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Add to Cart -->
            <?php if($product['product_type'] === 'digital' || $product['stock_quantity'] > 0): ?>
            <div class="d-flex gap-3 align-items-center mb-4">
                <div class="qty-wrapper d-flex align-items-center border rounded">
                    <button class="btn btn-sm px-3 qty-btn" data-action="dec">-</button>
                    <input type="number" id="productQty" class="cart-qty-input form-control border-0 text-center" value="1" min="1" max="<?= $product['stock_quantity'] ?: 99 ?>">
                    <button class="btn btn-sm px-3 qty-btn" data-action="inc">+</button>
                </div>
                <button class="btn btn-primary btn-lg px-5" onclick="addToCart(<?= $product['id'] ?>, parseInt(document.getElementById('productQty').value))">
                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                </button>
            </div>
            <?php else: ?>
            <button class="btn btn-secondary btn-lg" disabled><i class="fas fa-times me-1"></i>Out of Stock</button>
            <?php endif; ?>

            <!-- Meta -->
            <div class="text-muted small">
                <span>Category: <a href="<?= SITE_URL ?>/shop.php?category=<?= sanitize($product['category_slug']) ?>"><?= sanitize($product['category_name']) ?></a></span>
            </div>
        </div>
    </div>

    <!-- Tabs: Description, Specs, Reviews -->
    <div class="mt-5">
        <ul class="nav nav-tabs" id="productTabs">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc">Description</button></li>
            <?php if(!empty($specs)): ?><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#specs">Specifications</button></li><?php endif; ?>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews">Reviews (<?= count($reviews) ?>)</button></li>
        </ul>
        <div class="tab-content border border-top-0 rounded-bottom p-4">
            <div class="tab-pane fade show active" id="desc">
                <p><?= nl2br(sanitize($product['description'] ?? 'No description available.')) ?></p>
            </div>
            <?php if(!empty($specs)): ?>
            <div class="tab-pane fade" id="specs">
                <table class="table table-striped">
                    <tbody>
                        <?php foreach($specs as $key => $val): ?>
                        <tr><th style="width:200px"><?= sanitize($key) ?></th><td><?= sanitize($val) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            <div class="tab-pane fade" id="reviews">
                <?php if(empty($reviews)): ?>
                <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                <?php else: ?>
                <?php foreach($reviews as $review): ?>
                <div class="review-card card mb-3 ps-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong><?= sanitize($review['full_name'] ?? $review['username']) ?></strong>
                                <div><?= renderStars($review['rating']) ?></div>
                            </div>
                            <small class="text-muted"><?= date('d M Y', strtotime($review['created_at'])) ?></small>
                        </div>
                        <?php if(!empty($review['title'])): ?><h6 class="mt-2"><?= sanitize($review['title']) ?></h6><?php endif; ?>
                        <p class="mb-0 text-muted"><?= sanitize($review['comment']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if($canReview): ?>
                <h5 class="mt-4">Write a Review</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="rating-input">
                            <?php for($i=5; $i>=1; $i--): ?>
                            <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" class="d-none" required>
                            <label for="star<?= $i ?>" class="fs-4 text-muted" style="cursor:pointer">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="mb-3"><input type="text" name="title" class="form-control" placeholder="Review title (optional)"></div>
                    <div class="mb-3"><textarea name="comment" class="form-control" rows="4" placeholder="Write your review..." required></textarea></div>
                    <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                </form>
                <?php elseif(!isLoggedIn()): ?>
                <div class="alert alert-info"><a href="login.php">Login</a> to write a review. You must have purchased this product.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if(!empty($relatedProducts)): ?>
    <div class="mt-5">
        <h4 class="fw-bold mb-4">You Might Also Like</h4>
        <div class="row g-4">
            <?php foreach(array_slice($relatedProducts, 0, 4) as $product): ?>
            <div class="col-6 col-md-3"><?php include 'includes/product-card.php'; ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<button id="scrollTop"><i class="fas fa-chevron-up"></i></button>
<script>
const siteUrl = '<?= SITE_URL ?>';
// Star rating UI
document.querySelectorAll('.rating-input label').forEach((label, i, all) => {
    label.addEventListener('mouseover', () => all.forEach((l,j) => l.style.color = j <= i ? '#fbbf24' : '#ccc'));
    label.addEventListener('click', () => label.previousElementSibling?.click());
});
document.querySelector('.rating-input')?.addEventListener('mouseleave', () => {
    document.querySelectorAll('.rating-input label').forEach(l => l.style.color = '#ccc');
    const checked = document.querySelector('.rating-input input:checked');
    if(checked) {
        const labels = document.querySelectorAll('.rating-input label');
        const idx = [...document.querySelectorAll('.rating-input input')].indexOf(checked);
        labels.forEach((l,i) => l.style.color = i <= idx ? '#fbbf24' : '#ccc');
    }
});
</script>
<?php require_once 'includes/footer.php'; ?>
