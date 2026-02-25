<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];

            set_flash_message('success', "Welcome back, " . h($user['username']) . "!");

            // Redirect based on role
            if ($user['role'] === 'admin' || $user['role'] === 'staff') {
                header("Location: admin/index.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="form-container">
    <h2 class="text-center">Sign In</h2>
    <p class="text-center mb-2" style="color: var(--text-secondary);">Welcome back to Melody Masters</p>

    <?php display_flash_message(); ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo h($error); ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control"
                value="<?php echo isset($email) ? h($email) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Log In</button>
    </form>

    <div class="form-footer">
        Don't have an account? <a href="register.php">Create Account</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>