<?php
require_once __DIR__ . '/config/app.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $email = trim($_POST['email'] ?? '');

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $user['id']]);

        // Development mode: display token in flash message
        set_flash_message('success', "Password reset link created! (DEV: <a href='reset-password.php?token=$token'>Click here to reset</a>)");
    } else {
        set_flash_message('success', 'If your email exists, you will receive a reset link.');
    }
    redirect('/forgot-password.php');
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-container">
    <div class="card">
        <h2>Forgot Password</h2>
        <form method="POST" action="forgot-password.php">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Request Reset</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>