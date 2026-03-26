<?php
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the form data
$email = isset($_POST['reset_email']) ? trim($_POST['reset_email']) : '';
$token = isset($_POST['token']) ? trim($_POST['token']) : '';
$newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validate inputs
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

if (empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Password cannot be empty']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}

// Password strength validation
if (strlen($newPassword) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
    exit;
}

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Find the password reset record
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = :email AND token = :token AND verified = 1");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reset) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired reset request']);
        exit;
    }
    
    // Check if the reset token has expired
    $now = new DateTime();
    $expires = new DateTime($reset['expires_at']);
    
    if ($now > $expires) {
        echo json_encode(['success' => false, 'message' => 'Reset link has expired. Please request a new one']);
        exit;
    }
    
    // Update the user's password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    // Delete the password reset record
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    // Return success response
    echo json_encode(['success' => true, 'message' => 'Password has been reset successfully']);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again later']);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later']);
} 