<?php
// public/logout.php
session_start();

// Clear all session variables
$_SESSION = [];

// If session uses cookies, clear the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: login.php?success=' . urlencode('You have been logged out'));
exit;