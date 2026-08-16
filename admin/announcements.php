<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

// Handle Announcement Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$id]);

        create_audit_log($pdo, $_SESSION['user_id'], 'Delete Announcement', "Admin deleted announcement ID: $id");
        set_flash_message('success', 'Announcement deleted successfully.');
        redirect('/admin/announcements.php');
    }
}

// Fetch all announcements across all courses
$stmt = $pdo->query("
    SELECT a.*, c.title as course_title, u.name as instructor_name
    FROM announcements a
    JOIN courses c ON a.course_id = c.id
    LEFT JOIN users u ON c.instructor_id = u.id
    ORDER BY a.created_at DESC
");
$announcements = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <div class="dashboard-content card" style="flex: 3; min-width: 300px; overflow-x: auto;">
        <h2>Course Announcements</h2>
        <p style="color: gray;">Monitor all instructor broadcasts and announcements across the platform.</p>

        <table style="width: 100%; min-width: 700px; margin-top: 15px;">
            <thead>
                <tr>
                    <th>Announcement Details</th>
                    <th>Course & Instructor</th>
                    <th>Date Posted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($announcements)): ?>
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 20px;">No announcements found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($announcements as $a): ?>
                        <tr>
                            <td style="max-width: 300px;">
                                <strong><?= h($a['title']) ?></strong><br>
                                <span style="font-size: 0.85rem; color: gray; display: inline-block; margin-top: 5px;">
                                    <?= h(strlen($a['content']) > 60 ? substr($a['content'], 0, 60) . '...' : $a['content']) ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= h($a['course_title']) ?></strong><br>
                                <span style="font-size: 0.85rem; color: gray;">By <?= h($a['instructor_name']) ?></span>
                            </td>
                            <td style="font-size: 0.9rem;"><?= date('M d, Y', strtotime($a['created_at'])) ?></td>
                            <td>
                                <form method="POST" action="announcements.php" onsubmit="return confirm('Remove this announcement?');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= h($a['id']) ?>">
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