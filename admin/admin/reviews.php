<?php
require_once '../includes/functions.php';
requireStaff();

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewId = (int)$_POST['review_id'];
    $action = $_POST['action'];
    if (in_array($action, ['approved','rejected'])) {
        dbQuery("UPDATE reviews SET status=? WHERE id=?", [$action, $reviewId]);
        flash('success', 'Review ' . $action . '!');
        redirect(SITE_URL . '/admin/reviews.php');
    }
}

$filter = $_GET['status'] ?? 'pending';
$reviews = dbFetchAll(
    "SELECT r.*, u.full_name, p.name as product_name FROM reviews r 
     JOIN users u ON r.user_id = u.id JOIN products p ON r.product_id = p.id 
     WHERE r.status = ? ORDER BY r.created_at DESC",
    [$filter]
);

$pageTitle = 'Reviews';
require_once 'includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Reviews</h4>
    <div class="d-flex gap-2">
        <?php foreach(['pending','approved','rejected'] as $s): ?>
        <a href="?status=<?= $s ?>" class="btn btn-sm <?= $filter===$s?'btn-primary':'btn-outline-secondary' ?>"><?= ucfirst($s) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php foreach($reviews as $r): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <strong><?= sanitize($r['full_name']) ?></strong> on <strong><?= sanitize($r['product_name']) ?></strong>
                <div><?= renderStars($r['rating']) ?> <small class="text-muted"><?= date('d M Y', strtotime($r['created_at'])) ?></small></div>
                <?php if($r['title']): ?><h6 class="mt-2"><?= sanitize($r['title']) ?></h6><?php endif; ?>
                <p class="mb-0 text-muted"><?= sanitize($r['comment']) ?></p>
            </div>
            <?php if($filter === 'pending'): ?>
            <div class="d-flex gap-2 ms-3">
                <form method="POST"><input type="hidden" name="review_id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="approved">
                    <button class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i>Approve</button></form>
                <form method="POST"><input type="hidden" name="review_id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="rejected">
                    <button class="btn btn-danger btn-sm"><i class="fas fa-times me-1"></i>Reject</button></form>
            </div>
            <?php else: ?>
            <span class="badge bg-<?= $filter==='approved'?'success':'danger' ?>"><?= $filter ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php if(empty($reviews)): ?><div class="text-center py-5 text-muted"><i class="fas fa-star fa-3x mb-3"></i><p>No <?= $filter ?> reviews</p></div><?php endif; ?>

<?php require_once 'includes/admin-footer.php'; ?>
