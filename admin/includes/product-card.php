<?php
$effectivePrice = $product['sale_price'] ?? $product['price'];
$onSale = !empty($product['sale_price']);
?>
<div class="product-card card border-0 shadow-sm">
    <div class="card-img-wrapper">
        <?php if(!empty($product['image'])): ?>
        <img src="<?= SITE_URL ?>/images/products/<?= sanitize($product['image']) ?>" 
             alt="<?= sanitize($product['name']) ?>" loading="lazy"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <?php endif; ?>
        <div class="product-img-placeholder" <?= !empty($product['image']) ? 'style="display:none"' : '' ?>>
            <i class="fas fa-guitar"></i>
            <small><?= sanitize($product['brand'] ?? '') ?></small>
        </div>
        <?php if($onSale): ?><span class="badge-sale">SALE</span><?php endif; ?>
        <?php if(!empty($product['featured']) && empty($onSale)): ?><span class="badge-featured"><i class="fas fa-star me-1"></i>Featured</span><?php endif; ?>
        <?php if($product['product_type'] === 'digital'): ?><span class="badge-digital"><i class="fas fa-download me-1"></i>Digital</span><?php endif; ?>
    </div>
    <div class="card-body d-flex flex-column p-3">
        <div class="product-brand mb-1"><?= sanitize($product['brand'] ?? '') ?></div>
        <h6 class="card-title mb-2 fw-semibold" style="font-size:0.9rem;line-height:1.3;">
            <a href="<?= SITE_URL ?>/product.php?slug=<?= sanitize($product['slug']) ?>" class="text-decoration-none text-dark">
                <?= sanitize($product['name']) ?>
            </a>
        </h6>
        <?php if(isset($product['avg_rating']) && $product['avg_rating'] > 0): ?>
        <div class="mb-2" style="font-size:0.8rem;">
            <?= renderStars($product['avg_rating']) ?>
            <span class="text-muted">(<?= $product['review_count'] ?>)</span>
        </div>
        <?php endif; ?>
        <div class="mt-auto">
            <div class="d-flex align-items-center gap-2 mb-3">
                <?php if($onSale): ?>
                <span class="price-sale"><?= formatPrice($effectivePrice) ?></span>
                <span class="price-original"><?= formatPrice($product['price']) ?></span>
                <?php else: ?>
                <span class="price-current"><?= formatPrice($effectivePrice) ?></span>
                <?php endif; ?>
            </div>
            <?php if($product['product_type'] === 'digital' || $product['stock_quantity'] > 0): ?>
            <button class="btn btn-primary btn-sm w-100" onclick="addToCart(<?= $product['id'] ?>)">
                <i class="fas fa-cart-plus me-1"></i>Add to Cart
            </button>
            <?php else: ?>
            <button class="btn btn-secondary btn-sm w-100" disabled><i class="fas fa-times me-1"></i>Out of Stock</button>
            <?php endif; ?>
        </div>
    </div>
</div>
