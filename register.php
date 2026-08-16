<?php
require_once __DIR__ . '/config/app.php';

if (is_logged_in()) {
    redirect('/student/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        set_flash_message('error', 'All fields are required.');
    } elseif ($password !== $confirm) {
        set_flash_message('error', 'Passwords do not match.');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            set_flash_message('error', 'Email is already registered.');
        } else {
            $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_pw]);
            $user_id = $pdo->lastInsertId();

            $stmtRole = $pdo->prepare("SELECT id FROM roles WHERE name = 'Student'");
            $stmtRole->execute();
            $role_id = $stmtRole->fetchColumn();

            $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $role_id]);

            create_audit_log($pdo, $user_id, 'Register', 'New student account created');

            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            login_user($pdo, $stmt->fetch());

            set_flash_message('success', 'Registration successful.');
            redirect('/student/index.php');
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-container">
    <div class="card">
        <h2>Register</h2>
        <form method="POST" action="register.php">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>