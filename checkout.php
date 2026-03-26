<?php include 'nav.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Booking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
    <div class="sep">
    <div class="container">
        <div class="breadcrumb">
            <span>Dashboard ></span>
            <span>Search ></span>
            <span>Results ></span>
            <span>Ride Details ></span>
            <span><strong>Checkout</strong></span>
        </div>
        <h2>Complete Your Booking</h2>
        <div class="summary-container">
            <div class="card">
                <i class="fas fa-file-alt"></i>
                <p><strong>Booking Summary</strong></p>
                <p>San Francisco to Los Angeles - Today, 2:00 PM</p>
            </div>
            <div class="card">
                <i class="fas fa-user"></i>
                <p><strong>Driver</strong></p>
                <p>Michael Johnson - 4.8 ★</p>
            </div>
        </div>
        <h3>Passenger Information</h3>
        <div class="form-container">
            <label>Full Name</label>
            <input type="text" value="John Doe" readonly>
        
            <label>Phone Number</label>
            <input type="text" placeholder="Enter your phone number">
            <h3>Payment Details</h3>
        
            <label>Card Number</label>
            <input type="text" placeholder="1234 5678 9012 3456">
            
            <div class="payment-details">
                <div>
                    <label>Expiration Date</label>
                    <input type="text" placeholder="MM/YY">
                </div>
                <div>
                    <label>CVV</label>
                    <input type="text" placeholder="123">
                </div>
            </div>
        
            <label>Billing Zip Code</label>
            <input type="text" placeholder="Enter zip code">
        </div>
        <h3>Order Summary</h3>
        <div class="order-summary">
            <p>Ride fare (1 seat) <span>₹445.00</span></p>
            <p>Service fee <span>₹914.50</span></p>
            <p class="total">Total <span>₹749.50</span></p>
            
        </div>
        <br>
        <div class="profile-photo">
            <div class="photo-box">
              <div class="icon-container">
                <i class="fas fa-info-circle"></i>
              </div>
              <div class="text-content">
                <span class="title">Cancellation Policy</span>
                <span class="subtitle">Free cancellation up to 24 hours before departure</span>
              </div>
            </div>
          </div>
        <div class="buttons">
            <button class="btn btn-back">Back</button>
            <button class="btn btn-primary">Complete Payment</button>
        </div>
    </div>
    <style>
        .sep {
        font-family: 'Poppins', sans-serif;
        background-color: #fff;
        justify-content: center;
        align-items: center;
        padding: 40px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .breadcrumb {
            font-size: 14px;
            color: gray;
        }
        .breadcrumb span {
            margin-right: 5px;
        }
        h2 {
            font-size: 22px;
            font-weight: 600;
        }
        .summary-container {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 20px;
    }

    .card {
        background: #e3ddfc;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        width: 48%;
        text-align: left;
    }

    .card i {
        font-size: 20px;
        color: #6C49F4;
        display: block;
        margin-bottom: 10px;
    }

    .card p {
        margin: 5px 0;
        font-size: 14px;
        color: #333;
    }

    .card strong {
        font-size: 16px;
        color: black;
    }
        label {
            font-weight: 500;
            font-size: 14px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            background-color:#f3f3f3;
            border: transparent;
            border-radius: 5px;
            margin-top: 5px;
        }
        .form-container {
        max-width: 900px; /* Restrict max width */
        margin: auto;
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .form-container label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 5px;
        color: #333;
    }

    .form-container input {
        width: 100%;
        padding: 10px;
        border: transparent;
        border-radius: 8px;
        font-size: 14px;
        box-sizing: border-box; /* Prevents input overflow */
        margin-bottom: 15px; /* Adds spacing between fields */
    }

    .form-container input[readonly] {
        background: #f3f3f3; /* Light background for read-only fields */
    }

    .payment-details {
        display: flex;
        justify-content: space-between;
        gap: 15px; /* Proper spacing between fields */
        margin-bottom: 15px;
    }

    .payment-details div {
        flex: 1; /* Ensures equal width */
    }
        .order-summary {
            background: #F3F1FF;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }
        .order-summary p {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .total {
            font-weight: bold;
        }
        .cancellation {
            font-size: 12px;
            color: gray;
            margin-top: 10px;
        }
        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-back {
            background: #E0E0E0;
        }
        .btn-primary {
            background: #ffbf00;
            color: white;
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
