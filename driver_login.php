<?php include_once 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Page</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php
// Display error messages
if (isset($_GET['error'])) {
  $error = $_GET['error'];
  $errorMsg = '';
  
  if ($error == 'invalid_password') {
    $errorMsg = 'Invalid password. Please try again.';
  } elseif ($error == 'user_not_found') {
    $errorMsg = 'User not found. Please check your email.';
  } elseif ($error == 'not_verified') {
    $errorMsg = 'Your account is waiting for admin verification. Please try again later.';
  } elseif ($error == 'account_suspended') {
    $errorMsg = 'Your account has been suspended. Please contact support for assistance.';
  }
  
  if (!empty($errorMsg)) {
    echo "<script>
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '$errorMsg',
        confirmButtonColor: '#ffbf00'
      });
    </script>";
  }
}
?>

<div class="separate">
  <div class="lp-container">
    <h2>Rider's Login</h2>
    <br />
    <form method="POST" action="driver_login_action.php">
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
          <input type="password" name="password" placeholder="Enter your password" />
          <i class="fas fa-lock"></i>
          <i class="fas fa-eye password-toggle" data-target="password-field"></i>
        </div>
      </div>

      <a href="forgot-password.php" class="lp-forgot-password">Forgot password?</a>

      <br />
      <div class="lp-profile-photo">
        <div class="lp-photo-box">
          <div class="lp-text-content">
            <span class="lp-title">Remember me</span>
          </div>
          <div class="lp-toggle-container">
            <label class="lp-toggle">
              <input type="checkbox" />
              <span class="lp-toggle-switch"></span>
            </label>
          </div>
        </div>
      </div>

      <button type="submit" class="lp-login-btn">Login</button>
      
      <div class="lp-signup-text">
        Not a member yet? <a href="select_vehicle_type.php">Sign up</a>
      </div>
    </form>
  </div>
</div>
  <style>
.separate {
  font-family: 'Poppins', sans-serif;
  background-color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
  min-height: 60vh;
}

.lp-container {
  width: 100%;
  max-width: 350px;
  text-align: center;
  padding: 20px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  background-color: #fff;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.lp-container:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.lp-container h2 {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 5px;
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
  transition: color 0.3s ease;
}

.lp-input-field {
  position: relative;
  margin-bottom: 5px;
}

.lp-input-field input {
  width: 100%;
  padding: 12px;
  background: #f9f9f9;
  border: 1px solid #eee;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  padding-right: 65px;
  box-sizing: border-box;
  transition: all 0.3s ease;
}

.lp-input-field input:focus {
  border-color: #ffbf00;
  box-shadow: 0 0 0 3px rgba(255, 191, 0, 0.1);
  background: #fff;
}

.lp-input-field i {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  color: #888;
  transition: color 0.3s ease;
}

.lp-input-field i.fas.fa-lock, .lp-input-field i.fas.fa-envelope {
  left: auto;
  right: 12px;
}

.lp-input-field i.password-toggle {
  right: 38px;
  cursor: pointer;
  color: #aaa;
}

.lp-input-field i.password-toggle:hover {
  color: #ffbf00;
}

.lp-input-field input:focus + i {
  color: #ffbf00;
}

.lp-forgot-password {
  font-size: 14px;
  color: #6C49F4;
  text-decoration: none;
  display: block;
  text-align: right;
  margin-top: 5px;
  transition: color 0.3s;
  position: relative;
  overflow: hidden;
}

/* No underline effect */
.lp-forgot-password:after {
  display: none;
}

.lp-forgot-password:hover {
  color: #ffbf00;
}

.lp-profile-photo {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: white;
  padding: 0px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.lp-photo-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}

.lp-text-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.lp-title {
  font-size: 14px;
  color: #000;
  font-weight: 500;
}

.lp-toggle-container {
  display: flex;
  justify-content: flex-end;
}

.lp-toggle {
  position: relative;
  display: inline-block;
}

.lp-toggle-switch {
  width: 36px;
  height: 18px;
  background: #ddd;
  border-radius: 9px;
  position: relative;
  cursor: pointer;
  display: inline-block;
  transition: background 0.3s;
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
  transition: left 0.3s;
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
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.lp-login-btn:hover {
  background: #e6ac00;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(255, 191, 0, 0.3);
}

.lp-login-btn:active {
  transform: translateY(0);
}

.lp-or-text {
  font-size: 14px;
  color: gray;
  margin: 10px 0;
}

.lp-social-buttons {
  display: flex;
  gap: 10px;
}

.lp-social-buttons button {
  flex: 1;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
  cursor: pointer;
  font-size: 14px;
}

.lp-google {
  background: white;
}

.lp-facebook {
  background: #3b5998;
  color: white;
}

.lp-signup-text {
  font-size: 15px;
  margin-top: 20px;
  font-weight: 500;
  color: #555;
  position: relative;
  padding-top: 15px;
}

.lp-signup-text:before {
  content: '';
  position: absolute;
  top: 0;
  left: 25%;
  width: 50%;
  height: 1px;
  background: #eee;
}

.lp-signup-text a {
  color: #6C49F4;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  position: relative;
}

/* No underline effect */
.lp-signup-text a:after {
  display: none;
}

.lp-signup-text a:hover {
  color: #ffbf00;
}

/* Responsive styles */
@media (max-width: 480px) {
  .separate {
    padding: 15px;
  }
  
  .lp-container {
    padding: 15px;
  }
  
  .lp-container h2 {
    font-size: 20px;
  }
  
  .lp-input-field input {
    padding: 10px 35px 10px 10px;
  }
  
  .lp-login-btn {
    padding: 10px;
    font-size: 15px;
  }
}

@media (max-width: 320px) {
  .lp-container {
    padding: 10px;
  }
  
  .lp-container h2 {
    font-size: 18px;
  }
  
  .lp-title {
    font-size: 13px;
  }
}

  </style>

<script>
// Add password toggle functionality
document.addEventListener('DOMContentLoaded', function() {
  const toggles = document.querySelectorAll('.password-toggle');
  
  toggles.forEach(toggle => {
    toggle.addEventListener('click', function() {
      const passwordField = this.parentElement.querySelector('input[type="password"], input[type="text"]');
      
      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        this.classList.remove('fa-eye');
        this.classList.add('fa-eye-slash');
      } else {
        passwordField.type = 'password';
        this.classList.remove('fa-eye-slash');
        this.classList.add('fa-eye');
      }
    });
  });

  // Add input focus animation
  const inputs = document.querySelectorAll('input');
  
  inputs.forEach(input => {
    input.addEventListener('focus', function() {
      this.parentElement.classList.add('input-focused');
    });
    
    input.addEventListener('blur', function() {
      this.parentElement.classList.remove('input-focused');
    });
  });
});
</script>

<br><?php include  'footer.php';?>
</body>
</html>
