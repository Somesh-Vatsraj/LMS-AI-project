<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Instructor') {
    redirect('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_course'])) {
    verify_csrf_token();

    // Generate a temporary title and slug
    $title = "New Draft Course " . time();
    $slug = "draft-" . time();

    $stmt = $pdo->prepare("INSERT INTO courses (title, slug, instructor_id, status) VALUES (?, ?, ?, 'Draft')");
    $stmt->execute([$title, $slug, $_SESSION['user_id']]);
    $new_course_id = $pdo->lastInsertId();

    set_flash_message('success', 'New draft course created. Please fill in the details.');
    redirect('/instructor/course-edit.php?id=' . $new_course_id);
}

$stmt = $pdo->prepare("
    SELECT c.*, 
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as students
    FROM courses c 
    WHERE instructor_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/instructor_sidebar.php'; ?>


    <div class="dashboard-content" style="flex: 3; min-width: 300px;">
        <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
            <h2>My Courses</h2>
            <form method="POST" action="courses.php">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <button type="submit" name="create_course" class="btn btn-primary">Create New Course</button>
            </form>
        </div>

        <div class="grid mt-2">
            <?php foreach ($courses as $course): ?>
                <div class="card">
                    <h3><?= h($course['title']) ?></h3>
                    <p>Status: <strong><?= h($course['status']) ?></strong></p>
                    <p>Students: <?= h($course['students']) ?></p>
                    <p class="price"><?= $course['price'] > 0 ? '₹' . $course['price'] : 'Free' ?></p>
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <a href="course-edit.php?id=<?= $course['id'] ?>" class="btn btn-outline" style="flex: 1; text-align: center;">Edit Details</a>
                        <a href="modules.php?id=<?= $course['id'] ?>" class="btn btn-primary" style="flex: 1; text-align: center;">Curriculum</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>