<?php
// Razorpay Configuration
define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_XXXXXXXXXXXX');
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: 'XXXXXXXXXXXXXXXXXXXXXXXX');
define('RAZORPAY_CURRENCY', 'INR');

// Merchant Configuration
define('MERCHANT_NAME', 'PoolPal');
define('MERCHANT_ID', getenv('MERCHANT_ID') ?: 'your_merchant_id');
define('MERCHANT_LOGO_URL', 'https://poolpal.in/images/logo.png');
define('MERCHANT_UPI_ID', getenv('MERCHANT_UPI_ID') ?: 'your_upi_id');

// Webhook Configuration
define('WEBHOOK_SECRET', 'whsec_poolpal_' . bin2hex(random_bytes(16))); // Generate a secure webhook secret
define('WEBHOOK_URL', 'https://poolpal.in/webhook.php'); // Replace with your actual domain

// Payment Status Constants
define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_SUCCESS', 'success');
define('PAYMENT_STATUS_FAILED', 'failed');
define('PAYMENT_STATUS_REFUNDED', 'refunded');

// Payment URLs
define('PAYMENT_SUCCESS_URL', 'bookconfrm.php');
define('PAYMENT_FAILURE_URL', 'booking_failed.php');

// Payment Method Constants
define('PAYMENT_METHOD_UPI', 'upi');
define('PAYMENT_METHOD_CARD', 'card');
define('PAYMENT_METHOD_NETBANKING', 'netbanking');
define('PAYMENT_METHOD_WALLET', 'wallet');

// Minimum required PHP extensions
$required_extensions = array('curl', 'json');
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        die("The {$ext} extension is required for payment processing.");
    }
} 