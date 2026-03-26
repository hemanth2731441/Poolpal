<?php
include 'nav.php';
include 'db.php';  // Assuming you have a database connection setup here

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];  // Assuming you store user ID in the session
    // Query the database to get the user's name
    $query = "SELECT full_name FROM drivers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($full_name);
    $stmt->fetch();
    $stmt->close();
} else {
    // If the user is not logged in, redirect to the login page
    header("Location: driver_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email, contact, profile_pic, vehicle_number, vehicle_name FROM drivers WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If vehicle_name is not found in database, set a placeholder
if (!isset($user['vehicle_name'])) {
    $user['vehicle_name'] = 'Not provided';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Captain Profile Overview | PoolPal</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
<div class="driver-profile-wrapper">
  <div class="driver-profile-container animate__animated animate__fadeIn">
    <h1 class="profile-main-title">Profile Overview</h1>

    <div class="profile-cards-grid">
      <div class="profile-card animate__animated animate__fadeInUp">
        <div class="card-icon"><i class="fas fa-user-circle"></i></div>
        <h2>Personal Info</h2>
        <p>View and update your name, email, contact details, and more</p>
        <a href="driproedit.php" class="no-underline">
          <button class="profile-action-btn edit-btn">
            <span>Edit Profile</span>
            <i class="fas fa-chevron-right"></i>
          </button>
        </a>
      </div>

      <div class="profile-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
        <div class="card-icon"><i class="fas fa-headset"></i></div>
        <h2>Captain Support</h2>
        <p>Need help with your account or vehicle? Contact our support team for assistance.</p>
        <a href="https://wa.me/919999999999" target="_blank" class="no-underline">
          <button class="profile-action-btn whatsapp-btn">
            <span>Contact via WhatsApp</span>
            <i class="fab fa-whatsapp"></i>
          </button>
        </a>
      </div>
    </div>

    <div class="profile-section animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
      <h2 class="section-title">Account Details</h2>

      <div class="detail-item">
        <div class="detail-icon">
          <img class="profile-avatar" src="<?php echo htmlspecialchars(!empty($user['profile_pic']) ? $user['profile_pic'] : 'images/avatar-placeholder.png'); ?>" alt="Profile Picture">
        </div>
        <div class="detail-content">
          <h3>Name</h3>
          <p><?php echo htmlspecialchars($user['full_name']); ?></p>
        </div>
      </div>

      <div class="detail-item">
        <div class="detail-icon">
          <i class="fas fa-envelope"></i>
        </div>
        <div class="detail-content">
          <h3>Email</h3>
          <p><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
      </div>

      <div class="detail-item">
        <div class="detail-icon">
          <i class="fas fa-phone-alt"></i>
        </div>
        <div class="detail-content">
          <h3>Phone</h3>
          <p><?php echo htmlspecialchars($user['contact']); ?></p>
        </div>
      </div>
    </div>

    <div class="profile-section animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
      <h2 class="section-title">Captain Profile</h2>

      <div class="detail-item">
        <div class="detail-icon">
          <i class="fas fa-car-side"></i>
        </div>
        <div class="detail-content">
          <h3>Vehicle Name</h3>
          <p><?php echo htmlspecialchars($user['vehicle_name']); ?></p>
        </div>
      </div>

      <div class="detail-item">
        <div class="detail-icon">
          <i class="fas fa-car"></i>
        </div>
        <div class="detail-content">
          <h3>Vehicle Number Plate</h3>
          <p><?php echo htmlspecialchars($user['vehicle_number']); ?></p>
        </div>
      </div>

      <div class="detail-item">
        <div class="detail-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="detail-content">
          <h3>Driver Status</h3>
          <p class="status-verified">Verified</p>
        </div>
      </div>
    </div>

    <div class="back-btn-wrapper animate__animated animate__fadeInUp" style="animation-delay: 0.5s">
      <button class="back-btn" onclick="goBack()">
        <i class="fas fa-arrow-left"></i>
        <span>Back</span>
      </button>
    </div>
  </div>
</div>

<style>
:root {
  --primary: #ffbf00;
  --primary-light: #ffe180;
  --primary-dark: #e6ac00;
  --dark: #333333;
  --light: #ffffff;
  --gray: #f5f5f5;
  --text: #4a4a4a;
  --text-light: #767676;
  --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
  --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
  --transition: all 0.3s ease;
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 20px;
}

.driver-profile-wrapper {
  font-family: 'Inter', sans-serif;
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  min-height: 100vh;
  padding: 40px 20px;
  color: var(--text);
}

.driver-profile-container {
  max-width: 900px;
  margin: 0 auto;
  background: var(--light);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  padding: 40px;
  position: relative;
  overflow: hidden;
}

.driver-profile-container::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 350px;
  height: 350px;
  background: radial-gradient(circle, rgba(255, 191, 0, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
  z-index: 0;
  border-radius: 50%;
}

.profile-main-title {
  font-size: 2.2rem;
  font-weight: 700;
  margin-bottom: 30px;
  color: var(--dark);
  position: relative;
  display: inline-block;
}

.profile-main-title::after {
  content: '';
  position: absolute;
  bottom: -6px;
  left: 0;
  width: 40px;
  height: 4px;
  background-color: var(--primary);
  border-radius: 2px;
}

.profile-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.profile-card {
  background: linear-gradient(to bottom right, var(--gray), #fff);
  border-radius: var(--radius-md);
  padding: 30px;
  transition: var(--transition);
  border: 1px solid rgba(0, 0, 0, 0.05);
  position: relative;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.profile-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: var(--primary);
  opacity: 0;
  transition: var(--transition);
}

.profile-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
}

.profile-card:hover::before {
  opacity: 1;
}

.card-icon {
  font-size: 2rem;
  color: var(--primary);
  margin-bottom: 15px;
}

.profile-card h2 {
  font-size: 1.3rem;
  font-weight: 600;
  margin-bottom: 10px;
  color: var(--dark);
}

.profile-card p {
  font-size: 0.95rem;
  color: var(--text-light);
  margin-bottom: 20px;
  line-height: 1.5;
}

.no-underline {
  text-decoration: none;
}

.profile-action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 20px;
  background: var(--primary);
  color: var(--light);
  border: none;
  border-radius: var(--radius-sm);
  font-weight: 500;
  font-size: 0.9rem;
  cursor: pointer;
  transition: var(--transition);
  width: 100%;
  box-shadow: 0 2px 10px rgba(255, 191, 0, 0.2);
}

.profile-action-btn:hover {
  background: var(--primary-dark);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 191, 0, 0.25);
}

.profile-action-btn i {
  font-size: 0.8rem;
  transition: var(--transition);
}

.profile-action-btn:hover i {
  transform: translateX(3px);
}

.whatsapp-btn {
  background: #25d366;
  box-shadow: 0 2px 10px rgba(37, 211, 102, 0.2);
}

.whatsapp-btn:hover {
  background: #1fba57;
  box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
}

.profile-section {
  background: var(--light);
  border-radius: var(--radius-md);
  padding: 30px;
  margin-bottom: 30px;
  box-shadow: var(--shadow-sm);
  border: 1px solid rgba(0, 0, 0, 0.05);
  position: relative;
  overflow: hidden;
}

.profile-section::after {
  content: '';
  position: absolute;
  bottom: -50px;
  right: -50px;
  width: 150px;
  height: 150px;
  background: radial-gradient(circle, rgba(255, 191, 0, 0.05) 0%, rgba(255, 255, 255, 0) 70%);
  border-radius: 50%;
  z-index: 0;
}

.section-title {
  font-size: 1.4rem;
  font-weight: 600;
  margin-bottom: 25px;
  color: var(--dark);
  position: relative;
  padding-bottom: 10px;
}

.section-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 60px;
  height: 3px;
  background-color: var(--primary);
  border-radius: 2px;
}

.detail-item {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
  transition: var(--transition);
  padding: 15px;
  border-radius: var(--radius-sm);
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid rgba(0, 0, 0, 0.02);
}

.detail-item:hover {
  background: rgba(255, 191, 0, 0.05);
  transform: translateX(5px);
  box-shadow: var(--shadow-sm);
}

.detail-icon {
  width: 50px;
  height: 50px;
  background: rgba(255, 191, 0, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20px;
  color: var(--primary);
  font-size: 1.2rem;
  flex-shrink: 0;
  box-shadow: 0 2px 10px rgba(255, 191, 0, 0.15);
}

.profile-avatar {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid var(--primary-light);
}

.detail-content h3 {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 5px 0;
  color: var(--text-light);
}

.detail-content p {
  font-size: 1.1rem;
  margin: 0;
  color: var(--dark);
  font-weight: 500;
}

.status-verified {
  color: #38b000 !important;
  display: inline-flex;
  align-items: center;
}

.status-verified::before {
  content: "";
  display: inline-block;
  width: 8px;
  height: 8px;
  background-color: #38b000;
  border-radius: 50%;
  margin-right: 8px;
}

.back-btn-wrapper {
  text-align: center;
  margin-top: 30px;
}

.back-btn {
  background: transparent;
  border: 2px solid var(--primary);
  color: var(--primary);
  padding: 12px 25px;
  border-radius: var(--radius-sm);
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.back-btn:hover {
  background: var(--primary);
  color: var(--light);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 191, 0, 0.15);
}

@media (max-width: 768px) {
  .driver-profile-wrapper {
    padding: 20px 15px;
  }
  
  .driver-profile-container {
    padding: 30px 20px;
    border-radius: var(--radius-md);
  }
  
  .profile-main-title {
    font-size: 1.8rem;
  }
  
  .profile-section {
    padding: 20px;
  }
  
  .section-title {
    font-size: 1.2rem;
  }
}

@media (max-width: 480px) {
  .driver-profile-container {
    padding: 25px 15px;
  }
  
  .profile-main-title {
    font-size: 1.5rem;
  }
  
  .profile-card {
    padding: 20px;
  }
  
  .card-icon {
    font-size: 1.5rem;
  }
  
  .profile-card h2 {
    font-size: 1.1rem;
  }
  
  .detail-icon {
    width: 38px;
    height: 38px;
  }
  
  .detail-content h3 {
    font-size: 0.9rem;
  }
  
  .detail-content p {
    font-size: 1rem;
  }
}
</style>

<script>
  function goBack() {
    window.history.back();
  }

  // Add entrance animations on scroll
  document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
          behavior: 'smooth'
        });
      });
    });
    
    // Animation for detail items when they come into view
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate__animated', 'animate__fadeInRight');
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1
    });
    
    document.querySelectorAll('.detail-item').forEach(item => {
      observer.observe(item);
    });
  });
</script>
<br><?php include 'footer.php';?>
</div></body>
</html>
