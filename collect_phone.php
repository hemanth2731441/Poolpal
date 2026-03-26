<?php
session_start();

// Only allow access if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone'])) {
    $phone = $_POST['phone'];
    $user_id = $_SESSION['user_id'];
    
    // Server-side validation: must be exactly 10 digits
    if (!preg_match('/^\d{10}$/', $phone)) {
        $error = "Phone number must be exactly 10 digits.";
    } else {
        // Update user's phone number
        $stmt = $conn->prepare("UPDATE users SET phone = ? WHERE id = ?");
        $stmt->bind_param("si", $phone, $user_id);
        
        try {
            if ($stmt->execute()) {
                // Update session
                $_SESSION['phone'] = $phone;
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Failed to update phone number. Please try again.";
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Duplicate entry
                $duplicate_phone_error = true;
            } else {
                $error = "Failed to update phone number. Please try again.";
            }
        }
    }
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email, phone, profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            text-align: center;
        }
        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        p {
            color: #666;
            margin-bottom: 25px;
        }
        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            display: block;
            border: 3px solid #ffbf00;
        }
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .input-field {
            position: relative;
        }
        .input-field input {
            width: 100%;
            padding: 12px;
            padding-left: 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .input-field i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #ffbf00;
        }
        button {
            background: #ffbf00;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        button:hover {
            background: #e6ac00;
        }
        .skip-link {
            display: inline-block;
            margin-top: 15px;
            color: #666;
            text-decoration: none;
        }
        .skip-link:hover {
            color: #ffbf00;
        }
        .error {
            color: #e74c3c;
            margin-top: 15px;
        }
    </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
    <div class="container">
        <img src="<?php echo !empty($user['profile_photo']) ? htmlspecialchars($user['profile_photo']) : 'images/default.jpg'; ?>" 
             alt="Profile" class="profile-img" onerror="this.src='images/default.jpg'">
        
        <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
        <p>Please provide your phone number to complete your profile.</p>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="input-group">
                <label for="phone">Phone Number</label>
                <div class="input-field">
                    <i class="fas fa-phone"></i>
                    <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" 
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                </div>
            </div>
            
            <button type="submit">Save & Continue</button>
        </form>
        
        <a href="dashboard.php" class="skip-link">Skip for now</a>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const phoneInput = document.getElementById('phone');
            const phoneValue = phoneInput.value.trim();
            // Client-side validation: exactly 10 digits
            if (!/^\d{10}$/.test(phoneValue)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Phone Number',
                    text: 'Phone number must be exactly 10 digits.',
                    confirmButtonColor: '#ffbf00'
                });
                return;
            }
        });
        // SweetAlert for duplicate phone error
        <?php if (isset($duplicate_phone_error) && $duplicate_phone_error): ?>
        Swal.fire({
            icon: 'error',
            title: 'Duplicate Phone Number',
            text: 'This phone number is already in use. Please use a different one.',
            confirmButtonColor: '#ffbf00'
        });
        <?php endif; ?>
    });
    </script>
</div></body>
</html> 