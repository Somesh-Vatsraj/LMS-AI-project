<?php
$current_page = basename($_SERVER['PHP_SELF']);
function is_inst_active($page, $current)
{
    return $page === $current ? 'style="font-weight: bold; color: var(--primary-color);"' : '';
}
?>
<aside class="sidebar card" style="flex: 1; min-width: 250px; max-width: 300px; max-height: 100vh; overflow-y: auto; position: sticky; top: 80px;">
    <h3>Instructor Menu</h3>
    <ul style="list-style: none; padding-top: 15px;">
        <li style="margin-bottom: 10px;"><a href="index.php" <?= is_inst_active('index.php', $current_page) ?>>Dashboard</a></li>
        <li style="margin-bottom: 10px;"><a href="analytics.php" <?= is_inst_active('analytics.php', $current_page) ?>>My Analytics</a></li>

        <h4 style="margin: 15px 0 5px 0; font-size: 0.85rem; color: gray; text-transform: uppercase;">Teaching</h4>
        <li style="margin-bottom: 10px;"><a href="courses.php" <?= is_inst_active('courses.php', $current_page) ?>>Manage Courses</a></li>
        <li style="margin-bottom: 10px;"><a href="students.php" <?= is_inst_active('students.php', $current_page) ?>>My Students</a></li>

        <h4 style="margin: 15px 0 5px 0; font-size: 0.85rem; color: gray; text-transform: uppercase;">Assessments</h4>
        <li style="margin-bottom: 10px;"><a href="quizzes.php" <?= is_inst_active('quizzes.php', $current_page) ?>>Quizzes & Tests</a></li>
        <li style="margin-bottom: 10px;"><a href="assignments.php" <?= is_inst_active('assignments.php', $current_page) ?>>Assignments</a></li>
        <li style="margin-bottom: 10px;"><a href="submissions.php" <?= is_inst_active('submissions.php', $current_page) ?>>Grade Submissions</a></li>

        <h4 style="margin: 15px 0 5px 0; font-size: 0.85rem; color: gray; text-transform: uppercase;">Communication</h4>
        <li style="margin-bottom: 10px;"><a href="announcements.php" <?= is_inst_active('announcements.php', $current_page) ?>>Announcements</a></li>
        <li style="margin-bottom: 10px;"><a href="discussions.php" <?= is_inst_active('discussions.php', $current_page) ?>>Q&A Discussions</a></li>

        <h4 style="margin: 15px 0 5px 0; font-size: 0.85rem; color: gray; text-transform: uppercase;">Account</h4>
        <li style="margin-bottom: 10px;"><a href="profile.php" <?= is_inst_active('profile.php', $current_page) ?>>My Profile</a></li>
        <li style="margin-top: 20px;"><a href="../logout.php" class="btn btn-outline btn-block text-center">Logout</a></li>
    </ul>
</aside>