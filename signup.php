<?php include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - PoolPal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="styles.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Add password strength meter -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
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
      const fbButtons = document.querySelectorAll('.facebook');
      fbButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          console.log('Facebook button clicked');
          handleFacebookSignup();
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
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>

<?php
// Remove the duplicate status message handling at the top
if (isset($_GET['error'])) {
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: '" . ($_GET['error'] === 'password_mismatch' ? 'Password Mismatch' : 
                      ($_GET['error'] === 'user_exists' ? 'User Already Exists' : 
                      ($_GET['error'] === 'upload_failed' ? 'Upload Failed' : 
                      ($_GET['error'] === 'invalid_file_type' ? 'Invalid File Type' : 'Registration Failed')))) . "',
            text: '" . ($_GET['error'] === 'password_mismatch' ? 'Passwords do not match!' : 
                     ($_GET['error'] === 'user_exists' ? 'A user with this email or phone number already exists.' : 
                     ($_GET['error'] === 'upload_failed' ? 'Failed to upload the profile photo.' : 
                     ($_GET['error'] === 'invalid_file_type' ? 'Only JPG, JPEG, PNG, and GIF files are allowed.' : 'Something went wrong while registering.')))) . "',
            confirmButtonColor: '#ff3e3e'
        });
    });
    </script>";
}

// Handle success status separately
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Registration Successful!',
            text: 'Your account has been created successfully. You will be redirected to login.',
            confirmButtonColor: '#28a745',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = 'login.php';
        });
    });
    </script>";
}
?>

<div class="main-content">
<div class="separate">
  <div class="signup-container">
    <h2>Create your account</h2>

    <form id="signupForm" method="POST" action="signup_process.php" enctype="multipart/form-data" class="pp-signup-form">
      <div class="pp-input-group">
        <label>Full Name</label>
        <div class="pp-input-field">
          <input type="text" name="full_name" placeholder="Enter your full name" required>
          <i class="fas fa-user"></i>
        </div>
      </div>

      <div class="pp-input-group">
        <label>Email</label>
        <div class="pp-input-field">
          <input type="email" name="email" placeholder="Enter your email address" required>
          <i class="fas fa-envelope"></i>
        </div>
      </div>

      <div class="pp-input-group">
        <label>Phone Number</label>
        <div class="pp-input-field">
          <input type="text" name="phone" placeholder="Enter your phone number" required>
          <i class="fas fa-phone"></i>
        </div>
      </div>

      <div class="pp-input-group">
        <label>Password</label>
        <div class="pp-input-field">
          <input type="password" name="password" id="password" placeholder="Create a password" required>
          <i class="fas fa-lock"></i>
          <i class="fas fa-eye password-toggle" data-target="password"></i>
        </div>
        <div class="pp-strength-meter">
          <div class="pp-meter-bar"></div>
        </div>
        <span class="pp-strength-text"></span>
      </div>

      <div class="pp-input-group">
        <label>Confirm Password</label>
        <div class="pp-input-field">
          <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>
          <i class="fas fa-lock"></i>
          <i class="fas fa-eye password-toggle" data-target="confirm_password"></i>
        </div>
      </div>

      <div class="profile-photo">
        <div class="photo-box">
          <div class="icon-container">
            <i class="fas fa-camera"></i>
          </div>
          <div class="text-content">
            <span class="title">Profile Photo</span>
            <span class="subtitle">Add a photo to your profile</span>
          </div>
        </div>
        <div class="upload-icon">
          <label for="profile_photo">
            <i class="fas fa-upload"></i>
          </label>
          <input type="file" name="profile_photo" id="profile_photo" style="display: none;" accept="image/*" required>
        </div>
      </div>

      <!-- Add image preview container -->
      <div id="image-preview-container" style="display: none; margin-bottom: 15px;">
        <img id="image-preview" src="#" alt="Profile Preview" style="max-width: 150px; border-radius: 50%; margin: 10px auto; display: block;">
        <button type="button" id="remove-image" class="remove-image-btn">
          <i class="fas fa-times"></i> Remove Image
        </button>
      </div>

      <div class="terms-section">
        <div class="terms-toggle">
          <div class="terms-text">
            <i class="fas fa-file-contract"></i>
            I agree to the Terms & Conditions
            <span class="terms-required">* Required</span>
          </div>
          <label class="toggle">
            <input type="checkbox" name="terms_accepted" id="terms_accepted" required>
            <span class="toggle-switch"></span>
          </label>
        </div>
      </div>

      <div class="profile-photo">
        <div class="photo-box">
          <div class="icon-container">
            <i class="fas fa-envelope"></i>
          </div>
          <div class="text-content">
            <span class="title">Subscribe to newsletter</span>
            <span class="subtitle">For new features and promotions</span>
          </div>
        </div>
        <label class="toggle">
          <input type="checkbox">
          <span class="toggle-switch"></span>
        </label>
      </div>

      <button type="submit" class="pp-create-account">Create Account</button>

      <p class="pp-or-signup">Or sign up with </p>

      <div class="pp-social-buttons">
        <button type="button" class="google" onclick="handleGoogleSignup()">
          <i class="fab fa-google"></i> Google
        </button>
        <button type="button" class="facebook" id="fb-signup-button">
          <i class="fab fa-facebook-f"></i> Facebook
        </button>
      </div>

      <p class="login-link">Already have an account? <a href="login.php">Login</a></p>

    </form>
  </div>

  <style>
  /* General Styling */
.separate {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.13) 100%);
  backdrop-filter: blur(10px);
  min-height: 100vh;
  margin: 0;
  padding: 40px 20px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.signup-container {
  width: 100%;
  max-width: 480px;
  text-align: center;
  padding: 40px;
  background: rgba(255, 255, 255, 0.95);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
  backdrop-filter: blur(4px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.18);
  position: relative;
  overflow: hidden;
}

.signup-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #ffbf00, #ff4b2b);
}

h2 {
  font-weight: 700;
  margin-bottom: 30px;
  font-size: 28px;
  color: #2c3e50;
  position: relative;
  padding-bottom: 10px;
}

h2::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 3px;
  background: #ffbf00;
  border-radius: 3px;
}

/* Input Fields */
.pp-input-group {
  text-align: left;
  margin-bottom: 25px;
  opacity: 1;
  transform: none;
}

.pp-input-group label {
  display: block;
  font-size: 14px;
  margin-bottom: 8px;
  font-weight: 600;
  color: #34495e;
  transition: color 0.3s ease;
}

.pp-input-field {
  position: relative;
  transition: transform 0.3s ease;
  margin-bottom: 5px;
}

.pp-input-field input {
  width: 100%;
  padding: 15px 75px 15px 20px;
  border: 2px solid #e0e0e0;
  background: #f8f9fa;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 500;
  color: #2c3e50;
  transition: all 0.3s ease;
}

.pp-input-field i.fas.fa-lock {
  position: absolute;
  top: 50%;
  right: 45px;
  transform: translateY(-50%);
  color: #95a5a6;
  transition: all 0.3s ease;
  font-size: 18px;
  z-index: 1;
}

.pp-input-field i.password-toggle {
  position: absolute;
  top: 50%;
  right: 15px;
  transform: translateY(-50%);
  color: #95a5a6;
  transition: all 0.3s ease;
  font-size: 18px;
  cursor: pointer;
  z-index: 2;
}

.pp-input-field i:not(.password-toggle):not(.fas.fa-lock) {
  position: absolute;
  top: 50%;
  right: 15px;
  transform: translateY(-50%);
  color: #95a5a6;
  transition: all 0.3s ease;
  font-size: 18px;
  z-index: 1;
}

.pp-input-field input:focus {
  border-color: #ffbf00;
  background: #fff;
  box-shadow: 0 0 0 5px rgba(255, 191, 0, 0.1);
  transform: translateY(-2px);
}

/* Icon hover effects */
.pp-input-field:hover i,
.pp-input-field input:focus + i {
  color: #ffbf00;
}

.pp-input-field .password-toggle:hover {
  color: #ffbf00;
}

/* Password Strength Meter */
.pp-strength-meter {
  height: 6px;
  background: #ecf0f1;
  margin: 10px 0;
  border-radius: 3px;
  overflow: hidden;
}

.pp-meter-bar {
  height: 100%;
  border-radius: 3px;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  background: linear-gradient(90deg, 
    #ff4b2b 0%, 
    #ffbf00 50%, 
    #2ecc71 100%
  );
  background-size: 200% 100%;
}

.pp-strength-text {
  font-size: 12px;
  font-weight: 600;
  color: #7f8c8d;
  margin-top: 5px;
}

/* Profile Photo Upload */
.profile-photo {
  background: #f8f9fa;
  border-radius: 15px;
  padding: 20px;
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border: 2px solid #e0e0e0;
  transition: all 0.3s ease;
}

.profile-photo:hover {
  border-color: #ffbf00;
  transform: translateY(-2px);
}

.photo-box {
  display: flex;
  align-items: center;
  gap: 15px;
}

.icon-container {
  width: 50px;
  height: 50px;
  background: rgba(255, 191, 0, 0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-container i {
  font-size: 24px;
  color: #ffbf00;
}

/* Social Buttons */
.pp-social-buttons {
  display: flex;
  gap: 15px;
  margin: 25px 0;
}

.pp-social-buttons button {
  flex: 1;
  padding: 14px;
  border-radius: 12px;
  border: none;
  font-size: 15px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.google {
  background: #fff;
  color: #34495e;
  border: 2px solid #e0e0e0;
}

.facebook {
  background: #1877f2;
  color: white;
}

.pp-social-buttons button:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Create Account Button */
.pp-create-account {
  width: 100%;
  padding: 16px;
  background: linear-gradient(45deg, #ffbf00, #ff4b2b);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 20px;
}

.pp-create-account:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 20px rgba(255, 191, 0, 0.4);
}

.pp-create-account:active {
  transform: translateY(0);
}

/* Toggle Switch */
.toggle {
  position: relative;
  display: inline-block;
  width: 52px;
  height: 28px;
}

.toggle input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-switch {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #e0e0e0;
  transition: .4s;
  border-radius: 34px;
}

.toggle-switch:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .toggle-switch {
  background: linear-gradient(45deg, #ffbf00, #ff4b2b);
}

input:checked + .toggle-switch:before {
  transform: translateX(24px);
}

/* Update input field error states to maintain icon positioning */
.pp-input-field.error input {
  border-color: #e74c3c;
  background: #fff;
  padding-right: 75px !important; /* Force padding even in error state */
}

.pp-input-field.success input {
  border-color: #2ecc71;
  background: #fff;
  padding-right: 75px !important; /* Force padding even in success state */
}

/* Ensure icons stay in position during validation */
.pp-input-field i.fas.fa-lock,
.pp-input-field.error i.fas.fa-lock,
.pp-input-field.success i.fas.fa-lock {
  right: 45px !important;
  z-index: 1;
}

.pp-input-field i.password-toggle,
.pp-input-field.error i.password-toggle,
.pp-input-field.success i.password-toggle {
  right: 15px !important;
  z-index: 2;
}

/* Error message styling */
.pp-error-message {
  color: #e74c3c;
  font-size: 12px;
  margin-top: 5px;
  font-weight: 500;
  display: none;
}

.pp-error-message.visible {
  display: block;
}

/* Responsive Design */
@media (max-width: 768px) {
  .signup-container {
    padding: 30px 20px;
  }

  h2 {
    font-size: 24px;
  }

  .pp-social-buttons {
    flex-direction: column;
  }

  .profile-photo {
    padding: 15px;
  }
}

/* Loading Animation */
.pp-create-account.loading {
  position: relative;
  color: transparent;
}

.pp-create-account.loading::after {
  content: '';
  position: absolute;
  width: 24px;
  height: 24px;
  top: 50%;
  left: 50%;
  margin: -12px 0 0 -12px;
  border: 3px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: pp-spin 0.8s infinite linear;
}

@keyframes pp-spin {
  to { transform: rotate(360deg); }
}

/* Login Link */
.login-link {
  margin-top: 25px;
  font-size: 15px;
  color: #7f8c8d;
}

.login-link a {
  color: #ffbf00;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.3s ease;
}

.login-link a:hover {
  color: #ff4b2b;
}

/* Or Separator */
.pp-or-signup {
  position: relative;
  text-align: center;
  margin: 25px 0;
  color: #95a5a6;
  font-size: 14px;
  font-weight: 500;
}

.pp-or-signup::before,
.pp-or-signup::after {
  content: '';
  position: absolute;
  top: 50%;
  width: calc(50% - 30px);
  height: 1px;
  background: #e0e0e0;
}

.pp-or-signup::before {
  left: 0;
}

.pp-or-signup::after {
  right: 0;
}

/* Image preview styles */
#image-preview-container {
  margin: 15px auto;
  text-align: center;
  background: #f8f9fa;
  padding: 20px;
  border-radius: 12px;
  border: 2px dashed #e0e0e0;
  transition: all 0.3s ease;
}

#image-preview {
  max-width: 150px;
  max-height: 150px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #ffbf00;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.remove-image-btn {
  background: #ff4b2b;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  margin-top: 10px;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.remove-image-btn:hover {
  background: #e63e1a;
  transform: translateY(-2px);
}

/* Terms agreement section */
.terms-section {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 12px;
  margin: 20px 0;
  border: 2px solid #e0e0e0;
}

.terms-toggle {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
}

.terms-text {
  flex: 1;
  font-size: 14px;
  font-weight: 500;
  color: #2c3e50;
}

.terms-required {
  color: #ff4b2b;
  font-size: 12px;
  margin-top: 5px;
  display: none;
}

.terms-required.visible {
  display: block;
}
  </style>
  </div>
  <?php if (isset($_GET['error']) || isset($_GET['status'])): ?>
<script>
// Remove this entire block as it's now handled above
</script>
<?php endif; ?>

<br><?php include  'footer.php';?>
<script>
document.getElementById('signupForm').addEventListener('submit', function(e) {
  e.preventDefault(); // Prevent form submission initially
  
  const termsAccepted = document.getElementById('terms_accepted');
  const termsRequired = document.querySelector('.terms-required');
  
  if (!termsAccepted.checked) {
    termsRequired.classList.add('visible');
    Swal.fire({
      icon: 'warning',
      title: 'Terms Required',
      text: 'Please accept the Terms and Conditions to continue',
      confirmButtonColor: '#ffbf00'
    });
    return;
  }
  
  // If terms are accepted, submit the form
  this.submit();
});

// Image preview handler
document.getElementById('profile_photo').addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    const previewContainer = document.getElementById('image-preview-container');
    const previewImage = document.getElementById('image-preview');
    
    reader.onload = function(e) {
      previewImage.src = e.target.result;
      previewContainer.style.display = 'block';
    }
    
    reader.readAsDataURL(file);
  }
});

// Remove image handler
document.getElementById('remove-image').addEventListener('click', function() {
  const input = document.getElementById('profile_photo');
  const previewContainer = document.getElementById('image-preview-container');
  const previewImage = document.getElementById('image-preview');
  
  input.value = '';
  previewImage.src = '#';
  previewContainer.style.display = 'none';
});

// Password toggle functionality
document.addEventListener('DOMContentLoaded', function() {
  const toggles = document.querySelectorAll('.password-toggle');
  
  toggles.forEach(toggle => {
    toggle.addEventListener('click', function(e) {
      e.preventDefault(); // Prevent any default behavior
      e.stopPropagation(); // Stop event bubbling
      
      const targetId = this.getAttribute('data-target');
      const passwordField = document.getElementById(targetId);
      
      if (passwordField) {
        // Toggle password visibility
        if (passwordField.type === 'password') {
          passwordField.type = 'text';
          this.classList.remove('fa-eye');
          this.classList.add('fa-eye-slash');
        } else {
          passwordField.type = 'password';
          this.classList.remove('fa-eye-slash');
          this.classList.add('fa-eye');
        }
      }
    });
  });
});

// Google OAuth Configuration
const googleClientId = '420898413031-o62i2047rj334erorsdmfv4i7v8g68e5.apps.googleusercontent.com';

// Handle Google Signup
function handleGoogleSignup() {
  const client = google.accounts.oauth2.initTokenClient({
    client_id: googleClientId,
    scope: 'email profile',
    callback: async (response) => {
      if (response.access_token) {
        try {
          // Get user info from Google
          const userInfo = await fetch('https://www.googleapis.com/oauth2/v3/userinfo', {
            headers: { Authorization: `Bearer ${response.access_token}` }
          }).then(res => res.json());
          
          // Prepare data for backend
          const userData = {
            provider: 'google',
            email: userInfo.email,
            name: userInfo.name,
            picture: userInfo.picture,
            id: userInfo.sub,
            signup_mode: true
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

          if (result.success) {
            Swal.fire({
              icon: 'success',
              title: 'Signup Successful!',
              text: 'Your account has been created successfully.',
              confirmButtonColor: '#28a745'
            }).then(() => {
              window.location.href = result.redirect || 'dashboard.php';
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Signup Failed',
              text: result.message,
              confirmButtonColor: '#ff3e3e'
            });
          }
        } catch (error) {
          console.error('Google signup error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Signup Failed',
            text: 'Something went wrong with Google signup',
            confirmButtonColor: '#ff3e3e'
          });
        }
      }
    }
  });
  client.requestAccessToken();
}

// Handle Facebook Signup
function handleFacebookSignup() {
  if (typeof FB === 'undefined') {
    console.error('Facebook SDK not loaded or not initialized');
    Swal.fire({
      icon: 'error',
      title: 'Signup Error',
      text: 'Facebook signup is not available at the moment. Please try again later.',
      confirmButtonColor: '#ff3e3e'
    });
    return;
  }
  
  FB.login(async function(response) {
    if (response.authResponse) {
      try {
        console.log("Facebook OAuth response received");
        
        // Show loading state
        Swal.fire({
          title: 'Signing up...',
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
            fb_app_secret: 'b724541682710ff9ecb79908c11ca5e3', // Adding app secret for server-side validation
            signup_mode: true
          };
          
          // Send to your backend
          const result = await fetch('social_login.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
          }).then(res => res.json());

          console.log("Social signup response:", result);

          if (result.success) {
            Swal.fire({
              icon: 'success',
              title: 'Signup Successful!',
              text: 'Your account has been created successfully.',
              confirmButtonColor: '#28a745'
            }).then(() => {
              window.location.href = result.redirect || 'dashboard.php';
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Signup Failed',
              text: result.message || 'Authentication failed',
              confirmButtonColor: '#ff3e3e'
            });
          }
        });
      } catch (error) {
        console.error('Facebook signup error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Signup Failed',
          text: 'Something went wrong with Facebook signup',
          confirmButtonColor: '#ff3e3e'
        });
      }
    } else {
      console.log('User cancelled signup or did not fully authorize.');
      Swal.close();
      
      Swal.fire({
        icon: 'info',
        title: 'Signup Cancelled',
        text: 'Facebook signup was cancelled',
        confirmButtonColor: '#ff3e3e'
      });
    }
  }, { scope: 'email,public_profile' });
}
</script>

<script>
// Check if FB SDK is loaded properly
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM fully loaded, checking Facebook SDK status...');
  
  // Check if Facebook SDK is loaded
  if (typeof FB === 'undefined') {
    console.error('Facebook SDK not loaded.');
    // Try to reload the page if FB is not defined
    setTimeout(function() {
      if (typeof FB === 'undefined') {
        console.log('FB still undefined after timeout, will try to fix buttons.');
        // Direct event listener as backup
        const fbButton = document.getElementById('fb-signup-button');
        if (fbButton) {
          fbButton.addEventListener('click', function() {
            console.log('Facebook button clicked (backup handler)');
            Swal.fire({
              icon: 'error',
              title: 'Signup Error',
              text: 'Facebook signup is not available at the moment. Please try refreshing the page.',
              confirmButtonColor: '#ff3e3e'
            });
          });
        }
      } else {
        // If FB is defined after timeout, set up the button
        setupFBButton();
      }
    }, 3000);
  } else {
    console.log('Facebook SDK loaded successfully');
    // FB is already defined, set up the button right away
    setupFBButton();
  }
  
  // Direct setup for the button
  document.getElementById('fb-signup-button')?.addEventListener('click', function(e) {
    e.preventDefault();
    console.log('Facebook button clicked (direct handler)');
    if (typeof FB === 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Signup Error',
        text: 'Facebook signup is not available at the moment. Please try refreshing the page.',
        confirmButtonColor: '#ff3e3e'
      });
    } else {
      handleFacebookSignup();
    }
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('signupForm');
  const submitButton = form.querySelector('.pp-create-account');
  let formData = new FormData();
  
  // Enhanced validation patterns
  const patterns = {
    full_name: {
      pattern: /^[a-zA-Z\s]{2,50}$/,
      message: 'Name should be 2-50 characters long and contain only letters'
    },
    email: {
      pattern: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
      message: 'Please enter a valid email address'
    },
    phone: {
      pattern: /^\+?[\d\s-]{10,}$/,
      message: 'Phone number should be at least 10 digits'
    },
    password: {
      pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/,
      message: 'Password must contain at least 8 characters, including uppercase, lowercase, number and special character'
    }
  };

  // Real-time validation with debounce
  function debounce(func, wait) {
    let timeout;
    return function(...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, args), wait);
    };
  }

  function validateInput(input) {
    const field = input.closest('.pp-input-field');
    const pattern = patterns[input.name];
    
    if (!pattern) return true; // Skip validation if no pattern defined
    
    const errorSpan = field.querySelector('.pp-error-message') || document.createElement('span');
    errorSpan.className = 'pp-error-message';

    if (!pattern.pattern.test(input.value)) {
      field.classList.add('error');
      field.classList.remove('success');
      errorSpan.textContent = pattern.message;
      errorSpan.classList.add('visible');
      if (!field.querySelector('.pp-error-message')) {
        field.appendChild(errorSpan);
      }
      return false;
    } else {
      field.classList.remove('error');
      field.classList.add('success');
      errorSpan.classList.remove('visible');
      return true;
    }
  }

  // Add real-time validation to all inputs
  form.querySelectorAll('input').forEach(input => {
    input.addEventListener('input', debounce(function() {
      validateInput(this);
      updateProgress();
    }, 300));
  });

  // Password strength meter
  const password = document.getElementById('password');
  const meterBar = form.querySelector('.pp-meter-bar');
  const strengthText = form.querySelector('.pp-strength-text');

  password.addEventListener('input', debounce(function() {
    const result = zxcvbn(this.value);
    const strength = result.score;
    
    meterBar.style.width = `${(strength + 1) * 20}%`;
    meterBar.style.backgroundPosition = `${strength * 25}% 0`;
    
    const texts = ['Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'];
    strengthText.textContent = texts[strength];
  }, 300));

  // Confirm password validation
  const confirmPassword = document.getElementById('confirm_password');
  confirmPassword.addEventListener('input', function() {
    const field = this.closest('.pp-input-field');
    const errorSpan = field.querySelector('.pp-error-message') || document.createElement('span');
    errorSpan.className = 'pp-error-message';

    if (this.value !== password.value) {
      field.classList.add('error');
      field.classList.remove('success');
      errorSpan.textContent = 'Passwords do not match';
      errorSpan.classList.add('visible');
      if (!field.querySelector('.pp-error-message')) {
        field.appendChild(errorSpan);
      }
    } else {
      field.classList.remove('error');
      field.classList.add('success');
      errorSpan.classList.remove('visible');
    }
  });

  // Update progress steps
  function updateProgress() {
    const steps = document.querySelectorAll('.pp-progress-step');
    const inputs = form.querySelectorAll('input');
    let validCount = 0;

    inputs.forEach(input => {
      if (input.closest('.pp-input-field').classList.contains('success')) {
        validCount++;
      }
    });

    const progress = Math.floor((validCount / inputs.length) * steps.length);

    steps.forEach((step, index) => {
      if (index < progress) {
        step.classList.add('completed');
        step.classList.remove('active');
      } else if (index === progress) {
        step.classList.add('active');
        step.classList.remove('completed');
      } else {
        step.classList.remove('active', 'completed');
      }
    });
  }

  // Form submission
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Validate all fields
    let isValid = true;
    form.querySelectorAll('input').forEach(input => {
      if (!validateInput(input)) {
        isValid = false;
      }
    });

    // Additional validation for confirm password
    if (password.value !== confirmPassword.value) {
      isValid = false;
      const field = confirmPassword.closest('.pp-input-field');
      field.classList.add('error');
      const errorSpan = field.querySelector('.pp-error-message') || document.createElement('span');
      errorSpan.className = 'pp-error-message';
      errorSpan.textContent = 'Passwords do not match';
      errorSpan.classList.add('visible');
      if (!field.querySelector('.pp-error-message')) {
        field.appendChild(errorSpan);
      }
    }

    if (!isValid) {
      const firstError = form.querySelector('.pp-input-field.error input');
      if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstError.focus();
      }
      
      Swal.fire({
        title: 'Validation Error',
        text: 'Please check the highlighted fields and try again',
        icon: 'error',
        confirmButtonColor: '#ffbf00'
      });
      
      return;
    }

    // Show loading state
    submitButton.classList.add('loading');
    submitButton.disabled = true;

    try {
      formData = new FormData(form);
      const response = await fetch(form.action, {
        method: 'POST',
        body: formData
      });

      const result = await response.text();
      
      // Check if the response is a redirect
      if (response.redirected) {
        window.location.href = response.url;
        return;
      }

      // Try to parse the response as JSON
      try {
        const jsonResult = JSON.parse(result);
        if (jsonResult.success) {
          Swal.fire({
            icon: 'success',
            title: 'Registration Successful!',
            text: 'Your account has been created successfully. You will be redirected to login.',
            confirmButtonColor: '#28a745',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            window.location.href = 'login.php';
          });
        } else {
          throw new Error(jsonResult.message || 'Registration failed');
        }
      } catch (parseError) {
        // If response is not JSON, check for success status in URL
        if (result.includes('status=success')) {
          Swal.fire({
            icon: 'success',
            title: 'Registration Successful!',
            text: 'Your account has been created successfully. You will be redirected to login.',
            confirmButtonColor: '#28a745',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            window.location.href = 'login.php';
          });
        } else {
          // Extract error message from URL parameters if present
          const urlParams = new URLSearchParams(result);
          const errorMsg = urlParams.get('msg') || 'Something went wrong. Please try again.';
          throw new Error(errorMsg);
        }
      }
    } catch (error) {
      console.error('Signup error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Registration Failed',
        text: error.message || 'Something went wrong. Please try again.',
        confirmButtonColor: '#ff3e3e'
      });
    } finally {
      submitButton.classList.remove('loading');
      submitButton.disabled = false;
    }
  });
});
</script>
</div></body>
</html>
