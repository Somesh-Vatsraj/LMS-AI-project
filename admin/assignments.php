<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

// Handle Assignment Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM assignments WHERE id = ?");
        $stmt->execute([$id]);

        create_audit_log($pdo, $_SESSION['user_id'], 'Delete Assignment', "Admin deleted assignment ID: $id");
        set_flash_message('success', 'Assignment deleted successfully.');
        redirect('/admin/assignments.php');
    }
}

// Fetch all assignments with course and instructor details
$stmt = $pdo->query("
    SELECT a.*, c.title as course_title, u.name as instructor_name,
    (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) as submission_count
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    LEFT JOIN users u ON c.instructor_id = u.id
    ORDER BY a.due_date DESC
");
$assignments = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <div class="dashboard-content card" style="flex: 3; min-width: 300px; overflow-x: auto;">
        <h2>Platform Assignments</h2>
        <p style="color: gray;">Monitor all assignments created by instructors across courses.</p>

        <table style="width: 100%; min-width: 700px; margin-top: 15px;">
            <thead>
                <tr>
                    <th>Title & Course</th>
                    <th>Instructor</th>
                    <th>Due Date</th>
                    <th>Submissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 20px;">No assignments found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td>
                                <strong><?= h($a['title']) ?></strong><br>
                                <span style="font-size: 0.85rem; color: gray;">Course: <?= h($a['course_title']) ?></span>
                            </td>
                            <td><?= h($a['instructor_name']) ?></td>
                            <td>
                                <?php if ($a['due_date']): ?>
                                    <?= date('M d, Y', strtotime($a['due_date'])) ?>
                                <?php else: ?>
                                    <span style="color: gray;">No Due Date</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="background: var(--bg-color); padding: 3px 8px; border-radius: 12px; font-size: 0.85rem;">
                                    <?= h($a['submission_count']) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="assignments.php" onsubmit="return confirm('Delete this assignment? All submissions will also be deleted.');">
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