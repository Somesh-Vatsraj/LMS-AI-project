<?php
require_once __DIR__ . '/../config/app.php';

// Check if user is logged in as Student
if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    redirect('/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $course_id = (int)$_POST['course_id'];
    $user_id = $_SESSION['user_id'];

    // 1. Check if the user is enrolled
    $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    if (!$stmt->fetch()) {
        set_flash_message('error', 'You are not enrolled in this course.');
        redirect('/student/my-courses.php');
    }

    // 2. Check if a certificate already exists
    $stmt = $pdo->prepare("SELECT id FROM certificates WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    if ($stmt->fetch()) {
        set_flash_message('success', 'You have already earned this certificate!');
        redirect('/student/certificates.php');
    }

    // 3. Count Total Lectures in the Course
    $stmt = $pdo->prepare("
        SELECT COUNT(l.id) FROM lectures l
        JOIN modules m ON l.module_id = m.id
        WHERE m.course_id = ?
    ");
    $stmt->execute([$course_id]);
    $total_lectures = (int)$stmt->fetchColumn();

    // 4. Count Completed Lectures by the Student
    $stmt = $pdo->prepare("
        SELECT COUNT(p.id) FROM progress p
        JOIN lectures l ON p.lecture_id = l.id
        JOIN modules m ON l.module_id = m.id
        WHERE p.user_id = ? AND m.course_id = ?
    ");
    $stmt->execute([$user_id, $course_id]);
    $completed_lectures = (int)$stmt->fetchColumn();

    // 5. Verify 100% Completion
    if ($total_lectures > 0 && $completed_lectures >= $total_lectures) {
        // Generate Certificate (Insert into Database)
        $certificate_code = 'CERT-' . strtoupper(uniqid()) . '-' . $user_id;

        // Agar aapke DB mein certificate_code column nahi hai, toh usey SQL update mein add kar lena, 
        // ya sirf basic details insert karein:
        $stmt = $pdo->prepare("INSERT INTO certificates (user_id, course_id, issued_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $course_id]);

        set_flash_message('success', 'Congratulations! Your certificate has been generated.');
        redirect('/student/certificates.php');
    } else {
        $percent = $total_lectures > 0 ? round(($completed_lectures / $total_lectures) * 100) : 0;
        set_flash_message('warning', "You need to complete 100% of the course. Current progress: $percent%.");
        redirect('/student/player.php?course=' . $course_id);
    }
} else {
    redirect('/student/my-courses.php');
}
