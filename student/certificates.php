<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    redirect('/login.php');
}

// Fetch earned certificates
$stmt = $pdo->prepare("
    SELECT cert.*, c.title as course_title, u.name as instructor_name 
    FROM certificates cert
    JOIN courses c ON cert.course_id = c.id
    LEFT JOIN users u ON c.instructor_id = u.id
    WHERE cert.user_id = ?
    ORDER BY cert.issued_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$certificates = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Naya CSS specifically Print ke liye -->
<style>
    @media print {

        /* Page par maujood sab kuch chhupa do */
        body * {
            visibility: hidden;
        }

        /* Sirf us area ko dikhao jispar .print-area class lagi hai */
        .print-area,
        .print-area * {
            visibility: visible;
        }

        /* Certificate ko page ke top-left par set karke full width do */
        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: 10px solid var(--primary-color) !important;
            border-radius: 15px;
            padding: 50px !important;
            background: white !important;
            box-shadow: none !important;
        }

        /* Print karte time Button ko chhupa do taaki wo paper par na aaye */
        .no-print {
            display: none !important;
        }
    }
</style>

<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/student_sidebar.php'; ?>

    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2 class="no-print">My Certificates</h2>
        <p class="no-print" style="color: gray;">Achievements and course completions.</p>

        <div class="grid mt-2">
            <?php if (empty($certificates)): ?>
                <p>You haven't earned any certificates yet. Keep learning!</p>
            <?php else: ?>
                <?php foreach ($certificates as $cert): ?>
                    <!-- Har certificate ko ek unique ID di gayi hai -->
                    <div id="cert-<?= h($cert['id']) ?>" class="card text-center" style="border: 2px solid var(--primary-color); background: linear-gradient(to bottom right, var(--secondary-color), var(--bg-color));">

                        <!-- Certificate Content (Jo print hoga) -->
                        <div style="font-size: 3rem; margin-bottom: 10px;">🎓</div>
                        <h3 style="color: var(--primary-color); margin-bottom: 5px;">Certificate of Completion</h3>
                        <p style="font-size: 0.9rem; color: gray;">This certifies that</p>
                        <h2 style="margin: 10px 0;"><?= h($_SESSION['user_name']) ?></h2>
                        <p style="font-size: 0.9rem; color: gray;">has successfully completed the course</p>
                        <h4 style="margin: 10px 0;"><?= h($cert['course_title']) ?></h4>
                        <p style="font-size: 0.8rem; color: gray;">Issued on: <?= date('F d, Y', strtotime($cert['issued_at'])) ?></p>

                        <hr class="no-print" style="border: 1px solid var(--border-color); margin: 15px 0;">

                        <!-- Print Button (Paper par print nahi hoga due to .no-print class) -->
                        <button class="btn btn-outline btn-block no-print" onclick="printCertificate('cert-<?= h($cert['id']) ?>')">
                            Print / Save as PDF
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript function Print Control ke liye -->
<script>
    function printCertificate(certId) {
        // Jis certificate par click hua hai, usko get karein
        var certToPrint = document.getElementById(certId);

        // Usme 'print-area' class add karein (Jisse CSS sirf isko dikhayega)
        certToPrint.classList.add('print-area');

        // Browser ka print dialog open karein
        window.print();

        // Print cancel/save hone ke baad wapas normal kardien
        certToPrint.classList.remove('print-area');
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>