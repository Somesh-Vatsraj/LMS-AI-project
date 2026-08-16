<?php
$current_page = basename($_SERVER['PHP_SELF']);
function is_stu_active($page, $current)
{
    return $page === $current ? 'style="font-weight: bold; color: var(--primary-color);"' : '';
}
?>
<aside class="sidebar card" style="flex: 1; min-width: 250px; max-width: 300px; max-height: 100vh; overflow-y: auto; position: sticky; top: 80px;">
    <h3>Student Menu</h3>
    <ul style="list-style: none; padding-top: 15px;">
        <li style="margin-bottom: 10px;"><a href="index.php" <?= is_stu_active('index.php', $current_page) ?>>Dashboard</a></li>

        <h4 style="margin: 15px 0 5px 0; font-size: 0.85rem; color: gray; text-transform: uppercase;">My Learning</h4>
        <li style="margin-bottom: 10px;"><a href="my-courses.php" <?= is_stu_active('my-courses.php', $current_page) ?>>My Courses</a></li>
        <li style="margin-bottom: 10px;"><a href="assignments.php" <?= is_stu_active('assignments.php', $current_page) ?>>Assignments</a></li>
        <li style="margin-bottom: 10px;"><a href="quizzes.php" <?= is_stu_active('quizzes.php', $current_page) ?>>Quizzes & Tests</a></li>
        <li style="margin-bottom: 10px;"><a href="ai-tutor.php" <?= is_stu_active('ai-tutor.php', $current_page) ?>>AI Tutor</a></li>

        <h4 style="margin: 15px 0 5px 0; font-size: 0.85rem; color: gray; text-transform: uppercase;">Study Materials</h4>
        <li style="margin-bottom: 10px;"><a href="notes.php" <?= is_stu_active('notes.php', $current_page) ?>>Personal Notes</a></li>
        <li style="margin-bottom: 10px;"><a href="bookmarks.php" <?= is_stu_active('bookmarks.php', $current_page) ?>>Bookmarked Lectures</a></li>

        <h4 style="margin: 15px 0 5px 0; font-size: 0.85rem; color: gray; text-transform: uppercase;">Achievements</h4>
        <li style="margin-bottom: 10px;"><a href="certificates.php" <?= is_stu_active('certificates.php', $current_page) ?>>My Certificates</a></li>
        <li style="margin-bottom: 10px;"><a href="badges.php" <?= is_stu_active('badges.php', $current_page) ?>>Badges & Rewards</a></li>

        <h4 style="margin: 15px 0 5px 0; font-size: 0.85rem; color: gray; text-transform: uppercase;">Account</h4>
        <li style="margin-bottom: 10px;"><a href="notifications.php" <?= is_stu_active('notifications.php', $current_page) ?>>Notification History</a></li>
        <li style="margin-bottom: 10px;"><a href="profile.php" <?= is_stu_active('profile.php', $current_page) ?>>My Profile</a></li>
        <li style="margin-top: 20px;"><a href="../logout.php" class="btn btn-outline btn-block text-center">Logout</a></li>
    </ul>
</aside>