<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $course_id = (int)$_POST['course_id'];
    $status = $_POST['status'] ?? ''; // 'Approved' or 'Rejected'
    $admin_notes = trim($_POST['admin_notes'] ?? '');

    if (in_array($status, ['Approved', 'Rejected'])) {
        // Record the approval/rejection
        $stmt = $pdo->prepare("INSERT INTO course_approvals (course_id, status, admin_notes) VALUES (?, ?, ?)");
        $stmt->execute([$course_id, $status, $admin_notes]);

        // Update the main course status
        $course_new_status = ($status === 'Approved') ? 'Published' : 'Draft';
        $stmt = $pdo->prepare("UPDATE courses SET status = ? WHERE id = ?");
        $stmt->execute([$course_new_status, $course_id]);

        create_audit_log($pdo, $_SESSION['user_id'], 'Course Approval', "$status course ID: $course_id");
        set_flash_message('success', "Course has been $status.");
    }
    redirect('/admin/course-approvals.php');
}

// Fetch courses waiting for approval (Status = 'Draft' but let's assume instructors want to publish them)
// Realistically, instructors should have a 'Pending' status, but we'll list Drafts here for the Admin to review.
$stmt = $pdo->query("
    SELECT c.*, u.name as instructor_name, cat.name as category_name
    FROM courses c
    LEFT JOIN users u ON c.instructor_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE c.status = 'Draft'
    ORDER BY c.updated_at DESC
");
$pending_courses = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>Course Approvals</h2>
        <p>Review draft courses and publish them to the live site.</p>

        <?php if (empty($pending_courses)): ?>
            <div class="alert alert-success mt-2">No courses pending approval!</div>
        <?php else: ?>
            <div class="grid mt-2">
                <?php foreach ($pending_courses as $c): ?>
                    <div class="card" style="border: 1px solid var(--border-color); box-shadow: none;">
                        <h3><?= h($c['title']) ?></h3>
                        <p style="font-size: 0.9rem;">Instructor: <strong><?= h($c['instructor_name']) ?></strong></p>
                        <p style="font-size: 0.9rem;">Category: <?= h($c['category_name']) ?> | Price: $<?= h($c['price']) ?></p>

                        <form method="POST" action="course-approvals.php" style="margin-top: 15px;">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="course_id" value="<?= h($c['id']) ?>">

                            <div class="form-group">
                                <input type="text" name="admin_notes" placeholder="Feedback / Notes (Optional)">
                            </div>

                            <div style="display: flex; gap: 10px;">
                                <button type="submit" name="status" value="Approved" class="btn btn-success" style="flex: 1;">Approve & Publish</button>
                                <button type="submit" name="status" value="Rejected" class="btn btn-outline" style="flex: 1; color: var(--danger); border-color: var(--danger);">Reject</button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>