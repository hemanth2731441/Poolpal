<?php
include 'header.php';
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Notification Alerts</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <script src="https://kit.fontawesome.com/yourfontawesomekit.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
<div class="notification-container">
  <div class="notification-wrapper animate__animated animate__fadeIn">
    <div class="notification-header">
      <h1>Notification Preferences</h1>
      <p class="subtext">Customize your alert settings for a better experience</p>
    </div>

    <div class="notification-card">
      <div class="section">
        <h2><span class="section-icon">✉️</span> Email Alerts</h2>

        <div class="alert-item" data-aos="fade-up">
          <div class="icon-box">
            <img src="images/icons/Trip.gif" alt="Trip Updates" class="iconss-img">
          </div>
          <div class="alert-text">
            <h3>Trip Updates</h3>
            <p>Get notified about ride status changes</p>
          </div>
          <div class="toggle-switch">
            <input type="checkbox" id="trip-updates" name="trip_updates" class="toggle-input">
            <label for="trip-updates" class="toggle-label"></label>
          </div>
        </div>

        <div class="alert-item" data-aos="fade-up" data-aos-delay="100">
          <div class="icon-box">
            <img src="images/icons/Promotions.gif" alt="Promotions" class="iconss-img">
          </div>
          <div class="alert-text">
            <h3>Promotions</h3>
            <p>Receive special offers and discounts</p>
          </div>
          <div class="toggle-switch">
            <input type="checkbox" id="promotions" name="promotions" class="toggle-input">
            <label for="promotions" class="toggle-label"></label>
          </div>
        </div>

        <div class="alert-item" data-aos="fade-up" data-aos-delay="200">
          <div class="icon-box">
            <img src="images/icons/accounts.gif" alt="Account Activity" class="iconss-img">
          </div>
          <div class="alert-text">
            <h3>Account Activity</h3>
            <p>Security and login alerts</p>
          </div>
          <div class="toggle-switch">
            <input type="checkbox" id="account-activity" name="account_activity" class="toggle-input">
            <label for="account-activity" class="toggle-label"></label>
          </div>
        </div>
      </div>

      <div class="section-divider"></div>

      <div class="section">
        <h2><span class="section-icon">🔔</span> Push Notifications</h2>

        <div class="alert-item" data-aos="fade-up">
          <div class="icon-box">
            <img src="images/icons/not.gif" alt="Ride Reminders" class="iconss-img">
          </div>
          <div class="alert-text">
            <h3>Ride Reminders</h3>
            <p>Get reminders before your trip starts</p>
          </div>
          <div class="toggle-switch">
            <input type="checkbox" id="ride-reminders" name="ride_reminders" class="toggle-input">
            <label for="ride-reminders" class="toggle-label"></label>
          </div>
        </div>

        <div class="alert-item" data-aos="fade-up" data-aos-delay="100">
          <div class="icon-box">
            <img src="images/icons/message.gif" alt="Driver Messages" class="iconss-img">
          </div>
          <div class="alert-text">
            <h3>Driver Messages</h3>
            <p>Receive messages from your driver</p>
          </div>
          <div class="toggle-switch">
            <input type="checkbox" id="driver-messages" name="driver_messages" class="toggle-input">
            <label for="driver-messages" class="toggle-label"></label>
          </div>
        </div>

        <div class="alert-item" data-aos="fade-up" data-aos-delay="200">
          <div class="icon-box">
            <img src="images/icons/system.gif" alt="System Announcements" class="iconss-img">
          </div>
          <div class="alert-text">
            <h3>System Announcements</h3>
            <p>Important updates from PoolPal</p>
          </div>
          <div class="toggle-switch">
            <input type="checkbox" id="system-announcements" name="system_announcements" class="toggle-input">
            <label for="system-announcements" class="toggle-label"></label>
          </div>
        </div>
      </div>

      <div class="button-group">
        <a href="settings.php">
          <button class="cancel-btn">
            <span class="btn-icon">←</span>
            <span class="btn-text">Back</span>
          </button>
        </a>
        <button class="save-btn" id="saveButton">
          <span class="btn-icon">✓</span>
          <span class="btn-text">Save Changes</span>
        </button>
      </div>
    </div>
  </div>
</div>

<style>
:root {
  --primary-color: #ffbf00;
  --primary-dark: #e6ac00;
  --primary-light: #ffdb75;
  --secondary-color: #333;
  --text-primary: #333;
  --text-secondary: #666;
  --background-light: #fff;
  --background-grey: #f8f8f8;
  --border-color: #eaeaea;
  --shadow-color: rgba(0, 0, 0, 0.1);
}

body {
  font-family: 'Inter', sans-serif;
  margin: 0;
  padding: 0;
  background-color: var(--background-grey);
  color: var(--text-primary);
}

.notification-container {
  display: flex;
  justify-content: center;
  padding: 40px 20px;
}

.notification-wrapper {
  width: 100%;
  max-width: 850px;
}

.notification-header {
  text-align: center;
  margin-bottom: 30px;
}

.notification-header h1 {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 8px;
  color: var(--secondary-color);
  position: relative;
  display: inline-block;
}

.notification-header h1:after {
  content: '';
  position: absolute;
  width: 60px;
  height: 4px;
  background-color: var(--primary-color);
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  border-radius: 2px;
}

.subtext {
  font-size: 16px;
  color: var(--text-secondary);
  margin-top: 20px;
}

.notification-card {
  background-color: var(--background-light);
  border-radius: 16px;
  box-shadow: 0 10px 30px var(--shadow-color);
  padding: 30px;
  margin-bottom: 20px;
  transition: all 0.3s ease;
}

.section {
  margin-bottom: 40px;
}

.section-divider {
  height: 1px;
  background: linear-gradient(to right, transparent, var(--border-color), transparent);
  margin: 40px 0;
}

.section h2 {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 25px;
  color: var(--secondary-color);
  display: flex;
  align-items: center;
}

.section-icon {
  margin-right: 10px;
  font-size: 20px;
}

.alert-item {
  display: flex;
  align-items: center;
  padding: 16px;
  margin-bottom: 16px;
  border-radius: 12px;
  background-color: var(--background-light);
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.alert-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.icon-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  margin-right: 15px;
  border-radius: 12px;
  padding: 10px;
}

.iconss-img {
  width: 40px;
  height: 40px;
  object-fit: contain;
  transition: transform 0.3s ease;
}

.alert-item:hover .iconss-img {
  transform: scale(1.1);
}

.alert-text {
  flex-grow: 1;
  padding-right: 15px;
}

.alert-text h3 {
  font-size: 17px;
  font-weight: 600;
  margin-bottom: 5px;
  color: var(--text-primary);
}

.alert-text p {
  font-size: 14px;
  color: var(--text-secondary);
  margin: 0;
}

.toggle-switch {
  position: relative;
  width: 50px;
  height: 26px;
}

.toggle-input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-label {
  position: absolute;
  cursor: pointer;
  background-color: #e0e0e0;
  border-radius: 34px;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  transition: background-color 0.4s;
}

.toggle-label:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  border-radius: 50%;
  transition: transform 0.4s, box-shadow 0.2s;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.toggle-input:checked + .toggle-label {
  background-color: var(--primary-color);
}

.toggle-input:checked + .toggle-label:before {
  transform: translateX(24px);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

.button-group {
  display: flex;
  gap: 15px;
  margin-top: 20px;
  justify-content: flex-end;
}

.button-group a {
  text-decoration: none;
}

.cancel-btn, .save-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px 24px;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
}

.btn-icon {
  margin-right: 8px;
}

.cancel-btn {
  background-color: #f0f0f0;
  color: #555;
}

.cancel-btn:hover {
  background-color: #e0e0e0;
  transform: translateY(-2px);
}

.save-btn {
  background-color: var(--primary-color);
  color: white;
  box-shadow: 0 4px 12px rgba(255, 191, 0, 0.3);
}

.save-btn:hover {
  background-color: var(--primary-dark);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(255, 191, 0, 0.4);
}

/* Animation for save button */
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.save-btn:focus {
  animation: pulse 0.5s ease-in-out;
}

/* Responsive Styles */
@media screen and (max-width: 768px) {
  .notification-card {
    padding: 20px;
  }
  
  .notification-header h1 {
    font-size: 28px;
  }
  
  .alert-item {
    flex-direction: row;
    padding: 12px;
  }
  
  .icon-box {
    width: 50px;
    height: 50px;
    margin-right: 12px;
  }
  
  .iconss-img {
    width: 32px;
    height: 32px;
  }
  
  .alert-text h3 {
    font-size: 16px;
  }
  
  .button-group {
    flex-direction: column-reverse;
    gap: 10px;
  }
  
  .save-btn, .cancel-btn {
    width: 100%;
  }
}

@media screen and (max-width: 480px) {
  .notification-container {
    padding: 20px 10px;
  }
  
  .notification-card {
    padding: 15px;
    border-radius: 12px;
  }
  
  .notification-header h1 {
    font-size: 24px;
  }
  
  .subtext {
    font-size: 14px;
  }
  
  .section h2 {
    font-size: 18px;
  }
  
  .alert-item {
    padding: 10px;
  }
  
  .icon-box {
    width: 40px;
    height: 40px;
  }
  
  .iconss-img {
    width: 28px;
    height: 28px;
  }
  
  .alert-text h3 {
    font-size: 15px;
  }
  
  .alert-text p {
    font-size: 13px;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Add animation to alert items
  const alertItems = document.querySelectorAll('.alert-item');
  alertItems.forEach((item, index) => {
    setTimeout(() => {
      item.classList.add('animate__animated', 'animate__fadeInUp');
    }, index * 100);
  });
  
  // Toggle switch animation
  const toggles = document.querySelectorAll('.toggle-input');
  toggles.forEach(toggle => {
    toggle.addEventListener('change', function() {
      const alertItem = this.closest('.alert-item');
      if (this.checked) {
        alertItem.style.borderLeft = '4px solid var(--primary-color)';
      } else {
        alertItem.style.borderLeft = 'none';
      }
    });
  });
  
  // Save button animation
  const saveButton = document.getElementById('saveButton');
  saveButton.addEventListener('click', function(e) {
    e.preventDefault();
    this.innerHTML = '<span class="btn-icon">✓</span><span class="btn-text">Saved!</span>';
    this.classList.add('animate__animated', 'animate__pulse');
    setTimeout(() => {
      this.innerHTML = '<span class="btn-icon">✓</span><span class="btn-text">Save Changes</span>';
      this.classList.remove('animate__animated', 'animate__pulse');
    }, 2000);
  });
});
</script>
<br><?php include 'footer.php';?>
</div></body>
</html>
