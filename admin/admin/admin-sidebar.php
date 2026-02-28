<?php // admin/admin-sidebar.php ?>
<aside class="admin-sidebar">
    <div class="admin-sidebar-brand">
        <i class="fas fa-music" style="color:var(--secondary);margin-right:8px;"></i>
        <span>Melody<strong>Masters</strong></span>
        <div style="font-size:11px;color:rgba(255,255,255,.4);margin-top:4px;">Admin Panel</div>
    </div>
    <ul class="admin-nav">
        <li class="admin-nav-section">Main</li>
        <li><a href="index.php" <?= basename($_SERVER['PHP_SELF'])=='index.php'?'class="active"':'' ?>><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="orders.php" <?= basename($_SERVER['PHP_SELF'])=='orders.php'?'class="active"':'' ?>><i class="fas fa-shopping-bag"></i> Orders</a></li>
        <li class="admin-nav-section">Catalog</li>
        <li><a href="products.php" <?= basename($_SERVER['PHP_SELF'])=='products.php'?'class="active"':'' ?>><i class="fas fa-guitar"></i> Products</a></li>
        <li><a href="add-product.php" <?= basename($_SERVER['PHP_SELF'])=='add-product.php'?'class="active"':'' ?>><i class="fas fa-plus-circle"></i> Add Product</a></li>
        <li><a href="categories.php" <?= basename($_SERVER['PHP_SELF'])=='categories.php'?'class="active"':'' ?>><i class="fas fa-tags"></i> Categories</a></li>
        <?php if (hasRole('admin')): ?>
        <li class="admin-nav-section">Administration</li>
        <li><a href="users.php" <?= basename($_SERVER['PHP_SELF'])=='users.php'?'class="active"':'' ?>><i class="fas fa-users"></i> Users</a></li>
        <li><a href="reviews.php" <?= basename($_SERVER['PHP_SELF'])=='reviews.php'?'class="active"':'' ?>><i class="fas fa-star"></i> Reviews</a></li>
        <?php endif; ?>
        <li class="admin-nav-section">Account</li>
        <li><a href="<?= SITE_URL ?>/index.php"><i class="fas fa-globe"></i> View Store</a></li>
        <li><a href="<?= SITE_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</aside>
