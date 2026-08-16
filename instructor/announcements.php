<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Instructor') {
    redirect('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $course_id = (int)$_POST['course_id'];
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        $stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND instructor_id = ?");
        $stmt->execute([$course_id, $_SESSION['user_id']]);
        if ($stmt->fetch() && !empty($title) && !empty($content)) {
            $stmt = $pdo->prepare("INSERT INTO announcements (course_id, title, content) VALUES (?, ?, ?)");
            $stmt->execute([$course_id, $title, $content]);
            set_flash_message('success', 'Announcement posted successfully.');
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE a FROM announcements a JOIN courses c ON a.course_id = c.id WHERE a.id = ? AND c.instructor_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        set_flash_message('success', 'Announcement deleted.');
    }
    redirect('/instructor/announcements.php');
}

$stmt = $pdo->prepare("SELECT id, title FROM courses WHERE instructor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT a.*, c.title as course_title 
    FROM announcements a JOIN courses c ON a.course_id = c.id 
    WHERE c.instructor_id = ? ORDER BY a.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$announcements = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/instructor_sidebar.php'; ?>
    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>Course Announcements</h2>
        <form method="POST" action="announcements.php" style="background: var(--bg-color); padding: 15px; border-radius: var(--radius); margin-bottom: 20px;">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="add">
            <div class="grid" style="grid-template-columns: 1fr 2fr;">
                <div class="form-group">
                    <label>Course</label>
                    <select name="course_id" class="custom-select" required>
                        <?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>"><?= h($c['title']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required>
                </div>
            </div>
            <div class="form-group"><label>Message</label><textarea name="content" rows="3" required></textarea></div>
            <button type="submit" class="btn btn-primary">Post Announcement</button>
        </form>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Course</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($announcements as $a): ?>
                    <tr>
                        <td><strong><?= h($a['title']) ?></strong></td>
                        <td><?= h($a['course_title']) ?></td>
                        <td><?= date('M d, Y', strtotime($a['created_at'])) ?></td>
                        <td>
                            <form method="POST" action="announcements.php"><input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= h($a['id']) ?>"><button type="submit" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger); padding: 4px 10px;">Delete</button></form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>