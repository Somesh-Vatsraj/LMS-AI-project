<?php
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function get_user_role($pdo, $user_id)
{
    $stmt = $pdo->prepare("SELECT r.name FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $role = $stmt->fetchColumn();
    return $role ?: 'Student'; // Default
}

function login_user($pdo, $user)
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = get_user_role($pdo, $user['id']);
    create_audit_log($pdo, $user['id'], 'Login', 'Successful login');
}

function logout_user($pdo)
{
    if (is_logged_in()) {
        create_audit_log($pdo, $_SESSION['user_id'], 'Logout', 'User logged out');
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
}
