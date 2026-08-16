<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $course_id = (int)$_POST['course_id'];

    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    create_audit_log($pdo, $_SESSION['user_id'], 'Delete Course', "Admin deleted course ID: $course_id");

    set_flash_message('success', 'Course permanently deleted.');
    redirect('/admin/courses.php');
}

$stmt = $pdo->query("
    SELECT c.*, u.name as instructor_name, cat.name as category_name,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as students
    FROM courses c
    LEFT JOIN users u ON c.instructor_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    ORDER BY c.created_at DESC
");
$courses = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <div class="dashboard-content card" style="flex: 3; min-width: 300px; overflow-x: auto;">
        <h2>All Platform Courses</h2>

        <table style="width: 100%; min-width: 700px;">
            <thead>
                <tr>
                    <th>Title & Instructor</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Students</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $c): ?>
                    <tr>
                        <td>
                            <strong><?= h($c['title']) ?></strong><br>
                            <span style="font-size: 0.85rem; color: gray;">By <?= h($c['instructor_name']) ?></span>
                        </td>
                        <td><?= h($c['category_name']) ?></td>
                        <td>
                            <span style="background: <?= $c['status'] === 'Published' ? '#def7ec' : '#fde8e8' ?>; color: <?= $c['status'] === 'Published' ? '#03543f' : '#9b1c1c' ?>; padding: 3px 8px; border-radius: 12px; font-size: 0.8rem;">
                                <?= h($c['status']) ?>
                            </span>
                        </td>
                        <td><?= h($c['students']) ?></td>
                        <td>
                            <form method="POST" action="courses.php" onsubmit="return confirm('Are you sure you want to delete this course entirely?');">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="course_id" value="<?= h($c['id']) ?>">
                                <button type="submit" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger); padding: 4px 10px; font-size: 0.85rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>