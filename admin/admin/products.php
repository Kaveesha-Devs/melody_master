<?php
require_once '../includes/functions.php';
requireStaff();

$action = $_GET['action'] ?? 'list';
$errors = [];

// Handle delete
if ($action === 'delete' && isAdmin()) {
    $id = (int)($_GET['id'] ?? 0);
    dbQuery("UPDATE products SET status='inactive' WHERE id = ?", [$id]);
    flash('success', 'Product deactivated.');
    redirect(SITE_URL . '/admin/products.php');
}

// Handle add/edit form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $brand = trim($_POST['brand'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $stock = (int)($_POST['stock_quantity'] ?? 0);
    $type = $_POST['product_type'] ?? 'physical';
    $description = trim($_POST['description'] ?? '');
    $specs = trim($_POST['specifications'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'active';

    if (empty($name)) $errors[] = 'Product name is required.';
    if ($categoryId <= 0) $errors[] = 'Category is required.';
    if ($price <= 0) $errors[] = 'Valid price is required.';

    // Handle image upload
    $imageName = $_POST['existing_image'] ?? '';
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = dirname(__DIR__) . '/images/products/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
            $errors[] = 'Invalid image format.';
        } else {
            $imageName = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }
    }

    if (empty($errors)) {
        $slug = generateSlug($name);
        // Ensure unique slug
        $existing = dbFetch("SELECT id FROM products WHERE slug = ? AND id != ?", [$slug, $id]);
        if ($existing) $slug .= '-' . time();

        if ($id) {
            dbQuery("UPDATE products SET name=?, slug=?, category_id=?, brand=?, price=?, sale_price=?, stock_quantity=?, product_type=?, description=?, specifications=?, image=?, featured=?, status=? WHERE id=?",
                [$name, $slug, $categoryId, $brand, $price, $salePrice, $stock, $type, $description, $specs, $imageName, $featured, $status, $id]);
            flash('success', 'Product updated!');
        } else {
            $newId = dbInsert("INSERT INTO products (name, slug, category_id, brand, price, sale_price, stock_quantity, product_type, description, specifications, image, featured, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [$name, $slug, $categoryId, $brand, $price, $salePrice, $stock, $type, $description, $specs, $imageName, $featured, $status]);
            flash('success', 'Product added!');
        }
        redirect(SITE_URL . '/admin/products.php');
    }
    $action = $id ? 'edit' : 'add';
}

$editProduct = null;
if ($action === 'edit') {
    $editProduct = dbFetch("SELECT * FROM products WHERE id = ?", [(int)($_GET['id'] ?? 0)]);
}

$categories = dbFetchAll("SELECT c.*, p.name as parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id ORDER BY COALESCE(p.name, c.name), c.name");

// List
$search = $_GET['search'] ?? '';
$filterCat = $_GET['category'] ?? '';
$products = dbFetchAll(
    "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id
     WHERE (? = '' OR p.name LIKE ? OR p.brand LIKE ?)
     AND (? = '' OR c.slug = ?)
     ORDER BY p.created_at DESC",
    [$search, "%$search%", "%$search%", $filterCat, $filterCat]
);

$pageTitle = 'Products';
require_once 'includes/admin-header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><?= $action === 'edit' ? 'Edit Product' : 'Add New Product' ?></h4>
    <a href="products.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<?php if(!empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $editProduct['id'] ?? 0 ?>">
    <input type="hidden" name="existing_image" value="<?= sanitize($editProduct['image'] ?? '') ?>">
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Basic Information</h6>
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Product Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?= sanitize($editProduct['name'] ?? $_POST['name'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($editProduct['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                                    <?= $cat['parent_name'] ? sanitize($cat['parent_name']) . ' → ' : '' ?><?= sanitize($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select></div>
                        <div class="col-md-6"><label class="form-label">Brand</label>
                            <input type="text" name="brand" class="form-control" value="<?= sanitize($editProduct['brand'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"><?= sanitize($editProduct['description'] ?? '') ?></textarea></div>
                        <div class="col-12"><label class="form-label">Specifications (JSON)</label>
                            <textarea name="specifications" class="form-control font-monospace" rows="4" placeholder='{"Key":"Value","Key2":"Value2"}'><?= sanitize($editProduct['specifications'] ?? '') ?></textarea></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Pricing & Inventory</h6>
                    <div class="mb-3"><label class="form-label">Price *</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" required value="<?= $editProduct['price'] ?? '' ?>"></div>
                    <div class="mb-3"><label class="form-label">Sale Price (optional)</label>
                        <input type="number" name="sale_price" class="form-control" step="0.01" min="0" value="<?= $editProduct['sale_price'] ?? '' ?>"></div>
                    <div class="mb-3"><label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control" min="0" value="<?= $editProduct['stock_quantity'] ?? 0 ?>"></div>
                    <div class="mb-3"><label class="form-label">Product Type</label>
                        <select name="product_type" class="form-select">
                            <option value="physical" <?= ($editProduct['product_type'] ?? 'physical') === 'physical' ? 'selected' : '' ?>>Physical</option>
                            <option value="digital" <?= ($editProduct['product_type'] ?? '') === 'digital' ? 'selected' : '' ?>>Digital</option>
                        </select></div>
                    <div class="mb-3"><label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($editProduct['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($editProduct['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select></div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="featured" id="featured" <?= ($editProduct['featured'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="featured">Featured Product</label>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Product Image</h6>
                    <?php if(!empty($editProduct['image'])): ?>
                    <img src="<?= SITE_URL ?>/images/products/<?= sanitize($editProduct['image']) ?>" class="img-thumbnail mb-2 w-100" style="max-height:150px;object-fit:cover" alt="">
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <small class="text-muted">JPG, PNG, WEBP accepted</small>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary px-4"><?= $action === 'edit' ? 'Update Product' : 'Add Product' ?></button>
        <a href="products.php" class="btn btn-outline-secondary ms-2">Cancel</a>
    </div>
</form>

<?php else: ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Products</h4>
    <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Product</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form class="row g-2">
            <div class="col-md-6"><input type="search" name="search" class="form-control" placeholder="Search products..." value="<?= sanitize($search) ?>"></div>
            <div class="col-md-4"><select name="category" class="form-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['slug'] ?>" <?= $filterCat === $cat['slug'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-light"><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach($products as $p): ?>
                <tr class="<?= $p['status'] === 'inactive' ? 'table-secondary' : '' ?>">
                    <td>
                        <div style="width:50px;height:50px;overflow:hidden;background:#f8f9fa;border-radius:6px;display:flex;align-items:center;justify-content:center">
                            <?php if($p['image']): ?><img src="<?= SITE_URL ?>/images/products/<?= sanitize($p['image']) ?>" style="width:100%;height:100%;object-fit:cover" onerror="this.style.display='none'"><?php else: ?><i class="fas fa-guitar text-muted"></i><?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <strong><?= sanitize($p['name']) ?></strong>
                        <div class="text-muted small"><?= sanitize($p['brand'] ?? '') ?></div>
                        <?php if($p['featured']): ?><span class="badge bg-warning text-dark small">Featured</span><?php endif; ?>
                        <?php if($p['product_type']==='digital'): ?><span class="badge bg-primary small">Digital</span><?php endif; ?>
                    </td>
                    <td><?= sanitize($p['cat_name']) ?></td>
                    <td>
                        <?php if($p['sale_price']): ?>
                        <span class="text-danger fw-bold"><?= formatPrice($p['sale_price']) ?></span><br>
                        <small class="text-muted text-decoration-line-through"><?= formatPrice($p['price']) ?></small>
                        <?php else: ?><?= formatPrice($p['price']) ?><?php endif; ?>
                    </td>
                    <td><span class="badge bg-<?= $p['stock_quantity'] <= 0 ? 'danger' : ($p['stock_quantity'] <= 5 ? 'warning text-dark' : 'success') ?>"><?= $p['stock_quantity'] ?></span></td>
                    <td><span class="badge bg-<?= $p['status']==='active'?'success':'secondary' ?>"><?= $p['status'] ?></span></td>
                    <td>
                        <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                        <?php if(isAdmin()): ?>
                        <a href="?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deactivate this product?')"><i class="fas fa-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($products)): ?><tr><td colspan="7" class="text-center text-muted py-4">No products found</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/admin-footer.php'; ?>
