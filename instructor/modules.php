<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Instructor') {
    redirect('/login.php');
}

$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    redirect('/instructor/courses.php');
}

// Verify ownership
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    set_flash_message('error', 'Course not found or access denied.');
    redirect('/instructor/courses.php');
}

// Handle Form Submissions (Add Module / Add Lecture)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_module') {
        $module_title = trim($_POST['module_title'] ?? '');
        $order_num = (int)($_POST['order_num'] ?? 0);

        if (!empty($module_title)) {
            $stmt = $pdo->prepare("INSERT INTO modules (course_id, title, order_num) VALUES (?, ?, ?)");
            $stmt->execute([$course_id, $module_title, $order_num]);
            set_flash_message('success', 'Module added successfully.');
        }
    } elseif ($action === 'add_lecture') {
        $module_id = (int)$_POST['module_id'];
        $lecture_title = trim($_POST['lecture_title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $video_url = trim($_POST['video_url'] ?? '');
        $order_num = (int)($_POST['order_num'] ?? 0);

        // Verify module belongs to this course
        $stmt = $pdo->prepare("SELECT id FROM modules WHERE id = ? AND course_id = ?");
        $stmt->execute([$module_id, $course_id]);
        if ($stmt->fetch() && !empty($lecture_title)) {
            $stmt = $pdo->prepare("INSERT INTO lectures (module_id, title, content, video_url, order_num) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$module_id, $lecture_title, $content, $video_url, $order_num]);
            set_flash_message('success', 'Lecture added successfully.');
        }
    }
    redirect('/instructor/modules.php?id=' . $course_id);
}

// Fetch Modules and Lectures
$stmt = $pdo->prepare("SELECT * FROM modules WHERE course_id = ? ORDER BY order_num ASC");
$stmt->execute([$course_id]);
$modules = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/instructor_sidebar.php'; ?>


    <div class="dashboard-content" style="flex: 3; min-width: 300px;">
        <div class="card">
            <h2>Curriculum: <?= h($course['title']) ?></h2>
            <p>Organize your course into modules and add lectures.</p>
        </div>

        <!-- Add Module Form -->
        <div class="card mt-2">
            <h3>Add New Module</h3>
            <form method="POST" action="modules.php?id=<?= h($course_id) ?>" style="display: flex; gap: 10px; align-items: flex-end; margin-top: 10px;">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="action" value="add_module">

                <div class="form-group" style="flex: 3; margin-bottom: 0;">
                    <label>Module Title</label>
                    <input type="text" name="module_title" required>
                </div>
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <label>Order No.</label>
                    <input type="number" name="order_num" value="0">
                </div>
                <button type="submit" class="btn btn-primary" style="margin-bottom: 0;">Add Module</button>
            </form>
        </div>

        <!-- Modules List -->
        <?php foreach ($modules as $module): ?>
            <div class="card mt-2" style="border-left: 4px solid var(--primary-color);">
                <h3>Module: <?= h($module['title']) ?></h3>

                <?php
                $stmt = $pdo->prepare("SELECT * FROM lectures WHERE module_id = ? ORDER BY order_num ASC");
                $stmt->execute([$module['id']]);
                $lectures = $stmt->fetchAll();
                ?>

                <?php if (!empty($lectures)): ?>
                    <table style="width: 100%; margin-top: 15px; border-collapse: collapse;">
                        <tbody>
                            <?php foreach ($lectures as $lecture): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 10px;">
                                        <strong>Lecture:</strong> <?= h($lecture['title']) ?>
                                    </td>
                                    <td style="padding: 10px; text-align: right;">Order: <?= h($lecture['order_num']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="margin-top: 15px; color: gray;">No lectures in this module yet.</p>
                <?php endif; ?>

                <!-- Add Lecture Form inline -->
                <div style="background: var(--bg-color); padding: 15px; border-radius: 4px; margin-top: 15px;">
                    <h4>+ Add Lecture to this Module</h4>
                    <form method="POST" action="modules.php?id=<?= h($course_id) ?>" class="mt-2">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action" value="add_lecture">
                        <input type="hidden" name="module_id" value="<?= h($module['id']) ?>">

                        <div class="form-group">
                            <label>Lecture Title</label>
                            <input type="text" name="lecture_title" required>
                        </div>
                        <div class="form-group">
                            <label>Video URL (Optional)</label>
                            <input type="url" name="video_url" placeholder="https://...">
                        </div>
                        <div class="form-group">
                            <label>Text Content</label>
                            <textarea name="content" rows="3"></textarea>
                        </div>
                        <div class="form-group" style="max-width: 100px;">
                            <label>Order No.</label>
                            <input type="number" name="order_num" value="0">
                        </div>
                        <button type="submit" class="btn btn-success">Save Lecture</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>