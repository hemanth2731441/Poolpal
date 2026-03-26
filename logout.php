<?php
session_start();
include 'db.php';

// Clear Remember Me token if it exists
if (isset($_COOKIE['remember_user']) && isset($_COOKIE['remember_token'])) {
    $user_id = $_COOKIE['remember_user'];
    $token = $_COOKIE['remember_token'];
    
    // Delete token from database
    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ? AND token = ?");
    $stmt->bind_param("is", $user_id, $token);
    $stmt->execute();
    
    // Clear cookies
    setcookie('remember_user', '', time() - 3600, "/");
    setcookie('remember_token', '', time() - 3600, "/");
}

// Clear session variables
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>
