<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    redirect('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_bookmark'])) {
    verify_csrf_token();
    $bookmark_id = (int)$_POST['bookmark_id'];
    $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookmark_id, $_SESSION['user_id']]);
    set_flash_message('success', 'Bookmark removed.');
    redirect('/student/bookmarks.php');
}

$stmt = $pdo->prepare("
    SELECT b.id as bookmark_id, b.created_at, l.id as lecture_id, l.title as lecture_title, c.id as course_id, c.title as course_title 
    FROM bookmarks b
    JOIN lectures l ON b.lecture_id = l.id
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookmarks = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/student_sidebar.php'; ?>

    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>My Bookmarks</h2>
        <p style="color: gray;">Quick links to your saved lectures and important topics.</p>

        <table style="width: 100%; margin-top: 15px;">
            <thead>
                <tr>
                    <th>Lecture</th>
                    <th>Course</th>
                    <th>Bookmarked On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookmarks)): ?>
                    <tr>
                        <td colspan="4" class="text-center">No bookmarks saved yet. Use the player to bookmark lectures.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookmarks as $bm): ?>
                        <tr>
                            <td><strong><?= h($bm['lecture_title']) ?></strong></td>
                            <td style="font-size: 0.9rem;"><?= h($bm['course_title']) ?></td>
                            <td style="font-size: 0.85rem;"><?= date('M d, Y', strtotime($bm['created_at'])) ?></td>
                            <td style="display: flex; gap: 10px;">
                                <a href="player.php?course=<?= h($bm['course_id']) ?>&lecture=<?= h($bm['lecture_id']) ?>" class="btn btn-primary" style="padding: 4px 10px; font-size: 0.85rem;">Watch</a>

                                <form method="POST" action="bookmarks.php" style="margin: 0;" onsubmit="return confirm('Remove bookmark?');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="bookmark_id" value="<?= h($bm['bookmark_id']) ?>">
                                    <button type="submit" name="delete_bookmark" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger); padding: 4px 10px; font-size: 0.85rem;">Remove</button>
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