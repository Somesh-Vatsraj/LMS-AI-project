<?php
require_once __DIR__ . '/config/app.php';

$cert_id = (int)($_GET['id'] ?? 0);
$certificate = null;

if ($cert_id) {
    // Fetch certificate details securely
    $stmt = $pdo->prepare("
        SELECT cert.*, c.title as course_title, u.name as student_name 
        FROM certificates cert
        JOIN courses c ON cert.course_id = c.id
        JOIN users u ON cert.user_id = u.id
        WHERE cert.id = ?
    ");
    $stmt->execute([$cert_id]);
    $certificate = $stmt->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 600px; margin: 50px auto; padding: 20px;">
    <?php if ($certificate): ?>
        <div class="card text-center" style="border-top: 6px solid var(--success); box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div style="font-size: 5rem; color: var(--success); margin-bottom: 10px;">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 style="color: var(--success); font-size: 2rem;">Verified Certificate</h2>
            <p style="color: gray; margin-bottom: 20px; font-size: 1.1rem;">This certificate is an official, valid document.</p>

            <div style="background: #f8f9fa; padding: 25px; border-radius: var(--radius); text-align: left; margin-bottom: 20px; border: 1px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <span style="color: gray;">Student Name:</span>
                    <strong style="font-size: 1.1rem;"><?= h($certificate['student_name']) ?></strong>
                </div>
                <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 15px 0;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <span style="color: gray;">Course Completed:</span>
                    <strong style="font-size: 1.1rem; text-align: right; max-width: 60%;"><?= h($certificate['course_title']) ?></strong>
                </div>
                <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 15px 0;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <span style="color: gray;">Issue Date:</span>
                    <strong style="font-size: 1.1rem;"><?= date('F d, Y', strtotime($certificate['issued_at'])) ?></strong>
                </div>
                <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 15px 0;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: gray;">Certificate ID:</span>
                    <strong style="font-size: 1.1rem; color: var(--primary-color);">#<?= str_pad($certificate['id'], 6, '0', STR_PAD_LEFT) ?></strong>
                </div>
            </div>

            <a href="index.php" class="btn btn-outline btn-block text-center">Go to Homepage</a>
        </div>
    <?php else: ?>
        <div class="card text-center" style="border-top: 6px solid var(--danger);">
            <div style="font-size: 5rem; color: var(--danger); margin-bottom: 10px;">
                <i class="fas fa-times-circle"></i>
            </div>
            <h2 style="color: var(--danger); font-size: 2rem;">Invalid Certificate</h2>
            <p style="color: gray; font-size: 1.1rem;">We could not find any record of this certificate in our system. It may be fake or incorrectly scanned.</p>
            <a href="index.php" class="btn btn-outline mt-2">Go Back</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>