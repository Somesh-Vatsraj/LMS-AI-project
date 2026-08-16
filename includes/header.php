<?php
// Database se Theme Settings (Colors/Logo) nikaalna
$stmt = $pdo->query("SELECT key_name, key_value FROM appearance_settings");
$raw_appearance = $stmt->fetchAll();

// Default colors agar SQL mein kuch na mile
$theme_settings = [
    'primary_color' => '#4361ee', // Default Blue
    'logo_text' => 'LMS-AI'
];

foreach ($raw_appearance as $row) {
    $theme_settings[$row['key_name']] = $row['key_value'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($theme_settings['logo_text']) ?> - Learning Platform</title>

    <!-- Main Static CSS File -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">

    <!-- Dynamic CSS injected from SQL Database -->
    <style>
        :root {
            /* SQL se aaya hua primary color CSS variable ko override karega */
            --primary-color: <?= h($theme_settings['primary_color']) ?> !important;
        }

        /* Buttons ka hover effect primary color ke hisaab se thoda dark karne ke liye */
        .btn-primary:hover,
        a:hover {
            filter: brightness(0.9);
        }
    </style>
</head>

<body>

    <?php require_once __DIR__ . '/navbar.php'; ?>

    <main class="container">
        <?php display_flash_messages(); ?>