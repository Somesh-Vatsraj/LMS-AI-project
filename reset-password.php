<?php
require_once __DIR__ . '/config/app.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    set_flash_message('error', 'Invalid token.');
    redirect('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $stmt->execute([$hashed, $user['id']]);
        set_flash_message('success', 'Password reset successfully. You can now login.');
        redirect('/login.php');
    } else {
        set_flash_message('error', 'Invalid or expired token.');
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-container">
    <div class="card">
        <h2>Reset Password</h2>
        <form method="POST" action="reset-password.php?token=<?= h($token) ?>">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Update Password</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>