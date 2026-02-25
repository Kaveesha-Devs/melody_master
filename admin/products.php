<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_staff();

$upload_dir = '../uploads';
$images_dir = '../assets/images';
if (!is_dir($upload_dir))
    mkdir($upload_dir, 0777, true);
if (!is_dir($images_dir))
    mkdir($images_dir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $category_id = (int) $_POST['category_id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = (float) $_POST['price'];
            $brand = trim($_POST['brand']);
            $is_digital = isset($_POST['is_digital']) ? 1 : 0;
            $stock_quantity = $is_digital ? 0 : (int) $_POST['stock_quantity'];

            $image_url = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('img_') . '.' . $ext;
                $dest = $images_dir . '/' . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $image_url = '/assets/images/' . $filename;
                }
            }

            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, brand, stock_quantity, is_digital, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$category_id, $name, $description, $price, $brand, $stock_quantity, $is_digital, $image_url]);
                $product_id = $pdo->lastInsertId();

                if ($is_digital) {
                    $download_limit = !empty($_POST['download_limit']) ? (int) $_POST['download_limit'] : null;
                    if (isset($_FILES['digital_file']) && $_FILES['digital_file']['error'] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['digital_file']['name'], PATHINFO_EXTENSION);
                        $filename = uniqid('file_') . '.' . $ext;
                        $dest = $upload_dir . '/' . $filename;
                        if (move_uploaded_file($_FILES['digital_file']['tmp_name'], $dest)) {
                            $stmt_dig = $pdo->prepare("INSERT INTO digital_products (product_id, file_path, download_limit) VALUES (?, ?, ?)");
                            $stmt_dig->execute([$product_id, $dest, $download_limit]);
                        }
                    }
                }

                $pdo->commit();
                set_flash_message('success', 'Product added successfully!');
            } catch (Exception $e) {
                $pdo->rollBack();
                set_flash_message('error', 'Error adding product: ' . $e->getMessage());
            }

        } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message('success', 'Product deleted successfully!');
        }
    }
    header("Location: products.php");
    exit();
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC")->fetchAll();

require_once '../includes/header.php';
?>

<div style="display: flex; gap: 2rem;">
    <!-- Sidebar -->
    <div style="width: 250px; flex-shrink: 0;">
        <div
            style="background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border); padding: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Admin Menu</h3>
            <ul style="list-style: none;">
                <li style="margin-bottom: 0.5rem;"><a href="index.php">Dashboard</a></li>
                <li style="margin-bottom: 0.5rem;"><a href="categories.php">Categories</a></li>
                <li style="margin-bottom: 0.5rem;"><a href="products.php"
                        style="color: var(--primary-color);">Products</a></li>
                <li style="margin-bottom: 0.5rem;"><a href="orders.php">Orders</a></li>
            </ul>
        </div>
    </div>

    <div style="flex: 1;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Manage Products</h2>
        </div>

        <?php display_flash_message(); ?>

        <div
            style="background: var(--surface); padding: 2rem; border-radius: var(--border-radius); border: 1px solid var(--border); margin-bottom: 2rem;">
            <h3>Add New Product</h3>
            <form action="products.php" method="POST" enctype="multipart/form-data" style="margin-top: 1rem;">
                <input type="hidden" name="action" value="add">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo h($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (£)</label>
                        <input type="number" step="0.01" id="price" name="price" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="brand">Brand</label>
                        <input type="text" id="brand" name="brand" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="image">Product Image (Optional)</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    </div>

                    <div class="form-group"
                        style="display: flex; align-items: center; gap: 0.5rem; padding-top: 1.5rem;">
                        <input type="checkbox" id="is_digital" name="is_digital" value="1"
                            onchange="toggleDigitalFields()">
                        <label for="is_digital" style="margin: 0; color: var(--primary-color); font-weight: bold;">This
                            is a Digital Product (Sheet Music, Software)</label>
                    </div>
                </div>

                <div id="physical_fields"
                    style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" value="0">
                    </div>
                </div>

                <div id="digital_fields"
                    style="display: none; padding: 1.5rem; background: rgba(0,0,0,0.2); border-radius: var(--border-radius); border: 1px dashed var(--primary-color); margin-top: 1.5rem;">
                    <h4 style="margin-bottom: 1rem;">Digital Product Details</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label for="digital_file">Upload File</label>
                            <input type="file" id="digital_file" name="digital_file" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="download_limit">Download Limit (leave blank for unlimited)</label>
                            <input type="number" id="download_limit" name="download_limit" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                </div>

                <button type="submit" class="btn btn-primary mt-2">Add Product</button>
            </form>
        </div>

        <div
            style="background: var(--surface); border-radius: var(--border-radius); overflow: hidden; border: 1px solid var(--border);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: rgba(0,0,0,0.2);">
                    <tr>
                        <th style="padding: 1rem; text-align: left;">Product</th>
                        <th style="padding: 1rem; text-align: left;">Category</th>
                        <th style="padding: 1rem; text-align: right;">Price</th>
                        <th style="padding: 1rem; text-align: right;">Stock</th>
                        <th style="padding: 1rem; text-align: center;">Type</th>
                        <th style="padding: 1rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="6" style="padding: 1rem; text-align: center; color: var(--text-secondary);">No
                                products found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr style="border-top: 1px solid var(--border);">
                                <td style="padding: 1rem;">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <?php if ($product['image_url']): ?>
                                            <img src="<?php echo h($product['image_url']); ?>" alt="Product"
                                                style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <div
                                                style="width: 40px; height: 40px; background: #333; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                                No Img</div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 500;">
                                                <?php echo h($product['name']); ?>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                                <?php echo h($product['brand']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1rem; color: var(--text-secondary);">
                                    <?php echo h($product['category_name']); ?>
                                </td>
                                <td style="padding: 1rem; text-align: right;">
                                    <?php echo format_price($product['price']); ?>
                                </td>
                                <td
                                    style="padding: 1rem; text-align: right; <?php echo $product['stock_quantity'] <= 5 && !$product['is_digital'] ? 'color: var(--error); font-weight: bold;' : ''; ?>">
                                    <?php echo $product['is_digital'] ? '&infin;' : $product['stock_quantity']; ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php if ($product['is_digital']): ?>
                                        <span
                                            style="background: rgba(212, 175, 55, 0.2); color: var(--primary-color); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Digital</span>
                                    <?php else: ?>
                                        <span
                                            style="background: rgba(255, 255, 255, 0.1); color: var(--text-secondary); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Physical</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; text-align: right;">
                                    <form action="products.php" method="POST" style="display:inline;"
                                        onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                        <button type="submit"
                                            style="background:none; border:none; color:var(--error); cursor:pointer; font-weight:bold; font-family:'Outfit'">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleDigitalFields() {
        const isDigital = document.getElementById('is_digital').checked;
        const physicalFields = document.getElementById('physical_fields');
        const digitalFields = document.getElementById('digital_fields');
        const digitalFileInput = document.getElementById('digital_file');

        if (isDigital) {
            physicalFields.style.display = 'none';
            digitalFields.style.display = 'block';
            digitalFileInput.required = true;
        } else {
            physicalFields.style.display = 'grid';
            digitalFields.style.display = 'none';
            digitalFileInput.required = false;
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>