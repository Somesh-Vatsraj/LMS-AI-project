<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

// Handle Broadcasting a Notification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $target_role = $_POST['target_role'] ?? '';
    $message = trim($_POST['message'] ?? '');

    if (!empty($message) && in_array($target_role, ['All', 'Student', 'Instructor'])) {
        $pdo->beginTransaction();
        try {
            $query = "SELECT u.id FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id";
            $params = [];

            if ($target_role !== 'All') {
                $query .= " WHERE r.name = ?";
                $params[] = $target_role;
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $insert_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            foreach ($users as $uid) {
                $insert_stmt->execute([$uid, $message]);
            }

            $pdo->commit();
            create_audit_log($pdo, $_SESSION['user_id'], 'System Broadcast', "Sent notification to $target_role(s)");
            set_flash_message('success', "Notification broadcasted successfully to " . count($users) . " user(s).");
        } catch (Exception $e) {
            $pdo->rollBack();
            set_flash_message('error', 'Failed to send notifications.');
        }
        redirect('/admin/notifications.php');
    } else {
        set_flash_message('error', 'Invalid message or target audience.');
    }
}

// Fetch the 50 most recent notifications to monitor platform activity
$stmt = $pdo->query("
    SELECT n.*, u.name as user_name, u.email as user_email 
    FROM notifications n 
    JOIN users u ON n.user_id = u.id 
    ORDER BY n.created_at DESC 
    LIMIT 50
");
$recent_notifications = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <div class="dashboard-content" style="flex: 3; min-width: 300px;">
        <div class="card">
            <h2>Broadcast Notification</h2>
            <p style="color: gray;">Send a real-time system alert to users on the platform.</p>

            <form method="POST" action="notifications.php" style="margin-top: 20px; max-width: 600px;">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="form-group">
                    <label>Target Audience</label>
                    <select name="target_role" required>
                        <option value="All">All Users (Students, Instructors & Admins)</option>
                        <option value="Student">Students Only</option>
                        <option value="Instructor">Instructors Only</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Notification Message</label>
                    <textarea name="message" rows="3" required placeholder="e.g. Scheduled maintenance this weekend!"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" onsubmit="return confirm('Are you sure you want to broadcast this message?');">Broadcast Message</button>
            </form>
        </div>

        <div class="card mt-2" style="overflow-x: auto;">
            <h3>Recent Notification History</h3>
            <table style="width: 100%; min-width: 600px; margin-top: 15px;">
                <thead>
                    <tr>
                        <th>Recipient</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Sent On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_notifications)): ?>
                        <tr>
                            <td colspan="4" class="text-center" style="padding: 20px;">No notifications in system.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_notifications as $n): ?>
                            <tr>
                                <td>
                                    <strong><?= h($n['user_name']) ?></strong><br>
                                    <span style="font-size: 0.85rem; color: gray;"><?= h($n['user_email']) ?></span>
                                </td>
                                <td style="font-size: 0.95rem; max-width: 300px;"><?= h($n['message']) ?></td>
                                <td>
                                    <?php if ($n['is_read']): ?>
                                        <span style="color: var(--success); font-size: 0.85rem; font-weight: bold;">Read</span>
                                    <?php else: ?>
                                        <span style="color: var(--warning); font-size: 0.85rem; font-weight: bold;">Unread</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.85rem;"><?= date('M d, H:i', strtotime($n['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>