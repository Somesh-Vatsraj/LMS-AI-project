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
        $description = trim($_POST['description'] ?? '');
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

        // Verify ownership
        $stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND instructor_id = ?");
        $stmt->execute([$course_id, $_SESSION['user_id']]);
        if ($stmt->fetch() && !empty($title)) {
            $stmt = $pdo->prepare("INSERT INTO assignments (course_id, title, description, due_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$course_id, $title, $description, $due_date]);
            set_flash_message('success', 'Assignment added successfully.');
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE a FROM assignments a JOIN courses c ON a.course_id = c.id WHERE a.id = ? AND c.instructor_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        set_flash_message('success', 'Assignment removed.');
    }
    redirect('/instructor/assignments.php');
}

$stmt = $pdo->prepare("SELECT id, title FROM courses WHERE instructor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT a.*, c.title as course_title,
    (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) as sub_count
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    WHERE c.instructor_id = ? ORDER BY a.due_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$assignments = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/instructor_sidebar.php'; ?>

    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>Manage Assignments</h2>

        <form method="POST" action="assignments.php" style="background: var(--bg-color); padding: 15px; border-radius: var(--radius); margin-bottom: 20px;">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="add">

            <div class="grid" style="grid-template-columns: 1fr 2fr;">
                <div class="form-group">
                    <label>Course</label>
                    <select name="course_id" class="custom-select" required>
                        <option value="">Select Course...</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= h($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Assignment Title</label>
                    <input type="text" name="title" required>
                </div>
            </div>
            <div class="form-group">
                <label>Description / Instructions</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <div class="form-group" style="max-width: 250px;">
                <label>Due Date</label>
                <input type="datetime-local" name="due_date">
            </div>
            <button type="submit" class="btn btn-primary">Create Assignment</button>
        </form>

        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>Assignment</th>
                    <th>Course</th>
                    <th>Due Date</th>
                    <th>Submissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $a): ?>
                    <tr>
                        <td><strong><?= h($a['title']) ?></strong></td>
                        <td><?= h($a['course_title']) ?></td>
                        <td><?= $a['due_date'] ? date('M d, Y', strtotime($a['due_date'])) : 'No due date' ?></td>
                        <td><span style="background: var(--bg-color); padding: 3px 8px; border-radius: 10px;"><?= h($a['sub_count']) ?></span></td>
                        <td>
                            <form method="POST" action="assignments.php" onsubmit="return confirm('Delete?');">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= h($a['id']) ?>">
                                <button type="submit" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger); padding: 4px 10px;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>