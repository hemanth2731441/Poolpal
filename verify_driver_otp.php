<?php include_once 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Verify OTP</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .separate {
      font-family: 'Poppins', sans-serif;
      background-color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
    }

    .lp-container {
      width: 350px;
      text-align: center;
    }

    .lp-container h2 {
      font-size: 22px;
      font-weight: 600;
    }

    .lp-input-group {
      text-align: left;
      margin-bottom: 15px;
    }

    .lp-input-group label {
      font-size: 14px;
      font-weight: 500;
    }

    .lp-input-field {
      position: relative;
    }

    .lp-input-field input {
      width: 85%;
      padding: 12px;
      background: #f9f9f9;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      outline: none;
      padding-right: 35px;
      margin-top: 5px;
    }

    .lp-input-field i {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #888;
    }

    .password-toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #888;
      cursor: pointer;
      z-index: 2;
    }

    .lp-login-btn {
      width: 100%;
      background: #ffbf00;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      margin-bottom: 15px;
    }
    
    .spinner {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(255,191,0,.3); 
      border-radius: 50%;
      border-top-color: #ffbf00;
      animation: spin 1s ease-in-out infinite; 
      vertical-align: middle;
      margin-right: 10px;
    }
    
    @keyframes spin { to { transform: rotate(360deg); } }
    
    #result {
      margin-top: 20px;
      padding: 10px;
      border-left: 4px solid #ffbf00;
      background: #f9f9f9;
    }
    
    .back-link {
      display: block;
      margin-top: 15px;
      color: #6C49F4;
      text-decoration: none;
      font-size: 14px;
    }
    
    .otp-container {
      display: flex;
      justify-content: space-between;
      margin: 20px 0;
    }
    
    .otp-input {
      width: 40px !important;
      height: 40px;
      text-align: center;
      font-size: 18px;
      padding: 0 !important;
    }
  </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">

<?php
// Get the email from the query string
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
if (empty($email)) {
    echo '<script>
        Swal.fire({
            icon: "error",
            title: "Email Required",
            text: "Email address is required to verify your OTP.",
            confirmButtonColor: "#ffbf00"
        }).then(() => {
            window.location.href = "forgot-password.php";
        });
    </script>';
}
?>

<div class="separate">
  <div class="lp-container">
    <h2>Verify OTP</h2>
    <p>Enter the verification code sent to your email</p>
    <br />
    
    <input type="hidden" id="email" value="<?php echo $email; ?>" />
    
    <div class="otp-container">
      <input type="text" class="otp-input" id="otp1" maxlength="1" />
      <input type="text" class="otp-input" id="otp2" maxlength="1" />
      <input type="text" class="otp-input" id="otp3" maxlength="1" />
      <input type="text" class="otp-input" id="otp4" maxlength="1" />
      <input type="text" class="otp-input" id="otp5" maxlength="1" />
      <input type="text" class="otp-input" id="otp6" maxlength="1" />
    </div>
    
    <div class="lp-input-group">
      <label>New Password</label>
      <div class="lp-input-field">
        <input type="password" id="new_password" placeholder="Enter new password" />
        <i class="password-toggle fas fa-eye-slash" id="toggleNewPassword"></i>
      </div>
    </div>
    
    <div class="lp-input-group">
      <label>Confirm Password</label>
      <div class="lp-input-field">
        <input type="password" id="confirm_password" placeholder="Confirm new password" />
        <i class="password-toggle fas fa-eye-slash" id="toggleConfirmPassword"></i>
      </div>
    </div>

    <button id="resetBtn" class="lp-login-btn">Reset Password</button>
    
    <div id="result"></div>
    
    <a href="forgot-password.php" class="back-link">Resend Code</a>
  </div>
</div>

<script>
$(document).ready(function() {
    // Auto-focus to next OTP input field
    $('.otp-input').keyup(function(e) {
        if ($(this).val().length === 1) {
            $(this).next('.otp-input').focus();
        } else if (e.keyCode === 8 && $(this).val().length === 0) {
            $(this).prev('.otp-input').focus();
        }
    });
    
    // Auto-focus first OTP field
    $('#otp1').focus();
    
    // Toggle password visibility for new password
    $('#toggleNewPassword').click(function() {
        const passwordField = $('#new_password');
        const passwordToggleIcon = $(this);
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            passwordToggleIcon.removeClass('fa-eye-slash');
            passwordToggleIcon.addClass('fa-eye');
        } else {
            passwordField.attr('type', 'password');
            passwordToggleIcon.removeClass('fa-eye');
            passwordToggleIcon.addClass('fa-eye-slash');
        }
    });
    
    // Toggle password visibility for confirm password
    $('#toggleConfirmPassword').click(function() {
        const passwordField = $('#confirm_password');
        const passwordToggleIcon = $(this);
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            passwordToggleIcon.removeClass('fa-eye-slash');
            passwordToggleIcon.addClass('fa-eye');
        } else {
            passwordField.attr('type', 'password');
            passwordToggleIcon.removeClass('fa-eye');
            passwordToggleIcon.addClass('fa-eye-slash');
        }
    });
    
    $('#resetBtn').click(function() {
        const email = $('#email').val();
        const resetBtn = $(this);
        
        // Combine OTP digits
        const otp = $('#otp1').val() + $('#otp2').val() + $('#otp3').val() + 
                   $('#otp4').val() + $('#otp5').val() + $('#otp6').val();
        
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();
        
        // Validate inputs
        if (!email) {
            $('#result').html('<span style="color:red;">Email is required.</span>');
            return;
        }
        
        if (otp.length !== 6) {
            $('#result').html('<span style="color:red;">Please enter the complete 6-digit verification code.</span>');
            return;
        }
        
        if (!newPassword) {
            $('#result').html('<span style="color:red;">Please enter a new password.</span>');
            return;
        }
        
        if (newPassword !== confirmPassword) {
            $('#result').html('<span style="color:red;">Passwords do not match.</span>');
            return;
        }
        
        // Show loading indicator
        resetBtn.prop('disabled', true);
        resetBtn.html('<div class="spinner"></div> Processing...');
        $('#result').html('');
        
        $.ajax({
            url: 'reset_driver_password.php',
            type: 'POST',
            data: { email: email, otp: otp, new_password: newPassword },
            dataType: 'json',
            cache: false,
            timeout: 30000, // 30 second timeout
            success: function(response) {
                if(response && response.success === true) {
                    $('#result').html('<span style="color:green;">' + response.message + '</span>');
                    
                    // Show success alert
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Reset Successful',
                        text: 'Your password has been reset successfully. You can now login with your new password.',
                        confirmButtonColor: '#ffbf00'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect to login page
                            window.location.href = 'driver_login.php';
                        }
                    });
                } else if(response && response.message) {
                    $('#result').html('<span style="color:red;">' + response.message + '</span>');
                } else {
                    $('#result').html('<span style="color:red;">An unexpected response was received. Please try again.</span>');
                    console.error('Unexpected response format:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.log('Response text:', xhr.responseText);
                
                // Try to parse the response if it's JSON
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if(errorResponse && errorResponse.message) {
                        $('#result').html('<span style="color:red;">' + errorResponse.message + '</span>');
                        return;
                    }
                } catch(e) {
                    console.error('Error parsing JSON response:', e);
                }
                
                $('#result').html('<span style="color:red;">Something went wrong. Please try again later.</span>');
            },
            complete: function() {
                // Reset button state
                resetBtn.prop('disabled', false);
                resetBtn.html('Reset Password');
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>
</div></body>
</html>