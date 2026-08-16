<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Instructor') {
    redirect('/login.php');
}

$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    redirect('/instructor/courses.php');
}

// Verify ownership
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    set_flash_message('error', 'Course not found or access denied.');
    redirect('/instructor/courses.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $title = trim($_POST['title'] ?? '');
    $slug = trim(strtolower(str_replace(' ', '-', $_POST['slug'] ?? '')));
    $description = trim($_POST['description'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $difficulty = $_POST['difficulty'] ?? 'Beginner';
    $price = $_POST['price'] ?? 0;
    $status = $_POST['status'] ?? 'Draft';

    if (empty($title) || empty($slug)) {
        set_flash_message('error', 'Title and Slug are required.');
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE courses 
                SET title = ?, slug = ?, description = ?, category_id = ?, difficulty = ?, price = ?, status = ?
                WHERE id = ? AND instructor_id = ?
            ");
            $stmt->execute([$title, $slug, $description, $category_id, $difficulty, $price, $status, $course_id, $_SESSION['user_id']]);
            set_flash_message('success', 'Course details updated successfully.');
            redirect('/instructor/course-edit.php?id=' . $course_id);
        } catch (PDOException $e) {
            // Likely a duplicate slug error
            set_flash_message('error', 'Error updating course. Make sure the slug is unique.');
        }
    }
}

// Get categories for the dropdown
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/instructor_sidebar.php'; ?>


    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>Edit Course Details</h2>
        <form method="POST" action="course-edit.php?id=<?= h($course_id) ?>" class="mt-2">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <div class="form-group">
                <label>Course Title</label>
                <input type="text" name="title" value="<?= h($course['title']) ?>" required>
            </div>

            <div class="form-group">
                <label>URL Slug (Must be unique)</label>
                <input type="text" name="slug" value="<?= h($course['slug']) ?>" required>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select a Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $course['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= h($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Difficulty</label>
                <select name="difficulty">
                    <option value="Beginner" <?= $course['difficulty'] === 'Beginner' ? 'selected' : '' ?>>Beginner</option>
                    <option value="Intermediate" <?= $course['difficulty'] === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
                    <option value="Advanced" <?= $course['difficulty'] === 'Advanced' ? 'selected' : '' ?>>Advanced</option>
                </select>
            </div>

            <div class="form-group">
                <label>Price (₹ INR)</label>
                <input type="number" step="0.01" name="price" value="<?= h($course['price']) ?>">
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="Draft" <?= $course['status'] === 'Draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="Published" <?= $course['status'] === 'Published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>

            <div class="form-group">
                <label>Course Description</label>
                <textarea name="description" rows="6" required><?= h($course['description']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Save Course Details</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>