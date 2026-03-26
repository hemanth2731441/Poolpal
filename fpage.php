<?php include 'nav.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
  <div class="sep">
    <div class="container">
        <div class="header">Payment Receipt</div>
        <div class="receipt-box">
            <i class="fas fa-receipt"></i>
            <div>
                <strong>Receipt #RS-78945612</strong><br>
                Issued on May 15, 2023
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Trip Summary</div>
            <div class="details"><span><i class="fas fa-route icon"></i> Route</span> <span>San Francisco to Los Angeles</span></div>
            <div class="details"><span><i class="fas fa-calendar-alt icon"></i> Date</span> <span>May 15, 2023</span></div>
            <div class="details"><span><i class="fas fa-bus icon"></i> Departure</span> <span>San Francisco Transit Center, 2:00 PM</span></div>
            <div class="details"><span><i class="fas fa-flag icon"></i> Arrival</span> <span>Los Angeles Union Station, 8:30 PM</span></div>
        </div>
        
        <div class="section">
            <div class="section-title">Payment Details</div>
            <div class="details"><span><i class="fas fa-dollar-sign icon"></i> Base Fare</span> <span>₹435.00</span></div>
            <div class="details"><span><i class="fas fa-receipt icon"></i> Service Fee</span> <span>₹930.50</span></div>
            <div class="details"><span><i class="fas fa-shield-alt icon"></i> Insurance</span> <span>₹291.00</span></div>
            <div class="details"><span><i class="fas fa-wallet icon"></i> <strong>Total Paid</strong></span> <span><strong>₹1049.50</strong></span></div>
        </div>
        
        <div class="section">
            <div class="section-title">Payment Method</div>
            <div class="profile-photo">
              <div class="photo-box">
                <div class="icon-container">
                  <i class="fas fa-credit-card icon"></i>
                </div>
                <div class="text-content">
                  <span class="title">Visa •••• 3456</span>
                  <span class="subtitle">Charged on May 15, 2023</span>
                </div>
              </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Passenger Information</div>
            <div class="profile-photo">
              <div class="photo-box">
                <div class="icon-container">
                  <i class="fas fa-user-circle icon"></i>
                </div>
                <div class="text-content">
                  <span class="title">Your Name</span>
                  <span class="subtitle">your.email@example.com • +91 9999999999</span>
                </div>
              </div>
            </div>
        </div>
        
        <div class="footer">
            <button class="btn btn-primary">Back to Dashboard</button>
        </div>
    </div>
    <style>
        .sep {
            font-family: 'Inter', sans-serif;
            background-color: #f9f9fb;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            width: 600px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .receipt-box {
            background: #f1f1f9;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }
        .receipt-box i {
            margin-right: 10px;
            font-size: 18px;
            color: #6c63ff;
        }
        .section {
            margin-top: 20px;
        }
        .section-title {
            font-weight: 600;
            margin-bottom: 10px;
        }
        .details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }
        .details:last-child {
            border-bottom: none;
        }
        .icon {
            margin-right: 10px;
            color: #6c63ff;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-primary {
            background-color: #6c63ff;
            color: white;
        }
        .btn-secondary {
            background-color: #e0e0e0;
            color: black;
        }
        .profile-photo {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: white;
      padding: 0px;
      border-radius: 8px;
      margin-bottom: 8px;
    }

    .photo-box {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .photo-box .icon-container {
      width: 40px;
      height: 40px;
      background: #f6f5ff; /* Light purple background */
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 12px;
    }

    .photo-box i {
      font-size: 18px;
      color: #6C49F4; /* Purple color */
    }
    .text-content {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .text-content .title {
      font-size: 14px;
      display: flex;
      color: #000;
    }

    .text-content .subtitle {
      font-size: 12px;
      color: gray;
    }
    </style>
    </div>
</div></body>
</html>
