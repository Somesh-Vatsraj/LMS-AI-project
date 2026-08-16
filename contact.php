<?php
require_once __DIR__ . '/config/app.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        set_flash_message('error', 'Please fill all required fields.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        set_flash_message('success', 'Thank you! Your message has been received.');
        redirect('/contact.php');
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="contact-container card">
    <h2>Contact Us</h2>
    <form method="POST" action="contact.php">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Subject</label>
            <input type="text" name="subject">
        </div>
        <div class="form-group">
            <label>Message</label>
            <textarea name="message" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>