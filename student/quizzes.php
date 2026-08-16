<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    redirect('/login.php');
}

// Fetch all quizzes for courses the student is enrolled in, along with their best score
$stmt = $pdo->prepare("
    SELECT q.id, q.title, c.title as course_title,
    (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as q_count,
    (SELECT MAX(score) FROM attempts WHERE quiz_id = q.id AND user_id = e.user_id) as best_score,
    (SELECT COUNT(*) FROM attempts WHERE quiz_id = q.id AND user_id = e.user_id) as attempt_count
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.user_id = ?
    ORDER BY q.id DESC
");
$stmt->execute([$_SESSION['user_id']]);
$quizzes = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/student_sidebar.php'; ?>

    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>My Quizzes</h2>
        <p style="color: gray;">Assessments for your enrolled courses.</p>

        <table style="width: 100%; margin-top: 15px;">
            <thead>
                <tr>
                    <th>Quiz Title</th>
                    <th>Course</th>
                    <th>Questions</th>
                    <th>Best Score</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($quizzes)): ?>
                    <tr>
                        <td colspan="5" class="text-center">No quizzes available for your courses yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($quizzes as $quiz): ?>
                        <tr>
                            <td><strong><?= h($quiz['title']) ?></strong></td>
                            <td style="font-size: 0.9rem;"><?= h($quiz['course_title']) ?></td>
                            <td><?= h($quiz['q_count']) ?></td>
                            <td>
                                <?php if ($quiz['attempt_count'] > 0): ?>
                                    <span style="color: var(--success); font-weight: bold;"><?= h($quiz['best_score']) ?>%</span>
                                <?php else: ?>
                                    <span style="color: gray;">Not attempted</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($quiz['q_count'] > 0): ?>
                                    <a href="take-quiz.php?id=<?= h($quiz['id']) ?>" class="btn btn-primary" style="padding: 5px 12px; font-size: 0.85rem;">
                                        <?= $quiz['attempt_count'] > 0 ? 'Retake Quiz' : 'Start Quiz' ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: gray; font-size: 0.85rem;">No questions yet</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>