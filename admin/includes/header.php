<?php
require_once __DIR__ . '/functions.php';
$cartCount = getCartCount();
$user = getCurrentUser();
$allCategories = getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' - ' : '' ?><?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/css/style.css" rel="stylesheet">
</head>
<body>
<!-- Top bar -->
<div class="topbar bg-dark text-white py-1">
    <div class="container d-flex justify-content-between align-items-center">
        <small><i class="fas fa-phone me-1"></i>+44 20 7946 0958 | <i class="fas fa-envelope me-1"></i>info@melodymaster.com</small>
        <small><?php if(isLoggedIn()): ?>
            Welcome, <strong><?= sanitize($user['full_name']) ?></strong> |
            <?php if(isStaff()): ?><a href="<?= SITE_URL ?>/admin/" class="text-warning">Admin Panel</a> | <?php endif; ?>
            <a href="<?= SITE_URL ?>/account.php" class="text-light">My Account</a> |
            <a href="<?= SITE_URL ?>/logout.php" class="text-light">Logout</a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/login.php" class="text-light">Login</a> |
            <a href="<?= SITE_URL ?>/register.php" class="text-light">Register</a>
        <?php endif; ?></small>
    </div>
</div>

<!-- Main Header -->
<header class="main-header shadow-sm sticky-top bg-white">
    <div class="container py-3">
        <div class="row align-items-center">
            <div class="col-md-3">
                <a href="<?= SITE_URL ?>/index.php" class="text-decoration-none">
                    <div class="logo">
                        <i class="fas fa-music text-primary fs-2"></i>
                        <div class="logo-text">
                            <span class="brand-name">Melody Masters</span>
                            <small class="brand-sub">Instrument Shop</small>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <form action="<?= SITE_URL ?>/shop.php" method="GET" class="d-flex">
                    <input type="search" name="search" class="form-control form-control-lg rounded-pill-start border-end-0"
                           placeholder="Search instruments, brands..." value="<?= sanitize($_GET['search'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary rounded-pill-end px-4">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-md-3 text-end">
                <a href="<?= SITE_URL ?>/cart.php" class="btn btn-outline-primary position-relative me-2">
                    <i class="fas fa-shopping-cart"></i> Cart
                    <?php if($cartCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $cartCount ?>
                    </span>
                    <?php endif; ?>
                </a>
                <?php if(!isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>/login.php" class="btn btn-primary"><i class="fas fa-user me-1"></i>Login</a>
                <?php else: ?>
                <a href="<?= SITE_URL ?>/account.php" class="btn btn-outline-secondary"><i class="fas fa-user me-1"></i>Account</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/index.php"><i class="fas fa-home me-1"></i>Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/shop.php"><i class="fas fa-store me-1"></i>All Products</a></li>
                    <?php foreach($allCategories as $cat): ?>
                    <?php $subs = getCategories($cat['id']); ?>
                    <?php if(!empty($subs)): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="<?= SITE_URL ?>/shop.php?category=<?= $cat['slug'] ?>" data-bs-toggle="dropdown">
                            <?= sanitize($cat['name']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>/shop.php?category=<?= $cat['slug'] ?>">All <?= sanitize($cat['name']) ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php foreach($subs as $sub): ?>
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>/shop.php?category=<?= $sub['slug'] ?>"><?= sanitize($sub['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>/shop.php?category=<?= $cat['slug'] ?>"><?= sanitize($cat['name']) ?></a>
                    </li>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <div class="d-flex">
                    <a href="<?= SITE_URL ?>/shop.php?featured=1" class="btn btn-warning btn-sm"><i class="fas fa-star me-1"></i>Featured</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Flash messages -->
<div class="container mt-3">
<?php foreach(['success','error','info','warning'] as $type):
    $msg = getFlash($type);
    if($msg): ?>
    <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert">
        <?= $msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; endforeach; ?>
</div>
