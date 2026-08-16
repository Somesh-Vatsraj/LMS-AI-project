<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Instructor') {
    redirect('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    verify_csrf_token();
    $id = (int)$_POST['id'];
    // Strict ownership check before deleting discussion
    $stmt = $pdo->prepare("SELECT d.id FROM discussions d JOIN courses c ON d.course_id = c.id WHERE d.id = ? AND c.instructor_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM discussion_posts WHERE discussion_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM discussions WHERE id = ?")->execute([$id]);
        set_flash_message('success', 'Discussion thread deleted.');
    }
    redirect('/instructor/discussions.php');
}

$stmt = $pdo->prepare("
    SELECT d.*, c.title as course_title,
    (SELECT COUNT(*) FROM discussion_posts WHERE discussion_id = d.id) as reply_count
    FROM discussions d
    JOIN courses c ON d.course_id = c.id
    WHERE c.instructor_id = ?
    ORDER BY d.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$discussions = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/instructor_sidebar.php'; ?>
    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>Course Discussions</h2>
        <table style="width: 100%; margin-top: 15px;">
            <thead>
                <tr>
                    <th>Topic</th>
                    <th>Course</th>
                    <th>Replies</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($discussions)): ?>
                    <tr>
                        <td colspan="4" class="text-center">No discussions yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($discussions as $d): ?>
                        <tr>
                            <td><strong><?= h($d['title']) ?></strong></td>
                            <td style="font-size: 0.85rem;"><?= h($d['course_title']) ?></td>
                            <td><span style="font-weight:bold; color:var(--primary-color);"><?= h($d['reply_count']) ?></span></td>
                            <td>
                                <form method="POST" action="discussions.php" onsubmit="return confirm('Delete this thread and all replies?');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= h($d['id']) ?>">
                                    <button type="submit" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger); padding: 4px 10px; font-size: 0.85rem;">Delete</button>
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