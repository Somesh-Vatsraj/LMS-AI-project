<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

// Handle Add / Delete Category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $slug = trim(strtolower(str_replace(' ', '-', $_POST['slug'] ?? '')));

        if (!empty($name) && !empty($slug)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
                $stmt->execute([$name, $slug]);
                create_audit_log($pdo, $_SESSION['user_id'], 'Add Category', "Created category: $name");
                set_flash_message('success', 'Category added successfully.');
            } catch (PDOException $e) {
                set_flash_message('error', 'Category slug must be unique.');
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        create_audit_log($pdo, $_SESSION['user_id'], 'Delete Category', "Deleted category ID: $id");
        set_flash_message('success', 'Category deleted successfully.');
    }
    redirect('/admin/categories.php');
}

$stmt = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC");
$categories = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>Manage Categories</h2>

        <form method="POST" action="categories.php" style="display: flex; gap: 10px; align-items: flex-end; margin: 20px 0; background: var(--bg-color); padding: 15px; border-radius: var(--radius);">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="add">
            <div class="form-group" style="flex: 1; margin: 0;">
                <label>Category Name</label>
                <input type="text" name="name" required placeholder="e.g. Web Development">
            </div>
            <div class="form-group" style="flex: 1; margin: 0;">
                <label>URL Slug</label>
                <input type="text" name="slug" required placeholder="e.g. web-development">
            </div>
            <button type="submit" class="btn btn-primary" style="margin: 0;">Add Category</button>
        </form>

        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><strong><?= h($cat['name']) ?></strong></td>
                        <td style="color: gray;"><?= h($cat['slug']) ?></td>
                        <td>
                            <form method="POST" action="categories.php" onsubmit="return confirm('Delete this category?');">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= h($cat['id']) ?>">
                                <button type="submit" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger); padding: 4px 10px;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>