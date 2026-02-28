<?php
require_once 'includes/functions.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

switch($action) {
    case 'add':
        $success = addToCart($productId, $quantity);
        echo json_encode(['success' => $success, 'cart_count' => getCartCount(), 'message' => $success ? 'Added to cart' : 'Product not available']);
        break;
    case 'update':
        updateCartItem($productId, $quantity);
        echo json_encode(['success' => true, 'cart_count' => getCartCount()]);
        break;
    case 'remove':
        removeFromCart($productId);
        echo json_encode(['success' => true, 'cart_count' => getCartCount()]);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
