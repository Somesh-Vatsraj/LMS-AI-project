<?php
require_once __DIR__ . '/config/app.php';

if (is_logged_in()) {
    redirect('/' . strtolower($_SESSION['user_role']) . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 3600);
    $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
    $stmt->execute([$token, $expires, $email]);


    if ($user && password_verify($password, $user['password'])) {
        login_user($pdo, $user);
        set_flash_message('success', 'Welcome back!');
        redirect('/' . strtolower($_SESSION['user_role']) . '/index.php');
    } else {
        set_flash_message('error', 'Invalid email or password.');
        create_audit_log($pdo, null, 'Failed Login', "Attempted email: $email");
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-container">
    <div class="card">
        <h2>Login</h2>
        <form method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <p class="mt-2"><a href="forgot-password.php">Forgot Password?</a> | <a href="register.php">Register</a></p>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>