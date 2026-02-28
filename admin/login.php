<?php
require_once 'includes/functions.php';
if (isLoggedIn()) redirect(SITE_URL . '/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        $user = dbFetch("SELECT * FROM users WHERE email = ?", [$email]);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            mergeSessionCartToDB($user['id']);
            flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            $redirect = $_SESSION['redirect_after_login'] ?? SITE_URL . '/index.php';
            unset($_SESSION['redirect_after_login']);
            redirect($redirect);
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-music text-primary fa-2x mb-2"></i>
                        <h3 class="fw-bold">Login</h3>
                        <p class="text-muted small">Welcome back to Melody Masters</p>
                    </div>
                    <?php if($error): ?>
                    <div class="alert alert-danger"><?= sanitize($error) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required value="<?= sanitize($_POST['email'] ?? '') ?>" placeholder="your@email.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg">Login</button>
                    </form>
                    <hr>
                    <div class="text-center">
                        <p class="text-muted mb-0">Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                    <div class="mt-3 p-3 bg-light rounded small text-muted">
                        <strong>Demo accounts:</strong><br>
                        Admin: admin@melodymaster.com / password<br>
                        Customer: john@example.com / password
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>const siteUrl = '<?= SITE_URL ?>';</script>
<?php require_once 'includes/footer.php'; ?>
