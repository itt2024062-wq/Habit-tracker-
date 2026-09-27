<?php
// config.php
// Database connection and common helper functions

session_start();

// Database credentials
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'habit_tracker';

// Create MySQLi connection
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($mysqli->connect_errno) {
    // In production you'd log this and show a friendly message
    die("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

// Set charset
$mysqli->set_charset('utf8mb4');

// Require user to be logged in. Call this at the top of protected pages.
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Simple escaping helper for output
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Get current logged in user's data (or null)
function current_user($mysqli) {
    if (!isset($_SESSION['user_id'])) return null;
    $id = (int)$_SESSION['user_id'];
    $stmt = $mysqli->prepare("SELECT id, name, email, created_at FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();
    return $user ?: null;
}

?>