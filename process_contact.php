<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once 'db.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data and sanitize inputs
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $subject = isset($_POST['subject']) ? htmlspecialchars(trim($_POST['subject'])) : '';
    $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';
    $date_submitted = date('Y-m-d H:i:s');
    
    // Validate required fields
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no errors, proceed with storing the message
    if (empty($errors)) {
        try {
            // Check if contacts table exists, if not create it
            $check_table = $conn->query("SHOW TABLES LIKE 'contact_messages'");
            if ($check_table->num_rows == 0) {
                // Create the table
                $create_table = "CREATE TABLE contact_messages (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    phone VARCHAR(50),
                    subject VARCHAR(255),
                    message TEXT NOT NULL,
                    date_submitted DATETIME NOT NULL,
                    status ENUM('unread', 'read', 'replied') DEFAULT 'unread'
                )";
                
                $conn->query($create_table);
            }
            
            // Insert the message into the database
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, date_submitted) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $phone, $subject, $message, $date_submitted);
            
            if ($stmt->execute()) {
                // Set success message
                $_SESSION['contact_success'] = "Thank you for your message! We'll get back to you soon.";
                
                // Optional: Send notification email to admin
                $to = "admin@poolpal.com"; // Change to your admin email
                $email_subject = "New Contact Form Submission: " . $subject;
                $email_body = "You have received a new message from the contact form.\n\n";
                $email_body .= "Name: $name\n";
                $email_body .= "Email: $email\n";
                $email_body .= "Phone: $phone\n";
                $email_body .= "Subject: $subject\n";
                $email_body .= "Message:\n$message\n";
                
                $headers = "From: noreply@poolpal.com\r\n";
                
                // Attempt to send email, but don't let it break the flow if it fails
                @mail($to, $email_subject, $email_body, $headers);
                
                // Redirect back to the contact page
                header("Location: contactus.php");
                exit();
            } else {
                // Set error message
                $_SESSION['contact_error'] = "Sorry, something went wrong. Please try again later.";
            }
        } catch (Exception $e) {
            // Set error message
            $_SESSION['contact_error'] = "Sorry, something went wrong. Please try again later.";
            
            // Log the error for debugging
            error_log("Contact form error: " . $e->getMessage());
        }
    } else {
        // Set error messages
        $_SESSION['contact_errors'] = $errors;
    }
    
    // Redirect back to contact page with error messages
    header("Location: contactus.php");
    exit();
} else {
    // If someone tries to access this file directly, redirect to the contact page
    header("Location: contactus.php");
    exit();
}
?> 