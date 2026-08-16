<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $themes = [
        'primary_color' => trim($_POST['primary_color'] ?? '#4361ee'),
        'logo_text' => trim($_POST['logo_text'] ?? 'LMS-AI')
    ];

    $stmt = $pdo->prepare("INSERT INTO appearance_settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");

    foreach ($themes as $key => $value) {
        $stmt->execute([$key, $value]);
    }

    create_audit_log($pdo, $_SESSION['user_id'], 'Appearance Update', 'Updated global theme UI');
    set_flash_message('success', 'UI settings updated! (Note: CSS requires dynamic DB variables in style.css to take full effect in production).');
    redirect('/admin/appearance.php');
}

$stmt = $pdo->query("SELECT key_name, key_value FROM appearance_settings");
$raw_appearance = $stmt->fetchAll();
$app_settings = ['primary_color' => '#4361ee', 'logo_text' => 'LMS-AI'];
foreach ($raw_appearance as $row) {
    $app_settings[$row['key_name']] = $row['key_value'];
}

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>Appearance Settings</h2>
        <hr style="border: 1px solid var(--border-color); margin: 15px 0;">

        <form method="POST" action="appearance.php" style="max-width: 400px;">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <div class="form-group">
                <label>Navbar Brand Text</label>
                <input type="text" name="logo_text" value="<?= h($app_settings['logo_text']) ?>" required>
            </div>

            <div class="form-group">
                <label>Primary Brand Color (Hex)</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="color" name="primary_color" value="<?= h($app_settings['primary_color']) ?>" style="width: 50px; height: 40px; padding: 2px;">
                    <span style="font-family: monospace;"><?= h($app_settings['primary_color']) ?></span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-2">Update Look & Feel</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>