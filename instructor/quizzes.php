<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Instructor') {
    redirect('/login.php');
}

// Handle Add / Delete Quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $course_id = (int)$_POST['course_id'];
        $title = trim($_POST['title'] ?? '');

        // Verify instructor owns this course
        $stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND instructor_id = ?");
        $stmt->execute([$course_id, $_SESSION['user_id']]);
        if ($stmt->fetch() && !empty($title)) {
            $stmt = $pdo->prepare("INSERT INTO quizzes (course_id, title) VALUES (?, ?)");
            $stmt->execute([$course_id, $title]);
            set_flash_message('success', 'Quiz created successfully. Now add some questions!');
        } else {
            set_flash_message('error', 'Invalid course or title.');
        }
    } elseif ($action === 'delete') {
        $quiz_id = (int)$_POST['quiz_id'];

        // Verify ownership via course join
        $stmt = $pdo->prepare("
            SELECT q.id FROM quizzes q 
            JOIN courses c ON q.course_id = c.id 
            WHERE q.id = ? AND c.instructor_id = ?
        ");
        $stmt->execute([$quiz_id, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
            $stmt->execute([$quiz_id]);
            set_flash_message('success', 'Quiz deleted successfully.');
        }
    }
    redirect('/instructor/quizzes.php');
}

// Fetch all courses for this instructor (for the dropdown)
$stmt = $pdo->prepare("SELECT id, title FROM courses WHERE instructor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$my_courses = $stmt->fetchAll();

// Fetch all quizzes created by this instructor
$stmt = $pdo->prepare("
    SELECT q.*, c.title as course_title,
    (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    WHERE c.instructor_id = ?
    ORDER BY q.id DESC
");
$stmt->execute([$_SESSION['user_id']]);
$quizzes = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <!-- Naya dynamic sidebar include kar diya gaya hai -->
    <?php require_once __DIR__ . '/../includes/instructor_sidebar.php'; ?>

    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>Manage Quizzes & Tests</h2>
        <p style="color: gray;">Create multiple-choice quizzes to assess your students.</p>

        <!-- Add Quiz Form -->
        <form method="POST" action="quizzes.php" style="display: flex; gap: 10px; align-items: flex-end; margin: 20px 0; background: var(--bg-color); padding: 15px; border-radius: var(--radius);">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="add">

            <div class="form-group" style="flex: 1; margin: 0;">
                <label>Select Course</label>
                <select name="course_id" required class="custom-select" style="width: 100%;">
                    <option value="">-- Choose a Course --</option>
                    <?php foreach ($my_courses as $course): ?>
                        <option value="<?= h($course['id']) ?>"><?= h($course['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="flex: 2; margin: 0;">
                <label>Quiz Title</label>
                <input type="text" name="title" required placeholder="e.g. PHP Basics Final Test">
            </div>

            <button type="submit" class="btn btn-primary" style="margin: 0;">Create Quiz</button>
        </form>

        <!-- List of Quizzes -->
        <table style="width: 100%; margin-top: 20px;">
            <thead>
                <tr>
                    <th>Quiz Title</th>
                    <th>Linked Course</th>
                    <th>Questions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($quizzes)): ?>
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 20px;">No quizzes created yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($quizzes as $quiz): ?>
                        <tr>
                            <td><strong><?= h($quiz['title']) ?></strong></td>
                            <td style="font-size: 0.9rem; color: gray;"><?= h($quiz['course_title']) ?></td>
                            <td><span style="background: var(--bg-color); padding: 2px 8px; border-radius: 10px; font-weight: bold;"><?= h($quiz['question_count']) ?></span></td>
                            <td style="display: flex; gap: 5px;">
                                <a href="quiz-edit.php?id=<?= h($quiz['id']) ?>" class="btn btn-outline" style="padding: 4px 10px; font-size: 0.85rem;">Add Questions</a>

                                <form method="POST" action="quizzes.php" onsubmit="return confirm('Delete this quiz entirely?');" style="margin: 0;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="quiz_id" value="<?= h($quiz['id']) ?>">
                                    <button type="submit" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger); padding: 4px 10px; font-size: 0.85rem;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>