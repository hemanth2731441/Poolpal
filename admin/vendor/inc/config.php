<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'u965304184_poolpal');
define('DB_PASS', 'N7dLfk2dGn*');
define('DB_NAME', 'u965304184_poolpal');

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants
define('SITE_URL', 'http://localhost/poolpal/');
define('ADMIN_URL', SITE_URL . 'admin/');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/poolpal/uploads/');

// Function to clean input data
function clean($string) {
    global $conn;
    $string = trim($string);
    $string = stripslashes($string);
    $string = htmlspecialchars($string);
    $string = mysqli_real_escape_string($conn, $string);
    return $string;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Function to redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}
?> 