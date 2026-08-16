<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

$stmt = $pdo->query("
    SELECT e.id, e.enrolled_at, u.name as student_name, u.email as student_email, c.title as course_title, c.price
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC
");
$enrollments = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <div class="dashboard-content card" style="flex: 3; min-width: 300px; overflow-x: auto;">
        <h2>Global Enrollments</h2>
        <p style="color: gray;">Real-time feed of students joining courses.</p>

        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Course Enrolled</th>
                    <th>Course Type</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enrollments as $e): ?>
                    <tr>
                        <td>
                            <strong><?= h($e['student_name']) ?></strong><br>
                            <span style="font-size: 0.85rem; color: gray;"><?= h($e['student_email']) ?></span>
                        </td>
                        <td><?= h($e['course_title']) ?></td>
                        <td><?= $e['price'] > 0 ? '$' . h($e['price']) : '<span style="color:var(--success);">Free</span>' ?></td>
                        <td style="font-size: 0.9rem;"><?= date('M d, Y H:i', strtotime($e['enrolled_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>