<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_staff();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $parent_id = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;

            if (!empty($name)) {
                $stmt = $pdo->prepare("INSERT INTO categories (name, description, parent_id) VALUES (?, ?, ?)");
                $stmt->execute([$name, $description, $parent_id]);
                set_flash_message('success', 'Category added successfully!');
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message('success', 'Category deleted successfully!');
        }
    }
    header("Location: categories.php");
    exit();
}

$categories = $pdo->query("SELECT c.*, p.name as parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id ORDER BY p.name, c.name")->fetchAll();

require_once '../includes/header.php';
?>

<div style="display: flex; gap: 2rem;">
    <!-- Sidebar navigation for admin -->
    <div style="width: 250px; flex-shrink: 0;">
        <div
            style="background: var(--surface); border-radius: var(--border-radius); border: 1px solid var(--border); padding: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Admin Menu</h3>
            <ul style="list-style: none;">
                <li style="margin-bottom: 0.5rem;"><a href="index.php">Dashboard</a></li>
                <li style="margin-bottom: 0.5rem;"><a href="categories.php"
                        style="color: var(--primary-color);">Categories</a></li>
                <li style="margin-bottom: 0.5rem;"><a href="products.php">Products</a></li>
                <li style="margin-bottom: 0.5rem;"><a href="orders.php">Orders</a></li>
            </ul>
        </div>
    </div>

    <div style="flex: 1;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Manage Categories</h2>
        </div>

        <?php display_flash_message(); ?>

        <div
            style="background: var(--surface); padding: 2rem; border-radius: var(--border-radius); border: 1px solid var(--border); margin-bottom: 2rem;">
            <h3>Add New Category</h3>
            <form action="categories.php" method="POST" style="margin-top: 1rem;">
                <input type="hidden" name="action" value="add">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="name">Category Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="parent_id">Parent Category</label>
                        <select id="parent_id" name="parent_id" class="form-control">
                            <option value="">None (Top Level)</option>
                            <?php foreach ($categories as $cat): ?>
                                <!-- Only allow top level categories to be parents for simple nesting -->
                                <?php if (empty($cat['parent_id'])): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo h($cat['name']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add Category</button>
            </form>
        </div>

        <div
            style="background: var(--surface); border-radius: var(--border-radius); overflow: hidden; border: 1px solid var(--border);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: rgba(0,0,0,0.2);">
                    <tr>
                        <th style="padding: 1rem; text-align: left;">Name</th>
                        <th style="padding: 1rem; text-align: left;">Parent</th>
                        <th style="padding: 1rem; text-align: left;">Description</th>
                        <th style="padding: 1rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="4" style="padding: 1rem; text-align: center; color: var(--text-secondary);">No
                                categories found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <tr style="border-top: 1px solid var(--border);">
                                <td style="padding: 1rem; font-weight: 500;">
                                    <?php echo h($category['name']); ?>
                                </td>
                                <td style="padding: 1rem; color: var(--text-secondary);">
                                    <?php echo $category['parent_name'] ? h($category['parent_name']) : '<i>None</i>'; ?>
                                </td>
                                <td style="padding: 1rem; color: var(--text-secondary);">
                                    <?php echo h(substr($category['description'], 0, 50)) . (strlen($category['description']) > 50 ? '...' : ''); ?>
                                </td>
                                <td style="padding: 1rem; text-align: right;">
                                    <form action="categories.php" method="POST" style="display:inline;"
                                        onsubmit="return confirm('Are you sure? This will nullify the category of related products and subcategories.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
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

<?php require_once '../includes/footer.php'; ?>