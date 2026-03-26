<?php
session_start();
require_once 'db.php';

if (!isset($_GET['booking_id'])) {
    header('Location: index.php');
    exit();
}

$bookingId = $_GET['booking_id'];

// Get booking details
$stmt = $conn->prepare("
    SELECT b.*, t.departure_city, t.destination_city, t.departure_date, t.departure_time,
           u.email as user_email, u.full_name as user_name,
           d.Email as driver_email, d.Full_Name as driver_name
    FROM bookings b
    JOIN trips t ON b.trip_id = t.id
    JOIN users u ON b.user_id = u.id
    JOIN drivers d ON t.driver_email = d.Email
    WHERE b.id = ? AND b.payment_status = 'success'
");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - PoolPal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .success-icon {
            color: #28a745;
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .success-title {
            color: #333;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .success-message {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }
        
        .booking-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .booking-details h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .detail-item {
            margin-bottom: 10px;
            color: #555;
        }
        
        .detail-item i {
            width: 20px;
            color: #007bff;
            margin-right: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .action-button {
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        
        .primary-button {
            background: #007bff;
            color: #fff;
            border: none;
        }
        
        .primary-button:hover {
            background: #0056b3;
        }
        
        .secondary-button {
            background: #6c757d;
            color: #fff;
            border: none;
        }
        
        .secondary-button:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="success-container">
        <i class="fas fa-check-circle success-icon"></i>
        <h1 class="success-title">Booking Confirmed!</h1>
        <p class="success-message">Your ride has been successfully booked. We've sent the confirmation details to your email.</p>
        
        <div class="booking-details">
            <h3>Booking Details</h3>
            <div class="detail-item">
                <i class="fas fa-map-marker-alt"></i>
                From: <?php echo htmlspecialchars($booking['departure_city']); ?>
            </div>
            <div class="detail-item">
                <i class="fas fa-map-marker"></i>
                To: <?php echo htmlspecialchars($booking['destination_city']); ?>
            </div>
            <div class="detail-item">
                <i class="far fa-calendar-alt"></i>
                Date: <?php echo date("l, F j, Y", strtotime($booking['departure_date'])); ?>
            </div>
            <div class="detail-item">
                <i class="far fa-clock"></i>
                Time: <?php echo date("g:i A", strtotime($booking['departure_time'])); ?>
            </div>
            <div class="detail-item">
                <i class="fas fa-user"></i>
                Driver: <?php echo htmlspecialchars($booking['driver_name']); ?>
            </div>
            <div class="detail-item">
                <i class="fas fa-chair"></i>
                Seats: <?php echo htmlspecialchars($booking['seats_booked']); ?>
            </div>
            <div class="detail-item">
                <i class="fas fa-rupee-sign"></i>
                Amount Paid: ₹<?php echo htmlspecialchars(number_format($booking['total_amount'], 2)); ?>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="dashboard.php" class="action-button primary-button">
                <i class="fas fa-home"></i> Go to Dashboard
            </a>
            <a href="mytrips.php" class="action-button secondary-button">
                <i class="fas fa-list"></i> View My Trips
            </a>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html> 