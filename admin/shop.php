<?php
$pageTitle = 'Shop';
require_once 'includes/header.php';

$search = $_GET['search'] ?? '';
$categorySlug = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$featured = isset($_GET['featured']) ? 1 : 0;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$category = null;
$categoryId = null;
if ($categorySlug) {
    $category = getCategory($categorySlug);
    $categoryId = $category['id'] ?? null;
}

$filters = [
    'search' => $search,
    'category_id' => $categoryId,
    'sort' => $sort,
    'min_price' => $minPrice ?: null,
    'max_price' => $maxPrice ?: null,
    'featured' => $featured ?: null,
];

$products = getProducts($filters, $perPage, $offset);
$totalProducts = getProductCount($filters);
$totalPages = ceil($totalProducts / $perPage);

$allCats = getCategories();
$brands = dbFetchAll("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND status='active' ORDER BY brand");
?>

<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li>
            <li class="breadcrumb-item <?= !$category ? 'active' : '' ?>"><a href="<?= SITE_URL ?>/shop.php">Shop</a></li>
            <?php if($category): ?><li class="breadcrumb-item active"><?= sanitize($category['name']) ?></li><?php endif; ?>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <form action="shop.php" method="GET" id="filterForm">
                <input type="hidden" name="category" value="<?= sanitize($categorySlug) ?>">
                <input type="hidden" name="sort" value="<?= sanitize($sort) ?>">
                
                <div class="filter-sidebar mb-4">
                    <h6 class="fw-bold mb-3">Categories</h6>
                    <div class="list-group list-group-flush">
                        <a href="shop.php" class="list-group-item list-group-item-action border-0 py-2 <?= !$categorySlug ? 'active' : '' ?>">
                            All Products
                        </a>
                        <?php foreach($allCats as $cat): ?>
                        <a href="shop.php?category=<?= $cat['slug'] ?>" 
                           class="list-group-item list-group-item-action border-0 py-2 <?= $categorySlug === $cat['slug'] ? 'active' : '' ?>">
                            <?= sanitize($cat['name']) ?>
                        </a>
                        <?php
                        $subs = getCategories($cat['id']);
                        foreach($subs as $sub): ?>
                        <a href="shop.php?category=<?= $sub['slug'] ?>"
                           class="list-group-item list-group-item-action border-0 py-1 ps-4 small <?= $categorySlug === $sub['slug'] ? 'active' : '' ?>">
                            — <?= sanitize($sub['name']) ?>
                        </a>
                        <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="filter-sidebar mb-4">
                    <h6 class="fw-bold mb-3">Price Range</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min £" value="<?= sanitize($minPrice) ?>">
                        </div>
                        <div class="col-6">
                            <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max £" value="<?= sanitize($maxPrice) ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">Apply Filter</button>
                </div>

                <div class="filter-sidebar">
                    <h6 class="fw-bold mb-3">Brands</h6>
                    <?php foreach($brands as $b): if(empty($b['brand'])) continue; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="brand" value="<?= sanitize($b['brand']) ?>"
                               id="brand_<?= sanitize($b['brand']) ?>" <?= ($_GET['brand'] ?? '') === $b['brand'] ? 'checked' : '' ?>
                               onchange="document.getElementById('filterForm').submit()">
                        <label class="form-check-label small" for="brand_<?= sanitize($b['brand']) ?>"><?= sanitize($b['brand']) ?></label>
                    </div>
                    <?php endforeach; ?>
                    <?php if(!empty($_GET['brand'])): ?>
                    <a href="shop.php?category=<?= sanitize($categorySlug) ?>" class="btn btn-link btn-sm p-0 mt-2">Clear brand filter</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h4 class="mb-0 fw-bold">
                        <?= $category ? sanitize($category['name']) : ($search ? "Search: \"$search\"" : 'All Products') ?>
                        <?php if($featured): ?><span class="badge bg-warning text-dark ms-2">Featured</span><?php endif; ?>
                    </h4>
                    <small class="text-muted"><?= $totalProducts ?> product(s) found</small>
                </div>
                <select class="form-select form-select-sm w-auto" onchange="window.location=this.value">
                    <?php
                    $base = "shop.php?" . http_build_query(array_filter(['category'=>$categorySlug,'search'=>$search,'min_price'=>$minPrice,'max_price'=>$maxPrice]));
                    $sorts = ['newest'=>'Newest First','price_asc'=>'Price: Low to High','price_desc'=>'Price: High to Low','name_asc'=>'Name A-Z'];
                    foreach($sorts as $val => $label): ?>
                    <option value="<?= $base ?>&sort=<?= $val ?>" <?= $sort === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if(empty($products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No products found</h5>
                <p class="text-muted">Try adjusting your filters or search terms</p>
                <a href="shop.php" class="btn btn-primary">View All Products</a>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach($products as $product): ?>
                <div class="col-6 col-md-4">
                    <?php include 'includes/product-card.php'; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
            <nav class="mt-5 d-flex justify-content-center">
                <ul class="pagination">
                    <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<button id="scrollTop"><i class="fas fa-chevron-up"></i></button>
<script>const siteUrl = '<?= SITE_URL ?>';</script>
<?php require_once 'includes/footer.php'; ?>
