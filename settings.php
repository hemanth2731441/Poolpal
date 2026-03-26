<?php
include 'header.php';
include 'db.php';

if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
} else {
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Settings | PoolPal</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
<div class="settingspage">
  <div class="settingspage-container animate__animated animate__fadeIn">
    <div class="settingspage-header">
      <h1>Account Settings</h1>
      <p class="settings-intro">Manage your profile and preferences</p>
    </div>

    <div class="settingspage-topcards">
      <div class="settingspage-card animate__animated animate__fadeInUp">
        <div class="card-icon-wrapper">
          <img src="images/icons/account.gif" alt="Account" class="settingspage-tripimage">
        </div>
        <div class="card-content">
          <h2>Account Details</h2>
          <p>Manage your profile information and credentials</p>
          <a href="edit-accountu.php" class="card-button">
            <span>Edit Profile</span>
            <i class="fas fa-chevron-right"></i>
          </a>
        </div>
      </div>

      <div class="settingspage-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
        <div class="card-icon-wrapper">
          <img src="images/icons/not.gif" alt="Notifications" class="settingspage-tripimage">
        </div>
        <div class="card-content">
          <h2>Notifications</h2>
          <p>Customize your email and push notification preferences</p>
          <a href="notificationu.php" class="card-button">
            <span>Manage Alerts</span>
            <i class="fas fa-chevron-right"></i>
          </a>
        </div>
      </div>
    </div>

    <div class="settingspage-section animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
      <div class="section-header">
        <h3>Appearance & Preferences</h3>
        <div class="section-divider"></div>
      </div>

      <div class="settingspage-items">
        <div class="settingspage-item">
          <div class="settingspage-item-left">
            <div class="item-icon">
              <img src="images/icons/mode.png" alt="Theme" class="settingspage-iconsimg">
            </div>
            <div class="item-details">
              <span class="settingspage-title">Theme Mode</span>
              <span class="settingspage-subtitle">Choose light or dark theme</span>
            </div>
          </div>
          <div class="item-action">
            <select class="theme-select" id="theme-select" name="theme_mode">
              <option value="light">Light</option>
              <option value="dark">Dark</option>
              <option value="system">System Default</option>
            </select>
          </div>
        </div>

        <div class="settingspage-item">
          <div class="settingspage-item-left">
            <div class="item-icon">
              <img src="images/icons/pass.png" alt="Privacy" class="settingspage-iconsimg">
            </div>
            <div class="item-details">
              <span class="settingspage-title">Privacy Settings</span>
              <span class="settingspage-subtitle">Manage your privacy preferences</span>
            </div>
          </div>
          <div class="item-action">
            <button class="settings-action-btn">
              <i class="fas fa-sliders-h"></i>
              <span>Configure</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="settingspage-section animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
      <div class="section-header">
        <h3>Security</h3>
        <div class="section-divider"></div>
      </div>

      <div class="settingspage-items">
        <div class="settingspage-item">
          <div class="settingspage-item-left">
            <div class="item-icon">
              <img src="images/icons/password.png" alt="Password" class="settingspage-iconsimg">
            </div>
            <div class="item-details">
              <span class="settingspage-title">Password</span>
              <span class="settingspage-subtitle">Last updated 30 days ago</span>
            </div>
          </div>
          <div class="item-action">
            <a href="edit-accountu.php" class="settings-action-btn">
              <i class="fas fa-key"></i>
              <span>Update</span>
            </a>
          </div>
        </div>

        <div class="settingspage-item">
          <div class="settingspage-item-left">
            <div class="item-icon">
              <img src="images/icons/2f.png" alt="Two-Factor" class="settingspage-iconsimg">
            </div>
            <div class="item-details">
              <span class="settingspage-title">Two-Factor Authentication</span>
              <span class="settingspage-subtitle">Enhanced account security</span>
            </div>
          </div>
          <div class="settingspage-toggle">
            <span class="toggle-label">Enabled</span>
            <div class="toggle-switch-container">
              <input type="checkbox" id="2fa-toggle" class="toggle-input" checked>
              <label for="2fa-toggle" class="toggle-label-switch"></label>
            </div>
          </div>
        </div>
        
        <div class="settingspage-item">
          <div class="settingspage-item-left">
            <div class="item-icon">
              <i class="fas fa-shield-alt"></i>
            </div>
            <div class="item-details">
              <span class="settingspage-title">Login History</span>
              <span class="settingspage-subtitle">Review recent account activity</span>
            </div>
          </div>
          <div class="item-action">
            <button class="settings-action-btn">
              <i class="fas fa-history"></i>
              <span>View</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <div class="settingspage-section animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
      <div class="section-header">
        <h3>App Settings</h3>
        <div class="section-divider"></div>
      </div>

      <div class="settingspage-items">
        <div class="settingspage-item">
          <div class="settingspage-item-left">
            <div class="item-icon">
              <i class="fas fa-globe"></i>
            </div>
            <div class="item-details">
              <span class="settingspage-title">Language</span>
              <span class="settingspage-subtitle">Choose your preferred language</span>
            </div>
          </div>
          <div class="item-action">
            <select class="settings-select" id="language-select" name="language">
              <option value="en">English</option>
              <option value="es">Español</option>
              <option value="fr">Français</option>
            </select>
          </div>
        </div>
        
        <div class="settingspage-item">
          <div class="settingspage-item-left">
            <div class="item-icon">
              <i class="fas fa-bell"></i>
            </div>
            <div class="item-details">
              <span class="settingspage-title">Push Notifications</span>
              <span class="settingspage-subtitle">Receive real-time updates</span>
            </div>
          </div>
          <div class="settingspage-toggle">
            <span class="toggle-label">On</span>
            <div class="toggle-switch-container">
              <input type="checkbox" id="notifications-toggle" class="toggle-input" checked>
              <label for="notifications-toggle" class="toggle-label-switch"></label>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="settingspage-footer animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
      <button class="save-all-btn" id="save-settings" name="save_settings">Save All Changes</button>
      <button class="reset-btn" id="reset-settings" name="reset_settings">Reset to Defaults</button>
    </div>
  </div>
</div>

<style>
:root {
  --primary-color: #ffbf00;
  --primary-light: #ffe066;
  --primary-dark: #e6ac00;
  --secondary-color: #333333;
  --text-color: #333333;
  --text-secondary: #666666;
  --background-color: #f9f9f9;
  --card-bg: #ffffff;
  --border-color: #eaeaea;
  --success-color: #4CAF50;
  --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
  --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
  --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
  --border-radius-sm: 8px;
  --border-radius-md: 12px;
  --border-radius-lg: 16px;
  --transition-fast: 0.2s ease;
  --transition-normal: 0.3s ease;
  --transition-slow: 0.5s ease;
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  background-color: var(--background-color);
  color: var(--text-color);
  font-family: 'Inter', sans-serif;
  line-height: 1.6;
}

.settingspage {
  display: flex;
  justify-content: center;
  padding: 40px 20px;
  min-height: 100vh;
}

.settingspage-container {
  width: 100%;
  max-width: 900px;
  background: var(--card-bg);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
  overflow: hidden;
  padding: 30px;
}

.settingspage-header {
  text-align: center;
  margin-bottom: 40px;
  padding-bottom: 20px;
  position: relative;
}

.settingspage-header:after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 80px;
  height: 4px;
  background: var(--primary-color);
  border-radius: 10px;
}

.settingspage-header h1 {
  font-size: 32px;
  font-weight: 700;
  color: var(--secondary-color);
  margin-bottom: 10px;
}

.settings-intro {
  color: var(--text-secondary);
  font-size: 16px;
}

.settingspage-topcards {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 25px;
  margin-bottom: 40px;
}

.settingspage-card {
  background: var(--card-bg);
  border-radius: var(--border-radius-md);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  transition: transform var(--transition-normal), box-shadow var(--transition-normal);
  display: flex;
  flex-direction: column;
  border: 1px solid var(--border-color);
}

.settingspage-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-lg);
  border-color: var(--primary-light);
}

.card-icon-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
  background: rgba(255, 191, 0, 0.1);
}

.settingspage-tripimage {
  width: 80px;
  height: 80px;
  object-fit: contain;
  transition: transform var(--transition-normal);
}

.settingspage-card:hover .settingspage-tripimage {
  transform: scale(1.05);
}

.card-content {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.settingspage-card h2 {
  font-size: 20px;
  font-weight: 600;
  color: var(--secondary-color);
  margin-bottom: 10px;
}

.settingspage-card p {
  font-size: 14px;
  color: var(--text-secondary);
  margin-bottom: 20px;
  flex: 1;
}

.card-button {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: var(--primary-color);
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: var(--border-radius-sm);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background var(--transition-fast);
  text-decoration: none;
  margin-top: auto;
}

.card-button:hover {
  background-color: var(--primary-dark);
}

.card-button i {
  transition: transform var(--transition-fast);
}

.card-button:hover i {
  transform: translateX(4px);
}

.settingspage-section {
  margin-top: 40px;
  background: var(--card-bg);
  border-radius: var(--border-radius-md);
  padding: 25px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
}

.section-header {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: var(--secondary-color);
  position: relative;
  padding-left: 15px;
}

.section-header h3:before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 5px;
  height: 20px;
  background: var(--primary-color);
  border-radius: 5px;
}

.section-divider {
  flex: 1;
  height: 1px;
  background: var(--border-color);
  margin-left: 15px;
}

.settingspage-items {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.settingspage-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border-radius: var(--border-radius-sm);
  transition: background-color var(--transition-fast);
  border: 1px solid transparent;
}

.settingspage-item:hover {
  background-color: rgba(255, 191, 0, 0.05);
  border-color: var(--border-color);
}

.settingspage-item-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.item-icon {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 45px;
  height: 45px;
  background: rgba(255, 191, 0, 0.15);
  border-radius: 50%;
  transition: background var(--transition-fast);
}

.settingspage-item:hover .item-icon {
  background: rgba(255, 191, 0, 0.3);
}

.settingspage-iconsimg {
  width: 25px;
  height: 25px;
  object-fit: contain;
}

.item-icon i {
  font-size: 18px;
  color: var(--primary-color);
}

.item-details {
  display: flex;
  flex-direction: column;
}

.settingspage-title {
  font-size: 15px;
  font-weight: 600;
  color: var(--text-color);
  display: block;
}

.settingspage-subtitle {
  font-size: 13px;
  color: var(--text-secondary);
}

.settings-action-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background-color: transparent;
  border: 1px solid var(--primary-color);
  color: var(--primary-color);
  padding: 8px 16px;
  border-radius: var(--border-radius-sm);
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all var(--transition-fast);
  text-decoration: none;
}

.settings-action-btn:hover {
  background-color: var(--primary-color);
  color: white;
}

.settings-select, .theme-select {
  padding: 8px 12px;
  border-radius: var(--border-radius-sm);
  border: 1px solid var(--border-color);
  background-color: white;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  cursor: pointer;
  transition: border-color var(--transition-fast);
  min-width: 120px;
}

.settings-select:hover, .theme-select:hover {
  border-color: var(--primary-color);
}

.settingspage-toggle {
  display: flex;
  align-items: center;
  gap: 15px;
}

.toggle-label {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-secondary);
}

.toggle-switch-container {
  position: relative;
}

.toggle-input {
  opacity: 0;
  position: absolute;
  width: 0;
  height: 0;
}

.toggle-label-switch {
  display: block;
  width: 50px;
  height: 26px;
  background: #e0e0e0;
  border-radius: 50px;
  position: relative;
  cursor: pointer;
  transition: background-color var(--transition-fast);
}

.toggle-label-switch:after {
  content: '';
  position: absolute;
  top: 3px;
  left: 3px;
  width: 20px;
  height: 20px;
  background: white;
  border-radius: 50%;
  transition: transform var(--transition-normal), box-shadow var(--transition-fast);
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.toggle-input:checked + .toggle-label-switch {
  background: var(--primary-color);
}

.toggle-input:checked + .toggle-label-switch:after {
  transform: translateX(24px);
  box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.settingspage-footer {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 40px;
}

.save-all-btn, .reset-btn {
  padding: 12px 24px;
  border-radius: var(--border-radius-sm);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.save-all-btn {
  background-color: var(--primary-color);
  color: white;
  border: none;
}

.save-all-btn:hover {
  background-color: var(--primary-dark);
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.reset-btn {
  background-color: transparent;
  color: var(--text-secondary);
  border: 1px solid var(--border-color);
}

.reset-btn:hover {
  background-color: #f5f5f5;
  color: var(--text-color);
}

/* Responsive Design */
@media (max-width: 900px) {
  .settingspage-container {
    padding: 25px;
  }
  
  .settingspage-header h1 {
    font-size: 28px;
  }
}

@media (max-width: 768px) {
  .settingspage-topcards {
    grid-template-columns: 1fr;
  }
  
  .settingspage-section {
    padding: 20px;
  }
  
  .settingspage-footer {
    flex-direction: column;
  }
  
  .save-all-btn, .reset-btn {
    width: 100%;
  }
}

@media (max-width: 576px) {
  .settingspage {
    padding: 20px 10px;
  }
  
  .settingspage-container {
    padding: 20px 15px;
  }
  
  .settingspage-header h1 {
    font-size: 24px;
  }
  
  .settings-intro {
    font-size: 14px;
  }
  
  .settingspage-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .item-action, .settingspage-toggle {
    width: 100%;
    justify-content: flex-end;
  }
  
  .toggle-switch-container {
    margin-left: auto;
  }
}
</style>

<script>
// Theme selector
document.querySelector('.theme-select').addEventListener('change', function() {
  const selectedTheme = this.value;
  // Apply theme change logic here
  console.log('Theme changed to:', selectedTheme);
});

// Toggle switches
document.querySelectorAll('.toggle-input').forEach(toggleInput => {
  toggleInput.addEventListener('change', function() {
    const toggleLabel = this.closest('.settingspage-toggle').querySelector('.toggle-label');
    toggleLabel.textContent = this.checked ? 'Enabled' : 'Disabled';
    
    // Add animation when toggling
    const labelSwitch = this.nextElementSibling;
    labelSwitch.classList.add('animate__animated', 'animate__pulse');
    setTimeout(() => {
      labelSwitch.classList.remove('animate__animated', 'animate__pulse');
    }, 500);
  });
});

// Action buttons hover effects
document.querySelectorAll('.settings-action-btn').forEach(btn => {
  btn.addEventListener('mouseenter', function() {
    this.classList.add('animate__animated', 'animate__pulse');
  });
  
  btn.addEventListener('mouseleave', function() {
    this.classList.remove('animate__animated', 'animate__pulse');
  });
});

// Add ripple effect to buttons
function createRipple(event) {
  const button = event.currentTarget;
  
  const circle = document.createElement('span');
  const diameter = Math.max(button.clientWidth, button.clientHeight);
  const radius = diameter / 2;
  
  circle.style.width = circle.style.height = `${diameter}px`;
  circle.style.left = `${event.clientX - (button.getBoundingClientRect().left + radius)}px`;
  circle.style.top = `${event.clientY - (button.getBoundingClientRect().top + radius)}px`;
  circle.classList.add('ripple');
  
  const ripple = button.querySelector('.ripple');
  if (ripple) {
    ripple.remove();
  }
  
  button.appendChild(circle);
}

document.querySelectorAll('.card-button, .save-all-btn, .reset-btn, .settings-action-btn').forEach(button => {
  button.addEventListener('click', createRipple);
});

// Save button functionality
document.querySelector('.save-all-btn').addEventListener('click', function() {
  // Add saving animation
  this.classList.add('animate__animated', 'animate__bounce');
  this.textContent = 'Saving...';
  
  // Simulate saving process
  setTimeout(() => {
    this.classList.remove('animate__animated', 'animate__bounce');
    this.classList.add('animate__animated', 'animate__tada');
    this.textContent = 'Changes Saved!';
    
    setTimeout(() => {
      this.classList.remove('animate__animated', 'animate__tada');
      this.textContent = 'Save All Changes';
    }, 2000);
  }, 1500);
});

// Intersection Observer for scroll animations
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.settingspage-section').forEach(section => {
  section.style.opacity = '0';
  section.style.transform = 'translateY(20px)';
  section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
  observer.observe(section);
});

// Add this CSS for the ripple effect
const style = document.createElement('style');
style.textContent = `
  .ripple {
    position: absolute;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    transform: scale(0);
    animation: ripple 0.6s linear;
    pointer-events: none;
  }
  
  @keyframes ripple {
    to {
      transform: scale(4);
      opacity: 0;
    }
  }
  
  .card-button, .save-all-btn, .reset-btn, .settings-action-btn {
    position: relative;
    overflow: hidden;
  }
`;

document.head.appendChild(style);
</script>
<br><?php include 'footer.php';?>
</div></body>
</html>
