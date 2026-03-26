<?php
require_once 'config/payment_config.php';
require_once 'includes/PaymentProcessor.php';
require_once 'includes/Database.php';

// Get webhook payload
$webhookBody = file_get_contents('php://input');
$webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

try {
    // Verify webhook signature
    $processor = new PaymentProcessor();
    if (!$processor->verifyWebhookSignature($webhookBody, $webhookSignature)) {
        throw new Exception('Invalid webhook signature');
    }

    // Process webhook data
    $webhookData = json_decode($webhookBody, true);
    if (!$webhookData) {
        throw new Exception('Invalid webhook data');
    }

    // Get event and payload
    $event = $webhookData['event'];
    $payload = $webhookData['payload']['payment']['entity'];

    // Connect to database
    $db = new Database();
    $conn = $db->getConnection();

    switch ($event) {
        case 'payment.captured':
            // Payment successful
            $orderId = $payload['order_id'];
            $paymentId = $payload['id'];
            $amount = $payload['amount'] / 100; // Convert from paise to rupees
            $method = $payload['method'];
            $status = PAYMENT_STATUS_SUCCESS;

            // Update payment status in database
            $stmt = $conn->prepare("UPDATE payments SET 
                payment_id = ?, 
                amount = ?, 
                status = ?, 
                payment_method = ?,
                updated_at = NOW() 
                WHERE order_id = ?");
            $stmt->bind_param("sdsss", $paymentId, $amount, $status, $method, $orderId);
            $stmt->execute();

            // Update booking status
            $stmt = $conn->prepare("UPDATE bookings b 
                JOIN payments p ON b.booking_id = p.booking_id 
                SET b.payment_status = ?, b.updated_at = NOW() 
                WHERE p.order_id = ?");
            $stmt->bind_param("ss", $status, $orderId);
            $stmt->execute();

            // Log successful payment
            error_log("Payment successful for order: $orderId, amount: $amount, method: $method");
            break;

        case 'payment.failed':
            // Payment failed
            $orderId = $payload['order_id'];
            $errorCode = $payload['error_code'];
            $errorDescription = $payload['error_description'];
            $status = PAYMENT_STATUS_FAILED;

            // Update payment status
            $stmt = $conn->prepare("UPDATE payments SET 
                status = ?, 
                error_code = ?,
                error_description = ?,
                updated_at = NOW() 
                WHERE order_id = ?");
            $stmt->bind_param("ssss", $status, $errorCode, $errorDescription, $orderId);
            $stmt->execute();

            // Update booking status
            $stmt = $conn->prepare("UPDATE bookings b 
                JOIN payments p ON b.booking_id = p.booking_id 
                SET b.payment_status = ?, b.updated_at = NOW() 
                WHERE p.order_id = ?");
            $stmt->bind_param("ss", $status, $orderId);
            $stmt->execute();

            // Log failed payment
            error_log("Payment failed for order: $orderId, error: $errorDescription");
            break;

        case 'refund.processed':
            // Refund processed
            $orderId = $payload['order_id'];
            $refundId = $payload['refund_id'];
            $refundAmount = $payload['refund_amount'] / 100;
            $status = PAYMENT_STATUS_REFUNDED;

            // Update payment status
            $stmt = $conn->prepare("UPDATE payments SET 
                status = ?, 
                refund_id = ?,
                refund_amount = ?,
                updated_at = NOW() 
                WHERE order_id = ?");
            $stmt->bind_param("ssds", $status, $refundId, $refundAmount, $orderId);
            $stmt->execute();

            // Update booking status
            $stmt = $conn->prepare("UPDATE bookings b 
                JOIN payments p ON b.booking_id = p.booking_id 
                SET b.payment_status = ?, b.updated_at = NOW() 
                WHERE p.order_id = ?");
            $stmt->bind_param("ss", $status, $orderId);
            $stmt->execute();

            // Log refund
            error_log("Refund processed for order: $orderId, amount: $refundAmount");
            break;
    }

    // Send 200 OK response
    http_response_code(200);
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    // Log error
    error_log('Webhook error: ' . $e->getMessage());
    
    // Send error response
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 