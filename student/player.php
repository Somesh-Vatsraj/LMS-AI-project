<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    redirect('/login.php');
}

$course_id = $_GET['course'] ?? null;
$lecture_id = $_GET['lecture'] ?? null;

if (!$course_id) redirect('/student/my-courses.php');

// Verify enrollment
$stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$_SESSION['user_id'], $course_id]);
if (!$stmt->fetch()) {
    set_flash_message('error', 'You are not enrolled in this course.');
    redirect('/student/my-courses.php');
}

// Handle Interactions: Complete, Note, Bookmark
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';
    $current_lecture = (int)($_POST['lecture_id'] ?? $lecture_id);

    if ($action === 'complete') {
        $stmt = $pdo->prepare("INSERT IGNORE INTO progress (user_id, lecture_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $current_lecture]);
        set_flash_message('success', 'Lecture completed!');
    } elseif ($action === 'add_note') {
        $note_content = trim($_POST['note_content'] ?? '');
        if (!empty($note_content)) {
            $stmt = $pdo->prepare("INSERT INTO notes (user_id, lecture_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $current_lecture, $note_content]);
            set_flash_message('success', 'Personal note saved.');
        }
    } elseif ($action === 'bookmark') {
        $stmt = $pdo->prepare("INSERT IGNORE INTO bookmarks (user_id, lecture_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $current_lecture]);
        set_flash_message('success', 'Lecture bookmarked.');
    }

    redirect("/student/player.php?course=$course_id&lecture=$current_lecture");
}

// Fetch Course
$stmt = $pdo->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

// Fetch Curriculum
$stmt = $pdo->prepare("
    SELECT m.id as module_id, m.title as module_title, l.id as lecture_id, l.title as lecture_title 
    FROM modules m JOIN lectures l ON m.id = l.module_id 
    WHERE m.course_id = ? ORDER BY m.order_num, l.order_num
");
$stmt->execute([$course_id]);
$curriculum = $stmt->fetchAll();

if (empty($lecture_id) && !empty($curriculum)) {
    $lecture_id = $curriculum[0]['lecture_id']; // Default to first lecture
}

// Fetch Active Lecture
$stmt = $pdo->prepare("SELECT * FROM lectures WHERE id = ?");
$stmt->execute([$lecture_id]);
$active_lecture = $stmt->fetch();

// Fetch Completed Lectures & Calculate Progress
$stmt = $pdo->prepare("
    SELECT p.lecture_id FROM progress p
    JOIN lectures l ON p.lecture_id = l.id
    JOIN modules m ON l.module_id = m.id
    WHERE p.user_id = ? AND m.course_id = ?
");
$stmt->execute([$_SESSION['user_id'], $course_id]);
$completed = $stmt->fetchAll(PDO::FETCH_COLUMN);

$total_lectures = count($curriculum);
$completed_count = count($completed);
$progress_percent = $total_lectures > 0 ? round(($completed_count / $total_lectures) * 100) : 0;

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0;"><?= h($course['title']) ?></h2>
        <!-- Progress Bar -->
        <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
            <div style="flex: 1; background: var(--bg-color); height: 8px; border-radius: 4px; overflow: hidden; width: 200px;">
                <div style="background: var(--success); height: 100%; width: <?= $progress_percent ?>%; transition: width 0.5s;"></div>
            </div>
            <span style="font-size: 0.85rem; color: gray; font-weight: bold;"><?= $progress_percent ?>% Completed</span>
        </div>
    </div>
    <a href="my-courses.php" class="btn btn-outline">Exit Player</a>
</div>

<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <!-- Course Curriculum Sidebar -->
    <aside class="sidebar card" style="flex: 1; min-width: 250px; max-width: 320px; max-height: 80vh; overflow-y: auto;">
        <h3>Course Content</h3>
        <ul style="list-style: none; padding-top: 15px;">
            <?php
            $current_module = null;
            foreach ($curriculum as $item):
                if ($current_module !== $item['module_id']):
                    $current_module = $item['module_id'];
                    echo "<li style='margin-top: 15px; font-weight: bold; border-bottom: 1px solid var(--border-color); padding-bottom: 5px;'>" . h($item['module_title']) . "</li>";
                endif;

                $is_active = ($item['lecture_id'] == $lecture_id) ? 'font-weight: bold; color: var(--primary-color);' : '';
                $is_completed = in_array($item['lecture_id'], $completed) ? '<span style="color:var(--success);">✓</span> ' : '<span style="color:gray;">○</span> ';
            ?>
                <li style="margin: 8px 0 8px 10px; font-size: 0.9rem;">
                    <a href="?course=<?= h($course_id) ?>&lecture=<?= h($item['lecture_id']) ?>" style="<?= $is_active ?>">
                        <?= $is_completed . h($item['lecture_title']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <!-- Video and Content Area -->
    <div class="dashboard-content" style="flex: 3; min-width: 300px;">
        <?php if ($active_lecture): ?>
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="margin: 0;"><?= h($active_lecture['title']) ?></h2>

                    <!-- Bookmark Button -->
                    <form method="POST" action="player.php?course=<?= h($course_id) ?>&lecture=<?= h($lecture_id) ?>" style="margin: 0;">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action" value="bookmark">
                        <button type="submit" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.85rem;" title="Save to Bookmarks">🔖 Bookmark</button>
                    </form>
                </div>

                <!-- Smart Video Player -->
                <?php if (!empty($active_lecture['video_url'])): ?>
                    <div style="background: #000; margin-bottom: 20px; border-radius: 8px; overflow: hidden; display: flex; justify-content: center; align-items: center; min-height: 400px;">
                        <?php
                        $vid_url = $active_lecture['video_url'];
                        if (strpos($vid_url, 'youtube.com') !== false || strpos($vid_url, 'youtu.be') !== false) {
                            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $vid_url, $match);
                            $yt_id = $match[1] ?? null;
                            if ($yt_id) {
                                echo '<iframe width="100%" height="450" src="https://www.youtube.com/embed/' . h($yt_id) . '" frameborder="0" allowfullscreen style="display: block;"></iframe>';
                            }
                        } else {
                            echo '<video width="100%" height="450" controls style="display: block; background: #000; max-height: 450px;"><source src="' . h($vid_url) . '" type="video/mp4"></video>';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <div style="line-height: 1.8; margin-bottom: 30px; font-size: 1.05rem;">
                    <?= nl2br(h($active_lecture['content'])) ?>
                </div>

                <hr style="border: 1px solid var(--border-color); margin: 20px 0;">

                <!-- Completion & Notes Section -->
                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <!-- Mark Complete Logic -->
                        <?php if (!in_array($active_lecture['id'], $completed)): ?>
                            <form method="POST" action="player.php?course=<?= h($course_id) ?>&lecture=<?= h($lecture_id) ?>">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="complete">
                                <button type="submit" class="btn btn-success btn-block" style="padding: 15px; font-size: 1.1rem;">Mark Lecture as Complete</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-success btn-block" disabled style="padding: 15px; font-size: 1.1rem; opacity: 0.8;">✓ Completed</button>
                        <?php endif; ?>

                        <!-- Certificate Generation Logic (Only shows when 100% completed) -->
                        <?php if ($progress_percent == 100): ?>
                            <div style="margin-top: 20px; background: #f0fdf4; border: 1px solid var(--success); padding: 15px; border-radius: var(--radius); text-align: center;">
                                <h4 style="color: var(--success); margin-bottom: 5px;">Course Completed! 🎉</h4>
                                <form method="POST" action="generate-certificate.php" style="margin: 0;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="course_id" value="<?= h($course_id) ?>">
                                    <button type="submit" class="btn mt-2" style="background: var(--success); color: white; width: 100%;">
                                        🏆 Claim My Certificate
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="background: var(--bg-color); padding: 15px; border-radius: var(--radius);">
                        <h4 style="margin-bottom: 10px;">📝 Save a Quick Note</h4>
                        <form method="POST" action="player.php?course=<?= h($course_id) ?>&lecture=<?= h($lecture_id) ?>" style="margin: 0;">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="add_note">
                            <textarea name="note_content" rows="3" class="form-control" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid var(--border-color);" placeholder="Type a key takeaway here..."></textarea>
                            <button type="submit" class="btn btn-primary mt-2" style="padding: 5px 15px; font-size: 0.85rem;">Save Note</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card text-center">
                <p>Select a lecture from the curriculum to begin.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>