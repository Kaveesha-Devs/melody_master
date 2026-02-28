<?php
$pageTitle = 'Home - Musical Instruments Online Store';
require_once 'includes/header.php';

$featuredProducts = getProducts(['featured' => true], 8);
$allCats = getCategories();
$latestProducts = getProducts([], 8);
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center text-white">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <span class="hero-badge mb-3"><i class="fas fa-guitar me-1"></i> UK's Premier Music Store</span>
                <h1 class="display-4 fw-bold mt-3 mb-3">Find Your Perfect <br><span class="text-warning">Sound</span></h1>
                <p class="lead text-light opacity-75 mb-4">Discover thousands of musical instruments, accessories and digital sheet music. From beginner to professional — we have it all.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="shop.php" class="btn btn-warning btn-lg px-4"><i class="fas fa-store me-2"></i>Shop Now</a>
                    <a href="shop.php?category=digital-sheet-music" class="btn btn-outline-light btn-lg px-4"><i class="fas fa-music me-2"></i>Sheet Music</a>
                </div>
                <div class="row mt-5 g-3">
                    <div class="col-4 text-center">
                        <div class="fs-2 fw-bold text-warning">5000+</div>
                        <small class="text-light opacity-75">Products</small>
                    </div>
                    <div class="col-4 text-center">
                        <div class="fs-2 fw-bold text-warning">50+</div>
                        <small class="text-light opacity-75">Brands</small>
                    </div>
                    <div class="col-4 text-center">
                        <div class="fs-2 fw-bold text-warning">Free</div>
                        <small class="text-light opacity-75">Shipping £100+</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center d-none d-lg-block">
                <div style="font-size:12rem;opacity:0.15;line-height:1;position:absolute;right:0;top:50%;transform:translateY(-50%);">🎸</div>
                <div style="font-size:8rem;opacity:0.2;position:absolute;right:100px;top:30%;">🎹</div>
                <div class="position-relative" style="z-index:1;">
                    <i class="fas fa-guitar" style="font-size:8rem;color:rgba(251,191,36,0.7);"></i>
                    <i class="fas fa-drum ms-5" style="font-size:5rem;color:rgba(27,86,219,0.7);"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Bar -->
<section class="bg-primary text-white py-3">
    <div class="container">
        <div class="row text-center g-3">
            <div class="col-md-3"><i class="fas fa-truck me-2"></i><strong>Free Shipping</strong> <span class="opacity-75">on orders £100+</span></div>
            <div class="col-md-3"><i class="fas fa-undo me-2"></i><strong>30-Day Returns</strong> <span class="opacity-75">Easy returns</span></div>
            <div class="col-md-3"><i class="fas fa-headset me-2"></i><strong>Expert Support</strong> <span class="opacity-75">Mon-Sat 9-7</span></div>
            <div class="col-md-3"><i class="fas fa-shield-alt me-2"></i><strong>Secure Payment</strong> <span class="opacity-75">SSL Encrypted</span></div>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="container my-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold">Shop By Category</h2>
        <p class="text-muted">Explore our wide range of musical instruments</p>
    </div>
    <div class="row g-3">
        <?php
        $catIcons = ['guitars'=>'fa-guitar','keyboards'=>'fa-piano','drums-percussion'=>'fa-drum',
                     'wind-instruments'=>'fa-saxophone','string-instruments'=>'fa-violin',
                     'accessories'=>'fa-box','digital-sheet-music'=>'fa-file-pdf'];
        $catColors = ['#ff6b6b','#4ecdc4','#45b7d1','#f7dc6f','#bb8fce','#82e0aa','#f0b27a'];
        foreach($allCats as $i => $cat):
            $color = $catColors[$i % count($catColors)];
            $icon = $catIcons[$cat['slug']] ?? 'fa-music';
        ?>
        <div class="col-6 col-md-3">
            <a href="shop.php?category=<?= $cat['slug'] ?>" class="text-decoration-none">
                <div class="category-card" style="background: linear-gradient(135deg, <?= $color ?>, <?= $color ?>88);">
                    <div class="cat-icon"><i class="fas <?= $icon ?>"></i></div>
                    <div class="cat-overlay">
                        <h6 class="text-white mb-0 fw-bold"><?= sanitize($cat['name']) ?></h6>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Products -->
<?php if(!empty($featuredProducts)): ?>
<section class="bg-light py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Featured Products</h2>
                <p class="text-muted mb-0">Handpicked instruments for every musician</p>
            </div>
            <a href="shop.php?featured=1" class="btn btn-outline-primary">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach($featuredProducts as $product): ?>
            <div class="col-6 col-md-3">
                <?php include 'includes/product-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Latest Products -->
<section class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">New Arrivals</h2>
            <p class="text-muted mb-0">The latest additions to our collection</p>
        </div>
        <a href="shop.php" class="btn btn-outline-primary">View All <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-4">
        <?php foreach($latestProducts as $product): ?>
        <div class="col-6 col-md-3">
            <?php include 'includes/product-card.php'; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Promo Banner -->
<section class="bg-dark text-white py-5 my-3">
    <div class="container text-center">
        <h2 class="fw-bold mb-2"><i class="fas fa-music text-warning me-2"></i>Download Sheet Music Instantly</h2>
        <p class="lead text-muted mb-4">Thousands of digital scores available for immediate download after purchase</p>
        <a href="shop.php?category=digital-sheet-music" class="btn btn-warning btn-lg px-5">Browse Sheet Music</a>
    </div>
</section>

<button id="scrollTop" title="Scroll to top"><i class="fas fa-chevron-up"></i></button>
<script>const siteUrl = '<?= SITE_URL ?>';</script>
<?php require_once 'includes/footer.php'; ?>
