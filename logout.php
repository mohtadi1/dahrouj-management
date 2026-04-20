<?php
require_once 'includes/config.php';

// Log the logout
if (isLoggedIn()) {
    logActivity('Déconnexion', 'user', $_SESSION['user_id']);
}

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
redirect('login.php');
?>
