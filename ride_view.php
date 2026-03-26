<?php
include 'header.php';
include 'db.php';

// Check if trip ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}

$trip_id = $_GET['id'];
$user_email = $_SESSION['user_email'] ?? '';

// Fetch trip details with driver information and available seats
$sql = "SELECT t.*, 
        t.driver_name, 
        t.driver_phone, 
        t.driver_email,
        t.vehicle_number,
        4.5 AS driver_rating,
        (t.seats - COALESCE(SUM(b.seats_booked), 0)) AS available_seats,
        (SELECT COUNT(*) FROM bookings WHERE trip_id = t.id) AS total_bookings
        FROM trips t
        LEFT JOIN bookings b ON t.id = b.trip_id
        WHERE t.id = ?
        GROUP BY t.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $trip_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>
        alert('Trip not found.');
        window.location.href='dashboard.php';
    </script>";
    exit;
}

$trip = $result->fetch_assoc();

// Check if user has already booked this trip
$user_booking = null;
if (!empty($user_email)) {
    $check_booking = "SELECT * FROM bookings WHERE trip_id = ? AND user_email = ?";
    $stmt_check = $conn->prepare($check_booking);
    $stmt_check->bind_param("is", $trip_id, $user_email);
    $stmt_check->execute();
    $booking_result = $stmt_check->get_result();
    if ($booking_result->num_rows > 0) {
        $user_booking = $booking_result->fetch_assoc();
    }
}

// Format trip times and calculate trip duration
$departure_time = new DateTime($trip['departure_time']);
$arrival_time = new DateTime($trip['arrival_time']);
$trip_duration = $departure_time->diff($arrival_time);

// Format duration string
if ($trip_duration->h > 0) {
    $duration_str = $trip_duration->h . ' hour' . ($trip_duration->h > 1 ? 's' : '');
    if ($trip_duration->i > 0) {
        $duration_str .= ' ' . $trip_duration->i . ' min';
    }
} else {
    $duration_str = $trip_duration->i . ' minutes';
}

// Calculate departure date in human readable format
$departure_date = new DateTime($trip['departure_date']);
$departure_date_str = $departure_date->format('l, F j, Y');

// Check if trip is in the past
$trip_datetime = new DateTime($trip['departure_date'] . ' ' . $trip['departure_time']);
$now = new DateTime();
$is_past_trip = $trip_datetime < $now;

// Calculate time remaining until departure
$time_diff = $now->diff($trip_datetime);
$days_remaining = $time_diff->days;
$hours_remaining = $time_diff->h;
$minutes_remaining = $time_diff->i;

if ($days_remaining > 0) {
    $time_remaining = $days_remaining . " day" . ($days_remaining > 1 ? "s" : "") . " left";
} elseif ($hours_remaining > 0) {
    $time_remaining = $hours_remaining . " hour" . ($hours_remaining > 1 ? "s" : "") . " left";
} else {
    $time_remaining = $minutes_remaining . " minute" . ($minutes_remaining > 1 ? "s" : "") . " left";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ride Details - <?php echo $trip['departure_city'] ?> to <?php echo $trip['destination_city'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <style>
        :root {
            --primary-color: #ffbf00;
            --primary-dark: #e6a700;
            --text-dark: #333;
            --text-light: #666;
            --bg-light: #f9f9f9;
            --transition: all 0.3s ease;
            --border-radius: 12px;
            --box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
        }
        
        .ride-view-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1.5rem;
            font-family: 'Inter', sans-serif;
        }
        
        .ride-view-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .back-button {
            position: absolute;
            left: 0;
            top: 0;
            background: var(--bg-light);
            color: var(--text-dark);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .back-button:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .ride-view-title {
            text-align: center;
            flex-grow: 1;
            padding-left: 40px;
        }
        
        .ride-view-title h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }
        
        .ride-view-title h2 {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text-light);
            margin: 0;
        }
        
        .ride-view-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }
        
        .ride-details-card, .driver-details-card, .booking-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }
        
        .ride-details-card:hover, .driver-details-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }
        
        .card-header {
            background: var(--primary-color);
            color: white;
            padding: 1.2rem 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            z-index: 1;
            position: relative;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            background: rgba(255,255,255,0.1);
            width: 100%;
            height: 200%;
            transform: rotate(30deg);
            z-index: 0;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .ride-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        .ride-route {
            display: flex;
            flex-direction: column;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .route-circle {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--primary-color);
            position: relative;
            z-index: 2;
        }
        
        .route-line {
            position: absolute;
            top: 14px;
            left: 7px;
            width: 2px;
            height: calc(100% - 28px);
            background: var(--primary-color);
            z-index: 1;
        }
        
        .route-point {
            display: flex;
            margin-bottom: 2rem;
        }
        
        .route-marker {
            margin-right: 1rem;
            position: relative;
        }
        
        .route-info h4 {
            margin: 0 0 0.2rem 0;
            font-size: 1rem;
            color: var(--text-dark);
        }
        
        .route-info p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .ride-meta {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .ride-meta i {
            color: var(--primary-color);
            font-size: 1.2rem;
            width: 30px;
            text-align: center;
        }
        
        .ride-meta-label {
            font-weight: 500;
            margin-right: 0.5rem;
            color: var(--text-light);
        }
        
        .ride-meta-value {
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .driver-profile {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .driver-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-right: 1rem;
        }
        
        .driver-info h4 {
            margin: 0 0 0.3rem 0;
            font-size: 1.2rem;
            color: var(--text-dark);
        }
        
        .driver-rating {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .star {
            color: var(--primary-color);
            margin-right: 2px;
        }
        
        .driver-contact {
            margin-bottom: 0.8rem;
        }
        
        .driver-contact a {
            display: flex;
            align-items: center;
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
            margin-bottom: 0.5rem;
        }
        
        .driver-contact a:hover {
            color: var(--primary-color);
        }
        
        .driver-contact i {
            width: 25px;
            color: var(--primary-color);
        }
        
        .booking-card {
            position: sticky;
            top: 20px;
        }
        
        .price-display {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: baseline;
        }
        
        .price-display small {
            font-size: 1rem;
            color: var(--text-light);
            margin-left: 0.5rem;
        }
        
        .seat-selector {
            margin-bottom: 1.5rem;
        }
        
        .seat-selector label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .seat-counter {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .seat-btn {
            background: var(--bg-light);
            border: none;
            color: var(--text-dark);
            font-size: 1.2rem;
            width: 40px;
            height: 40px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .seat-btn:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .seat-display {
            flex-grow: 1;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .book-button {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .book-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .book-button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .book-button i {
            margin-right: 0.5rem;
        }
        
        .time-remaining {
            text-align: center;
            margin: 1rem 0;
            padding: 0.8rem;
            border-radius: 8px;
            font-weight: 600;
            background: var(--bg-light);
            color: var(--text-dark);
        }
        
        .total-price {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        
        .total-price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .total-price-label {
            color: var(--text-light);
        }
        
        .total-price-value {
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .total-price-final {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            font-size: 1.1rem;
            font-weight: 700;
        }
        
        .total-calculation {
            margin: 1rem 0;
            padding: 0.8rem;
            background: #fff9e6;
            border-radius: 8px;
            text-align: center;
            border: 1px dashed var(--primary-color);
        }
        
        .calculation-formula {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .highlighted-price {
            color: var(--primary-color);
            font-size: 1.3rem;
        }
        
        .total-price-summary {
            background: #fff3d0;
            border: 2px solid var(--primary-color);
            border-radius: var(--border-radius);
            padding: 1.2rem;
            margin: 1.5rem 0;
            text-align: center;
            box-shadow: 0 4px 12px rgba(255, 191, 0, 0.15);
        }
        
        .summary-label {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 0.2rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .summary-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }
        
        .summary-calculation {
            font-size: 0.9rem;
            color: var(--text-light);
            background: rgba(255, 255, 255, 0.6);
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
        }
        
        /* Enhanced Total Price Box Styles */
        .total-calculation-box {
            background: var(--primary-color);
            color: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin: 1.5rem 0;
            text-align: center;
            box-shadow: 0 5px 20px rgba(255, 191, 0, 0.25);
            position: relative;
            overflow: hidden;
        }
        
        .total-calculation-box::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 60%);
            z-index: 0;
        }
        
        .total-price-header {
            font-size: 0.85rem;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        .total-price-amount {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .price-calculation {
            font-size: 1rem;
            background: rgba(0, 0, 0, 0.1);
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            position: relative;
            z-index: 1;
        }
        
        .already-booked {
            padding: 1rem;
            background: #e8f5e9;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .already-booked i {
            color: var(--success-color);
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        
        .already-booked p {
            margin: 0;
            font-weight: 500;
        }
        
        .past-trip-notice {
            padding: 1rem;
            background: #fff3e0;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .past-trip-notice i {
            color: var(--warning-color);
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        
        .past-trip-notice p {
            margin: 0;
            font-weight: 500;
        }
        
        /* Animations and effects */
        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulseCircle {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 191, 0, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(255, 191, 0, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 191, 0, 0);
            }
        }
        
        .route-circle {
            animation: pulseCircle 2s infinite;
        }
        
        .ride-details-card, .driver-details-card {
            animation: fadeSlideUp 0.6s ease-out;
        }
        
        .booking-card {
            animation: fadeSlideUp 0.8s ease-out;
        }
        
        /* Enhanced Responsive Design */
        @media (max-width: 992px) {
            .ride-view-grid {
                grid-template-columns: 1fr;
            }
            
            .booking-card {
                position: static;
                order: 1;
            }
            
            .ride-details-card {
                order: 2;
            }
            
            .driver-details-card {
                order: 3;
            }
            
            .summary-value {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 768px) {
            .ride-info-grid {
                grid-template-columns: 1fr;
            }
            
            .ride-view-title h1 {
                font-size: 1.5rem;
            }
            
            .ride-view-title h2 {
                font-size: 1rem;
            }
            
            .card-header h3 {
                font-size: 1.2rem;
            }
            
            .price-display {
                font-size: 1.8rem;
            }
            
            .summary-value {
                font-size: 1.6rem;
            }
            
            .calculation-formula {
                font-size: 1rem;
            }
            
            .ride-meta {
                margin-bottom: 0.8rem;
            }
            
            .driver-photo {
                width: 70px;
                height: 70px;
            }
        }
        
        @media (max-width: 576px) {
            .ride-view-container {
                margin: 1rem auto;
                padding: 0 1rem;
            }
            
            .back-button {
                width: 36px;
                height: 36px;
            }
            
            .driver-photo {
                width: 60px;
                height: 60px;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .ride-view-title h1 {
                font-size: 1.3rem;
                padding-left: 30px;
            }
            
            .ride-route {
                margin-bottom: 1.5rem;
            }
            
            .route-point {
                margin-bottom: 1.5rem;
            }
            
            .summary-value {
                font-size: 1.5rem;
            }
            
            .summary-calculation {
                font-size: 0.8rem;
            }
            
            .total-price-row, .total-price-final {
                font-size: 0.9rem;
            }
            
            .seat-btn {
                width: 36px;
                height: 36px;
            }
            
            .total-calculation {
                padding: 0.6rem;
            }
            
            .book-button {
                padding: 0.8rem;
            }
        }
        
        /* Mobile Touch Optimizations */
        @media (max-width: 480px) {
            .seat-btn {
                width: 44px;
                height: 44px;
                font-size: 1.5rem;
            }
            
            .book-button {
                padding: 1rem;
                font-size: 1.1rem;
            }
            
            .ride-view-title {
                padding-left: 25px;
            }
            
            .ride-meta i {
                width: 25px;
            }
            
            .ride-meta-label, .ride-meta-value {
                font-size: 0.9rem;
            }
            
            .card-header {
                padding: 1rem;
            }
            
            .summary-value {
                font-size: 1.3rem;
            }
        }
    </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
    <div class="ride-view-container" data-aos="fade-in">
        <div class="ride-view-header">
            <button class="back-button" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="ride-view-title">
                <h1><?php echo $trip['departure_city'] ?> to <?php echo $trip['destination_city'] ?></h1>
                <h2><?php echo $departure_date_str ?></h2>
            </div>
        </div>
        
        <div class="ride-view-grid">
            <div class="ride-details-card" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header">
                    <h3><i class="fas fa-route"></i> Ride Details</h3>
                </div>
                <div class="card-body">
                    <div class="ride-route">
                        <div class="route-point">
                            <div class="route-marker">
                                <div class="route-circle"></div>
                                <div class="route-line"></div>
                            </div>
                            <div class="route-info">
                                <h4><?php echo $trip['departure_city'] ?></h4>
                                <p><?php echo date('g:i A', strtotime($trip['departure_time'])) ?></p>
                            </div>
                        </div>
                        <div class="route-point">
                            <div class="route-marker">
                                <div class="route-circle"></div>
                            </div>
                            <div class="route-info">
                                <h4><?php echo $trip['destination_city'] ?></h4>
                                <p><?php echo date('g:i A', strtotime($trip['arrival_time'])) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ride-info-grid">
                        <div class="ride-meta" data-aos="fade-right" data-aos-delay="200">
                            <i class="fas fa-clock"></i>
                            <span class="ride-meta-label">Duration:</span>
                            <span class="ride-meta-value"><?php echo $duration_str ?></span>
                        </div>
                        <div class="ride-meta" data-aos="fade-right" data-aos-delay="250">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="ride-meta-label">Date:</span>
                            <span class="ride-meta-value"><?php echo date('d/m/Y', strtotime($trip['departure_date'])) ?></span>
                        </div>
                        <div class="ride-meta" data-aos="fade-right" data-aos-delay="300">
                            <i class="fas fa-user-friends"></i>
                            <span class="ride-meta-label">Available Seats:</span>
                            <span class="ride-meta-value"><?php echo $trip['available_seats'] ?></span>
                        </div>
                        <div class="ride-meta" data-aos="fade-right" data-aos-delay="350">
                            <i class="fas fa-users"></i>
                            <span class="ride-meta-label">Total Bookings:</span>
                            <span class="ride-meta-value"><?php echo $trip['total_bookings'] ?></span>
                        </div>
                        <div class="ride-meta" data-aos="fade-right" data-aos-delay="400">
                            <i class="fas fa-car"></i>
                            <span class="ride-meta-label">Vehicle:</span>
                            <span class="ride-meta-value"><?php echo $trip['vehicle_type'] ?? 'Standard' ?></span>
                        </div>
                        <div class="ride-meta" data-aos="fade-right" data-aos-delay="450">
                            <i class="fas fa-suitcase"></i>
                            <span class="ride-meta-label">Luggage:</span>
                            <span class="ride-meta-value">Medium allowed</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="booking-card" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header">
                    <h3><i class="fas fa-ticket-alt"></i> Booking</h3>
                </div>
                <div class="card-body">
                    <?php if ($is_past_trip): ?>
                        <div class="past-trip-notice">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>This trip has already departed</p>
                        </div>
                    <?php elseif ($user_booking): ?>
                        <div class="already-booked">
                            <i class="fas fa-check-circle"></i>
                            <p>You've already booked <?php echo $user_booking['seats_booked'] ?> seat(s) for this trip</p>
                        </div>
                    <?php else: ?>
                        <?php if (!$is_past_trip): ?>
                            <div class="time-remaining" data-aos="fade-in">
                                <i class="far fa-clock"></i> <?php echo $time_remaining ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="price-display" data-aos="fade-up">
                        ₹<?php echo $trip['price'] ?> <small>per seat</small>
                    </div>
                    
                    <?php if (!$is_past_trip && !$user_booking && $trip['available_seats'] > 0): ?>
                        <!-- Fixed Total Price Summary Box -->
                        <div class="total-price-summary" data-aos="fade-up" data-aos-delay="100">
                            <div class="summary-label">Total Amount to be Paid</div>
                            <div class="summary-value">₹<?php echo $trip['price'] ?></div>
                            <div class="summary-calculation">
                                <span>₹<?php echo $trip['price'] ?> × 1 seat</span>
                            </div>
                        </div>
                        
                        <form action="process_booking.php" method="POST" id="bookingForm">
                            <input type="hidden" name="trip_id" value="<?php echo $trip_id ?>">
                            
                            <div class="seat-selector" data-aos="fade-up" data-aos-delay="100">
                                <label for="seats">Number of Seats:</label>
                                <div class="seat-counter">
                                    <button type="button" class="seat-btn" id="decrementSeat">-</button>
                                    <div class="seat-display" id="seatCount">1</div>
                                    <input type="hidden" name="seats" id="seatInput" value="1">
                                    <button type="button" class="seat-btn" id="incrementSeat">+</button>
                                </div>
                            </div>
                            
                            <div class="total-price" data-aos="fade-up" data-aos-delay="150">
                                <div class="total-price-row">
                                    <div class="total-price-label">Price per seat</div>
                                    <div class="total-price-value">₹<?php echo $trip['price'] ?></div>
                                </div>
                                <div class="total-price-row">
                                    <div class="total-price-label">Number of seats</div>
                                    <div class="total-price-value" id="seatCountDisplay">1</div>
                                </div>
                                <div class="total-price-final">
                                    <div class="total-price-label">Total Amount</div>
                                    <div class="total-price-value" id="totalPrice">₹<?php echo $trip['price'] ?></div>
                                </div>
                            </div>
                            
                            <!-- Simplified, more visible total price display -->
                            <div class="total-calculation-box" data-aos="fade-up" data-aos-delay="175">
                                <div class="total-price-header">TOTAL AMOUNT</div>
                                <div class="total-price-amount">₹<span id="bigTotalPrice"><?php echo $trip['price'] ?></span></div>
                                <div class="price-calculation">
                                    <span id="pricePerSeatDisplay">₹<?php echo $trip['price'] ?></span> × 
                                    <span id="seatsFormula">1</span> seat
                                </div>
                            </div>
                            
                            <button type="submit" class="book-button" data-aos="fade-up" data-aos-delay="200">
                                <i class="fas fa-check-circle"></i> Book Now - Pay <span id="buttonTotalPrice">₹<?php echo $trip['price'] ?></span>
                            </button>
                        </form>
                    <?php elseif ($trip['available_seats'] <= 0 && !$user_booking && !$is_past_trip): ?>
                        <div class="past-trip-notice">
                            <i class="fas fa-ban"></i>
                            <p>No seats available for this trip</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="driver-details-card" data-aos="fade-up" data-aos-delay="300">
                <div class="card-header">
                    <h3><i class="fas fa-user-circle"></i> Driver Details</h3>
                </div>
                <div class="card-body">
                    <div class="driver-profile" data-aos="fade-right">
                        <img src="images/default.jpg" 
                             alt="Driver Photo" 
                             class="driver-photo">
                        <div class="driver-info">
                            <h4><?php echo $trip['driver_name'] ?></h4>
                            <div class="driver-rating">
                                <?php 
                                $rating = round($trip['driver_rating'] * 2) / 2;
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rating) {
                                        echo '<i class="fas fa-star star"></i>';
                                    } elseif ($i - 0.5 == $rating) {
                                        echo '<i class="fas fa-star-half-alt star"></i>';
                                    } else {
                                        echo '<i class="far fa-star star"></i>';
                                    }
                                }
                                ?>
                                <span style="margin-left: 5px;"><?php echo $rating ?>/5</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ride-meta" data-aos="fade-up" data-aos-delay="150">
                        <i class="fas fa-car"></i>
                        <span class="ride-meta-label">Vehicle Number:</span>
                        <span class="ride-meta-value"><?php echo $trip['vehicle_number'] ?></span>
                    </div>
                    
                    <div class="driver-contact" data-aos="fade-up" data-aos-delay="100">
                        <a href="tel:<?php echo $trip['driver_phone'] ?>">
                            <i class="fas fa-phone-alt"></i> <?php echo $trip['driver_phone'] ?>
                        </a>
                        <a href="mailto:<?php echo $trip['driver_email'] ?>">
                            <i class="fas fa-envelope"></i> <?php echo $trip['driver_email'] ?>
                        </a>
                    </div>
                    
                    <p data-aos="fade-up" data-aos-delay="150">
                        Experienced driver with a clean driving record. Priority is given to passenger comfort and safety.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AOS animation library
            AOS.init({
                duration: 800,
                easing: 'ease-out',
                once: true
            });
            
            // Seat counter functionality
            const decrementBtn = document.getElementById('decrementSeat');
            const incrementBtn = document.getElementById('incrementSeat');
            const seatCount = document.getElementById('seatCount');
            const seatInput = document.getElementById('seatInput');
            const seatCountDisplay = document.getElementById('seatCountDisplay');
            const totalPrice = document.getElementById('totalPrice');
            const buttonTotalPrice = document.getElementById('buttonTotalPrice');
            const seatsFormula = document.getElementById('seatsFormula');
            const bigTotalPrice = document.getElementById('bigTotalPrice');
            const pricePerSeatDisplay = document.getElementById('pricePerSeatDisplay');
            
            // New elements for the fixed total summary
            const summaryValue = document.querySelector('.summary-value');
            const summaryCalculation = document.querySelector('.summary-calculation');
            
            const maxSeats = <?php echo $trip['available_seats'] ?? 0 ?>;
            const pricePerSeat = <?php echo $trip['price'] ?>;
            
            // Initial update to ensure all price displays are consistent
            updateSeatCount(1);
            
            if (decrementBtn && incrementBtn) {
                decrementBtn.addEventListener('click', function() {
                    let currentCount = parseInt(seatCount.textContent);
                    if (currentCount > 1) {
                        currentCount--;
                        updateSeatCount(currentCount);
                    }
                });
                
                incrementBtn.addEventListener('click', function() {
                    let currentCount = parseInt(seatCount.textContent);
                    if (currentCount < maxSeats) {
                        currentCount++;
                        updateSeatCount(currentCount);
                    }
                });
                
                function updateSeatCount(count) {
                    // Update seat count display
                    seatCount.textContent = count;
                    seatInput.value = count;
                    
                    // Calculate the total price
                    const totalPriceValue = count * pricePerSeat;
                    
                    // Update all price displays
                    if (seatCountDisplay) seatCountDisplay.textContent = count;
                    if (totalPrice) totalPrice.textContent = '₹' + totalPriceValue;
                    if (buttonTotalPrice) buttonTotalPrice.textContent = '₹' + totalPriceValue;
                    if (seatsFormula) seatsFormula.textContent = count;
                    if (bigTotalPrice) bigTotalPrice.textContent = totalPriceValue;
                    if (pricePerSeatDisplay) pricePerSeatDisplay.textContent = '₹' + pricePerSeat;
                    
                    // Update the fixed total summary
                    if (summaryValue) summaryValue.textContent = '₹' + totalPriceValue;
                    if (summaryCalculation) summaryCalculation.innerHTML = '<span>₹' + pricePerSeat + ' × ' + count + ' seat' + (count > 1 ? 's' : '') + '</span>';
                    
                    console.log("Updated price: ₹" + totalPriceValue + " (" + count + " × " + pricePerSeat + ")");
                }
            }
            
            // Add parallax effect to card headers
            document.querySelectorAll('.card-header').forEach(header => {
                header.addEventListener('mousemove', function(e) {
                    const rect = header.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const xPercent = x / rect.width * 100;
                    const yPercent = y / rect.height * 100;
                    
                    header.querySelector('::before')?.style.setProperty('--x', `${xPercent}%`);
                    header.querySelector('::before')?.style.setProperty('--y', `${yPercent}%`);
                });
            });
        });
    </script>
</div></body>
</html> 