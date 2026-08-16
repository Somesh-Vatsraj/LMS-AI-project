<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/header.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$difficulty = $_GET['difficulty'] ?? '';

$courses = get_courses($pdo, $search, $category, $difficulty);

$stmt = $pdo->query("SELECT slug, name FROM categories");
$categories = $stmt->fetchAll();
?>

<div class="courses-page">
    <aside class="filters card sidebar-sticky">
        <form method="GET" action="courses.php">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 1.25rem;">Filters</h3>
                <!-- Agar koi filter active hai, tabhi 'Clear All' dikhaye -->
                <?php if (!empty($search) || !empty($category) || !empty($difficulty)): ?>
                    <a href="courses.php" style="font-size: 0.85rem; font-weight: 500; color: var(--danger);">Clear All</a>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Search Keyword</label>
                <input type="text" name="search" placeholder="e.g. Machine Learning" value="<?= h($search) ?>">
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= h($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>>
                            <?= h($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Difficulty</label>
                <select name="difficulty">
                    <option value="">Any Level</option>
                    <option value="Beginner" <?= $difficulty === 'Beginner' ? 'selected' : '' ?>>Beginner</option>
                    <option value="Intermediate" <?= $difficulty === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
                    <option value="Advanced" <?= $difficulty === 'Advanced' ? 'selected' : '' ?>>Advanced</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-2">Apply Filters</button>
        </form>
    </aside>

    <div class="course-list grid">
        <?php if (empty($courses)): ?>
            <p>No courses found.</p>
        <?php else: ?>
            <?php foreach ($courses as $course): ?>
                <div class="card course-card">
                    <h3><?= h($course['title']) ?></h3>
                    <p>Category: <?= h($course['category_name']) ?> | <?= h($course['difficulty']) ?></p>
                    <p><?= h(substr($course['description'], 0, 100)) ?>...</p>
                    <p class="price"><?= $course['price'] > 0 ? '₹' . $course['price'] : 'Free' ?></p>
                    <a href="course-details.php?slug=<?= h($course['slug']) ?>" class="btn btn-outline">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>