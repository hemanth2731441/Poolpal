<?php
ob_start();
include 'nav.php';
include 'db.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT password, languages FROM drivers WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $currentPassword = $row['password'];
    $currentLanguage = $row['languages'];
} else {
    header("Location: driver_login.php");
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
            header("Location: edit-account.php?mismatch=1");
            exit;
        }
    }

    // Update language if field is filled
    if (!empty($_POST['new_languages'])) {
        $selectedLanguages = $_POST['new_languages'];
        $newLanguages = mysqli_real_escape_string($conn, implode(', ', $selectedLanguages));
        $updateFields[] = "languages = '$newLanguages'";
    }

    // Perform update if at least one field is to be updated
    if (!empty($updateFields)) {
        $updateQuery = "UPDATE drivers SET " . implode(", ", $updateFields) . " WHERE id = '$user_id'";
        if (mysqli_query($conn, $updateQuery)) {
            header("Location: edit-account.php?success=1");
            exit;
        } else {
            header("Location: edit-account.php?error=1");
            exit;
        }
    } else {
        header("Location: edit-account.php?nochange=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Account</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
<div class="edit-account-wrapper">
  <div class="edit-account-page">
    <div class="page-header">
      <h1>Edit Account</h1>
      <p class="subtext">Update your account details below.</p>
    </div>

    <div class="section">
      <div class="section-header">
        <i class="fas fa-user-cog"></i>
        <h2>Change Password & Language</h2>
      </div>
      <form method="POST" action="">
        <div class="form-group">
          <label>Current Password</label>
          <div class="input-wrapper">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" name="current_password" value="<?php echo htmlspecialchars($currentPassword); ?>" readonly>
          </div>
        </div>

        <div class="form-group">
          <label>New Password</label>
          <div class="input-wrapper">
            <i class="fas fa-key input-icon"></i>
            <input type="password" name="new_password" placeholder="Enter new password" id="password-field">
            <span class="toggle-password">
              <i class="fas fa-eye"></i>
            </span>
          </div>
        </div>

        <div class="form-group">
          <label>Confirm New Password</label>
          <div class="input-wrapper">
            <i class="fas fa-check input-icon"></i>
            <input type="password" name="confirm_password" placeholder="Re-enter new password" id="confirm-password-field">
            <span class="toggle-password">
              <i class="fas fa-eye"></i>
            </span>
          </div>
          <div class="password-match-indicator">
            <span class="indicator-icon"><i class="fas fa-circle-check"></i></span>
            <span class="indicator-text">Passwords match</span>
          </div>
        </div>

        <div class="language-section">
          <div class="section-divider">
            <span>Language Settings</span>
          </div>
          
          <div class="form-group">
            <label>Current Languages</label>
            <div class="input-wrapper">
              <i class="fas fa-language input-icon"></i>
              <input type="text" name="current_language" value="<?php echo htmlspecialchars($currentLanguage); ?>" readonly>
            </div>
          </div>

          <div class="form-group">
            <label>Select Languages (Choose multiple)</label>
            <div class="language-checkboxes">
              <?php
              $allLanguages = [
                  'English', 'Hindi', 'Tamil', 'Telugu', 'Bengali', 
                  'Marathi', 'Gujarati', 'Kannada', 'Malayalam', 
                  'Punjabi', 'Odia', 'Urdu', 'Sanskrit', 'Spanish', 
                  'French', 'German', 'Italian', 'Portuguese', 
                  'Chinese', 'Japanese', 'Arabic'
              ];
              $currentLanguagesArray = array_map('trim', explode(',', $currentLanguage));
              
              foreach ($allLanguages as $language) {
                  $isChecked = in_array($language, $currentLanguagesArray) ? 'checked' : '';
                  echo '<div class="language-checkbox-item">';
                  echo '<input type="checkbox" name="new_languages[]" value="' . htmlspecialchars($language) . '" id="lang_' . htmlspecialchars($language) . '" ' . $isChecked . '>';
                  echo '<label for="lang_' . htmlspecialchars($language) . '">' . htmlspecialchars($language) . '</label>';
                  echo '</div>';
              }
              ?>
            </div>
          </div>
        </div>

        <div class="button-group">
          <a href="usersetting.php" class="cancel-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Back</span>
          </a>
          <button type="submit" name="save_changes" class="save-btn">
            <i class="fas fa-save"></i>
            <span>Save Changes</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
:root {
  --primary: #ffbf00;
  --primary-dark: #e6ac00;
  --primary-light: #fff6d9;
  --text-dark: #333;
  --text-medium: #666;
  --text-light: #888;
  --background: #fff;
  --surface: #f9f9f9;
  --border: #eaeaea;
  --shadow: rgba(0, 0, 0, 0.05);
  --shadow-dark: rgba(0, 0, 0, 0.1);
  --success: #4CAF50;
  --error: #f44336;
  --radius: 12px;
  --transition: all 0.3s ease;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.edit-account-wrapper {
  font-family: 'Inter', sans-serif;
  background: #fff;
  display: flex;
  justify-content: center;
  padding: 40px 20px;
  color: var(--text-dark);
}

.edit-account-page {
  width: 100%;
  max-width: 600px;
  animation: fadeIn 0.5s ease-out;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  font-size: 28px;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--text-dark);
  position: relative;
  display: inline-block;
}

.page-header h1::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 40px;
  height: 3px;
  background-color: var(--primary);
  border-radius: 2px;
}

.subtext {
  font-size: 14px;
  color: var(--text-light);
  margin-top: 16px;
}

.section {
  background-color: var(--surface);
  border-radius: var(--radius);
  padding: 30px;
  box-shadow: 0 8px 16px var(--shadow);
  transition: var(--transition);
  margin-bottom: 40px;
}

.section:hover {
  box-shadow: 0 12px 20px var(--shadow-dark);
}

.section-header {
  display: flex;
  align-items: center;
  margin-bottom: 25px;
  gap: 12px;
}

.section-header i {
  font-size: 20px;
  color: var(--primary);
}

.section-header h2 {
  font-size: 20px;
  font-weight: 600;
  color: var(--text-dark);
}

.form-group {
  margin-bottom: 24px;
  position: relative;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 8px;
  color: var(--text-dark);
}

.input-wrapper {
  position: relative;
  transition: var(--transition);
}

.input-wrapper:focus-within {
  transform: translateY(-2px);
}

.input-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-light);
  font-size: 16px;
  transition: var(--transition);
}

.input-wrapper:focus-within .input-icon {
  color: var(--primary);
}

.input-wrapper input,
.input-wrapper select {
  width: 100%;
  padding: 14px 14px 14px 42px;
  border: 1px solid var(--border);
  background-color: var(--background);
  border-radius: var(--radius);
  font-size: 14px;
  color: var(--text-dark);
  transition: var(--transition);
}

.input-wrapper select {
  appearance: none;
  background-image: url('data:image/svg+xml;charset=US-ASCII,<svg width="16" height="16" fill="gray" xmlns="http://www.w3.org/2000/svg"><path d="M4 6l4 4 4-4z"/></svg>');
  background-repeat: no-repeat;
  background-position: right 14px center;
  background-size: 16px 16px;
  padding-right: 40px;
}

.input-wrapper input:focus,
.input-wrapper select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(255, 191, 0, 0.2);
}

.input-wrapper input::placeholder {
  color: #ccc;
}

.input-wrapper input[readonly] {
  background-color: #f5f5f5;
  color: var(--text-medium);
  cursor: not-allowed;
  border-color: #eee;
}

.toggle-password {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-light);
  cursor: pointer;
  transition: var(--transition);
}

.toggle-password:hover {
  color: var(--primary);
}

.password-match-indicator {
  display: none;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
  font-size: 12px;
}

.password-match-indicator.visible {
  display: flex;
}

.password-match-indicator.match .indicator-icon {
  color: var(--success);
}

.password-match-indicator.mismatch .indicator-icon {
  color: var(--error);
}

.password-match-indicator.match .indicator-text {
  color: var(--success);
}

.password-match-indicator.mismatch .indicator-text {
  color: var(--error);
}

.section-divider {
  display: flex;
  align-items: center;
  margin: 30px 0;
  color: var(--text-light);
}

.section-divider::before,
.section-divider::after {
  content: "";
  flex: 1;
  height: 1px;
  background-color: var(--border);
}

.section-divider span {
  padding: 0 16px;
  font-size: 14px;
  font-weight: 500;
}

.language-section {
  animation: fadeIn 0.5s ease-out;
}

.button-group {
  display: flex;
  gap: 16px;
  margin-top: 32px;
}

.save-btn,
.cancel-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 24px;
  border-radius: var(--radius);
  font-size: 14px;
  font-weight: 500;
  border: none;
  cursor: pointer;
  transition: var(--transition);
  width: 100%;
}

.save-btn {
  background-color: var(--primary);
  color: #fff;
}

.save-btn:hover {
  background-color: var(--primary-dark);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.cancel-btn {
  background-color: #f5f5f5;
  color: var(--text-medium);
  text-decoration: none;
}

.cancel-btn:hover {
  background-color: #e9e9e9;
  color: var(--text-dark);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Responsive Styles */
@media (max-width: 768px) {
  .edit-account-page {
    padding: 20px;
  }
  
  .section {
    padding: 24px;
  }
  
  .page-header h1 {
    font-size: 24px;
  }
}

@media (max-width: 480px) {
  .edit-account-wrapper {
    padding: 20px 10px;
  }
  
  .section {
    padding: 20px;
  }
  
  .page-header h1 {
    font-size: 22px;
  }
  
  .section-header h2 {
    font-size: 18px;
  }
  
  .button-group {
    flex-direction: column;
  }
  
  .input-wrapper input,
  .input-wrapper select {
    padding: 12px 12px 12px 38px;
  }
}

.language-checkboxes {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px;
    padding: 15px;
    background-color: var(--background);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    margin-top: 10px;
}

.language-checkbox-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 5px;
}

.language-checkbox-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--primary);
}

.language-checkbox-item label {
    font-size: 14px;
    color: var(--text-dark);
    cursor: pointer;
}

.language-checkbox-item:hover {
    background-color: var(--primary-light);
    border-radius: 4px;
    transition: background-color 0.2s ease;
}
</style>

<script>
// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach((toggle, index) => {
  toggle.addEventListener('click', function() {
    const passwordField = index === 0 ? 
      document.getElementById('password-field') : 
      document.getElementById('confirm-password-field');
      
    if (passwordField.type === 'password') {
      passwordField.type = 'text';
      this.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
      passwordField.type = 'password';
      this.innerHTML = '<i class="fas fa-eye"></i>';
    }
    
    // Add animation
    this.style.transform = 'rotate(180deg)';
    setTimeout(() => {
      this.style.transform = 'rotate(0deg)';
    }, 300);
  });
});

// Password match indicator
const passwordField = document.getElementById('password-field');
const confirmPasswordField = document.getElementById('confirm-password-field');
const indicator = document.querySelector('.password-match-indicator');

function checkPasswordMatch() {
  const password = passwordField.value;
  const confirmPassword = confirmPasswordField.value;
  
  if (confirmPassword.length > 0) {
    indicator.classList.add('visible');
    
    if (password === confirmPassword) {
      indicator.classList.add('match');
      indicator.classList.remove('mismatch');
      indicator.querySelector('.indicator-text').textContent = 'Passwords match';
    } else {
      indicator.classList.add('mismatch');
      indicator.classList.remove('match');
      indicator.querySelector('.indicator-text').textContent = 'Passwords do not match';
    }
  } else {
    indicator.classList.remove('visible');
  }
}

passwordField.addEventListener('input', checkPasswordMatch);
confirmPasswordField.addEventListener('input', checkPasswordMatch);

// Form field animations
document.querySelectorAll('.form-group').forEach(group => {
  const input = group.querySelector('input, select');
  
  input.addEventListener('focus', () => {
    group.classList.add('active');
  });
  
  input.addEventListener('blur', () => {
    group.classList.remove('active');
  });
});

// Add hover effect to section
const section = document.querySelector('.section');
section.addEventListener('mouseenter', function() {
  this.style.transform = 'translateY(-5px)';
});

section.addEventListener('mouseleave', function() {
  this.style.transform = 'translateY(0)';
});

// Simulate form submission loading
document.querySelector('.save-btn').addEventListener('click', function() {
  // Don't interfere with the actual form submission processing
  // Just add a visual effect for better user experience
  this.classList.add('submitting');
  this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Saving...</span>';
  
  // The form will submit normally, and the PHP logic will handle the redirect
});

// Add validation for language selection
document.querySelector('form').addEventListener('submit', function(e) {
    const selectedLanguages = document.querySelectorAll('input[name="new_languages[]"]:checked');
    if (selectedLanguages.length === 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Language Required',
            text: 'Please select at least one language.',
            confirmButtonColor: '#ffbf00'
        });
    }
});
</script>

<?php if (isset($_GET['success'])): ?>
<script>
Swal.fire({
  icon: 'success',
  title: 'Success!',
  text: 'Your changes have been saved.',
  confirmButtonColor: '#ffbf00',
  showClass: {
    popup: 'animate__animated animate__fadeInDown'
  },
  hideClass: {
    popup: 'animate__animated animate__fadeOutUp'
  }
});
</script>
<?php elseif (isset($_GET['error'])): ?>
<script>
Swal.fire({
  icon: 'error',
  title: 'Error!',
  text: 'Failed to update your details. Please try again.',
  confirmButtonColor: '#ffbf00',
  showClass: {
    popup: 'animate__animated animate__fadeInDown'
  },
  hideClass: {
    popup: 'animate__animated animate__fadeOutUp'
  }
});
</script>
<?php elseif (isset($_GET['mismatch'])): ?>
<script>
Swal.fire({
  icon: 'warning',
  title: 'Password Mismatch',
  text: 'New Password and Confirm Password do not match.',
  confirmButtonColor: '#ffbf00',
  showClass: {
    popup: 'animate__animated animate__fadeInDown'
  },
  hideClass: {
    popup: 'animate__animated animate__fadeOutUp'
  }
});
</script>
<?php endif; ?>

<?php include 'footer.php';?>
</div></body>
</html>
<?php
ob_end_flush();
?>
