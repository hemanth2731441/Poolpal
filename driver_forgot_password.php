<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Twilio\Rest\Client;

// Create a log function to help with debugging
function logError($message) {
    error_log("[" . date("Y-m-d H:i:s") . "] " . $message . "\n", 3, "driver_password_error.log");
}

// Disable error output to prevent it from corrupting JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Ensure clean output buffer - this is critical for clean JSON
if (ob_get_level()) ob_end_clean();

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logError("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the email address from the request
$email = isset($_POST['reset_email']) ? trim($_POST['reset_email']) : '';

// Validate the email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    logError("Invalid email: $email");
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

try {
    // Log the connection attempt
    logError("Attempting to connect to the database");
    
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    logError("Database connection successful");

    // Check if the email exists in the database - check drivers table for drivers
    $stmt = $pdo->prepare("SELECT ID, Contact FROM drivers WHERE Email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$driver) {
        logError("No driver found with email: $email");
        echo json_encode(['success' => false, 'message' => 'No driver account found with this email address']);
        exit;
    }
    
    logError("Driver found with email: $email");
    
    // Generate a 6-digit OTP
    $otp = rand(100000, 999999);
    logError("Generated OTP: $otp for driver email: $email");
    
    // Store the OTP in the driver_password_resets table
    // First, check if table exists, if not create it
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'driver_password_resets'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        // Table doesn't exist, create it
        $sql = "CREATE TABLE driver_password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            otp VARCHAR(10) NOT NULL,
            token VARCHAR(100) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        logError("Created driver_password_resets table");
    }
    
    // Delete any existing reset requests for this email
    $stmt = $pdo->prepare("DELETE FROM driver_password_resets WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    // Insert new reset request
    $token = bin2hex(random_bytes(32)); // Generate a secure token
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // OTP expires in 1 hour
    
    $stmt = $pdo->prepare("INSERT INTO driver_password_resets (email, otp, token, expires_at) VALUES (:email, :otp, :token, :expires)");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':otp', $otp);
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':expires', $expires);
    $stmt->execute();
    
    logError("OTP stored in database for driver email: $email");
    
    // Send OTP via email
    $mail = new PHPMailer(true);
    $emailSent = false;
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        
        logError("SMTP configuration: Host=".SMTP_HOST.", Port=".SMTP_PORT.", User=".SMTP_USERNAME);
        
        // Additional SMTP debugging
        $mail->SMTPDebug = 0; // Set to 2 for full debug output
        
        // Recipients
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Driver Password Reset Verification Code';
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
                <link rel="stylesheet" href="css/animated-bg.css" />
</head>
            <body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
                <div class="container">
                    <div class="header">
                        <h2>Driver Password Reset</h2>
                    </div>
                    <div class="content">
                        <p>Hello,</p>
                        <p>We received a request to reset your driver account password. Please use the following verification code to complete the process:</p>
                        <div class="otp">' . $otp . '</div>
                        <p>This code will expire in 1 hour.</p>
                        <p>If you did not request a password reset, please ignore this email.</p>
                    </div>
                    <div class="footer">
                        <p>This is an automated message, please do not reply.</p>
                    </div>
                </div>
            </div></body>
            </html>
        ';
        
        $mail->send();
        $emailSent = true;
        logError("Email sent successfully to driver: $email");
    } catch (Exception $e) {
        logError("Email error: " . $mail->ErrorInfo);
        $emailSent = false;
    }
    
    // Send OTP via WhatsApp using Twilio (only if Twilio is configured)
    $whatsappSent = false;
    
    if (isset($driver['Contact']) && !empty($driver['Contact']) && 
        defined('TWILIO_SID') && TWILIO_SID !== 'your_twilio_sid' && 
        defined('TWILIO_TOKEN') && TWILIO_TOKEN !== 'your_twilio_token') {
        
        try {
            logError("Attempting to send WhatsApp message to: " . $driver['Contact']);
            $twilioClient = new Client(TWILIO_SID, TWILIO_TOKEN);
            
            $message = $twilioClient->messages->create(
                "whatsapp:" . $driver['Contact'],
                [
                    "from" => "whatsapp:" . TWILIO_WHATSAPP_NUMBER,
                    "body" => "Your Pool Pal driver account password reset code is: $otp. This code will expire in 1 hour."
                ]
            );
            
            $whatsappSent = true;
            logError("WhatsApp message sent successfully to: " . $driver['Contact']);
        } catch (Exception $e) {
            logError("WhatsApp error: " . $e->getMessage());
            // Continue even if WhatsApp fails, as long as email was sent
        }
    } else {
        logError("WhatsApp not configured or phone number missing. Contact: " . (isset($driver['Contact']) ? $driver['Contact'] : 'not set'));
    }
    
    // Return success response if email method worked
    if ($emailSent) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Verification code sent successfully to your email']);
    } else {
        logError("Failed to send verification code to driver: $email");
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send verification code. Please try again later or contact support.']);
    }
    
} catch (PDOException $e) {
    logError("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again later or contact support.']);
} catch (Exception $e) {
    logError("General error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later or contact support.']);
} 