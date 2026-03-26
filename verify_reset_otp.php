<?php
require_once 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Function to log errors
function logError($message, $data = []) {
    $logFile = __DIR__ . '/verify_reset_error.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    if (!empty($data)) {
        $logMessage .= "Data: " . print_r($data, true) . "\n";
    }
    error_log($logMessage, 3, $logFile);
}

try {
    // Log request data
    logError("Received verification request", $_POST);

    // Get and validate inputs
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    
    if (empty($email) || empty($otp) || empty($new_password)) {
        throw new Exception('All fields are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    if (strlen($otp) !== 6 || !ctype_digit($otp)) {
        throw new Exception('Invalid verification code format');
    }

    if (strlen($new_password) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }

    logError("Verifying OTP for email: $email");

    // Start transaction
    $conn->begin_transaction();

    try {
        // Verify OTP
        $stmt = $conn->prepare("SELECT * FROM password_resets WHERE email = ? AND otp = ? AND expires_at > NOW() AND verified = 0");
        if (!$stmt) {
            logError("Prepare statement error", ['error' => $conn->error]);
            throw new Exception('Database error occurred');
        }

        $stmt->bind_param("ss", $email, $otp);
        if (!$stmt->execute()) {
            logError("Execute error", ['error' => $stmt->error]);
            throw new Exception('Failed to verify code');
        }

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            logError("Invalid or expired OTP", ['email' => $email, 'otp' => $otp]);
            throw new Exception('Invalid or expired verification code');
        }

        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update driver's password
        $updateStmt = $conn->prepare("UPDATE drivers SET password = ? WHERE email = ?");
        if (!$updateStmt) {
            logError("Update prepare error", ['error' => $conn->error]);
            throw new Exception('Failed to update password');
        }

        $updateStmt->bind_param("ss", $hashed_password, $email);
        if (!$updateStmt->execute()) {
            logError("Update execute error", ['error' => $updateStmt->error]);
            throw new Exception('Failed to update password');
        }

        if ($updateStmt->affected_rows === 0) {
            logError("No driver found", ['email' => $email]);
            throw new Exception('Driver account not found');
        }

        // Mark OTP as verified
        $verifyStmt = $conn->prepare("UPDATE password_resets SET verified = 1 WHERE email = ? AND otp = ?");
        if (!$verifyStmt) {
            logError("Verify prepare error", ['error' => $conn->error]);
            throw new Exception('Database error occurred');
        }

        $verifyStmt->bind_param("ss", $email, $otp);
        if (!$verifyStmt->execute()) {
            logError("Verify execute error", ['error' => $verifyStmt->error]);
            throw new Exception('Failed to mark verification as complete');
        }

        // Commit the transaction
        $conn->commit();

        logError("Password reset successful", ['email' => $email]);

        echo json_encode([
            'success' => true,
            'message' => 'Password has been reset successfully'
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    logError("Error occurred", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 