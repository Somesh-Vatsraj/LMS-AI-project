<?php
require_once __DIR__ . '/config/app.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT c.*, cat.name as category_name, u.name as instructor_name FROM courses c LEFT JOIN categories cat ON c.category_id = cat.id LEFT JOIN users u ON c.instructor_id = u.id WHERE c.slug = ?");
$stmt->execute([$slug]);
$course = $stmt->fetch();

if (!$course) {
    set_flash_message('error', 'Course not found.');
    redirect('/courses.php');
}

$isEnrolled = false;
if (is_logged_in() && $_SESSION['user_role'] === 'Student') {
    $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$_SESSION['user_id'], $course['id']]);
    $isEnrolled = (bool)$stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    verify_csrf_token();
    if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
        set_flash_message('error', 'Only students can enroll.');
        redirect('/login.php');
    }
    if (!$isEnrolled) {
        if ($course['price'] > 0) {
            // Fake payment process record
            $stmt = $pdo->prepare("INSERT INTO payments (user_id, course_id, amount, status) VALUES (?, ?, ?, 'Completed')");
            $stmt->execute([$_SESSION['user_id'], $course['id'], $course['price']]);
        }
        $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $course['id']]);
        set_flash_message('success', 'Successfully enrolled in ' . h($course['title']));
        redirect('/course-details.php?slug=' . urlencode($slug));
    }
}

// Fetch modules
$stmt = $pdo->prepare("SELECT * FROM modules WHERE course_id = ? ORDER BY order_num");
$stmt->execute([$course['id']]);
$modules = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="course-header card">
    <h1><?= h($course['title']) ?></h1>
    <p><?= h($course['category_name']) ?> | <?= h($course['difficulty']) ?> | By <?= h($course['instructor_name']) ?></p>
    <h2><?= $course['price'] > 0 ? '$' . $course['price'] : 'Free' ?></h2>
    <p><?= nl2br(h($course['description'])) ?></p>

    <?php if (is_logged_in() && $_SESSION['user_role'] === 'Student'): ?>
        <?php if ($isEnrolled): ?>
            <button class="btn btn-success" disabled>Enrolled</button>
            <a href="student/course.php?id=<?= $course['id'] ?>" class="btn btn-primary">Go to Course</a>
        <?php else: ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <button type="submit" name="enroll" class="btn btn-primary">Enroll Now</button>
            </form>
        <?php endif; ?>
    <?php elseif (!is_logged_in()): ?>
        <a href="login.php" class="btn btn-primary">Log in to Enroll</a>
    <?php endif; ?>
</div>

<div class="course-curriculum card mt-2">
    <h2>Curriculum</h2>
    <?php foreach ($modules as $module): ?>
        <div class="module">
            <h3><?= h($module['title']) ?></h3>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM lectures WHERE module_id = ? ORDER BY order_num");
            $stmt->execute([$module['id']]);
            $lectures = $stmt->fetchAll();
            ?>
            <ul>
                <?php foreach ($lectures as $lecture): ?>
                    <li><?= h($lecture['title']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>