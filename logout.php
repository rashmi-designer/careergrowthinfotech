<?php
declare(strict_types=1);
// Ensure a session is started so we can clear it reliably
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Clear all session data
$_SESSION = [];

// If session cookies are used, expire the session cookie
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

// Destroy the session on the server
@session_destroy();
header('Location: index.php');
exit;
