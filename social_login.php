<?php
session_start();
include 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log incoming request
error_log("Received social login request: " . file_get_contents('php://input'));

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$provider = $data['provider'];
$email = $data['email'];
$name = $data['name'];
$picture = $data['picture'];
$social_id = $data['id'];
$phone = isset($data['phone']) ? $data['phone'] : null;
$signup_mode = isset($data['signup_mode']) ? $data['signup_mode'] : false;

// Log the extracted data
error_log("Extracted data - Email: $email, Name: $name, Picture URL: $picture, Provider: $provider, Social ID: $social_id, Phone: " . ($phone ?: "NULL"));

// Validate required fields
if (!$email || !$name || !$social_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required information']);
    exit;
}

try {
    // Check if user exists with this email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        error_log("Found existing user: " . print_r($user, true));
        
        // If signup_mode is true and user already exists, return error
        if ($signup_mode) {
            echo json_encode(['success' => false, 'message' => 'A user with this email already exists. Please log in instead.']);
            exit;
        }
        
        // Check if this user has a password set (manual account) but no social_provider
        if (!empty($user['password']) && empty($user['social_provider'])) {
            // This is a manual account - inform user they should use password login
            echo json_encode([
                'success' => false, 
                'message' => 'This email is already registered with a password. Please use the regular login form.'
            ]);
            exit;
        }
        
        // Set user_id in session
        $_SESSION['user_id'] = $user['id'];
        
        // Log successful login for existing user
        error_log("Social login successful for existing user: $email (ID: {$user['id']})");
        
        // Check if this is an existing social user
        if ($user['social_provider'] == $provider && $user['social_id'] == $social_id) {
            // Try to create a copy of the profile photo in our uploads folder
            $local_profile_photo = null;
            if (!empty($picture) && filter_var($picture, FILTER_VALIDATE_URL)) {
                try {
                    $uploads_dir = 'uploads/';
                    if (!file_exists($uploads_dir)) {
                        mkdir($uploads_dir, 0755, true);
                    }
                    
                    $picture_data = @file_get_contents($picture);
                    if ($picture_data) {
                        $filename = 'profile_' . time() . '_' . uniqid() . '.jpg';
                        $file_path = $uploads_dir . $filename;
                        if (file_put_contents($file_path, $picture_data)) {
                            $local_profile_photo = $file_path;
                            $picture = $file_path; // Update picture to use local file
                            error_log("Saved remote profile photo to local path: $local_profile_photo");
                        } else {
                            error_log("Failed to write profile photo to: $file_path");
                        }
                    } else {
                        error_log("Failed to get remote profile image content from: $picture");
                    }
                } catch (Exception $e) {
                    error_log("Error saving profile photo: " . $e->getMessage());
                }
            }
            
            // User exists with matching social credentials - update profile photo and phone if available
            if ($phone) {
                $stmt = $conn->prepare("UPDATE users SET 
                    profile_photo = ?, 
                    phone = ?,
                    created_at = NOW() 
                    WHERE email = ?");
                $stmt->bind_param("sss", $picture, $phone, $email);
            } else {
                $stmt = $conn->prepare("UPDATE users SET 
                    profile_photo = ?, 
                    created_at = NOW() 
                    WHERE email = ?");
                $stmt->bind_param("ss", $picture, $email);
            }
            $stmt->execute();
            error_log("Updated existing user with new profile photo: $picture");
        } else if ($user['social_provider'] === null && $user['social_id'] === null) {
            // Existing user with no social login - link accounts
            if ($phone) {
                $stmt = $conn->prepare("UPDATE users SET 
                    social_provider = ?, 
                    social_id = ?, 
                    profile_photo = ?,
                    phone = ?,
                    created_at = NOW()
                    WHERE email = ?");
                $stmt->bind_param("sssss", $provider, $social_id, $picture, $phone, $email);
            } else {
                $stmt = $conn->prepare("UPDATE users SET 
                    social_provider = ?, 
                    social_id = ?, 
                    profile_photo = ?,
                    created_at = NOW()
                    WHERE email = ?");
                $stmt->bind_param("ssss", $provider, $social_id, $picture, $email);
            }
            $stmt->execute();
            error_log("Linked social account to existing user. Profile photo: $picture");
        } else {
            // User exists with different social credentials
            // We'll allow login but log this unusual activity
            error_log("Social login mismatch: Email $email already exists with different social credentials");
            
            // Update the social credentials for this account
            if ($phone) {
                $stmt = $conn->prepare("UPDATE users SET 
                    social_provider = ?, 
                    social_id = ?, 
                    profile_photo = ?,
                    phone = ?,
                    created_at = NOW() 
                    WHERE email = ?");
                $stmt->bind_param("sssss", $provider, $social_id, $picture, $phone, $email);
            } else {
                $stmt = $conn->prepare("UPDATE users SET 
                    social_provider = ?, 
                    social_id = ?, 
                    profile_photo = ?,
                    created_at = NOW() 
                    WHERE email = ?");
                $stmt->bind_param("ssss", $provider, $social_id, $picture, $email);
            }
            $stmt->execute();
            error_log("Updated existing user with different social credentials. Profile photo: $picture");
        }
    } else {
        // Create new user
        error_log("Creating new user with email: $email, Picture: $picture");
        if ($phone) {
            $stmt = $conn->prepare("INSERT INTO users (
                full_name, 
                email, 
                phone,
                social_provider, 
                social_id, 
                profile_photo, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssss", $name, $email, $phone, $provider, $social_id, $picture);
        } else {
            // Try to create a copy of the profile photo in our uploads folder
            $local_profile_photo = null;
            if (!empty($picture) && filter_var($picture, FILTER_VALIDATE_URL)) {
                try {
                    $uploads_dir = 'uploads/';
                    if (!file_exists($uploads_dir)) {
                        mkdir($uploads_dir, 0755, true);
                    }
                    
                    $picture_data = @file_get_contents($picture);
                    if ($picture_data) {
                        $filename = 'profile_' . time() . '_' . uniqid() . '.jpg';
                        $file_path = $uploads_dir . $filename;
                        if (file_put_contents($file_path, $picture_data)) {
                            $local_profile_photo = $file_path;
                            $picture = $file_path; // Update picture to use local file
                            error_log("Saved remote profile photo to local path: $local_profile_photo");
                        } else {
                            error_log("Failed to write profile photo to: $file_path");
                        }
                    } else {
                        error_log("Failed to get remote profile image content from: $picture");
                    }
                } catch (Exception $e) {
                    error_log("Error saving profile photo: " . $e->getMessage());
                }
            }
            
            // Use local photo if available, otherwise use original URL
            $profile_photo_to_save = $local_profile_photo ?: $picture;
            
            $stmt = $conn->prepare("INSERT INTO users (
                full_name, 
                email, 
                social_provider, 
                social_id, 
                profile_photo, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssss", $name, $email, $provider, $social_id, $profile_photo_to_save);
        }
        $stmt->execute();
        
        if ($stmt->error) {
            error_log("SQL Error: " . $stmt->error);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
            exit;
        }
        
        // Get the new user's ID
        $user_id = $conn->insert_id;
        
        // Set user_id in session
        $_SESSION['user_id'] = $user_id;
        
        // Log successful creation of new user
        error_log("Social signup successful: $email (New ID: $user_id, Provider: $provider)");
    }

    // Verify the user record was updated
    $check_stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $updated_user = $check_result->fetch_assoc();
    error_log("User record after updates: " . print_r($updated_user, true));
    
    // Set session variables
    $_SESSION['user_id'] = isset($user_id) ? $user_id : $user['id']; // Make sure user_id is always set
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    $_SESSION['full_name'] = $name; // Add this to match regular login
    $_SESSION['user_picture'] = $picture;
    $_SESSION['profile_photo'] = $picture; // Add this to match regular login
    $_SESSION['social_provider'] = $provider;
    
    // Add phone to session if available, otherwise get it from the user record
    if ($phone) {
        $_SESSION['phone'] = $phone;
    } else if (isset($updated_user['phone']) && !empty($updated_user['phone'])) {
        $_SESSION['phone'] = $updated_user['phone'];
    }
    
    // Force session write
    session_write_close();
    session_start();
    
    // Save all session variables for debugging
    error_log("SESSION after social login: " . print_r($_SESSION, true));
    
    // Check if phone number is missing
    $redirect_url = 'dashboard.php';
    if (!$phone && (!isset($updated_user['phone']) || empty($updated_user['phone']))) {
        $redirect_url = 'collect_phone.php'; 
        error_log("Phone missing, will redirect to collect_phone.php");
    }

    echo json_encode([
        'success' => true, 
        'message' => $signup_mode ? 'Sign up successful!' : 'Login successful',
        'redirect' => $redirect_url,
        'user_data' => [
            'id' => isset($user_id) ? $user_id : $user['id'],
            'email' => $email,
            'name' => $name,
            'profile_photo' => $picture
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Social login error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'An error occurred during ' . ($signup_mode ? 'sign up' : 'login')]);
}
?> 