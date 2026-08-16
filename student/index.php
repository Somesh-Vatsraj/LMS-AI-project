<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    set_flash_message('error', 'Unauthorized access.');
    redirect('/login.php');
}

// Handle Mark Notification as Read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    verify_csrf_token();
    $notif_id = (int)$_POST['notif_id'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $_SESSION['user_id']]);
    redirect('/student/index.php');
}

// Fetch Unread Notifications
$stmt = $pdo->prepare("SELECT id, message, created_at FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Get student statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_enrolled = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM progress WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$completed_lectures = $stmt->fetchColumn();

// Get recent courses
$stmt = $pdo->prepare("
    SELECT c.id, c.title, c.slug 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    WHERE e.user_id = ? 
    ORDER BY e.enrolled_at DESC LIMIT 3
");
$stmt->execute([$_SESSION['user_id']]);
$recent_courses = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/student_sidebar.php'; ?>

    <div class="dashboard-content" style="flex: 3; min-width: 300px;">
        <h2>Welcome back, <?= h($_SESSION['user_name']) ?>!</h2>

        <!-- NOTIFICATION AREA -->
        <?php if (!empty($notifications)): ?>
            <div style="margin: 20px 0;">
                <?php foreach ($notifications as $notif): ?>
                    <div class="alert" style="background: #e1effe; border-left: 4px solid var(--primary-color); display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; margin-bottom: 10px; border-radius: var(--radius);">
                        <div>
                            <strong style="color: #1e429f;">System Notification:</strong>
                            <span style="color: #3f83f8; margin-left: 5px;"><?= h($notif['message']) ?></span>
                            <div style="font-size: 0.8rem; color: #76a9fa; mt-1"><?= date('d M Y, H:i', strtotime($notif['created_at'])) ?></div>
                        </div>
                        <form method="POST" action="index.php" style="margin: 0;">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="notif_id" value="<?= h($notif['id']) ?>">
                            <button type="submit" name="mark_read" style="background: transparent; border: none; font-size: 1.2rem; cursor: pointer; color: #1e429f;" title="Mark as Read">✖</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="grid mt-2">
            <div class="card text-center">
                <h3>Enrolled Courses</h3>
                <h2 style="font-size: 2.5rem; color: var(--primary-color);"><?= h($total_enrolled) ?></h2>
            </div>
            <div class="card text-center">
                <h3>Lectures Completed</h3>
                <h2 style="font-size: 2.5rem; color: var(--success);"><?= h($completed_lectures) ?></h2>
            </div>
        </div>

        <div class="card mt-2">
            <h3>Jump Back In</h3>
            <div class="grid mt-2">
                <?php if (empty($recent_courses)): ?>
                    <p>You haven't enrolled in any courses yet. <a href="../courses.php">Browse Courses</a></p>
                <?php else: ?>
                    <?php foreach ($recent_courses as $course): ?>
                        <div class="card" style="border: 1px solid var(--border-color); box-shadow: none;">
                            <h4><?= h($course['title']) ?></h4>
                            <a href="course.php?id=<?= $course['id'] ?>" class="btn btn-primary btn-block mt-2 text-center">Continue Learning</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>