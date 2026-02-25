<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    if (empty($username))
        $errors[] = "Username is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "Valid email is required.";
    if (empty($password) || strlen($password) < 6)
        $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm_password)
        $errors[] = "Passwords do not match.";

    // Check for existing users
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username or email already exists.";
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);

            // Log in the user automatically
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['role'] = 'customer';
            $_SESSION['username'] = $username;

            set_flash_message('success', 'Registration successful! Welcome to Melody Masters.');
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "A database error occurred during registration. Please try again.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="form-container">
    <h2 class="text-center">Create an Account</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="margin-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li>
                        <?php echo h($error); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control"
                value="<?php echo isset($username) ? h($username) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control"
                value="<?php echo isset($email) ? h($email) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Register</button>
    </form>

    <div class="form-footer">
        Already have an account? <a href="login.php">Sign In</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>