<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

require_login();

$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$order_id = isset($_GET['order']) ? (int) $_GET['order'] : 0;
$user_id = $_SESSION['user_id'];

if (!$product_id || !$order_id) {
    die("Invalid request.");
}

// Verify purchase
$stmt = $pdo->prepare("
    SELECT dp.file_path, p.name 
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN digital_products dp ON oi.product_id = dp.product_id
    JOIN products p ON dp.product_id = p.id
    WHERE o.id = ? AND o.user_id = ? AND oi.product_id = ? AND o.status IN ('processing', 'shipped', 'completed')
");
$stmt->execute([$order_id, $user_id, $product_id]);
$download = $stmt->fetch();

if (!$download) {
    die("You do not have permission to download this file or the order is not complete.");
}

$file_path = $download['file_path'];

if (!file_exists($file_path)) {
    die("File not found on server.");
}

// Determine file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_path);
finfo_close($finfo);

// Force download
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="MelodyMasters_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $download['name']) . '.' . pathinfo($file_path, PATHINFO_EXTENSION) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

readfile($file_path);
exit;
?>