<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    set_flash_message('error', 'Unauthorized access. Admin area only.');
    redirect('/login.php');
}

// Handle Mark Notification as Read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    verify_csrf_token();
    $notif_id = (int)$_POST['notif_id'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $_SESSION['user_id']]);
    redirect('/admin/index.php');
}

// Fetch Unread Notifications for Admin
$stmt = $pdo->prepare("SELECT id, message, created_at FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Get System Statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM courses");
$total_courses = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM enrollments");
$total_enrollments = $stmt->fetchColumn();

// Get recent audit logs
$stmt = $pdo->query("
    SELECT a.*, u.name as user_name 
    FROM audit_logs a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC LIMIT 5
");
$recent_logs = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="dashboard-content" style="flex: 3; min-width: 300px;">
        <h2>Admin Dashboard</h2>
        <p>System Overview</p>

        <!-- NOTIFICATION AREA -->
        <?php if (!empty($notifications)): ?>
            <div style="margin: 20px 0;">
                <?php foreach ($notifications as $notif): ?>
                    <div class="alert" style="background: #e1effe; border-left: 4px solid var(--primary-color); display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; margin-bottom: 10px; border-radius: var(--radius);">
                        <div>
                            <strong style="color: #1e429f;">System Notification:</strong>
                            <span style="color: #3f83f8; margin-left: 5px;"><?= h($notif['message']) ?></span>
                            <div style="font-size: 0.8rem; color: #76a9fa; margin-top: 4px;"><?= date('d M Y, H:i', strtotime($notif['created_at'])) ?></div>
                        </div>
                        <form method="POST" action="index.php" style="margin: 0;">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="notif_id" value="<?= h($notif['id']) ?>">
                            <button type="submit" name="mark_read" style="background: transparent; border: none; font-size: 1.2rem; cursor: pointer; color: #1e429f;" title="Mark as Read">✖</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="grid mt-2">
            <div class="card text-center">
                <h3>Total Users</h3>
                <h2 style="font-size: 2.5rem; color: var(--primary-color);"><?= h($total_users) ?></h2>
            </div>
            <div class="card text-center">
                <h3>Total Courses</h3>
                <h2 style="font-size: 2.5rem; color: var(--success);"><?= h($total_courses) ?></h2>
            </div>
            <div class="card text-center">
                <h3>Total Enrollments</h3>
                <h2 style="font-size: 2.5rem; color: #ff9800;"><?= h($total_enrollments) ?></h2>
            </div>
        </div>

        <div class="card mt-2">
            <h3>Recent Activity Logs</h3>
            <table style="width: 100%; text-align: left; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color);">
                        <th style="padding: 10px;">Timestamp</th>
                        <th style="padding: 10px;">User</th>
                        <th style="padding: 10px;">Action</th>
                        <th style="padding: 10px;">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_logs as $log): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 10px; font-size: 0.9rem;"><?= date('M d, H:i', strtotime($log['created_at'])) ?></td>
                            <td style="padding: 10px; font-size: 0.9rem;"><?= h($log['user_name'] ?? 'System / Guest') ?></td>
                            <td style="padding: 10px;"><strong><?= h($log['action']) ?></strong></td>
                            <td style="padding: 10px; font-size: 0.9rem;"><?= h($log['details']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="mt-2 text-center">
                <a href="audit-logs.php" class="btn btn-outline">View All Logs</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>