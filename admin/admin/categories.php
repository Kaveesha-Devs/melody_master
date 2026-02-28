<?php
require_once '../includes/functions.php';
requireStaff();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $parentId = (int)($_POST['parent_id'] ?? 0) ?: null;
    
    if (empty($name)) { flash('error', 'Name required.'); } else {
        $slug = generateSlug($name);
        if ($id) {
            dbQuery("UPDATE categories SET name=?, slug=?, description=?, parent_id=? WHERE id=?", [$name, $slug, $description, $parentId, $id]);
            flash('success', 'Category updated!');
        } else {
            dbInsert("INSERT INTO categories (name, slug, description, parent_id) VALUES (?,?,?,?)", [$name, $slug, $description, $parentId]);
            flash('success', 'Category added!');
        }
    }
    redirect(SITE_URL . '/admin/categories.php');
}

$categories = dbFetchAll("SELECT c.*, p.name as parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id ORDER BY COALESCE(p.name, c.name), c.name");
$parentCats = dbFetchAll("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name");
$editCat = null;
if(isset($_GET['edit'])) $editCat = dbFetch("SELECT * FROM categories WHERE id=?", [(int)$_GET['edit']]);

$pageTitle = 'Categories';
require_once 'includes/admin-header.php';
?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><?= $editCat ? 'Edit Category' : 'Add Category' ?></h6>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $editCat['id'] ?? 0 ?>">
                    <div class="mb-3"><label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" required value="<?= sanitize($editCat['name'] ?? '') ?>"></div>
                    <div class="mb-3"><label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= sanitize($editCat['description'] ?? '') ?></textarea></div>
                    <div class="mb-3"><label class="form-label">Parent Category</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None (Top Level)</option>
                            <?php foreach($parentCats as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($editCat['parent_id'] ?? 0) == $p['id'] ? 'selected' : '' ?>><?= sanitize($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <button type="submit" class="btn btn-primary"><?= $editCat ? 'Update' : 'Add Category' ?></button>
                    <?php if($editCat): ?><a href="categories.php" class="btn btn-outline-secondary ms-2">Cancel</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light"><tr><th>Name</th><th>Parent</th><th>Slug</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($categories as $cat): ?>
                        <tr>
                            <td><?= $cat['parent_id'] ? '&nbsp;&nbsp;&rarr;&nbsp;' : '' ?><strong><?= sanitize($cat['name']) ?></strong></td>
                            <td><?= sanitize($cat['parent_name'] ?? '—') ?></td>
                            <td><code><?= sanitize($cat['slug']) ?></code></td>
                            <td><a href="?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
