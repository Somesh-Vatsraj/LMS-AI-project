<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $settings_to_update = [
        'site_name' => trim($_POST['site_name'] ?? ''),
        'support_email' => trim($_POST['support_email'] ?? ''),
        'maintenance_mode' => trim($_POST['maintenance_mode'] ?? '0')
    ];

    $stmt = $pdo->prepare("INSERT INTO site_settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");

    $pdo->beginTransaction();
    try {
        foreach ($settings_to_update as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        $pdo->commit();
        create_audit_log($pdo, $_SESSION['user_id'], 'Settings Update', 'Updated global site settings');
        set_flash_message('success', 'Site settings updated successfully.');
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash_message('error', 'Error updating settings.');
    }

    redirect('/admin/settings.php');
}

// Fetch current settings
$stmt = $pdo->query("SELECT key_name, key_value FROM site_settings");
$raw_settings = $stmt->fetchAll();

$settings = [
    'site_name' => 'LMS AI Platform',
    'support_email' => 'support@lms.com',
    'maintenance_mode' => '0'
]; // Defaults

foreach ($raw_settings as $row) {
    $settings[$row['key_name']] = $row['key_value'];
}

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>Site Settings</h2>
        <hr style="border: 1px solid var(--border-color); margin: 15px 0;">

        <form method="POST" action="settings.php" style="max-width: 600px;">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <div class="form-group">
                <label>Platform Name</label>
                <input type="text" name="site_name" value="<?= h($settings['site_name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Support Email</label>
                <input type="email" name="support_email" value="<?= h($settings['support_email']) ?>" required>
            </div>

            <div class="form-group">
                <label>Maintenance Mode</label>
                <select name="maintenance_mode">
                    <option value="0" <?= $settings['maintenance_mode'] === '0' ? 'selected' : '' ?>>Disabled - Site is Live</option>
                    <option value="1" <?= $settings['maintenance_mode'] === '1' ? 'selected' : '' ?>>Enabled - Block access to non-admins</option>
                </select>
                <small style="color: gray;">(UI representation only; requires middleware implementation to enforce fully)</small>
            </div>

            <button type="submit" class="btn btn-primary mt-2">Save Settings</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>