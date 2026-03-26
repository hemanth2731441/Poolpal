<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Twilio\Rest\Client;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');

// Function to log errors with more detail
function logError($message, $data = []) {
    $logFile = __DIR__ . '/forgot_password_error.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    if (!empty($data)) {
        $logMessage .= "Data: " . print_r($data, true) . "\n";
    }
    error_log($logMessage, 3, $logFile);
}

try {
    // Log request data
    logError("Received password reset request", $_POST);

    // Get and validate email
    $email = isset($_POST['reset_email']) ? trim($_POST['reset_email']) : '';
    
    if (empty($email)) {
        throw new Exception('Email address is required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address format');
    }

    logError("Processing reset request for email: $email");

    // Check database connection
    if (!$conn || $conn->connect_error) {
        logError("Database connection failed", ['error' => $conn->connect_error]);
        throw new Exception('Database connection failed');
    }

    // Check if driver exists
    $stmt = $conn->prepare("SELECT id, email FROM drivers WHERE email = ? UNION SELECT id, email FROM users WHERE email = ?");
    if (!$stmt) {
        logError("Database prepare error", ['error' => $conn->error]);
        throw new Exception('Database error occurred');
    }

    $stmt->bind_param("ss", $email, $email);
    if (!$stmt->execute()) {
        logError("Database execute error", ['error' => $stmt->error]);
        throw new Exception('Failed to check account existence');
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        logError("No account found with email: $email");
        throw new Exception('No account found with this email address');
    }

    $user = $result->fetch_assoc();
    logError("User found", $user);

    // Generate OTP and token
    $otp = sprintf("%06d", mt_rand(100000, 999999));
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    logError("Generated OTP for $email: $otp");

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete any existing reset requests
        $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        if (!$deleteStmt) {
            throw new Exception('Failed to prepare delete statement: ' . $conn->error);
        }

        $deleteStmt->bind_param("s", $email);
        if (!$deleteStmt->execute()) {
            throw new Exception('Failed to delete old reset requests: ' . $deleteStmt->error);
        }

        // Insert new reset request with verified=0
        $insertStmt = $conn->prepare("INSERT INTO password_resets (email, otp, token, verified, expires_at) VALUES (?, ?, ?, 0, ?)");
        if (!$insertStmt) {
            throw new Exception('Failed to prepare insert statement: ' . $conn->error);
        }

        $insertStmt->bind_param("ssss", $email, $otp, $token, $expires);
        if (!$insertStmt->execute()) {
            throw new Exception('Failed to create reset request: ' . $insertStmt->error);
        }

        // Initialize PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->SMTPDebug = 0; // Disable debug output
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;

            // Recipients
            $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Verification Code - Pool Pal';
            $mail->Body = '
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #ffbf00; color: white; padding: 10px; text-align: center; }
                        .content { padding: 20px; background-color: #f9f9f9; }
                        .otp { font-size: 24px; font-weight: bold; text-align: center; padding: 15px; margin: 20px 0; background-color: #eee; }
                        .footer { font-size: 12px; text-align: center; margin-top: 20px; color: #777; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h2>Password Reset</h2>
                        </div>
                        <div class="content">
                            <p>Hello,</p>
                            <p>We received a request to reset your password. Please use the following verification code to complete the process:</p>
                            <div class="otp">' . $otp . '</div>
                            <p>This code will expire in 1 hour.</p>
                            <p>If you did not request a password reset, please ignore this email.</p>
                        </div>
                        <div class="footer">
                            <p>This is an automated message, please do not reply.</p>
                        </div>
                    </div>
                </body>
                </html>
            ';

            $mail->send();
            logError("Email sent successfully to: $email");

            // If everything is OK, commit the transaction
            $conn->commit();

            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Verification code sent successfully to your email'
            ]);

        } catch (Exception $e) {
            $conn->rollback();
            logError("Email sending failed", ['error' => $mail->ErrorInfo]);
            throw new Exception('Failed to send verification code. Please try again later.');
        }

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