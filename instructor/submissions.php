<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Instructor') {
    redirect('/login.php');
}

// Handle Grading
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission'])) {
    verify_csrf_token();
    $submission_id = (int)$_POST['submission_id'];
    $grade = $_POST['grade'] !== '' ? (float)$_POST['grade'] : null;

    // Verify ownership of the assignment before grading
    $stmt = $pdo->prepare("
        SELECT s.id FROM submissions s 
        JOIN assignments a ON s.assignment_id = a.id 
        JOIN courses c ON a.course_id = c.id 
        WHERE s.id = ? AND c.instructor_id = ?
    ");
    $stmt->execute([$submission_id, $_SESSION['user_id']]);

    if ($stmt->fetch()) {
        $updateStmt = $pdo->prepare("UPDATE submissions SET grade = ? WHERE id = ?");
        $updateStmt->execute([$grade, $submission_id]);
        set_flash_message('success', 'Grade updated successfully.');
    } else {
        set_flash_message('error', 'Unauthorized action.');
    }
    redirect('/instructor/submissions.php');
}

// Fetch all submissions for this instructor's assignments
$stmt = $pdo->prepare("
    SELECT s.*, a.title as assignment_title, u.name as student_name, u.email as student_email, c.title as course_title
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    JOIN users u ON s.user_id = u.id
    WHERE c.instructor_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$submissions = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/instructor_sidebar.php'; ?>

    <div class="dashboard-content card" style="flex: 3; min-width: 300px; overflow-x: auto;">
        <h2>Student Submissions</h2>
        <p style="color: gray;">Review assignments and assign grades.</p>

        <table style="width: 100%; min-width: 800px; margin-top: 15px;">
            <thead>
                <tr>
                    <th>Student Details</th>
                    <th>Assignment & Course</th>
                    <th>Submitted Content</th>
                    <th>Grade (/100)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($submissions)): ?>
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 20px;">No submissions yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($submissions as $sub): ?>
                        <tr>
                            <td>
                                <strong><?= h($sub['student_name']) ?></strong><br>
                                <span style="font-size: 0.85rem; color: gray;"><?= h($sub['student_email']) ?></span>
                            </td>
                            <td>
                                <strong><?= h($sub['assignment_title']) ?></strong><br>
                                <span style="font-size: 0.85rem; color: gray;"><?= h($sub['course_title']) ?></span><br>
                                <span style="font-size: 0.8rem; color: var(--primary-color);"><?= date('M d, Y H:i', strtotime($sub['submitted_at'])) ?></span>
                            </td>
                            <td style="font-size: 0.9rem; max-width: 300px;">
                                <div style="background: var(--bg-color); padding: 10px; border-radius: 4px; max-height: 100px; overflow-y: auto;">
                                    <?= nl2br(h($sub['content'])) ?>
                                </div>
                            </td>
                            <td>
                                <form method="POST" action="submissions.php" style="display: flex; gap: 5px; align-items: center; margin: 0;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="submission_id" value="<?= h($sub['id']) ?>">
                                    <input type="number" step="0.01" min="0" max="100" name="grade" value="<?= h($sub['grade']) ?>" class="form-control" style="width: 80px; padding: 5px;" placeholder="---">
                                    <button type="submit" name="grade_submission" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.85rem;">Save</button>
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