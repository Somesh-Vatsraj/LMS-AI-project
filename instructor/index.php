<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Instructor') {
    set_flash_message('error', 'Unauthorized access. Instructor area only.');
    redirect('/login.php');
}

// Handle Mark Notification as Read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    verify_csrf_token();
    $notif_id = (int)$_POST['notif_id'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $_SESSION['user_id']]);
    redirect('/instructor/index.php');
}

// Fetch Unread Notifications
$stmt = $pdo->prepare("SELECT id, message, created_at FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Get Instructor Statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE instructor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_courses = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(e.id) 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    WHERE c.instructor_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$total_students = $stmt->fetchColumn();

// Get recent courses by instructor
$stmt = $pdo->prepare("SELECT id, title, status, created_at FROM courses WHERE instructor_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recent_courses = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/instructor_sidebar.php'; ?>


    <div class="dashboard-content" style="flex: 3; min-width: 300px;">
        <h2>Instructor Dashboard</h2>
        <p>Welcome back, <?= h($_SESSION['user_name']) ?>!</p>

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
                <h3>My Courses</h3>
                <h2 style="font-size: 2.5rem; color: var(--primary-color);"><?= h($total_courses) ?></h2>
            </div>
            <div class="card text-center">
                <h3>Total Enrollments</h3>
                <h2 style="font-size: 2.5rem; color: var(--success);"><?= h($total_students) ?></h2>
            </div>
        </div>

        <div class="card mt-2">
            <h3>Recent Courses</h3>
            <?php if (empty($recent_courses)): ?>
                <p>You haven't created any courses yet. <a href="courses.php">Create one now</a>.</p>
            <?php else: ?>
                <table style="width: 100%; text-align: left; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color);">
                            <th style="padding: 10px;">Title</th>
                            <th style="padding: 10px;">Status</th>
                            <th style="padding: 10px;">Created</th>
                            <th style="padding: 10px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_courses as $c): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 10px;"><?= h($c['title']) ?></td>
                                <td style="padding: 10px;">
                                    <span style="padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; background: <?= $c['status'] === 'Published' ? 'var(--success)' : 'var(--border-color)' ?>; color: <?= $c['status'] === 'Published' ? '#fff' : '#000' ?>;">
                                        <?= h($c['status']) ?>
                                    </span>
                                </td>
                                <td style="padding: 10px;"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                                <td style="padding: 10px;">
                                    <a href="course-edit.php?id=<?= $c['id'] ?>" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.9rem;">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>