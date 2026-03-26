<?php
require_once 'config.php';

// Create a log function for debugging
function logError($message) {
    error_log("[" . date("Y-m-d H:i:s") . "] " . $message . "\n", 3, "driver_password_error.log");
}

// Disable error output to prevent it from corrupting JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Ensure clean output buffer - critical for clean JSON
if (ob_get_level()) ob_end_clean();

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logError("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the parameters from the request
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
$newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';

// Validate inputs
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    logError("Invalid email: $email");
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

if (empty($otp) || strlen($otp) !== 6 || !ctype_digit($otp)) {
    logError("Invalid OTP: $otp");
    echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
    exit;
}

if (empty($newPassword) || strlen($newPassword) < 6) {
    logError("Invalid new password: Too short");
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verify the OTP
    $stmt = $pdo->prepare("SELECT * FROM driver_password_resets WHERE email = :email AND otp = :otp AND expires_at > NOW()");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':otp', $otp);
    $stmt->execute();
    
    $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resetRequest) {
        logError("Invalid or expired OTP for email: $email");
        echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
        exit;
    }
    
    // Hash the new password using PHP's password_hash function
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update the driver's password - use correct column names (Password with capital P)
    $stmt = $pdo->prepare("UPDATE drivers SET Password = :password WHERE Email = :email");
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        logError("Failed to update password for driver: $email");
        echo json_encode(['success' => false, 'message' => 'Failed to update password. Please try again.']);
        exit;
    }
    
    // Delete the OTP record
    $stmt = $pdo->prepare("DELETE FROM driver_password_resets WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    logError("Successfully reset password for driver: $email");
    
    // Return success response
    echo json_encode(['success' => true, 'message' => 'Your password has been reset successfully']);
    
} catch (PDOException $e) {
    logError("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again later or contact support.']);
} catch (Exception $e) {
    logError("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later or contact support.']);
} 