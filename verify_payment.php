<?php
session_start();
require_once 'includes/PaymentProcessor.php';
require_once 'config/payment_config.php';
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['booking_id'])) {
    header('Location: index.php');
    exit();
}

try {
    $bookingId = $_POST['booking_id'];
    $razorpayPaymentId = $_POST['razorpay_payment_id'] ?? null;
    $razorpayOrderId = $_POST['razorpay_order_id'] ?? null;
    $razorpaySignature = $_POST['razorpay_signature'] ?? null;

    if (!$razorpayPaymentId || !$razorpayOrderId || !$razorpaySignature) {
        throw new Exception("Invalid payment verification data");
    }

    // Initialize payment processor
    $processor = new PaymentProcessor();
    
    // Verify payment signature
    $isValid = $processor->verifyPayment($razorpayPaymentId, $razorpayOrderId, $razorpaySignature);
    
    if ($isValid) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get booking details
            $booking_query = $conn->prepare("SELECT * FROM bookings WHERE id = ? FOR UPDATE");
            $booking_query->bind_param("i", $bookingId);
            $booking_query->execute();
            $booking_result = $booking_query->get_result();
            $booking = $booking_result->fetch_assoc();
            
            if (!$booking) {
                throw new Exception("Booking not found");
            }
            
            // Get trip details
            $trip_query = $conn->prepare("SELECT * FROM trips WHERE id = ? FOR UPDATE");
            $trip_query->bind_param("i", $booking['trip_id']);
            $trip_query->execute();
            $trip_result = $trip_query->get_result();
            $trip = $trip_result->fetch_assoc();
            
            if (!$trip) {
                throw new Exception("Trip not found");
            }
            
            // Get current booked seats
            $booked_seats_query = $conn->prepare("
                SELECT COALESCE(SUM(seats_booked), 0) as total_booked 
                FROM bookings 
                WHERE trip_id = ? AND payment_status = 'completed'
            ");
            $booked_seats_query->bind_param("i", $booking['trip_id']);
            $booked_seats_query->execute();
            $booked_seats_result = $booked_seats_query->get_result();
            $booked_seats = $booked_seats_result->fetch_assoc()['total_booked'];
            
            // Verify seats are still available
            $available_seats = $trip['seats'] - $booked_seats;
            if ($available_seats < $booking['seats_booked']) {
                throw new Exception("Not enough seats available");
            }
            
            // Update booking status
            $stmt = $conn->prepare("UPDATE bookings SET 
                payment_status = 'completed',
                razorpay_payment_id = ?,
                razorpay_order_id = ?,
                razorpay_signature = ?,
                payment_date = CURRENT_TIMESTAMP
                WHERE id = ?");
            
            $stmt->bind_param("sssi", 
                $razorpayPaymentId,
                $razorpayOrderId,
                $razorpaySignature,
                $bookingId
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update booking status");
            }
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to success page
            header('Location: ' . PAYMENT_SUCCESS_URL . '?booking_id=' . $bookingId);
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            
            // Update booking status to failed
            $stmt = $conn->prepare("UPDATE bookings SET payment_status = 'failed' WHERE id = ?");
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            
            // Redirect to error page
            header('Location: ' . PAYMENT_ERROR_URL . '?error=' . urlencode($e->getMessage()));
            exit();
        }
    } else {
        // If payment verification fails, update booking status and redirect
        $stmt = $conn->prepare("UPDATE bookings SET payment_status = 'failed' WHERE id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        
        header('Location: ' . PAYMENT_ERROR_URL . '?error=payment_verification_failed');
        exit();
    }
    
} catch (Exception $e) {
    error_log("Payment verification error: " . $e->getMessage());
    
    // Update booking status as failed
    $stmt = $conn->prepare("UPDATE bookings SET 
        payment_status = 'failed',
        payment_date = CURRENT_TIMESTAMP
        WHERE id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    
    header('Location: ' . PAYMENT_FAILURE_URL . '?booking_id=' . $bookingId . '&error=' . urlencode($e->getMessage()));
    exit();
} 