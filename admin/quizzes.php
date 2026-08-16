<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

// Handle Quiz Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
        $stmt->execute([$id]);

        create_audit_log($pdo, $_SESSION['user_id'], 'Delete Quiz', "Admin deleted quiz ID: $id");
        set_flash_message('success', 'Quiz deleted successfully.');
        redirect('/admin/quizzes.php');
    }
}

// Fetch all quizzes with question count and course details
$stmt = $pdo->query("
    SELECT q.*, c.title as course_title, u.name as instructor_name,
    (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count,
    (SELECT COUNT(*) FROM attempts WHERE quiz_id = q.id) as attempt_count
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    LEFT JOIN users u ON c.instructor_id = u.id
    ORDER BY q.id DESC
");
$quizzes = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <div class="dashboard-content card" style="flex: 3; min-width: 300px; overflow-x: auto;">
        <h2>Platform Quizzes</h2>
        <p style="color: gray;">Overview of all quizzes and assessments.</p>

        <table style="width: 100%; min-width: 700px; margin-top: 15px;">
            <thead>
                <tr>
                    <th>Quiz Title & Course</th>
                    <th>Instructor</th>
                    <th>Questions</th>
                    <th>Total Attempts</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($quizzes)): ?>
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 20px;">No quizzes found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($quizzes as $q): ?>
                        <tr>
                            <td>
                                <strong><?= h($q['title']) ?></strong><br>
                                <span style="font-size: 0.85rem; color: gray;">Course: <?= h($q['course_title']) ?></span>
                            </td>
                            <td><?= h($q['instructor_name']) ?></td>
                            <td><?= h($q['question_count']) ?></td>
                            <td><?= h($q['attempt_count']) ?></td>
                            <td>
                                <form method="POST" action="quizzes.php" onsubmit="return confirm('Delete this quiz? All questions and student attempts will be lost.');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= h($q['id']) ?>">
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