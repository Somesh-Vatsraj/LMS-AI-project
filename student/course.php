<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    redirect('/login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch all courses the student is enrolled in
$stmt = $pdo->prepare("
    SELECT c.id, c.title, c.slug, c.difficulty, u.name as instructor_name, e.enrolled_at 
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN users u ON c.instructor_id = u.id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$user_id]);
$my_courses = $stmt->fetchAll();

// Prepare statements for calculating progress (to be used in the loop below)
$stmtTotal = $pdo->prepare("
    SELECT COUNT(l.id) FROM lectures l 
    JOIN modules m ON l.module_id = m.id 
    WHERE m.course_id = ?
");
$stmtCompleted = $pdo->prepare("
    SELECT COUNT(p.id) FROM progress p 
    JOIN lectures l ON p.lecture_id = l.id 
    JOIN modules m ON l.module_id = m.id 
    WHERE m.course_id = ? AND p.user_id = ?
");

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">

    <!-- Student Sidebar -->
    <?php require_once __DIR__ . '/../includes/student_sidebar.php'; ?>

    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2>My Learning</h2>
                <p style="color: gray;">Pick up right where you left off.</p>
            </div>
            <a href="../courses.php" class="btn btn-outline">Explore More Courses</a>
        </div>

        <div class="grid">
            <?php if (empty($my_courses)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: var(--bg-color); border-radius: var(--radius); color: gray;">
                    <h3 style="margin-bottom: 10px;">You haven't enrolled in any courses yet.</h3>
                    <p>Start exploring and learn something new today!</p>
                    <a href="../courses.php" class="btn btn-primary mt-2">Browse Courses</a>
                </div>
            <?php else: ?>
                <?php foreach ($my_courses as $course):
                    // Calculate Progress for this specific course
                    $stmtTotal->execute([$course['id']]);
                    $total_lectures = (int)$stmtTotal->fetchColumn();

                    $stmtCompleted->execute([$course['id'], $user_id]);
                    $completed_lectures = (int)$stmtCompleted->fetchColumn();

                    $progress_percent = $total_lectures > 0 ? round(($completed_lectures / $total_lectures) * 100) : 0;
                ?>
                    <div class="card" style="display: flex; flex-direction: column; overflow: hidden; transition: transform 0.3s ease;">
                        <!-- Course Thumbnail Placeholder -->
                        <div style="height: 120px; background: linear-gradient(45deg, var(--primary-color), #76a9fa); margin: -15px -15px 15px -15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 2.5rem; font-weight: bold;">
                            <?= strtoupper(substr($course['title'], 0, 1)) ?>
                        </div>

                        <span style="font-size: 0.75rem; background: var(--bg-color); padding: 3px 8px; border-radius: 12px; color: gray; align-self: flex-start; margin-bottom: 10px;">
                            <?= h($course['difficulty'] ?? 'Beginner') ?>
                        </span>

                        <h3 style="margin-bottom: 5px; font-size: 1.1rem; line-height: 1.3;"><?= h($course['title']) ?></h3>
                        <p style="font-size: 0.85rem; color: gray; margin-bottom: 15px;">By <?= h($course['instructor_name']) ?></p>

                        <!-- Progress Bar UI -->
                        <div style="margin-top: auto; margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 5px; color: gray; font-weight: bold;">
                                <span>Progress</span>
                                <span><?= $progress_percent ?>%</span>
                            </div>
                            <div style="background: var(--bg-color); height: 8px; border-radius: 4px; overflow: hidden;">
                                <div style="background: <?= $progress_percent == 100 ? 'var(--success)' : 'var(--primary-color)' ?>; height: 100%; width: <?= $progress_percent ?>%; transition: width 0.5s;"></div>
                            </div>
                        </div>

                        <a href="player.php?course=<?= h($course['id']) ?>" class="btn <?= $progress_percent == 100 ? 'btn-outline' : 'btn-primary' ?> btn-block text-center">
                            <?= $progress_percent == 0 ? 'Start Learning' : ($progress_percent == 100 ? 'Review Course' : 'Resume Learning') ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>