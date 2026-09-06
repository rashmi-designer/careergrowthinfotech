<?php
/**
 * Candidate Authentication Helper
 * Ensures user is authenticated as a candidate and is active.
 * Sets up session variables for authenticated candidate.
 * Redirects to login.php if not authenticated.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if candidate is authenticated
if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'candidate') {
    header('Location: ' . ($_SESSION['user_role'] === 'admin' ? '/admin/login.php' : '/login.php'));
    exit;
}

// Additional security - verify user exists and is still active
require_once __DIR__ . '/db.php';
$conn = getDbConnection();
$stmt = $conn->prepare('SELECT status, role FROM users WHERE id = ? LIMIT 1');
if ($stmt) {
    $userId = (int)$_SESSION['user_id'];
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || $user['role'] !== 'candidate' || (int)$user['status'] !== 1) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        header('Location: /login.php');
        exit;
    }
} else {
    header('Location: /login.php');
    exit;
}
$conn->close();
