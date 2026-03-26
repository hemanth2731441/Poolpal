<?php
session_start();
require_once 'includes/PaymentProcessor.php';
require_once 'config/payment_config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['booking_id']) || !isset($_POST['amount'])) {
    header('Location: index.php');
    exit();
}

try {
    $bookingId = $_POST['booking_id'];
    $amount = $_POST['amount'];
    
    // Get booking details from session
    $bookingDetails = $_SESSION['booking_details'] ?? null;
    if (!$bookingDetails) {
        throw new Exception("Booking details not found");
    }
    
    // Initialize payment processor
    $processor = new PaymentProcessor();
    
    // Create Razorpay order
    $razorpayOrder = $processor->createOrder($bookingId, $amount);
    $orderId = $razorpayOrder['id'];
    
    // Get user details from session
    $userEmail = $bookingDetails['user_email'] ?? '';
    $userName = $bookingDetails['user_name'] ?? '';
    $userPhone = $bookingDetails['user_phone'] ?? '';
    
    // Store order ID in session for verification
    $_SESSION['razorpay_order_id'] = $orderId;
    
} catch (Exception $e) {
    error_log("Payment processing error: " . $e->getMessage());
    $_SESSION['payment_error'] = "Failed to initialize payment. Please try again.";
    header('Location: Ridedetails.php?id=' . $bookingDetails['trip_id'] . '&error=payment_init_failed');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - PoolPal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .payment-header img {
            height: 50px;
            margin-bottom: 20px;
        }
        
        .payment-container h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
        }
        
        .payment-details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        .payment-details p {
            margin: 12px 0;
            color: #555;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
        }
        
        .payment-details .amount {
            font-size: 24px;
            color: #007bff;
            font-weight: bold;
        }
        
        .payment-button {
            display: block;
            width: 100%;
            padding: 16px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .payment-button:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }
        
        .payment-icon {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .payment-icon i {
            font-size: 48px;
            color: #007bff;
        }
        
        .secure-badge {
            text-align: center;
            margin-top: 20px;
            color: #28a745;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .error-message {
            color: #dc3545;
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            border-radius: 6px;
            background: #fff;
            border: 1px solid #dc3545;
            display: none;
        }
        
        .payment-methods {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .payment-methods img {
            height: 35px;
            opacity: 1;
        }
        
        .payment-methods img[alt="UPI"] {
            height: 42px;
            width: 45px; /* Smaller size specifically for UPI icon */
        }
    </style>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="payment-container">
        <div class="payment-header">
            <img src="images/logo.png" alt="PoolPal">
            <h2>Complete Your Payment</h2>
        </div>
        
        <div id="errorMessage" class="error-message"></div>
        
        <div class="payment-details">
            <p>
                <span>Booking ID:</span>
                <span>#<?php echo htmlspecialchars($bookingId); ?></span>
            </p>
            <p>
                <span>Customer:</span>
                <span><?php echo htmlspecialchars($userName); ?></span>
            </p>
            <p class="amount">
                <span>Amount:</span>
                <span>₹<?php echo htmlspecialchars(number_format($amount, 2)); ?></span>
            </p>
        </div>
        
        <button id="payButton" class="payment-button">
            <i class="fas fa-lock"></i> Pay Securely Now
        </button>
        
        <div class="payment-methods">
            <img src="images/BANK/UPI.svg" alt="UPI">
            <img src="images/BANK/card.png" alt="Cards">
            <img src="images/BANK/net.png" alt="Net Banking">
            <img src="images/BANK/wallet.png" alt="Wallets">
        </div>
        
        <div class="secure-badge">
            <i class="fas fa-shield-alt"></i>
            <span>100% Secure Payments by Razorpay</span>
        </div>
    </div>

    <script>
        var options = {
            "key": "<?php echo RAZORPAY_KEY_ID; ?>",
            "amount": "<?php echo $amount * 100; ?>",
            "currency": "<?php echo RAZORPAY_CURRENCY; ?>",
            "name": "<?php echo MERCHANT_NAME; ?>",
            "description": "Booking ID: #<?php echo $bookingId; ?>",
            "image": "<?php echo MERCHANT_LOGO_URL; ?>",
            "order_id": "<?php echo $orderId; ?>",
            "handler": function (response) {
                document.getElementById("razorpay_payment_id").value = response.razorpay_payment_id;
                document.getElementById("razorpay_order_id").value = response.razorpay_order_id;
                document.getElementById("razorpay_signature").value = response.razorpay_signature;
                document.getElementById("payment_form").submit();
            },
            "prefill": {
                "name": "<?php echo htmlspecialchars($userName); ?>",
                "email": "<?php echo htmlspecialchars($userEmail); ?>",
                "contact": "<?php echo htmlspecialchars($userPhone); ?>"
            },
            "notes": {
                "booking_id": "<?php echo $bookingId; ?>",
                "merchant_id": "<?php echo MERCHANT_ID; ?>"
            },
            "theme": {
                "color": "#007bff"
            },
            "modal": {
                "ondismiss": function() {
                    document.getElementById("errorMessage").style.display = "block";
                    document.getElementById("errorMessage").innerHTML = "Payment cancelled. Please try again.";
                }
            }
        };
        
        var rzp = new Razorpay(options);
        
        rzp.on('payment.failed', function (response) {
            document.getElementById("errorMessage").style.display = "block";
            document.getElementById("errorMessage").innerHTML = "Payment failed: " + response.error.description;
            console.error("Payment Failed:", response.error);
        });
        
        document.getElementById('payButton').onclick = function(e) {
            rzp.open();
            e.preventDefault();
        }
    </script>
    
    <form id="payment_form" action="verify_payment.php" method="POST" style="display: none;">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
        <input type="hidden" name="razorpay_signature" id="razorpay_signature">
        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($bookingId); ?>">
    </form>

    <?php include 'footer.php'; ?>
</body>
</html> 