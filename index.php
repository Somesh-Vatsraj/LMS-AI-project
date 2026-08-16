<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/header.php';
$courses = get_courses($pdo);
?>
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* In-page CSS for modern landing page enhancements */
    .hero {
        padding: 4rem 1rem;
        background: linear-gradient(135deg, #f8f9fa, #e1effe);
        border-radius: 0 0 2rem 2rem;
        margin-top: -20px;
        box-shadow: inset 0 -10px 20px rgba(0, 0, 0, 0.02);
    }

    .hero-badge {
        display: inline-block;
        padding: 5px 15px;
        background: rgba(67, 97, 238, 0.1);
        color: var(--primary-color);
        border-radius: 20px;
        font-weight: bold;
        font-size: 0.85rem;
        margin-bottom: 15px;
    }

    .hero h1 {
        font-size: 2.8rem;
        color: #111;
        margin-bottom: 15px;
        line-height: 1.2;
    }

    .hero-subtitle {
        font-size: 1.1rem;
        color: #555;
        max-width: 600px;
        margin: 0 auto 30px auto;
    }

    .search-form {
        max-width: 600px;
        margin: 0 auto;
    }

    .search-input-wrapper {
        display: flex;
        align-items: center;
        background: #fff;
        padding: 8px 12px;
        border-radius: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-color);
    }

    .search-input {
        border: none;
        outline: none;
        padding: 10px;
        flex: 1;
        font-size: 1rem;
        background: transparent;
    }

    .search-btn {
        border-radius: 25px;
        padding: 10px 25px;
        font-size: 1rem;
    }

    .feature-icon {
        width: 50px;
        height: 50px;
        background: #e1effe;
        color: var(--primary-color);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px auto;
    }

    .course-card {
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    }

    .course-thumb {
        height: 140px;
        background: linear-gradient(45deg, var(--primary-color), #76a9fa);
        margin: -15px -15px 15px -15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }

    .cta-section {
        background: var(--primary-color);
        color: white;
        padding: 3rem 2rem;
        border-radius: 1.5rem;
        text-align: center;
        margin: 4rem auto;
        max-width: 900px;
    }

    .cta-section h2 {
        color: white;
        margin-bottom: 10px;
    }

    .cta-section p {
        opacity: 0.9;
        margin-bottom: 20px;
        font-size: 1.1rem;
    }
</style>

<!-- HERO SECTION -->
<section class="hero text-center">
    <span class="hero-badge">✨ India's #1 AI-Powered Learning Platform</span>
    <h1>Learn Anything, Anytime<br>with your <span style="color: var(--primary-color);">AI Tutor</span></h1>
    <p class="hero-subtitle">Empower your future with interactive courses, real-time AI assistance, and industry-recognized certificates.</p>

    <form action="courses.php" method="GET" class="search-form">
        <div class="search-input-wrapper">
            <svg class="search-icon" style="color: gray; margin-left: 10px;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" name="search" placeholder="What do you want to learn today?" class="search-input" value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES) ?>">
            <button type="submit" class="btn btn-primary search-btn">Search</button>
        </div>
    </form>
</section>

<!-- FEATURES SECTION -->
<section class="features" style="margin-top: -30px; position: relative; z-index: 10;">
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
        <div class="card text-center">
            <div class="feature-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <h3>Expert Instructors</h3>
            <p style="color: gray; font-size: 0.9rem;">Learn from industry professionals with real-world experience.</p>
        </div>
        <div class="card text-center">
            <div class="feature-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                    <circle cx="12" cy="5" r="2"></circle>
                    <path d="M12 7v4"></path>
                    <line x1="8" y1="16" x2="8" y2="16"></line>
                    <line x1="16" y1="16" x2="16" y2="16"></line>
                </svg>
            </div>
            <h3>AI Powered</h3>
            <p style="color: gray; font-size: 0.9rem;">Get instant 24/7 help and explanations from our AI Tutor.</p>
        </div>
        <div class="card text-center">
            <div class="feature-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 15l-2 5l9-3l-3-9z"></path>
                    <circle cx="10" cy="8" r="5"></circle>
                </svg>
            </div>
            <h3>Earn Certificates</h3>
            <p style="color: gray; font-size: 0.9rem;">Showcase your validated skills to future employers.</p>
        </div>
    </div>
</section>

<!-- FEATURED COURSES SECTION -->
<section class="featured-courses" style="margin-top: 4rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
        <h2 style="margin: 0;">Top Featured Courses</h2>
        <a href="courses.php" style="color: var(--primary-color); font-weight: bold; text-decoration: none;">View All &rarr;</a>
    </div>

    <div class="grid">
        <?php if (empty($courses)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: var(--bg-color); border-radius: var(--radius); color: gray;">
                No courses available right now. Instructors are cooking up something great!
            </div>
        <?php else: ?>
            <?php foreach (array_slice($courses, 0, 3) as $course): ?>
                <div class="card course-card">
                    <!-- Course Thumbnail Placeholder -->
                    <div class="course-thumb">
                        <i class='fas fa-chalkboard-teacher' style="font-size: 48px;/* color: var(--bg-color); */padding: 21px 25px;border-radius: 8px;color: gray;background: azure;"></i>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 0.8rem; background: var(--bg-color); padding: 3px 8px; border-radius: 12px; color: gray;">
                            <?= h($course['difficulty'] ?? 'Beginner') ?>
                        </span>
                        <strong style="color: var(--primary-color); font-size: 1.1rem;">
                            <?= $course['price'] > 0 ? '₹' . h($course['price']) : 'Free' ?>
                        </strong>
                    </div>
                    <h3 style="margin-bottom: 5px; font-size: 1.2rem;"><?= h($course['title']) ?></h3>
                    <p style="font-size: 0.85rem; color: gray; margin-bottom: 15px;">By <?= h($course['instructor_name']) ?></p>

                    <!-- Pushes button to bottom if title is short -->
                    <div style="margin-top: auto;">
                        <a href="course-details.php?slug=<?= h($course['slug']) ?>" class="btn btn-outline btn-block text-center">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- BOTTOM CTA SECTION -->
<?php if (!is_logged_in()): ?>
    <section class="cta-section">
        <h2>Ready to transform your career?</h2>
        <p>Join thousands of students learning new skills every day.</p>
        <a href="register.php" class="btn" style="background: white; color: var(--primary-color); font-size: 1.1rem; padding: 12px 30px; border-radius: 30px; font-weight: bold;">Start Learning for Free</a>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>