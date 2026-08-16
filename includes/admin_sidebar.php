<?php
// Pata lagayein ki user abhi kis page par hai
$current_page = basename($_SERVER['PHP_SELF']);

// Helper function active link ko highlight karne ke liye
function is_active($page, $current)
{
    return $page === $current ? 'style="font-weight: bold; color: var(--primary-color);"' : '';
}
?>
<aside class="sidebar card" style="flex: 1; min-width: 250px; max-width: 300px; max-height: 100vh; overflow-y: auto; position: sticky; top: 80px;">
    <h3>Admin Menu</h3>
    <ul style="list-style: none; padding-top: 15px;">
        <li style="margin-bottom: 10px;"><a href="index.php" <?= is_active('index.php', $current_page) ?>>Dashboard</a></li>

        <h4 style="margin: 15px 0 5px 0; font-size: 0.85rem; color: gray; text-transform: uppercase;">Users & Analytics</h4>
        <li style="margin-bottom: 10px;"><a href="users.php" <?= is_active('users.php', $current_page) ?>>Manage Users</a></li>
        <li style="margin-bottom: 10px;"><a href="enrollments.php" <?= is_active('enrollments.php', $current_page) ?>>Enrollments</a></li>
        <li style="margin-bottom: 10px;"><a href="reports.php" <?= is_active('reports.php', $current_page) ?>>Reports & Analytics</a></li>

        <h4 style="margin: 15px 0 5px 0; font-size: 0.85rem; color: gray; text-transform: uppercase;">Courses</h4>
        <li style="margin-bottom: 10px;"><a href="categories.php" <?= is_active('categories.php', $current_page) ?>>Categories</a></li>
        <li style="margin-bottom: 10px;"><a href="courses.php" <?= is_active('courses.php', $current_page) ?>>All Courses</a></li>
        <li style="margin-bottom: 10px;"><a href="course-approvals.php" <?= is_active('course-approvals.php', $current_page) ?>>Course Approvals</a></li>

        <h4 style="margin: 15px 0 5px 0; font-size: 0.85rem; color: gray; text-transform: uppercase;">Engagement</h4>
        <li style="margin-bottom: 10px;"><a href="assignments.php" <?= is_active('assignments.php', $current_page) ?>>Assignments</a></li>
        <li style="margin-bottom: 10px;"><a href="quizzes.php" <?= is_active('quizzes.php', $current_page) ?>>Quizzes</a></li>
        <li style="margin-bottom: 10px;"><a href="discussions.php" <?= is_active('discussions.php', $current_page) ?>>Discussions</a></li>
        <li style="margin-bottom: 10px;"><a href="announcements.php" <?= is_active('announcements.php', $current_page) ?>>Announcements</a></li>

        <h4 style="margin: 15px 0 5px 0; font-size: 0.85rem; color: gray; text-transform: uppercase;">System</h4>
        <li style="margin-bottom: 10px;"><a href="notifications.php" <?= is_active('notifications.php', $current_page) ?>>System Notifications</a></li>
        <li style="margin-bottom: 10px;"><a href="appearance.php" <?= is_active('appearance.php', $current_page) ?>>Appearance UI</a></li>
        <li style="margin-bottom: 10px;"><a href="settings.php" <?= is_active('settings.php', $current_page) ?>>Site Settings</a></li>
        <li style="margin-bottom: 10px;"><a href="audit-logs.php" <?= is_active('audit-logs.php', $current_page) ?>>Audit Logs</a></li>

        <li style="margin-top: 20px;"><a href="../logout.php" class="btn btn-outline btn-block text-center">Logout</a></li>
    </ul>
</aside>