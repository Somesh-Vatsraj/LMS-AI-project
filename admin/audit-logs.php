<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

// Fetch the 100 most recent logs for performance
$stmt = $pdo->query("
    SELECT a.*, u.name as user_name, u.email as user_email
    FROM audit_logs a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 100
");
$logs = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <div class="dashboard-content card" style="flex: 3; min-width: 300px; overflow-x: auto;">
        <h2>System Audit Logs</h2>
        <p style="color: gray; margin-bottom: 15px;">Showing the latest 100 system events.</p>

        <table style="width: 100%; text-align: left; border-collapse: collapse; min-width: 600px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color); background: var(--bg-color);">
                    <th style="padding: 10px;">ID</th>
                    <th style="padding: 10px;">Timestamp</th>
                    <th style="padding: 10px;">User</th>
                    <th style="padding: 10px;">Action</th>
                    <th style="padding: 10px;">Details</th>
                    <th style="padding: 10px;">IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" style="padding: 20px; text-align: center;">No logs found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 10px; font-size: 0.85rem; color: gray;">#<?= h($log['id']) ?></td>
                            <td style="padding: 10px; font-size: 0.85rem;"><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                            <td style="padding: 10px; font-size: 0.9rem;">
                                <?php if ($log['user_name']): ?>
                                    <strong><?= h($log['user_name']) ?></strong><br>
                                    <span style="font-size: 0.8rem; color: gray;"><?= h($log['user_email']) ?></span>
                                <?php else: ?>
                                    <em>System/Guest</em>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px;">
                                <span style="background: var(--bg-color); padding: 2px 6px; border-radius: 4px; font-size: 0.85rem; border: 1px solid var(--border-color);">
                                    <?= h($log['action']) ?>
                                </span>
                            </td>
                            <td style="padding: 10px; font-size: 0.9rem;"><?= h($log['details']) ?></td>
                            <td style="padding: 10px; font-size: 0.85rem; font-family: monospace;"><?= h($log['ip_address']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>