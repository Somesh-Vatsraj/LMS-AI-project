<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

// 1. Calculate Total Revenue
$stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'Completed'");
$total_revenue = $stmt->fetchColumn() ?: 0.00;

// 2. Calculate Total Certificates Issued
$stmt = $pdo->query("SELECT COUNT(*) FROM certificates");
$total_certificates = $stmt->fetchColumn();

// 3. Get Top 5 Most Popular Courses (By Enrollments)
$stmt = $pdo->query("
    SELECT c.title, u.name as instructor_name, 
    COUNT(e.id) as enrollment_count 
    FROM courses c 
    LEFT JOIN enrollments e ON c.id = e.course_id 
    LEFT JOIN users u ON c.instructor_id = u.id
    GROUP BY c.id 
    ORDER BY enrollment_count DESC 
    LIMIT 5
");
$top_courses = $stmt->fetchAll();

// Find max enrollment for CSS Chart Scaling
$max_enrollment = 0;
foreach ($top_courses as $tc) {
    if ($tc['enrollment_count'] > $max_enrollment) $max_enrollment = $tc['enrollment_count'];
}
$max_enrollment = $max_enrollment > 0 ? $max_enrollment : 1;

// 4. Get Top 5 Earning Courses (By Revenue)
$stmt = $pdo->query("
    SELECT c.title, SUM(p.amount) as course_revenue 
    FROM payments p 
    JOIN courses c ON p.course_id = c.id 
    WHERE p.status = 'Completed' 
    GROUP BY c.id 
    ORDER BY course_revenue DESC 
    LIMIT 5
");
$revenue_courses = $stmt->fetchAll();

$max_course_revenue = 0;
foreach ($revenue_courses as $rc) {
    if ($rc['course_revenue'] > $max_course_revenue) $max_course_revenue = $rc['course_revenue'];
}
$max_course_revenue = $max_course_revenue > 0 ? $max_course_revenue : 1;

// 5. Get Platform Growth (Signups by Role)
$stmt = $pdo->query("
    SELECT r.name as role_name, COUNT(ur.user_id) as total_users
    FROM roles r
    LEFT JOIN user_roles ur ON r.id = ur.role_id
    GROUP BY r.id
");
$user_demographics = $stmt->fetchAll();

$total_demographic_users = 0;
foreach ($user_demographics as $demo) {
    $total_demographic_users += $demo['total_users'];
}
$total_demographic_users = $total_demographic_users > 0 ? $total_demographic_users : 1;

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Embedded Pure CSS for Charts to keep it lightweight and external-library-free -->
<style>
    .css-chart-row {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .css-chart-label {
        width: 35%;
        font-size: 0.85rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-right: 15px;
    }

    .css-chart-track {
        width: 50%;
        background: var(--bg-color);
        height: 10px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .css-chart-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 1s ease-in-out;
    }

    .css-chart-value {
        width: 15%;
        text-align: right;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-main);
    }

    /* Demographics specific sizes */
    .demo-track {
        height: 20px;
        border-radius: 4px;
    }

    .demo-fill {
        height: 100%;
        border-radius: 4px;
        display: flex;
        align-items: center;
        padding-left: 10px;
        color: white;
        font-size: 0.75rem;
        font-weight: bold;
    }
</style>

<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="dashboard-content" style="flex: 3; min-width: 300px;">
        <h2>Platform Analytics & Reports</h2>
        <p style="color: gray;">Visual intelligence and platform health overview.</p>

        <!-- Top Overview Cards -->
        <div class="grid mt-2">
            <div class="card text-center" style="border-left: 4px solid var(--success);">
                <h3>Total Revenue</h3>
                <h2 style="font-size: 2.5rem; color: var(--success);">₹<?= number_format($total_revenue, 2) ?></h2>
            </div>
            <div class="card text-center" style="border-left: 4px solid var(--primary-color);">
                <h3>Certificates Issued</h3>
                <h2 style="font-size: 2.5rem; color: var(--primary-color);"><?= h($total_certificates) ?></h2>
            </div>
            <div class="card text-center" style="border-left: 4px solid var(--warning);">
                <h3>Total Users</h3>
                <h2 style="font-size: 2.5rem; color: var(--warning);"><?= h($total_demographic_users) ?></h2>
            </div>
        </div>

        <div class="grid mt-2">
            <!-- Chart 1: Top Courses by Enrollment -->
            <div class="card" style="flex: 1; min-width: 300px;">
                <h3>Most Popular Courses (Enrollments)</h3>
                <hr style="border: 1px solid var(--border-color); margin: 15px 0;">

                <?php if (empty($top_courses)): ?>
                    <p style="color: gray; text-align: center;">No enrollments yet.</p>
                <?php else: ?>
                    <?php foreach ($top_courses as $tc):
                        $percentage = ($tc['enrollment_count'] / $max_enrollment) * 100;
                    ?>
                        <div class="css-chart-row">
                            <div class="css-chart-label" title="<?= h($tc['title']) ?>"><?= h($tc['title']) ?></div>
                            <div class="css-chart-track">
                                <div class="css-chart-fill" style="width: <?= $percentage ?>%; background: var(--primary-color);"></div>
                            </div>
                            <div class="css-chart-value"><?= h($tc['enrollment_count']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Chart 2: Top Courses by Revenue -->
            <div class="card" style="flex: 1; min-width: 300px;">
                <h3>Top Earning Courses (Revenue)</h3>
                <hr style="border: 1px solid var(--border-color); margin: 15px 0;">

                <?php if (empty($revenue_courses)): ?>
                    <p style="color: gray; text-align: center;">No revenue generated yet.</p>
                <?php else: ?>
                    <?php foreach ($revenue_courses as $rc):
                        $percentage = ($rc['course_revenue'] / $max_course_revenue) * 100;
                    ?>
                        <div class="css-chart-row">
                            <div class="css-chart-label" title="<?= h($rc['title']) ?>"><?= h($rc['title']) ?></div>
                            <div class="css-chart-track">
                                <div class="css-chart-fill" style="width: <?= $percentage ?>%; background: var(--success);"></div>
                            </div>
                            <div class="css-chart-value">₹<?= number_format($rc['course_revenue'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chart 3: Demographics Breakdown -->
        <div class="card mt-2">
            <h3>User Demographics Breakdown</h3>
            <hr style="border: 1px solid var(--border-color); margin: 15px 0;">

            <?php foreach ($user_demographics as $demo):
                $percent = ($demo['total_users'] / $total_demographic_users) * 100;

                // Assign different colors based on roles
                $bar_color = 'var(--primary-color)';
                if (strtolower($demo['role_name']) === 'admin') $bar_color = 'var(--danger)';
                if (strtolower($demo['role_name']) === 'instructor') $bar_color = 'var(--warning)';
            ?>
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 5px;">
                        <strong><?= h($demo['role_name']) ?>s</strong>
                        <span style="color: gray;"><?= h($demo['total_users']) ?> Users (<?= round($percent, 1) ?>%)</span>
                    </div>
                    <div class="css-chart-track demo-track">
                        <div class="css-chart-fill demo-fill" style="width: <?= $percent ?>%; background: <?= $bar_color ?>;">
                            <?= $percent >= 5 ? round($percent) . '%' : '' ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>