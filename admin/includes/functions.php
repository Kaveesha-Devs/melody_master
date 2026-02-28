<?php
require_once __DIR__ . '/db.php';

// Auth functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return dbFetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isStaff() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

function requireStaff() {
    requireLogin();
    if (!isStaff()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

// Product functions
function getProducts($filters = [], $limit = 12, $offset = 0) {
    $where = ["p.status = 'active'"];
    $params = [];

    if (!empty($filters['category_id'])) {
        // Include subcategories
        $catIds = getCategoryWithChildren($filters['category_id']);
        $placeholders = implode(',', array_fill(0, count($catIds), '?'));
        $where[] = "p.category_id IN ($placeholders)";
        $params = array_merge($params, $catIds);
    }
    if (!empty($filters['search'])) {
        $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
    }
    if (!empty($filters['brand'])) {
        $where[] = "p.brand = ?";
        $params[] = $filters['brand'];
    }
    if (!empty($filters['min_price'])) {
        $where[] = "COALESCE(p.sale_price, p.price) >= ?";
        $params[] = $filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $where[] = "COALESCE(p.sale_price, p.price) <= ?";
        $params[] = $filters['max_price'];
    }
    if (!empty($filters['featured'])) {
        $where[] = "p.featured = 1";
    }

    $whereStr = implode(' AND ', $where);
    $sort = "p.created_at DESC";
    if (!empty($filters['sort'])) {
        switch ($filters['sort']) {
            case 'price_asc': $sort = "COALESCE(p.sale_price, p.price) ASC"; break;
            case 'price_desc': $sort = "COALESCE(p.sale_price, p.price) DESC"; break;
            case 'name_asc': $sort = "p.name ASC"; break;
            case 'newest': $sort = "p.created_at DESC"; break;
        }
    }

    $params[] = $limit;
    $params[] = $offset;
    return dbFetchAll(
        "SELECT p.*, c.name as category_name, 
         COALESCE(AVG(r.rating),0) as avg_rating, COUNT(r.id) as review_count
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN reviews r ON p.id = r.product_id AND r.status = 'approved'
         WHERE $whereStr GROUP BY p.id ORDER BY $sort LIMIT ? OFFSET ?",
        $params
    );
}

function getProductCount($filters = []) {
    $where = ["p.status = 'active'"];
    $params = [];
    if (!empty($filters['category_id'])) {
        $catIds = getCategoryWithChildren($filters['category_id']);
        $placeholders = implode(',', array_fill(0, count($catIds), '?'));
        $where[] = "p.category_id IN ($placeholders)";
        $params = array_merge($params, $catIds);
    }
    if (!empty($filters['search'])) {
        $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
    }
    $whereStr = implode(' AND ', $where);
    return dbFetch("SELECT COUNT(DISTINCT p.id) as cnt FROM products p WHERE $whereStr", $params)['cnt'];
}

function getProduct($id = null, $slug = null) {
    if ($id) {
        return dbFetch(
            "SELECT p.*, c.name as category_name, c.slug as category_slug 
             FROM products p LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.id = ? AND p.status = 'active'", [$id]);
    }
    return dbFetch(
        "SELECT p.*, c.name as category_name, c.slug as category_slug 
         FROM products p LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.slug = ? AND p.status = 'active'", [$slug]);
}

function getCategoryWithChildren($catId) {
    $ids = [$catId];
    $children = dbFetchAll("SELECT id FROM categories WHERE parent_id = ?", [$catId]);
    foreach ($children as $child) {
        $ids[] = $child['id'];
    }
    return $ids;
}

function getCategories($parentId = null) {
    if ($parentId === null) {
        return dbFetchAll("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name");
    }
    return dbFetchAll("SELECT * FROM categories WHERE parent_id = ? ORDER BY name", [$parentId]);
}

function getCategory($slug) {
    return dbFetch("SELECT * FROM categories WHERE slug = ?", [$slug]);
}

// Cart functions
function getCartItems($userId = null) {
    if ($userId) {
        // DB cart
        return dbFetchAll(
            "SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity, p.product_type,
             COALESCE(p.sale_price, p.price) as effective_price
             FROM cart c JOIN products p ON c.product_id = p.id
             WHERE c.user_id = ?", [$userId]);
    } else {
        // Session cart
        $cart = [];
        if (empty($_SESSION['cart'])) return $cart;
        foreach ($_SESSION['cart'] as $productId => $qty) {
            $product = dbFetch(
                "SELECT *, COALESCE(sale_price, price) as effective_price FROM products WHERE id = ? AND status = 'active'",
                [$productId]);
            if ($product) {
                $product['quantity'] = $qty;
                $cart[] = $product;
            }
        }
        return $cart;
    }
}

function getCartCount() {
    if (isLoggedIn()) {
        $result = dbFetch("SELECT SUM(quantity) as cnt FROM cart WHERE user_id = ?", [$_SESSION['user_id']]);
        return $result['cnt'] ?? 0;
    }
    if (empty($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}

function addToCart($productId, $quantity = 1) {
    $product = dbFetch("SELECT * FROM products WHERE id = ? AND status = 'active'", [$productId]);
    if (!$product) return false;

    if (isLoggedIn()) {
        $existing = dbFetch("SELECT * FROM cart WHERE user_id = ? AND product_id = ?", [$_SESSION['user_id'], $productId]);
        if ($existing) {
            $newQty = min($existing['quantity'] + $quantity, $product['stock_quantity']);
            dbQuery("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?",
                [$newQty, $_SESSION['user_id'], $productId]);
        } else {
            dbInsert("INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,?)",
                [$_SESSION['user_id'], $productId, min($quantity, $product['stock_quantity'])]);
        }
    } else {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $current = $_SESSION['cart'][$productId] ?? 0;
        $_SESSION['cart'][$productId] = min($current + $quantity, $product['stock_quantity']);
    }
    return true;
}

function updateCartItem($productId, $quantity) {
    if (isLoggedIn()) {
        if ($quantity <= 0) {
            dbQuery("DELETE FROM cart WHERE user_id = ? AND product_id = ?", [$_SESSION['user_id'], $productId]);
        } else {
            dbQuery("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?",
                [$quantity, $_SESSION['user_id'], $productId]);
        }
    } else {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }
}

function removeFromCart($productId) {
    updateCartItem($productId, 0);
}

function getCartTotal($items = null) {
    if ($items === null) {
        $items = getCartItems(isLoggedIn() ? $_SESSION['user_id'] : null);
    }
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['effective_price'] * $item['quantity'];
    }
    return $subtotal;
}

function calculateShipping($subtotal, $items = null) {
    // Check if all digital
    if ($items) {
        $allDigital = true;
        foreach ($items as $item) {
            if ($item['product_type'] !== 'digital') {
                $allDigital = false;
                break;
            }
        }
        if ($allDigital) return 0;
    }
    return $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
}

function mergeSessionCartToDB($userId) {
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $productId => $qty) {
            $existing = dbFetch("SELECT * FROM cart WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
            if ($existing) {
                dbQuery("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?",
                    [$qty, $userId, $productId]);
            } else {
                dbQuery("INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,?) ON DUPLICATE KEY UPDATE quantity = quantity + ?",
                    [$userId, $productId, $qty, $qty]);
            }
        }
        unset($_SESSION['cart']);
    }
}

// Order functions
function createOrder($userId, $cartItems, $shippingData, $paymentMethod) {
    $subtotal = getCartTotal($cartItems);
    $shipping = calculateShipping($subtotal, $cartItems);
    $total = $subtotal + $shipping;
    $orderNumber = 'MM-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    $orderId = dbInsert(
        "INSERT INTO orders (user_id, order_number, subtotal, shipping_cost, total, 
         shipping_name, shipping_address, shipping_city, shipping_postal, shipping_country, payment_method)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)",
        [$userId, $orderNumber, $subtotal, $shipping, $total,
         $shippingData['name'], $shippingData['address'], $shippingData['city'],
         $shippingData['postal'], $shippingData['country'], $paymentMethod]
    );

    foreach ($cartItems as $item) {
        dbInsert(
            "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) VALUES (?,?,?,?,?,?)",
            [$orderId, $item['product_id'] ?? $item['id'], $item['name'],
             $item['quantity'], $item['effective_price'],
             $item['effective_price'] * $item['quantity']]
        );
        // Decrease stock
        dbQuery("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?",
            [$item['quantity'], $item['product_id'] ?? $item['id']]);

        // Digital download entry
        if ($item['product_type'] === 'digital') {
            dbInsert(
                "INSERT INTO digital_downloads (user_id, order_id, product_id, max_downloads) VALUES (?,?,?,5)",
                [$userId, $orderId, $item['product_id'] ?? $item['id']]
            );
        }
    }

    // Mark as paid (demo)
    dbQuery("UPDATE orders SET payment_status = 'paid' WHERE id = ?", [$orderId]);

    // Clear cart
    dbQuery("DELETE FROM cart WHERE user_id = ?", [$userId]);

    return $orderId;
}

// Review functions
function canReview($userId, $productId) {
    // Check if user purchased product
    $order = dbFetch(
        "SELECT o.id FROM orders o 
         JOIN order_items oi ON o.id = oi.order_id 
         WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'
         LIMIT 1", [$userId, $productId]);
    if (!$order) return false;
    // Check not already reviewed
    $existing = dbFetch("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
    return !$existing;
}

function getProductReviews($productId) {
    return dbFetchAll(
        "SELECT r.*, u.username, u.full_name FROM reviews r 
         JOIN users u ON r.user_id = u.id 
         WHERE r.product_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC",
        [$productId]);
}

// Utility
function formatPrice($price) {
    return '£' . number_format($price, 2);
}

function sanitize($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function getFlash($type) {
    $msg = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $msg;
}

function generateSlug($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return trim($str, '-');
}

function getProductImage($image) {
    $path = SITE_URL . '/images/products/' . $image;
    return $path;
}

function renderStars($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<i class="fas fa-star text-warning"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
        } else {
            $html .= '<i class="far fa-star text-warning"></i>';
        }
    }
    return $html;
}
