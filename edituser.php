<?php
include 'header.php';
include 'db.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch user details
    $query = "SELECT full_name, email, phone, profile_photo FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($full_name, $email, $phone, $profile_photo);
    $stmt->fetch();
    $stmt->close();    

} else {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Your Profile | PoolPal</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
<div class="profile-edit-wrapper">
  <div class="profile-edit-container animate__animated animate__fadeIn">
    <div class="profile-edit-header">
      <h1 class="profile-edit-title">Edit Your Profile</h1>
      <p class="profile-edit-subtitle">Update your account information to keep your PoolPal experience personalized</p>
      <div class="header-decoration">
        <div class="decoration-circle"></div>
        <div class="decoration-shape"></div>
      </div>
    </div>

    <div class="profile-edit-content">
      <!-- Profile Preview Card -->
      <div class="profile-preview-card animate__animated animate__fadeInLeft">
        <div class="profile-photo-preview">
          <?php if(!empty($profile_photo)): ?>
            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo" class="preview-photo">
          <?php else: ?>
            <div class="photo-placeholder">
              <i class="fas fa-user-circle"></i>
            </div>
          <?php endif; ?>
        </div>
        <div class="profile-info-preview">
          <h3 class="preview-name"><?php echo htmlspecialchars($full_name); ?></h3>
          <div class="preview-contact">
            <div class="preview-contact-item">
              <i class="fas fa-envelope"></i>
              <span><?php echo htmlspecialchars($email); ?></span>
            </div>
            <div class="preview-contact-item">
              <i class="fas fa-phone"></i>
              <span><?php echo htmlspecialchars($phone); ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Form Section -->
      <div class="profile-edit-form animate__animated animate__fadeInRight">
        <h2 class="form-section-title">Personal Information</h2>

        <form id="editAccountForm" method="POST" action="update_accountu.php" enctype="multipart/form-data">
          <div class="form-group">
            <label for="fullName">Full Name</label>
            <div class="input-container">
              <i class="fas fa-user input-icon"></i>
              <input type="text" id="fullName" value="<?php echo htmlspecialchars($full_name); ?>" readonly>
            </div>
          </div>

          <!-- Upload New Profile Picture -->
          <div class="form-group">
            <label for="profile_photo">Profile Picture</label>
            <div class="file-upload-container">
              <input type="file" id="profile_photo" name="profile_photo" class="file-input" accept="image/*">
              <label for="profile_photo" class="file-upload-label">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Choose a new photo</span>
              </label>
              <div id="file-chosen" class="file-chosen">No file chosen</div>
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <div class="input-container">
              <i class="fas fa-envelope input-icon"></i>
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label for="phone">Phone Number</label>
            <div class="input-container">
              <i class="fas fa-phone input-icon"></i>
              <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="save-button">
              <span>Save Changes</span>
              <i class="fas fa-check"></i>
            </button>
            <a href="profile.php" class="cancel-button-link">
              <button type="button" class="cancel-button">
                <span>Cancel</span>
                <i class="fas fa-times"></i>
              </button>
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
:root {
  --primary: #ffbf00;
  --primary-light: #ffe066;
  --primary-dark: #e6ac00;
  --text-dark: #333333;
  --text-medium: #555555;
  --text-light: #777777;
  --background: #f8f9fa;
  --white: #ffffff;
  --border: #e0e0e0;
  --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 5px 20px rgba(0, 0, 0, 0.08);
  --shadow-hover: 0 10px 30px rgba(0, 0, 0, 0.12);
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 20px;
  --radius-full: 50%;
  --transition: all 0.3s ease;
}

/* Base Styles */
.profile-edit-wrapper {
  font-family: 'Inter', sans-serif;
  background: linear-gradient(120deg, #f8f9fa, #e9ecef);
  min-height: 100vh;
  padding: 40px 20px;
  color: var(--text-dark);
}

.profile-edit-container {
  max-width: 1000px;
  margin: 0 auto;
  background: var(--white);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-md);
}

/* Header Styles */
.profile-edit-header {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: white;
  padding: 40px;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.profile-edit-title {
  font-size: 2.2rem;
  font-weight: 700;
  margin-bottom: 12px;
  position: relative;
  z-index: 1;
}

.profile-edit-subtitle {
  font-size: 1rem;
  opacity: 0.9;
  max-width: 600px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.header-decoration {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.decoration-circle {
  position: absolute;
  top: -50px;
  right: -50px;
  width: 200px;
  height: 200px;
  border-radius: var(--radius-full);
  background: rgba(255, 255, 255, 0.1);
}

.decoration-shape {
  position: absolute;
  bottom: -50px;
  left: -50px;
  width: 200px;
  height: 200px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
}

/* Content Styles */
.profile-edit-content {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 30px;
  padding: 40px;
}

/* Profile Preview Card */
.profile-preview-card {
  background: var(--background);
  border-radius: var(--radius-md);
  padding: 30px;
  text-align: center;
  box-shadow: var(--shadow-sm);
  transition: var(--transition);
  height: fit-content;
}

.profile-preview-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-hover);
}

.profile-photo-preview {
  width: 120px;
  height: 120px;
  border-radius: var(--radius-full);
  margin: 0 auto 20px;
  overflow: hidden;
  border: 4px solid var(--primary);
  box-shadow: 0 4px 15px rgba(255, 191, 0, 0.2);
}

.preview-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-placeholder {
  width: 100%;
  height: 100%;
  background-color: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
  color: #adb5bd;
}

.preview-name {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 15px;
  color: var(--text-dark);
}

.preview-contact {
  text-align: left;
}

.preview-contact-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
  padding: 8px 12px;
  background: rgba(255, 191, 0, 0.05);
  border-radius: var(--radius-sm);
  transition: var(--transition);
}

.preview-contact-item:hover {
  background: rgba(255, 191, 0, 0.1);
  transform: translateX(5px);
}

.preview-contact-item i {
  color: var(--primary);
  margin-right: 12px;
  width: 20px;
  text-align: center;
}

.preview-contact-item span {
  font-size: 0.9rem;
  color: var(--text-medium);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Form Styles */
.profile-edit-form {
  padding: 0 10px;
}

.form-section-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 25px;
  color: var(--text-dark);
  padding-bottom: 15px;
  border-bottom: 2px solid var(--primary-light);
  position: relative;
}

.form-section-title::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -2px;
  width: 60px;
  height: 2px;
  background-color: var(--primary);
}

.form-group {
  margin-bottom: 25px;
}

label {
  display: block;
  font-size: 0.95rem;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--text-dark);
}

.input-container {
  position: relative;
  transition: transform 0.2s ease;
}

.input-container:focus-within {
  transform: translateY(-4px);
}

.input-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--primary);
  font-size: 18px;
}

input[type="text"],
input[type="email"],
input[type="tel"] {
  width: 100%;
  padding: 14px 14px 14px 45px;
  border: 2px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 1rem;
  background-color: var(--white);
  transition: var(--transition);
}

input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(255, 191, 0, 0.2);
}

input[readonly] {
  background-color: #f8f9fa;
  color: var(--text-medium);
  cursor: not-allowed;
}

/* File Upload Styles */
.file-upload-container {
  position: relative;
}

.file-input {
  width: 0.1px;
  height: 0.1px;
  opacity: 0;
  overflow: hidden;
  position: absolute;
  z-index: -1;
}

.file-upload-label {
  display: flex;
  align-items: center;
  padding: 14px 20px;
  background-color: var(--background);
  border: 2px dashed var(--border);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition);
}

.file-upload-label:hover {
  background-color: rgba(255, 191, 0, 0.05);
  border-color: var(--primary);
}

.file-upload-label i {
  margin-right: 10px;
  font-size: 1.2rem;
  color: var(--primary);
}

.file-chosen {
  margin-top: 8px;
  font-size: 0.85rem;
  color: var(--text-light);
  transition: var(--transition);
}

.file-selected {
  color: var(--primary-dark);
  font-weight: 600;
}

/* Button Styles */
.form-actions {
  display: flex;
  gap: 15px;
  margin-top: 35px;
}

.save-button,
.cancel-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 14px 30px;
  border: none;
  border-radius: var(--radius-sm);
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
}

.save-button {
  background-color: var(--primary);
  color: white;
  flex: 1;
  position: relative;
  overflow: hidden;
}

.save-button:hover {
  background-color: var(--primary-dark);
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(255, 191, 0, 0.3);
}

.save-button::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 5px;
  height: 5px;
  background: rgba(255, 255, 255, 0.5);
  opacity: 0;
  border-radius: var(--radius-full);
  transform: scale(1, 1) translate(-50%);
  transform-origin: 50% 50%;
}

.save-button:active::after {
  animation: ripple 1s ease-out;
}

@keyframes ripple {
  0% {
    transform: scale(0, 0);
    opacity: 0.5;
  }
  20% {
    transform: scale(25, 25);
    opacity: 0.5;
  }
  100% {
    opacity: 0;
    transform: scale(40, 40);
  }
}

.cancel-button-link {
  flex: 1;
  text-decoration: none;
}

.cancel-button {
  background-color: transparent;
  color: var(--text-light);
  border: 2px solid var(--border);
  width: 100%;
}

.cancel-button:hover {
  background-color: #f5f5f5;
  color: var(--text-dark);
  transform: translateY(-3px);
}

.save-button i,
.cancel-button i {
  font-size: 0.9rem;
  transition: transform 0.2s ease;
}

.save-button:hover i,
.cancel-button:hover i {
  transform: scale(1.2);
}

/* Loading State */
.btn-loading {
  position: relative;
}

.btn-loading span {
  visibility: hidden;
}

.btn-loading:before {
  content: "";
  position: absolute;
  width: 20px;
  height: 20px;
  top: 50%;
  left: 50%;
  margin-top: -10px;
  margin-left: -10px;
  border-radius: 50%;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Responsive Design */
@media (max-width: 900px) {
  .profile-edit-wrapper {
    padding: 20px 15px;
  }
  
  .profile-edit-content {
    grid-template-columns: 1fr;
    padding: 30px 20px;
  }
  
  .profile-edit-header {
    padding: 30px 20px;
  }
  
  .profile-edit-title {
    font-size: 1.8rem;
  }
  
  .profile-preview-card {
    margin-bottom: 20px;
  }
}

@media (max-width: 600px) {
  .profile-edit-wrapper {
    padding: 0;
  }
  
  .profile-edit-container {
    border-radius: 0;
    box-shadow: none;
  }
  
  .profile-edit-header {
    padding: 25px 15px;
  }
  
  .profile-edit-title {
    font-size: 1.5rem;
  }
  
  .profile-edit-content {
    padding: 20px 15px;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .save-button, 
  .cancel-button {
    width: 100%;
    padding: 12px 20px;
  }
  
  input[type="text"],
  input[type="email"],
  input[type="tel"] {
    padding: 12px 12px 12px 40px;
    font-size: 0.95rem;
  }
  
  .form-section-title {
    font-size: 1.3rem;
  }
}

/* Adding animation for hover effects */
.input-container:hover .input-icon {
  transform: translateY(-50%) scale(1.1);
  color: var(--primary-dark);
}

.profile-photo-preview:hover {
  transform: scale(1.05);
}
</style>

<script>
// File upload preview
const actualFileInput = document.getElementById('profile_photo');
const fileChosen = document.getElementById('file-chosen');

actualFileInput.addEventListener('change', function() {
  if (this.files.length > 0) {
    fileChosen.textContent = this.files[0].name;
    fileChosen.classList.add('file-selected');
    
    // Preview the image if it's an image file
    const file = this.files[0];
    if (file.type.match('image.*')) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const previewContainer = document.querySelector('.profile-photo-preview');
        if (previewContainer) {
          // Replace placeholder with actual image or update existing image
          if (previewContainer.querySelector('.photo-placeholder')) {
            previewContainer.innerHTML = `<img src="${e.target.result}" alt="Profile Preview" class="preview-photo">`;
          } else if (previewContainer.querySelector('img')) {
            previewContainer.querySelector('img').src = e.target.result;
          }
        }
      };
      reader.readAsDataURL(file);
    }
  } else {
    fileChosen.textContent = 'No file chosen';
    fileChosen.classList.remove('file-selected');
  }
});

// Input field animations
const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"]');
inputs.forEach(input => {
  // Focus effects
  input.addEventListener('focus', () => {
    input.parentElement.classList.add('input-focused');
  });
  
  input.addEventListener('blur', () => {
    input.parentElement.classList.remove('input-focused');
  });
  
  // Styling for inputs with values
  if (input.value.trim() !== '') {
    input.classList.add('has-value');
  }
  
  input.addEventListener('input', () => {
    if (input.value.trim() !== '') {
      input.classList.add('has-value');
    } else {
      input.classList.remove('has-value');
    }
  });
});

// Form submit animation
const form = document.getElementById('editAccountForm');
form.addEventListener('submit', function(e) {
  const saveBtn = document.querySelector('.save-button');
  saveBtn.classList.add('btn-loading');
});

// Show success/error messages using SweetAlert
<?php
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) {
        echo "
        Swal.fire({
            icon: 'success',
            title: 'Changes Saved!',
            text: 'Your profile has been updated successfully.',
            confirmButtonColor: '#ffbf00',
            showClass: {
                popup: 'animate__animated animate__fadeIn'
            }
        });
        ";
    } elseif ($_GET['success'] == 0) {
        echo "
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Something went wrong while updating. Please try again.',
            confirmButtonColor: '#ffbf00',
            showClass: {
                popup: 'animate__animated animate__fadeIn'
            }
        });
        ";
    }
}
?>

// Animate elements on scroll
const animateOnScroll = function() {
  const animElements = document.querySelectorAll('.animate__animated:not(.animate__fadeIn)');
  animElements.forEach(element => {
    const elementPosition = element.getBoundingClientRect();
    // If element is in viewport
    if (elementPosition.top < window.innerHeight && elementPosition.bottom >= 0) {
      // Add the animation class based on what's already there
      if (element.classList.contains('animate__fadeInLeft')) {
        element.style.opacity = 1;
      } else if (element.classList.contains('animate__fadeInRight')) {
        element.style.opacity = 1;
      }
    }
  });
};

// Run on page load and scroll
document.addEventListener('DOMContentLoaded', animateOnScroll);
window.addEventListener('scroll', animateOnScroll);
</script>

<!-- Include footer -->
<?php include 'footer.php';?>
</div></body>
</html>
