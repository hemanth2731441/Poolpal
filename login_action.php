<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember_me']) ? true : false;

    // First check for standard login
    $stmt = $conn->prepare("SELECT id, full_name, password, profile_photo, phone, social_provider, social_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if this is a social login account without a password
        if ((empty($user['password']) || $user['password'] === null) && 
            (!empty($user['social_provider']) && !empty($user['social_id']))) {
            error_log("Failed login attempt - user trying to use password for social account: $email");
            
            // Output proper HTML with SweetAlert
            echo "<!DOCTYPE html>
<html>
<head>
    <title>Login Redirect</title>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <link rel='stylesheet' href='css/animated-bg.css' />
</head>
<body class='animated-background-wrapper'>
<?php include_once 'includes/animated-background.php'; ?>
<div class='main-content'>
        <script>
            window.onload = function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Social Login Required',
                    text: 'This email is registered with " . $user['social_provider'] . " login. Please use the " . $user['social_provider'] . " login button instead.',
                    confirmButtonColor: '#ffbf00'
                }).then(function() {
                    " . (isset($_POST['redirect']) && isset($_POST['id']) ? 
                         "window.location.href = 'login.php?redirect=" . urlencode($_POST['redirect']) . "&id=" . urlencode($_POST['id']) . "';" : 
                         "window.location.href = 'login.php';") . "
                });
            };
            // Trigger immediately if page already loaded
            if (document.readyState === 'complete') {
                window.onload();
            }
        </script>
</div></body>
</html>";
            exit;
        }

        // Check if the password is hashed (starts with $2y$) and use password_verify
        // Otherwise fall back to direct comparison for backward compatibility
        if ((substr($user['password'], 0, 4) === '$2y$' && password_verify($password, $user['password'])) ||
            $password === $user['password']) {
            // Set all required session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['full_name'] = $user['full_name']; 
            $_SESSION['profile_photo'] = $user['profile_photo'];
            $_SESSION['phone'] = $user['phone'];
            
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $user_id = $user['id'];
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                // Clean up any existing tokens for this user
                $cleanup = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
                $cleanup->bind_param("i", $user_id);
                $cleanup->execute();
                
                // Create new token
                $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user_id, $token, $expires);
                $stmt->execute();
                
                setcookie('remember_user', $user_id, time() + (86400 * 30), "/");
                setcookie('remember_token', $token, time() + (86400 * 30), "/");
            }

            // Log successful login
            error_log("User logged in successfully: $email");
            
            // Check if there was a redirect request
            if (isset($_POST['redirect']) && isset($_POST['id'])) {
                $redirect = $_POST['redirect'];
                $id = $_POST['id'];
                header("Location: $redirect?id=$id");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            error_log("Failed login attempt - invalid password for: $email");
            
            // Preserve redirect parameters if they exist
            $redirect_params = '';
            if (isset($_POST['redirect']) && isset($_POST['id'])) {
                $redirect = urlencode($_POST['redirect']);
                $id = urlencode($_POST['id']);
                $redirect_params = "&redirect=$redirect&id=$id";
            }
            
            header("Location: login.php?error=invalid_password$redirect_params");
            exit;
        }
    } else {
        // Check if the user exists with social login
        $social_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND (social_provider IS NOT NULL OR social_id IS NOT NULL)");
        $social_check->bind_param("s", $email);
        $social_check->execute();
        $social_result = $social_check->get_result();
        
        if ($social_result->num_rows > 0) {
            // User exists but only with social login
            error_log("Failed login attempt - social user trying standard login: $email");
            
            // Get the social provider
            $social_provider_stmt = $conn->prepare("SELECT social_provider FROM users WHERE email = ?");
            $social_provider_stmt->bind_param("s", $email);
            $social_provider_stmt->execute();
            $provider_result = $social_provider_stmt->get_result();
            $provider_row = $provider_result->fetch_assoc();
            $provider = $provider_row['social_provider'];
            
            // Output proper HTML with SweetAlert
            echo "<!DOCTYPE html>
<html>
<head>
    <title>Login Redirect</title>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <link rel='stylesheet' href='css/animated-bg.css' />
</head>
<body class='animated-background-wrapper'>
<?php include_once 'includes/animated-background.php'; ?>
<div class='main-content'>
        <script>
            window.onload = function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Social Login Required',
                    text: 'This email is registered with " . $provider . " login. Please use the " . $provider . " login button instead.',
                    confirmButtonColor: '#ffbf00'
                }).then(function() {
                    " . (isset($_POST['redirect']) && isset($_POST['id']) ? 
                         "window.location.href = 'login.php?redirect=" . urlencode($_POST['redirect']) . "&id=" . urlencode($_POST['id']) . "';" : 
                         "window.location.href = 'login.php';") . "
                });
            };
            // Trigger immediately if page already loaded
            if (document.readyState === 'complete') {
                window.onload();
            }
        </script>
</div></body>
</html>";
            exit;
        } else {
            error_log("Failed login attempt - user not found: $email");
            
            // Preserve redirect parameters if they exist
            $redirect_params = '';
            if (isset($_POST['redirect']) && isset($_POST['id'])) {
                $redirect = urlencode($_POST['redirect']);
                $id = urlencode($_POST['id']);
                $redirect_params = "&redirect=$redirect&id=$id";
            }
            
            header("Location: login.php?error=user_not_found$redirect_params");
            exit;
        }
    }
}
?>
