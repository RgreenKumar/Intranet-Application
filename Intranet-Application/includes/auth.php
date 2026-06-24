<?php
// Start session once, globally
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login before accessing protected pages
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Require a specific role (e.g., ADMIN)
function require_role($role) {
    require_login(); // make sure user is logged in first
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: unauthorized.php");
        exit();
    }
}
?>
