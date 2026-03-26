<?php
// Start session at the very beginning
session_start();

// Detailed logging for debugging session issues
error_log("Dashboard accessed. SESSION: " . print_r($_SESSION, true));

// Check login status first, before any output
if (!isset($_SESSION['user_id'])) {
    error_log("No user_id in session - redirecting to login");
    header("Location: login.php?error=not_logged_in");
    exit;
}

// Now include files that might produce output
include 'header.php';
include 'db.php';  // Assuming you have a database connection setup here

// Process user data
$user_id = $_SESSION['user_id'];
$query = "SELECT full_name, email, phone, profile_photo FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$full_name = $user['full_name'];
$user_email = $user['email'];
$phone = $user['phone'] ?? 'Not provided';
$profile_photo = $user['profile_photo'] ?? '';

$_SESSION['user_email'] = $user_email;
$_SESSION['profile_photo'] = $profile_photo;
$_SESSION['phone'] = $user['phone'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PoolPal Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FFB800;
            --primary-light: #FFD866;
            --primary-dark: #E6A800;
            --accent-color: #FF9500;
            --text-dark: #2A2A2A;
            --text-medium: #555555;
            --text-light: #777777;
            --bg-light: #F9F9FC;
            --bg-white: #FFFFFF;
            --shadow-sm: 0 4px 6px rgba(0,0,0,0.04);
            --shadow-md: 0 6px 15px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.12);
            --border-radius-sm: 8px;
            --border-radius-md: 12px;
            --border-radius-lg: 16px;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .sep {
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Dashboard Wrapper */
        .dashboard-wrapper {
            padding: 30px 0;
        }

        /* Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            position: relative;
        }

                .welcome-text {            position: relative;            margin-bottom: 20px;            padding-left: 15px;            border-left: 3px solid var(--primary-color);        }        .welcome-text h1 {            font-size: 28px;            font-weight: 600;            margin: 0;            color: var(--text-dark);            position: relative;            display: inline-block;        }        .welcome-text h1 .highlight-name {            font-weight: 700;            color: var(--primary-color);            position: relative;            display: inline-block;            padding-bottom: 2px;        }        .welcome-text h1 .highlight-name::after {            content: '';            position: absolute;            bottom: 0;            left: 0;            width: 100%;            height: 2px;            background: var(--primary-color);            transform: scaleX(0);            transform-origin: left;            animation: underlineAnimation 0.8s ease-out forwards;            animation-delay: 0.5s;        }                @keyframes underlineAnimation {            0% { transform: scaleX(0); }            100% { transform: scaleX(1); }        }

        /* Welcome Text Animation */
        @keyframes fadeInUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .dashboard-header {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .dashboard-subtitle {            font-size: 16px;            color: var(--text-medium);            margin: 10px 0;            font-weight: 400;            overflow: hidden;            border-right: 2px solid var(--primary-color);            white-space: nowrap;            width: 0;            animation:                 typing 2.5s steps(30, end) forwards,                blink-caret .75s step-end infinite;            animation-delay: 0.8s;        }                @keyframes typing {            from { width: 0 }            to { width: 100% }        }                @keyframes blink-caret {            from, to { border-color: transparent }            50% { border-color: var(--primary-color) }        }

        .dashboard-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            box-shadow: var(--shadow-sm);
        }

        .dashboard-badge i {
            margin-right: 6px;
        }

        /* Dashboard Sections */
        .dashboard-section {
            background-color: transparent;
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            position: relative;
        }

        .section-header h2 {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
            padding-bottom: 8px;
            position: relative;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .section-subheading {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 400;
            margin-left: 15px;
        }

        .section-action {
            font-size: 14px;
        }

                .section-action a {            color: var(--primary-color);            text-decoration: none;            display: flex;            align-items: center;            transition: all 0.3s ease;            font-weight: 500;        }        .section-action a:hover {            color: var(--accent-color);        }        .section-action a i {            font-size: 12px;            margin-left: 8px;            transition: all 0.3s ease;            position: relative;        }        .section-action a:hover i {            transform: translateX(5px);            color: var(--primary-dark);        }

        /* User Profile Card */
        .profile-card-container {
            margin-bottom: 30px;
        }

        .user-profile-card {
            display: flex;
            align-items: center;
            background: var(--bg-white);
            padding: 25px 30px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.03);
        }

        .user-profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            opacity: 1;
        }

        .user-profile-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .profile-image-container {
            position: relative;
            margin-right: 25px;
        }

        .profile-image {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(255, 184, 0, 0.25);
            transition: var(--transition);
        }

        .user-profile-card:hover .profile-image {
            transform: scale(1.05);
            border-color: var(--primary-color);
        }

        .profile-status {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 15px;
            height: 15px;
            background-color: #4CAF50;
            border-radius: 50%;
            border: 2px solid white;
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7);
            }
            
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(76, 175, 80, 0);
            }
            
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
            }
        }

        .profile-details {
            flex: 1;
        }

        .profile-name {
            margin: 0 0 10px 0;
            font-size: 22px;
            font-weight: 700;
            color: var(--text-dark);
            position: relative;
            display: inline-block;
        }

        .profile-info {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            color: var(--text-medium);
            font-size: 15px;
            transition: var(--transition);
        }

        .profile-info:last-child {
            margin-bottom: 0;
        }

        .profile-info i {
            color: var(--primary-color);
            margin-right: 10px;
            font-size: 16px;
            width: 18px;
            text-align: center;
        }

        .profile-info:hover {
            transform: translateX(5px);
            color: var(--text-dark);
        }

        .profile-button {
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 30px;
            font-size: 15px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            transition: var(--transition);
            border: none;
            margin-left: 30px;
            box-shadow: 0 5px 15px rgba(255, 184, 0, 0.25);
        }

        .profile-button i {
            margin-left: 8px;
            transition: transform 0.3s ease;
        }

        .profile-button:hover {
            background: linear-gradient(to right, var(--accent-color), var(--primary-color));
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 184, 0, 0.35);
        }

        .profile-button:hover i {
            transform: translateX(4px);
        }

        /* Search Card Styles */
        .search-card {
            background: var(--bg-white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            padding: 25px;
            margin-bottom: 30px;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.03);
            position: relative;
            overflow: hidden;
        }

        .search-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-3px);
        }

        .search-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
        }

        .search-card-header {
            margin-bottom: 20px;
        }

        .search-card-header h2 {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0 0 8px 0;
        }

        .search-card-header p {
            font-size: 14px;
            color: var(--text-light);
            margin: 0;
        }

        #rideSearchForm {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            width: 100%;
        }

        .search-form-group {
            position: relative;
        }

        .search-form-group label {
            display: block;
            font-size: 14px;
            color: var(--text-medium);
            margin-bottom: 8px;
            font-weight: 500;
        }

        .search-form-group label i {
            color: var(--primary-color);
            margin-right: 5px;
        }

        /* Responsive styles */
        @media (min-width: 992px) {
            #rideSearchForm {
                display: flex;
                align-items: center;
                gap: 15px;
                padding: 20px;
                background: var(--bg-white);
                border-radius: var(--border-radius-lg);
                box-shadow: var(--shadow-md);
            }

            .search-form-group {
                flex: 1;
                margin: 0;
            }

            .search-form-group:last-child {
                flex: 0 0 auto;
                width: 180px;
            }

            .swap-button-container {
                margin: 0;
                padding: 0 10px;
            }

            .search-btn {
                height: 48px;
                margin-left: 10px;
            }

            /* Make the date picker same width as location inputs */
            .date-input-wrapper {
                width: 100%;
            }

            .ride-date-picker {
                width: 100%;
            }
        }

        @media (max-width: 991px) {
            #rideSearchForm {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            #rideSearchForm {
                grid-template-columns: 1fr;
            }
        }

        .search-form-group input,
        .ride-date-picker {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: var(--border-radius-sm);
            font-size: 15px;
            color: var(--text-dark);
            background-color: #fff;
            transition: all 0.3s ease, transform 0.15s ease-out, opacity 0.15s ease-out;
            height: 48px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .search-form-group input:focus,
        .ride-date-picker:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 184, 0, 0.15);
        }

        .search-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            border-radius: var(--border-radius-sm);
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            height: 48px;
            box-shadow: 0 4px 10px rgba(255, 184, 0, 0.25);
            width: 100%;
        }

        .search-btn:hover {
            background: linear-gradient(45deg, var(--accent-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 184, 0, 0.3);
        }

        .search-btn i {
            font-size: 14px;
            transition: transform 0.3s ease;
        }

        .search-btn:hover i {
            transform: translateX(3px);
        }

        /* Add styles for the swap button container */
        .swap-button-container {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -10px 0;
            z-index: 2;
        }

        /* Style for the swap button */
        .swap-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(255, 184, 0, 0.25);
        }

        .swap-button:hover {
            transform: scale(1.1) rotate(180deg);
            background: linear-gradient(45deg, var(--accent-color), var(--primary-color));
            box-shadow: 0 6px 15px rgba(255, 184, 0, 0.35);
        }

        .swap-button i {
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        /* Responsive styles for swap button */
        @media (max-width: 768px) {
            .swap-button {
                width: 35px;
                height: 35px;
            }
            
            .swap-button i {
                font-size: 14px;
            }
        }

        .date-input-wrapper {
            position: relative;
            width: 100%;
        }

        .ride-date-picker {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%23888" stroke-width="1.5"><path d="M8 2v3M16 2v3M3 8h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
            padding-right: 40px;
        }

        .ride-date-picker::-webkit-calendar-picker-indicator {
            opacity: 0;
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            cursor: pointer;
        }
        
        /* Booking Card Styles */
        .bookings-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .booking-card {
            background: var(--bg-white);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            padding: 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.03);
            position: relative;
            overflow: hidden;
        }

        .booking-card::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 100%;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }

        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .booking-card:hover::after {
            transform: scaleX(1);
        }

        .booking-icon {
            background: rgba(255, 184, 0, 0.1);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            transition: all 0.3s ease;
        }

        .booking-icon i {
            color: var(--primary-color);
            font-size: 20px;
        }

        .booking-card:hover .booking-icon {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
        }

        .booking-card:hover .booking-icon i {
            color: white;
        }

        .booking-info {
            flex: 1;
        }

        .booking-info h3 {
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .booking-info p {
            margin: 0;
            font-size: 14px;
            color: var(--text-medium);
        }

        .booking-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: var(--border-radius-sm);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-view {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-cancel {
            background-color: #f0f0f0;
            color: var(--text-medium);
        }

        .btn-view:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-cancel:hover {
            background-color: #e0e0e0;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(255, 184, 0, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(255, 184, 0, 0.3);
        }

        /* Empty State */
        .empty-state {
            padding: 40px 20px;
                text-align: center;
            background: var(--bg-white);
            border-radius: var(--border-radius-md);
            border: 1px dashed rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 40px;
            color: #ddd;
            margin-bottom: 15px;
        }

        .empty-state p {
            font-size: 16px;
            color: var(--text-medium);
                margin-bottom: 20px;
            }

        /* Ride Cards */
        .ride-card {
            background: var(--bg-white);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            padding: 20px;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.03);
            display: flex;
                flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .ride-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-md);
        }

        .ride-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
                width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            transform: scaleX(0);
            transition: transform 0.4s ease;
            transform-origin: left;
        }

        .ride-card:hover::after {
            transform: scaleX(1);
        }

        .ride-card-header {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
        }

        .time-remaining {
            display: inline-block;
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        .time-urgent {
            background-color: #ffebee;
            color: #e53935;
        }

        .time-warning {
            background-color: #fff8e1;
            color: #ff9800;
        }

        .time-normal {
            background-color: #e8f5e9;
            color: #43a047;
        }

        .ride-route {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .journey-cities {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
        
        .from-city, .to-city {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }
        
        .from-city i {
            color: var(--primary-color);
        }
        
        .to-city i {
            color: var(--accent-color);
        }
        
        .journey-arrow {
            color: var(--text-light);
            padding: 5px 0;
        }
        
        .journey-arrow i {
            font-size: 16px;
            color: var(--primary-color);
            opacity: 0.7;
        }

        .ride-route h3 {
            margin: 0;
            font-size: 17px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .ride-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .ride-detail {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: var(--text-medium);
        }

        .ride-detail i {
            color: var(--primary-color);
            width: 20px;
            margin-right: 8px;
            text-align: center;
        }

        .btn-book {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: var(--border-radius-sm);
            text-align: center;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: auto;
            box-shadow: 0 4px 10px rgba(255, 184, 0, 0.15);
            display: flex;
            justify-content: center;
            align-items: center;
                gap: 8px;
            }
            
        .btn-book i {
            font-size: 12px;
            transition: transform 0.3s ease;
        }

        .btn-book:hover {
            background: linear-gradient(45deg, var(--accent-color), var(--primary-color));
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(255, 184, 0, 0.25);
        }
        
        .btn-book:hover i {
            transform: translateX(3px);
        }

        /* Slider Styles */
        .ride-slider-container {
            position: relative;
            width: 100%;
            margin: 0 auto;
            padding: 10px 0;
        }

        .ride-slider {
            width: 100%;
            overflow: hidden;
            position: relative;
        }

        .ride-slider-track {
            display: flex;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            gap: 24px;
            width: 100%;
        }

        @media (min-width: 992px) {
            .ride-card {
                flex: 0 0 calc(33.333% - 16px);
                min-width: calc(33.333% - 16px);
                max-width: calc(33.333% - 16px);
                margin: 0;
            }
        }

        .ride-card {
            background: var(--bg-white);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .ride-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            border-color: rgba(255, 184, 0, 0.1);
        }

        .slider-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(255, 184, 0, 0.2);
        }

        .slider-nav:hover {
            background: linear-gradient(45deg, var(--accent-color), var(--primary-color));
            transform: translateY(-50%) scale(1.1);
        }

        .slider-nav.prev-btn {
            left: -20px;
        }

        .slider-nav.next-btn {
            right: -20px;
        }

        .slider-nav:disabled {
            background: #e0e0e0;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .ride-card-header {
            margin-bottom: 15px;
        }

        .time-remaining {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .journey-cities {
            margin: 15px 0;
            padding: 10px 0;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .from-city, .to-city {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 0;
        }

        .journey-arrow {
            margin: 5px 0;
            color: var(--primary-color);
            opacity: 0.7;
        }

        .ride-details {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin: 15px 0;
        }

        .ride-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: var(--text-medium);
        }

        .ride-detail i {
            color: var(--primary-color);
            width: 16px;
        }

        .btn-book {
            margin-top: auto;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-book:hover {
            background: linear-gradient(45deg, var(--accent-color), var(--primary-color));
            transform: translateY(-2px);
        }

        .btn-book i {
            font-size: 14px;
            transition: transform 0.3s ease;
        }

        .btn-book:hover i {
            transform: translateX(4px);
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .ride-card {
                flex: 0 0 calc(50% - 12px);
                min-width: calc(50% - 12px);
            }
        }

        @media (max-width: 576px) {
            .ride-card {
                flex: 0 0 100%;
                min-width: 100%;
            }
            
            .slider-nav {
                display: none;
            }
            
            .ride-slider {
                overflow: visible;
            }
            
            .ride-slider-track {
                gap: 15px;
                flex-direction: column;
                transform: none !important;
            }
        }

        /* Activity Table */
        .activity-table-container {
            background: var(--bg-white);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.03);
            transition: var(--transition);
        }
        
        .activity-table-container:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-3px);
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
        }

        .activity-table th {
            background-color: rgba(255, 184, 0, 0.03);
            color: var(--text-medium);
            font-weight: 600;
            text-align: left;
            padding: 16px 20px;
            font-size: 14px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            position: relative;
        }
        
        .activity-table th:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, var(--primary-color), transparent);
            opacity: 0.3;
        }

        .activity-table th i {
            color: var(--primary-color);
            margin-right: 6px;
            font-size: 13px;
        }

        .activity-table td {
            padding: 16px 20px;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            color: var(--text-dark);
            font-size: 14px;
            transition: var(--transition);
        }
        
        .text-center {
            text-align: center;
        }

        .ride-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .ride-route {
            display: flex;
            align-items: center;
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .ride-route i {
            color: var(--primary-color);
            margin-right: 8px;
            font-size: 14px;
        }
        
        .ride-passengers {
            font-size: 13px;
            color: var(--text-medium);
            display: flex;
            align-items: center;
        }
        
        .ride-passengers i {
            color: var(--text-light);
            margin-right: 6px;
            font-size: 12px;
        }

        .activity-table tr:last-child td {
            border-bottom: none;
        }
        
        .activity-table tr {
            transition: all 0.3s ease;
            position: relative;
        }

        .activity-table tr:hover {
            background-color: rgba(255, 184, 0, 0.03);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            line-height: 1;
        }
        
        .status-badge:before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .status-completed {
            background-color: #e8f5e9;
            color: #43a047;
        }
        
        .status-completed:before {
            background-color: #43a047;
        }

        .status-upcoming {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .status-upcoming:before {
            background-color: #1976d2;
        }

        .status-in-progress {
            background-color: #fff8e1;
            color: #ff9800;
        }
        
        .status-in-progress:before {
            background-color: #ff9800;
        }

        .view-details {
            color: var(--primary-color);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: rgba(255, 184, 0, 0.1);
        }

        .view-details:hover {
            color: var(--accent-color);
            background-color: rgba(255, 184, 0, 0.2);
            transform: scale(1.1);
        }

        .no-data {
            text-align: center;
            color: var(--text-light);
            padding: 40px !important;
            background-color: rgba(0,0,0,0.01);
        }
        
        .no-data i {
            font-size: 24px;
            color: #ddd;
            margin-bottom: 10px;
            display: block;
        }

                /* Animation */        @keyframes fadeIn {            from {                 opacity: 0;                 transform: translateY(10px);             }            to {                 opacity: 1;                 transform: translateY(0);             }        }        @keyframes shimmer {            0% {                background-position: -200% 0;            }            100% {                background-position: 200% 0;            }        }        .dashboard-section {            animation: fadeIn 0.5s ease-out forwards;        }                .section-action a {            position: relative;            padding: 8px 15px;            border-radius: 30px;            background-color: rgba(255, 184, 0, 0.1);            box-shadow: 0 3px 10px rgba(255, 184, 0, 0.1);            transition: all 0.3s ease;        }                .section-action a:hover {            background-color: rgba(255, 184, 0, 0.2);            box-shadow: 0 5px 15px rgba(255, 184, 0, 0.15);            transform: translateY(-2px);        }                .section-action a::before {            content: '';            position: absolute;            top: -2px;            left: -2px;            right: -2px;            bottom: -2px;            background: linear-gradient(90deg,                 rgba(255, 184, 0, 0),                 rgba(255, 184, 0, 0.3),                 rgba(255, 184, 0, 0));            background-size: 200% 100%;            border-radius: 32px;            z-index: -1;            opacity: 0;            transition: opacity 0.3s ease;        }                .section-action a:hover::before {            opacity: 1;            animation: shimmer 2s infinite;        }

        /* Responsive styles */
        @media (max-width: 992px) {
            #rideSearchForm {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .ride-card {
                min-width: calc(50% - 15px);
                flex: 0 0 calc(50% - 15px);
            }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .user-profile-card {
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 20px;
            }
            
            .profile-image-container {
                margin-right: 0;
                margin-bottom: 20px;
            }
            
            .profile-details {
                margin-bottom: 20px;
                align-items: center;
            }
            
            .profile-info {
                justify-content: center;
            }
            
            .profile-button {
                margin-left: 0;
                width: 100%;
                justify-content: center;
            }
            
            .booking-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .booking-icon {
                margin-right: 0;
            }
            
            .booking-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .btn {
                flex: 1;
                text-align: center;
            }
            
            .activity-table th:nth-child(4),
            .activity-table td:nth-child(4) {
                display: none;
            }
        }

        @media (max-width: 576px) {
            #rideSearchForm {
                grid-template-columns: 1fr;
            }
            
            .ride-card {
                min-width: 100%;
                flex: 0 0 100%;
            }
            
            .slider-nav {
                display: none;
            }
            
            .ride-slider {
                overflow: visible;
            }
            
            .ride-slider-track {
                display: flex;
                flex-direction: column;
                gap: 15px;
                transform: none !important;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .section-subheading {
                margin-left: 0;
            }
            
            .activity-table th:nth-child(3),
            .activity-table td:nth-child(3) {
                display: none;
            }
        }

        /* Activity Table Responsive Design */
        @media (max-width: 992px) {
            .activity-table th:nth-child(3),
            .activity-table td:nth-child(3) {
                white-space: nowrap;
            }
        }
        
        @media (max-width: 768px) {
            .activity-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
                scrollbar-color: var(--primary-color) #f1f1f1;
            }
            
            .activity-table::-webkit-scrollbar {
                height: 6px;
            }
            
            .activity-table::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }
            
            .activity-table::-webkit-scrollbar-thumb {
                background: var(--primary-color);
                border-radius: 10px;
            }
            
            .activity-table th,
            .activity-table td {
                padding: 15px 15px;
            }
            
            .activity-table th:nth-child(4),
            .activity-table td:nth-child(4) {
                display: table-cell;
            }
            
            .ride-info {
                max-width: 200px;
            }
            
            /* Add a scroll indicator */
            .activity-table-container {
            position: relative;
        }
        
            .activity-table-container:after {
                content: '→ Scroll →';
            position: absolute;
            bottom: 0;
            left: 0;
                width: 100%;
                padding: 5px 10px;
                background: rgba(255, 184, 0, 0.05);
                color: var(--text-light);
                font-size: 11px;
                text-align: center;
                opacity: 0.7;
                transform: translateY(100%);
                animation: fadeInUp 0.5s forwards 2s, fadeOut 0.5s forwards 5s;
            }
            
            @keyframes fadeOut {
                from { opacity: 0.7; transform: translateY(0); }
                to { opacity: 0; transform: translateY(100%); }
            }
        }
        
        /* Enhanced mobile experience for activity table */
        @media (max-width: 576px) {
            .activity-table-container {
                margin: 0 -15px;
                width: calc(100% + 30px);
                border-radius: 0;
                box-shadow: none;
                border-left: none;
                border-right: none;
            }
            
            .activity-table th,
            .activity-table td {
                padding: 12px 10px;
                font-size: 13px;
            }
            
            /* Stack the ride info on mobile */
            .activity-table .ride-info {
                display: flex;
                flex-direction: column;
                gap: 4px;
                max-width: 140px;
            }
            
            .activity-table .ride-route {
                font-size: 13px;
                white-space: normal;
                line-height: 1.3;
            }
            
            .activity-table .ride-passengers {
                font-size: 11px;
            }
            
            /* Make status badge more compact */
            .status-badge {
                padding: 4px 8px;
                font-size: 11px;
            }
            
            .activity-table th:nth-child(3), 
            .activity-table td:nth-child(3),
            .activity-table th:nth-child(4), 
            .activity-table td:nth-child(4) {
                display: table-cell;
                white-space: nowrap;
            }
            
            /* Create horizontal scroll indicator */
            .activity-table-container:after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 3px;
                background: linear-gradient(90deg, var(--primary-color), transparent);
                opacity: 0.2;
            }
        }

        /* Enhanced Animation Effects */
        .user-profile-card {
            animation: fadeInUp 0.8s ease-out forwards;
            animation-delay: 0.2s;
            opacity: 0;
        }
        
        .search-card {
            animation: fadeInUp 0.8s ease-out forwards;
            animation-delay: 0.4s;
            opacity: 0;
        }
        
        .dashboard-section {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
        }
        
        .dashboard-section:nth-child(3) {
            animation-delay: 0.6s;
        }
        
        .dashboard-section:nth-child(4) {
            animation-delay: 0.8s;
        }
        
        .dashboard-section:nth-child(5) {
            animation-delay: 1s;
        }
        
        /* Enhanced hover effects */
        .booking-card, .ride-card, .activity-table-container {
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), 
                        box-shadow 0.4s ease, 
                        border-color 0.3s ease;
        }
        
        .booking-card:hover, .activity-table-container:hover {
            border-color: rgba(255, 184, 0, 0.1);
        }
        
        /* Enhanced focus states */
        .search-form-group select:focus,
        .ride-date-picker:focus,
        .search-btn:focus {
            transform: translateY(-2px) scale(1.02);
        }
    </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
    <!-- Add this right before </head> -->
    <link rel="stylesheet" href="css/places-autocomplete.css">
    <!-- Load Google Maps API first -->
    <!-- <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places" async></script> -->
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize ModernPlacesAutocomplete for full address
    const placesUtil = new ModernPlacesAutocomplete();
    placesUtil.init().then(() => {
        placesUtil.createAutocomplete({
            fromInputId: 'from_city',
            toInputId: 'to_city',
            fromLatId: 'from_lat',
            fromLngId: 'from_lng',
            toLatId: 'to_lat',
            toLngId: 'to_lng'
        });
    }).catch(error => {
        console.error('Failed to initialize places autocomplete:', error);
    });
});
    </script>
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
<div class="sep">
    <div class="container">
        <div class="dashboard-wrapper">

        
        <div class="profile-card-container">
            <div class="user-profile-card">
                <div class="profile-image-container">
                    <img class="profile-image" 
                         src="<?php echo !empty($profile_photo) ? htmlspecialchars($profile_photo) : 'images/default.jpg'; ?>" 
                         alt="Profile Picture" 
                         onerror="this.src='images/default.jpg'">
                    <div class="profile-status"></div>
                </div>
                
                <div class="profile-details">
                    <h3 class="profile-name"><?php echo htmlspecialchars($full_name); ?></h3>
                    <div class="profile-info">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($user_email); ?></span>
                    </div>
                    <div class="profile-info">
                        <i class="fas fa-phone"></i>
                        <span><?php echo htmlspecialchars($phone); ?></span>
                    </div>
                </div>
                
                <a href="profile.php" class="profile-button">
                    View Profile <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
            
            <div class="search-card" id="find-ride">
                <div class="search-card-header">
                    <h2>Find a Ride</h2>
                    <p>Enter your journey details to find available rides</p>
                </div>
            <form id="rideSearchForm" method="POST" action="result.php">
                    <div class="search-form-group" style="position: relative;">
                        <label for="from_city"><i class="fas fa-map-marker-alt"></i> From</label>
                        <input type="text" name="from_city" id="from_city" placeholder="Select departure city" required>
                        <input type="hidden" name="from_lat" id="from_lat">
                        <input type="hidden" name="from_lng" id="from_lng">
                    </div>

                    <div class="swap-button-container">
                        <button type="button" class="swap-button" id="swapLocations" title="Swap locations">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                    </div>

                    <div class="search-form-group" style="position: relative;">
                        <label for="to_city"><i class="fas fa-map-marker"></i> To</label>
                        <input type="text" name="to_city" id="to_city" placeholder="Select destination city" required>
                        <input type="hidden" name="to_lat" id="to_lat">
                        <input type="hidden" name="to_lng" id="to_lng">
                    </div>

                    <div class="search-form-group">
                        <label for="travel_date"><i class="fas fa-calendar-alt"></i> When</label>
                <div class="date-input-wrapper">
                            <input type="date" name="travel_date" id="travel_date" class="ride-date-picker" required>
                </div>
                    </div>
                    
                    <div class="search-form-group">
                <button type="button" class="search-btn" onclick="submitSearchForm(event)">
                    <span>Search Rides</span>
                            <i class="fas fa-search"></i>
                </button>
                    </div>
            </form>
        </div>
            
        <!-- Upcoming Trips -->
        <?php
$currentDateTime = date('Y-m-d H:i:s');  // Current timestamp in MySQL format

$sql = "
    SELECT b.*, t.departure_city, t.destination_city, t.departure_date, t.departure_time
    FROM bookings b
    JOIN trips t ON b.trip_id = t.id
    WHERE CONCAT(t.departure_date, ' ', t.departure_time) >= '$currentDateTime'
      AND b.user_email = '$user_email'
    ORDER BY CONCAT(t.departure_date, ' ', t.departure_time) ASC
    LIMIT 4
";
$result = mysqli_query($conn, $sql);
?>

            <div class="dashboard-section">
                <div class="section-header">
    <h2>Upcoming Bookings</h2>
                    <span class="section-action">
                        <a href="mytripsu.php">View All <i class="fas fa-chevron-right"></i></a>
                    </span>
                </div>

                <div class="bookings-container">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): 
            $datetime = date("l, g:i A", strtotime($row['departure_date'] . ' ' . $row['departure_time']));
        ?>
                        <div class="booking-card">
                            <div class="booking-icon">
            <i class="fas fa-car-side"></i>
                            </div>
                            <div class="booking-info">
                <h3><?= htmlspecialchars($row['departure_city']) ?> → <?= htmlspecialchars($row['destination_city']) ?></h3>
                <p><?= $datetime ?> • <?= $row['seats_booked'] ?> passenger<?= $row['seats_booked'] > 1 ? 's' : '' ?></p>
            </div>
                            <div class="booking-actions">
                                <a href="ride_view.php?id=<?= $row['trip_id'] ?>" class="btn btn-view">View Details</a>
                                <a href="#" class="btn btn-cancel">Cancel</a>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-alt"></i>
                            <p>No upcoming bookings found</p>
                            <a href="#find-ride" class="btn-primary">Find a Ride</a>
                        </div>
    <?php endif; ?>
                </div>
</div>

        <!-- Recommended Rides -->
        <?php
            // Get current date and time in MySQL format
            $currentDateTime = date('Y-m-d H:i:s');

            // Fetch upcoming trips only, considering available seats
            $sql = "
                SELECT t.*, 
                       (t.seats - COALESCE(b.total_booked, 0)) AS available_seats
                FROM trips t
                LEFT JOIN (
                    SELECT trip_id, SUM(seats_booked) AS total_booked
                    FROM bookings
                    WHERE payment_status = 'completed'
                    GROUP BY trip_id
                ) b ON t.id = b.trip_id
                WHERE CONCAT(t.departure_date, ' ', t.departure_time) >= '$currentDateTime'
                HAVING available_seats > 0
                ORDER BY t.departure_date ASC, t.departure_time ASC 
                LIMIT 10";
            $result = mysqli_query($conn, $sql);
            ?>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Recommended Rides</h2>
                    <span class="section-subheading">Sorted by earliest departure</span>
                </div>
                
                <div class="ride-slider-container">
                    <button class="slider-nav prev-btn" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
                    <div class="ride-slider">
                        <div class="ride-slider-track">
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): 
                                    $dateTime = date("F j, g:i A", strtotime($row['departure_date'] . ' ' . $row['departure_time']));
                                    $now = new DateTime();
                                    $departureDateTime = new DateTime($row['departure_date'] . ' ' . $row['departure_time']);
                                    $interval = $now->diff($departureDateTime);
                                    
                                    // Calculate time remaining
                                    $timeRemaining = '';
                                    $timeClass = 'time-normal';
                                    
                                    if($interval->days > 0) {
                                        $timeRemaining = $interval->days . ' day' . ($interval->days > 1 ? 's' : '') . ' left';
                                        $timeClass = 'time-normal';
                                    } else if($interval->h > 0) {
                                        $timeRemaining = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' left';
                                        $timeClass = $interval->h < 12 ? 'time-warning' : 'time-normal';
                                    } else {
                                        $timeRemaining = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' left';
                                        $timeClass = 'time-urgent';
                                    }
                                    
                                    // Format price with commas
                                    $formattedPrice = number_format($row['price'], 0);
                                ?>
                                <div class="ride-card">
                                    <div class="ride-card-header">
                                    <span class="time-remaining <?= $timeClass ?>"><?= $timeRemaining ?></span>
                                    </div>
                                    <div class="ride-route">
                                        <div class="journey-cities">
                                            <div class="from-city">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span><?= htmlspecialchars($row['departure_city']) ?></span>
                                            </div>
                                            <div class="journey-arrow">
                                                <i class="fas fa-long-arrow-alt-right"></i>
                                            </div>
                                            <div class="to-city">
                                                <i class="fas fa-map-marker"></i>
                                                <span><?= htmlspecialchars($row['destination_city']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ride-details">
                                        <div class="ride-detail">
                                            <i class="fas fa-clock"></i> 
                                            <span><?= $dateTime ?></span>
                                        </div>
                                        <div class="ride-detail">
                                            <i class="fas fa-rupee-sign"></i> 
                                            <span><?= $formattedPrice ?></span>
                                        </div>
                                        <div class="ride-detail">
                                            <i class="fas fa-user-friends"></i> 
                                            <span><?= $row['available_seats'] ?> seat<?= $row['available_seats'] > 0 ? 's' : '' ?> available</span>
                                        </div>
                                    </div>
                                    <a href="Ridedetails.php?id=<?= $row['id'] ?>" class="btn-book">Book Now <i class="fas fa-arrow-right"></i></a>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-car"></i>
                                    <p>No recommended rides available at the moment.</p>
                                    <a href="#find-ride" class="btn-primary">Search Rides</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button class="slider-nav next-btn" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>

        <!-- Recent Activity -->
            <div class="dashboard-section">
                <div class="section-header">
    <h2>Recent Activity</h2>
                                        <span class="section-action">                        <a href="mytripsu.php">View All <i class="fas fa-chevron-right"></i></a>                    </span>
                </div>
                
                <div class="activity-table-container">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-route"></i> Ride Details</th>
                                <th><i class="fas fa-clock"></i> Status</th>
                                <th><i class="fas fa-calendar"></i> Date & Time</th>
                                <th><i class="fas fa-rupee-sign"></i> Fare</th>
                                <th class="text-center">Actions</th>
        </tr>
                        </thead>
                        <tbody>
        <?php
        date_default_timezone_set('Asia/Kolkata');

        $user_email = $_SESSION['user_email']; // Make sure this is set earlier as we discussed

        $sql = "
            SELECT b.*, t.departure_city, t.destination_city, t.departure_date, t.departure_time, t.arrival_time, t.price
            FROM bookings b
            JOIN trips t ON b.trip_id = t.id
            WHERE b.user_email = '$user_email'
            ORDER BY t.departure_date DESC, t.departure_time DESC
            LIMIT 5
        ";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Combine departure and arrival timestamps
                $departure_datetime_str = $row['departure_date'] . ' ' . $row['departure_time'];
                $arrival_datetime_str = $row['departure_date'] . ' ' . $row['arrival_time'];

                $departure_ts = strtotime($departure_datetime_str);
                $arrival_ts = strtotime($arrival_datetime_str);
                $now = time();

                // Determine status
                if ($arrival_ts < $now) {
                    $status = "Completed";
                                        $status_class = "status-completed";
                                        $status_icon = "check-circle";
                } elseif ($departure_ts > $now) {
                    $status = "Upcoming";
                                        $status_class = "status-upcoming";
                                        $status_icon = "clock";
                } else {
                    $status = "In Progress";
                                        $status_class = "status-in-progress";
                                        $status_icon = "car";
                }

                $display_datetime = date("F j, Y • g:i A", $departure_ts); // e.g., April 28, 2025 • 5:30 PM
                                    $price_total = "₹" . number_format($row['price'] * $row['seats_booked'], 0);

                echo "<tr>
                                            <td>
                                                <div class='ride-info'>
                                                    <div class='ride-route'>
                                                        <i class='fas fa-car-side'></i>
                                                        <span>{$row['departure_city']} → {$row['destination_city']}</span>
                                                    </div>
                                                    <div class='ride-passengers'>
                                                        <i class='fas fa-users'></i> {$row['seats_booked']} passenger" . ($row['seats_booked'] > 1 ? 's' : '') . "
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class='status-badge $status_class'>{$status}</span></td>
                        <td>{$display_datetime}</td>
                                            <td>{$price_total}</td>
                                            <td class='text-center'>
                                                <a href='ride_view.php?id={$row['trip_id']}' class='view-details' title='View Details'>
                                                    <i class='fas fa-info-circle'></i>
                                                </a>
                                            </td>
                    </tr>";
            }
        } else {
                                echo "<tr>
                                        <td colspan='5' class='no-data'>
                                            <i class='fas fa-route'></i>
                                            No recent activities found
                                        </td>
                                    </tr>";
                            }
                            ?>
                        </tbody>
    </table>
                </div>
            </div>
        </div>
</div>
</div>

<script>
    // Set today's date as default and handle date input interactions
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        document.getElementById('travel_date').value = yyyy + '-' + mm + '-' + dd;
        
        // Try to initialize Google Places if already loaded
        if (window.google && window.google.maps && window.google.maps.places) {
            initDashboardAutocomplete();
        }
        
        // Improve date input handling to ensure calendar only shows when clicking the input or icon
        const dateInput = document.getElementById('travel_date');
        const dateWrapper = document.querySelector('.date-input-wrapper');
        
        // Create a custom click handler that only activates the calendar when clicking the input or its icon
        dateWrapper.addEventListener('click', function(e) {
            // If clicked on the wrapper or the input itself, show the calendar
            if (e.target === dateInput || e.target === dateWrapper) {
                // Focus the input which will show the calendar
                dateInput.focus();
                // Create a click event on the calendar icon
                setTimeout(() => {
                    const calendarIcon = dateInput.querySelector('::-webkit-calendar-picker-indicator');
                    if (calendarIcon) {
                        calendarIcon.click();
                    }
                }, 100);
            }
        });
        
        // Block propagation from date input to prevent affecting the search button
        dateInput.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Add animation to sections
        const sections = document.querySelectorAll('.section');
        sections.forEach((section, index) => {
            section.style.opacity = "0";
            section.style.transform = "translateY(20px)";
            section.style.transition = "opacity 0.5s ease, transform 0.5s ease";
            section.style.transitionDelay = (0.2 * index) + "s";
            
            setTimeout(() => {
                section.style.opacity = "1";
                section.style.transform = "translateY(0)";
            }, 100);
        });
    });
    
    // Direct form submission with validation
    function submitSearchForm(event) {
        // Prevent any default behavior or propagation
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        const form = document.getElementById('rideSearchForm');
        const button = document.querySelector('.search-btn');
        
        const fromCity = document.querySelector('input[name="from_city"]').value;
        const toCity = document.querySelector('input[name="to_city"]').value;
        
        if (!fromCity) {
            showNotification("Please enter departure city", "error");
            resetButton(button);
            return;
        }
        
        if (!toCity) {
            showNotification("Please enter destination city", "error");
            resetButton(button);
            return;
        }
        
        if (fromCity === toCity) {
            showNotification("Departure and destination cities cannot be the same", "error");
            resetButton(button);
            return;
        }
        
        // Show loading animation
        button.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Searching...';
        button.disabled = true;
        button.style.opacity = "0.8";
        
        // Submit after a short delay to show loading animation
        setTimeout(() => {
            form.submit();
        }, 800);
    }
    
    // Helper function to reset button state
    function resetButton(button) {
        // Reset button to original state
        button.innerHTML = '<span>Search Rides</span><i class="fas fa-search"></i>';
        button.disabled = false;
    }
    
    // Modern alert function
    function showAlert(message) {
        const alertElement = document.createElement('div');
        alertElement.style.position = 'fixed';
        alertElement.style.top = '20px';
        alertElement.style.left = '50%';
        alertElement.style.transform = 'translateX(-50%)';
        alertElement.style.padding = '15px 25px';
        alertElement.style.backgroundColor = '#fff';
        alertElement.style.color = '#333';
        alertElement.style.borderRadius = '8px';
        alertElement.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
        alertElement.style.zIndex = '1000';
        alertElement.style.opacity = '0';
        alertElement.style.transition = 'all 0.3s ease';
        alertElement.style.borderLeft = '4px solid #ffbf00';
        alertElement.style.fontSize = '14px';
        alertElement.style.fontWeight = '500';
        alertElement.innerHTML = `<i class="fas fa-exclamation-circle" style="color:#ffbf00;margin-right:8px;"></i>${message}`;
        
        document.body.appendChild(alertElement);
        
        // Animate in
        setTimeout(() => {
            alertElement.style.opacity = '1';
            alertElement.style.transform = 'translateX(-50%) translateY(10px)';
        }, 10);
        
        // Close on click
        const closeBtn = alertElement.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            closeNotification(alertElement);
        });
        
        // Auto close after 4 seconds
        setTimeout(() => {
            closeNotification(alertElement);
        }, 4000);
        
        function closeNotification(alertElement) {
            alertElement.style.transform = 'translateX(100%)';
            alertElement.style.opacity = '0';
            
            setTimeout(() => {
                document.body.removeChild(alertElement);
            }, 300);
        }
    }
</script>

<script>
    // Slider functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sliderTrack = document.querySelector('.ride-slider-track');
        const cards = sliderTrack.querySelectorAll('.ride-card');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        
        // Don't initialize slider on mobile
        if (window.innerWidth <= 576) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            return;
        }
        
        if (cards.length <= 3) {
            // Hide navigation buttons if there are 3 or fewer cards
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        }
        
        let currentIndex = 0;
        let cardWidth = 0;
        let maxIndex = Math.max(0, cards.length - 3);
        
        // Calculate card width
        function calculateCardWidth() {
            if (cards.length > 0) {
                const computedStyle = window.getComputedStyle(cards[0]);
                const width = cards[0].offsetWidth;
                const marginRight = parseFloat(computedStyle.marginRight);
                const marginLeft = parseFloat(computedStyle.marginLeft);
                cardWidth = width + marginRight + marginLeft + 15; // 15px is the gap
                
                // Update maxIndex based on viewport
                if (window.innerWidth <= 768 && window.innerWidth > 576) {
                    maxIndex = Math.max(0, cards.length - 2); // Show 2 cards on tablet
                } else {
                    maxIndex = Math.max(0, cards.length - 3); // Show 3 cards on desktop
                }
                
                updateSliderPosition();
                updateButtonStates();
            }
        }
        
        // Move the slider to the current position
        function updateSliderPosition() {
            const position = -currentIndex * cardWidth;
            sliderTrack.style.transform = `translateX(${position}px)`;
        }
        
        // Update button states (disabled/enabled)
        function updateButtonStates() {
            prevBtn.disabled = currentIndex <= 0;
            nextBtn.disabled = currentIndex >= maxIndex;
            
            // Visual feedback for disabled state
            prevBtn.style.opacity = prevBtn.disabled ? '0.5' : '1';
            nextBtn.style.opacity = nextBtn.disabled ? '0.5' : '1';
        }
        
        // Previous button click handler
        prevBtn.addEventListener('click', function() {
            if (currentIndex > 0) {
                currentIndex--;
                updateSliderPosition();
                updateButtonStates();
            }
        });
        
        // Next button click handler
        nextBtn.addEventListener('click', function() {
            if (currentIndex < maxIndex) {
                currentIndex++;
                updateSliderPosition();
                updateButtonStates();
            }
        });
        
        // Initialize slider
        calculateCardWidth();
        
        // Recalculate on window resize
        window.addEventListener('resize', function() {
            // If resized to mobile, disable slider functionality
            if (window.innerWidth <= 576) {
                sliderTrack.style.transform = 'none';
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
                return;
            }
            
            // If resized from mobile to larger, reinitialize
            if (window.innerWidth > 576 && prevBtn.style.display === 'none') {
                prevBtn.style.display = '';
                nextBtn.style.display = '';
                if (cards.length <= 3) {
                    prevBtn.style.display = 'none';
                    nextBtn.style.display = 'none';
                }
            }
            
            // Reset position first
            currentIndex = 0;
            calculateCardWidth();
        });
    });
</script>

<?php include  'footer.php';?>

<!-- Include autocomplete styles and module -->
<link rel="stylesheet" href="css/places-autocomplete.css">
<!-- Load Google Maps API with Dashboard callback -->


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date picker with today's date
    initializeDatePicker();
    
    // Initialize the ride slider
    initializeRideSlider();
});

// Initialize date picker
function initializeDatePicker() {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    document.getElementById('travel_date').value = yyyy + '-' + mm + '-' + dd;
    
    // Improved date input handling
    const dateInput = document.getElementById('travel_date');
    const dateWrapper = document.querySelector('.date-input-wrapper');
    
    dateWrapper.addEventListener('click', function(e) {
        if (e.target === dateInput || e.target === dateWrapper) {
            dateInput.focus();
            setTimeout(() => {
                const calendarIcon = dateInput.querySelector('::-webkit-calendar-picker-indicator');
                if (calendarIcon) {
                    calendarIcon.click();
                }
            }, 100);
        }
    });
    
    dateInput.addEventListener('click', function(e) {
        e.stopPropagation();
    });
}

// Animate sections with staggered animation
function animateSections() {
    // This function is no longer needed as we've moved animations to CSS
    // Keeping the function for compatibility but it's empty now
}

// Initialize ride slider functionality
function initializeRideSlider() {
    const sliderTrack = document.querySelector('.ride-slider-track');
    if (!sliderTrack) return;
    
    const cards = sliderTrack.querySelectorAll('.ride-card');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    // Don't initialize slider on mobile
    if (window.innerWidth <= 576 || cards.length <= 0) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        return;
    }
    
    if (cards.length <= 3) {
        // Hide navigation buttons if there are 3 or fewer cards
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
    }
    
    let currentIndex = 0;
    let cardWidth = 0;
    let cardsPerView = 3; // Default for desktop
    let maxIndex = 0;
    
    // Calculate card width and maximum slide index
    function calculateSliderDimensions() {
        if (cards.length > 0) {
            const computedStyle = window.getComputedStyle(cards[0]);
            const width = cards[0].offsetWidth;
            const marginRight = parseFloat(computedStyle.marginRight || '0');
            const marginLeft = parseFloat(computedStyle.marginLeft || '0');
            cardWidth = width + marginRight + marginLeft + 15; // 15px is the gap
            
            // Update cards per view based on viewport
            if (window.innerWidth <= 768 && window.innerWidth > 576) {
                cardsPerView = 2; // Show 2 cards on tablet
            } else {
                cardsPerView = 3; // Show 3 cards on desktop
            }
            
            maxIndex = Math.max(0, cards.length - cardsPerView);
            
            updateSliderPosition();
            updateButtonStates();
        }
    }
    
    // Move the slider to the current position with smooth animation
    function updateSliderPosition() {
        const position = -currentIndex * cardWidth;
        sliderTrack.style.transform = `translateX(${position}px)`;
    }
    
    // Update button states (disabled/enabled)
    function updateButtonStates() {
        if (prevBtn) {
            prevBtn.disabled = currentIndex <= 0;
            prevBtn.style.opacity = prevBtn.disabled ? '0.5' : '1';
            prevBtn.style.cursor = prevBtn.disabled ? 'not-allowed' : 'pointer';
        }
        
        if (nextBtn) {
            nextBtn.disabled = currentIndex >= maxIndex;
            nextBtn.style.opacity = nextBtn.disabled ? '0.5' : '1';
            nextBtn.style.cursor = nextBtn.disabled ? 'not-allowed' : 'pointer';
        }
    }
    
    // Previous button click handler
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (currentIndex > 0) {
                currentIndex--;
                updateSliderPosition();
                updateButtonStates();
            }
        });
    }
    
    // Next button click handler
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (currentIndex < maxIndex) {
                currentIndex++;
                updateSliderPosition();
                updateButtonStates();
            }
        });
    }
    
    // Initialize slider dimensions
    calculateSliderDimensions();
    
    // Recalculate on window resize
    window.addEventListener('resize', function() {
        // If resized to mobile, disable slider functionality
        if (window.innerWidth <= 576) {
            if (sliderTrack) sliderTrack.style.transform = 'none';
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            return;
        }
        
        // If resized from mobile to larger, reinitialize
        if (window.innerWidth > 576 && prevBtn && prevBtn.style.display === 'none') {
            prevBtn.style.display = '';
            nextBtn.style.display = '';
            if (cards.length <= 3) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            }
        }
        
        // Reset position first
        currentIndex = 0;
        calculateSliderDimensions();
    });
}

// Modern toast notification system
function showNotification(message, type = "info") {
    // Remove any existing notifications
    const existingNotifications = document.querySelectorAll('.notification-toast');
    existingNotifications.forEach(notification => {
        notification.remove();
    });
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification-toast notification-' + type;
    
    // Set icon based on notification type
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'warning') icon = 'exclamation-circle';
    if (type === 'error') icon = 'times-circle';
    
    notification.innerHTML = `
        <div class="notification-icon">
            <i class="fas fa-${icon}"></i>
        </div>
        <div class="notification-content">
            ${message}
        </div>
        <button class="notification-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add to document
    document.body.appendChild(notification);
    
    // Add styles
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.backgroundColor = '#fff';
    notification.style.borderRadius = '8px';
    notification.style.boxShadow = '0 5px 15px rgba(0,0,0,0.15)';
    notification.style.padding = '15px';
    notification.style.display = 'flex';
    notification.style.alignItems = 'center';
    notification.style.maxWidth = '400px';
    notification.style.zIndex = '9999';
    notification.style.transform = 'translateY(-20px)';
    notification.style.opacity = '0';
    notification.style.transition = 'all 0.3s ease';
    
    // Set color based on type
    let color = '#2196F3';  // Info blue
    if (type === 'success') color = '#4CAF50';  // Success green
    if (type === 'warning') color = '#FF9800';  // Warning orange
    if (type === 'error') color = '#F44336';    // Error red
    
    notification.style.borderLeft = `4px solid ${color}`;
    
    // Style icon
    const iconDiv = notification.querySelector('.notification-icon');
    iconDiv.style.color = color;
    iconDiv.style.fontSize = '20px';
    iconDiv.style.marginRight = '15px';
    
    // Style content
    const contentDiv = notification.querySelector('.notification-content');
    contentDiv.style.flex = '1';
    contentDiv.style.fontSize = '14px';
    contentDiv.style.color = '#333';
    
    // Style close button
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.style.background = 'none';
    closeBtn.style.border = 'none';
    closeBtn.style.color = '#999';
    closeBtn.style.cursor = 'pointer';
    closeBtn.style.fontSize = '14px';
    closeBtn.style.padding = '0 0 0 10px';
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateY(0)';
        notification.style.opacity = '1';
    }, 10);
    
    // Close on click
    closeBtn.addEventListener('click', () => {
        closeNotification(notification);
    });
    
    // Auto close after 4 seconds
    setTimeout(() => {
        closeNotification(notification);
    }, 4000);
    
    function closeNotification(notification) {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}

// Global variables for Dashboard Google Places initialization
let dashboardGoogleMapsLoaded = false;
let dashboardAutocompleteInitialized = false;

// Enhanced Google Places initialization function for Dashboard
function initDashboardAutocomplete() {
    if (dashboardAutocompleteInitialized) return;

    if (!window.google || !window.google.maps || !window.google.maps.places) {
        console.error('Google Maps API not loaded correctly for Dashboard');
        return;
    }

    try {
        console.log('Initializing Dashboard Google Places Autocomplete');

        const options = {
            types: ['address'],
            componentRestrictions: { country: 'in' },
            fields: ['address_components', 'formatted_address', 'geometry', 'name', 'place_id']
        };

        const fromCityInput = document.getElementById('from_city');
        const toCityInput = document.getElementById('to_city');

        if (fromCityInput && toCityInput) {
            const fromAutocomplete = new google.maps.places.Autocomplete(fromCityInput, options);
            const toAutocomplete = new google.maps.places.Autocomplete(toCityInput, options);

            // Enhanced place change handlers
            fromAutocomplete.addListener('place_changed', function() {
                const place = fromAutocomplete.getPlace();
                if (place.geometry && place.geometry.location) {
                    fromCityInput.value = place.formatted_address || place.name;
                    const latField = document.getElementById('from_lat');
                    const lngField = document.getElementById('from_lng');
                    if (latField) latField.value = place.geometry.location.lat();
                    if (lngField) lngField.value = place.geometry.location.lng();
                }
            });

            toAutocomplete.addListener('place_changed', function() {
                const place = toAutocomplete.getPlace();
                if (place.geometry && place.geometry.location) {
                    toCityInput.value = place.formatted_address || place.name;
                    const latField = document.getElementById('to_lat');
                    const lngField = document.getElementById('to_lng');
                    if (latField) latField.value = place.geometry.location.lat();
                    if (lngField) lngField.value = place.geometry.location.lng();
                }
            });

            // Prevent form submission on enter
            fromCityInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') e.preventDefault();
            });
                    
            toCityInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') e.preventDefault();
            });

            dashboardAutocompleteInitialized = true;
            console.log('Dashboard Google Places Autocomplete initialized successfully');
        }
    } catch (error) {
        console.error('Error initializing Dashboard Google Places Autocomplete:', error);
    }
}

// Callback function for Google Maps API in Dashboard
window.initDashboardGoogleMapsCallback = function() {
    dashboardGoogleMapsLoaded = true;
    console.log('Google Maps API loaded successfully for Dashboard');
    
    // Initialize autocomplete after API is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardAutocomplete);
    } else {
        initDashboardAutocomplete();
    }
};

// Enhanced error logging
window.onerror = function(msg, url, line, col, error) {
    console.error('Global error:', {
        message: msg,
        url: url,
        line: line,
        column: col,
        error: error
    });
    return false;
};

// Function to check Google API status
function checkGoogleApiStatus() {
    console.log('Checking Google API status...');
    if (window.google && window.google.maps && window.google.maps.places) {
        console.log('Google Maps API and Places library loaded successfully');
        return true;
    }
    console.error('Google Maps API or Places library not loaded');
    console.log('window.google exists:', !!window.google);
    console.log('window.google.maps exists:', !!(window.google && window.google.maps));
    console.log('window.google.maps.places exists:', !!(window.google && window.google.maps && window.google.maps.places));
    return false;
}

// Enhanced initialization function
function initDashboardAutocomplete() {
    console.log('Initializing Dashboard autocomplete...');
    
    if (!checkGoogleApiStatus()) {
        console.error('Google Maps API not loaded correctly for Dashboard');
        return;
    }

    try {
        console.log('Setting up autocomplete fields...');

        const options = {
            types: ['address'],
            componentRestrictions: { country: 'in' },
            fields: ['address_components', 'formatted_address', 'geometry', 'name', 'place_id']
        };

        const fromCityInput = document.getElementById('from_city');
        const toCityInput = document.getElementById('to_city');

        console.log('Input elements found:', {
            fromCity: !!fromCityInput,
            toCity: !!toCityInput
        });

        if (fromCityInput && toCityInput) {
            console.log('Creating autocomplete instances...');
            
            try {
                const fromAutocomplete = new google.maps.places.Autocomplete(fromCityInput, options);
                console.log('From autocomplete created successfully');
                
                const toAutocomplete = new google.maps.places.Autocomplete(toCityInput, options);
                console.log('To autocomplete created successfully');

                // Enhanced place change handlers with error catching
                fromAutocomplete.addListener('place_changed', function() {
                    try {
                        const place = fromAutocomplete.getPlace();
                        console.log('From place selected:', place);
                        
                        if (place.geometry && place.geometry.location) {
                            fromCityInput.value = place.formatted_address || place.name;
                            const latField = document.getElementById('from_lat');
                            const lngField = document.getElementById('from_lng');
                            if (latField) latField.value = place.geometry.location.lat();
                            if (lngField) lngField.value = place.geometry.location.lng();
                            console.log('From coordinates updated:', {
                                lat: place.geometry.location.lat(),
                                lng: place.geometry.location.lng()
                            });
                        } else {
                            console.warn('Selected place has no geometry:', place);
                        }
                    } catch (error) {
                        console.error('Error in from_city place_changed handler:', error);
                    }
                });

                toAutocomplete.addListener('place_changed', function() {
                    try {
                        const place = toAutocomplete.getPlace();
                        console.log('To place selected:', place);
                        
                        if (place.geometry && place.geometry.location) {
                            toCityInput.value = place.formatted_address || place.name;
                            const latField = document.getElementById('to_lat');
                            const lngField = document.getElementById('to_lng');
                            if (latField) latField.value = place.geometry.location.lat();
                            if (lngField) lngField.value = place.geometry.location.lng();
                            console.log('To coordinates updated:', {
                                lat: place.geometry.location.lat(),
                                lng: place.geometry.location.lng()
                            });
                        } else {
                            console.warn('Selected place has no geometry:', place);
                        }
                    } catch (error) {
                        console.error('Error in to_city place_changed handler:', error);
                    }
                });

                // Prevent form submission on enter
                fromCityInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') e.preventDefault();
                });
                        
                toCityInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') e.preventDefault();
                });

                console.log('Dashboard Google Places Autocomplete initialized successfully');
            } catch (error) {
                console.error('Error creating autocomplete instances:', error);
            }
        } else {
            console.error('Could not find input elements');
        }
    } catch (error) {
        console.error('Error in initDashboardAutocomplete:', error);
    }
}

// Enhanced Google Maps API loading
function loadDashboardGoogleMapsScript() {
    if (window.dashboardGoogleMapsLoaded) {
        console.log('Google Maps API already loaded');
        return;
    }
    
    console.log('Loading Google Maps API...');
    const script = document.createElement('script');
    const apiKey = '<?php echo GOOGLE_MAPS_API_KEY; ?>';
    
    // Use the recommended loading pattern
    script.src = `https://maps.googleapis.com/maps/api/js`;
    script.async = true;
    
    // Add URL parameters after setting async
    const params = new URLSearchParams({
        key: apiKey,
        libraries: 'places',  // Removed webcomponents as it's not a valid library
        callback: 'initDashboardGoogleMapsCallback',
        loading: 'async',
        v: 'weekly'
    });
    script.src += '?' + params.toString();
    
    script.onerror = function(error) {
        console.error('Failed to load Google Maps API:', error);
        const inputs = document.querySelectorAll('#from_city, #to_city');
        inputs.forEach(input => {
            input.disabled = true;
            input.placeholder = 'Location services temporarily unavailable';
        });
    };
    
    document.body.appendChild(script);
    window.dashboardGoogleMapsLoaded = true;
    console.log('Google Maps API script added to page');
}

// Enhanced callback function
window.initDashboardGoogleMapsCallback = function() {
    console.log('Google Maps API callback triggered');
    initDashboardAutocomplete();
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing Google Maps...');
    loadDashboardGoogleMapsScript();
});

// Enhanced error handler for Google Maps
window.gm_authFailure = function() {
    console.error('Google Maps authentication failed');
    const inputs = document.querySelectorAll('#from_city, #to_city');
    inputs.forEach(input => {
        input.disabled = true;
        input.placeholder = 'Location services unavailable';
    });
};

// Function to initialize Dashboard autocomplete
function initDashboardAutocomplete() {
    if (dashboardAutocompleteInitialized) return;

    try {
        console.log('Initializing Dashboard Modern Places Autocomplete');

        // Create instance of ModernPlacesAutocomplete
        const placesUtil = new ModernPlacesAutocomplete();

        // Initialize and setup autocomplete
        placesUtil.createAutocomplete({
            fromInputId: 'from_city',
            toInputId: 'to_city',
            fromLatId: 'from_lat',
            fromLngId: 'from_lng',
            toLatId: 'to_lat',
            toLngId: 'to_lng'
        }).catch(error => {
            console.error('Failed to initialize places autocomplete:', error);
        });

        dashboardAutocompleteInitialized = true;
        console.log('Dashboard Modern Places Autocomplete initialized successfully');
    } catch (error) {
        console.error('Error initializing Dashboard Modern Places Autocomplete:', error);
    }
}

// Add swap functionality
document.addEventListener('DOMContentLoaded', function() {
    const swapButton = document.getElementById('swapLocations');
    if (swapButton) {
        swapButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get all the elements
            const fromCity = document.getElementById('from_city');
            const toCity = document.getElementById('to_city');
            const fromLat = document.getElementById('from_lat');
            const fromLng = document.getElementById('from_lng');
            const toLat = document.getElementById('to_lat');
            const toLng = document.getElementById('to_lng');
            
            // Store the values
            const tempFromCity = fromCity.value;
            const tempFromLat = fromLat.value;
            const tempFromLng = fromLng.value;
            
            // Swap the values with animation
            swapButton.style.transform = 'rotate(180deg)';
            
            // Add transition class to inputs
            fromCity.style.transform = 'translateX(20px)';
            toCity.style.transform = 'translateX(-20px)';
            fromCity.style.opacity = '0';
            toCity.style.opacity = '0';
            
            // Perform the swap after a short delay
            setTimeout(() => {
                // Swap the values
                fromCity.value = toCity.value;
                fromLat.value = toLat.value;
                fromLng.value = toLng.value;
                
                toCity.value = tempFromCity;
                toLat.value = tempFromLat;
                toLng.value = tempFromLng;
                
                // Reset animations
                fromCity.style.transform = 'translateX(-20px)';
                toCity.style.transform = 'translateX(20px)';
                
                // Show the inputs again
                setTimeout(() => {
                    fromCity.style.transform = '';
                    toCity.style.transform = '';
                    fromCity.style.opacity = '';
                    toCity.style.opacity = '';
                }, 50);
            }, 150);
        });
    }
});

</script>
</div></body>
</html>
