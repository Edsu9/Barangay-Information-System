<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log activity before destroying session
if (isset($_SESSION['user_id'])) {
    require_once 'config.php';
    logActivity($_SESSION['user_id'], 'Logged out');
}

// Clear all session variables
$_SESSION = array();

// If a session cookie is used, destroy it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ../index.php");
exit();
?>
