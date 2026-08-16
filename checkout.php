<?php
require_once __DIR__ . '/config/app.php';

// Check if user is logged in and is a Student
if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    set_flash_message('error', 'Please login as a Student to purchase courses.');
    redirect('/login.php');
}

$course_id = (int)($_GET['course_id'] ?? 0);
if (!$course_id) redirect('/index.php');

// Fetch course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    set_flash_message('error', 'Course not found.');
    redirect('/index.php');
}

// Check if student is already enrolled
$stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$_SESSION['user_id'], $course_id]);
if ($stmt->fetch()) {
    set_flash_message('success', 'You are already enrolled in this course!');
    redirect('/student/my-courses.php');
}

// Handle Payment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $amount = $course['price'];

    $pdo->beginTransaction();
    try {
        // 1. Record the Payment in payments table
        $stmt = $pdo->prepare("INSERT INTO payments (user_id, course_id, amount, status) VALUES (?, ?, ?, 'Completed')");
        $stmt->execute([$_SESSION['user_id'], $course_id, $amount]);

        // 2. Add student to enrollments table
        $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $course_id]);

        $pdo->commit();
        set_flash_message('success', 'Payment Successful! Welcome to the course.');
        redirect('/student/my-courses.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash_message('error', 'Payment failed. Please try again.');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 600px; margin: 40px auto;">
    <div class="card text-center" style="border-top: 5px solid var(--primary-color);">
        <h2>Secure Checkout</h2>
        <p style="color: gray;">Review your order details before payment.</p>

        <div style="background: var(--bg-color); padding: 20px; border-radius: var(--radius); margin: 20px 0; text-align: left;">
            <h3 style="margin-bottom: 5px;"><?= h($course['title']) ?></h3>
            <p style="font-size: 0.9rem; color: gray;">Course Access: Lifetime</p>

            <hr style="border: 1px solid var(--border-color); margin: 15px 0;">

            <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold;">
                <span>Total Amount:</span>
                <span style="color: var(--success);"><?= $course['price'] > 0 ? '$' . number_format($course['price'], 2) : 'FREE' ?></span>
            </div>
        </div>

        <form method="POST" action="checkout.php?course_id=<?= h($course_id) ?>">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <div style="background: #fdfdfd; border: 1px solid var(--border-color); padding: 15px; border-radius: var(--radius); margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 15px;">
                <i class="fas fa-lock" style="color: gray;"></i>
                <span style="font-size: 0.9rem; color: gray;">256-bit Secure Encrypted Payment</span>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="font-size: 1.1rem; padding: 15px;" onsubmit="return confirm('Confirm payment of $<?= $course['price'] ?>?');">
                <?= $course['price'] > 0 ? 'Pay Now & Enroll' : 'Enroll for Free' ?>
            </button>
        </form>

        <a href="course-details.php?slug=<?= h($course['slug']) ?>" class="btn btn-outline mt-2 btn-block">Cancel</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>