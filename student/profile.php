<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    redirect('/login.php');
}

$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {
        set_flash_message('error', 'Name cannot be empty.');
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->execute([$name, $_SESSION['user_id']]);

        $_SESSION['user_name'] = $name; // Update session data
        create_audit_log($pdo, $_SESSION['user_id'], 'Profile Update', 'Updated display name');

        set_flash_message('success', 'Profile updated successfully.');
        redirect('/student/profile.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/student_sidebar.php'; ?>

    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>My Profile</h2>
        <hr style="border: 1px solid var(--border-color); margin: 15px 0;">

        <form method="POST" action="profile.php" style="max-width: 500px;">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <div class="form-group">
                <label>Email Address (Cannot be changed)</label>
                <input type="email" value="<?= h($user['email']) ?>" disabled style="background: var(--bg-color);">
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= h($user['name']) ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>