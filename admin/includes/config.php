<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'melody_masters');

// Site Configuration
define('SITE_NAME', 'Melody Masters');
define('SITE_URL', 'http://localhost/melody-masters');
define('FREE_SHIPPING_THRESHOLD', 100.00);
define('SHIPPING_COST', 9.99);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
