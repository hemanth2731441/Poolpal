<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Check if user is already logged in from social login
if (isset($_SESSION['user_id'])) {
  // Redirect to dashboard if already logged in
  header("Location: dashboard.php");
  exit;
}


// Now include the header after handling any redirects
include_once 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Page</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Add Google OAuth Client -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>
  <!-- Add Facebook SDK -->
  <script>
    // Load Facebook SDK directly in the head for faster loading
    window.fbAsyncInit = function() {
      console.log('FB SDK async init called');
      FB.init({
        appId: '1344680903484668',
        cookie: true,
        xfbml: true,
        version: 'v18.0'
      });
      console.log('Facebook SDK initialized in fbAsyncInit');
      
      // Ensure the button is working after FB is loaded
      setupFBButton();
    };
    
    function setupFBButton() {
      console.log('Setting up Facebook button');
      const fbButtons = document.querySelectorAll('.lp-facebook');
      fbButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          console.log('Facebook button clicked');
          handleFacebookLogin();
        });
      });
    }
    
    (function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "https://connect.facebook.net/en_US/sdk.js";
      js.async = true;
      js.defer = true;
      fjs.parentNode.insertBefore(js, fjs);
      console.log('FB SDK script tag added to DOM');
    }(document, 'script', 'facebook-jssdk'));
  </script>
    <link rel="stylesheet" href="css/animated-bg.css" />
    <link rel="stylesheet" href="css/forgot-password.css">
</head>

<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">

<div class="separate">
  <div class="lp-container">
    <h2>Welcome back!</h2>
    <h3>Please login first</h3>
    <br />
    <form method="POST" action="login_action.php">
      <?php if(isset($_GET['redirect']) && isset($_GET['ride_id'])): ?>
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect']) ?>">
      <input type="hidden" name="ride_id" value="<?= htmlspecialchars($_GET['ride_id']) ?>">
      <?php endif; ?>
      <div class="lp-input-group">
        <label>Email</label>
        <div class="lp-input-field">
          <input type="email" name="email" placeholder="Enter your email address" />
          <i class="fas fa-envelope"></i>
        </div>
      </div>

      <div class="lp-input-group">
        <label>Password</label>
        <div class="lp-input-field">
          <input type="password" name="password" id="password" placeholder="Enter your password" />
          <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i>
        </div>
      </div>

      <a href="#" class="lp-forgot-password" id="forgotPasswordLink">Forgot password?</a>

      <br />
      <div class="lp-profile-photo">
        <div class="lp-photo-box">
          <div class="lp-text-content">
            <span class="lp-title">Remember me</span>
          </div>
        </div>
        <label class="lp-toggle">
          <input type="checkbox" name="remember_me" id="remember_me" />
          <span class="lp-toggle-switch"></span>
        </label>
      </div>

      <button type="submit" class="lp-login-btn">Login</button>

      <p class="lp-or-text">Or login with</p>

      <div class="lp-social-buttons">
        <button type="button" class="lp-google" onclick="handleGoogleLogin()">
          <i class="fab fa-google"></i> Google
        </button>
        <button type="button" class="lp-facebook" id="fb-login-button">
          <i class="fab fa-facebook-f"></i> Facebook
        </button>
      </div>

      <p class="lp-signup-text">Don't have an account? <a href="signup.php">Sign Up</a></p>
    </form>
  </div>
</div>

<!-- Forgot Password Modal -->
<div id="forgotPasswordModal" class="modal">
  <div class="modal-content">
    <button type="button" class="close">&times;</button>
    <h2>Reset Password</h2>
    <p>Enter your email address to receive a password reset code</p>
    
    <div id="step1" class="form-step active">
      <form id="requestResetForm">
        <div class="lp-input-group">
          <label>Email</label>
          <div class="lp-input-field">
            <input type="email" name="reset_email" id="reset_email" placeholder="Enter your email address" required />
            <i class="fas fa-envelope"></i>
          </div>
          <span class="error-message">Please enter a valid email address</span>
        </div>
        <button type="submit" class="lp-login-btn">Send Reset Code</button>
      </form>
    </div>
    
    <div id="step2" class="form-step">
      <form id="verifyOtpForm" method="POST" action="verify_otp.php">
        <div class="lp-input-group">
          <label>Verification Code</label>
          <div class="lp-input-field">
            <input type="text" name="otp_code" id="otp_code" placeholder="Enter verification code" required />
            <i class="fas fa-key"></i>
          </div>
          <span class="error-message">Please enter the verification code</span>
          <input type="hidden" name="reset_email" id="hidden_email">
        </div>
        <button type="submit" class="lp-login-btn">Verify Code</button>
      </form>
    </div>
    
    <div id="step3" class="form-step">
      <form id="resetPasswordForm" method="POST" action="reset_password.php">
        <div class="lp-input-group">
          <label>New Password</label>
          <div class="lp-input-field">
            <input type="password" name="new_password" id="new_password" placeholder="Enter new password" required />
            <i class="fas fa-eye toggle-password" data-target="new_password"></i>
          </div>
          <span class="error-message">Password must be at least 8 characters</span>
        </div>
        <div class="lp-input-group">
          <label>Confirm Password</label>
          <div class="lp-input-field">
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required />
            <i class="fas fa-eye toggle-password" data-target="confirm_password"></i>
          </div>
          <span class="error-message">Passwords do not match</span>
          <input type="hidden" name="reset_email" id="hidden_email2">
          <input type="hidden" name="token" id="reset_token">
        </div>
        <button type="submit" class="lp-login-btn">Reset Password</button>
      </form>
    </div>
  </div>
</div>

<style>
.separate {
  font-family: 'Poppins', sans-serif;
  background-color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px;
  min-height: 80vh;
}

.lp-container {
  width: 100%;
  max-width: 350px;
  text-align: center;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border-radius: 15px;
  background-color: #fff;
}

.lp-container h2 {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 10px;
}

.lp-container h3 {
  font-size: 16px;
  font-weight: 500;
  color: #555;
  margin-top: 0;
}

.lp-input-group {
  text-align: left;
  margin-bottom: 15px;
}

.lp-input-group label {
  font-size: 14px;
  font-weight: 500;
  display: block;
  margin-bottom: 5px;
}

.lp-input-field {
  position: relative;
  overflow: hidden;
}

.lp-input-field input {
  width: 100%;
  padding: 12px;
  background: #f9f9f9;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  padding-right: 70px;
  margin-top: 5px;
  box-sizing: border-box;
  transition: all 0.3s ease;
  border: 1px solid transparent;
}

.lp-input-field input:focus {
  border-color: #ffbf00;
  box-shadow: 0 0 0 2px rgba(255, 191, 0, 0.1);
  transform: translateY(-2px);
}

.lp-input-field i {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  color: #888;
  cursor: pointer;
  transition: all 0.3s ease;
  right: 12px;
}

.lp-input-field i.toggle-password {
  right: 12px;
  cursor: pointer;
}

.lp-input-field i:hover {
  color: #ffbf00;
}

.lp-forgot-password {  
  font-size: 14px;  
  color: #6C49F4;  
  text-decoration: none;  
  display: block;  
  text-align: right;  
  margin-top: 5px;  
  margin-bottom: 10px;  
  transition: all 0.3s ease;
}

.lp-forgot-password:hover {  
  transform: translateY(-2px);
}

.lp-profile-photo {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: white;
  padding: 0px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.lp-photo-box {
  display: flex;
  align-items: center;
  gap: 12px;
}

.lp-text-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.lp-title {
  font-size: 14px;
  display: flex;
  color: #000;
}

.lp-toggle {
  display: flex;
  align-items: center;
}

.lp-toggle-switch {
  width: 36px;
  height: 18px;
  background: #5d5d5d;
  border-radius: 9px;
  position: relative;
  cursor: pointer;
  display: inline-block;
}

.lp-toggle-switch::before {
  content: '';
  position: absolute;
  width: 14px;
  height: 14px;
  background: white;
  border-radius: 50%;
  top: 2px;
  left: 2px;
  transition: 0.3s;
}

.lp-toggle input {
  display: none;
}

.lp-toggle input:checked + .lp-toggle-switch {
  background: #ffbf00;
}

.lp-toggle input:checked + .lp-toggle-switch::before {
  left: 20px;
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
  font-weight: 600;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  position: relative;
  overflow: hidden;
  z-index: 1;
}

.lp-login-btn:before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.2);
  transition: all 0.4s ease;
  z-index: -1;
}

.lp-login-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 7px 14px rgba(255, 191, 0, 0.2);
}

.lp-login-btn:hover:before {
  left: 100%;
}

.lp-login-btn:active {
  transform: translateY(-1px);
}

.lp-or-text {
  font-size: 14px;
  color: gray;
  margin: 15px 0;
  position: relative;
}

.lp-or-text:before,
.lp-or-text:after {
  content: "";
  position: absolute;
  height: 1px;
  width: 40%;
  background-color: #eee;
  top: 50%;
}

.lp-or-text:before {
  left: 0;
}

.lp-or-text:after {
  right: 0;
}

.lp-social-buttons {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}

.lp-social-buttons button {
  flex: 1;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  position: relative;
  overflow: hidden;
  z-index: 1;
}

.lp-social-buttons button:before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.2);
  transition: all 0.4s ease;
  z-index: -1;
}

.lp-social-buttons button:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.lp-social-buttons button:hover:before {
  left: 100%;
}

.lp-social-buttons button:active {
  transform: translateY(-1px);
}

.lp-google {
  background: white;
  color: #757575;
}

.lp-google:hover {
  background: #f0f0f0;
}

.lp-facebook {
  background: #3b5998;
  color: white;
}

.lp-facebook:hover {
  background: #344e86;
}

.lp-social-buttons button i {
  font-size: 16px;
}

.lp-signup-text {
  font-size: 14px;
  margin-top: 15px;
  margin-bottom: 0;
}

.lp-signup-text a {
  color: #6C49F4;
  text-decoration: none;
  font-weight: 600;
}

/* Modal Styles */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.5);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.modal.show {
  opacity: 1;
}

.modal-content {
  background-color: #fff;
  margin: 15% auto;
  padding: 25px;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  max-width: 400px;
  position: relative;
  transform: translateY(50px);
  opacity: 0;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.modal.show .modal-content {
  transform: translateY(0);
  opacity: 1;
}

.close {
  position: absolute;
  right: 20px;
  top: 15px;
  color: #aaa;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
}

.close:hover {
  color: black;
}

.form-step {
  display: none;
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.4s ease;
}

.form-step.active {
  display: block;
  opacity: 1;
  transform: translateY(0);
}

/* Responsive styles */
@media (max-width: 768px) {
  .separate {
    padding: 30px 20px;
    min-height: 70vh;
  }

  .lp-container {
    padding: 15px;
  }
  
  .modal-content {
    margin: 15% auto;
    width: 90%;
  }
}

@media (max-width: 480px) {
  .separate {
    padding: 20px 15px;
  }

  .lp-container {
    padding: 15px 10px;
  }

  .lp-container h2 {
    font-size: 20px;
  }

  .lp-container h3 {
    font-size: 14px;
  }

  .lp-input-field input {
    padding-right: 40px;
  }
  
  .lp-social-buttons {
    flex-direction: column;
  }
}

@media (max-width: 320px) {
  .lp-container h2 {
    font-size: 18px;
  }
  
  .lp-container h3 {
    font-size: 13px;
  }
  
  .lp-login-btn {
    font-size: 14px;
  }
  
  .lp-social-buttons button,
  .lp-signup-text,
  .lp-or-text,
  .lp-forgot-password {
    font-size: 12px;
  }
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(255, 191, 0, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(255, 191, 0, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(255, 191, 0, 0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.lp-container {
  animation: fadeIn 0.7s ease-out forwards;
}

.lp-input-group {
  animation: fadeIn 0.5s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 0.1s);
  opacity: 0;
}

.lp-input-group:nth-child(1) {
  --i: 1;
}

.lp-input-group:nth-child(2) {
  --i: 2;
}

.lp-social-buttons button {
  animation: fadeIn 0.5s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 0.1s);
  opacity: 0;
}

.lp-social-buttons button:nth-child(1) {
  --i: 3;
}

.lp-social-buttons button:nth-child(2) {
  --i: 4;
}
</style>

<script>
// Add this at the start of your script
$(document).ready(function() {
    // Check if we should show the verification modal
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('show_verify') === 'true') {
        const email = urlParams.get('email');
        if(email) {
            // Set the email in the hidden field
            $("#hidden_email").val(email);
            // Show the modal and activate step 2
            modal.style.display = "block";
            step1.classList.remove("active");
            step2.classList.add("active");
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Email Sent',
                text: 'Please enter the verification code sent to your email',
                confirmButtonColor: '#ffbf00'
            });
            // Remove the query parameters from URL without refreshing
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
    
    // Rest of your existing document.ready code...
});

// Modal handling
const modal = document.getElementById("forgotPasswordModal");
const forgotLink = document.getElementById("forgotPasswordLink");
const closeBtn = document.querySelector('.close');
const step1 = document.getElementById("step1");
const step2 = document.getElementById("step2");
const step3 = document.getElementById("step3");
const requestForm = document.getElementById("requestResetForm");
const verifyForm = document.getElementById("verifyOtpForm");
const resetForm = document.getElementById("resetPasswordForm");
const hiddenEmail = document.getElementById("hidden_email");
const hiddenEmail2 = document.getElementById("hidden_email2");

// Open modal when "Forgot password?" is clicked
forgotLink.onclick = function(e) {
  e.preventDefault();
  modal.style.display = "block";
}

// Close the modal when X is clicked
closeBtn.onclick = function() {
  modal.style.display = "none";
  resetSteps();
}

// Close the modal when clicking outside of it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
    resetSteps();
  }
}

function resetSteps() {
  step1.classList.add("active");
  step2.classList.remove("active");
  step3.classList.remove("active");
  requestForm.reset();
  verifyForm.reset();
  resetForm.reset();
}

// Add keyframes for spinner
if (!document.querySelector('style#spinner-style')) {
  const style = document.createElement('style');
  style.id = 'spinner-style';
  style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
  document.head.appendChild(style);
}

// Handle the request password reset form
$(document).ready(function() {
  $("#requestResetForm").on("submit", function(e) {
    e.preventDefault();
    const email = $("#reset_email").val();
    
    if (!email) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Please enter your email address',
        confirmButtonColor: '#ffbf00'
      });
      return;
    }
    
    // Show loading indicator
    const submitBtn = $(this).find('button[type="submit"]');
    const originalBtnText = submitBtn.html();
    submitBtn.prop('disabled', true);
    submitBtn.html('<span style="display:inline-block;width:20px;height:20px;border:3px solid rgba(255,191,0,.3);border-radius:50%;border-top-color:#ffbf00;animation:spin 1s ease-in-out infinite;vertical-align:middle;margin-right:10px;"></span> Processing...');
    
    console.log('Sending request to forgot_password.php with email:', email);
    
    $.ajax({
      url: 'forgot_password.php',
      type: 'POST',
      data: { reset_email: email },
      dataType: 'json',
      success: function(response) {
        console.log('Response received:', response);
        if (response.success) {
          $("#hidden_email").val(email);
          step1.classList.remove("active");
          step2.classList.add("active");
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'A verification code has been sent to your email',
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
      error: function(xhr, status, error) {
        console.error('AJAX Error:', status, error);
        console.log('Response text:', xhr.responseText);
        
        try {
          const errorResponse = JSON.parse(xhr.responseText);
          if (errorResponse && errorResponse.message) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: errorResponse.message,
              confirmButtonColor: '#ffbf00'
            });
            return;
          }
        } catch(e) {
          console.error('Error parsing JSON response:', e);
        }
        
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Something went wrong. Please try again later.',
          confirmButtonColor: '#ffbf00'
        });
      },
      complete: function() {
        submitBtn.prop('disabled', false);
        submitBtn.html(originalBtnText);
      }
    });
  });

  // Handle the OTP verification form
  $("#verifyOtpForm").on("submit", function(e) {
    e.preventDefault();
    const email = $("#hidden_email").val();
    const otp = $("#otp_code").val();
    
    if (!otp) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Please enter the verification code',
        confirmButtonColor: '#ffbf00'
      });
      return;
    }
    
    // Show loading indicator
    const submitBtn = $(this).find('button[type="submit"]');
    const originalBtnText = submitBtn.html();
    submitBtn.prop('disabled', true);
    submitBtn.html('<span style="display:inline-block;width:20px;height:20px;border:3px solid rgba(255,191,0,.3);border-radius:50%;border-top-color:#ffbf00;animation:spin 1s ease-in-out infinite;vertical-align:middle;margin-right:10px;"></span> Verifying...');
    
    console.log('Sending request to verify_otp.php with email:', email, 'and OTP:', otp);
    
    $.ajax({
      url: 'verify_otp.php',
      type: 'POST',
      data: { reset_email: email, otp_code: otp },
      dataType: 'json',
      success: function(response) {
        console.log('Response received:', response);
        if (response.success) {
          $("#hidden_email2").val(email);
          $("#reset_token").val(response.token);
          step2.classList.remove("active");
          step3.classList.add("active");
          Swal.fire({
            icon: 'success',
            title: 'Code Verified',
            text: 'Please create your new password',
            confirmButtonColor: '#ffbf00'
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: response.message || 'Invalid verification code',
            confirmButtonColor: '#ffbf00'
          });
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX Error:', status, error);
        console.log('Response text:', xhr.responseText);
        
        try {
          const errorResponse = JSON.parse(xhr.responseText);
          if (errorResponse && errorResponse.message) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: errorResponse.message,
              confirmButtonColor: '#ffbf00'
            });
            return;
          }
        } catch(e) {
          console.error('Error parsing JSON response:', e);
        }
        
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Something went wrong. Please try again later.',
          confirmButtonColor: '#ffbf00'
        });
      },
      complete: function() {
        submitBtn.prop('disabled', false);
        submitBtn.html(originalBtnText);
      }
    });
  });

  // Handle the reset password form
  $("#resetPasswordForm").on("submit", function(e) {
    e.preventDefault();
    const email = $("#hidden_email2").val();
    const token = $("#reset_token").val();
    const password = $("#new_password").val();
    const confirmPassword = $("#confirm_password").val();
    
    if (!password) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Please enter a new password',
        confirmButtonColor: '#ffbf00'
      });
      return;
    }
    
    if (password !== confirmPassword) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Passwords do not match',
        confirmButtonColor: '#ffbf00'
      });
      return;
    }
    
    // Show loading indicator
    const submitBtn = $(this).find('button[type="submit"]');
    const originalBtnText = submitBtn.html();
    submitBtn.prop('disabled', true);
    submitBtn.html('<span style="display:inline-block;width:20px;height:20px;border:3px solid rgba(255,191,0,.3);border-radius:50%;border-top-color:#ffbf00;animation:spin 1s ease-in-out infinite;vertical-align:middle;margin-right:10px;"></span> Resetting...');
    
    console.log('Sending request to reset_password.php with email:', email, 'and token:', token);
    
    $.ajax({
      url: 'reset_password.php',
      type: 'POST',
      data: { 
        reset_email: email, 
        token: token, 
        new_password: password, 
        confirm_password: confirmPassword 
      },
      dataType: 'json',
      success: function(response) {
        console.log('Response received:', response);
        if (response.success) {
          modal.style.display = "none";
          resetSteps();
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Your password has been reset successfully',
            confirmButtonColor: '#ffbf00'
          }).then((result) => {
            if (result.isConfirmed) {
              // Clear any stored data
              $("#hidden_email").val('');
              $("#hidden_email2").val('');
              $("#reset_token").val('');
              $("#otp_code").val('');
              $("#new_password").val('');
              $("#confirm_password").val('');
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
      error: function(xhr, status, error) {
        console.error('AJAX Error:', status, error);
        console.log('Response text:', xhr.responseText);
        
        try {
          const errorResponse = JSON.parse(xhr.responseText);
          if (errorResponse && errorResponse.message) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: errorResponse.message,
              confirmButtonColor: '#ffbf00'
            });
            return;
          }
        } catch(e) {
          console.error('Error parsing JSON response:', e);
        }
        
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Something went wrong. Please try again later.',
          confirmButtonColor: '#ffbf00'
        });
      },
      complete: function() {
        submitBtn.prop('disabled', false);
        submitBtn.html(originalBtnText);
      }
    });
  });
});

// Google OAuth Configuration
const googleClientId = '420898413031-o62i2047rj334erorsdmfv4i7v8g68e5.apps.googleusercontent.com'; // Replace with your Google Client ID

// Handle Google Login
function handleGoogleLogin() {
  const client = google.accounts.oauth2.initTokenClient({
    client_id: googleClientId,
    scope: 'email profile',
    callback: async (response) => {
      if (response.access_token) {
        try {
          console.log("Google OAuth response received");
          
          // Get user info from Google
          const userInfo = await fetch('https://www.googleapis.com/oauth2/v3/userinfo', {
            headers: { Authorization: `Bearer ${response.access_token}` }
          }).then(res => res.json());
          
          console.log("Google user info received:", userInfo);
          
          // Show loading state
          Swal.fire({
            title: 'Logging in...',
            text: 'Please wait while we process your request',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });

          // Prepare data for backend
          const userData = {
            provider: 'google',
            email: userInfo.email,
            name: userInfo.name,
            picture: userInfo.picture,
            id: userInfo.sub
          };
          
          // If userInfo contains phone number (unlikely without verification)
          if (userInfo.phone_number) {
            userData.phone = userInfo.phone_number;
          }

          // Send to your backend
          const result = await fetch('social_login.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
          }).then(res => res.json());

          console.log("Social login response:", result);

          if (result.success) {
            // Redirect to the URL provided by the server
            window.location.href = result.redirect || 'dashboard.php';
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Login Failed',
              text: result.message,
              confirmButtonColor: '#ffbf00'
            });
          }
        } catch (error) {
          console.error('Google login error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: 'Something went wrong with Google login',
            confirmButtonColor: '#ffbf00'
          });
        }
      }
    }
  });
  client.requestAccessToken();
}

// Handle Facebook Login
function handleFacebookLogin() {
  if (typeof FB === 'undefined') {
    console.error('Facebook SDK not loaded or not initialized');
    Swal.fire({
      icon: 'error',
      title: 'Login Error',
      text: 'Facebook login is not available at the moment. Please try again later.',
      confirmButtonColor: '#ffbf00'
    });
    return;
  }
  
  FB.login(async function(response) {
    if (response.authResponse) {
      try {
        console.log("Facebook OAuth response received");
        
        // Show loading state
        Swal.fire({
          title: 'Logging in...',
          text: 'Please wait while we process your request',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        // Get user info from Facebook
        FB.api('/me', { fields: 'id,name,email,picture.width(200).height(200)' }, async function(userInfo) {
          console.log("Facebook user info received:", userInfo);
          
          // Prepare data for backend
          const userData = {
            provider: 'facebook',
            email: userInfo.email,
            name: userInfo.name,
            picture: userInfo.picture ? userInfo.picture.data.url : null,
            id: userInfo.id,
            token: response.authResponse.accessToken,
            fb_app_secret: 'b724541682710ff9ecb79908c11ca5e3' // Adding app secret for server-side validation
          };
          
          // Send to your backend
          const result = await fetch('social_login.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
          }).then(res => res.json());

          console.log("Social login response:", result);

          if (result.success) {
            // Redirect to the URL provided by the server
            window.location.href = result.redirect || 'dashboard.php';
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Login Failed',
              text: result.message || 'Authentication failed',
              confirmButtonColor: '#ffbf00'
            });
          }
        });
      } catch (error) {
        console.error('Facebook login error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Login Failed',
          text: 'Something went wrong with Facebook login',
          confirmButtonColor: '#ffbf00'
        });
      }
    } else {
      console.log('User cancelled login or did not fully authorize.');
      Swal.close();
      
      Swal.fire({
        icon: 'info',
        title: 'Login Cancelled',
        text: 'Facebook login was cancelled',
        confirmButtonColor: '#ffbf00'
      });
    }
  }, { scope: 'email,public_profile' });
}

// Password visibility toggle
document.addEventListener('DOMContentLoaded', function() {
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  
  if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', function() {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      
      // Toggle the eye icon
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
      
      // Add pulse animation
      this.style.animation = 'none';
      setTimeout(() => {
        this.style.animation = 'pulse 1s';
      }, 5);
    });
  }
  
  // Add animation classes to input groups
  document.querySelectorAll('.lp-input-group').forEach((group, index) => {
    group.style.setProperty('--i', index + 1);
  });
  
  // Add animation classes to social buttons
  document.querySelectorAll('.lp-social-buttons button').forEach((button, index) => {
    button.style.setProperty('--i', index + 3);
  });
  
  // Enhance modal animation
  if (forgotLink) {
    forgotLink.onclick = function(e) {
      e.preventDefault();
      modal.style.display = "block";
      setTimeout(() => {
        modal.classList.add('show');
      }, 10);
    }
  }
  
  if (closeBtn) {
    closeBtn.onclick = function() {
      modal.classList.remove('show');
      setTimeout(() => {
        modal.style.display = "none";
        resetSteps();
      }, 300);
    }
  }
  
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.classList.remove('show');
      setTimeout(() => {
        modal.style.display = "none";
        resetSteps();
      }, 300);
    }
  }
});

// Add this to your existing JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    document.querySelectorAll('.toggle-password').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
            
            // Add pulse animation
            this.style.animation = 'none';
            setTimeout(() => {
                this.style.animation = '';
            }, 10);
        });
    });

    // Modal animation
    const modal = document.getElementById('forgotPasswordModal');
    const closeBtn = document.querySelector('.close');
    
    if (modal) {
        // Show modal with animation
        window.showForgotPasswordModal = function() {
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        };
        
        // Close modal with animation
        const closeModal = function() {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                resetSteps();
            }, 300);
        };
        
        if (closeBtn) {
            closeBtn.onclick = closeModal;
        }
        
        // Close on outside click
        window.onclick = function(event) {
            if (event.target === modal) {
                closeModal();
            }
        };
    }
    
    // Form validation and submission
    const requestResetForm = document.getElementById('requestResetForm');
    if (requestResetForm) {
        requestResetForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = document.getElementById('reset_email');
            const errorMessage = emailInput.parentElement.nextElementSibling;
            const submitBtn = this.querySelector('button[type="submit"]');
            
            if (!emailInput.value || !emailInput.value.includes('@')) {
                errorMessage.classList.add('visible');
                emailInput.parentElement.classList.add('error');
                return;
            }
            
            // Remove error state if valid
            errorMessage.classList.remove('visible');
            emailInput.parentElement.classList.remove('error');
            
            // Add loading state
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            // Your existing AJAX call here
            // ...
        });
    }
});
</script>

<?php if (isset($_GET['error'])): ?>
<script>
<?php if ($_GET['error'] === 'invalid_password'): ?>
    Swal.fire({
        icon: 'error',
        title: 'Invalid Password',
        text: 'The password you entered is incorrect.',
        confirmButtonColor: '#ffbf00'
    });
<?php elseif ($_GET['error'] === 'user_not_found'): ?>
    Swal.fire({
        icon: 'error',
        title: 'User Not Found',
        text: 'No account exists with this email address.',
        confirmButtonColor: '#ffbf00'
    });
<?php elseif ($_GET['error'] === 'social_user'): ?>
    Swal.fire({
        icon: 'info',
        title: 'Social Login Required',
        text: 'This email is registered with Google or Facebook. Please use the respective social login button.',
        confirmButtonColor: '#ffbf00'
    });
<?php endif; ?>
</script>
<?php endif; ?>
<br><?php include 'footer.php'; ?>
</div></body>
</html>
