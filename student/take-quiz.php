<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    redirect('/login.php');
}

$quiz_id = $_GET['id'] ?? null;
if (!$quiz_id) redirect('/student/quizzes.php');

// Verify student is enrolled in the course that has this quiz
$stmt = $pdo->prepare("
    SELECT q.*, c.title as course_title 
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id
    WHERE q.id = ? AND e.user_id = ?
");
$stmt->execute([$quiz_id, $_SESSION['user_id']]);
$quiz = $stmt->fetch();

if (!$quiz) {
    set_flash_message('error', 'Quiz not found or you are not enrolled in this course.');
    redirect('/student/quizzes.php');
}

// Fetch Questions and their Options
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

// Handle Quiz Submission & Grading
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $total_questions = count($questions);
    $correct_answers = 0;

    // Check each submitted answer
    foreach ($questions as $q) {
        $submitted_option_id = $_POST['question_' . $q['id']] ?? null;

        if ($submitted_option_id) {
            // Verify if selected option is correct
            $stmtChk = $pdo->prepare("SELECT is_correct FROM options WHERE id = ? AND question_id = ?");
            $stmtChk->execute([$submitted_option_id, $q['id']]);
            $opt = $stmtChk->fetch();

            if ($opt && $opt['is_correct']) {
                $correct_answers++;
            }
        }
    }

    // Calculate Score Percentage
    $score_percentage = $total_questions > 0 ? ($correct_answers / $total_questions) * 100 : 0;

    // Save Attempt
    $stmt = $pdo->prepare("INSERT INTO attempts (user_id, quiz_id, score) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $quiz_id, $score_percentage]);

    set_flash_message('success', "Quiz Submitted! You scored " . round($score_percentage, 2) . "% ($correct_answers out of $total_questions).");
    redirect('/student/quizzes.php');
}

require_once __DIR__ . '/../includes/header.php';
?>
<div style="max-width: 800px; margin: 0 auto;">
    <div class="card text-center" style="margin-bottom: 20px; border-top: 5px solid var(--primary-color);">
        <h2><?= h($quiz['title']) ?></h2>
        <p style="color: gray;">Course: <?= h($quiz['course_title']) ?></p>
        <a href="quizzes.php" class="btn btn-outline mt-2">&larr; Back to Quizzes</a>
    </div>

    <form method="POST" action="take-quiz.php?id=<?= h($quiz_id) ?>">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

        <?php foreach ($questions as $index => $q): ?>
            <div class="card" style="margin-bottom: 20px;">
                <h3 style="font-size: 1.1rem; margin-bottom: 15px;">
                    <span style="color: var(--primary-color);">Q<?= $index + 1 ?>:</span> <?= h($q['text']) ?>
                </h3>

                <?php
                $stmtOpt = $pdo->prepare("SELECT * FROM options WHERE question_id = ? ORDER BY id ASC");
                $stmtOpt->execute([$q['id']]);
                $options = $stmtOpt->fetchAll();
                ?>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($options as $opt): ?>
                        <label style="padding: 12px 15px; border: 1px solid var(--border-color); border-radius: var(--radius); cursor: pointer; display: flex; align-items: center; gap: 10px; transition: var(--transition);">
                            <input type="radio" name="question_<?= $q['id'] ?>" value="<?= $opt['id'] ?>" required style="transform: scale(1.2);">
                            <?= h($opt['text']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="card text-center" style="position: sticky; bottom: 20px; box-shadow: var(--shadow-lg);">
            <button type="submit" class="btn btn-primary" style="font-size: 1.1rem; padding: 12px 40px;" onsubmit="return confirm('Are you sure you want to submit your answers?');">
                Submit Quiz
            </button>
        </div>
    </form>
</div>

<style>
    /* Add slight hover effect to radio labels */
    label:hover {
        background-color: rgba(67, 97, 238, 0.05);
        border-color: var(--primary-color) !important;
    }

    input[type="radio"]:checked+label {
        background-color: rgba(67, 97, 238, 0.1);
        border-color: var(--primary-color) !important;
        font-weight: bold;
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>