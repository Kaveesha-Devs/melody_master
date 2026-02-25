<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Prepare filters
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$sort_option = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$sql = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($category_filter) {
    // If it's an exact match on name (for simple URLs) or we can match ID
    if (is_numeric($category_filter)) {
        $sql .= " AND (p.category_id = ? OR c.parent_id = ?)";
        $params[] = $category_filter;
        $params[] = $category_filter;
    } else {
        $sql .= " AND c.name LIKE ?";
        $params[] = "%" . $category_filter . "%";
    }
}

if ($search_query) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)";
    $params[] = "%{$search_query}%";
    $params[] = "%{$search_query}%";
    $params[] = "%{$search_query}%";
}

// Sorting
switch ($sort_option) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY p.name DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch all categories for sidebar
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

require_once 'includes/header.php';
?>

<div style="display: flex; gap: 2rem; align-items: flex-start;">
    
    <!-- Sidebar Filters -->
    <aside style="width: 250px; flex-shrink: 0; position: sticky; top: 100px;">
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--border-radius); padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);">Search</h3>
            <form action="shop.php" method="GET">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo h($search_query); ?>">
                </div>
                <!-- Preserve other filters -->
                <?php if ($category_filter): ?><input type="hidden" name="category" value="<?php echo h($category_filter); ?>"><?php endif; ?>
                <?php if ($sort_option !== 'newest'): ?><input type="hidden" name="sort" value="<?php echo h($sort_option); ?>"><?php endif; ?>
                <button type="submit" class="btn btn-primary btn-block" style="padding: 0.5rem;">Search</button>
            </form>
        </div>
        
        <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--border-radius); padding: 1.5rem;">
            <h3 style="margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);">Categories</h3>
            <ul style="list-style: none;">
                <li style="margin-bottom: 0.5rem;">
                    <a href="shop.php" style="<?php echo empty($category_filter) ? 'color: var(--primary-color); font-weight: bold;' : ''; ?>">All Products</a>
                </li>
                <?php foreach ($categories as $cat): ?>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="shop.php?category=<?php echo $cat['id']; ?>&sort=<?php echo h($sort_option); ?>" style="<?php echo ($category_filter == $cat['id'] || strcasecmp($category_filter, $cat['name']) === 0) ? 'color: var(--primary-color); font-weight: bold;' : ''; ?>">
                            <?php echo h($cat['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>
    
    <!-- Main Content -->
    <div style="flex: 1;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: var(--surface); padding: 1rem 1.5rem; border-radius: var(--border-radius); border: 1px solid var(--border);">
            <h2 style="margin: 0; font-size: 1.5rem;">
                <?php 
                if ($search_query) {
                    echo "Search Results for '" . h($search_query) . "'";
                } elseif ($category_filter) {
                    echo "Category Products";
                } else {
                    echo "All Products";
                }
                ?>
                <span style="font-size: 1rem; color: var(--text-secondary); font-family: 'Outfit'; font-weight: normal;">(<?php echo count($products); ?> items)</span>
            </h2>
            
            <form action="shop.php" method="GET" style="display: flex; align-items: center; gap: 0.5rem;">
                <?php if ($search_query): ?><input type="hidden" name="search" value="<?php echo h($search_query); ?>"><?php endif; ?>
                <?php if ($category_filter): ?><input type="hidden" name="category" value="<?php echo h($category_filter); ?>"><?php endif; ?>
                
                <label for="sort" style="color: var(--text-secondary);">Sort by:</label>
                <select name="sort" id="sort" class="form-control" style="width: auto; padding: 0.4rem 1rem;" onchange="this.form.submit()">
                    <option value="newest" <?php echo $sort_option === 'newest' ? 'selected' : ''; ?>>Newest Arrivals</option>
                    <option value="price_asc" <?php echo $sort_option === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo $sort_option === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name_asc" <?php echo $sort_option === 'name_asc' ? 'selected' : ''; ?>>Name: A-Z</option>
                    <option value="name_desc" <?php echo $sort_option === 'name_desc' ? 'selected' : ''; ?>>Name: Z-A</option>
                </select>
            </form>
        </div>
        
        <?php if (empty($products)): ?>
            <div style="text-align: center; padding: 4rem 2rem; background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border);">
                <h3>No products found</h3>
                <p style="color: var(--text-secondary); margin-top: 1rem;">Try adjusting your filters or search terms.</p>
                <a href="shop.php" class="btn btn-primary" style="margin-top: 1.5rem;">Clear Filters</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
                <?php foreach ($products as $product): ?>
                    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--border-radius); overflow: hidden; transition: transform 0.3s; display: flex; flex-direction: column;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <a href="product.php?id=<?php echo $product['id']; ?>" style="display: block; height: 250px; background: #222; position: relative;">
                            <?php if ($product['image_url']): ?>
                                <img src="<?php echo h($product['image_url']); ?>" alt="<?php echo h($product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-secondary); font-family: 'Playfair Display', serif; font-size: 1.5rem; font-style: italic;">No Image</div>
                            <?php endif; ?>
                            
                            <?php if ($product['is_digital']): ?>
                                <span style="position: absolute; top: 1rem; right: 1rem; background: rgba(0,0,0,0.7); color: var(--primary-color); padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold; border: 1px solid var(--primary-color);">Digital</span>
                            <?php endif; ?>
                            
                            <?php if (!$product['is_digital'] && $product['stock_quantity'] <= 0): ?>
                                <span style="position: absolute; top: 1rem; left: 1rem; background: rgba(207, 102, 121, 0.9); color: #fff; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Out of Stock</span>
                            <?php endif; ?>
                        </a>
                        <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                            <div style="font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                                <span><?php echo h($product['category_name']); ?></span>
                                <span><?php echo h($product['brand']); ?></span>
                            </div>
                            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: var(--text-primary);">
                                <a href="product.php?id=<?php echo $product['id']; ?>" style="color: inherit;"><?php echo h($product['name']); ?></a>
                            </h3>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 1rem;">
                                <div style="font-size: 1.25rem; font-weight: bold; color: var(--primary-color);">
                                    <?php echo format_price($product['price']); ?>
                                </div>
                                <form action="cart.php" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;" <?php echo (!$product['is_digital'] && $product['stock_quantity'] <= 0) ? 'disabled style="background: #555; cursor: not-allowed;"' : ''; ?>>
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
