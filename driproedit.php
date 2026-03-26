<?php
include 'nav.php';
include 'db.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch user details
    $query = "SELECT full_name, email, contact, address, vehicle_name, vehicle_number, profile_pic, vehicle_color, vehicle_type FROM drivers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($full_name, $email, $contact, $address, $vehicle_name, $vehicle_number, $profile_pic, $vehicle_color, $vehicle_type);
    $stmt->fetch();
    $stmt->close();    

} else {
    header("Location: driver_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Driver Profile | PoolPal</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="driver-edit-wrapper">
  <div class="driver-edit-container animate__animated animate__fadeIn">
    <div class="driver-edit-header">
      <h1 class="driver-edit-title">Edit Your Profile</h1>
      <p class="driver-edit-subtitle">Update your details to keep your information current</p>
    </div>

    <div class="driver-edit-content">
      <!-- Profile Preview Section -->
      <div class="driver-profile-preview animate__animated animate__fadeInLeft">
        <div class="profile-image-container">
          <?php if (!empty($profile_pic)): ?>
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-image">
          <?php else: ?>
            <div class="profile-image-placeholder">
              <i class="fas fa-user"></i>
            </div>
          <?php endif; ?>
        </div>
        <div class="profile-name"><?php echo htmlspecialchars($full_name); ?></div>
        <div class="profile-info">
          <div class="profile-info-item">
            <i class="fas fa-envelope"></i>
            <span><?php echo htmlspecialchars($email); ?></span>
          </div>
          <div class="profile-info-item">
            <i class="fas fa-phone"></i>
            <span><?php echo htmlspecialchars($contact); ?></span>
          </div>
          <div class="profile-info-item">
            <i class="fas fa-car-side"></i>
            <span><?php echo htmlspecialchars($vehicle_name); ?></span>
          </div>
          <div class="profile-info-item">
            <i class="fas fa-car"></i>
            <span><?php echo htmlspecialchars($vehicle_number); ?></span>
          </div>
        </div>
      </div>

      <!-- Edit Form Section -->
      <div class="driver-edit-form-container animate__animated animate__fadeInRight">
        <form id="editAccountForm" method="POST" action="update_account.php" enctype="multipart/form-data">
          <div class="form-group">
            <label for="fullName">Full Name</label>
            <div class="input-container">
              <i class="fas fa-user input-icon"></i>
              <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($full_name); ?>" readonly>
            </div>
          </div>

          <div class="form-group">
            <label for="profile_pic">Profile Picture</label>
            <div class="file-upload-container">
              <input type="file" id="profile_pic" name="profile_pic" class="file-input" accept="image/*">
              <label for="profile_pic" class="file-upload-label">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Choose a file</span>
              </label>
              <div class="file-name" id="file-name">No file chosen</div>
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
            <label for="contact">Phone Number</label>
            <div class="input-container">
              <i class="fas fa-phone input-icon"></i>
              <input type="tel" id="contact" name="contact" value="<?php echo htmlspecialchars($contact); ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label for="address">Address</label>
            <div class="input-container">
              <i class="fas fa-map-marker-alt input-icon"></i>
              <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label for="vehicle_name">Vehicle Name</label>
            <div class="input-container">
              <i class="fas fa-car-side input-icon"></i>
              <input type="text" id="vehicle_name" name="vehicle_name" value="<?php echo htmlspecialchars($vehicle_name); ?>" required placeholder="e.g. Toyota Camry, Honda Civic">
            </div>
          </div>

          <div class="form-group">
            <label for="vehicle_color">Vehicle Color</label>
            <div class="input-container">
              <i class="fas fa-palette input-icon"></i>
              <input type="text" id="vehicle_color" name="vehicle_color" value="<?php echo htmlspecialchars($vehicle_color); ?>" required placeholder="e.g. Black, White, Silver">
            </div>
          </div>

          <div class="form-group">
            <label for="vehicle_type">Vehicle Type</label>
            <div class="input-container">
              <i class="fas fa-truck input-icon"></i>
              <select id="vehicle_type" name="vehicle_type" required class="form-select">
                <optgroup label="Car">
                  <option value="Car-Pooling" <?php echo ($vehicle_type == 'Car-Pooling') ? 'selected' : ''; ?>>Car Pooling</option>
                  <option value="Car-Taxi" <?php echo ($vehicle_type == 'Car-Taxi') ? 'selected' : ''; ?>>Car Taxi</option>
                </optgroup>
                <option value="Bike" <?php echo ($vehicle_type == 'Bike') ? 'selected' : ''; ?>>Bike</option>
                <option value="Auto Rickshaw" <?php echo ($vehicle_type == 'Auto Rickshaw') ? 'selected' : ''; ?>>Auto Rickshaw</option>
                <optgroup label="Goods Vehicle">
                  <option value="Goods-7ft" <?php echo ($vehicle_type == 'Goods-7ft') ? 'selected' : ''; ?>>7ft Vehicle</option>
                  <option value="Goods-8ft" <?php echo ($vehicle_type == 'Goods-8ft') ? 'selected' : ''; ?>>8ft Vehicle</option>
                  <option value="Goods-3Wheeler" <?php echo ($vehicle_type == 'Goods-3Wheeler') ? 'selected' : ''; ?>>3 Wheeler Cargo</option>
                  <option value="Goods-Tata407" <?php echo ($vehicle_type == 'Goods-Tata407') ? 'selected' : ''; ?>>Tata 407</option>
                </optgroup>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="vehicle_number">Vehicle Number</label>
            <div class="input-container">
              <i class="fas fa-car input-icon"></i>
              <input type="text" id="vehicle_number" name="vehicle_number" value="<?php echo htmlspecialchars($vehicle_number); ?>" required>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="save-btn">
              <span>Save Changes</span>
              <i class="fas fa-check-circle"></i>
            </button>
            <a href="driverprofile.php" class="cancel-link">
              <button type="button" class="cancel-btn">
                <span>Cancel</span>
                <i class="fas fa-times-circle"></i>
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
  --primary-light: #ffd54f;
  --primary-dark: #e6ac00;
  --secondary: #3498db;
  --secondary-light: #5dade2;
  --text-dark: #333333;
  --text-medium: #555555;
  --text-light: #777777;
  --bg-light: #ffffff;
  --bg-gray: #f8f9fa;
  --border-light: #e0e0e0;
  --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.06);
  --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.08);
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 20px;
  --transition: all 0.3s ease;
}

.driver-edit-wrapper {
  font-family: 'Inter', sans-serif;
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  min-height: 100vh;
  padding: 40px 20px;
  color: var(--text-dark);
}

.driver-edit-container {
  max-width: 1000px;
  margin: 0 auto;
  background-color: var(--bg-light);
  border-radius: var(--radius-lg);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
  overflow: hidden;
  position: relative;
}

.driver-edit-header {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  padding: 40px;
  text-align: center;
  color: white;
  position: relative;
  overflow: hidden;
}

.driver-edit-header::before {
  content: "";
  position: absolute;
  width: 200px;
  height: 200px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  top: -100px;
  right: -50px;
}

.driver-edit-header::after {
  content: "";
  position: absolute;
  width: 150px;
  height: 150px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  bottom: -70px;
  left: -50px;
}

.driver-edit-title {
  font-size: 2.2rem;
  font-weight: 700;
  margin-bottom: 12px;
  position: relative;
  z-index: 1;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.driver-edit-subtitle {
  font-size: 1rem;
  opacity: 0.9;
  max-width: 600px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.driver-edit-content {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 30px;
  padding: 40px;
}

/* Profile Preview Section */
.driver-profile-preview {
  background-color: var(--bg-gray);
  padding: 30px;
  border-radius: var(--radius-md);
  text-align: center;
  height: fit-content;
  box-shadow: var(--shadow-sm);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  position: relative;
  z-index: 1;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.driver-profile-preview:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.profile-image-container {
  width: 130px;
  height: 130px;
  margin: 0 auto 20px;
  border-radius: 50%;
  overflow: hidden;
  border: 4px solid var(--primary);
  box-shadow: 0 4px 15px rgba(255, 191, 0, 0.3);
  position: relative;
}

.profile-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.profile-image:hover {
  transform: scale(1.05);
}

.profile-image-placeholder {
  width: 100%;
  height: 100%;
  background-color: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
  color: #adb5bd;
}

.profile-name {
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 20px;
  color: var(--text-dark);
  position: relative;
  display: inline-block;
}

.profile-name:after {
  content: '';
  position: absolute;
  width: 50%;
  height: 3px;
  background: var(--primary);
  bottom: -8px;
  left: 25%;
  border-radius: 2px;
}

.profile-info {
  text-align: left;
}

.profile-info-item {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
  padding: 10px 15px;
  background-color: rgba(255, 191, 0, 0.05);
  border-radius: var(--radius-sm);
  transition: var(--transition);
  border-left: 3px solid transparent;
  max-width: 100%;
  overflow: hidden;
}

.profile-info-item:hover {
  transform: translateX(5px);
  background-color: rgba(255, 191, 0, 0.1);
  border-left: 3px solid var(--primary);
}

.profile-info-item i {
  color: var(--primary);
  margin-right: 12px;
  width: 20px;
  text-align: center;
  flex-shrink: 0;
}

.profile-info-item span {
  font-size: 0.9rem;
  color: var(--text-medium);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  min-width: 0;
  flex: 1;
}

/* Form Styles */
.driver-edit-form-container {
  padding: 0 10px;
  max-width: 100%;
}

.form-group {
  margin-bottom: 25px;
  position: relative;
  max-width: 100%;
}

label {
  display: block;
  font-size: 0.95rem;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--text-dark);
  transition: var(--transition);
}

.input-container {
  position: relative;
  transition: transform 0.2s ease;
  max-width: 100%;
}

.input-container:focus-within {
  transform: translateY(-4px);
}

.input-container:focus-within label {
  color: var(--primary);
}

.input-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--primary);
  font-size: 16px;
  transition: var(--transition);
}

input[type="text"],
input[type="email"],
input[type="tel"] {
  width: 100%;
  padding: 14px 14px 14px 45px;
  border: 2px solid var(--border-light);
  border-radius: var(--radius-sm);
  font-size: 1rem;
  background-color: var(--bg-light);
  transition: var(--transition);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
  overflow: hidden;
  text-overflow: ellipsis;
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

/* Select Field Styles */
.form-select {
  width: 100%;
  padding: 14px 14px 14px 45px;
  border: 2px solid var(--border-light);
  border-radius: var(--radius-sm);
  font-size: 1rem;
  background-color: var(--bg-light);
  transition: var(--transition);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  cursor: pointer;
}

.form-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(255, 191, 0, 0.2);
}

.form-select optgroup {
  font-weight: 600;
  color: var(--text-dark);
}

.form-select option {
  padding: 8px;
  font-weight: normal;
}

.input-container:has(.form-select)::after {
  content: '\f0d7';
  font-family: 'Font Awesome 6 Free';
  font-weight: 900;
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-medium);
  pointer-events: none;
  transition: var(--transition);
}

.input-container:has(.form-select:focus)::after {
  color: var(--primary);
  transform: translateY(-50%) rotate(180deg);
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
  background-color: var(--bg-gray);
  border: 2px dashed var(--border-light);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition);
}

.file-upload-label:hover {
  background-color: #f0f0f0;
  border-color: var(--primary);
}

.file-upload-label i {
  margin-right: 10px;
  font-size: 1.2rem;
  color: var(--primary);
}

.file-name {
  margin-top: 8px;
  font-size: 0.85rem;
  color: var(--text-light);
  padding: 5px 10px;
  background-color: rgba(255, 191, 0, 0.05);
  border-radius: 4px;
  display: inline-block;
}

.file-name.has-file {
  color: var(--primary-dark);
  font-weight: 600;
  background-color: rgba(255, 191, 0, 0.1);
}

/* Button Styles */
.form-actions {
  display: flex;
  gap: 15px;
  margin-top: 30px;
}

.save-btn,
.cancel-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 30px;
  border: none;
  border-radius: var(--radius-sm);
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.save-btn {
  background-color: var(--primary);
  color: white;
  flex: 1;
}

.save-btn:hover {
  background-color: var(--primary-dark);
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(255, 191, 0, 0.3);
}

.cancel-link {
  text-decoration: none;
  flex: 1;
}

.cancel-btn {
  background-color: transparent;
  color: var(--text-medium);
  border: 2px solid var(--border-light);
  width: 100%;
}

.cancel-btn:hover {
  background-color: #f5f5f5;
  color: var(--text-dark);
  transform: translateY(-3px);
}

.save-btn i,
.cancel-btn i {
  font-size: 0.9rem;
  transition: transform 0.2s ease;
}

.save-btn:hover i,
.cancel-btn:hover i {
  transform: scale(1.2);
}

/* Loading State */
.btn-loading {
  position: relative;
}

.btn-loading span {
  visibility: hidden;
}

.btn-loading:after {
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
  .driver-edit-content {
    grid-template-columns: 1fr;
    padding: 30px 20px;
    max-width: 100%;
  }
  
  .driver-edit-header {
    padding: 30px 20px;
  }
  
  .driver-edit-title {
    font-size: 1.8rem;
  }

  .driver-profile-preview {
    max-width: 100%;
    overflow: hidden;
  }
}

@media (max-width: 600px) {
  .driver-edit-wrapper {
    padding: 0;
    width: 100%;
    overflow-x: hidden;
  }
  
  .driver-edit-container {
    border-radius: 0;
    box-shadow: none;
    width: 100%;
    max-width: 100vw;
    overflow-x: hidden;
  }
  
  .driver-edit-content {
    padding: 20px 15px;
  }

  .profile-info {
    max-width: 100%;
  }

  .input-container {
    max-width: 100%;
  }

  input[type="text"],
  input[type="email"],
  input[type="tel"] {
    max-width: 100%;
    font-size: 14px;
  }
}

/* Animations */
@keyframes ripple {
  0% {
    transform: scale(0);
    opacity: 0.5;
  }
  100% {
    transform: scale(4);
    opacity: 0;
  }
}

.save-btn:before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 20px;
  height: 20px;
  background-color: rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  transform: translate(-50%, -50%) scale(0);
  opacity: 0;
}

.save-btn:active:before {
  animation: ripple 0.6s ease-out;
}

/* Enhanced Input Animation */
.input-container:focus-within .input-icon {
  color: var(--primary-dark);
  transform: translateY(-50%) scale(1.1);
}

/* Elegant Form UI Enhancements */
.form-group:before {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background: linear-gradient(90deg, var(--primary), var(--primary-light));
  transition: width 0.3s ease;
}

.form-group:focus-within:before {
  width: 100%;
}

input::placeholder {
  color: #bbb;
  font-style: italic;
}

/* Card hover effects */
.driver-profile-preview:before {
  content: '';
  position: absolute;
  top: -2px;
  left: -2px;
  right: -2px;
  bottom: -2px;
  background: linear-gradient(45deg, var(--primary), transparent 60%);
  z-index: -1;
  border-radius: calc(var(--radius-md) + 2px);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.driver-profile-preview:hover:before {
  opacity: 0.5;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // File upload preview
  const fileInput = document.getElementById('profile_pic');
  const fileName = document.getElementById('file-name');
  
  fileInput.addEventListener('change', function() {
    if (this.files && this.files.length > 0) {
      fileName.textContent = this.files[0].name;
      fileName.classList.add('has-file');
      
      // Preview image if available
      const profilePreview = document.querySelector('.profile-image-container');
      if (profilePreview) {
        const file = this.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
          const img = profilePreview.querySelector('img') || document.createElement('img');
          img.src = e.target.result;
          img.classList.add('profile-image');
          
          if (!profilePreview.querySelector('img')) {
            profilePreview.innerHTML = '';
            profilePreview.appendChild(img);
          }
        };
        
        reader.readAsDataURL(file);
      }
    } else {
      fileName.textContent = 'No file chosen';
      fileName.classList.remove('has-file');
    }
  });
  
  // Form submission animation
  const form = document.getElementById('editAccountForm');
  form.addEventListener('submit', function() {
    const saveBtn = document.querySelector('.save-btn');
    saveBtn.classList.add('btn-loading');
  });
  
  // Input field animations
  const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"]');
  inputs.forEach(input => {
    // Add focus effects
    input.addEventListener('focus', function() {
      this.parentElement.classList.add('input-focused');
    });
    
    input.addEventListener('blur', function() {
      this.parentElement.classList.remove('input-focused');
    });
    
    // Add validation styling
    input.addEventListener('input', function() {
      if (this.value.trim() !== '') {
        this.classList.add('has-value');
      } else {
        this.classList.remove('has-value');
      }
    });
    
    // Trigger input for initial state
    if (input.value.trim() !== '') {
      input.classList.add('has-value');
    }
  });
  
  // Enhanced animations
  const formGroups = document.querySelectorAll('.form-group');
  formGroups.forEach((group, index) => {
    group.style.animationDelay = `${index * 0.1}s`;
    group.classList.add('animate__animated', 'animate__fadeInUp');
  });
});

// SweetAlert for success/error messages
<?php
if (isset($_GET['success']) && $_GET['success'] == 1) {
?>
Swal.fire({
  icon: 'success',
  title: 'Profile Updated!',
  text: 'Your profile has been successfully updated.',
  confirmButtonColor: '#ffbf00',
  timer: 3000,
  timerProgressBar: true,
  showClass: {
    popup: 'animate__animated animate__fadeInDown'
  },
  hideClass: {
    popup: 'animate__animated animate__fadeOutUp'
  }
});
<?php
}
?>
</script>
<br><?php include 'footer.php';?>
</body>
</html>
