<?php
include 'nav.php';
include 'db.php';

if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
  $sql = "SELECT languages FROM drivers WHERE id = '$user_id'";
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($result);
  $currentLanguage = $row['languages'];
} else {
  header("Location: driver_login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Settings</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
<div class="settingspage">
<div class="settingspage-container">
  <h1>Settings</h1>

  <div class="settingspage-topcards">
    <div class="settingspage-card">
      <div class="card-icon">
        <i class="fas fa-user-circle"></i>
      </div>
      <h2>Account</h2>
      <p>Change your password or language.</p>
      <a href="edit-account.php">
        <button class="btn-primary">Edit Account</button>
      </a>
    </div>

    <div class="settingspage-card">
      <div class="card-icon">
        <i class="fas fa-bell"></i>
      </div>
      <h2>Notifications</h2>
      <p>Manage email and push alerts.</p>
      <a href="notification.php">
        <button class="btn-primary">Edit Alerts</button>
      </a>
    </div>
  </div>

  <div class="settingspage-section">
    <h3>Preferences</h3>
    
    <div class="settingspage-item">
      <div class="settingspage-item-left">
        <div class="item-icon">
          <i class="fas fa-language"></i>
        </div>
        <div>
          <span class="settingspage-title">Languages</span>
          <span class="settingspage-subtitle"><?php echo htmlspecialchars($currentLanguage); ?></span>
        </div>
      </div>
      <a href="edit-account.php" class="edit-button"><i class="fas fa-pen"></i></a>
    </div>

    <div class="settingspage-item">
      <div class="settingspage-item-left">
        <div class="item-icon">
          <i class="fas fa-moon"></i>
        </div>
        <div>
          <span class="settingspage-title">Theme</span>
          <span class="settingspage-subtitle">Light</span>
        </div>
      </div>
      <div class="edit-button" id="theme-toggle"><i class="fas fa-pen"></i></div>
    </div>

    <div class="settingspage-item">
      <div class="settingspage-item-left">
        <div class="item-icon">
          <i class="fas fa-shield-alt"></i>
        </div>
        <div>
          <span class="settingspage-title">Privacy</span>
          <span class="settingspage-subtitle">Standard</span>
        </div>
      </div>
      <div class="edit-button"><i class="fas fa-pen"></i></div>
    </div>
  </div>

  <div class="settingspage-section">
    <h3>Security</h3>

    <div class="settingspage-item">
      <div class="settingspage-item-left">
        <div class="item-icon">
          <i class="fas fa-key"></i>
        </div>
        <div>
          <span class="settingspage-title">Password</span>
          <span class="settingspage-subtitle">Click to update</span>
        </div>
      </div>
      <a href="edit-account.php" class="edit-button"><i class="fas fa-pen"></i></a>
    </div>

    <div class="settingspage-item">
      <div class="settingspage-item-left">
        <div class="item-icon">
          <i class="fas fa-lock"></i>
        </div>
        <div>
          <span class="settingspage-title">Two-Factor Auth</span>
          <span class="settingspage-subtitle">Enabled</span>
        </div>
      </div>
      <div class="settingspage-toggle">
        <label class="switch">
          <input type="checkbox" checked>
          <span class="slider round"></span>
        </label>
      </div>
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
  --radius: 12px;
  --transition: all 0.3s ease;
}

.settingspage {
  font-family: 'Inter', sans-serif;
  background: var(--background);
  display: flex;
  justify-content: center;
  padding: 40px 20px;
  color: var(--text-dark);
}

.settingspage-container {
  width: 100%;
  max-width: 800px;
  animation: fadeIn 0.5s ease-out;
}

.settingspage-container h1 {
  font-size: 28px;
  font-weight: 600;
  margin-bottom: 30px;
  position: relative;
}

.settingspage-container h1::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 40px;
  height: 3px;
  background-color: var(--primary);
  border-radius: 2px;
}

.settingspage-topcards {
  display: flex;
  gap: 24px;
  margin-bottom: 40px;
}

.settingspage-card {
  flex: 1;
  background-color: var(--background);
  border-radius: var(--radius);
  padding: 24px;
  position: relative;
  box-shadow: 0 8px 16px var(--shadow);
  transition: var(--transition);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.settingspage-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
}

.card-icon {
  width: 70px;
  height: 70px;
  background: var(--primary-light);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.card-icon i {
  font-size: 32px;
  color: var(--primary);
}

.settingspage-card h2 {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 10px;
}

.settingspage-card p {
  font-size: 14px;
  color: var(--text-light);
  margin-bottom: 20px;
  line-height: 1.5;
}

.btn-primary {
  background-color: var(--primary);
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: var(--radius);
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
  width: 100%;
}

.btn-primary:hover {
  background-color: var(--primary-dark);
  transform: scale(1.02);
}

.settingspage-section {
  background-color: var(--surface);
  border-radius: var(--radius);
  padding: 24px;
  margin-top: 40px;
  box-shadow: 0 4px 12px var(--shadow);
  transition: var(--transition);
}

.settingspage-section:hover {
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
}

.settingspage-section h3 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 24px;
  color: var(--text-dark);
  position: relative;
  padding-bottom: 10px;
}

.settingspage-section h3::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 30px;
  height: 2px;
  background-color: var(--primary);
  border-radius: 2px;
}

.settingspage-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid var(--border);
  transition: var(--transition);
}

.settingspage-item:last-child {
  border-bottom: none;
}

.settingspage-item:hover {
  background-color: rgba(255, 191, 0, 0.03);
}

.settingspage-item-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.item-icon {
  width: 40px;
  height: 40px;
  background: var(--primary-light);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.item-icon i {
  font-size: 16px;
  color: var(--primary);
}

.settingspage-title {
  font-size: 15px;
  font-weight: 500;
  color: var(--text-dark);
  display: block;
  margin-bottom: 4px;
}

.settingspage-subtitle {
  font-size: 13px;
  color: var(--text-light);
}

.edit-button {
  width: 36px;
  height: 36px;
  background-color: var(--surface);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition);
}

.edit-button:hover {
  background-color: var(--primary-light);
}

.edit-button i {
  font-size: 14px;
  color: var(--primary);
}

.settingspage-toggle {
  display: flex;
  align-items: center;
}

.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #e0e0e0;
  border-radius: 34px;
  transition: var(--transition);
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  border-radius: 50%;
  transition: var(--transition);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.switch input:checked + .slider {
  background-color: var(--primary);
}

.switch input:checked + .slider:before {
  transform: translateX(20px);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Responsive Styles */
@media (max-width: 768px) {
  .settingspage-topcards {
    flex-direction: column;
  }
  
  .settingspage-card {
    width: 100%;
  }
  
  .settingspage-container h1 {
    font-size: 24px;
  }
}

@media (max-width: 480px) {
  .settingspage {
    padding: 20px 10px;
  }
  
  .settingspage-container h1 {
    font-size: 22px;
  }
  
  .settingspage-section h3 {
    font-size: 16px;
  }
  
  .settingspage-item-left {
    gap: 12px;
  }
  
  .item-icon {
    width: 36px;
    height: 36px;
  }
  
  .settingspage-title {
    font-size: 14px;
  }
  
  .settingspage-subtitle {
    font-size: 12px;
  }
}
</style>

<script>
// Toggle Switch Animation
document.querySelectorAll('.switch').forEach(switchEl => {
  switchEl.addEventListener('click', () => {
    const input = switchEl.querySelector('input');
    if (!input.checked) {
      input.checked = true;
    } else {
      input.checked = false;
    }
  });
});

// Theme toggle effect
document.getElementById('theme-toggle').addEventListener('click', function() {
  // This is just a visual demo - actual theme switching would need more logic
  this.classList.toggle('active');
  const subtitleEl = this.parentElement.querySelector('.settingspage-subtitle');
  if (subtitleEl.textContent === 'Light') {
    subtitleEl.textContent = 'Dark';
  } else {
    subtitleEl.textContent = 'Light';
  }
});

// Add subtle hover animations to all items
document.querySelectorAll('.settingspage-item').forEach(item => {
  item.addEventListener('mouseenter', function() {
    this.style.paddingLeft = '8px';
  });
  
  item.addEventListener('mouseleave', function() {
    this.style.paddingLeft = '0px';
  });
});
</script>
</div>
<?php include 'footer.php';?>
</div></body>
</html>
