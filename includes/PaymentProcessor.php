<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/payment_config.php';
require_once __DIR__ . '/../db.php';

use Razorpay\Api\Api;

class PaymentProcessor {
    private $api;
    private $conn;
    
    public function __construct() {
        $this->api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
        global $conn;
        $this->conn = $conn;
    }
    
    public function createOrder($bookingId, $amount) {
        try {
            $orderData = [
                'receipt' => 'booking_' . $bookingId,
                'amount' => $amount * 100, // Convert to paise
                'currency' => RAZORPAY_CURRENCY,
                'notes' => [
                    'booking_id' => $bookingId,
                    'merchant_name' => MERCHANT_NAME,
                    'upi_id' => MERCHANT_UPI_ID
                ],
                'payment_capture' => 1
            ];
            
            $razorpayOrder = $this->api->order->create($orderData);
            $orderId = $razorpayOrder['id']; // Get order ID as string
            
            // Store order details in database
            $stmt = $this->conn->prepare("INSERT INTO payments (booking_id, order_id, amount, currency) VALUES (?, ?, ?, ?)");
            $currency = RAZORPAY_CURRENCY;
            $stmt->bind_param("isds", $bookingId, $orderId, $amount, $currency);
            $stmt->execute();
            
            // Update booking with Razorpay order ID
            $updateStmt = $this->conn->prepare("UPDATE bookings SET razorpay_order_id = ? WHERE id = ?");
            $updateStmt->bind_param("si", $orderId, $bookingId);
            $updateStmt->execute();
            
            return $razorpayOrder;
        } catch (Exception $e) {
            error_log("Razorpay Order Creation Error: " . $e->getMessage());
            throw new Exception("Failed to create payment order: " . $e->getMessage());
        }
    }
    
    public function verifyPayment($paymentId, $orderId, $signature) {
        try {
            $attributes = [
                'razorpay_payment_id' => $paymentId,
                'razorpay_order_id' => $orderId,
                'razorpay_signature' => $signature
            ];
            
            $this->api->utility->verifyPaymentSignature($attributes);
            
            // Get payment details
            $payment = $this->api->payment->fetch($paymentId);
            $paymentMethod = $payment->method;
            
            // Update payment status in payments table
            $stmt = $this->conn->prepare("UPDATE payments SET 
                payment_id = ?, 
                status = ?, 
                payment_method = ?,
                updated_at = CURRENT_TIMESTAMP 
                WHERE order_id = ?");
            $status = 'success';
            $stmt->bind_param("ssss", $paymentId, $status, $paymentMethod, $orderId);
            $stmt->execute();
            
            // Update booking status
            $updateStmt = $this->conn->prepare("UPDATE bookings SET 
                payment_status = 'completed',
                payment_method = ?,
                razorpay_payment_id = ?,
                razorpay_signature = ?,
                payment_date = CURRENT_TIMESTAMP
                WHERE razorpay_order_id = ?");
            $updateStmt->bind_param("ssss", $paymentMethod, $paymentId, $signature, $orderId);
            $updateStmt->execute();
            
            // Update trip seats
            $this->updateTripSeats($orderId);
            
            return true;
        } catch (Exception $e) {
            error_log("Razorpay Payment Verification Error: " . $e->getMessage());
            
            // Update payment status as failed
            $stmt = $this->conn->prepare("UPDATE payments SET 
                status = 'failed', 
                updated_at = CURRENT_TIMESTAMP 
                WHERE order_id = ?");
            $stmt->bind_param("s", $orderId);
            $stmt->execute();
            
            // Update booking status as failed
            $updateStmt = $this->conn->prepare("UPDATE bookings SET 
                payment_status = 'failed',
                payment_date = CURRENT_TIMESTAMP
                WHERE razorpay_order_id = ?");
            $updateStmt->bind_param("s", $orderId);
            $updateStmt->execute();
            
            return false;
        }
    }
    
    private function updateTripSeats($orderId) {
        // Get booking details
        $stmt = $this->conn->prepare("
            SELECT b.trip_id, b.seats_booked 
            FROM bookings b 
            WHERE b.razorpay_order_id = ? 
            AND b.payment_status = 'completed'
        ");
        $stmt->bind_param("s", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
        
        // We don't need to update the trips table seats directly
        // The available seats are calculated dynamically using the bookings table
    }
    
    public function getPaymentStatus($orderId) {
        $stmt = $this->conn->prepare("SELECT status FROM payments WHERE order_id = ?");
        $stmt->bind_param("s", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $payment = $result->fetch_assoc();
        return $payment ? $payment['status'] : null;
    }
    
    public function refundPayment($paymentId, $amount = null) {
        try {
            $refundData = ['payment_id' => $paymentId];
            if ($amount !== null) {
                $refundData['amount'] = $amount * 100; // Convert to paise
            }
            
            $refund = $this->api->refund->create($refundData);
            
            // Log refund details
            error_log("Refund processed: " . json_encode($refund));
            
            return $refund;
        } catch (Exception $e) {
            error_log("Refund failed: " . $e->getMessage());
            throw new Exception("Failed to process refund");
        }
    }
    
    public function getPaymentDetails($paymentId) {
        try {
            return $this->api->payment->fetch($paymentId);
        } catch (Exception $e) {
            error_log("Razorpay Payment Fetch Error: " . $e->getMessage());
            throw new Exception("Failed to fetch payment details");
        }
    }

    public function verifyWebhookSignature($payload, $signature) {
        try {
            // Verify webhook signature using Razorpay SDK
            $this->api->utility->verifyWebhookSignature(
                $payload,
                $signature,
                WEBHOOK_SECRET
            );
            return true;
        } catch (Exception $e) {
            error_log("Webhook signature verification failed: " . $e->getMessage());
            return false;
        }
    }

    public function processWebhookEvent($event, $payload) {
        switch ($event) {
            case 'payment.captured':
                return $this->handlePaymentSuccess($payload);
            case 'payment.failed':
                return $this->handlePaymentFailure($payload);
            case 'refund.processed':
                return $this->handleRefundProcessed($payload);
            default:
                error_log("Unhandled webhook event: $event");
                return false;
        }
    }

    private function handlePaymentSuccess($payload) {
        try {
            $orderId = $payload['order_id'];
            $paymentId = $payload['id'];
            $amount = $payload['amount'] / 100;
            $method = $payload['method'];
            
            // Update payment record
            $sql = "UPDATE payments SET 
                    payment_id = ?, 
                    amount = ?, 
                    status = ?, 
                    payment_method = ?,
                    updated_at = NOW() 
                    WHERE order_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $status = PAYMENT_STATUS_SUCCESS;
            $stmt->bind_param("sdsss", $paymentId, $amount, $status, $method, $orderId);
            $stmt->execute();
            
            // Update booking status
            $this->updateBookingStatus($orderId, PAYMENT_STATUS_SUCCESS);
            
            return true;
        } catch (Exception $e) {
            error_log("Error handling payment success: " . $e->getMessage());
            return false;
        }
    }

    private function handlePaymentFailure($payload) {
        try {
            $orderId = $payload['order_id'];
            $errorCode = $payload['error_code'];
            $errorDescription = $payload['error_description'];
            
            // Update payment record
            $sql = "UPDATE payments SET 
                    status = ?, 
                    error_code = ?,
                    error_description = ?,
                    updated_at = NOW() 
                    WHERE order_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $status = PAYMENT_STATUS_FAILED;
            $stmt->bind_param("ssss", $status, $errorCode, $errorDescription, $orderId);
            $stmt->execute();
            
            // Update booking status
            $this->updateBookingStatus($orderId, PAYMENT_STATUS_FAILED);
            
            return true;
        } catch (Exception $e) {
            error_log("Error handling payment failure: " . $e->getMessage());
            return false;
        }
    }

    private function handleRefundProcessed($payload) {
        try {
            $orderId = $payload['order_id'];
            $refundId = $payload['refund_id'];
            $refundAmount = $payload['refund_amount'] / 100;
            
            // Update payment record
            $sql = "UPDATE payments SET 
                    status = ?, 
                    refund_id = ?,
                    refund_amount = ?,
                    updated_at = NOW() 
                    WHERE order_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $status = PAYMENT_STATUS_REFUNDED;
            $stmt->bind_param("ssds", $status, $refundId, $refundAmount, $orderId);
            $stmt->execute();
            
            // Update booking status
            $this->updateBookingStatus($orderId, PAYMENT_STATUS_REFUNDED);
            
            return true;
        } catch (Exception $e) {
            error_log("Error handling refund: " . $e->getMessage());
            return false;
        }
    }

    private function updateBookingStatus($orderId, $status) {
        $sql = "UPDATE bookings b 
                JOIN payments p ON b.booking_id = p.booking_id 
                SET b.payment_status = ?, b.updated_at = NOW() 
                WHERE p.order_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $status, $orderId);
        $stmt->execute();
    }
} 