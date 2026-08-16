<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Instructor') {
    redirect('/login.php');
}

$quiz_id = $_GET['id'] ?? null;
if (!$quiz_id) redirect('/instructor/quizzes.php');

// Verify Quiz ownership (Check if this instructor owns the course linked to this quiz)
$stmt = $pdo->prepare("
    SELECT q.*, c.title as course_title 
    FROM quizzes q 
    JOIN courses c ON q.course_id = c.id 
    WHERE q.id = ? AND c.instructor_id = ?
");
$stmt->execute([$quiz_id, $_SESSION['user_id']]);
$quiz = $stmt->fetch();

if (!$quiz) {
    set_flash_message('error', 'Quiz not found or unauthorized.');
    redirect('/instructor/quizzes.php');
}

// Handle Add / Delete Question
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    // Add Question Logic
    if (isset($_POST['add_question'])) {
        $question_text = trim($_POST['question_text'] ?? '');
        $correct_option = (int)$_POST['correct_option']; // 1, 2, 3, or 4

        $options = [
            1 => trim($_POST['option_1'] ?? ''),
            2 => trim($_POST['option_2'] ?? ''),
            3 => trim($_POST['option_3'] ?? ''),
            4 => trim($_POST['option_4'] ?? '')
        ];

        // Ensure question text and at least 2 options are provided
        if (!empty($question_text) && !empty($options[1]) && !empty($options[2])) {
            $pdo->beginTransaction();
            try {
                // Insert the main Question
                $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, text) VALUES (?, ?)");
                $stmt->execute([$quiz_id, $question_text]);
                $question_id = $pdo->lastInsertId();

                // Insert the Options linked to that question
                $stmtOpt = $pdo->prepare("INSERT INTO options (question_id, text, is_correct) VALUES (?, ?, ?)");
                foreach ($options as $index => $opt_text) {
                    if (!empty($opt_text)) {
                        $is_correct = ($index === $correct_option) ? 1 : 0;
                        $stmtOpt->execute([$question_id, $opt_text, $is_correct]);
                    }
                }

                $pdo->commit();
                set_flash_message('success', 'Question added successfully.');
            } catch (Exception $e) {
                $pdo->rollBack();
                set_flash_message('error', 'Failed to add question. Please try again.');
            }
        } else {
            set_flash_message('error', 'Please provide a question and at least 2 options.');
        }
    }
    // Delete Question Logic
    elseif (isset($_POST['delete_question'])) {
        $q_id = (int)$_POST['question_id'];

        // Options are deleted first to maintain relational integrity
        $pdo->prepare("DELETE FROM options WHERE question_id = ?")->execute([$q_id]);
        $pdo->prepare("DELETE FROM questions WHERE id = ? AND quiz_id = ?")->execute([$q_id, $quiz_id]);

        set_flash_message('success', 'Question deleted.');
    }

    redirect("/instructor/quiz-edit.php?id=$quiz_id");
}

// Fetch all existing questions for this quiz
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">

    <!-- Instructor Sidebar -->
    <?php require_once __DIR__ . '/../includes/instructor_sidebar.php'; ?>

    <div class="dashboard-content" style="flex: 3; min-width: 300px;">
        <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin-bottom: 5px;"><?= h($quiz['title']) ?></h2>
                <p style="color: gray; margin: 0; font-size: 0.9rem;">Linked Course: <strong><?= h($quiz['course_title']) ?></strong></p>
            </div>
            <a href="quizzes.php" class="btn btn-outline">&larr; Back to Quizzes</a>
        </div>

        <!-- Add New Question Form -->
        <div class="card mt-2" style="border-left: 4px solid var(--primary-color);">
            <h3>+ Create a New Question</h3>
            <form method="POST" action="quiz-edit.php?id=<?= h($quiz_id) ?>" style="margin-top: 15px;">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="form-group">
                    <label>Question Text</label>
                    <textarea name="question_text" rows="2" required placeholder="e.g., What does PHP stand for?"></textarea>
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Option 1 (Required)</label>
                        <input type="text" name="option_1" required placeholder="First choice">
                    </div>
                    <div class="form-group">
                        <label>Option 2 (Required)</label>
                        <input type="text" name="option_2" required placeholder="Second choice">
                    </div>
                    <div class="form-group">
                        <label>Option 3 (Optional)</label>
                        <input type="text" name="option_3" placeholder="Third choice">
                    </div>
                    <div class="form-group">
                        <label>Option 4 (Optional)</label>
                        <input type="text" name="option_4" placeholder="Fourth choice">
                    </div>
                </div>

                <div class="form-group" style="max-width: 250px;">
                    <label>Which option is correct?</label>
                    <select name="correct_option" class="custom-select" required>
                        <option value="1">Option 1 is correct</option>
                        <option value="2">Option 2 is correct</option>
                        <option value="3">Option 3 is correct</option>
                        <option value="4">Option 4 is correct</option>
                    </select>
                </div>

                <button type="submit" name="add_question" class="btn btn-primary">Save Question</button>
            </form>
        </div>

        <!-- Existing Questions List -->
        <h3 class="mt-2" style="margin-bottom: 15px;">Existing Questions (<?= count($questions) ?>)</h3>

        <?php if (empty($questions)): ?>
            <div class="card text-center" style="color: gray;">
                No questions added to this quiz yet. Use the form above to add one.
            </div>
        <?php else: ?>
            <?php foreach ($questions as $index => $q): ?>
                <div class="card mt-2">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <h4 style="margin: 0;">
                            <span style="color: var(--primary-color);">Q<?= $index + 1 ?>:</span> <?= h($q['text']) ?>
                        </h4>

                        <!-- Delete Question Button -->
                        <form method="POST" action="quiz-edit.php?id=<?= h($quiz_id) ?>" onsubmit="return confirm('Are you sure you want to delete this question?');" style="margin: 0;">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="question_id" value="<?= h($q['id']) ?>">
                            <button type="submit" name="delete_question" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger); padding: 4px 10px; font-size: 0.85rem;">Delete</button>
                        </form>
                    </div>

                    <?php
                    // Fetch options specifically for this question
                    $stmtOpt = $pdo->prepare("SELECT * FROM options WHERE question_id = ? ORDER BY id ASC");
                    $stmtOpt->execute([$q['id']]);
                    $options = $stmtOpt->fetchAll();
                    ?>

                    <ul style="list-style: none; padding-top: 15px; margin: 0;">
                        <?php foreach ($options as $opt_idx => $opt): ?>
                            <li style="padding: 10px 15px; margin-bottom: 8px; background: <?= $opt['is_correct'] ? '#def7ec' : 'var(--bg-color)' ?>; border-radius: var(--radius); border-left: 4px solid <?= $opt['is_correct'] ? 'var(--success)' : 'var(--border-color)' ?>; font-size: 0.95rem;">
                                <strong>Option <?= $opt_idx + 1 ?>:</strong> <?= h($opt['text']) ?>
                                <?php if ($opt['is_correct']): ?>
                                    <span style="color: var(--success); float: right; font-weight: bold; font-size: 0.85rem;">✓ Correct Answer</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>