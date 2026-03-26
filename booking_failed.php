<?php
session_start();

$error = isset($_GET['error']) ? $_GET['error'] : 'Payment could not be processed';
$bookingId = isset($_GET['booking_id']) ? $_GET['booking_id'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - PoolPal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .failed-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .failed-icon {
            color: #dc3545;
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .failed-title {
            color: #333;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .failed-message {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }
        
        .error-details {
            background: #fff1f0;
            border: 1px solid #ffccc7;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            color: #cf1322;
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
            background: #dc3545;
            color: #fff;
            border: none;
        }
        
        .primary-button:hover {
            background: #c82333;
        }
        
        .secondary-button {
            background: #6c757d;
            color: #fff;
            border: none;
        }
        
        .secondary-button:hover {
            background: #5a6268;
        }
        
        .support-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #666;
        }
        
        .support-info p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="failed-container">
        <i class="fas fa-times-circle failed-icon"></i>
        <h1 class="failed-title">Payment Failed</h1>
        <p class="failed-message">We were unable to process your payment. Don't worry, no money has been deducted from your account.</p>
        
        <div class="error-details">
            <i class="fas fa-exclamation-circle"></i>
            Error: <?php echo htmlspecialchars($error); ?>
            <?php if ($bookingId): ?>
                <br>Booking ID: <?php echo htmlspecialchars($bookingId); ?>
            <?php endif; ?>
        </div>
        
        <div class="action-buttons">
            <a href="<?php echo 'Ridedetails.php?id=' . $_SESSION['booking_details']['trip_id']; ?>" class="action-button primary-button">
                <i class="fas fa-redo"></i> Try Again
            </a>
            <a href="dashboard.php" class="action-button secondary-button">
                <i class="fas fa-home"></i> Go to Dashboard
            </a>
        </div>
        
        <div class="support-info">
            <p><i class="fas fa-headset"></i> Need help? Contact our support team</p>
            <p><i class="fas fa-envelope"></i> support@poolpal.com</p>
            <p><i class="fas fa-phone"></i> +91 1234567890</p>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html> 