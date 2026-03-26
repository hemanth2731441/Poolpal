<?php
ob_start();
include 'header.php';
include 'db.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT password FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $currentPassword = $row['password'];
} else {
    header("Location: login.php");
    exit;
}

if (isset($_POST['save_changes'])) {
    $updateFields = [];
    $messages = [];

    // Update password if new password fields are filled
    if (!empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $newPassword = mysqli_real_escape_string($conn, $_POST['new_password']);
        $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirm_password']);
        if ($newPassword === $confirmPassword) {
            $updateFields[] = "password = '$newPassword'";
        } else {
            header("Location: edit-accountu.php?mismatch=1");
            exit;
        }
    }

    // Perform update if at least one field is to be updated
    if (!empty($updateFields)) {
        $updateQuery = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = '$user_id'";
        if (mysqli_query($conn, $updateQuery)) {
            header("Location: edit-accountu.php?success=1");
            exit;
        } else {
            header("Location: edit-accountu.php?error=1");
            exit;
        }
    } else {
        header("Location: edit-accountu.php?nochange=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Account | PoolPal</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">

<div class="page-container">
    <div class="edit-account-card animate__animated animate__fadeIn">
        <div class="card-header">
            <div class="header-icon">
                <i class="fas fa-user-edit"></i>
            </div>
            <h1>Edit Account</h1>
            <p class="subtext">Update your account details below</p>
        </div>

        <div class="section-divider">
            <span><i class="fas fa-lock"></i> Security</span>
        </div>

        <form method="POST" action="" id="editAccountForm">
            <div class="form-section password-section">
                <div class="form-group">
                    <label for="current_password">
                        <i class="fas fa-key"></i>
                        Current Password
                    </label>
                    <div class="input-wrapper">
                        <input type="password" id="current_password" name="current_password" value="<?php echo htmlspecialchars($currentPassword); ?>" readonly>
                        <button type="button" class="toggle-password" data-target="current_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_password">
                        <i class="fas fa-unlock"></i>
                        New Password
                    </label>
                    <div class="input-wrapper">
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
                        <button type="button" class="toggle-password" data-target="new_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength-meter">
                        <div class="strength-bar"></div>
                    </div>
                    <small class="password-hint">Use at least 8 characters with letters, numbers and symbols</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-check-circle"></i>
                        Confirm New Password
                    </label>
                    <div class="input-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter new password">
                        <button type="button" class="toggle-password" data-target="confirm_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="match-status"></small>
                </div>
            </div>

            <div class="button-group">
                <a href="profile.php" class="btn cancel-btn">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" name="save_changes" class="btn save-btn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Sweetalert JS Alerts -->
<?php if (isset($_GET['success'])): ?>
<script>
Swal.fire({
  icon: 'success',
  title: 'Success!',
  text: 'Your password has been updated successfully.',
  confirmButtonColor: '#ffbf00',
  timer: 3000,
  timerProgressBar: true
});
</script>
<?php elseif (isset($_GET['error'])): ?>
<script>
Swal.fire({
  icon: 'error',
  title: 'Error!',
  text: 'Failed to update details. Please try again.',
  confirmButtonColor: '#ffbf00'
});
</script>
<?php elseif (isset($_GET['mismatch'])): ?>
<script>
Swal.fire({
  icon: 'warning',
  title: 'Password Mismatch',
  text: 'New Password and Confirm Password do not match.',
  confirmButtonColor: '#ffbf00'
});
</script>
<?php elseif (isset($_GET['nochange'])): ?>
<script>
Swal.fire({
  icon: 'info',
  title: 'No Changes',
  text: 'No changes were made to your account.',
  confirmButtonColor: '#ffbf00'
});
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            
            if (input.type === 'password') {
                input.type = 'text';
                this.querySelector('i').classList.remove('fa-eye');
                this.querySelector('i').classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                this.querySelector('i').classList.remove('fa-eye-slash');
                this.querySelector('i').classList.add('fa-eye');
            }
        });
    });

    // Password strength meter
    const newPassword = document.getElementById('new_password');
    const strengthBar = document.querySelector('.strength-bar');
    
    newPassword.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/)) strength += 25;
        if (password.match(/[^A-Za-z0-9]/)) strength += 25;
        
        strengthBar.style.width = strength + '%';
        
        if (strength <= 25) {
            strengthBar.style.backgroundColor = '#ff4d4d';
        } else if (strength <= 50) {
            strengthBar.style.backgroundColor = '#ffa64d';
        } else if (strength <= 75) {
            strengthBar.style.backgroundColor = '#ffcc00';
        } else {
            strengthBar.style.backgroundColor = '#66cc66';
        }
    });

    // Password match validation
    const confirmPassword = document.getElementById('confirm_password');
    const matchStatus = document.querySelector('.match-status');
    
    confirmPassword.addEventListener('input', function() {
        if (this.value === '') {
            matchStatus.textContent = '';
            return;
        }
        
        if (this.value === newPassword.value) {
            matchStatus.textContent = 'Passwords match';
            matchStatus.style.color = '#66cc66';
        } else {
            matchStatus.textContent = 'Passwords do not match';
            matchStatus.style.color = '#ff4d4d';
        }
    });

    // Form animations
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach((group, index) => {
        group.classList.add('animate__animated', 'animate__fadeInUp');
        group.style.animationDelay = (index * 0.1) + 's';
    });
});
</script>

<style>
:root {
    --primary-color: #ffbf00;
    --primary-light: #ffe066;
    --primary-dark: #e6ac00;
    --secondary-color: #333;
    --text-color: #333;
    --text-light: #777;
    --white: #fff;
    --gray-light: #f8f9fa;
    --gray: #e9ecef;
    --success: #66cc66;
    --warning: #ffcc00;
    --danger: #ff4d4d;
    --shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    --border-radius: 12px;
    --transition: all 0.3s ease;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background-color: #f5f5f5;
    background-image: linear-gradient(135deg, #f5f7fa 0%, #f7f9fc 100%);
    min-height: 100vh;
    color: var(--text-color);
    line-height: 1.6;
}

.page-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
    padding: 40px 15px;
}

.edit-account-card {
    width: 100%;
    max-width: 650px;
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
    transform: translateY(0);
}

.edit-account-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.card-header {
    padding: 30px;
    text-align: center;
    position: relative;
    background: linear-gradient(45deg, var(--primary-dark), var(--primary-color));
    color: var(--white);
    border-radius: var(--border-radius) var(--border-radius) 0 0;
}

.header-icon {
    margin-bottom: 15px;
}

.header-icon i {
    font-size: 48px;
    opacity: 0.9;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

.card-header h1 {
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 5px;
    letter-spacing: 0.5px;
}

.subtext {
    font-size: 16px;
    opacity: 0.9;
    font-weight: 300;
}

.section-divider {
    padding: 15px 30px;
    background-color: var(--gray-light);
    border-bottom: 1px solid var(--gray);
    font-weight: 600;
    color: var(--secondary-color);
}

.section-divider span {
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-divider i {
    color: var(--primary-color);
}

.form-section {
    padding: 30px;
}

.form-group {
    margin-bottom: 25px;
    opacity: 0;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group label i {
    color: var(--primary-color);
}

.input-wrapper {
    position: relative;
    transition: var(--transition);
}

.input-wrapper input {
    width: 100%;
    padding: 14px 45px 14px 15px;
    border: 2px solid var(--gray);
    background-color: var(--white);
    border-radius: 8px;
    font-size: 15px;
    color: var(--text-color);
    transition: var(--transition);
    font-family: 'Poppins', sans-serif;
}

.input-wrapper input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(255, 191, 0, 0.2);
    outline: none;
}

.input-wrapper input::placeholder {
    color: #aaa;
}

.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    font-size: 16px;
    transition: var(--transition);
    padding: 5px;
}

.toggle-password:hover {
    color: var(--primary-color);
}

.password-strength-meter {
    height: 5px;
    background-color: var(--gray);
    border-radius: 10px;
    margin-top: 10px;
    overflow: hidden;
}

.strength-bar {
    height: 100%;
    width: 0;
    background-color: var(--primary-color);
    transition: width 0.3s ease, background-color 0.3s ease;
}

.password-hint, .match-status {
    display: block;
    font-size: 12px;
    color: var(--text-light);
    margin-top: 5px;
}

.button-group {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 20px 30px 30px;
}

.btn {
    flex: 1;
    padding: 14px 10px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    border: none;
    text-decoration: none;
}

.save-btn {
    background-color: var(--primary-color);
    color: var(--white);
    box-shadow: 0 4px 10px rgba(255, 191, 0, 0.3);
}

.save-btn:hover {
    background-color: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(255, 191, 0, 0.4);
}

.save-btn:active {
    transform: translateY(0);
}

.cancel-btn {
    background-color: var(--gray-light);
    color: var(--text-color);
}

.cancel-btn:hover {
    background-color: var(--gray);
}

/* Responsive Design */
@media (max-width: 768px) {
    .edit-account-card {
        border-radius: 0;
    }
    
    .form-section {
        padding: 20px;
    }
    
    .button-group {
        padding: 15px 20px 25px;
        flex-direction: column-reverse;
    }
    
    .btn {
        width: 100%;
    }
    
    .card-header {
        padding: 25px 20px;
    }
    
    .header-icon i {
        font-size: 40px;
    }
    
    .card-header h1 {
        font-size: 24px;
    }
}

@media (max-width: 480px) {
    .form-section {
        padding: 15px;
    }
    
    .input-wrapper input {
        padding: 12px 40px 12px 12px;
        font-size: 14px;
    }
    
    .form-group label {
        font-size: 13px;
    }
    
    .card-header {
        padding: 20px 15px;
    }
    
    .header-icon i {
        font-size: 36px;
    }
    
    .card-header h1 {
        font-size: 22px;
    }
    
    .subtext {
        font-size: 14px;
    }
}
</style>

<?php include 'footer.php'; ?>
</div></body>
</html>
<?php
ob_end_flush();
?>
