<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make sure vehicle_type is set in session or URL parameter
$vehicle_type = '';

// Skip vehicle type check if we're showing success message
if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_SESSION['registration_success'])) {
    // Don't redirect, we're showing success message
    $vehicle_type = 'Car'; // Default value just to prevent errors
} else {
    if (isset($_SESSION['vehicle_type'])) {
        $vehicle_type = $_SESSION['vehicle_type'];
    } elseif (isset($_GET['vt'])) {
        // Fallback to URL parameter if session doesn't have it
        $vehicle_type = $_GET['vt'];
        // Store in session for consistency
        $_SESSION['vehicle_type'] = $vehicle_type;
    } else {
        header('Location: select_vehicle_type.php');
        exit();
    }
}

// Initialize variables for form processing
$form_submitted = false;
$display_debug = isset($_GET['debug']) ? true : false;

include 'header.php';
include('db.php');

// Check if vehicle_type column exists in drivers table, if not add it
try {
    $checkColumnQuery = "SELECT COUNT(*) as count FROM information_schema.COLUMNS 
                         WHERE TABLE_SCHEMA = 'ride_app' 
                         AND TABLE_NAME = 'drivers' 
                         AND COLUMN_NAME = 'vehicle_type'";
    $result = $conn->query($checkColumnQuery);
    
    if (!$result) {
        error_log("Error checking for vehicle_type column: " . $conn->error);
        throw new Exception("Database query failed: " . $conn->error);
    }
    
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Add vehicle_type column if it doesn't exist
        $addColumnQuery = "ALTER TABLE drivers ADD COLUMN vehicle_type VARCHAR(50) DEFAULT NULL AFTER vehicle_name";
        if ($conn->query($addColumnQuery) === TRUE) {
            error_log("Added vehicle_type column to drivers table");
        } else {
            error_log("Error adding vehicle_type column: " . $conn->error);
            throw new Exception("Failed to add vehicle_type column: " . $conn->error);
        }
    }

    // Check if vehicle_color column exists, if not add it
    $checkColorColumnQuery = "SELECT COUNT(*) as count FROM information_schema.COLUMNS 
                            WHERE TABLE_SCHEMA = 'ride_app' 
                            AND TABLE_NAME = 'drivers' 
                            AND COLUMN_NAME = 'vehicle_color'";
    $colorResult = $conn->query($checkColorColumnQuery);
    
    if (!$colorResult) {
        error_log("Error checking for vehicle_color column: " . $conn->error);
        throw new Exception("Database query failed: " . $conn->error);
    }
    
    $colorRow = $colorResult->fetch_assoc();
    
    if ($colorRow['count'] == 0) {
        // Add vehicle_color column if it doesn't exist
        $addColorColumnQuery = "ALTER TABLE drivers ADD COLUMN vehicle_color VARCHAR(50) DEFAULT NULL AFTER vehicle_type";
        if ($conn->query($addColorColumnQuery) === TRUE) {
            error_log("Added vehicle_color column to drivers table");
        } else {
            error_log("Error adding vehicle_color column: " . $conn->error);
            throw new Exception("Failed to add vehicle_color column: " . $conn->error);
        }
    }
} catch (Exception $e) {
    error_log("Error checking/adding columns: " . $e->getMessage());
}

// Require the PHPMailer library
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include mail configuration
require_once 'config.php';

$display_form = true;
$err = null;
$succ = null;

// Function to send confirmation email
function sendConfirmationEmail($name, $email, $vehicle_type = 'Driver') {
  // Create a new PHPMailer instance
  $mail = new PHPMailer(true);
  
  try {
    // Enable verbose debug output
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    
    // Log SMTP debug output
    $mail->Debugoutput = function($str, $level) {
      error_log("SMTP DEBUG: $str");
    };
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';  // Gmail SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = defined('SMTP_USERNAME') ? SMTP_USERNAME : 'your_email@gmail.com';
    $mail->Password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : 'your_app_password';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;  // TCP port to connect to
    
    // Set timeout
    $mail->Timeout = 60;
    $mail->SMTPKeepAlive = true;
    
    // Recipients
    $mail->setFrom('poolpal.in@gmail.com', 'PoolPal Team');
    $mail->addAddress($email, $name);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = "Welcome to PoolPal - {$vehicle_type} Driver Registration Confirmation";
    
    // Create HTML email content (keeping the existing HTML content)
    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Registration Confirmation</title>
      <style>
        body {
          font-family: "Segoe UI", Arial, sans-serif;
          line-height: 1.6;
          color: #333333;
          background-color: #f9f9f9;
          margin: 0;
          padding: 0;
        }
        .email-container {
          max-width: 600px;
          margin: 0 auto;
          background-color: #ffffff;
          border-radius: 10px;
          overflow: hidden;
          box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .email-header {
          background: linear-gradient(135deg, #ffca28, #ffa000);
          padding: 30px 20px;
          text-align: center;
        }
        .email-header img {
          max-width: 150px;
          height: auto;
        }
        .email-body {
          padding: 40px 30px;
        }
        .email-footer {
          background-color: #f1f1f1;
          padding: 15px;
          text-align: center;
          font-size: 12px;
          color: #666666;
        }
        h1 {
          color: #ffffff;
          font-size: 28px;
          margin: 10px 0 0;
          text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        }
        h2 {
          color: #ffa000;
          font-size: 22px;
          margin-top: 0;
          margin-bottom: 20px;
        }
        p {
          margin: 0 0 20px;
        }
        .highlight {
          color: #ffa000;
          font-weight: 600;
        }
        .steps {
          background-color: #f9f9f9;
          border-left: 4px solid #ffa000;
          padding: 15px 20px;
          margin: 20px 0;
          border-radius: 0 5px 5px 0;
        }
        .step {
          margin-bottom: 10px;
          padding-left: 15px;
          position: relative;
        }
        .step:before {
          content: "•";
          color: #ffa000;
          font-weight: bold;
          position: absolute;
          left: 0;
        }
        .social-links {
          margin-top: 20px;
        }
        .social-icon {
          display: inline-block;
          margin: 0 10px;
          color: #666666;
          text-decoration: none;
        }
      </style>
    </head>
    <body>
      <div class="email-container">
        <div class="email-header">
          <h1>Welcome to PoolPal!</h1>
        </div>
        <div class="email-body">
          <h2>Registration Confirmation</h2>
          <p>Dear <span class="highlight">' . htmlspecialchars($name) . '</span>,</p>
          <p>Thank you for registering as a <span class="highlight">' . htmlspecialchars($vehicle_type) . '</span> driver with PoolPal! We\'re excited to have you join our community.</p>
          
          <p>Your account has been created successfully with the following details:</p>
          <div class="steps">
            <div class="step">Name: ' . htmlspecialchars($name) . '</div>
            <div class="step">Email: ' . htmlspecialchars($email) . '</div>
            <div class="step">Vehicle Type: ' . htmlspecialchars($vehicle_type) . '</div>
          </div>
          
          <p>Here\'s what happens next:</p>
          <div class="steps">
            <div class="step">Our support team will review and verify your documents within <span class="highlight">24 hours</span>.</div>
            <div class="step">You\'ll receive a notification once your account is verified.</div>
            <div class="step">After verification, you can start offering rides and earning with PoolPal!</div>
          </div>
          
          <p>If you have any questions or need assistance, please don\'t hesitate to contact our support team at <span class="highlight">support@poolpal.com</span>.</p>
          
          
          <p>We look forward to seeing you on the road!</p>
          
          <p>Best regards,<br>The PoolPal Team</p>
        </div>
        <div class="email-footer">
          <p>© ' . date('Y') . ' PoolPal. All rights reserved.</p>
          <p>This is an automated email, please do not reply to this message.</p>
          <div class="social-links">
            <a href="#" class="social-icon">Facebook</a> |
            <a href="#" class="social-icon">Twitter</a> |
            <a href="#" class="social-icon">Instagram</a>
          </div>
        </div>
      </div>
    </body>
    </html>
    ';
    
    // Plain text alternative
    $mail->AltBody = "Welcome to PoolPal, " . $name . "!\n\n"
                   . "Thank you for registering as a " . $vehicle_type . " driver with PoolPal. "
                   . "We're excited to have you join our community.\n\n"
                   . "Your account details:\n"
                   . "Name: " . $name . "\n"
                   . "Email: " . $email . "\n"
                   . "Vehicle Type: " . $vehicle_type . "\n\n"
                   . "Next steps:\n"
                   . "- Our support team will review and verify your documents within 24 hours.\n"
                   . "- You'll receive a notification once your account is verified.\n"
                   . "- After verification, you can start offering rides and earning with PoolPal!\n\n"
                   . "If you have any questions, contact us at support@poolpal.com\n\n"
                   . "Best regards,\nThe PoolPal Team";
    
    // Attempt to send email
    if(!$mail->send()) {
      error_log("Email sending failed: " . $mail->ErrorInfo);
      throw new Exception("Failed to send confirmation email: " . $mail->ErrorInfo);
    }
    
    error_log("Confirmation email sent successfully to $email");
    return true;
    
  } catch (Exception $e) {
    error_log("Email Error: " . $e->getMessage());
    // Don't throw the exception, just return false
    return false;
  }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST request received in driver_signup.php");
    error_log("POST data: " . print_r($_POST, true));
    error_log("SESSION data before processing POST: " . print_r($_SESSION, true));
    
    // Only process the form if add_driver is set (form was actually submitted)
    if (isset($_POST['add_driver'])) {
        // Set a flag to indicate form was submitted - this will help with debugging
        $form_submitted = true;
        $display_debug = true; // Always show debug info when form is submitted
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Log the POST data
    error_log("=== START OF REGISTRATION PROCESS ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    error_log("SESSION data: " . print_r($_SESSION, true)); // Log session data to check vehicle_type
    
    try {
        // Database connection check
        if (!$conn) {
            error_log("Database connection failed: " . mysqli_connect_error());
            throw new Exception("Database connection failed");
        }
        error_log("Database connection successful");
        
        // Get form data
        $u_fname = trim($_POST['u_fname']);  
        $u_gender = trim($_POST['u_gender']); 
        $u_phone = trim($_POST['u_phone']);  
        $alt_phone = isset($_POST['alt_phone']) ? trim($_POST['alt_phone']) : '';  
        $u_email = trim($_POST['u_email']);  
        $u_addr = trim($_POST['u_addr']);  
        $vehicle_name = trim($_POST['vehicle_name']);
        
        // Get vehicle_type from URL parameter if present
        if (isset($_GET['vt']) && !empty($_GET['vt'])) {
            $vehicle_type = $_GET['vt'];
            $_SESSION['vehicle_type'] = $vehicle_type;
            error_log("Vehicle type from URL parameter: " . $vehicle_type);
        }

        // Also check cookie as a fallback
        if (!isset($_SESSION['vehicle_type']) && isset($_COOKIE['vehicle_type'])) {
            $_SESSION['vehicle_type'] = $_COOKIE['vehicle_type'];
            error_log("Vehicle type from cookie: " . $_COOKIE['vehicle_type']);
        }

        // Get vehicle_type from POST data (from the hidden form field)
        if (isset($_POST['vehicle_type']) && !empty($_POST['vehicle_type'])) {
            $vehicle_type = trim($_POST['vehicle_type']);
            error_log("Vehicle type from form field: " . $vehicle_type);
        } else if (isset($_SESSION['vehicle_type']) && !empty($_SESSION['vehicle_type'])) {
            $vehicle_type = $_SESSION['vehicle_type'];
            error_log("Vehicle type from session: " . $vehicle_type);
        } else {
            $vehicle_type = 'Car';
            error_log("No vehicle type found, defaulting to: Car");
        }

        // Double check that vehicle_type is not empty
        if (empty($vehicle_type)) {
            $vehicle_type = 'Car'; // Default to Car if somehow empty
            error_log("WARNING: Empty vehicle_type, defaulting to 'Car'");
        }
        
        $u_vehicle = trim($_POST['u_vehicle']);  
        $u_languages = trim($_POST['u_languages']);  
        $u_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $member_since = date('Y-m-d');
        $verification_status = 'pending';
        $status = 1;
        $status_int = 1; // Define status_int to match status
        
        // Log critical data for debugging
        error_log("CRITICAL - Vehicle Type Value: '" . $vehicle_type . "'");
        
        error_log("Processed form data: " . print_r([
            'name' => $u_fname,
            'gender' => $u_gender,
            'phone' => $u_phone,
            'email' => $u_email,
            'vehicle_name' => $vehicle_name,
            'vehicle_type' => $vehicle_type,
            'vehicle_number' => $u_vehicle
        ], true));
        
        // Check if email or phone exists
        $check_query = "SELECT * FROM drivers WHERE Email = ? OR Contact = ?";
        $check_stmt = $conn->prepare($check_query);
        
        if (!$check_stmt) {
            error_log("Prepare statement failed: " . $conn->error);
            throw new Exception("Database preparation failed");
        }
        
        $check_stmt->bind_param('ss', $u_email, $u_phone);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['Email'] == $u_email) {
                $err = "Email address already registered";
            } else {
                $err = "Phone number already registered";
            }
            error_log("Duplicate registration attempt: " . $err);
            $check_stmt->close();
        } else {
            $check_stmt->close();
            
            // Define upload directory
            $target_dir = "uploads/";

            // Ensure upload directory exists
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
                error_log("Created upload directory: $target_dir");
            }

            // Check if directory is writable
            if (!is_writable($target_dir)) {
                error_log("WARNING: Upload directory is not writable: $target_dir");
                chmod($target_dir, 0777);
                error_log("Attempted to make upload directory writable");
            }
            
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0755, true)) {
                    error_log("Failed to create uploads directory");
                    throw new Exception("Failed to create uploads directory");
                }
            }
            
            // Process file uploads
            $license_relative_path = '';
            $rc_relative_path = '';
            $profile_relative_path = '';
            $aadhar_relative_path = '';
            
            // Function to handle file upload
            function handleFileUpload($file, $prefix, $required = true) {
                global $target_dir, $err;
                
                // Debug the file upload data
                error_log("File upload data for $prefix: " . print_r($file, true));
                
                // Check if file is set and is an array
                if (!isset($file) || !is_array($file) || empty($file['name'])) {
                    if ($required) {
                        $err = "File not provided for $prefix";
                        error_log("File not provided for $prefix");
                        return "default_{$prefix}.jpg";
                    }
                    return "default_{$prefix}.jpg";
                }
                
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    if ($required) {
                        $err = "File upload error: " . $file['error'];
                        error_log("File upload error for $prefix: " . $file['error']);
                        return "default_{$prefix}.jpg";
                    }
                    return "default_{$prefix}.jpg";
                }
                
                $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $new_name = time() . '_' . $prefix . '_' . uniqid() . '.' . $file_ext;
                $target_file = $target_dir . $new_name;
                
                // Create directory if it doesn't exist (one more check)
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                    error_log("Created upload directory from inside function: $target_dir");
                }
                
                // Debug information
                error_log("Attempting to move uploaded file from {$file['tmp_name']} to {$target_file}");
                
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    error_log("File uploaded successfully: $target_file");
                    return "uploads/" . $new_name;
                } else {
                    // Log detailed error information
                    error_log("Failed to move uploaded file: " . $file['name']);
                    error_log("PHP Error: " . error_get_last()['message']);
                    error_log("Source exists: " . (file_exists($file['tmp_name']) ? 'Yes' : 'No'));
                    error_log("Target directory writable: " . (is_writable($target_dir) ? 'Yes' : 'No'));
                    
                    // Try direct copy as fallback
                    if (copy($file['tmp_name'], $target_file)) {
                        error_log("File copied successfully as fallback: $target_file");
                        return "uploads/" . $new_name;
                    }
                    
                    $err = "Error uploading file: " . $file['name'];
                    return false;
                }
            }
            
            // Handle all file uploads
            // Log file upload data for debugging
            error_log("File upload data: " . print_r($_FILES, true));
            
            // Make sure the target directory exists and is writable
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
                error_log("Created upload directory: $target_dir");
            }
            
            // Check if directory is writable
            if (!is_writable($target_dir)) {
                error_log("WARNING: Upload directory is not writable: $target_dir");
                chmod($target_dir, 0777);
                error_log("Attempted to make upload directory writable");
            }
            
            // Debug file uploads
            error_log("File upload data: " . print_r($_FILES, true));
            
            // For testing purposes, set default file paths if files are not uploaded
            // This will allow the form to be submitted without file uploads during testing
            $license_relative_path = "default_license.jpg";
            $rc_relative_path = "default_rc.jpg";
            $profile_relative_path = "default_profile.jpg";
            $aadhar_relative_path = "default_aadhar.jpg";
            
            // Try to process file uploads if they exist
            if (isset($_FILES["u_photo"]) && !empty($_FILES["u_photo"]["name"])) {
                $license_relative_path = handleFileUpload($_FILES["u_photo"], "license", false);
            }
            
            if (isset($_FILES["rc_photo"]) && !empty($_FILES["rc_photo"]["name"])) {
                $rc_relative_path = handleFileUpload($_FILES["rc_photo"], "rc", false);
            }
            
            if (isset($_FILES["profile_pic"]) && !empty($_FILES["profile_pic"]["name"])) {
                $profile_relative_path = handleFileUpload($_FILES["profile_pic"], "profile", false);
            }
            
            if (isset($_FILES["aadhar_photo"]) && !empty($_FILES["aadhar_photo"]["name"])) {
                $aadhar_relative_path = handleFileUpload($_FILES["aadhar_photo"], "aadhar", false);
            }
            
            error_log("File paths after processing: license=$license_relative_path, rc=$rc_relative_path, profile=$profile_relative_path, aadhar=$aadhar_relative_path");
            
            if (!$err) {
                // Insert into database
                // Verify vehicle_type is set before insertion
                if (empty($vehicle_type)) {
                    $vehicle_type = 'Car'; // Default to Car if somehow empty
                    error_log("WARNING: Empty vehicle_type before database insertion, defaulting to 'Car'");
                }
                
                // Log critical data before insertion
                error_log("CRITICAL - About to insert driver with vehicle_type: '" . $vehicle_type . "'");
                
                $query = "INSERT INTO drivers (Full_Name, Gender, Contact, alt_phone, Email, Address, Driving_License, RC, Profile_Pic, Aadhar, vehicle_name, vehicle_type, vehicle_color, Vehicle_Number, Languages, Password, member_since, verification_status, status) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                error_log("Preparing insert query: " . $query);
                
                // Verify database connection before preparing statement
                if ($conn->connect_error) {
                    error_log("Database connection failed: " . $conn->connect_error);
                    throw new Exception("Database connection failed");
                }
                
                $stmt = $conn->prepare($query);
                
                if (!$stmt) {
                    error_log("Prepare statement failed: " . $conn->error);
                    throw new Exception("Database preparation failed: " . $conn->error);
                }
                
                // Direct debugging of vehicle_type value
                error_log("FINAL VEHICLE TYPE BEFORE INSERT: '" . $vehicle_type . "'");
                
                // Log the query and parameters for debugging
                error_log("INSERT QUERY: " . $query);
                error_log("PARAMETERS: " . json_encode([
                    'u_fname' => $u_fname,
                    'u_gender' => $u_gender,
                    'u_phone' => $u_phone,
                    'alt_phone' => $alt_phone,
                    'u_email' => $u_email,
                    'u_addr' => $u_addr,
                    'license_path' => $license_relative_path,
                    'rc_path' => $rc_relative_path,
                    'profile_path' => $profile_relative_path,
                    'aadhar_path' => $aadhar_relative_path,
                    'vehicle_name' => $vehicle_name,
                    'vehicle_type' => $vehicle_type,
                    'vehicle_color' => trim($_POST['vehicle_color']), // Add vehicle_color
                    'u_vehicle' => $u_vehicle,
                    'u_languages' => $u_languages,
                    'member_since' => $member_since,
                    'verification_status' => $verification_status,
                    'status' => $status
                ]));
                
                // Convert status to integer to ensure proper binding
                $status_int = (int)$status;
                
                // Ensure all variables are properly set before binding
                $u_fname = !empty($u_fname) ? $u_fname : '';
                $u_gender = !empty($u_gender) ? $u_gender : '';
                $u_phone = !empty($u_phone) ? $u_phone : '';
                $alt_phone = !empty($alt_phone) ? $alt_phone : '';
                $u_email = !empty($u_email) ? $u_email : '';
                $u_addr = !empty($u_addr) ? $u_addr : '';
                $license_relative_path = !empty($license_relative_path) ? $license_relative_path : '';
                $rc_relative_path = !empty($rc_relative_path) ? $rc_relative_path : '';
                $profile_relative_path = !empty($profile_relative_path) ? $profile_relative_path : '';
                $aadhar_relative_path = !empty($aadhar_relative_path) ? $aadhar_relative_path : '';
                $vehicle_name = !empty($vehicle_name) ? $vehicle_name : '';
                $vehicle_type = !empty($vehicle_type) ? $vehicle_type : 'Car';
                $u_vehicle = !empty($u_vehicle) ? $u_vehicle : '';
                $u_languages = !empty($u_languages) ? $u_languages : '';
                $member_since = !empty($member_since) ? $member_since : date('Y-m-d');
                $verification_status = !empty($verification_status) ? $verification_status : 'pending';
                
                // Log all parameters before binding
                error_log("All parameters before binding: " . json_encode([
                    'u_fname' => $u_fname,
                    'u_gender' => $u_gender,
                    'u_phone' => $u_phone,
                    'alt_phone' => $alt_phone,
                    'u_email' => $u_email,
                    'u_addr' => $u_addr,
                    'license_path' => $license_relative_path,
                    'rc_path' => $rc_relative_path,
                    'profile_path' => $profile_relative_path,
                    'aadhar_path' => $aadhar_relative_path,
                    'vehicle_name' => $vehicle_name,
                    'vehicle_type' => $vehicle_type,
                    'vehicle_color' => trim($_POST['vehicle_color']), // Add vehicle_color
                    'u_vehicle' => $u_vehicle,
                    'u_languages' => $u_languages,
                    'u_pass' => '[REDACTED]',
                    'member_since' => $member_since,
                    'verification_status' => $verification_status,
                    'status' => $status_int
                ]));
                
                // Use prepared statement with proper parameter binding
                try {
                    // Bind parameters to the prepared statement
                    $stmt->bind_param('ssssssssssssssssssi', 
                        $u_fname, 
                        $u_gender, 
                        $u_phone, 
                        $alt_phone, 
                        $u_email, 
                        $u_addr, 
                        $license_relative_path, 
                        $rc_relative_path, 
                        $profile_relative_path, 
                        $aadhar_relative_path, 
                        $vehicle_name, 
                        $vehicle_type, 
                        trim($_POST['vehicle_color']), // Add vehicle_color
                        $u_vehicle, 
                        $u_languages, 
                        $u_pass, 
                        $member_since, 
                        $verification_status, 
                        $status_int
                    );
                    
                    error_log("Executing prepared statement with parameters bound");
                    
                    // Execute the prepared statement
                    if ($stmt->execute()) {
                        error_log("Prepared statement execution successful with ID: " . $conn->insert_id);
                        $execute_result = true;
                    } else {
                        error_log("Prepared statement execution failed: " . $stmt->error);
                        
                        // Fallback to direct query if prepared statement fails
                        error_log("Falling back to direct query insertion");
                        
                        // Create a direct SQL query with values inserted - USING MYSQLI REAL ESCAPE STRING FOR SAFETY
                        $u_fname = $conn->real_escape_string($u_fname);
                        $u_gender = $conn->real_escape_string($u_gender);
                        $u_phone = $conn->real_escape_string($u_phone);
                        $alt_phone = $conn->real_escape_string($alt_phone);
                        $u_email = $conn->real_escape_string($u_email);
                        $u_addr = $conn->real_escape_string($u_addr);
                        $license_relative_path = $conn->real_escape_string($license_relative_path);
                        $rc_relative_path = $conn->real_escape_string($rc_relative_path);
                        $profile_relative_path = $conn->real_escape_string($profile_relative_path);
                        $aadhar_relative_path = $conn->real_escape_string($aadhar_relative_path);
                        $vehicle_name = $conn->real_escape_string($vehicle_name);
                        $vehicle_type = $conn->real_escape_string($vehicle_type);
                        $u_vehicle = $conn->real_escape_string($u_vehicle);
                        $u_languages = $conn->real_escape_string($u_languages);
                        
                        // Create the query
                        $direct_query = "INSERT INTO drivers (Full_Name, Gender, Contact, alt_phone, Email, Address, Driving_License, RC, Profile_Pic, Aadhar, vehicle_name, vehicle_type, vehicle_color, Vehicle_Number, Languages, Password, member_since, verification_status, status) 
                             VALUES ('$u_fname', '$u_gender', '$u_phone', '$alt_phone', '$u_email', '$u_addr', '$license_relative_path', '$rc_relative_path', '$profile_relative_path', '$aadhar_relative_path', '$vehicle_name', '$vehicle_type', '$vehicle_color', '$u_vehicle', '$u_languages', '$u_pass', '$member_since', '$verification_status', $status_int)";
                        
                        error_log("Executing direct query: " . $direct_query);
                        
                        // Execute the direct query
                        if ($conn->query($direct_query) === TRUE) {
                            error_log("Direct database insertion successful with ID: " . $conn->insert_id);
                            $execute_result = true;
                        } else {
                            error_log("Direct database insertion failed: " . $conn->error);
                            throw new Exception("Database execution failed: " . $conn->error);
                        }
                    }
                } catch (Exception $bind_error) {
                    error_log("Database error: " . $bind_error->getMessage());
                    throw new Exception("Failed to insert data: " . $bind_error->getMessage());
                }
                
                error_log("Executing insert query with parameters: " . print_r([
                    'name' => $u_fname,
                    'gender' => $u_gender,
                    'phone' => $u_phone,
                    'email' => $u_email,
                    'license_path' => $license_relative_path,
                    'rc_path' => $rc_relative_path,
                    'profile_path' => $profile_relative_path,
                    'aadhar_path' => $aadhar_relative_path
                ], true));
                
                // We're now using direct query execution instead of prepared statements
                // The execute_result variable is set in the direct query execution block above
                try {
                    // If we get here, it means the direct query was successful
                    error_log("Database insertion completed successfully");
                    
                    if ($execute_result) {
                        error_log("Driver registration successful for: " . $u_email);
                        error_log("Inserted driver with vehicle_type: '" . $vehicle_type . "'");
                        
                        // Verify the insertion by querying the database
                        $verify_query = "SELECT * FROM drivers WHERE Email = ?";
                        $verify_stmt = $conn->prepare($verify_query);
                        if ($verify_stmt) {
                            $verify_stmt->bind_param('s', $u_email);
                            $verify_stmt->execute();
                            $verify_result = $verify_stmt->get_result();
                            
                            if ($verify_result->num_rows > 0) {
                                $driver_data = $verify_result->fetch_assoc();
                                error_log("Verification: Driver inserted with ID: " . $driver_data['ID'] . ", vehicle_type: " . $driver_data['vehicle_type']);
                            } else {
                                error_log("WARNING: Driver verification failed - no record found after insertion");
                            }
                            $verify_stmt->close();
                        }
                        
                        // Try to send confirmation email
                        try {
                            $emailSent = sendConfirmationEmail($u_fname, $u_email, $vehicle_type);
                            if ($emailSent) {
                                error_log("Confirmation email sent successfully to {$u_email}");
                            } else {
                                error_log("Failed to send confirmation email to {$u_email}, but continuing with registration");
                            }
                        } catch (Exception $email_error) {
                            error_log("Failed to send confirmation email: " . $email_error->getMessage());
                            // Continue with registration even if email fails
                        }
                        
                        $succ = "Thank you for registering as a {$vehicle_type} driver! Your account has been created successfully. Our support team will verify your documents within 24 hours.";
                        
                        // Log success for debugging
                        error_log("Registration successful for {$u_email} with vehicle type {$vehicle_type}");
                        
                        // Clear the vehicle_type from session after successful registration
                        unset($_SESSION['vehicle_type']);
                        
                        // Store success message in session
                        $_SESSION['registration_success'] = $succ;
                        
                        // Set a flag for JavaScript redirect
                        $redirect_success = true;
                    } else {
                        $err = "Database error occurred during registration. Please try again.";
                        error_log("Database error during insertion - execute_result is false");
                    }
                } catch (Exception $e) {
                    $err = "Exception during database insertion: " . $e->getMessage();
                    error_log("Exception during database insertion: " . $e->getMessage());
                }
                
                // Only close the statement if it exists and is a valid object
                if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                    $stmt->close();
                }
            }
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        $err = "An error occurred during registration. Please try again.";
    }
    
    error_log("=== END OF REGISTRATION PROCESS ===");
  } // End of if (isset($_POST['add_driver']))
} // End of if ($_SERVER['REQUEST_METHOD'] === 'POST')
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Google Fonts - Poppins -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <!-- Animate.css for animations -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <!-- AOS - Animate on Scroll Library -->
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
  <!-- SweetAlert2 - Load it early in the head -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Custom enhanced styles for driver signup -->
  <link rel="stylesheet" href="css/driver_signup_enhanced.css">
  <!-- Background animation styles -->
  <link rel="stylesheet" href="css/driver_signup_bg.css">
  
  <?php if(isset($redirect_success) && $redirect_success === true) { ?>
  <script>
      window.onload = function() {
          // Redirect to the success URL
          window.location.href = 'driver_signup.php?success=1';
      };
  </script>
  <?php } ?>
  
  <?php
  // Show SweetAlert for success - Move this before any other output
  if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_SESSION['registration_success'])) {
      $success_message = $_SESSION['registration_success'];
      unset($_SESSION['registration_success']); // Clear the message after use
  ?>
  <script>
      window.onload = function() {
          Swal.fire({
              title: 'Registration Successful!',
              text: '<?php echo addslashes($success_message); ?>',
              icon: 'success',
              confirmButtonColor: '#ffc107',
              confirmButtonText: 'Great!',
              allowOutsideClick: false
          }).then((result) => {
              // Redirect to login page when the alert is closed
              window.location.href = 'driver_login.php';
          });
      };
  </script>
  <?php } ?>

  <!-- Show SweetAlert for error -->
  <?php if(isset($err)) { ?>
  <script>
      window.onload = function() {
          Swal.fire({
              title: 'Registration Error',
              text: '<?php echo addslashes($err); ?>',
              icon: 'error',
              confirmButtonColor: '#ffc107',
              confirmButtonText: 'OK'
          });
      };
  </script>
  <?php } ?>
  
  <style>
    /* Modern form styling to match the image */
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fc;
    }
    
    .container-fluid {
      max-width: 550px;
      margin: 0 auto;
      padding: 2rem 1rem;
    }
    
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
      background: white;
      padding: 2rem;
    }
    
    .card-header {
      background: none;
      border: none;
      text-align: center;
      padding-bottom: 1.5rem;
      color: #333;
      font-size: 28px;
      font-weight: 700;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #333;
    }
    
    .form-control {
      width: 100%;
      padding: 12px 45px 12px 15px;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      font-size: 15px;
      transition: all 0.3s;
      background-color: #f9f9ff;
    }
    
    .form-control:focus {
      border-color: #ffc107;
      box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.15);
    }
    
    .input-group {
      position: relative;
    }
    
    .input-group i {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #aaa;
    }
    
    .custom-file-upload {
      display: flex;
      align-items: center;
      margin-top: 0.5rem;
    }
    
    .file-icon {
      background: #f9f9ff;
      width: 45px;
      height: 45px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
    }
    
    .file-icon i {
      color: #ffc107;
      font-size: 20px;
    }
    
    .file-info {
      flex: 1;
    }
    
    .file-info p {
      margin: 0;
      color: #777;
      font-size: 14px;
    }
    
    .upload-btn {
      background: #f9f9ff;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    
    .upload-btn i {
      color: #333;
    }
    
    .checkbox-container {
      display: flex;
      align-items: center;
      margin: 1.5rem 0;
    }
    
    .checkbox-icon {
      width: 20px;
      height: 20px;
      border: 2px solid #ffc107;
      border-radius: 4px;
      margin-right: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .checkbox-label {
      color: #333;
      font-weight: 500;
    }
    
    .toggle-switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 25px;
      margin-left: auto;
    }
    
    .toggle-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }
    
    .toggle-slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: .4s;
      border-radius: 34px;
    }
    
    .toggle-slider:before {
      position: absolute;
      content: "";
      height: 17px;
      width: 17px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }
    
    input:checked + .toggle-slider {
      background-color: #ffc107;
    }
    
    input:checked + .toggle-slider:before {
      transform: translateX(26px);
    }
    
    .newsletter-row {
      display: flex;
      align-items: center;
      margin: 1.5rem 0;
    }
    
    .newsletter-icon {
      width: 40px;
      height: 40px;
      background: #fff9e6;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
    }
    
    .newsletter-icon i {
      color: #ffc107;
    }
    
    .newsletter-text {
      flex: 1;
    }
    
    .newsletter-text p {
      margin: 0;
      font-size: 14px;
      color: #777;
    }
    
    .btn-submit {
      width: 100%;
      padding: 15px;
      background: #ffc107;
      color: #333;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      margin-top: 1rem;
    }
    
    .btn-submit:hover {
      background: #e0a800;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    
    .invalid-feedback {
      display: none;
      color: #e74a3b;
      font-size: 80%;
      margin-top: 0.25rem;
    }
    
    .was-validated .form-control:invalid {
      border-color: #e74a3b;
    }
    
    .was-validated .form-control:invalid + i {
      color: #e74a3b;
    }
    
    .was-validated .form-control:invalid ~ .invalid-feedback {
      display: block;
    }
    
    .custom-file-input {
      position: absolute;
      width: 100%;
      height: 100%;
      opacity: 0;
      cursor: pointer;
      z-index: 1;
    }
    
    /* Responsive styles */
    @media (max-width: 576px) {
      .container-fluid {
        padding: 1rem;
      }
      
      .card {
        padding: 1.5rem;
      }
      
      .card-header {
        font-size: 24px;
      }
    }
    
    /* Add this to your existing CSS to ensure the button works properly */
    .btn-primary {
      background-color: #ffc107;
      border-color: #ffc107;
      color: #333;
      font-weight: 600;
      font-size: 16px;
      transition: all 0.3s;
      cursor: pointer;
    }
    
    .btn-primary:hover {
      background-color: #e0a800;
      border-color: #e0a800;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    }
    
    /* Fix file inputs */
    .file-info {
      position: relative;
    }
    
    .file-info .custom-file-input {
      right: 0;
      top: 0;
      height: 100%;
      width: 100%;
      z-index: 1;
    }

    .vehicle-type-display {
        background: linear-gradient(135deg, #fff9e6 0%, #fff5cc 100%);
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.1);
        border: 1px solid rgba(255, 193, 7, 0.2);
    }

    .vehicle-type-content {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .vehicle-type-icon {
        background: #ffc107;
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .vehicle-type-icon i {
        color: white;
        font-size: 1.5rem;
    }

    .vehicle-type-info {
        flex: 1;
    }

    .vehicle-type-info h3 {
        font-size: 0.9rem;
        color: #666;
        margin: 0 0 0.3rem 0;
        font-weight: 500;
    }

    .vehicle-type-badge {
        font-size: 1.2rem;
        font-weight: 600;
        color: #2c3e50;
    }

    .vehicle-type-change {
        background: white;
        color: #2c3e50;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .vehicle-type-change:hover {
        background: #ffc107;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(255, 193, 7, 0.2);
    }

    @media (max-width: 576px) {
        .vehicle-type-content {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .vehicle-type-info {
            margin-bottom: 0.5rem;
        }

        .vehicle-type-change {
            width: 100%;
            justify-content: center;
        }
    }

    /* ... existing styles ... */
    
    .image-preview {
      margin-top: 10px;
      max-width: 200px;
      display: none;
    }
    
    .image-preview img {
      width: 100%;
      height: auto;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .custom-file-upload {
      position: relative;
    }
    
    .file-info {
      flex: 1;
    }
    
    .preview-container {
      display: flex;
      align-items: center;
      margin-top: 10px;
    }
    
    .preview-image {
      max-width: 100px;
      margin-right: 10px;
    }
    
    .remove-preview {
      color: #dc3545;
      cursor: pointer;
      margin-left: 10px;
    }
  </style>
</head>
<body id="page-top" class="driver-signup-page">
  <!-- Animated Background -->
  <div class="animated-background">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <div class="shape shape-4"></div>
    <div class="shape shape-5"></div>
    <div class="car-icon car-icon-1"><i class="fas fa-car"></i></div>
    <div class="car-icon car-icon-2"><i class="fas fa-car-side"></i></div>
    <div class="car-icon car-icon-3"><i class="fas fa-taxi"></i></div>
  </div>
  <!-- Handle redirect with JavaScript if needed -->
  <?php if(isset($redirect_success) && $redirect_success === true) { ?>
  <script>
    // Redirect to success page
    window.location.href = 'driver_signup.php?success=1';
  </script>
  <?php } ?>

  <!-- Show SweetAlert immediately if success or error exists -->
  <?php if(isset($succ)) { ?>
  <script>
    // Execute immediately without waiting for DOMContentLoaded
    Swal.fire({
      title: 'Registration Successful!',
      text: '<?php echo addslashes($succ); ?>',
      icon: 'success',
      confirmButtonColor: '#ffc107',
      confirmButtonText: 'Great!',
      timer: 5000,
      timerProgressBar: true,
      showClass: {
        popup: 'animate__animated animate__fadeInDown'
      },
      hideClass: {
        popup: 'animate__animated animate__fadeOutUp'
      },
      allowOutsideClick: false
    }).then((result) => {
      // Redirect to login page when the alert is closed
      window.location.href = 'driver_login.php';
    });
  </script>
  <?php } ?>
  
  <?php if(isset($err)) { ?>
  <script>
    // Execute immediately without waiting for DOMContentLoaded
    Swal.fire({
      title: 'Registration Error',
      text: '<?php echo addslashes($err); ?>',
      icon: 'error',
      confirmButtonColor: '#ffc107',
      confirmButtonText: 'OK'
    });
  </script>
  <?php } ?>
  
  <div id="wrapper">
    <div id="content-wrapper">

      <div class="container-fluid">
        
      <div class="card animate__animated animate__fadeIn" data-aos="fade-up">
        <!-- Card highlight effect -->
        <div class="card-highlight"></div>
        <div class="card-header">
          <i class="fas fa-user-plus me-2"></i> Create your account
        </div>
        <div class="card-body">
          <!--Add User Form-->
          <!-- Progress Indicator -->
          <div class="progress-indicator">
            <div class="progress-step active">
              <div class="step-number">1</div>
              <div class="step-label">Personal Info</div>
            </div>
            <div class="progress-step">
              <div class="step-number">2</div>
              <div class="step-label">Documents</div>
            </div>
            <div class="progress-step">
              <div class="step-number">3</div>
              <div class="step-label">Vehicle Info</div>
            </div>
            <div class="progress-step">
              <div class="step-number">4</div>
              <div class="step-label">Finish</div>
            </div>
          </div>
          
          <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" class="needs-validation" id="driverSignupForm" novalidate>
            <!-- Hidden field for vehicle type -->
            <input type="hidden" name="vehicle_type" value="<?php echo htmlspecialchars($vehicle_type); ?>">
            <input type="hidden" name="add_driver" value="1">
            
            <!-- Vehicle Type Display -->
            <div class="vehicle-type-display animate__animated animate__fadeIn mb-4">
                <div class="vehicle-type-content">
                    <div class="vehicle-type-icon">
                        <i class="fas <?php
                            $icon = 'fa-car';
                            if (strpos(strtolower($vehicle_type), 'bike') !== false) $icon = 'fa-motorcycle';
                            else if (strpos(strtolower($vehicle_type), 'auto') !== false) $icon = 'fa-taxi';
                            else if (strpos(strtolower($vehicle_type), 'bus') !== false) $icon = 'fa-bus-alt';
                            else if (strpos(strtolower($vehicle_type), 'goods') !== false) $icon = 'fa-truck';
                            echo $icon;
                        ?>"></i>
                    </div>
                    <div class="vehicle-type-info">
                        <h3>Selected Vehicle Type</h3>
                        <span class="vehicle-type-badge"><?php echo htmlspecialchars($vehicle_type); ?></span>
                    </div>
                    <a href="select_vehicle_type.php" class="vehicle-type-change">
                        <i class="fas fa-edit"></i> Change
                    </a>
                </div>
            </div>

            <style>
                .vehicle-type-display {
                    background: linear-gradient(135deg, #fff9e6 0%, #fff5cc 100%);
                    border-radius: 15px;
                    padding: 1.5rem;
                    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.1);
                    border: 1px solid rgba(255, 193, 7, 0.2);
                }

                .vehicle-type-content {
                    display: flex;
                    align-items: center;
                    gap: 1.5rem;
                }

                .vehicle-type-icon {
                    background: #ffc107;
                    width: 50px;
                    height: 50px;
                    border-radius: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .vehicle-type-icon i {
                    color: white;
                    font-size: 1.5rem;
                }

                .vehicle-type-info {
                    flex: 1;
                }

                .vehicle-type-info h3 {
                    font-size: 0.9rem;
                    color: #666;
                    margin: 0 0 0.3rem 0;
                    font-weight: 500;
                }

                .vehicle-type-badge {
                    font-size: 1.2rem;
                    font-weight: 600;
                    color: #2c3e50;
                }

                .vehicle-type-change {
                    background: white;
                    color: #2c3e50;
                    padding: 0.6rem 1.2rem;
                    border-radius: 8px;
                    text-decoration: none;
                    font-weight: 500;
                    font-size: 0.9rem;
                    transition: all 0.3s ease;
                    border: 1px solid rgba(0, 0, 0, 0.1);
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }

                .vehicle-type-change:hover {
                    background: #ffc107;
                    color: white;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 10px rgba(255, 193, 7, 0.2);
                }

                @media (max-width: 576px) {
                    .vehicle-type-content {
                        flex-direction: column;
                        text-align: center;
                        gap: 1rem;
                    }

                    .vehicle-type-info {
                        margin-bottom: 0.5rem;
                    }

                    .vehicle-type-change {
                        width: 100%;
                        justify-content: center;
                    }
                }
            </style>

            <!-- Display error message if exists -->
            <?php if(isset($err) && !empty($err)) { ?>
            <div class="alert alert-danger mb-4">
              <strong>Error:</strong> <?php echo $err; ?>
            </div>
            <?php } ?>
            
            <!-- Display success message if exists -->
            <?php if(isset($succ) && !empty($succ)) { ?>
            <div class="alert alert-success mb-4">
              <strong>Success:</strong> <?php echo $succ; ?>
            </div>
            <?php } ?>
            
            <!-- Vehicle Type (hidden) -->
            <input type="hidden" name="vehicle_type_hidden" value="<?php echo htmlspecialchars($vehicle_type); ?>">
            <?php error_log("Form hidden field vehicle_type_hidden value: " . htmlspecialchars($vehicle_type)); ?>
            
            <div class="form-group animate-fade-in">
              <label for="full_name">Full Name</label>
              <div class="input-group">
                <input type="text" required class="form-control" id="full_name" name="u_fname" placeholder="Enter your full name">
                <i class="fas fa-user"></i>
                <div class="invalid-feedback">Please enter your full name</div>
              </div>
            </div>
            
            <div class="form-group animate-fade-in">
              <label for="gender">Gender</label>
              <div class="input-group">
                <select class="form-control" id="gender" name="u_gender" required>
                  <option value="">Select Gender</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
                </select>
                <i class="fas fa-venus-mars"></i>
                <div class="invalid-feedback">Please select your gender</div>
              </div>
            </div>
            
            <div class="form-group animate-fade-in">
              <label for="phone">Phone Number</label>
              <div class="input-group">
                <input type="tel" required class="form-control" id="phone" name="u_phone" pattern="[0-9]{10}" placeholder="Enter your phone number">
                <i class="fas fa-phone"></i>
                <div class="invalid-feedback">Please enter a valid 10-digit phone number</div>
              </div>
            </div>
            
            <div class="form-group animate-fade-in">
              <label for="alt_phone">Alternate Phone Number</label>
              <div class="input-group">
                <input type="tel" class="form-control" id="alt_phone" name="alt_phone" pattern="[0-9]{10}" placeholder="Enter alternate phone number (optional)">
                <i class="fas fa-phone-alt"></i>
                <div class="invalid-feedback">Please enter a valid 10-digit phone number</div>
              </div>
            </div>
            
            <div class="form-group animate-fade-in">
              <label for="email">Email</label>
              <div class="input-group">
                <input type="email" required class="form-control" id="email" name="u_email" placeholder="Enter your email address">
                <i class="fas fa-envelope"></i>
                <div class="invalid-feedback">Please enter a valid email address</div>
              </div>
            </div>
            
            <div class="form-group animate-fade-in">
              <label for="address">Address</label>
              <div class="input-group">
                <input type="text" required class="form-control" id="address" name="u_addr" placeholder="Enter your address">
                <i class="fas fa-map-marker-alt"></i>
                <div class="invalid-feedback">Please enter your address</div>
              </div>
            </div>
            
            <!-- Profile Picture -->
            <div class="form-group animate-fade-in">
              <label for="profile_pic">Profile Picture</label>
              <div class="custom-file-upload">
                <div class="file-icon">
                  <i class="fas fa-camera"></i>
                </div>
                <div class="file-info">
                  <p id="profile-file-name">Upload your profile picture (any image format)</p>
                  <input type="file" class="custom-file-input" name="profile_pic" id="profile_pic" accept="image/*" required>
                  <div class="invalid-feedback">Please select a profile picture</div>
                  <div class="image-preview" id="profile-preview"></div>
                </div>
                <label for="profile_pic" class="upload-btn">
                  <i class="fas fa-arrow-up"></i>
                </label>
              </div>
            </div>
            
            <!-- Driving License -->
            <div class="form-group animate-fade-in">
              <label for="u_photo">Driving License</label>
              <div class="custom-file-upload">
                <div class="file-icon">
                  <i class="fas fa-id-card"></i>
                </div>
                <div class="file-info">
                  <p id="license-file-name">Upload your driving license (any image format)</p>
                  <input type="file" class="custom-file-input" name="u_photo" id="u_photo" accept="image/*" required>
                  <div class="invalid-feedback">Please upload your driving license</div>
                  <div class="image-preview" id="license-preview"></div>
                </div>
                <label for="u_photo" class="upload-btn">
                  <i class="fas fa-arrow-up"></i>
                </label>
              </div>
            </div>
            
            <!-- Registration Certificate (RC) -->
            <div class="form-group animate-fade-in">
              <label for="rc_photo">Registration Certificate (RC)</label>
              <div class="custom-file-upload">
                <div class="file-icon">
                  <i class="fas fa-file-image"></i>
                </div>
                <div class="file-info">
                  <p id="rc-file-name">Upload your vehicle RC (any image format)</p>
                  <input type="file" class="custom-file-input" name="rc_photo" id="rc_photo" accept="image/*" required>
                  <div class="invalid-feedback">Please upload your vehicle RC</div>
                  <div class="image-preview" id="rc-preview"></div>
                </div>
                <label for="rc_photo" class="upload-btn">
                  <i class="fas fa-arrow-up"></i>
                </label>
              </div>
            </div>
            
            <!-- Aadhar Card -->
            <div class="form-group animate-fade-in">
              <label for="aadhar_photo">Aadhar Card</label>
              <div class="custom-file-upload">
                <div class="file-icon">
                  <i class="fas fa-id-card"></i>
                </div>
                <div class="file-info">
                  <p id="aadhar-file-name">Upload your Aadhar card (any image format)</p>
                  <input type="file" class="custom-file-input" name="aadhar_photo" id="aadhar_photo" accept="image/*" required>
                  <div class="invalid-feedback">Please upload your Aadhar card</div>
                  <div class="image-preview" id="aadhar-preview"></div>
                </div>
                <label for="aadhar_photo" class="upload-btn">
                  <i class="fas fa-arrow-up"></i>
                </label>
              </div>
            </div>
            
            <div class="form-group animate-fade-in">
              <label for="vehicle_name">Vehicle Name</label>
              <div class="input-group">
                <input type="text" class="form-control" name="vehicle_name" id="vehicle_name" required placeholder="Enter your vehicle name/model">
                <i class="fas fa-car-side"></i>
                <div class="invalid-feedback">Please enter your vehicle name</div>
              </div>
            </div>
            
            <div class="form-group animate-fade-in">
              <label for="vehicle_color">Vehicle Color</label>
              <div class="input-group">
                <input type="text" class="form-control" name="vehicle_color" id="vehicle_color" required placeholder="Enter your vehicle color">
                <i class="fas fa-palette"></i>
                <div class="invalid-feedback">Please enter your vehicle color</div>
              </div>
            </div>
            
            <div class="form-group animate-fade-in">
              <label for="u_vehicle">Vehicle Number</label>
              <div class="input-group">
                <input type="text" class="form-control" name="u_vehicle" id="u_vehicle" required placeholder="Enter your vehicle number (e.g. MH02AB1234)" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                <i class="fas fa-car"></i>
                <div class="invalid-feedback">Please enter a valid vehicle number (e.g., MH02AB1234)</div>
              </div>
            </div>
            
            <div class="form-group animate-fade-in">
              <label for="u_languages">Languages Known</label>
              <div class="input-group">
                <input type="text" class="form-control" name="u_languages" id="u_languages" placeholder="e.g., English, Hindi, Tamil" required>
                <i class="fas fa-language"></i>
                <div class="invalid-feedback">Please enter at least one language</div>
              </div>
            </div>
            
            <div class="form-group animate-fade-in">
              <label for="password">Password</label>
              <div class="input-group">
                <input type="password" class="form-control" name="password" id="password" required placeholder="Create a strong password" minlength="6">
                <i class="fas fa-lock" style="position: absolute; right: 45px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
                <button type="button" class="btn btn-link toggle-password" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); z-index: 10; background: none; border: none; padding: 0 15px;">
                  <i class="fas fa-eye"></i>
                </button>
                <div class="invalid-feedback">Password must be at least 6 characters long</div>
              </div>
            </div>
            
            <!-- Submit button -->
            <div class="form-group mt-4 animate-fade-in" style="margin-top: 30px;">
              <button type="submit" class="btn btn-primary btn-block py-3 animate__animated animate__pulse animate__infinite">Register Now <i class="fas fa-arrow-right ms-2"></i></button>
            </div>
            

            <!-- Debug info -->
            <?php if($display_debug || isset($_GET['debug'])): ?>
            <div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 5px;">
              <h5>Debug Information</h5>
              <p>Vehicle Type: <?php echo htmlspecialchars($vehicle_type); ?></p>
              <p>Form Submitted: <?php echo $form_submitted ? 'Yes' : 'No'; ?></p>
              <?php if(isset($err)): ?>
              <p>Error: <span style="color: red;"><?php echo htmlspecialchars($err); ?></span></p>
              <?php endif; ?>
              <?php if(isset($succ)): ?>
              <p>Success: <span style="color: green;"><?php echo htmlspecialchars($succ); ?></span></p>
              <?php endif; ?>
              <p>Session Data: <pre><?php echo htmlspecialchars(print_r($_SESSION, true)); ?></pre></p>
              <p>POST Data: <pre><?php echo htmlspecialchars(print_r($_POST, true)); ?></pre></p>
            </div>
            <?php endif; ?>
          </form>
          <!-- End Form-->
        </div>
      </div>

    </div>
    <!-- /.content-wrapper -->

  </div>
  <!-- /#wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Bootstrap core JavaScript-->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Page level plugin JavaScript-->
  <script src="vendor/chart.js/Chart.min.js"></script>
  <script src="vendor/datatables/jquery.dataTables.js"></script>
  <script src="vendor/datatables/dataTables.bootstrap4.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="vendor/js/sb-admin.min.js"></script>

  <!-- Demo scripts for this page-->
  <script src="vendor/js/demo/datatables-demo.js"></script>
  <script src="vendor/js/demo/chart-area-demo.js"></script>
  
  <!-- AOS - Animate on Scroll Library -->
  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  
  <!-- Form validation and file handling script -->
  <script>
    // Initialize AOS
    AOS.init({
      duration: 800,
      easing: 'ease-in-out',
      once: false,
      mirror: true
    });
    
    // Add parallax effect to background shapes
    document.addEventListener('mousemove', function(e) {
      const shapes = document.querySelectorAll('.shape');
      const mouseX = e.clientX / window.innerWidth;
      const mouseY = e.clientY / window.innerHeight;
      
      shapes.forEach(function(shape, index) {
        const speed = 0.03 + (index * 0.01);
        const x = (mouseX - 0.5) * speed * 100;
        const y = (mouseY - 0.5) * speed * 100;
        shape.style.transform = `translate(${x}px, ${y}px)`;
      });
    });
    
    // Show/hide scroll to top button
    window.addEventListener('scroll', function() {
      const scrollToTopBtn = document.querySelector('.scroll-to-top');
      if (window.pageYOffset > 300) {
        scrollToTopBtn.classList.add('visible');
      } else {
        scrollToTopBtn.classList.remove('visible');
      }
    });
    
    // Simulate progress steps (for demonstration)
    const formGroups = document.querySelectorAll('.form-group');
    const progressSteps = document.querySelectorAll('.progress-step');
    
    if (formGroups.length > 0 && progressSteps.length > 0) {
      formGroups.forEach(function(group, index) {
        const inputElement = group.querySelector('input, select');
        if (inputElement) {
          inputElement.addEventListener('focus', function() {
            // Determine which step this input belongs to
            let stepIndex = 0;
            if (index >= 0 && index <= 5) stepIndex = 0; // Personal info
            else if (index > 5 && index <= 9) stepIndex = 1; // Documents
            else if (index > 9) stepIndex = 2; // Vehicle info
            
            // Update active step
            progressSteps.forEach(function(step, i) {
              if (i <= stepIndex) {
                step.classList.add('active');
              } else {
                step.classList.remove('active');
              }
            });
          });
        }
      });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
      // Add animation classes to form groups for staggered animation
      const formGroups = document.querySelectorAll('.form-group');
      formGroups.forEach(function(group, index) {
        setTimeout(function() {
          group.classList.add('animate__animated', 'animate__fadeInUp');
          
          // Add hover effect to form groups
          group.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
          });
          
          group.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
          });
        }, index * 100);
      });
      
      // Add focus effects to form controls
      const formControls = document.querySelectorAll('.form-control');
      formControls.forEach(function(control) {
        control.addEventListener('focus', function() {
          const parentGroup = this.closest('.form-group');
          if (parentGroup) {
            parentGroup.classList.add('animate__pulse');
          }
        });
        
        control.addEventListener('blur', function() {
          const parentGroup = this.closest('.form-group');
          if (parentGroup) {
            parentGroup.classList.remove('animate__pulse');
          }
        });
      });
      
      // Get the form element
      const form = document.getElementById('driverSignupForm');
      
      // Password toggle functionality
      const togglePassword = document.querySelector('.toggle-password');
      const password = document.getElementById('password');
      
      if (togglePassword && password) {
        togglePassword.addEventListener('click', function(e) {
          e.preventDefault();
          // Toggle the type attribute
          const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
          password.setAttribute('type', type);
          
          // Toggle the eye / eye-slash icon
          const icon = this.querySelector('i');
          icon.classList.toggle('fa-eye');
          icon.classList.toggle('fa-eye-slash');
        });
      }
      
      if (!form) return; // Exit if form not found
      
      // Update file names when selected
      const profileInput = document.getElementById('profile_pic');
      if (profileInput) {
        profileInput.addEventListener('change', function(e) {
          e.stopPropagation(); // Prevent event bubbling
          const fileName = this.files[0] ? this.files[0].name : 'Upload your profile picture';
          const nameElement = document.getElementById('profile-file-name');
          if (nameElement) nameElement.textContent = fileName;
        });
      }
      
      const licenseInput = document.getElementById('u_photo');
      if (licenseInput) {
        licenseInput.addEventListener('change', function(e) {
          e.stopPropagation(); // Prevent event bubbling
          const fileName = this.files[0] ? this.files[0].name : 'Upload your driving license';
          const nameElement = document.getElementById('license-file-name');
          if (nameElement) nameElement.textContent = fileName;
        });
      }
      
      const rcInput = document.getElementById('rc_photo');
      if (rcInput) {
        rcInput.addEventListener('change', function(e) {
          e.stopPropagation(); // Prevent event bubbling
          const fileName = this.files[0] ? this.files[0].name : 'Upload your vehicle RC';
          const nameElement = document.getElementById('rc-file-name');
          if (nameElement) nameElement.textContent = fileName;
        });
      }
      
      // Add event listener for Aadhaar file input
      const aadharInput = document.getElementById('aadhar_photo');
      if (aadharInput) {
        aadharInput.addEventListener('change', function(e) {
          e.stopPropagation(); // Prevent event bubbling
          const fileName = this.files[0] ? this.files[0].name : 'Upload your Aadhar card';
          const nameElement = document.getElementById('aadhar-file-name');
          if (nameElement) nameElement.textContent = fileName;
        });
      }
      
      // Handle file upload button clicks
      const uploadButtons = document.querySelectorAll('.upload-btn');
      uploadButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          const inputId = this.getAttribute('for');
          const input = document.getElementById(inputId);
          if (input) {
            input.click();
          }
        });
      });
      
      // Prevent clicks on file input from bubbling up
      const fileInputs = document.querySelectorAll('.custom-file-input');
      fileInputs.forEach(function(input) {
        input.addEventListener('click', function(e) {
          e.stopPropagation();
        });
      });
      
      // Validate on form submission
      form.addEventListener('submit', function(event) {
        console.log('Form submit event triggered');
        
        // Log form data for debugging
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        console.log('Form enctype:', form.enctype);
        
        // Collect form data for debugging
        const formData = new FormData(form);
        console.log('Form data entries:');
        for (let pair of formData.entries()) {
          if (pair[0] !== 'password') { // Don't log password
            console.log(pair[0] + ': ' + pair[1]);
          }
        }
        
        // IMPORTANT: Disable client-side validation temporarily
        // We'll let the server handle validation
        
        // If form is valid, show loading state
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
          submitButton.disabled = true;
          submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        }
        
        // Allow the form to submit
        return true;
      });
    });
    
    // Add a success message if redirected from a successful registration
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
      Swal.fire({
        title: 'Registration Successful!',
        text: 'Your driver account has been created successfully.',
        icon: 'success',
        confirmButtonColor: '#ffc107'
      });
    }
  </script>

  <!-- Function to handle image preview -->
  <script>
  function handleImagePreview(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        preview.style.display = 'block';
        preview.innerHTML = `
          <div class="preview-container">
            <img src="${e.target.result}" class="preview-image" alt="Preview">
            <i class="fas fa-times remove-preview" onclick="removePreview('${previewId}', '${input.id}')"></i>
          </div>
        `;
      }
      
      reader.readAsDataURL(file);
    }
  }

  // Function to remove preview
  function removePreview(previewId, inputId) {
    const preview = document.getElementById(previewId);
    const input = document.getElementById(inputId);
    
    preview.style.display = 'none';
    preview.innerHTML = '';
    input.value = '';
    
    // Reset the file name text
    const fileNameId = inputId + '-file-name';
    const fileNameElement = document.getElementById(fileNameId);
    if (fileNameElement) {
      const defaultText = {
        'profile_pic': 'Upload your profile picture',
        'u_photo': 'Upload your driving license',
        'rc_photo': 'Upload your vehicle RC',
        'aadhar_photo': 'Upload your Aadhar card'
      };
      fileNameElement.textContent = defaultText[inputId] || 'Upload file';
    }
  }

  // Add event listeners for file inputs
  document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = {
      'profile_pic': 'profile-preview',
      'u_photo': 'license-preview',
      'rc_photo': 'rc-preview',
      'aadhar_photo': 'aadhar-preview'
    };
    
    for (let inputId in fileInputs) {
      const input = document.getElementById(inputId);
      if (input) {
        input.addEventListener('change', function() {
          handleImagePreview(this, fileInputs[inputId]);
        });
      }
    }
  });
  </script>

  <script>
document.addEventListener('DOMContentLoaded', function() {
    // Store form data before validation
    function storeFormData() {
        const formData = {};
        const form = document.getElementById('driverSignupForm');
        const inputs = form.querySelectorAll('input:not([type="file"]), select');
        
        inputs.forEach(input => {
            formData[input.id] = input.value;
        });
        
        sessionStorage.setItem('driverFormData', JSON.stringify(formData));
    }

    // Restore form data
    function restoreFormData() {
        const storedData = sessionStorage.getItem('driverFormData');
        if (storedData) {
            const formData = JSON.parse(storedData);
            Object.keys(formData).forEach(inputId => {
                const input = document.getElementById(inputId);
                if (input) {
                    input.value = formData[inputId];
                }
            });
        }
    }

    // Clear stored form data
    function clearStoredFormData() {
        sessionStorage.removeItem('driverFormData');
    }

    // Reset submit button state
    function resetSubmitButton() {
        const submitButton = document.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Register Now <i class="fas fa-arrow-right ms-2"></i>';
        }
    }

    // Show loading state
    function showLoadingState() {
        const submitButton = document.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        }
    }

    // Form validation function
    function validateForm() {
        const form = document.getElementById('driverSignupForm');
        let isValid = true;
        let errorMessage = '';
        let firstInvalidField = null;

        // Store form data before validation
        storeFormData();

        // Full Name validation
        const fullName = document.getElementById('full_name').value.trim();
        if (!fullName) {
            isValid = false;
            errorMessage += '- Please enter your full name\n';
            if (!firstInvalidField) firstInvalidField = 'full_name';
        } else if (!/^[a-zA-Z\s]{3,50}$/.test(fullName)) {
            isValid = false;
            errorMessage += '- Full name should only contain letters and spaces (3-50 characters)\n';
            if (!firstInvalidField) firstInvalidField = 'full_name';
        }

        // Gender validation
        const gender = document.getElementById('gender').value;
        if (!gender) {
            isValid = false;
            errorMessage += '- Please select your gender\n';
            if (!firstInvalidField) firstInvalidField = 'gender';
        }

        // Phone number validation
        const phone = document.getElementById('phone').value.trim();
        if (!phone) {
            isValid = false;
            errorMessage += '- Please enter your phone number\n';
            if (!firstInvalidField) firstInvalidField = 'phone';
        } else if (!/^[0-9]{10}$/.test(phone)) {
            isValid = false;
            errorMessage += '- Phone number must be exactly 10 digits\n';
            if (!firstInvalidField) firstInvalidField = 'phone';
        }

        // Alternate phone validation (if provided)
        const altPhone = document.getElementById('alt_phone').value.trim();
        if (altPhone && !/^[0-9]{10}$/.test(altPhone)) {
            isValid = false;
            errorMessage += '- Alternate phone number must be exactly 10 digits\n';
            if (!firstInvalidField) firstInvalidField = 'alt_phone';
        }

        // Email validation
        const email = document.getElementById('email').value.trim();
        if (!email) {
            isValid = false;
            errorMessage += '- Please enter your email address\n';
            if (!firstInvalidField) firstInvalidField = 'email';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            isValid = false;
            errorMessage += '- Please enter a valid email address\n';
            if (!firstInvalidField) firstInvalidField = 'email';
        }

        // Address validation
        const address = document.getElementById('address').value.trim();
        if (!address) {
            isValid = false;
            errorMessage += '- Please enter your address\n';
            if (!firstInvalidField) firstInvalidField = 'address';
        } else if (address.length < 10) {
            isValid = false;
            errorMessage += '- Address should be at least 10 characters long\n';
            if (!firstInvalidField) firstInvalidField = 'address';
        }

        // File validations
        const profilePic = document.getElementById('profile_pic').files[0];
        const license = document.getElementById('u_photo').files[0];
        const rc = document.getElementById('rc_photo').files[0];
        const aadhar = document.getElementById('aadhar_photo').files[0];

        if (!profilePic) {
            isValid = false;
            errorMessage += '- Please upload your profile picture\n';
            if (!firstInvalidField) firstInvalidField = 'profile_pic';
        }
        if (!license) {
            isValid = false;
            errorMessage += '- Please upload your driving license\n';
            if (!firstInvalidField) firstInvalidField = 'u_photo';
        }
        if (!rc) {
            isValid = false;
            errorMessage += '- Please upload your RC\n';
            if (!firstInvalidField) firstInvalidField = 'rc_photo';
        }
        if (!aadhar) {
            isValid = false;
            errorMessage += '- Please upload your Aadhar card\n';
            if (!firstInvalidField) firstInvalidField = 'aadhar_photo';
        }

        // Vehicle details validation
        const vehicleName = document.getElementById('vehicle_name').value.trim();
        if (!vehicleName) {
            isValid = false;
            errorMessage += '- Please enter your vehicle name/model\n';
            if (!firstInvalidField) firstInvalidField = 'vehicle_name';
        }

        const vehicleColor = document.getElementById('vehicle_color').value.trim();
        if (!vehicleColor) {
            isValid = false;
            errorMessage += '- Please enter your vehicle color\n';
            if (!firstInvalidField) firstInvalidField = 'vehicle_color';
        }

        const vehicleNumber = document.getElementById('u_vehicle').value.trim().toUpperCase();
        if (!vehicleNumber) {
            isValid = false;
            errorMessage += '- Please enter your vehicle number\n';
            if (!firstInvalidField) firstInvalidField = 'u_vehicle';
        } else if (!/^[A-Z]{2}[0-9]{2}[A-Z]{1,2}[0-9]{4}$/.test(vehicleNumber)) {
            isValid = false;
            errorMessage += '- Please enter a valid vehicle number (e.g., MH02AB1234)\n';
            if (!firstInvalidField) firstInvalidField = 'u_vehicle';
        }

        // Languages validation
        const languages = document.getElementById('u_languages').value.trim();
        if (!languages) {
            isValid = false;
            errorMessage += '- Please enter languages known\n';
            if (!firstInvalidField) firstInvalidField = 'u_languages';
        }

        // Password validation
        const password = document.getElementById('password').value;
        if (!password) {
            isValid = false;
            errorMessage += '- Please enter a password\n';
            if (!firstInvalidField) firstInvalidField = 'password';
        } else if (password.length < 6) {
            isValid = false;
            errorMessage += '- Password must be at least 6 characters long\n';
            if (!firstInvalidField) firstInvalidField = 'password';
        } else if (!/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/.test(password)) {
            isValid = false;
            errorMessage += '- Password must contain at least one letter and one number\n';
            if (!firstInvalidField) firstInvalidField = 'password';
        }

        // File type validation
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        function validateFile(file, fieldName) {
            if (file) {
                if (!allowedTypes.includes(file.type)) {
                    isValid = false;
                    errorMessage += `- ${fieldName} must be a JPG, JPEG or PNG file\n`;
                }
                if (file.size > maxSize) {
                    isValid = false;
                    errorMessage += `- ${fieldName} must be less than 5MB\n`;
                }
            }
        }

        validateFile(profilePic, 'Profile picture');
        validateFile(license, 'Driving license');
        validateFile(rc, 'RC');
        validateFile(aadhar, 'Aadhar card');

        return { isValid, errorMessage, firstInvalidField };
    }

    // Form submission handler
    const form = document.getElementById('driverSignupForm');
    if (form) {
        // Restore form data on page load
        restoreFormData();

        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            // Show loading state
            showLoadingState();
            
            const validation = validateForm();
            
            if (!validation.isValid) {
                // Reset submit button state
                resetSubmitButton();
                
                // Show error message using SweetAlert
                await Swal.fire({
                    title: 'Validation Error',
                    html: validation.errorMessage.split('\n').join('<br>'),
                    icon: 'error',
                    confirmButtonColor: '#ffc107'
                });

                // Focus on the first invalid field after the alert is closed
                if (validation.firstInvalidField) {
                    const invalidField = document.getElementById(validation.firstInvalidField);
                    if (invalidField) {
                        invalidField.focus();
                        invalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            } else {
                try {
                    // Clear stored form data before submission
                    clearStoredFormData();
                    
                    // Submit the form
                    form.submit();
                } catch (error) {
                    console.error('Form submission error:', error);
                    
                    // Reset submit button state if submission fails
                    resetSubmitButton();
                    
                    // Show error message
                    await Swal.fire({
                        title: 'Submission Error',
                        text: 'There was an error submitting the form. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#ffc107'
                    });
                }
            }
        });

        // Add autosave functionality for form fields
        const inputs = form.querySelectorAll('input:not([type="file"]), select');
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                storeFormData();
            });

            // Also store data when user types (debounced)
            let timeout;
            input.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    storeFormData();
                }, 500);
            });
        });
    }

    // Real-time validation feedback
    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const formGroup = this.closest('.form-group');
            const invalidFeedback = formGroup.querySelector('.invalid-feedback');
            
            if (this.value.trim() === '') {
                formGroup.classList.add('was-validated');
                this.classList.add('is-invalid');
            } else {
                formGroup.classList.remove('was-validated');
                this.classList.remove('is-invalid');
            }
        });
    });

    // Handle page refresh/unload
    window.addEventListener('beforeunload', function(e) {
        // Store form data before page is unloaded
        storeFormData();
    });
});
</script>
</body>
</html>