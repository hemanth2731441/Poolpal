<?php
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the email and OTP from the request
$email = isset($_POST['reset_email']) ? trim($_POST['reset_email']) : '';
$otp = isset($_POST['otp_code']) ? trim($_POST['otp_code']) : '';

// Validate inputs
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

if (empty($otp) || !is_numeric($otp) || strlen($otp) != 6) {
    echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
    exit;
}

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Find the password reset record with matching email and OTP
    $stmt = $pdo->prepare("SELECT token, expires_at FROM password_resets WHERE email = :email AND otp = :otp");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':otp', $otp);
    $stmt->execute();
    
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reset) {
        echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
        exit;
    }
    
    // Check if the OTP has expired
    $now = new DateTime();
    $expires = new DateTime($reset['expires_at']);
    
    if ($now > $expires) {
        echo json_encode(['success' => false, 'message' => 'Verification code has expired. Please request a new one']);
        exit;
    }
    
    // Mark the OTP as verified in the database
    $stmt = $pdo->prepare("UPDATE password_resets SET verified = 1 WHERE email = :email AND otp = :otp");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':otp', $otp);
    $stmt->execute();
    
    // Return success response with the token for the next step
    echo json_encode([
        'success' => true, 
        'message' => 'Verification code is valid',
        'token' => $reset['token']
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again later']);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later']);
} 