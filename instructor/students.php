<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Instructor') {
    redirect('/login.php');
}

// Fetch all students enrolled in courses taught by this instructor
$stmt = $pdo->prepare("
    SELECT u.name, u.email, c.title as course_title, e.enrolled_at 
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    WHERE c.instructor_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$students = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/instructor_sidebar.php'; ?>

    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>My Students</h2>
        <p style="color: gray;">A list of all students currently enrolled in your courses.</p>

        <table style="width: 100%; margin-top: 15px;">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Enrolled Course</th>
                    <th>Enrollment Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="4" class="text-center">No students enrolled yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td><strong><?= h($s['name']) ?></strong></td>
                            <td><?= h($s['email']) ?></td>
                            <td><span style="background: var(--bg-color); padding: 3px 8px; border-radius: 4px; font-size: 0.85rem;"><?= h($s['course_title']) ?></span></td>
                            <td style="font-size: 0.9rem;"><?= date('M d, Y', strtotime($s['enrolled_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>