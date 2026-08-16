<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    redirect('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_note'])) {
    verify_csrf_token();
    $note_id = (int)$_POST['note_id'];
    $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$note_id, $_SESSION['user_id']]);
    set_flash_message('success', 'Note deleted successfully.');
    redirect('/student/notes.php');
}

$stmt = $pdo->prepare("
    SELECT n.*, l.title as lecture_title, c.title as course_title 
    FROM notes n
    JOIN lectures l ON n.lecture_id = l.id
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/student_sidebar.php'; ?>

    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>My Study Notes</h2>

        <table style="width: 100%; margin-top: 15px;">
            <thead>
                <tr>
                    <th>Course & Lecture</th>
                    <th>My Note</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($notes)): ?>
                    <tr>
                        <td colspan="4" class="text-center">No notes saved yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($notes as $note): ?>
                        <tr>
                            <td style="width: 25%;">
                                <strong><?= h($note['course_title']) ?></strong><br>
                                <span style="font-size: 0.85rem; color: gray;"><?= h($note['lecture_title']) ?></span>
                            </td>
                            <td style="font-size: 0.95rem; line-height: 1.4; width: 50%;"><?= nl2br(h($note['content'])) ?></td>
                            <td style="font-size: 0.85rem; width: 15%;"><?= date('M d, Y', strtotime($note['created_at'])) ?></td>
                            <td style="width: 10%;">
                                <form method="POST" action="notes.php" onsubmit="return confirm('Delete this note?');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="note_id" value="<?= h($note['id']) ?>">
                                    <button type="submit" name="delete_note" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger); padding: 4px 8px; font-size: 0.8rem;">Delete</button>
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