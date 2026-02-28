<?php
require_once 'includes/functions.php';
requireLogin();

$downloadId = (int)($_GET['id'] ?? 0);
$download = dbFetch("SELECT dd.*, dp.file_path, dp.file_name FROM digital_downloads dd JOIN digital_products dp ON dd.product_id = dp.product_id WHERE dd.id = ? AND dd.user_id = ?", [$downloadId, $_SESSION['user_id']]);

if (!$download) {
    flash('error', 'Download not found or access denied.');
    redirect(SITE_URL . '/account.php?tab=downloads');
}

if ($download['download_count'] >= $download['max_downloads']) {
    flash('error', 'Download limit reached for this product.');
    redirect(SITE_URL . '/account.php?tab=downloads');
}

// Increment download count
dbQuery("UPDATE digital_downloads SET download_count = download_count + 1 WHERE id = ?", [$downloadId]);

// In real app, would serve the actual file
// For demo, redirect with success message
flash('success', 'Download started: ' . $download['file_name']);
redirect(SITE_URL . '/account.php?tab=downloads');
