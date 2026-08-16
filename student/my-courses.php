<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    redirect('/login.php');
}

$stmt = $pdo->prepare("
    SELECT c.id, c.title, c.difficulty, cat.name as category_name
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE e.user_id = ? 
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/student_sidebar.php'; ?>

    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>My Enrolled Courses</h2>

        <div class="grid mt-2">
            <?php if (empty($courses)): ?>
                <p>No courses found. <a href="../courses.php">Find a course to start learning.</a></p>
            <?php else: ?>
                <?php foreach ($courses as $course): ?>
                    <div class="card" style="border: 1px solid var(--border-color); box-shadow: none;">
                        <h3><?= h($course['title']) ?></h3>
                        <p><?= h($course['category_name']) ?> | <?= h($course['difficulty']) ?></p>
                        <a href="course.php?id=<?= $course['id'] ?>" class="btn btn-primary btn-block mt-2 text-center">Go to Course</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>