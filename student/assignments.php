<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    redirect('/login.php');
}

// Handle Assignment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $assignment_id = (int)$_POST['assignment_id'];
    $content = trim($_POST['content'] ?? '');

    if (!empty($content)) {
        // Check if already submitted
        $stmt = $pdo->prepare("SELECT id FROM submissions WHERE assignment_id = ? AND user_id = ?");
        $stmt->execute([$assignment_id, $_SESSION['user_id']]);

        if ($stmt->fetch()) {
            set_flash_message('error', 'You have already submitted this assignment.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$assignment_id, $_SESSION['user_id'], $content]);
            set_flash_message('success', 'Assignment submitted successfully!');
        }
    }
    redirect('/student/assignments.php');
}

// Fetch Assignments for Enrolled Courses & Check Submissions
$stmt = $pdo->prepare("
    SELECT a.*, c.title as course_title, 
    s.id as submission_id, s.grade, s.submitted_at 
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN submissions s ON a.id = s.assignment_id AND s.user_id = e.user_id
    WHERE e.user_id = ?
    ORDER BY a.due_date ASC
");
$stmt->execute([$_SESSION['user_id']]);
$assignments = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/student_sidebar.php'; ?>

    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>My Assignments</h2>
        <p style="color: gray;">Complete assignments for your enrolled courses.</p>

        <div class="grid mt-2">
            <?php if (empty($assignments)): ?>
                <p>No assignments found for your courses.</p>
            <?php else: ?>
                <?php foreach ($assignments as $a): ?>
                    <div class="card" style="border: 1px solid var(--border-color); box-shadow: none;">
                        <h3><?= h($a['title']) ?></h3>
                        <p style="font-size: 0.9rem; color: gray;">Course: <?= h($a['course_title']) ?></p>

                        <div style="margin: 15px 0; font-size: 0.95rem;">
                            <?= nl2br(h($a['description'])) ?>
                        </div>

                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 15px;">
                            <span><strong>Due:</strong> <?= $a['due_date'] ? date('M d, Y', strtotime($a['due_date'])) : 'No due date' ?></span>
                            <span><strong>Status:</strong>
                                <?php if ($a['submission_id']): ?>
                                    <span style="color: var(--success);">Submitted</span>
                                <?php else: ?>
                                    <span style="color: var(--warning);">Pending</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <?php if ($a['submission_id']): ?>
                            <div style="background: var(--bg-color); padding: 10px; border-radius: var(--radius); text-align: center;">
                                <strong>Grade:</strong> <?= $a['grade'] !== null ? h($a['grade']) . '/100' : 'Waiting for grading' ?>
                                <div style="font-size: 0.8rem; color: gray; mt-1">Submitted on <?= date('M d, Y', strtotime($a['submitted_at'])) ?></div>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="assignments.php">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="assignment_id" value="<?= h($a['id']) ?>">
                                <div class="form-group">
                                    <textarea name="content" rows="3" required placeholder="Type your answer or paste your project link here..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Submit Assignment</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>