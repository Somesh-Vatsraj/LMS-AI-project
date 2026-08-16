<?php
require_once __DIR__ . '/../config/app.php';

if (!is_logged_in() || $_SESSION['user_role'] !== 'Admin') {
    redirect('/login.php');
}

// Handle role update or user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $action = $_POST['action'] ?? '';
    $target_user_id = (int)($_POST['user_id'] ?? 0);

    if ($action === 'update_role') {
        $new_role_id = (int)($_POST['role_id'] ?? 0);

        // Prevent admin from removing their own admin role
        if ($target_user_id === $_SESSION['user_id'] && $new_role_id !== 1) {
            set_flash_message('error', 'You cannot remove your own Admin privileges.');
        } elseif ($new_role_id > 0) {
            $stmt = $pdo->prepare("UPDATE user_roles SET role_id = ? WHERE user_id = ?");
            $stmt->execute([$new_role_id, $target_user_id]);
            create_audit_log($pdo, $_SESSION['user_id'], 'Role Change', "Updated user ID {$target_user_id} to role ID {$new_role_id}");
            set_flash_message('success', 'User role updated successfully.');
        }
    } elseif ($action === 'delete_user') {
        if ($target_user_id === $_SESSION['user_id']) {
            set_flash_message('error', 'You cannot delete your own account.');
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$target_user_id]);
            create_audit_log($pdo, $_SESSION['user_id'], 'Delete User', "Deleted user ID {$target_user_id}");
            set_flash_message('success', 'User deleted successfully.');
        }
    }

    redirect('/admin/users.php');
}

// Fetch all roles for the dropdown
$stmt = $pdo->query("SELECT id, name FROM roles ORDER BY id ASC");
$roles = $stmt->fetchAll();

// Fetch all users with their current role
$stmt = $pdo->query("
    SELECT u.id, u.name, u.email, u.created_at, r.id as role_id, r.name as role_name
    FROM users u
    JOIN user_roles ur ON u.id = ur.user_id
    JOIN roles r ON ur.role_id = r.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>


    <div class="dashboard-content card" style="flex: 3; min-width: 300px;">
        <h2>Manage Users</h2>
        <table style="width: 100%; text-align: left; border-collapse: collapse; margin-top: 15px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color);">
                    <th style="padding: 10px;">Name / Email</th>
                    <th style="padding: 10px;">Registered</th>
                    <th style="padding: 10px;">Role</th>
                    <th style="padding: 10px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 10px;">
                            <strong><?= h($u['name']) ?></strong><br>
                            <span style="font-size: 0.85rem; color: gray;"><?= h($u['email']) ?></span>
                        </td>
                        <td style="padding: 10px; font-size: 0.9rem;"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>

                        <td style="padding: 10px;">
                            <form method="POST" action="users.php" style="display: flex; gap: 5px;">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="update_role">
                                <input type="hidden" name="user_id" value="<?= h($u['id']) ?>">
                                <select name="role_id" class="custom-select" <?= $u['id'] === $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= $role['id'] ?>" <?= $u['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                            <?= h($role['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                    <button type="submit" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.85rem;">Update</button>
                                <?php endif; ?>
                            </form>
                        </td>

                        <td style="padding: 10px;">
                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                <form method="POST" action="users.php" onsubmit="return confirm('Delete user permanently? This cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?= h($u['id']) ?>">
                                    <button type="submit" class="btn" style="background: var(--danger); color: white; padding: 5px 10px; font-size: 0.85rem;">Delete</button>
                                </form>
                            <?php else: ?>
                                <span style="font-size: 0.85rem; color: gray;">(You)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>