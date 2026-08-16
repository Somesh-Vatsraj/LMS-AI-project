<nav class="navbar">
    <div class="nav-container">
        <a href="<?= BASE_URL ?>/index.php" class="brand">LMS-AI</a>
        <button class="mobile-toggle" id="mobileToggle">&#9776;</button>
        <div class="nav-links" id="navLinks">
            <a href="<?= BASE_URL ?>/courses.php">Courses</a>
            <a href="<?= BASE_URL ?>/about.php">About</a>
            <a href="<?= BASE_URL ?>/contact.php">Contact</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= BASE_URL ?>/<?= strtolower($_SESSION['user_role']) ?>/index.php">Dashboard</a>
                <a href="<?= BASE_URL ?>/logout.php">Logout (<?= h($_SESSION['user_name']) ?>)</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php">Login</a>
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>