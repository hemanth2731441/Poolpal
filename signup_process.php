<?php
session_start();
include 'db.php';

function sendResponse($status, $message, $isAjax = false) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $status === 'success',
            'message' => $message
        ]);
    } else {
        header("Location: signup.php?status=$status&msg=" . urlencode($message));
    }
    exit;
}

// Check if it's an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Check if terms were accepted
if (!isset($_POST['terms_accepted'])) {
    sendResponse('error', 'You must accept the terms and conditions', $isAjax);
}

// Get and sanitize form data
$full_name = trim($_POST['full_name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone = trim($_POST['phone'] ?? '');
$raw_password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse('error', 'Invalid email format', $isAjax);
}

// Check password match
if ($raw_password !== $confirm_password) {
    sendResponse('error', 'Passwords do not match', $isAjax);
}

// Hash the password
$password = password_hash($raw_password, PASSWORD_DEFAULT);

// First check email
$check_email_sql = "SELECT id, social_provider FROM users WHERE email = ?";
$check_email_stmt = $conn->prepare($check_email_sql);
$check_email_stmt->bind_param("s", $email);
$check_email_stmt->execute();
$email_result = $check_email_stmt->get_result();

if ($email_result->num_rows > 0) {
    $user = $email_result->fetch_assoc();
    if (!empty($user['social_provider'])) {
        sendResponse('error', "This email is already registered with {$user['social_provider']} login. Please use the {$user['social_provider']} login button", $isAjax);
    } else {
        sendResponse('error', 'This email is already registered. Please use a different email or login', $isAjax);
    }
}
$check_email_stmt->close();

// Then check phone
$check_phone_sql = "SELECT id FROM users WHERE phone = ?";
$check_phone_stmt = $conn->prepare($check_phone_sql);
$check_phone_stmt->bind_param("s", $phone);
$check_phone_stmt->execute();
$phone_result = $check_phone_stmt->get_result();

if ($phone_result->num_rows > 0) {
    sendResponse('error', 'This phone number is already registered. Please use a different phone number', $isAjax);
}
$check_phone_stmt->close();

// Profile photo upload
$upload_path = "";
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        sendResponse('error', 'Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed', $isAjax);
    }

    // Create uploads directory if it doesn't exist
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    $new_name = 'profile_' . time() . '_' . uniqid() . '.' . $ext;
    $upload_path = 'uploads/' . $new_name;
    
    if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
        sendResponse('error', 'Failed to upload profile photo. Please try again', $isAjax);
    }
}

// Insert user
try {
    $sql = "INSERT INTO users (full_name, email, phone, password, profile_photo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $full_name, $email, $phone, $password, $upload_path);

    if ($stmt->execute()) {
        // Set session variables for the new user
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_name'] = $full_name;
        $_SESSION['user_email'] = $email;
        
        sendResponse('success', 'Your account has been created successfully! Please log in.', $isAjax);
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    sendResponse('error', 'Registration failed: ' . $e->getMessage(), $isAjax);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>

