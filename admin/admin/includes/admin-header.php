<?php
require_once dirname(__DIR__) . '/../includes/functions.php';
requireStaff();
$adminUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' - ' : '' ?>Admin | Melody Masters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= SITE_URL ?>/admin/"><i class="fas fa-music me-2"></i>Melody Masters Admin</a>
        <div class="d-flex align-items-center gap-3">
            <a href="<?= SITE_URL ?>/index.php" class="text-light small" target="_blank"><i class="fas fa-external-link-alt me-1"></i>View Store</a>
            <span class="text-light small"><?= sanitize($adminUser['full_name']) ?></span>
            <a href="<?= SITE_URL ?>/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="d-flex" style="min-height:calc(100vh - 56px)">
    <!-- Sidebar -->
    <div class="admin-sidebar" style="width:220px;flex-shrink:0">
        <nav class="py-3">
            <?php $curr = basename($_SERVER['PHP_SELF']); ?>
            <a href="index.php" class="nav-link <?= $curr==='index.php'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="products.php" class="nav-link <?= $curr==='products.php'?'active':'' ?>"><i class="fas fa-guitar"></i> Products</a>
            <a href="categories.php" class="nav-link <?= $curr==='categories.php'?'active':'' ?>"><i class="fas fa-tags"></i> Categories</a>
            <a href="orders.php" class="nav-link <?= $curr==='orders.php'?'active':'' ?>"><i class="fas fa-shopping-bag"></i> Orders</a>
            <a href="reviews.php" class="nav-link <?= $curr==='reviews.php'?'active':'' ?>"><i class="fas fa-star"></i> Reviews</a>
            <?php if(isAdmin()): ?>
            <a href="users.php" class="nav-link <?= $curr==='users.php'?'active':'' ?>"><i class="fas fa-users"></i> Users</a>
            <?php endif; ?>
            <hr class="border-secondary mx-3">
            <a href="<?= SITE_URL ?>/index.php" class="nav-link"><i class="fas fa-store"></i> View Store</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 p-4">
        <!-- Flash messages -->
        <?php foreach(['success','error','info','warning'] as $type):
            $msg = getFlash($type);
            if($msg): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show">
            <?= $msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; endforeach; ?>
