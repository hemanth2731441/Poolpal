<?php
if (basename($_SERVER['SCRIPT_FILENAME']) === 'email_functions.php') {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access not permitted');
}

// Guard against multiple inclusions
if (defined('EMAIL_FUNCTIONS_INCLUDED')) {
    return;
}
define('EMAIL_FUNCTIONS_INCLUDED', true);

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php';

// Function to send email using PHPMailer
if (!function_exists('sendEmail')) {
    function sendEmail($to, $subject, $body, $isHTML = true) {
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = SMTP_AUTH;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            $mail->SMTPDebug = SMTP_DEBUG;
            
            // Set UTF-8 encoding
            $mail->CharSet = 'UTF-8';
            
            // Recipients
            $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            // Send email
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    }
}
?> 