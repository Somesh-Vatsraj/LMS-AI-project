<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Instructor') {
    redirect('/login.php');
}

// 1. Calculate Instructor's Total Revenue
$stmt = $pdo->prepare("
    SELECT SUM(p.amount) FROM payments p 
    JOIN courses c ON p.course_id = c.id 
    WHERE c.instructor_id = ? AND p.status = 'Completed'
");
$stmt->execute([$_SESSION['user_id']]);
$total_revenue = $stmt->fetchColumn() ?: 0.00;

// 2. Course-wise Enrollments
$stmt = $pdo->prepare("
    SELECT c.title, COUNT(e.id) as enrollment_count 
    FROM courses c 
    LEFT JOIN enrollments e ON c.id = e.course_id 
    WHERE c.instructor_id = ? 
    GROUP BY c.id 
    ORDER BY enrollment_count DESC
");
$stmt->execute([$_SESSION['user_id']]);
$courses_stats = $stmt->fetchAll();

$max_enrollment = 0;
foreach ($courses_stats as $cs) {
    if ($cs['enrollment_count'] > $max_enrollment) $max_enrollment = $cs['enrollment_count'];
}
$max_enrollment = $max_enrollment > 0 ? $max_enrollment : 1;

require_once __DIR__ . '/../includes/header.php';
?>
<style>
    .css-chart-row {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .css-chart-label {
        width: 40%;
        font-size: 0.9rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-right: 15px;
    }

    .css-chart-track {
        width: 50%;
        background: var(--bg-color);
        height: 12px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .css-chart-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 1s ease-in-out;
        background: var(--primary-color);
    }

    .css-chart-value {
        width: 10%;
        text-align: right;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-main);
    }
</style>

<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/instructor_sidebar.php'; ?>

    <div class="dashboard-content" style="flex: 3; min-width: 300px;">
        <h2>My Performance Analytics</h2>
        <p style="color: gray;">Track your course sales and student engagement.</p>

        <div class="grid mt-2">
            <div class="card text-center" style="border-left: 4px solid var(--success);">
                <h3>Total Earnings</h3>
                <h2 style="font-size: 2.5rem; color: var(--success);">₹<?= number_format($total_revenue, 2) ?></h2>
            </div>
            <div class="card text-center" style="border-left: 4px solid var(--primary-color);">
                <h3>Total Courses</h3>
                <h2 style="font-size: 2.5rem; color: var(--primary-color);"><?= count($courses_stats) ?></h2>
            </div>
        </div>

        <div class="card mt-2">
            <h3>Enrollments per Course</h3>
            <hr style="border: 1px solid var(--border-color); margin: 15px 0;">

            <?php if (empty($courses_stats)): ?>
                <p style="color: gray; text-align: center;">No courses published or enrolled yet.</p>
            <?php else: ?>
                <?php foreach ($courses_stats as $stat):
                    $percentage = ($stat['enrollment_count'] / $max_enrollment) * 100;
                ?>
                    <div class="css-chart-row">
                        <div class="css-chart-label" title="<?= h($stat['title']) ?>"><?= h($stat['title']) ?></div>
                        <div class="css-chart-track">
                            <div class="css-chart-fill" style="width: <?= $percentage ?>%;"></div>
                        </div>
                        <div class="css-chart-value"><?= h($stat['enrollment_count']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>