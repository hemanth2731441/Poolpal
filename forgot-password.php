<?php include_once 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password</title>
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
      cursor: pointer;
    }

    .lp-input-field i:hover {
      color: #ffbf00;
    }

    /* Verification code input styling */
    .verification-code-container {
      display: flex;
      gap: 8px;
      justify-content: center;
      margin-top: 5px;
    }

    .verification-code-input {
      width: 40px !important;
      height: 40px;
      text-align: center;
      font-size: 18px !important;
      padding: 0 !important;
    }

    @media (max-width: 480px) {
      .separate {
        padding: 20px;
      }

      .lp-container {
        width: 100%;
        max-width: 350px;
      }

      .verification-code-input {
        width: 35px !important;
        height: 35px;
      }
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

    #verificationForm {
      display: none;
    }
  </style>
  <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">

<div class="separate">
  <div class="lp-container">
    <div id="requestForm">
      <h2>Forgot Password</h2>
      <p>Enter your email address and we'll send you a verification code to reset your password.</p>
      <br />
      
      <div class="lp-input-group">
        <label>Email</label>
        <div class="lp-input-field">
          <input type="email" id="reset_email" placeholder="Enter your email address" />
          <i class="fas fa-envelope"></i>
        </div>
      </div>

      <button id="resetBtn" class="lp-login-btn">Send Reset Code</button>
    </div>

    <div id="verificationForm">
      <h2>Verify Code</h2>
      <p>Enter the verification code sent to your email and your new password.</p>
      <br />
      
      <div class="lp-input-group">
        <label>Verification Code</label>
        <div class="verification-code-container">
          <input type="text" class="verification-code-input" maxlength="1" data-index="1">
          <input type="text" class="verification-code-input" maxlength="1" data-index="2">
          <input type="text" class="verification-code-input" maxlength="1" data-index="3">
          <input type="text" class="verification-code-input" maxlength="1" data-index="4">
          <input type="text" class="verification-code-input" maxlength="1" data-index="5">
          <input type="text" class="verification-code-input" maxlength="1" data-index="6">
        </div>
        <input type="hidden" id="otp_code">
      </div>

      <div class="lp-input-group">
        <label>New Password</label>
        <div class="lp-input-field">
          <input type="password" id="new_password" placeholder="Enter new password" />
          <i class="fas fa-eye-slash toggle-password" data-target="new_password"></i>
        </div>
      </div>

      <div class="lp-input-group">
        <label>Confirm Password</label>
        <div class="lp-input-field">
          <input type="password" id="confirm_password" placeholder="Confirm new password" />
          <i class="fas fa-eye-slash toggle-password" data-target="confirm_password"></i>
        </div>
      </div>

      <button id="verifyBtn" class="lp-login-btn">Reset Password</button>
    </div>
    
    <div id="result"></div>
    
    <a href="login.php" class="back-link">Back to Login</a>
  </div>
</div>

<script>
$(document).ready(function() {
    let userEmail = '';

    $('#resetBtn').click(function() {
        const email = $('#reset_email').val().trim();
        const resetBtn = $(this);
        
        // Validate email
        if(!email) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please enter your email address',
                confirmButtonColor: '#ffbf00'
            });
            return;
        }

        if(!isValidEmail(email)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please enter a valid email address',
                confirmButtonColor: '#ffbf00'
            });
            return;
        }
        
        // Show loading indicator
        resetBtn.prop('disabled', true);
        resetBtn.html('<div class="spinner"></div> Processing...');
        $('#result').html('');
        
        $.ajax({
            url: 'forgot_password.php',
            type: 'POST',
            data: { reset_email: email },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    userEmail = email;
                    $('#requestForm').hide();
                    $('#verificationForm').show();
                    Swal.fire({
                        icon: 'success',
                        title: 'Code Sent',
                        text: 'A verification code has been sent to your email.',
                        confirmButtonColor: '#ffbf00'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to send verification code',
                        confirmButtonColor: '#ffbf00'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Something went wrong. Please try again later.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if(response.message) {
                        errorMessage = response.message;
                    }
                } catch(e) {}
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonColor: '#ffbf00'
                });
            },
            complete: function() {
                resetBtn.prop('disabled', false);
                resetBtn.html('Send Reset Code');
            }
        });
    });

    $('#verifyBtn').click(function() {
        const otp = $('#otp_code').val().trim();
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();
        const verifyBtn = $(this);

        // Validate inputs
        if(!otp || !newPassword || !confirmPassword) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please fill in all fields',
                confirmButtonColor: '#ffbf00'
            });
            return;
        }

        if(otp.length !== 6) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please enter a valid 6-digit code',
                confirmButtonColor: '#ffbf00'
            });
            return;
        }

        if(newPassword !== confirmPassword) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Passwords do not match',
                confirmButtonColor: '#ffbf00'
            });
            return;
        }

        // Show loading indicator
        verifyBtn.prop('disabled', true);
        verifyBtn.html('<div class="spinner"></div> Processing...');

        $.ajax({
            url: 'verify_reset_otp.php',
            type: 'POST',
            data: {
                email: userEmail,
                otp: otp,
                new_password: newPassword
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Your password has been reset successfully.',
                        confirmButtonColor: '#ffbf00'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to reset password',
                        confirmButtonColor: '#ffbf00'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Something went wrong. Please try again later.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if(response.message) {
                        errorMessage = response.message;
                    }
                } catch(e) {}
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonColor: '#ffbf00'
                });
            },
            complete: function() {
                verifyBtn.prop('disabled', false);
                verifyBtn.html('Reset Password');
            }
        });
    });

    // Email validation function
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Password visibility toggle
    $('.toggle-password').click(function() {
        const targetId = $(this).data('target');
        const input = $(`#${targetId}`);
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).toggleClass('fa-eye fa-eye-slash');
    });

    // Verification code input handling
    $('.verification-code-container input').on('input', function() {
        const value = $(this).val();
        const index = parseInt($(this).data('index'));
        
        if (value.length === 1) {
            if (index < 6) {
                $(`.verification-code-input[data-index="${index + 1}"]`).focus();
            }
        }
        updateOtpCode();
    });

    $('.verification-code-container input').on('keydown', function(e) {
        const index = parseInt($(this).data('index'));
        
        if (e.key === 'Backspace' && !$(this).val() && index > 1) {
            e.preventDefault();
            $(`.verification-code-input[data-index="${index - 1}"]`).focus().val('');
            updateOtpCode();
        }
    });

    function updateOtpCode() {
        const code = Array.from($('.verification-code-input'))
            .map(input => $(input).val() || '')
            .join('');
        $('#otp_code').val(code);
    }
});
</script>

<?php include 'footer.php'; ?>
</div></body>
</html> 