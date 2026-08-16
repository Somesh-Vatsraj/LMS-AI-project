<?php
function h($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($path)
{
    header("Location: " . BASE_URL . $path);
    exit;
}

function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die('CSRF token validation failed.');
        }
    }
}

function create_audit_log($pdo, $user_id, $action, $details = '')
{
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $details, $_SERVER['REMOTE_ADDR'] ?? '']);
}

function get_courses($pdo, $search = '', $category = '', $difficulty = '')
{
    $sql = "SELECT c.*, cat.name as category_name, u.name as instructor_name, 
            (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count
            FROM courses c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            LEFT JOIN users u ON c.instructor_id = u.id 
            WHERE c.status = 'Published'";
    $params = [];
    if ($search) {
        $sql .= " AND c.title LIKE ?";
        $params[] = "%$search%";
    }
    if ($category) {
        $sql .= " AND cat.slug = ?";
        $params[] = $category;
    }
    if ($difficulty) {
        $sql .= " AND c.difficulty = ?";
        $params[] = $difficulty;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
