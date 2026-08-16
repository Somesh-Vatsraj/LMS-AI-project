<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Student') {
    redirect('/login.php');
}

// Fetch all badges available in the system
$stmt = $pdo->query("SELECT * FROM badges ORDER BY id ASC");
$all_badges = $stmt->fetchAll();

// Fetch badges specifically earned by this user
$stmt = $pdo->prepare("SELECT badge_id, awarded_at FROM user_badges WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$earned_raw = $stmt->fetchAll();

// Convert to an easy-to-check lookup array [badge_id => awarded_at]
$earned_badges = [];
foreach ($earned_raw as $row) {
    $earned_badges[$row['badge_id']] = $row['awarded_at'];
}

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/student_sidebar.php'; ?>

    <div class="dashboard-content" style="flex: 3; min-width: 300px;">
        <div class="card">
            <h2>My Badges & Achievements</h2>
            <p style="color: gray;">Complete courses and maintain streaks to unlock rewards.</p>
        </div>

        <div class="grid mt-2">
            <?php foreach ($all_badges as $badge): ?>
                <?php
                $is_earned = isset($earned_badges[$badge['id']]);
                $border_color = $is_earned ? 'var(--primary-color)' : 'var(--border-color)';
                $opacity = $is_earned ? '1' : '0.5';
                $bg_color = $is_earned ? '#f0f5ff' : 'var(--secondary-color)';
                ?>
                <div class="card text-center" style="border: 2px solid <?= $border_color ?>; opacity: <?= $opacity ?>; background: <?= $bg_color ?>; padding: 30px 15px;">
                    <div style="font-size: 3.5rem; margin-bottom: 15px;">
                        <?php
                        // Simulate icons with emojis if filenames are stored, or display an image.
                        // Assuming $badge['icon'] is text or standard. We will fallback to emojis.
                        echo $is_earned ? '🏆' : '🔒';
                        ?>
                    </div>
                    <h3 style="margin-bottom: 10px; color: <?= $is_earned ? 'var(--primary-color)' : 'gray' ?>;">
                        <?= h($badge['name']) ?>
                    </h3>
                    <p style="font-size: 0.9rem; color: var(--text-muted);">
                        <?= h($badge['description']) ?>
                    </p>

                    <?php if ($is_earned): ?>
                        <div style="margin-top: 15px; font-size: 0.8rem; font-weight: bold; color: var(--success);">
                            Unlocked on: <br> <?= date('M d, Y', strtotime($earned_badges[$badge['id']])) ?>
                        </div>
                    <?php else: ?>
                        <div style="margin-top: 15px; font-size: 0.8rem; font-weight: bold; color: gray;">
                            Locked
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (empty($all_badges)): ?>
                <p>Gamification is currently being set up. Check back later!</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>