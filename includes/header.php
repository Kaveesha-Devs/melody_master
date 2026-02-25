<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

// Determine base URL for assets/links (handles subdirectories like /admin/)
$base_url = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'admin') ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melody Masters Instrument Shop</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Playfair+Display:ital,wght@0,600;1,600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/style.css">
</head>

<body>
    <header class="main-header">
        <div class="header-container">
            <div class="logo">
                <a href="<?php echo $base_url; ?>index.php">Melody Masters</a>
            </div>

            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo $base_url; ?>index.php">Home</a></li>
                    <li><a href="<?php echo $base_url; ?>shop.php">Shop</a></li>
                    <?php if (is_logged_in()): ?>
                        <?php if (is_admin() || is_staff()): ?>
                            <li><a href="<?php echo $base_url; ?>admin/index.php" class="nav-highlight">Admin Panel</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo $base_url; ?>dashboard.php">My Account</a></li>
                        <li><a href="<?php echo $base_url; ?>logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $base_url; ?>login.php">Login</a></li>
                        <li><a href="<?php echo $base_url; ?>register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="header-actions">
                <a href="<?php echo $base_url; ?>cart.php" class="cart-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span class="cart-count">
                        <?php echo htmlspecialchars($cart_count); ?>
                    </span>
                </a>
            </div>
        </div>
    </header>

    <main class="main-content">