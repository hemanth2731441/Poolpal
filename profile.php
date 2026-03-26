<?php
include 'header.php';
include 'db.php';  // Assuming you have a database connection setup here

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];  // Assuming you store user ID in the session
    // Query the database to get the user's name
    $query = "SELECT full_name FROM users WHERE id = ?";
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
$stmt = $conn->prepare("SELECT full_name, email, phone, profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get trip statistics
$upcoming_trips = 0;
$completed_trips = 0;
$cancelled_trips = 0;

// First get the user's email
$user_email = $user['email'];

// Query for upcoming trips (bookings where departure date is in the future)
$stmt = $conn->prepare("
    SELECT COUNT(*) as count FROM bookings b 
    JOIN trips t ON b.trip_id = t.id 
    WHERE b.user_email = ? AND t.departure_date >= CURDATE()
");
if ($stmt) {
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $upcoming_trips = $row['count'];
    }
    $stmt->close();
}

// Query for completed trips (bookings where departure date is in the past)
$stmt = $conn->prepare("
    SELECT COUNT(*) as count FROM bookings b 
    JOIN trips t ON b.trip_id = t.id 
    WHERE b.user_email = ? AND t.departure_date < CURDATE()
");
if ($stmt) {
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $completed_trips = $row['count'];
    }
    $stmt->close();
}

// Query for cancelled trips
$stmt = $conn->prepare("
    SELECT COUNT(*) as count FROM cancelled_bookings 
    WHERE user_email = ?
");
if ($stmt) {
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $cancelled_trips = $row['count'];
    }
    $stmt->close();
}

// Get recent bookings
$recent_bookings = array();
$stmt = $conn->prepare("
    SELECT b.id, t.departure_city, t.destination_city, t.departure_date, t.departure_time, 
           b.seats_booked, t.price, t.driver_name 
    FROM bookings b 
    JOIN trips t ON b.trip_id = t.id 
    WHERE b.user_email = ? 
    ORDER BY b.booking_time DESC LIMIT 3
");
if ($stmt) {
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recent_bookings[] = $row;
    }
    $stmt->close();
}
?>

<div class="profile-wrapper">
  <div class="profile-header">
    <div class="container">
      <div class="profile-banner">
        <div class="profile-avatar">
          <img src="<?php echo !empty($user['profile_photo']) ? htmlspecialchars($user['profile_photo']) : 'images/default.jpg'; ?>" alt="Profile" onerror="this.src='images/default.jpg'">
          <div class="avatar-status"></div>
        </div>
        <div class="profile-info">
          <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
          <p><i data-feather="map-pin"></i> PoolPal Member</p>
        </div>
      </div>
    </div>
  </div>
  
  <div class="container">
    <div class="profile-content">
      <div class="profile-nav">
        <ul>
          <li class="active" data-tab="profile-tab"><a href="#profile"><i data-feather="user"></i> Profile</a></li>
          <li data-tab="trips-tab"><a href="#trips"><i data-feather="map"></i> Trips</a></li>
          <li data-tab="settings-tab"><a href="#settings"><i data-feather="settings"></i> Settings</a></li>
          <li><a href="logout.php"><i data-feather="log-out"></i> Logout</a></li>
        </ul>
      </div>
      
      <div class="profile-main">
        <!-- Profile Tab Content -->
        <div id="profile-tab" class="tab-content active">
          <div class="profile-section">
            <div class="section-header">
              <h2><i data-feather="user"></i> Personal Information</h2>
              <a href="edituser.php" class="btn-edit"><i data-feather="edit-2"></i> Edit Profile</a>
            </div>
            
            <div class="info-cards">
              <div class="info-card">
                <div class="info-icon">
                  <i data-feather="user"></i>
                </div>
                <div class="info-content">
                  <h3>Full Name</h3>
                  <p><?php echo htmlspecialchars($user['full_name']); ?></p>
                </div>
              </div>
              
              <div class="info-card">
                <div class="info-icon">
                  <i data-feather="mail"></i>
                </div>
                <div class="info-content">
                  <h3>Email</h3>
                  <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
              </div>
              
              <div class="info-card">
                <div class="info-icon">
                  <i data-feather="phone"></i>
                </div>
                <div class="info-content">
                  <h3>Phone</h3>
                  <p><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not provided'; ?></p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="profile-section">
            <div class="section-header">
              <h2><i data-feather="life-buoy"></i> Support & Help</h2>
            </div>
            
            <div class="support-card">
              <div class="support-content">
                <h3>Need assistance with your rides?</h3>
                <p>Our support team is available 24/7 to help you with any questions or issues you might have with your account or rides.</p>
                <a href="https://wa.me/919999999999" target="_blank" class="btn-support">
                  <i data-feather="message-circle"></i> Contact via WhatsApp
                </a>
              </div>
              <div class="support-image">
                <img src="images/support.svg" alt="Support" onerror="this.src='https://api.iconify.design/fluent:chat-help-24-filled.svg?color=%23ffbf00&width=128&height=128'">
              </div>
            </div>
          </div>
        </div>
        
        <!-- Trips Tab Content -->
        <div id="trips-tab" class="tab-content">
          <div class="profile-section trips-overview">
            <div class="section-header">
              <h2><i data-feather="map"></i> Your Rides Overview</h2>
              <a href="mytripsu.php" class="btn-edit"><i data-feather="external-link"></i> View All Trips</a>
            </div>
            
            <div class="trips-stats">
              <div class="trip-stat-card upcoming">
                <div class="trip-stat-icon">
                  <i data-feather="clock"></i>
                </div>
                <div class="trip-stat-content">
                  <h3>Upcoming Rides</h3>
                  <p class="trip-count"><?php echo $upcoming_trips; ?></p>
                  <div class="progress-bar">
                    <div class="progress" style="width: <?php echo min(100, ($upcoming_trips / max(1, $upcoming_trips + $completed_trips + $cancelled_trips)) * 100); ?>%"></div>
                  </div>
                </div>
              </div>
              
              <div class="trip-stat-card completed">
                <div class="trip-stat-icon">
                  <i data-feather="check-circle"></i>
                </div>
                <div class="trip-stat-content">
                  <h3>Completed Rides</h3>
                  <p class="trip-count"><?php echo $completed_trips; ?></p>
                  <div class="progress-bar">
                    <div class="progress" style="width: <?php echo min(100, ($completed_trips / max(1, $upcoming_trips + $completed_trips + $cancelled_trips)) * 100); ?>%"></div>
                  </div>
                </div>
              </div>
              
              <div class="trip-stat-card cancelled">
                <div class="trip-stat-icon">
                  <i data-feather="x-circle"></i>
                </div>
                <div class="trip-stat-content">
                  <h3>Cancelled Rides</h3>
                  <p class="trip-count"><?php echo $cancelled_trips; ?></p>
                  <div class="progress-bar">
                    <div class="progress" style="width: <?php echo min(100, ($cancelled_trips / max(1, $upcoming_trips + $completed_trips + $cancelled_trips)) * 100); ?>%"></div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="trips-recent">
              <h3 class="subsection-title">Recently Booked Rides</h3>
              <?php if (empty($recent_bookings)): ?>
                <div class="no-trips">
                  <p>You don't have any trips yet. Book a ride to get started!</p>
                  <a href="findrides.php" class="btn-book-ride">
                    <span class="icon"><i data-feather="search"></i></span>
                    <span class="text">Find & Book a Ride</span>
                    <span class="arrow"><i data-feather="arrow-right"></i></span>
                  </a>
                </div>
              <?php else: ?>
                                <div class="recent-trips-list">                  
                                  <?php foreach ($recent_bookings as $booking): ?>                    
                                    <div class="trip-card">                      
                                      <div class="trip-card-header">                        
                                        <div class="trip-date">                          
                                          <span class="day"><?php echo date('d', strtotime($booking['departure_date'])); ?></span>                          
                                          <span class="month"><?php echo date('M', strtotime($booking['departure_date'])); ?></span>                          
                                          <span class="year"><?php echo date('Y', strtotime($booking['departure_date'])); ?></span>                        
                                        </div>                        
                                        <div class="trip-time">                          
                                          <i data-feather="clock"></i>                          
                                          <span><?php echo date('H:i', strtotime($booking['departure_time'])); ?></span>                        
                                        </div>                      </div>                                            
                                        <div class="trip-card-body">                        
                                          <div class="trip-route-container">                          
                                            <div class="trip-route">                            
                                              <div class="route-point origin">                              
                                                <div class="point-dot"></div>                              
                                                <div class="point-text"><?php echo htmlspecialchars($booking['departure_city']); ?></div>                            
                                              </div>                            
                                              <div class="route-line">                              
                                                <div class="route-line-inner"></div>                            
                                              </div>                            
                                              <div class="route-point destination">                              
                                                <div class="point-dot"></div>                              
                                                <div class="point-text"><?php echo htmlspecialchars($booking['destination_city']); ?></div>                            
                                              </div>                          
                                            </div>                        
                                          </div>                                                
                                          <div class="trip-details-container">                          
                                            <div class="trip-detail-group">                            
                                              <div class="trip-detail seats">                              
                                                <i data-feather="users"></i>                              
                                                <span><?php echo $booking['seats_booked']; ?> <?php echo $booking['seats_booked'] > 1 ? 'Seats' : 'Seat'; ?></span>                            
                                              </div>                                                        
                                              <div class="trip-detail price">                              
                                                <i data-feather="indian-rupee"></i>                              
                                                <span>₹<?php echo number_format($booking['price'] * $booking['seats_booked']); ?></span>                            
                                              </div>                          
                                            </div>                                                    
                                            <div class="trip-detail driver">                            
                                              <i data-feather="user"></i>                            
                                              <span><?php echo htmlspecialchars($booking['driver_name']); ?></span>                          
                                            </div>                        
                                          </div>                      
                                        </div>                    
                                      </div>                  
                                      <?php endforeach; ?>
                </div>
                <div class="view-more-container">
                  <a href="mytripsu.php" class="btn-view-more">
                    <span>View All Your Trips</span>
                    <i data-feather="chevron-right"></i>
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Settings Tab Content -->
        <div id="settings-tab" class="tab-content">
          <div class="profile-section settings-section">
            <div class="section-header">
              <h2><i data-feather="settings"></i> Account Settings</h2>
            </div>
            
            <div class="settings-container">
              <div class="settings-group">
                <h3 class="settings-group-title">Personal Information</h3>
                
                <div class="setting-item">
                  <div class="setting-info">
                    <h4>Profile Information</h4>
                    <p>Update your name, email, and profile picture</p>
                  </div>
                  <a href="edituser.php" class="btn-setting-action">
                    <i data-feather="edit-2"></i>
                  </a>
                </div>
                
                <div class="setting-item">
                  <div class="setting-info">
                    <h4>Password & Security</h4>
                    <p>Change your password and security settings</p>
                  </div>
                  <a href="edit-accountu.php" class="btn-setting-action">
                    <i data-feather="shield"></i>
                  </a>
                </div>
              </div>
              
              <div class="settings-group">
                <h3 class="settings-group-title">Notifications</h3>
                
                <div class="setting-item">
                  <div class="setting-info">
                    <h4>Email Notifications</h4>
                    <p>Manage emails you want to receive</p>
                  </div>
                  <div class="setting-toggle">
                    <label class="switch">
                      <input type="checkbox" checked>
                      <span class="slider round"></span>
                    </label>
                  </div>
                </div>
                
                <div class="setting-item">
                  <div class="setting-info">
                    <h4>SMS Notifications</h4>
                    <p>Get text messages for important updates</p>
                  </div>
                  <div class="setting-toggle">
                    <label class="switch">
                      <input type="checkbox">
                      <span class="slider round"></span>
                    </label>
                  </div>
                </div>
                
                <div class="setting-item">
                  <div class="setting-info">
                    <h4>Push Notifications</h4>
                    <p>Get alerts on your mobile device</p>
                  </div>
                  <div class="setting-toggle">
                    <label class="switch">
                      <input type="checkbox" checked>
                      <span class="slider round"></span>
                    </label>
                  </div>
                </div>
              </div>
              
              <div class="settings-group">
                <h3 class="settings-group-title">Payment & Billing</h3>
                
                <div class="setting-item">
                  <div class="setting-info">
                    <h4>Payment Methods</h4>
                    <p>Add or update your payment information</p>
                  </div>
                  <a href="payment-methods.php" class="btn-setting-action">
                    <i data-feather="credit-card"></i>
                  </a>
                </div>
                
                <div class="setting-item">
                  <div class="setting-info">
                    <h4>Billing History</h4>
                    <p>View your past payments and invoices</p>
                  </div>
                  <a href="billing-history.php" class="btn-setting-action">
                    <i data-feather="file-text"></i>
                  </a>
                </div>
              </div>
              
              <div class="settings-group">
                <h3 class="settings-group-title">Privacy & Data</h3>
                
                <div class="setting-item">
                  <div class="setting-info">
                    <h4>Privacy Settings</h4>
                    <p>Control how your information is used</p>
                  </div>
                  <a href="privacy-settings.php" class="btn-setting-action">
                    <i data-feather="lock"></i>
                  </a>
                </div>
                
                <div class="setting-item">
                  <div class="setting-info">
                    <h4>Download Your Data</h4>
                    <p>Get a copy of your personal data</p>
                  </div>
                  <a href="download-data.php" class="btn-setting-action">
                    <i data-feather="download"></i>
                  </a>
                </div>
                
                <div class="setting-item danger">
                  <div class="setting-info">
                    <h4>Delete Account</h4>
                    <p>Permanently delete your account and data</p>
                  </div>
                  <a href="delete-account.php" class="btn-setting-action danger">
                    <i data-feather="trash-2"></i>
                  </a>
                </div>
              </div>
              
              <div class="settings-group">
                <h3 class="settings-group-title">Account</h3>
                
                <div class="setting-item logout-item">
                  <div class="setting-info">
                    <h4>Logout</h4>
                    <p>Sign out from your account</p>
                  </div>
                  <a href="logout.php" class="btn-setting-action danger">
                    <i data-feather="log-out"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
:root {
  --primary: #ffbf00;
  --primary-dark: #e6ac00;
  --primary-light: #fff9e6;
  --secondary: #333;
  --text-dark: #333;
  --text-light: #666;
  --bg-light: #f8f9fa;
  --bg-dark: #222;
  --border-color: #eaeaea;
  --card-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
  --transition: all 0.3s ease;
  --success: #4CAF50;
  --warning: #ff9800;
  --danger: #f44336;
}

.profile-wrapper {
  font-family: 'Inter', sans-serif;
  color: var(--text-dark);
  background-color: var(--bg-light);
  min-height: 100vh;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* Profile Header */
.profile-header {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  padding: 30px 0;
  margin-bottom: 30px;
  position: relative;
  overflow: hidden;
}

.profile-header::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 100%;
  height: 200%;
  background: rgba(255, 255, 255, 0.1);
  transform: rotate(-30deg);
  pointer-events: none;
}

.profile-banner {
  display: flex;
  align-items: center;
  position: relative;
  z-index: 1;
}

.profile-avatar {
  position: relative;
  margin-right: 30px;
}

.profile-avatar img {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  border: 4px solid white;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  object-fit: cover;
  transition: var(--transition);
}

.profile-avatar img:hover {
  transform: scale(1.05);
}

.avatar-status {
  position: absolute;
  bottom: 10px;
  right: 10px;
  width: 20px;
  height: 20px;
  background-color: #4CAF50;
  border-radius: 50%;
  border: 3px solid white;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(76, 175, 80, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
  }
}

.profile-info {
  color: white;
}

.profile-info h1 {
  font-size: 2.5rem;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.profile-info p {
  display: flex;
  align-items: center;
  font-size: 1rem;
  margin: 10px 0 0;
  opacity: 0.9;
}

.profile-info p svg {
  margin-right: 5px;
  width: 16px;
  height: 16px;
}

/* Profile Content */
.profile-content {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 30px;
  margin-bottom: 50px;
}

.profile-nav {
  background: white;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: var(--card-shadow);
  transition: var(--transition);
}

.profile-nav:hover {
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
}

.profile-nav ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.profile-nav li {
  border-bottom: 1px solid var(--border-color);
  cursor: pointer;
}

.profile-nav li:last-child {
  border-bottom: none;
}

.profile-nav a {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  color: var(--text-dark);
  text-decoration: none;
  transition: var(--transition);
}

.profile-nav a svg {
  margin-right: 10px;
  width: 20px;
  height: 20px;
  color: var(--primary);
}

.profile-nav li.active a {
  background-color: var(--primary-light);
  color: var(--primary);
  font-weight: 600;
  border-left: 4px solid var(--primary);
}

.profile-nav a:hover {
  background-color: var(--primary-light);
}

.profile-main {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.profile-section {
  background: white;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: var(--card-shadow);
  transition: var(--transition);
}

.profile-section:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 25px;
  border-bottom: 1px solid var(--border-color);
}

.section-header h2 {
  margin: 0;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
}

.section-header h2 svg {
  margin-right: 10px;
  color: var(--primary);
}

.btn-edit {
  display: inline-flex;
  align-items: center;
  padding: 8px 15px;
  background-color: var(--primary);
  color: white;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: var(--transition);
}

.btn-edit svg {
  margin-right: 5px;
}

.btn-edit:hover {
  background-color: var(--primary-dark);
  transform: translateY(-2px);
}

/* Info Cards */
.info-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  padding: 25px;
}

.info-card {
  display: flex;
  align-items: center;
  padding: 20px;
  border-radius: 10px;
  background-color: var(--bg-light);
  transition: var(--transition);
}

.info-card:hover {
  background-color: var(--primary-light);
  transform: scale(1.02);
}

.info-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  background-color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20px;
  transition: var(--transition);
}

.info-card:hover .info-icon {
  transform: rotate(10deg) scale(1.1);
}

.info-icon svg {
  color: white;
  width: 24px;
  height: 24px;
}

.info-content h3 {
  margin: 0 0 5px;
  font-size: 1rem;
  color: var(--text-light);
}

.info-content p {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--text-dark);
}

/* Support Card */
.support-card {
  display: flex;
  align-items: center;
  padding: 25px;
  background: linear-gradient(to right, var(--primary-light), white);
}

.support-content {
  flex: 1;
}

.support-content h3 {
  margin: 0 0 10px;
  font-size: 1.3rem;
  color: var(--text-dark);
}

.support-content p {
  margin: 0 0 20px;
  color: var(--text-light);
  line-height: 1.6;
}

.btn-support {
  display: inline-flex;
  align-items: center;
  padding: 12px 24px;
  background-color: var(--primary);
  color: white;
  border-radius: 30px;
  text-decoration: none;
  font-weight: 500;
  transition: var(--transition);
  box-shadow: 0 4px 15px rgba(255, 191, 0, 0.3);
}

.btn-support svg {
  margin-right: 8px;
}

.btn-support:hover {
  background-color: var(--primary-dark);
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(255, 191, 0, 0.4);
}

.support-image {
  flex: 0 0 150px;
  margin-left: 20px;
}

.support-image img {
  max-width: 100%;
  height: auto;
  transition: var(--transition);
}

.support-image:hover img {
  transform: scale(1.1) rotate(5deg);
}

/* Trip Stats */
.trips-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  padding: 25px 25px 15px;
}

.trip-stat-card {
  background-color: var(--bg-light);
  border-radius: 15px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.trip-stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 100%;
  transition: var(--transition);
}

.trip-stat-card.upcoming::before {
  background-color: var(--warning);
}

.trip-stat-card.completed::before {
  background-color: var(--success);
}

.trip-stat-card.cancelled::before {
  background-color: var(--danger);
}

.trip-stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}

.trip-stat-card:hover::before {
  width: 100%;
  opacity: 0.1;
}

.trip-stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 15px;
  transition: var(--transition);
}

.trip-stat-card.upcoming .trip-stat-icon {
  background-color: rgba(255, 152, 0, 0.15);
  color: var(--warning);
}

.trip-stat-card.completed .trip-stat-icon {
  background-color: rgba(76, 175, 80, 0.15);
  color: var(--success);
}

.trip-stat-card.cancelled .trip-stat-icon {
  background-color: rgba(244, 67, 54, 0.15);
  color: var(--danger);
}

.trip-stat-icon svg {
  width: 24px;
  height: 24px;
}

.trip-stat-content h3 {
  margin: 0 0 10px;
  font-size: 0.9rem;
  color: var(--text-light);
  font-weight: 500;
}

.trip-stat-content .trip-count {
  font-size: 2.4rem;
  font-weight: 700;
  margin: 0 0 10px;
  color: var(--text-dark);
}

.progress-bar {
  width: 100%;
  height: 6px;
  background-color: #eee;
  border-radius: 10px;
  overflow: hidden;
}

.trip-stat-card.upcoming .progress-bar .progress {
  background-color: var(--warning);
}

.trip-stat-card.completed .progress-bar .progress {
  background-color: var(--success);
}

.trip-stat-card.cancelled .progress-bar .progress {
  background-color: var(--danger);
}

.progress-bar .progress {
  height: 100%;
  border-radius: 10px;
  transition: width 0.8s ease;
}

.trips-recent {
  padding: 0 25px 25px;
}

.subsection-title {
  font-size: 1.2rem;
  margin: 20px 0 15px;
  color: var(--text-dark);
  font-weight: 600;
  display: flex;
  align-items: center;
}

.subsection-title::after {
  content: '';
  flex: 1;
  height: 1px;
  background-color: var(--border-color);
  margin-left: 15px;
}

.trips-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 0;
  color: var(--text-light);
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 191, 0, 0.3);
  border-radius: 50%;
  border-top-color: var(--primary);
  animation: spin 1s linear infinite;
  margin-bottom: 15px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.trips-list {
  min-height: 150px;
}

/* Tab Content */
.tab-content {
  display: none;
}

.tab-content.active {
  display: block;
  animation: fadeIn 0.5s ease-out forwards;
}

/* Improved Mobile Responsiveness */
@media (max-width: 992px) {
  .profile-content {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .profile-nav {
    margin-bottom: 0;
  }
  
  .profile-nav ul {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .profile-nav li {
    flex: 1 1 auto;
    min-width: 80px;
    border-bottom: none;
    border-right: 1px solid var(--border-color);
  }
  
  .profile-nav li:last-child {
    border-right: none;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 0 15px;
  }
  
  .profile-banner {
    flex-direction: column;
    text-align: center;
    padding: 15px 0;
  }
  
  .profile-avatar {
    margin: 0 auto 15px;
  }
  
  .profile-info {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  
  .profile-info p {
    justify-content: center;
  }
  
  .profile-nav ul {
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 5px;
  }
  
  .profile-nav li {
    flex: 0 0 auto;
    width: auto;
    border-right: none;
    border-bottom: 2px solid transparent;
  }
  
  .profile-nav li.active {
    border-left: none;
    border-bottom: 2px solid var(--primary);
  }
  
  .profile-nav a {
    white-space: nowrap;
    padding: 12px 15px;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    padding: 15px 20px;
  }
  
  .section-header h2 {
    font-size: 1.2rem;
  }
  
  .info-cards {
    grid-template-columns: 1fr;
    gap: 15px;
    padding: 15px;
  }
  
  .trips-stats {
    grid-template-columns: 1fr;
    gap: 15px;
    padding: 15px;
  }
  
  .support-card {
    flex-direction: column;
    padding: 20px 15px;
    text-align: center;
  }
  
  .support-content {
    order: 2;
  }
  
  .support-image {
    margin: 0 0 15px 0;
    order: 1;
  }
  
  .support-content h3 {
    font-size: 1.1rem;
  }
  
  .btn-support {
    width: 100%;
    padding: 12px 15px;
  }
}

@media (max-width: 480px) {
  .profile-header {
    padding: 15px 0;
    margin-bottom: 20px;
  }
  
  .profile-avatar img {
    width: 80px;
    height: 80px;
    border-width: 3px;
  }
  
  .avatar-status {
    width: 16px;
    height: 16px;
    border-width: 2px;
    bottom: 5px;
    right: 5px;
  }
  
  .profile-info h1 {
    font-size: 1.5rem;
  }
  
  .profile-nav {
    border-radius: 10px;
  }
  
  .profile-nav a {
    padding: 10px;
    display: flex;
    flex-direction: column;
    font-size: 0.8rem;
  }
  
  .profile-nav a svg {
    margin: 0 0 5px;
  }
  
  .profile-section {
    border-radius: 10px;
    margin-bottom: 15px;
  }
  
  .btn-edit {
    padding: 6px 12px;
    font-size: 0.85rem;
  }
  
  .info-card {
    padding: 12px;
  }
  
  .info-icon {
    width: 36px;
    height: 36px;
    margin-right: 15px;
  }
  
  .info-icon svg {
    width: 18px;
    height: 18px;
  }
  
  .info-content h3 {
    font-size: 0.85rem;
  }
  
  .info-content p {
    font-size: 0.95rem;
  }
  
  .trip-stat-content .trip-count {
    font-size: 1.6rem;
  }
  
  .trip-stat-card {
    padding: 15px;
  }
  
  .trip-stat-icon {
    width: 40px;
    height: 40px;
  }
  
  .setting-item {
    padding: 12px 10px;
    margin-bottom: 10px;
  }
  
  .setting-info h4 {
    font-size: 0.9rem;
  }
  
  .setting-info p {
    font-size: 0.8rem;
  }
  
  .btn-view-more {
    padding: 10px 20px;
    font-size: 0.85rem;
  }
  
  /* Better touch targets for mobile */
  .btn-setting-action {
    width: 44px;
    height: 44px;
  }
  
  .switch {
    transform: scale(1.1);
  }
  
  /* Trip cards mobile optimization */
  .trip-card-header {
    padding: 10px 15px;
  }
  
  .trip-card-body {
    padding: 12px;
    gap: 15px;
  }
  
  .point-dot {
    width: 12px;
    height: 12px;
    margin-right: 10px;
  }
  
  .route-line {
    left: 15px;
  }
  
  .trip-detail {
    padding: 6px 10px;
    font-size: 0.8rem;
    gap: 2px;
  }
  
  .trip-detail svg {
    width: 14px;
    height: 14px;
    margin-right: 5px;
  }
  
  .trip-detail-group {
    flex-wrap: wrap;
    gap: 8px;
  }
}

/* Improved scroll behavior for tabs on mobile */
@media (max-width: 768px) {
  .profile-nav {
    position: sticky;
    top: 0;
    z-index: 10;
    background: white;
    margin: 0 0 20px;
    padding: 5px 0;
    border-radius: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  
  .profile-nav ul {
    -ms-overflow-style: none;  /* Internet Explorer 10+ */
    scrollbar-width: none;  /* Firefox */
    padding-bottom: 0;
  }
  
  .profile-nav ul::-webkit-scrollbar { 
    display: none;  /* Safari and Chrome */
  }
  
  .profile-nav li {
    position: relative;
  }
  
  .profile-nav li.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 15px;
    right: 15px;
    height: 2px;
    background-color: var(--primary);
  }
  
  /* Improved touch feedback */
  .setting-item,
  .info-card,
  .trip-card,
  .btn-edit,
  .btn-support,
  .btn-view-more,
  .btn-setting-action {
    -webkit-tap-highlight-color: transparent;
  }
  
  /* Active state for touch */
  .setting-item:active,
  .info-card:active,
  .trip-card:active {
    transform: scale(0.98);
  }
  
  .btn-edit:active,
  .btn-support:active,
  .btn-view-more:active,
  .btn-setting-action:active {
    transform: scale(0.95);
  }
}

/* Fix for ios position:fixed bug */
@supports (-webkit-touch-callout: none) {
  .profile-nav {
    position: -webkit-sticky;
  }
}

/* Make sure elements don't overflow on small screens */
@media (max-width: 480px) {
  .profile-content,
  .profile-main,
  .profile-section,
  .trips-stats,
  .info-cards,
  .support-card,
  .settings-container,
  .recent-trips-list {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
  }
  
  /* Improve readability of trips on small screens */
  .trip-card {
    margin-left: 3px;
    margin-right: 3px;
  }
  
  .point-text {
    font-size: 0.9rem;
    word-break: break-word;
  }
  
  /* Full width buttons on small screens */
  .btn-book-ride {
    width: 100%;
    max-width: none;
    padding: 12px 20px;
    font-size: 0.95rem;
  }
  
  /* Better progress bar visibility */
  .progress-bar {
    height: 8px;
  }
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.profile-section {
  animation: fadeIn 0.5s ease-out forwards;
}

.profile-section:nth-child(2) {
  animation-delay: 0.2s;
}

.info-card {
  animation: fadeIn 0.5s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 0.1s);
}

.info-card:nth-child(1) { --i: 1; }
.info-card:nth-child(2) { --i: 2; }
.info-card:nth-child(3) { --i: 3; }

.trip-stat-card {
  animation: fadeIn 0.5s ease-out forwards;
  animation-delay: calc(var(--i, 0) * 0.1s);
}

.trip-stat-card:nth-child(1) { --i: 1; }
.trip-stat-card:nth-child(2) { --i: 2; }
.trip-stat-card:nth-child(3) { --i: 3; }

/* Book a Ride Button */
.btn-book-ride {
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: white;
  padding: 15px 30px;
  border-radius: 50px;
  text-decoration: none;
  font-weight: 600;
  margin: 25px auto 10px;
  max-width: 300px;
  transition: all 0.3s ease;
  box-shadow: 0 8px 20px rgba(255, 191, 0, 0.3);
  position: relative;
  overflow: hidden;
}

.btn-book-ride::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: all 0.5s ease;
}

.btn-book-ride:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(255, 191, 0, 0.4);
}

.btn-book-ride:hover::before {
  left: 100%;
}

.btn-book-ride .icon,
.btn-book-ride .arrow {
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-book-ride .icon {
  margin-right: 12px;
}

.btn-book-ride .arrow {
  margin-left: 12px;
  transition: transform 0.3s ease;
}

.btn-book-ride:hover .arrow {
  transform: translateX(4px);
}

/* No Trips */
.no-trips {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-light);
}

.no-trips p {
  margin-bottom: 25px;
  font-size: 1.1rem;
}

/* Recent Trips List */.recent-trips-list {  display: flex;  flex-direction: column;  gap: 20px;  margin-top: 25px;  padding: 0 5px;}.trip-card {  background-color: white;  border-radius: 16px;  box-shadow: 0 5px 20px rgba(0,0,0,0.05);  overflow: hidden;  transition: all 0.3s ease;  position: relative;}.trip-card:hover {  transform: translateY(-3px);  box-shadow: 0 8px 25px rgba(0,0,0,0.1);}.trip-card::before {  content: '';  position: absolute;  top: 0;  left: 0;  width: 4px;  height: 100%;  background: linear-gradient(to bottom, var(--primary), var(--primary-dark));}.trip-card-header {  display: flex;  justify-content: space-between;  align-items: center;  padding: 15px 20px;  border-bottom: 1px solid var(--border-color);  background-color: rgba(255, 191, 0, 0.05);}.trip-date {  display: flex;  align-items: baseline;  gap: 5px;}.trip-date .day {  font-size: 1.4rem;  font-weight: 700;  color: var(--text-dark);}.trip-date .month {  font-size: 1rem;  font-weight: 600;  color: var(--primary);  text-transform: uppercase;}.trip-date .year {  font-size: 0.9rem;  color: var(--text-light);  margin-left: 2px;}.trip-time {  display: flex;  align-items: center;  color: var(--text-dark);  font-weight: 500;}.trip-time svg {  width: 16px;  height: 16px;  margin-right: 8px;  color: var(--primary);}.trip-card-body {  padding: 20px;  display: flex;  flex-direction: column;  gap: 20px;}.trip-route-container {  position: relative;}.trip-route {  display: flex;  flex-direction: column;  gap: 5px;  position: relative;  padding: 0 10px;}.route-point {  display: flex;  align-items: center;  position: relative;  padding: 8px 0;}.point-dot {  width: 14px;  height: 14px;  border-radius: 50%;  margin-right: 15px;  position: relative;  z-index: 2;}.route-point.origin .point-dot {  background-color: var(--primary);  box-shadow: 0 0 0 4px rgba(255, 191, 0, 0.2);}.route-point.destination .point-dot {  background-color: var(--primary-dark);  box-shadow: 0 0 0 4px rgba(230, 172, 0, 0.2);}.point-text {  font-weight: 600;  color: var(--text-dark);  flex: 1;}.route-line {  position: absolute;  top: 28px;  bottom: 28px;  left: 17px;  width: 2px;  z-index: 1;}.route-line-inner {  height: 100%;  width: 100%;  background: linear-gradient(to bottom, var(--primary), var(--primary-dark));  animation: pulse-line 2s infinite;}@keyframes pulse-line {  0% {    opacity: 0.6;  }  50% {    opacity: 1;  }  100% {    opacity: 0.6;  }}.trip-details-container {  border-top: 1px dashed var(--border-color);  padding-top: 15px;  display: flex;  flex-direction: column;  gap: 12px;}.trip-detail-group {  display: flex;  justify-content: space-between;  flex-wrap: wrap;  gap: 10px;}.trip-detail {  display: flex;  align-items: center;  padding: 8px 12px;  background-color: var(--bg-light);  border-radius: 30px;  font-size: 0.9rem;}.trip-detail svg {  width: 16px;  height: 16px;  margin-right: 8px;  color: var(--primary);}.trip-detail.seats {  color: var(--text-dark);}.trip-detail.price {  font-weight: 600;  color: var(--text-dark);}.trip-detail.driver {  color: var(--text-dark);  font-weight: 500;  align-self: flex-start;}.view-more-container {  display: flex;  justify-content: center;  margin-top: 30px;  margin-bottom: 10px;}.btn-view-more {  display: flex;  align-items: center;  color: white;  font-weight: 600;  text-decoration: none;  padding: 12px 24px;  border-radius: 50px;  transition: all 0.3s ease;  background: linear-gradient(135deg, var(--primary), var(--primary-dark));  box-shadow: 0 4px 15px rgba(255, 191, 0, 0.3);}.btn-view-more:hover {  transform: translateY(-2px);  box-shadow: 0 6px 20px rgba(255, 191, 0, 0.4);}.btn-view-more span {  margin-right: 5px;}.btn-view-more svg {  width: 18px;  height: 18px;  transition: transform 0.3s ease;}.btn-view-more:hover svg {  transform: translateX(4px);}/* Media queries for trip cards */@media (max-width: 768px) {  .trip-card-body {    padding: 15px;  }    .trip-route {    padding: 0 5px;  }    .trip-detail-group {    flex-direction: row;  }    .trip-detail {    flex: 1;    min-width: auto;    justify-content: center;  }}@media (max-width: 480px) {  .trip-card-header {    padding: 12px 15px;  }    .trip-date .day {    font-size: 1.2rem;  }    .trip-date .month {    font-size: 0.9rem;  }    .trip-date .year {    font-size: 0.8rem;  }    .trip-card-body {    padding: 15px 12px;  }    .trip-detail {    padding: 7px 10px;    font-size: 0.85rem;  }    .point-text {    font-size: 0.95rem;  }    .btn-view-more {    padding: 10px 20px;    font-size: 0.9rem;  }}

/* Settings Styles */
.settings-container {
  padding: 25px;
}

.settings-group {
  margin-bottom: 30px;
}

.settings-group:last-child {
  margin-bottom: 0;
}

.settings-group-title {
  font-size: 1.1rem;
  color: var(--text-dark);
  margin: 0 0 15px;
  padding-bottom: 10px;
  border-bottom: 1px solid var(--border-color);
}

.setting-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 12px;
  background-color: var(--bg-light);
  transition: var(--transition);
}

.setting-item:last-child {
  margin-bottom: 0;
}

.setting-item:hover {
  transform: translateX(5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.setting-info {
  flex: 1;
}

.setting-info h4 {
  margin: 0 0 5px;
  font-size: 1rem;
  color: var(--text-dark);
}

.setting-info p {
  margin: 0;
  font-size: 0.9rem;
  color: var(--text-light);
}

.btn-setting-action {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  color: var(--primary);
  background-color: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  transition: var(--transition);
}

.btn-setting-action:hover {
  background-color: var(--primary);
  color: white;
  transform: scale(1.1);
}

.btn-setting-action.danger {
  color: var(--danger);
}

.btn-setting-action.danger:hover {
  background-color: var(--danger);
  color: white;
}

.setting-item.danger:hover {
  background-color: rgba(244, 67, 54, 0.05);
}

.setting-toggle {
  display: flex;
  align-items: center;
}

/* Toggle Switch */
.switch {
  position: relative;
  display: inline-block;
  width: 48px;
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
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
}

input:checked + .slider {
  background-color: var(--primary);
}

input:focus + .slider {
  box-shadow: 0 0 1px var(--primary);
}

input:checked + .slider:before {
  transform: translateX(24px);
}

.slider.round {
  border-radius: 24px;
}

.slider.round:before {
  border-radius: 50%;
}

@media (max-width: 768px) {
  .settings-container {
    padding: 20px 15px;
  }
  
  .setting-item {
    padding: 12px;
  }
  
  .setting-info h4 {
    font-size: 0.95rem;
  }
  
  .setting-info p {
    font-size: 0.85rem;
  }
}

@media (max-width: 480px) {
  .settings-container {
    padding: 15px 10px;
  }
  
  .btn-setting-action {
    width: 36px;
    height: 36px;
  }
  
  .btn-setting-action svg {
    width: 18px;
    height: 18px;
  }
  
  .switch {
    width: 44px;
    height: 22px;
  }
  
  .slider:before {
    height: 16px;
    width: 16px;
  }
  
  input:checked + .slider:before {
    transform: translateX(22px);
  }
}
</style>

<script src="https://unpkg.com/feather-icons"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
    
    // Tab functionality
    const tabs = document.querySelectorAll('.profile-nav li');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
      tab.addEventListener('click', function() {
        const tabId = this.getAttribute('data-tab');
        
        // Remove active class from all tabs and content
        tabs.forEach(t => t.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));
        
        // Add active class to clicked tab and corresponding content
        this.classList.add('active');
        document.getElementById(tabId).classList.add('active');
      });
    });
    
    // Add entrance animations when scrolling
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-in');
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1
    });
    
    document.querySelectorAll('.profile-section, .info-card, .trip-stat-card').forEach(el => {
      observer.observe(el);
    });
    
    // Handle direct links to tabs
    const hash = window.location.hash;
    if (hash === '#trips') {
      document.querySelector('[data-tab="trips-tab"]').click();
    }
  });
</script>

<?php include 'footer.php'; ?>
