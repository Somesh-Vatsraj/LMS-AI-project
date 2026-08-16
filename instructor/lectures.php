<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Instructor') {
    redirect('/login.php');
}

$lecture_id = $_GET['id'] ?? null;
$lecture = null;
$course_id = null;

if ($lecture_id) {
    // Verify ownership
    $stmt = $pdo->prepare("
        SELECT l.*, m.course_id 
        FROM lectures l 
        JOIN modules m ON l.module_id = m.id 
        JOIN courses c ON m.course_id = c.id 
        WHERE l.id = ? AND c.instructor_id = ?
    ");
    $stmt->execute([$lecture_id, $_SESSION['user_id']]);
    $lecture = $stmt->fetch();
    if ($lecture) $course_id = $lecture['course_id'];
}

// Handle Update or Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'update' && $lecture) {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $video_url = trim($_POST['video_url'] ?? '');
        $order_num = (int)($_POST['order_num'] ?? 0);

        if (!empty($title)) {
            $stmt = $pdo->prepare("UPDATE lectures SET title = ?, content = ?, video_url = ?, order_num = ? WHERE id = ?");
            $stmt->execute([$title, $content, $video_url, $order_num, $lecture_id]);
            set_flash_message('success', 'Lecture updated successfully.');
            redirect("/instructor/lectures.php?id=$lecture_id");
        }
    } elseif ($action === 'delete' && $lecture) {
        $stmt = $pdo->prepare("DELETE FROM lectures WHERE id = ?");
        $stmt->execute([$lecture_id]);
        set_flash_message('success', 'Lecture deleted.');
        redirect("/instructor/modules.php?id=$course_id");
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/instructor_sidebar.php'; ?>

    <div class="dashboard-content" style="flex: 3; min-width: 300px;">
        <?php if ($lecture): ?>
            <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Edit Lecture: <?= h($lecture['title']) ?></h2>
                <a href="modules.php?id=<?= h($course_id) ?>" class="btn btn-outline">&larr; Back to Curriculum</a>
            </div>

            <div class="card mt-2">
                <form method="POST" action="lectures.php?id=<?= h($lecture_id) ?>">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="update">

                    <div class="form-group">
                        <label>Lecture Title</label>
                        <input type="text" name="title" value="<?= h($lecture['title']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Video URL (YouTube or MP4)</label>
                        <input type="url" name="video_url" value="<?= h($lecture['video_url']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Text Content / Notes</label>
                        <textarea name="content" rows="8"><?= h($lecture['content']) ?></textarea>
                    </div>

                    <div class="form-group" style="max-width: 150px;">
                        <label>Display Order</label>
                        <input type="number" name="order_num" value="<?= h($lecture['order_num']) ?>">
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>

                <form method="POST" action="lectures.php?id=<?= h($lecture_id) ?>" onsubmit="return confirm('Permanently delete this lecture?');" style="margin-top: 15px; border-top: 1px solid var(--border-color); padding-top: 15px;">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger);">Delete Lecture</button>
                </form>
            </div>
        <?php else: ?>
            <div class="card text-center">
                <h2>No Lecture Selected</h2>
                <p style="color: gray;">Please go to your <a href="courses.php">Courses</a>, click on 'Curriculum', and select a lecture to edit.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>