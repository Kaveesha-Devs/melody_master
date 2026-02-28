<?php
require_once 'includes/functions.php';
if (isLoggedIn()) redirect(SITE_URL . '/index.php');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($fullName)) $errors[] = 'Full name is required.';
    if (empty($username) || !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) $errors[] = 'Username must be 3-20 characters (letters, numbers, underscore).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    
    if (empty($errors)) {
        $exists = dbFetch("SELECT id FROM users WHERE email = ? OR username = ?", [$email, $username]);
        if ($exists) {
            $errors[] = 'Email or username already taken.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $userId = dbInsert("INSERT INTO users (full_name, username, email, password) VALUES (?,?,?,?)", [$fullName, $username, $email, $hashed]);
            $_SESSION['user_id'] = $userId;
            $_SESSION['role'] = 'customer';
            $_SESSION['username'] = $username;
            mergeSessionCartToDB($userId);
            flash('success', 'Account created! Welcome to Melody Masters, ' . $fullName . '!');
            redirect(SITE_URL . '/index.php');
        }
    }
}

$pageTitle = 'Register';
require_once 'includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus text-primary fa-2x mb-2"></i>
                        <h3 class="fw-bold">Create Account</h3>
                        <p class="text-muted small">Join Melody Masters today</p>
                    </div>
                    <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3"><label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required value="<?= sanitize($_POST['full_name'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required value="<?= sanitize($_POST['username'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required value="<?= sanitize($_POST['email'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6"></div>
                        <div class="mb-3"><label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required></div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg">Create Account</button>
                    </form>
                    <hr>
                    <div class="text-center"><p class="text-muted mb-0">Already have an account? <a href="login.php">Login here</a></p></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>const siteUrl = '<?= SITE_URL ?>';</script>
<?php require_once 'includes/footer.php'; ?>
