<?php
include 'header.php';
include 'db.php'; // make sure this file connects using $conn

if (isset($_GET['id'])) {
    $driver_id = $_GET['id'];

    // Fetch driver details
    $query = "SELECT * FROM drivers WHERE ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $driver = $result->fetch_assoc();

        // Fetch driver stats - count how many trips they've completed
        $stats_query = "SELECT COUNT(*) as trip_count FROM trips WHERE driver_email = ?";
        $stats_stmt = $conn->prepare($stats_query);
        $stats_stmt->bind_param("s", $driver['Email']);
        $stats_stmt->execute();
        $stats_result = $stats_stmt->get_result();
        $stats = $stats_result->fetch_assoc();
        $trip_count = $stats['trip_count'] ?? 0;

        // Calculate driver membership duration
        $member_since = new DateTime($driver['member_since'] ?? 'now');
        $now = new DateTime();
        $membership_duration = $member_since->diff($now);
    } else {
        echo "Driver not found.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Profile - <?= htmlspecialchars($driver['Full_Name']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* General Styles */
        :root {
            --primary-color: #ffbf00;
            --primary-dark: #e6ac00;
            --primary-light: #ffe180;
            --primary-bg: #fffbf0;
            --secondary-color: #333;
            --text-color: #333;
            --text-light: #666;
            --border-color: #eaeaea;
            --shadow-sm: 0 2px 10px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --transition-fast: 0.3s;
            --transition-med: 0.5s;
            --green: #4CAF50;
            --blue: #2196F3;
            --orange: #FF9800;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: var(--text-color);
            -webkit-text-size-adjust: 100%;
            -webkit-font-smoothing: antialiased;
            line-height: 1.6;
            font-size: 14px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px 15px;
            width: 100%;
        }
        
        .profile-wrapper {
            position: relative;
        }

        /* Advanced Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulseGlow {
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

        @keyframes floatingIcon {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-5px);
            }
            100% {
                transform: translateY(0px);
            }
        }

        @keyframes gradientBg {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        /* Breadcrumb */
        .breadcrumb {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: white;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-sm);
            display: flex;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            animation: fadeInUp 0.8s ease;
            border-left: 3px solid var(--primary-color);
            z-index: 1;
        }

        .breadcrumb span {
            display: inline-block;
            position: relative;
            transition: all 0.3s ease;
        }

        .breadcrumb span:hover {
            color: var(--primary-color);
        }

        .breadcrumb span:not(:last-child) {
            margin-right: 25px;
        }

        .breadcrumb span:not(:last-child)::after {
            content: '›';
            position: absolute;
            right: -15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .breadcrumb .active {
            font-weight: 500;
            color: var(--primary-color);
        }
        
        /* Layout */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Profile Header */
        .profile-header {
            background-color: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 30px;
            position: relative;
            animation: fadeInUp 1s ease;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .profile-header:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .profile-banner {
            background: linear-gradient(-45deg, #ffbf00, #ff8c00, #e73c7e, #23a6d5);
            background-size: 400% 400%;
            height: 120px;
            position: relative;
            animation: gradientBg 15s ease infinite;
        }
        
        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.2);
            z-index: 1;
        }

        .profile-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 4px solid white;
            position: absolute;
            bottom: -50px;
            left: 30px;
            box-shadow: var(--shadow-md);
            background-color: white;
            overflow: hidden;
            transition: all 0.4s ease;
            z-index: 2;
        }

        .profile-avatar:hover {
            transform: scale(1.05);
            border-color: var(--primary-color);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .profile-avatar:hover img {
            transform: scale(1.1);
        }

        .profile-info {
            padding: 15px 20px 20px 150px;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            min-height: 70px;
        }
        
        .profile-credentials {
            display: flex;
            flex-direction: column;
        }

        .profile-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 2px;
            transition: color 0.3s ease;
            animation: fadeInLeft 1s ease;
            letter-spacing: -0.5px;
            color: var(--secondary-color);
        }

        .profile-since {
            color: var(--text-light);
            font-size: 12px;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 6px;
            animation: fadeInLeft 1.2s ease;
        }

        .profile-since i {
            color: var(--primary-color);
            animation: floatingIcon 3s ease-in-out infinite;
            font-size: 11px;
        }

        .profile-verification {
            display: inline-flex;
            align-items: center;
            background-color: #e8f5e9;
            color: var(--green);
            font-size: 12px;
            font-weight: 500;
            padding: 4px 10px;
            border-radius: 20px;
            animation: fadeInLeft 1.4s ease;
            transition: all 0.3s ease;
        }

        .profile-verification:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
            background-color: #d4edda;
        }

        .profile-verification i {
            margin-right: 5px;
            animation: floatingIcon 3s ease-in-out infinite;
            font-size: 11px;
        }

        /* Stats */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: white;
            border-radius: var(--radius-sm);
            padding: 15px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 1.2s ease;
            border-bottom: 2px solid transparent;
        }

        .stat-card:nth-child(1) {
            border-color: var(--primary-color);
        }

        .stat-card:nth-child(2) {
            border-color: var(--blue);
        }

        .stat-card:nth-child(3) {
            border-color: var(--orange);
        }
        
        .stat-icon {
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .stat-card:nth-child(1) .stat-icon {
            color: var(--primary-color);
        }
        
        .stat-card:nth-child(2) .stat-icon {
            color: var(--blue);
        }
        
        .stat-card:nth-child(3) .stat-icon {
            color: var(--orange);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transform: translateX(-100%);
            transition: all 0.8s ease;
        }

        .stat-card:hover::before {
            transform: translateX(100%);
        }

        .stat-value {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 3px;
            transition: transform 0.3s ease;
            position: relative;
            display: inline-block;
            line-height: 1.2;
        }

        .stat-card:nth-child(2) .stat-value {
            color: var(--blue);
        }

        .stat-card:nth-child(3) .stat-value {
            color: var(--orange);
        }

        .stat-card:hover .stat-value {
            transform: scale(1.1);
        }

        .stat-label {
            color: var(--text-light);
            font-size: 12px;
            transition: all 0.3s ease;
            position: relative;
            font-weight: 400;
        }

        .stat-card:hover .stat-label {
            color: var(--text-color);
            font-weight: 500;
        }
        
        /* Reviews Section */
        .reviews-section {
            background-color: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.4s ease;
        }
        
        .reviews-section:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-5px);
        }
        
        .review-item {
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .review-item:last-child {
            border-bottom: none;
        }
        
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            gap: 10px;
        }
        
        .reviewer-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-size: 16px;
        }
        
        .reviewer-info {
            flex-grow: 1;
        }
        
        .reviewer-name {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-color);
            margin-bottom: 2px;
        }
        
        .review-rating {
            color: var(--primary-color);
            font-size: 11px;
        }
        
        .review-date {
            font-size: 11px;
            color: var(--text-light);
        }
        
        .review-text {
            font-size: 12px;
            color: var(--text-light);
            line-height: 1.4;
            padding-left: 40px;
        }

        /* Details Section */
        .details-section, .vehicle-section {
            background-color: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.4s ease;
            animation: fadeInUp 1.4s ease;
            position: relative;
            overflow: hidden;
        }

        .details-section:hover, .vehicle-section:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-5px);
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            position: relative;
            padding-left: 12px;
            letter-spacing: -0.3px;
            color: var(--secondary-color);
        }

        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background-color: var(--primary-color);
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        .details-section:hover .section-title::before, .vehicle-section:hover .section-title::before {
            width: 5px;
            background-color: var(--primary-dark);
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .detail-item:hover {
            background-color: var(--primary-bg);
            padding-left: 8px;
            border-radius: var(--radius-sm);
            border-bottom-color: transparent;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-icon {
            color: var(--primary-color);
            font-size: 14px;
            min-width: 30px;
            height: 30px;
            background-color: var(--primary-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .detail-item:hover .detail-icon {
            transform: scale(1.1) rotate(5deg);
            background-color: var(--primary-light);
        }

        .detail-content h4 {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 3px;
            transition: all 0.3s ease;
            color: var(--secondary-color);
        }

        .detail-item:hover .detail-content h4 {
            color: var(--primary-color);
        }

        .detail-content p {
            color: var(--text-light);
            font-size: 12px;
            transition: all 0.3s ease;
            margin: 0;
            line-height: 1.4;
        }

        .detail-item:hover .detail-content p {
            color: var(--text-color);
        }

        /* Vehicle Section */
        .vehicle-detail {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background-color: var(--primary-bg);
            border-radius: var(--radius-sm);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            flex-wrap: wrap;
        }

        .vehicle-detail::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-color), transparent);
        }

        .vehicle-detail:hover {
            background-color: #fff8e0;
            box-shadow: var(--shadow-sm);
            transform: translateY(-3px);
        }

        .vehicle-icon {
            font-size: 24px;
            color: var(--primary-color);
            transition: all 0.5s ease;
            min-width: 40px;
            height: 40px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm);
        }

        .vehicle-detail:hover .vehicle-icon {
            transform: rotate(15deg) scale(1.1);
            color: var(--primary-dark);
        }

        .vehicle-info {
            flex-grow: 1;
        }

        .vehicle-name {
            font-size: 14px;
            font-weight: 500;
            color: var(--secondary-color);
            transition: all 0.3s ease;
        }

        .vehicle-detail:hover .vehicle-name {
            color: var(--primary-color);
        }

        .vehicle-number {
            color: var(--text-light);
            font-size: 12px;
            transition: all 0.3s ease;
        }
        
        .vehicle-features {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-left: auto;
        }
        
        .feature-tag {
            font-size: 11px;
            background-color: rgba(255, 191, 0, 0.1);
            color: var(--primary-dark);
            padding: 3px 8px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .vehicle-detail:hover .feature-tag {
            background-color: rgba(255, 191, 0, 0.2);
            transform: translateY(-2px);
        }

        /* Contact Button */
        .contact-container {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            animation: fadeInUp 1.8s ease;
        }

        .contact-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            background: linear-gradient(45deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 500;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.4s ease;
            gap: 8px;
            position: relative;
            overflow: hidden;
            z-index: 1;
            box-shadow: 0 4px 15px rgba(255, 191, 0, 0.3);
            flex: 1;
        }

        .contact-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, var(--primary-dark), var(--primary-color));
            opacity: 0;
            z-index: -1;
            transition: all 0.5s ease;
        }

        .contact-btn:hover::before {
            opacity: 1;
        }

        .contact-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(255, 191, 0, 0.4);
        }

        .contact-btn i {
            transition: all 0.3s ease;
        }

        .contact-btn:hover i {
            transform: rotate(15deg);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            background-color: white;
            color: var(--secondary-color);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-weight: 500;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.4s ease;
            text-decoration: none;
            gap: 8px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            flex: 1;
        }

        .back-btn::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--primary-color);
            transition: all 0.4s ease;
        }

        .back-btn:hover {
            background-color: var(--primary-bg);
            color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light);
        }

        .back-btn:hover::after {
            width: 100%;
        }

        .back-btn i {
            transition: all 0.3s ease;
        }

        .back-btn:hover i {
            transform: translateX(-5px);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .container {
                padding: 15px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .profile-banner {
                height: 110px;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
                left: 50%;
                transform: translateX(-50%);
                bottom: -50px;
            }
            
            .profile-info {
                padding: 60px 15px 15px;
                text-align: center;
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
            
            .profile-name {
                font-size: 18px;
            }
            
            .profile-credentials {
                align-items: center;
            }
            
            .profile-since {
                justify-content: center;
            }
            
            .stat-value {
                font-size: 22px;
            }
            
            .details-section, .vehicle-section, .reviews-section {
                padding: 15px;
            }
            
            .vehicle-detail {
                padding: 12px;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .vehicle-features {
                margin: 8px 0 0;
                width: 100%;
            }
            
            .contact-container {
                flex-direction: column;
            }
        }

        @media (max-width: 576px) {
            .profile-banner {
                height: 90px;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
                bottom: -40px;
            }
            
            .profile-info {
                padding: 50px 10px 15px;
            }
            
            .profile-name {
                font-size: 16px;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .section-title {
                font-size: 15px;
            }
            
            .detail-item {
                padding: 10px 0;
            }
            
            .detail-icon {
                font-size: 12px;
            }
            
            .detail-content h4 {
                font-size: 13px;
            }
        }
    </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <span>Dashboard</span>
            <span>Rides</span>
            <span class="active">Driver Profile</span>
        </div>

        <div class="profile-wrapper">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-banner">
                    <div class="banner-overlay"></div>
                </div>
                <div class="profile-avatar">
                    <img src="<?= !empty($driver['Profile_Pic']) ? htmlspecialchars($driver['Profile_Pic']) : 'images/default-profile.jpg' ?>" alt="<?= htmlspecialchars($driver['Full_Name']) ?>">
                </div>
                <div class="profile-info">
                    <div class="profile-credentials">
                        <h1 class="profile-name"><?= htmlspecialchars($driver['Full_Name']) ?></h1>
                        <div class="profile-since">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Member since <?= date('F Y', strtotime($driver['member_since'] ?? 'now')) ?></span>
                        </div>
                    </div>
                    <?php if(isset($driver['verification_status']) && $driver['verification_status'] == 'accepted'): ?>
                    <div class="profile-verification">
                        <i class="fas fa-check-circle"></i> Verified Driver
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="main-content">
                <div class="left-column">
                    <!-- Driver Details -->
                    <div class="details-section">
                        <h2 class="section-title">Driver Information</h2>
                        <div class="details-content">
                            <?php if(!empty($driver['Languages'])): ?>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-language"></i>
                                </div>
                                <div class="detail-content">
                                    <h4>Languages</h4>
                                    <p><?= htmlspecialchars($driver['Languages']) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($driver['Contact'])): ?>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div class="detail-content">
                                    <h4>Contact</h4>
                                    <p><?= htmlspecialchars($driver['Contact']) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if(!empty($driver['Email'])): ?>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="detail-content">
                                    <h4>Email</h4>
                                    <p><?= htmlspecialchars($driver['Email']) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div class="detail-content">
                                    <h4>Driving License</h4>
                                    <p>Verified Driver</p>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div class="detail-content">
                                    <h4>Safety</h4>
                                    <p>All rides are insured and follow safety protocols</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Details -->
                    <div class="vehicle-section">
                        <h2 class="section-title">Vehicle Information</h2>
                        <div class="vehicle-detail">
                            <div class="vehicle-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="vehicle-info">
                                <div class="vehicle-name"><?= !empty($driver['vehicle_name']) ? htmlspecialchars($driver['vehicle_name']) : 'Vehicle' ?></div>
                                <div class="vehicle-number"><?= htmlspecialchars($driver['Vehicle_Number']) ?></div>
                            </div>
                            <div class="vehicle-features">
                                <div class="feature-tag">AC</div>
                                <div class="feature-tag">Music</div>
                                <div class="feature-tag">Luggage Space</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="right-column">
                    <!-- Stats -->
                    <div class="stats-container">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-route"></i></div>
                            <div class="stat-value"><?= $trip_count ?></div>
                            <div class="stat-label">Trips Completed</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-star"></i></div>
                            <div class="stat-value">4.8</div>
                            <div class="stat-label">Rating</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                            <div class="stat-value"><?= $membership_duration->y ?></div>
                            <div class="stat-label">Years Active</div>
                        </div>
                    </div>

                    <!-- Reviews Section Placeholder -->
                    <div class="reviews-section">
                        <h2 class="section-title">Recent Reviews</h2>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="reviewer-info">
                                    <div class="reviewer-name">Rahul M.</div>
                                    <div class="review-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                                <div class="review-date">2 weeks ago</div>
                            </div>
                            <div class="review-text">
                                Great driver, very punctual and safe driving. Car was clean and comfortable.
                            </div>
                        </div>
                        
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="reviewer-info">
                                    <div class="reviewer-name">Priya S.</div>
                                    <div class="review-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                </div>
                                <div class="review-date">1 month ago</div>
                            </div>
                            <div class="review-text">
                                The ride was comfortable and driver was professional. Would recommend!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Button -->
            <div class="contact-container">
                <a href="tel:<?= htmlspecialchars($driver['Contact']) ?>" class="contact-btn">
                    <i class="fas fa-phone"></i> Contact Driver
                </a>
                <a href="javascript:history.back()" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Ride Details
                </a>
            </div>
        </div>
    </div>

    <script>
        // Add animations on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.profile-header, .stat-card, .details-section, .vehicle-section, .reviews-section, .contact-container');
                
                elements.forEach((element, index) => {
                    const elementTop = element.getBoundingClientRect().top;
                    const elementBottom = element.getBoundingClientRect().bottom;
                    
                    if (elementTop < window.innerHeight && elementBottom > 0) {
                        if (!element.classList.contains('animated')) {
                            element.classList.add('animated');
                            
                            // Apply sequential delay for smoother animation
                            const delay = 100 * index;
                            
                            setTimeout(() => {
                                element.style.opacity = "0";
                                element.style.transform = "translateY(30px)";
                                
                                setTimeout(() => {
                                    element.style.transition = "opacity 0.8s ease-out, transform 0.6s ease-out";
                                    element.style.opacity = "1";
                                    element.style.transform = "translateY(0)";
                                }, 50);
                            }, delay);
                        }
                    }
                });
            };
            
            // Add hover effects to stat cards
            const statCards = document.querySelectorAll('.stat-value');
            statCards.forEach(card => {
                // Create counting animation for stat values
                const finalValue = parseInt(card.textContent);
                let startValue = 0;
                let duration = 2000;
                let startTime = null;
                
                const countUp = (timestamp) => {
                    if (!startTime) startTime = timestamp;
                    const progress = Math.min((timestamp - startTime) / duration, 1);
                    const currentValue = Math.floor(progress * finalValue);
                    card.textContent = currentValue;
                    
                    if (progress < 1) {
                        window.requestAnimationFrame(countUp);
                    } else {
                        card.textContent = finalValue;
                    }
                };
                
                window.requestAnimationFrame(countUp);
            });
            
            // Add floating animation to icons
            const icons = document.querySelectorAll('.detail-icon i, .vehicle-icon i, .stat-icon i');
            icons.forEach(icon => {
                icon.style.transition = "transform 0.3s ease";
                
                icon.addEventListener('mouseover', () => {
                    icon.style.transform = "scale(1.3) rotate(5deg)";
                });
                
                icon.addEventListener('mouseout', () => {
                    icon.style.transform = "scale(1) rotate(0)";
                });
            });
            
            // Add ripple effect to buttons
            const buttons = document.querySelectorAll('.contact-btn, .back-btn');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const x = e.clientX - e.target.getBoundingClientRect().left;
                    const y = e.clientY - e.target.getBoundingClientRect().top;
                    
                    const ripple = document.createElement("span");
                    ripple.style.position = "absolute";
                    ripple.style.width = "100px";
                    ripple.style.height = "100px";
                    ripple.style.borderRadius = "50%";
                    ripple.style.backgroundColor = "rgba(255, 255, 255, 0.4)";
                    ripple.style.left = x + "px";
                    ripple.style.top = y + "px";
                    ripple.style.transform = "translate(-50%, -50%) scale(0)";
                    ripple.style.animation = "ripple 0.6s linear";
                    
                    button.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Add intersection observer for better scroll animation
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });
            
            document.querySelectorAll('.detail-item, .review-item').forEach(item => {
                observer.observe(item);
            });
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Run once on load
            
            // Add extra style for ripple effect
            const style = document.createElement('style');
            style.innerHTML = `
                @keyframes ripple {
                    to {
                        transform: translate(-50%, -50%) scale(3);
                        opacity: 0;
                    }
                }
                
                .detail-item.visible, .review-item.visible {
                    animation: fadeInLeft 0.5s ease forwards;
                }
                
                .contact-btn, .back-btn {
                    position: relative;
                    overflow: hidden;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</div></body>
</html> 