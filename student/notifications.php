<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    redirect('/login.php');
}

// Mark all as read feature
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    verify_csrf_token();
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    set_flash_message('success', 'All notifications marked as read.');
    redirect('/student/notifications.php');
}

// Fetch all notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/student_sidebar.php'; ?>

    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Notification History</h2>
            <form method="POST" action="notifications.php" style="margin: 0;">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <button type="submit" name="mark_all_read" class="btn btn-outline" style="font-size: 0.85rem;">Mark All as Read</button>
            </form>
        </div>

        <div class="mt-2">
            <?php if (empty($notifications)): ?>
                <p>No notifications received yet.</p>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                    <div style="padding: 15px; border: 1px solid var(--border-color); border-radius: var(--radius); margin-bottom: 10px; background: <?= $notif['is_read'] ? 'var(--secondary-color)' : '#e1effe' ?>; border-left: 4px solid <?= $notif['is_read'] ? 'var(--border-color)' : 'var(--primary-color)' ?>;">
                        <div style="display: flex; justify-content: space-between;">
                            <strong style="color: <?= $notif['is_read'] ? 'var(--text-main)' : '#1e429f' ?>;">System Alert</strong>
                            <span style="font-size: 0.8rem; color: gray;"><?= date('M d, Y - H:i', strtotime($notif['created_at'])) ?></span>
                        </div>
                        <div style="margin-top: 5px; font-size: 0.95rem; color: var(--text-main);">
                            <?= h($notif['message']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>