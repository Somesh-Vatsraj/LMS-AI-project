<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

// Handle Discussion Deletion (Moderation)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)$_POST['id'];

        $pdo->beginTransaction();
        try {
            // Delete child posts first to prevent foreign key constraint failures
            $stmt = $pdo->prepare("DELETE FROM discussion_posts WHERE discussion_id = ?");
            $stmt->execute([$id]);

            // Delete main discussion thread
            $stmt = $pdo->prepare("DELETE FROM discussions WHERE id = ?");
            $stmt->execute([$id]);

            $pdo->commit();
            create_audit_log($pdo, $_SESSION['user_id'], 'Delete Discussion', "Admin moderated/deleted discussion ID: $id");
            set_flash_message('success', 'Discussion thread and all its posts were permanently removed.');
        } catch (Exception $e) {
            $pdo->rollBack();
            set_flash_message('error', 'An error occurred while deleting the discussion.');
        }
        redirect('/admin/discussions.php');
    }
}

// Fetch all discussions and post counts
$stmt = $pdo->query("
    SELECT d.*, c.title as course_title,
    (SELECT COUNT(*) FROM discussion_posts WHERE discussion_id = d.id) as reply_count
    FROM discussions d
    JOIN courses c ON d.course_id = c.id
    ORDER BY d.created_at DESC
");
$discussions = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <div class="dashboard-content card" style="flex: 3; min-width: 300px; overflow-x: auto;">
        <h2>Community Discussions</h2>
        <p style="color: gray;">Moderate Q&A threads across all courses.</p>

        <table style="width: 100%; min-width: 700px; margin-top: 15px;">
            <thead>
                <tr>
                    <th>Thread Title</th>
                    <th>Course</th>
                    <th>Replies</th>
                    <th>Started On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($discussions)): ?>
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 20px;">No discussions found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($discussions as $d): ?>
                        <tr>
                            <td><strong><?= h($d['title']) ?></strong></td>
                            <td style="font-size: 0.9rem;"><?= h($d['course_title']) ?></td>
                            <td>
                                <span style="background: var(--bg-color); padding: 3px 8px; border-radius: 12px; font-size: 0.85rem; font-weight: bold;">
                                    <?= h($d['reply_count']) ?>
                                </span>
                            </td>
                            <td style="font-size: 0.9rem;"><?= date('M d, Y', strtotime($d['created_at'])) ?></td>
                            <td>
                                <form method="POST" action="discussions.php" onsubmit="return confirm('Delete this entire thread and all replies? This cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= h($d['id']) ?>">
                                    <button type="submit" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger); padding: 4px 10px; font-size: 0.85rem;">Moderate / Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>