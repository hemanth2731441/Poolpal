<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

session_start();
include 'header.php';
include 'db.php'; // make sure this file connects using $conn
require_once 'email_functions.php'; // use require_once instead of include

// Check database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verify booking ID is provided
if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
    header('Location: dashboard.php');
    exit();
}

$booking_id = (int)$_GET['booking_id'];
$user_id = $_SESSION['user_id'];

try {
    // Get booking details with trip, driver, and user information
    $query = "SELECT b.*, t.*, d.*, u.phone as user_phone, u.full_name as user_full_name
              FROM bookings b 
              JOIN trips t ON b.trip_id = t.id 
              LEFT JOIN drivers d ON t.driver_email = d.Email 
              LEFT JOIN users u ON b.user_id = u.id
              WHERE b.id = ? AND b.user_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Booking not found or unauthorized access");
    }
    
    $booking = $result->fetch_assoc();
    $ride = $booking; // For compatibility with existing template
    
    // Set profile picture
    $profilePic = !empty($booking['Profile_Pic']) ? $booking['Profile_Pic'] : 'images/default_profile.png';
    
    // Send final confirmation email if payment is completed
    if ($booking['payment_status'] === 'completed') {
        $finalConfirmationSubject = "PoolPal - Payment Confirmed (ID: RS-" . str_pad($booking['id'], 5, '0', STR_PAD_LEFT) . ")";
        $finalConfirmationBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #ffbf00; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .details { margin: 20px 0; }
                .footer { font-size: 12px; text-align: center; margin-top: 20px; color: #777; }
                .success { color: #28a745; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Payment Confirmation</h2>
                </div>
                <div class='content'>
                    <p class='success'>Payment Successful!</p>
                    <p>Dear " . htmlspecialchars($booking['user_full_name']) . ",</p>
                    <p>Your payment has been confirmed for your upcoming trip. Here are your trip details:</p>
                    <div class='details'>
                        <p><strong>Booking ID:</strong> RS-" . str_pad($booking['id'], 5, '0', STR_PAD_LEFT) . "</p>
                        <p><strong>From:</strong> " . htmlspecialchars($booking['departure_city']) . "</p>
                        <p><strong>To:</strong> " . htmlspecialchars($booking['destination_city']) . "</p>
                        <p><strong>Date:</strong> " . date("l, F j, Y", strtotime($booking['departure_date'])) . "</p>
                        <p><strong>Time:</strong> " . date("g:i A", strtotime($booking['departure_time'])) . "</p>
                        <p><strong>Seats Booked:</strong> " . $booking['seats_booked'] . "</p>
                        <p><strong>Amount Paid:</strong> ₹" . $booking['total_amount'] . "</p>
                        <p><strong>Driver Name:</strong> " . htmlspecialchars($booking['driver_name']) . "</p>
                        <p><strong>Driver Contact:</strong> " . htmlspecialchars($booking['Contact']) . "</p>
                    </div>
                    <p>Please save this email for your records. You can also view your booking details anytime by logging into your PoolPal account.</p>
                    <p>Have a great trip!</p>
                </div>
                <div class='footer'>
                    <p>Thank you for choosing PoolPal!</p>
                </div>
            </div>
        </body>
        </html>";

        // Send to customer
        if (!sendEmail($booking['user_email'], $finalConfirmationSubject, $finalConfirmationBody)) {
            if (!isset($_SESSION['email_errors'])) {
                $_SESSION['email_errors'] = [];
            }
            $_SESSION['email_errors'][] = "Failed to send payment confirmation email";
        }

        // Send to driver
        $driverFinalSubject = "PoolPal - Payment Received (ID: RS-" . str_pad($booking['id'], 5, '0', STR_PAD_LEFT) . ")";
        $driverFinalBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #ffbf00; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .details { margin: 20px 0; }
                .footer { font-size: 12px; text-align: center; margin-top: 20px; color: #777; }
                .success { color: #28a745; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Payment Received</h2>
                </div>
                <div class='content'>
                    <p class='success'>Payment Successful!</p>
                    <p>Hello,</p>
                    <p>The payment for booking ID RS-" . str_pad($booking['id'], 5, '0', STR_PAD_LEFT) . " has been confirmed.</p>
                    <div class='details'>
                        <p><strong>Passenger Name:</strong> " . htmlspecialchars($booking['user_full_name']) . "</p>
                        <p><strong>Passenger Contact:</strong> " . htmlspecialchars($booking['user_phone']) . "</p>
                        <p><strong>From:</strong> " . htmlspecialchars($booking['departure_city']) . "</p>
                        <p><strong>To:</strong> " . htmlspecialchars($booking['destination_city']) . "</p>
                        <p><strong>Date:</strong> " . date("l, F j, Y", strtotime($booking['departure_date'])) . "</p>
                        <p><strong>Time:</strong> " . date("g:i A", strtotime($booking['departure_time'])) . "</p>
                        <p><strong>Seats Booked:</strong> " . $booking['seats_booked'] . "</p>
                        <p><strong>Amount:</strong> ₹" . $booking['total_amount'] . "</p>
                    </div>
                    <p>Please ensure you're ready for the trip at the scheduled time.</p>
                </div>
                <div class='footer'>
                    <p>Thank you for using PoolPal!</p>
                </div>
            </div>
        </body>
        </html>";

        if (!sendEmail($booking['driver_email'], $driverFinalSubject, $driverFinalBody)) {
            if (!isset($_SESSION['email_errors'])) {
                $_SESSION['email_errors'] = [];
            }
            $_SESSION['email_errors'][] = "Failed to send payment confirmation email to driver";
        }
    }
} catch (Exception $e) {
    header('Location: dashboard.php?error=' . urlencode($e->getMessage()));
    exit();
}

// Display email errors if any
if (isset($_SESSION['email_errors']) && !empty($_SESSION['email_errors'])) {
    $emailErrors = $_SESSION['email_errors'];
    unset($_SESSION['email_errors']); // Clear after displaying
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9fb;
            color: #333;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            width: 100%;
            box-sizing: border-box;
        }
    
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            box-sizing: border-box;
        }
        
        .breadcrumb {
            font-size: 14px;
            color: #999;
            margin-bottom: 20px;
            white-space: nowrap;
            overflow-x: auto;
            padding-bottom: 5px;
            -webkit-overflow-scrolling: touch;
        }
        
        .breadcrumb a {
            color: #999;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .breadcrumb a:hover {
            color: #ffbf00;
        }
        
        .success-box {
            background-color: rgb(255, 255, 255);
            border: 3px solid rgb(243, 240, 240);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .success-box i {
            margin-right: 12px;
            font-size: 20px;
        }
        
        .success-box h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .success-box p {
            font-size: 14px;
            color: #666;
            margin: 0;
            width: 100%;
            margin-top: 5px;
        }
        
        h2, h3 {
            color: #333;
            margin-top: 30px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        h2 {
            font-size: 20px;
        }
        
        h3 {
            font-size: 18px;
        }
        
        .btn {
            background: #ffbf00;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 14px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
            -webkit-tap-highlight-color: transparent;
        }
        
        .btn:hover {
            background: #e6ac00;
        }
        
        .trip-details {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .trip-card {
            background-color: rgb(255, 255, 255);
            border: 2.2px solid rgb(243, 240, 240);
            padding: 20px;
            border-radius: 10px;
            flex: 1;
            min-width: 250px;
            transition: transform 0.3s, box-shadow 0.3s;
            box-sizing: border-box;
        }
        
        .trip-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .trip-card i {
            color: #ffbf00;
            margin-left: 10px;
        }
        
        .trip-card h3 {
            font-size: 16px;
            margin: 12px 0 8px;
            color: #333;
        }
        
        .trip-card p {
            font-size: 14px;
            color: #666;
            margin: 0;
            line-height: 1.4;
        }
        
        .booking-info {
            margin-top: 20px;
            border: 2px solid #f3f0f0;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #f3f0f0;
            font-size: 14px;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-row img {
            flex-shrink: 0;
        }
        
        .info-row span {
            color: #333;
            font-weight: 500;
            flex: 1;
            margin-left: 12px;
        }
        
        .info-row p {
            margin: 0;
            font-weight: 500;
            color: #555;
            text-align: right;
            margin-left: auto;
            padding-left: 0;
        }
        
        .driver-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            gap: 15px;
        }
        
        .driver-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }
        
        .name {
            margin-right: 0;
            flex: 1;
        }
        
        .name strong {
            display: block;
            margin-bottom: 4px;
            font-size: 15px;
        }
        
        .name p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }
        
        .icon {
            display: flex;
            gap: 15px;
            font-size: 18px;
            color: #ffbf00;
        }
        
        .notes {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .btn-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            margin-bottom: 10px;
        }
        
        .btn-primary {
            background: #ffbf00;
            color: white;
            min-width: 180px;
        }
        
        .btn-secondary {
            background: #E5E7EB;
            color: #333;
        }
        
        .profile-photo {
            display: flex;
            align-items: center;
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border: 2px solid #f3f0f0;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .profile-photo:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .photo-box {
            display: flex;
            align-items: center;
            gap: 15px;
            width: 100%;
        }
        
        .photo-box .icon-container {
            width: 45px;
            height: 45px;
            min-width: 45px;
            background: #f6f5ff;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .photo-box i {
            font-size: 18px;
            color: #6C49F4;
        }
        
        .text-content {
            display: flex;
            flex-direction: column;
            gap: 3px;
            flex: 1;
        }
        
        .text-content .title {
            font-size: 15px;
            font-weight: 500;
            color: #333;
            display: block;
        }
        
        .text-content .subtitle {
            font-size: 13px;
            color: #666;
            display: block;
        }
        
        .trip-image {
            width: 70px;
            height: 60px;
            object-fit: contain;
            margin-right: 5px;
        }
        
        .icon-img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        
        .iconss-img {
            width: 28px;
            height: 28px;
            object-fit: contain;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
                padding: 15px;
            }
            
            .trip-card {
                padding: 15px;
                min-width: 200px;
            }
            
            .btn {
                padding: 10px 15px;
            }
            
            .driver-section {
                flex-wrap: wrap;
            }
            
            .icon {
                margin-top: 10px;
            }
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 15px auto;
                border-radius: 0;
                box-shadow: none;
                padding: 15px;
            }
            
            .trip-details {
                flex-direction: column;
                gap: 10px;
            }
            
            .trip-card {
                width: 100%;
                min-width: 0;
            }
            
            .info-row {
                flex-wrap: wrap;
                padding: 12px;
            }
            
            .info-row span {
                width: 100%;
                margin-bottom: 5px;
                margin-left: 5px;
            }
            
            .info-row p {
                width: 100%;
                text-align: left;
                margin-left: 45px;
            }
            
            .driver-section {
                padding: 12px;
            }
            
            .name {
                margin-right: 0;
            }
            
            .icon {
                width: 100%;
                justify-content: flex-start;
                margin-top: 15px;
                margin-left: 55px;
            }
            
            .photo-box {
                gap: 10px;
            }
            
            .btn-container {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
            
            h2 {
                font-size: 18px;
            }
            
            h3 {
                font-size: 16px;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 12px;
            }
            
            .success-box {
                padding: 15px;
            }
            
            .success-box h2 {
                font-size: 16px;
            }
            
            .success-box p {
                font-size: 13px;
            }
            
            .trip-card h3 {
                font-size: 15px;
                margin-top: 10px;
            }
            
            .trip-card p {
                font-size: 13px;
            }
            
            .info-row {
                font-size: 13px;
            }
            
            .text-content .title {
                font-size: 14px;
            }
            
            .text-content .subtitle {
                font-size: 12px;
            }
            
            .icon-img, .iconss-img {
                width: 25px;
                height: 25px;
            }
            
            .photo-box .icon-container {
                width: 40px;
                height: 40px;
                min-width: 40px;
            }
        }
    </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
    <div class="container">
        <div class="breadcrumb">
            <a href="dashboard.php">Home</a> > <a href="result.php">Trips</a> > <a href="#">Booking Confirmation</a>
        </div>
        
        <?php if (isset($emailErrors) && !empty($emailErrors)): ?>
        <div class="card" style="background-color: #fff8e6; border-left: 4px solid #ffc107; margin-bottom: 20px;">
            <h3 style="color: #856404; margin-top: 0;">Email Notification Status</h3>
            <p>Your booking was successful, but there were issues sending the confirmation emails:</p>
            <ul style="color: #856404; margin-bottom: 10px;">
                <?php foreach($emailErrors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <p>Don't worry, your booking is still confirmed. Please take a screenshot of this page for your records.</p>
        </div>
        <?php endif; ?>
        
        <div class="success-box">
            <h2><i class="fas fa-check-circle" style="color: #ffbf00;"></i> Booking Confirmed</h2>
            <p>Your booking has been successfully confirmed. An email with details has been sent to your email address.</p>
        </div>
        
        <h2>Trip Details</h2>
        <div class="trip-details">
            <div class="trip-card">
                <img src="images/icons/starting.gif" alt="Departure" class="icon-img">
                <h3>Departure</h3>
                <p><?= htmlspecialchars($ride['departure_city']) ?> • <?= date("l", strtotime($ride['departure_date'])) ?>, <?= date("g:i A", strtotime($ride['departure_time'])) ?></p>
            </div>
            <div class="trip-card">
                <img src="images/icons/ending.gif" alt="Arrival" class="icon-img">
                <h3>Arrival</h3>
                <p><?= htmlspecialchars($ride['destination_city']) ?> • <?= date("l", strtotime($ride['departure_date'])) ?>, <?= date("g:i A", strtotime($ride['arrival_time'])) ?></p>
            </div>
        </div>
        
        <h2>Booking Information</h2>
        <div class="booking-info">
            <div class="info-row">
                <img src="images/icons/ticket.png" alt="Booking ID" class="iconss-img">
                <span>Booking ID</span>
                <p>RS-<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></p>
            </div>
            <div class="info-row">
                <img src="images/icons/card.png" alt="Payment Method" class="iconss-img">
                <span>Payment Method</span>
                <p>Cash</p>
            </div>
            <div class="info-row">
                <img src="images/icons/cash.png" alt="Amount Paid" class="iconss-img">
                <span>Amount Paid</span>
                <p>₹<?= $booking['total_amount'] ?></p>
            </div>
            <div class="info-row">
                <img src="images/icons/chair.gif" alt="Seats Booked" class="iconss-img">
                <span>Seats Booked</span>
                <p><?= $booking['seats_booked'] ?> seat(s)</p>
            </div>
        </div>
        
        <h3>Driver Information</h3>
        <div class="card driver-section">
            <img src="<?= htmlspecialchars($profilePic) ?>" alt="Driver" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
            <div class="name">
                <strong><?= htmlspecialchars($ride['driver_name']) ?></strong>
                <p>4.8 ★  • 124 rides</p>
            </div>
            <div class="icon">
                <a href="tel:<?= htmlspecialchars($ride['Contact']) ?>" style="text-decoration: none;">
                    <img src="images/icons/call.gif" alt="Call" class="icon-img" style="cursor: pointer;">
                </a>
                <a href="https://wa.me/91<?=$ride['Contact'] ?>?text=Hi <?= urlencode($ride['driver_name']) ?>, I have booked a ride with you." target="_blank" style="text-decoration: none;">
                    <img src="images/icons/message.gif" alt="Message" class="icon-img" style="cursor: pointer;">
                </a>
            </div>
        </div>
        
        <h3>Pickup Information</h3>
        <div class="profile-photo">
            <div class="photo-box">
                <div class="icon-container">
                    <img src="images/icons/map.png" alt="Location" class="iconss-img">
                </div>
                <div class="text-content">
                    <span class="title"><?= htmlspecialchars($ride['departure_city']) ?></span>
                    <span class="subtitle"><?= date("l", strtotime($ride['departure_date'])) ?>, <?= date("g:i A", strtotime($ride['departure_time'])) ?></span>
                </div>
            </div>
        </div>
        
        <h3>Important Notes</h3>
        <div class="profile-photo">
            <div class="photo-box">
                <div class="icon-container">
                    <img src="images/icons/ban.png" alt="Cancellation Policy" class="iconss-img">
                </div>
                <div class="text-content">
                    <span class="title">Cancellation Policy</span>
                    <span class="subtitle">Free cancellation up to 24 hours before departure</span>
                </div>
            </div>
        </div>
        <div class="profile-photo">
            <div class="photo-box">
                <div class="icon-container">
                    <img src="images/icons/suitcase.png" alt="Luggage Allowance" class="iconss-img">
                </div>
                <div class="text-content">
                    <span class="title">Luggage Allowance</span>
                    <span class="subtitle"><?php echo htmlspecialchars($ride['luggage_space'])?: 'No luggage information available'; ?></span>
                </div>
            </div>
        </div>
        
        <div class="btn-container">
            <a href="dashboard.php"><button class="btn btn-primary">Back to Dashboard</button></a>
        </div>
    </div>
</div></body>
</html>
