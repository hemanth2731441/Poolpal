<?php
include 'nav.php';
include 'db.php';  // Assuming you have a database connection setup here

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];  // Assuming you store user ID in the session
    // Query the database to get the user's name
    $query = "SELECT full_name, vehicle_number, vehicle_name, languages, member_since FROM drivers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($full_name, $vehicle_number, $vehicle_name, $languages, $member_since);
    $stmt->fetch();
    $stmt->close();

    // Get completed trips count - Updated query to match with mytrips.php
    $query_trips = "SELECT COUNT(DISTINCT t.id) as completed_trips 
                   FROM trips t 
                   JOIN bookings b ON t.id = b.trip_id 
                   WHERE t.driver_email = ? 
                   AND CONCAT(t.departure_date, ' ', t.arrival_time) <= NOW()";
    $stmt_trips = $conn->prepare($query_trips);
    $stmt_trips->bind_param('s', $_SESSION['email']);
    $stmt_trips->execute();
    $result_trips = $stmt_trips->get_result();
    $completed_trips = $result_trips->fetch_assoc()['completed_trips'];
    $stmt_trips->close();

    // Get total completed bookings count
    $query_bookings = "SELECT COUNT(*) as total_bookings 
                      FROM bookings b 
                      JOIN trips t ON b.trip_id = t.id 
                      WHERE t.driver_email = ? 
                      AND CONCAT(t.departure_date, ' ', t.arrival_time) <= NOW()";
    $stmt_bookings = $conn->prepare($query_bookings);
    $stmt_bookings->bind_param('s', $_SESSION['email']);
    $stmt_bookings->execute();
    $result_bookings = $stmt_bookings->get_result();
    $total_bookings = $result_bookings->fetch_assoc()['total_bookings'];
    $stmt_bookings->close();

    // Get earnings for current month
    $query_earnings = "SELECT COALESCE(SUM(b.total_amount), 0) as monthly_earnings 
                      FROM bookings b 
                      JOIN trips t ON b.trip_id = t.id 
                      WHERE t.driver_email = ? 
                      AND MONTH(b.booking_time) = MONTH(CURRENT_DATE())
                      AND YEAR(b.booking_time) = YEAR(CURRENT_DATE())";
    $stmt_earnings = $conn->prepare($query_earnings);
    $stmt_earnings->bind_param('s', $_SESSION['email']);
    $stmt_earnings->execute();
    $result_earnings = $stmt_earnings->get_result();
    $monthly_earnings = $result_earnings->fetch_assoc()['monthly_earnings'];
    $stmt_earnings->close();

    // Get total earnings
    $query_total_earnings = "SELECT COALESCE(SUM(b.total_amount), 0) as total_earnings 
                            FROM bookings b 
                            JOIN trips t ON b.trip_id = t.id 
                            WHERE t.driver_email = ?";
    $stmt_total_earnings = $conn->prepare($query_total_earnings);
    $stmt_total_earnings->bind_param('s', $_SESSION['email']);
    $stmt_total_earnings->execute();
    $result_total_earnings = $stmt_total_earnings->get_result();
    $total_earnings = $result_total_earnings->fetch_assoc()['total_earnings'];
    $stmt_total_earnings->close();

    // Calculate earnings trend
    $query_last_month = "SELECT COALESCE(SUM(b.total_amount), 0) as last_month_earnings 
                         FROM bookings b 
                         JOIN trips t ON b.trip_id = t.id 
                         WHERE t.driver_email = ? 
                         AND MONTH(b.booking_time) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
                         AND YEAR(b.booking_time) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
    $stmt_last_month = $conn->prepare($query_last_month);
    $stmt_last_month->bind_param('s', $_SESSION['email']);
    $stmt_last_month->execute();
    $result_last_month = $stmt_last_month->get_result();
    $last_month_earnings = $result_last_month->fetch_assoc()['last_month_earnings'];
    $stmt_last_month->close();

    // Calculate percentage change
    $earnings_change = 0;
    $earnings_trend = '';
    if ($last_month_earnings > 0) {
        $earnings_change = (($monthly_earnings - $last_month_earnings) / $last_month_earnings) * 100;
        $earnings_trend = $earnings_change >= 0 ? 'positive' : 'negative';
    }

    $member_since_formatted = date('F Y', strtotime($member_since));
} else {
    // If the user is not logged in, redirect to the login page
    header("Location: driver_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard | PoolPal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
<div class="driver-dashboard-wrapper">
    <div class="driver-dashboard-container animate__animated animate__fadeIn">
        <div class="dashboard-breadcrumb">
            <a href="#" class="breadcrumb-item">Dashboard</a>
            <i class="fas fa-chevron-right breadcrumb-separator"></i>
            <span class="breadcrumb-item active">Driver Profile</span>
        </div>

        <div class="dashboard-welcome-banner">
            <div class="welcome-content">
                <span class="driver-dash-badge animate__animated animate__fadeInDown">
                    <i class="fas fa-shield-alt"></i> Verified Driver
                </span>
                <h1 class="driver-dash-title animate__animated animate__fadeInUp animate__delay-1s">Welcome back, <span class="driver-dash-highlight"><?php echo htmlspecialchars($full_name); ?></span>!</h1>
                <p class="driver-dash-subtitle animate__animated animate__fadeInUp animate__delay-2s">Ready to hit the road? Check your stats and manage your rides.</p>
                <a href="tripdetails.php" class="driver-dash-btn animate__animated animate__fadeInUp animate__delay-3s">
                    <span>Offer Rides</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="welcome-decoration">
                <div class="decoration-circle pulse"></div>
                <div class="decoration-shape floating"></div>
                <div class="car-animation">
                    <i class="fas fa-car-side"></i>
                </div>
            </div>
        </div>

        <div class="dashboard-stats-grid">
            <div class="driver-stat-card animate__animated animate__fadeInUp">
                <div class="driver-stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="driver-stat-content">
                    <h3 class="driver-stat-label">Member Since</h3>
                    <div class="driver-stat-value"><?php echo htmlspecialchars($member_since_formatted); ?></div>
                    <div class="driver-stat-progress">
                        <div class="driver-progress-bar" style="width: 100%"></div>
                    </div>
                </div>
            </div>

            <div class="driver-stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                <div class="driver-stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="driver-stat-content">
                    <h3 class="driver-stat-label">Rating</h3>
                    <div class="driver-stat-value">4.8<span class="driver-stat-unit">/5.0</span></div>
                    <div class="driver-stat-subtitle">Based on 124 trips</div>
                    <div class="driver-star-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                </div>
            </div>

            <div class="driver-stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                <div class="driver-stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="driver-stat-content">
                    <h3 class="driver-stat-label">Completed Trips</h3>
                    <div class="driver-stat-value count-up"><?php echo $completed_trips; ?></div>
                    <div class="driver-stat-subtitle">
                        <?php echo $total_bookings; ?> total bookings
                    </div>
                    <div class="driver-stat-chart">
                        <div class="driver-chart-bar" style="height: 20%"></div>
                        <div class="driver-chart-bar" style="height: 45%"></div>
                        <div class="driver-chart-bar" style="height: 65%"></div>
                        <div class="driver-chart-bar" style="height: 40%"></div>
                        <div class="driver-chart-bar" style="height: 80%"></div>
                    </div>
                </div>
            </div>
            
            <div class="driver-stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                <div class="driver-stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="driver-stat-content">
                    <h3 class="driver-stat-label">Earnings This Month</h3>
                    <div class="driver-stat-value">₹<?php echo number_format($monthly_earnings); ?></div>
                    <?php if ($earnings_change != 0): ?>
                    <div class="driver-stat-subtitle">
                        <span class="driver-stat-trend <?php echo $earnings_trend; ?>">
                            <i class="fas fa-arrow-<?php echo $earnings_change >= 0 ? 'up' : 'down'; ?>"></i>
                            <?php echo abs(round($earnings_change)); ?>%
                        </span>
                        from last month
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="dashboard-sections-grid">
            <div class="dashboard-section verification-section animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                <div class="section-header">
                    <h2 class="driver-section-title">Verification Status</h2>
                </div>
                <div class="driver-verification-status">
                    <div class="driver-status-badge verified">
                        <i class="fas fa-check-circle"></i>
                        <span>Verified</span>
                    </div>
                    <p class="driver-verification-text">Your driver's license has been verified. You can now offer rides on the platform.</p>
                </div>
            </div>

            <div class="dashboard-section driver-info-section animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                <div class="section-header">
                    <h2 class="driver-section-title">Driver Information</h2>
                </div>
                <div class="info-items">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="info-content">
                            <h3 class="driver-info-label">Verified Driver</h3>
                            <p class="driver-info-text">ID and license verified</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-car-side"></i>
                        </div>
                        <div class="info-content">
                            <h3 class="driver-info-label">Vehicle Name</h3>
                            <p class="driver-info-text"><?php echo htmlspecialchars($vehicle_name ?? 'Not provided'); ?></p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="info-content">
                            <h3 class="driver-info-label">Vehicle Number Plate</h3>
                            <p class="driver-info-text"><?php echo htmlspecialchars($vehicle_number); ?></p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-language"></i>
                        </div>
                        <div class="info-content">
                            <h3 class="driver-info-label">Languages</h3>
                            <p class="driver-info-text"><?php echo htmlspecialchars($languages); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-quick-actions animate__animated animate__fadeInUp" style="animation-delay: 0.45s">
            <div class="section-header">
                <h2>Quick Actions</h2>
                <p>Common tasks for managing your driving experience</p>
            </div>
            <div class="quick-actions-grid">
                <a href="tripdetails.php" class="quick-action-card">
                    <div class="action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h3>Create New Ride</h3>
                    <p>Offer a new ride to passengers</p>
                    <div class="action-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
                <a href="mytrips.php" class="quick-action-card">
                    <div class="action-icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <h3>Manage Rides</h3>
                    <p>View and manage your ride offers</p>
                    <div class="action-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
                <a href="driproedit.php" class="quick-action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <h3>Edit Profile</h3>
                    <p>Update your personal information</p>
                    <div class="action-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
                <a href="#" class="quick-action-card">
                    <div class="action-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h3>Payment Details</h3>
                    <p>Manage your payment methods</p>
                    <div class="action-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </div>
        </div>

        <div class="dashboard-preferences-section animate__animated animate__fadeInUp" style="animation-delay: 0.5s">
            <div class="section-header">
                <h2 class="driver-section-title">Driver Preferences</h2>
                <p class="driver-section-subtitle">These are the preferences you can set for your rides</p>
            </div>
            <div class="driver-preferences-grid">
                <div class="driver-preference-item">
                    <div class="driver-preference-icon">
                        <i class="fas fa-smoking-ban"></i>
                    </div>
                    <div class="driver-preference-content">
                        <h3 class="driver-preference-label">Smoking</h3>
                        <p class="driver-preference-text">Allowed/Not allowed</p>
                    </div>
                    <div class="driver-preference-toggle">
                        <label class="driver-toggle-switch">
                            <input type="checkbox">
                            <span class="driver-toggle-slider"></span>
                            <span class="driver-toggle-label" data-on="Allowed" data-off="Not Allowed"></span>
                        </label>
                    </div>
                </div>
                <div class="driver-preference-item">
                    <div class="driver-preference-icon">
                        <i class="fas fa-paw"></i>
                    </div>
                    <div class="driver-preference-content">
                        <h3 class="driver-preference-label">Pets</h3>
                        <p class="driver-preference-text">Allowed/Not allowed</p>
                    </div>
                    <div class="driver-preference-toggle">
                        <label class="driver-toggle-switch">
                            <input type="checkbox">
                            <span class="driver-toggle-slider"></span>
                            <span class="driver-toggle-label" data-on="Allowed" data-off="Not Allowed"></span>
                        </label>
                    </div>
                </div>
                <div class="driver-preference-item">
                    <div class="driver-preference-icon">
                        <i class="fas fa-music"></i>
                    </div>
                    <div class="driver-preference-content">
                        <h3 class="driver-preference-label">Music</h3>
                        <p class="driver-preference-text">Driver's choice or passenger's preference</p>
                    </div>
                    <div class="driver-preference-toggle">
                        <label class="driver-toggle-switch">
                            <input type="checkbox" checked>
                            <span class="driver-toggle-slider"></span>
                            <span class="driver-toggle-label" data-on="Allowed" data-off="Not Allowed"></span>
                        </label>
                    </div>
                </div>
                <div class="driver-preference-item">
                    <div class="driver-preference-icon">
                        <i class="fas fa-baby"></i>
                    </div>
                    <div class="driver-preference-content">
                        <h3 class="driver-preference-label">Child Seat</h3>
                        <p class="driver-preference-text">Available for passengers with children</p>
                    </div>
                    <div class="driver-preference-toggle">
                        <label class="driver-toggle-switch">
                            <input type="checkbox">
                            <span class="driver-toggle-slider"></span>
                            <span class="driver-toggle-label" data-on="Available" data-off="Not Available"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-reviews-section animate__animated animate__fadeInUp" style="animation-delay: 0.6s">
            <div class="section-header">
                <h2 class="driver-section-title">Recent Reviews</h2>
                <a href="#" class="driver-view-all-link">View All <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="driver-reviews-container">
                <div class="driver-review-card">
                    <div class="driver-review-rating-banner">
                        <div class="driver-rating-value">5.0</div>
                        <div class="driver-rating-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <div class="driver-review-content">
                        <div class="driver-reviewer-info">
                            <div class="driver-reviewer-avatar">
                                <span>SK</span>
                            </div>
                            <div class="driver-reviewer-details">
                                <h4 class="driver-reviewer-name">Sarah K.</h4>
                                <span class="driver-review-date">
                                    <i class="far fa-calendar-alt"></i> 2 days ago
                                </span>
                            </div>
                        </div>
                        <h3 class="driver-review-title">Great driver, very punctual!</h3>
                        <p class="driver-review-text">John was on time and very friendly. The car was clean and the ride was smooth. Would definitely ride with him again.</p>
                        <div class="driver-review-trip">
                            <i class="fas fa-route"></i> Mumbai to Pune
                        </div>
                    </div>
                </div>

                <div class="driver-review-card">
                    <div class="driver-review-rating-banner">
                        <div class="driver-rating-value">4.5</div>
                        <div class="driver-rating-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                    <div class="driver-review-content">
                        <div class="driver-reviewer-info">
                            <div class="driver-reviewer-avatar">
                                <span>MT</span>
                            </div>
                            <div class="driver-reviewer-details">
                                <h4 class="driver-reviewer-name">Michael T.</h4>
                                <span class="driver-review-date">
                                    <i class="far fa-calendar-alt"></i> 1 week ago
                                </span>
                            </div>
                        </div>
                        <h3 class="driver-review-title">Safe and comfortable ride</h3>
                        <p class="driver-review-text">Very professional driver. Made sure I was comfortable throughout the journey. Highly recommended!</p>
                        <div class="driver-review-trip">
                            <i class="fas fa-route"></i> Delhi to Agra
                        </div>
                    </div>
                </div>

                <div class="driver-review-card">
                    <div class="driver-review-rating-banner">
                        <div class="driver-rating-value">5.0</div>
                        <div class="driver-rating-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <div class="driver-review-content">
                        <div class="driver-reviewer-info">
                            <div class="driver-reviewer-avatar">
                                <span>LR</span>
                            </div>
                            <div class="driver-reviewer-details">
                                <h4 class="driver-reviewer-name">Lisa R.</h4>
                                <span class="driver-review-date">
                                    <i class="far fa-calendar-alt"></i> 2 weeks ago
                                </span>
                            </div>
                        </div>
                        <h3 class="driver-review-title">Excellent conversation</h3>
                        <p class="driver-review-text">John is a great conversationalist but also respects when you need quiet time. Perfect balance!</p>
                        <div class="driver-review-trip">
                            <i class="fas fa-route"></i> Bangalore to Mysore
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
    --primary-light: #ffe180;
    --primary-dark: #e6ac00;
    --primary-transparent: rgba(255, 191, 0, 0.1);
    --secondary: #4f46e5;
    --secondary-light: #6366f1;
    --tertiary: #16a34a;
    --text-dark: #334155;
    --text-medium: #64748b;
    --text-light: #94a3b8;
    --text-xlight: #cbd5e1;
    --bg-light: #ffffff;
    --bg-gray: #f8fafc;
    --bg-light-hover: #f1f5f9;
    --border-light: #e2e8f0;
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.06);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.03);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.02);
    --shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.06), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
    --shadow-inner: inset 0 2px 4px 0 rgba(0, 0, 0, 0.04);
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 20px;
    --radius-full: 9999px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --transition-fast: all 0.15s ease;
    --transition-bounce: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
}

/* Base Styles */
.driver-dashboard-wrapper {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    padding: 40px 20px;
    color: var(--text-dark);
    min-height: 100vh;
    font-weight: 400;
    letter-spacing: 0.01em;
    line-height: 1.5;
}

.driver-dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* Breadcrumb */
.dashboard-breadcrumb {
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    color: var(--text-light);
    transition: var(--transition);
}

.breadcrumb-item {
    text-decoration: none;
    color: var(--text-light);
    transition: var(--transition);
    font-weight: 500;
}

.breadcrumb-item:hover {
    color: var(--primary);
}

.breadcrumb-item.active {
    color: var(--text-dark);
    font-weight: 600;
}

.breadcrumb-separator {
    margin: 0 8px;
    font-size: 0.7rem;
    color: var(--text-xlight);
}

/* Welcome Banner */
.dashboard-welcome-banner {
    background: linear-gradient(135deg, #ffca28, #ffa000);
    border-radius: var(--radius-lg);
    padding: 35px 40px;
    margin-bottom: 30px;
    color: white;
    position: relative;
    overflow: hidden;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--shadow-lg);
    transition: var(--transition);
}

.dashboard-welcome-banner:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.welcome-content {
    position: relative;
    z-index: 2;
    max-width: 65%;
}

.driver-dash-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(8px);
    padding: 5px 10px;
    border-radius: var(--radius-full);
    font-size: 0.7rem;
    font-weight: 500;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.driver-dash-badge i {
    margin-right: 5px;
    font-size: 0.65rem;
}

.driver-dash-title {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 10px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    line-height: 1.3;
    letter-spacing: -0.01em;
}

.driver-dash-highlight {
    position: relative;
    display: inline-block;
    padding-bottom: 2px;
    font-weight: 700;
}

.driver-dash-highlight::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: white;
    transform: scaleX(0);
    transform-origin: left;
    animation: underlineAnimation 0.8s ease-out forwards;
    animation-delay: 1.5s;
}

.driver-dash-subtitle {
    font-size: 1rem;
    margin-bottom: 25px;
    opacity: 0.9;
    font-weight: 400;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    max-width: 90%;
}

.driver-dash-btn {
    display: inline-flex;
    align-items: center;
    background: white;
    color: var(--primary-dark);
    padding: 10px 22px;
    border-radius: var(--radius-sm);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: var(--transition-bounce);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.8);
}

.driver-dash-btn i {
    margin-left: 10px;
    font-size: 0.8rem;
    transition: var(--transition);
}

.driver-dash-btn:hover {
    background: rgba(255, 255, 255, 0.95);
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.driver-dash-btn:hover i {
    transform: translateX(5px);
}

.driver-dash-btn:active {
    transform: translateY(-1px) scale(0.98);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}

.welcome-decoration {
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 40%;
    overflow: hidden;
}

.decoration-circle {
    position: absolute;
    width: 220px;
    height: 220px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    right: -70px;
    top: -70px;
    backdrop-filter: blur(3px);
}

.decoration-shape {
    position: absolute;
    width: 280px;
    height: 280px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
    right: -80px;
    bottom: -80px;
    transform: rotate(25deg);
    backdrop-filter: blur(3px);
}

.car-animation {
    position: absolute;
    bottom: 30px;
    right: calc(50% - 30px);
    font-size: 2.5rem;
    color: rgba(255, 255, 255, 0.9);
    text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    animation: driveAnimation 5s infinite linear;
    z-index: 2;
}

@keyframes driveAnimation {
    0% { 
        transform: translateX(-150px) rotateY(180deg); 
        opacity: 0;
    }
    20% { opacity: 1; }
    80% { opacity: 1; }
    100% { 
        transform: translateX(150px) rotateY(180deg); 
        opacity: 0;
    }
}

.pulse {
    animation: pulse 4s infinite;
}

.floating {
    animation: floating 6s infinite ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.05); opacity: 1; }
    100% { transform: scale(1); opacity: 0.8; }
}

@keyframes floating {
    0% { transform: translate(0, 0) rotate(25deg); }
    50% { transform: translate(-15px, 15px) rotate(20deg); }
    100% { transform: translate(0, 0) rotate(25deg); }
}

/* Stats Grid */
.dashboard-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.driver-stat-card {
    background: var(--bg-light);
    border-radius: var(--radius-md);
    padding: 24px;
    display: flex;
    align-items: flex-start;
    box-shadow: var(--shadow-md);
    transition: var(--transition);
    border: 1px solid var(--border-light);
    position: relative;
    overflow: hidden;
}

.driver-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: var(--primary);
    opacity: 0;
    transition: var(--transition);
}

.driver-stat-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-hover);
}

.driver-stat-card:hover::before {
    opacity: 1;
}

.driver-stat-icon {
    width: 44px;
    height: 44px;
    background-color: var(--primary-transparent);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 18px;
    color: var(--primary);
    font-size: 1.1rem;
    flex-shrink: 0;
    transition: var(--transition);
}

.driver-stat-card:hover .driver-stat-icon {
    background-color: var(--primary);
    color: white;
    transform: scale(1.1);
}

.driver-stat-content {
    flex-grow: 1;
}

.driver-stat-label {
    font-size: 0.825rem;
    font-weight: 500;
    color: var(--text-medium);
    margin-bottom: 8px;
    transition: var(--transition);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.driver-stat-value {
    font-size: 1.6rem;
    font-weight: 600;
    color: var(--text-dark);
    line-height: 1.2;
    display: flex;
    align-items: baseline;
    margin-bottom: 10px;
    letter-spacing: -0.01em;
}

.driver-stat-unit {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--text-light);
    margin-left: 3px;
}

.driver-stat-subtitle {
    font-size: 0.775rem;
    color: var(--text-light);
    margin-top: 5px;
    display: flex;
    align-items: center;
}

.driver-stat-trend {
    display: inline-flex;
    align-items: center;
    font-weight: 600;
    margin-right: 5px;
}

.driver-stat-trend.positive {
    color: var(--tertiary);
}

.driver-stat-trend.negative {
    color: #dc2626;
}

.driver-stat-trend i {
    margin-right: 2px;
    font-size: 0.7rem;
}

.driver-stat-progress {
    height: 3px;
    background-color: var(--bg-gray);
    border-radius: var(--radius-full);
    margin-top: 15px;
    overflow: hidden;
}

.driver-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    border-radius: var(--radius-full);
    transition: width 1s ease-in-out;
}

.driver-star-rating {
    margin-top: 8px;
    color: var(--primary);
    font-size: 0.85rem;
    letter-spacing: 1px;
}

.driver-stat-chart {
    display: flex;
    align-items: flex-end;
    height: 30px;
    gap: 3px;
    margin-top: 12px;
}

.driver-chart-bar {
    flex-grow: 1;
    background: var(--primary-light);
    border-radius: 2px;
    transition: height 1s ease-out;
}

/* Quick Actions */
.dashboard-quick-actions {
    background: var(--bg-light);
    border-radius: var(--radius-md);
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.quick-action-card {
    background: var(--bg-gray);
    border-radius: var(--radius-sm);
    padding: 20px;
    text-decoration: none;
    color: var(--text-dark);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    border: 1px solid transparent;
}

.quick-action-card:hover {
    background: var(--bg-light);
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
    border-color: var(--border-light);
}

.action-icon {
    width: 45px;
    height: 45px;
    background: var(--primary-transparent);
    color: var(--primary);
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 15px;
    transition: var(--transition);
}

.quick-action-card:hover .action-icon {
    background: var(--primary);
    color: white;
    transform: scale(1.1);
}

.quick-action-card h3 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 5px;
    transition: var(--transition);
}

.quick-action-card p {
    font-size: 0.85rem;
    color: var(--text-light);
    margin-bottom: 20px;
}

.action-arrow {
    position: absolute;
    bottom: 20px;
    right: 20px;
    font-size: 0.9rem;
    color: var(--text-light);
    transition: var(--transition);
    opacity: 0;
    transform: translateX(-10px);
}

.quick-action-card:hover .action-arrow {
    opacity: 1;
    transform: translateX(0);
    color: var(--primary);
}

/* Driver Preferences Section Styles */
.dashboard-preferences-section {
    background: var(--bg-light);
    border-radius: var(--radius-md);
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
    position: relative;
    overflow: hidden;
}

.dashboard-preferences-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(to bottom, var(--primary), var(--primary-dark));
}

.driver-preferences-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 25px;
}

.driver-preference-item {
    display: flex;
    align-items: center;
    padding: 18px;
    background-color: var(--bg-gray);
    border-radius: var(--radius-sm);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    border: 1px solid transparent;
}

.driver-preference-item:hover {
    transform: translateY(-5px);
    background-color: var(--bg-light);
    box-shadow: var(--shadow-md);
    border-color: var(--border-light);
}

.driver-preference-icon {
    width: 48px;
    height: 48px;
    background-color: var(--primary-transparent);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: var(--primary);
    font-size: 1.2rem;
    flex-shrink: 0;
    transition: var(--transition);
}

.driver-preference-item:hover .driver-preference-icon {
    background-color: var(--primary);
    color: white;
    transform: scale(1.1);
}

.driver-preference-content {
    flex-grow: 1;
}

.driver-preference-content h3 {
    font-size: 1.05rem;
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--text-dark);
    transition: var(--transition);
}

.driver-preference-content p {
    font-size: 0.85rem;
    color: var(--text-light);
    margin: 0;
}

.driver-preference-toggle {
    margin-left: 15px;
}

.driver-toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
    cursor: pointer;
}

.driver-toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.driver-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #e2e8f0;
    transition: var(--transition);
    border-radius: var(--radius-full);
}

.driver-toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: var(--transition);
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.driver-toggle-switch input:checked + .driver-toggle-slider {
    background-color: var(--primary);
}

.driver-toggle-switch input:focus + .driver-toggle-slider {
    box-shadow: 0 0 1px var(--primary);
}

.driver-toggle-switch input:checked + .driver-toggle-slider:before {
    transform: translateX(24px);
}

.driver-toggle-label {
    display: block;
    position: absolute;
    top: 30px;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--text-light);
    left: 0;
    right: 0;
    text-align: center;
    opacity: 0;
    transition: var(--transition);
}

.driver-preference-item:hover .driver-toggle-label {
    opacity: 1;
    top: 32px;
}

.driver-toggle-switch input:checked ~ .driver-toggle-label::after {
    content: attr(data-on);
    color: var(--tertiary);
}

.driver-toggle-switch input:not(:checked) ~ .driver-toggle-label::after {
    content: attr(data-off);
    color: var(--text-light);
}

/* Reviews Section Styles */
.dashboard-reviews-section {
    background: var(--bg-light);
    border-radius: var(--radius-md);
    padding: 30px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
    position: relative;
    overflow: hidden;
}

.dashboard-reviews-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(to bottom, var(--primary), var(--primary-dark));
}

.section-header {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border-light);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.driver-section-title {
    font-size: 1.15rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 5px;
    position: relative;
    display: inline-block;
    padding-bottom: 5px;
    letter-spacing: -0.01em;
}

.driver-section-title::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 35px;
    height: 2px;
    background: var(--primary);
    border-radius: var(--radius-full);
}

.driver-section-subtitle {
    font-size: 0.85rem;
    color: var(--text-light);
    margin-top: 5px;
    font-weight: 400;
}

/* Driver Verification Status */
.driver-verification-status {
    padding: 18px 20px;
    background-color: rgba(79, 70, 229, 0.04);
    border-radius: var(--radius-sm);
    border: 1px solid rgba(79, 70, 229, 0.1);
}

.driver-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 10px;
    border-radius: 50px;
    margin-bottom: 12px;
    font-size: 0.8rem;
    font-weight: 500;
    letter-spacing: 0.02em;
}

.driver-status-badge.verified {
    background-color: rgba(56, 161, 105, 0.08);
    color: #38a169;
    border: 1px solid rgba(56, 161, 105, 0.15);
}

.driver-status-badge i {
    margin-right: 5px;
    font-size: 0.75rem;
}

.driver-verification-text {
    color: var(--text-medium);
    font-size: 0.9rem;
    line-height: 1.5;
    margin: 0;
    font-weight: 400;
}

/* Driver Information */
.info-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.info-item {
    display: flex;
    align-items: center;
    padding: 16px;
    background-color: var(--bg-gray);
    border-radius: var(--radius-sm);
    transition: var(--transition);
    margin-bottom: 0;
    border: 1px solid transparent;
}

.info-icon {
    width: 38px;
    height: 38px;
    background-color: white;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 14px;
    color: var(--primary);
    font-size: 1rem;
    flex-shrink: 0;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
}

.driver-info-label {
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 3px;
    color: var(--text-dark);
    transition: var(--transition);
}

.driver-info-text {
    font-size: 0.85rem;
    color: var(--text-light);
    margin: 0;
    font-weight: 400;
}

/* Driver Preferences */
.driver-preference-item {
    display: flex;
    align-items: center;
    padding: 16px;
    background-color: var(--bg-gray);
    border-radius: var(--radius-sm);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    border: 1px solid transparent;
}

.driver-preference-icon {
    width: 42px;
    height: 42px;
    background-color: var(--primary-transparent);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 14px;
    color: var(--primary);
    font-size: 1.1rem;
    flex-shrink: 0;
    transition: var(--transition);
}

.driver-preference-content {
    flex-grow: 1;
}

.driver-preference-label {
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 3px;
    color: var(--text-dark);
    transition: var(--transition);
}

.driver-preference-text {
    font-size: 0.85rem;
    color: var(--text-light);
    margin: 0;
    font-weight: 400;
}

/* Reviews Section */
.driver-reviews-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 22px;
}

.driver-review-card {
    border-radius: var(--radius-md);
    transition: var(--transition);
    overflow: hidden;
    background-color: var(--bg-light);
    border: 1px solid var(--border-light);
    box-shadow: var(--shadow-sm);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.driver-review-rating-banner {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 12px 15px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.driver-rating-value {
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1;
    letter-spacing: -0.02em;
}

.driver-rating-stars {
    color: white;
    font-size: 0.85rem;
    letter-spacing: 2px;
}

.driver-review-content {
    padding: 18px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    position: relative;
}

.driver-reviewer-info {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
}

.driver-reviewer-avatar {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-full);
    background: linear-gradient(135deg, var(--secondary-light), var(--secondary));
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    color: white;
    font-weight: 500;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.driver-reviewer-name {
    font-size: 0.95rem;
    font-weight: 500;
    margin: 0 0 3px;
    color: var(--text-dark);
}

.driver-review-date {
    font-size: 0.78rem;
    color: var(--text-light);
    display: flex;
    align-items: center;
    gap: 4px;
}

.driver-review-date i {
    font-size: 0.75rem;
}

.driver-review-title {
    font-size: 1rem;
    font-weight: 500;
    margin: 0 0 8px;
    color: var(--text-dark);
    letter-spacing: -0.01em;
}

.driver-review-text {
    font-size: 0.85rem;
    color: var(--text-medium);
    line-height: 1.5;
    margin: 0 0 15px;
    flex-grow: 1;
    font-weight: 400;
}

.driver-review-trip {
    font-size: 0.8rem;
    color: var(--text-light);
    display: flex;
    align-items: center;
    gap: 6px;
    padding-top: 10px;
    border-top: 1px dashed var(--border-light);
    margin-top: auto;
}

.driver-review-trip i {
    color: var(--primary);
    font-size: 0.78rem;
}

/* View All Link */
.driver-view-all-link {
    color: var(--primary);
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    transition: var(--transition);
    padding: 7px 12px;
    border-radius: var(--radius-sm);
    background-color: var(--primary-transparent);
}

@keyframes underlineAnimation {
    0% { transform: scaleX(0); }
    100% { transform: scaleX(1); }
}

/* Responsive Design */
@media (max-width: 1200px) {
    .dashboard-stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    }
    
    .driver-preferences-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
}

@media (max-width: 992px) {
    .dashboard-welcome-banner {
        padding: 30px;
    }
    
    .welcome-content {
        max-width: 100%;
    }
    
    .welcome-content h1 {
        font-size: 1.8rem;
    }
    
    .welcome-decoration {
        display: none;
    }
    
    .dashboard-sections-grid,
    .quick-actions-grid,
    .driver-reviews-container {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }
    
    .stat-value {
        font-size: 1.6rem;
    }
    
    .driver-preferences-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
}

@media (max-width: 768px) {
    .driver-dashboard-wrapper {
        padding: 20px 15px;
    }
    
    .dashboard-welcome-banner {
        padding: 25px;
    }
    
    .welcome-content h1 {
        font-size: 1.5rem;
    }
    
    .dashboard-stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(100%, 1fr));
    }
    
    .dashboard-sections-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions-grid,
    .driver-preferences-grid {
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 15px;
    }
    
    .driver-reviews-container {
        grid-template-columns: 1fr;
    }
    
    .stat-card, 
    .dashboard-section, 
    .dashboard-preferences-section, 
    .dashboard-reviews-section,
    .dashboard-quick-actions {
        padding: 20px;
    }
    
    .section-header h2 {
        font-size: 1.2rem;
    }
    
    .info-item,
    .driver-preference-item,
    .driver-review-card {
        padding: 15px;
    }
    
    .info-icon,
    .driver-preference-icon {
        width: 36px;
        height: 36px;
        font-size: 0.9rem;
    }
    
    .info-content h3,
    .driver-preference-content h3 {
        font-size: 0.9rem;
    }
    
    .driver-review-content {
        padding: 15px;
    }
    
    .driver-review-title {
        font-size: 1rem;
    }
    
    .driver-rating-value {
        font-size: 1.5rem;
    }
}

@media (max-width: 576px) {
    .dashboard-welcome-banner {
        padding: 20px;
    }
    
    .welcome-badge {
        font-size: 0.7rem;
        padding: 4px 10px;
    }
    
    .welcome-content h1 {
        font-size: 1.3rem;
    }
    
    .welcome-content p {
        font-size: 0.9rem;
    }
    
    .primary-btn {
        padding: 10px 20px;
        font-size: 0.9rem;
        width: 100%;
        justify-content: center;
    }
    
    .stat-value {
        font-size: 1.4rem;
    }
    
    .quick-actions-grid,
    .driver-preferences-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-action-card,
    .driver-preference-item {
        display: flex;
        flex-direction: row;
        align-items: center;
        padding: 15px;
    }
    
    .action-icon,
    .driver-preference-icon {
        margin-bottom: 0;
        margin-right: 15px;
        width: 40px;
        height: 40px;
    }
    
    .quick-action-card h3,
    .driver-preference-content h3 {
        font-size: 1rem;
        margin-bottom: 2px;
    }
    
    .quick-action-card p,
    .driver-preference-content p {
        margin-bottom: 0;
        font-size: 0.8rem;
    }
    
    .action-arrow {
        position: static;
        margin-left: auto;
        opacity: 1;
        transform: none;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .driver-view-all-link {
        margin-top: 10px;
        align-self: flex-end;
    }
}

/* Count-up Animation */
.count-up {
    animation: countUp 2s ease-out forwards;
    animation-delay: 0.5s;
}

@keyframes countUp {
    from { content: "0"; }
    to { content: "124"; }
}

.driver-toggle-ripple {
    position: absolute;
    background: rgba(255, 191, 0, 0.3);
    border-radius: 50%;
    transform: scale(0);
    pointer-events: none;
    z-index: 0;
}

.driver-toggle-ripple.active {
    animation: rippleEffect 0.6s linear;
}

@keyframes rippleEffect {
    0% {
        transform: scale(0);
        opacity: 0.5;
    }
    100% {
        transform: scale(1);
        opacity: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate elements when they come into view
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.animate__animated:not(.animate__fadeIn)');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect();
            // If element is in viewport
            if (elementPosition.top < window.innerHeight && elementPosition.bottom >= 0) {
                // Add animation class
                const animationClass = element.classList.contains('animate__fadeInUp') 
                    ? 'animate__fadeInUp' 
                    : 'animate__fadeIn';
                element.classList.add(animationClass);
                element.style.opacity = 1;
            }
        });
    };
    
    // Run on load
    animateOnScroll();
    
    // Run on scroll
    window.addEventListener('scroll', animateOnScroll);
    
    // Toggle switches functionality
    const toggles = document.querySelectorAll('.driver-toggle-switch input');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const preference = this.closest('.driver-preference-item').querySelector('.driver-preference-content h3').textContent;
            console.log(`${preference} preference changed to: ${this.checked ? 'Allowed' : 'Not allowed'}`);
            // Here you would typically send this data to your server
        });
    });
    
    // Animate stat numbers with countUp
    const animateNumbers = () => {
        const countElements = document.querySelectorAll('.count-up');
        
        countElements.forEach(element => {
            const finalValue = parseInt(element.textContent);
            let startValue = 0;
            const duration = 2000; // 2 seconds
            const startTime = performance.now();
            
            function updateCount(currentTime) {
                const elapsedTime = currentTime - startTime;
                if (elapsedTime < duration) {
                    const progress = elapsedTime / duration;
                    const currentValue = Math.floor(progress * finalValue);
                    element.textContent = currentValue;
                    requestAnimationFrame(updateCount);
                } else {
                    element.textContent = finalValue;
                }
            }
            
            requestAnimationFrame(updateCount);
        });
    };
    
    // Start the animation after a short delay
    setTimeout(animateNumbers, 500);
    
    // Initialize chart bars animation
    const chartBars = document.querySelectorAll('.driver-chart-bar');
    chartBars.forEach(bar => {
        // Store original height
        const targetHeight = bar.style.height;
        // Set initial height to 0
        bar.style.height = '0%';
        
        setTimeout(() => {
            // Animate to target height
            bar.style.height = targetHeight;
        }, 800);
    });
    
    // Add hover effects for review cards
    const reviewCards = document.querySelectorAll('.driver-review-card');
    reviewCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.querySelector('.driver-rating-stars').classList.add('animate__animated', 'animate__pulse');
        });
        
        card.addEventListener('mouseleave', function() {
            this.querySelector('.driver-rating-stars').classList.remove('animate__animated', 'animate__pulse');
        });
    });
    
    // Enhanced hover effects for preference items
    const preferenceItems = document.querySelectorAll('.driver-preference-item');
    preferenceItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.driver-preference-icon');
            icon.classList.add('animate__animated', 'animate__heartBeat');
        });
        
        item.addEventListener('mouseleave', function() {
            const icon = this.querySelector('.driver-preference-icon');
            icon.classList.remove('animate__animated', 'animate__heartBeat');
        });
    });
    
    // Staggered animation for preferences and reviews
    function animateStaggered(selector, baseDelay = 100) {
        const elements = document.querySelectorAll(selector);
        elements.forEach((element, index) => {
            setTimeout(() => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, 50);
            }, index * baseDelay);
        });
    }
    
    // Animate preferences and reviews with staggered effect
    setTimeout(() => {
        animateStaggered('.driver-preference-item', 100);
    }, 800);
    
    setTimeout(() => {
        animateStaggered('.driver-review-card', 150);
    }, 1000);
    
    // Add ripple effect to toggle switches
    const toggleSliders = document.querySelectorAll('.driver-toggle-slider');
    toggleSliders.forEach(slider => {
        slider.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.classList.add('driver-toggle-ripple');
            this.appendChild(ripple);
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height) * 2;
            
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${e.clientX - rect.left - size/2}px`;
            ripple.style.top = `${e.clientY - rect.top - size/2}px`;
            
            ripple.classList.add('active');
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});
</script>
<br><?php include 'footer.php';?>
</div></body>
</html>
