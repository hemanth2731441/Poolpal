<?php
include('vendor/inc/config.php');

// Check if booking ID is provided
if(isset($_POST['booking_id'])) {
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get booking details
        $query = "SELECT b.*, c.refund_status, c.refund_amount 
                 FROM bookings b 
                 JOIN cancellations c ON b.booking_id = c.booking_id 
                 WHERE b.booking_id = '$booking_id'";
        $result = mysqli_query($conn, $query);
        $booking = mysqli_fetch_assoc($result);
        
        if(!$booking) {
            throw new Exception("Booking not found");
        }
        
        if($booking['refund_status'] == 'processed') {
            throw new Exception("Refund already processed");
        }
        
        // Calculate refund amount (you may have your own business logic here)
        $refund_amount = $booking['amount']; // Full refund in this example
        
        // Update cancellation record
        $update_query = "UPDATE cancellations SET 
                        refund_status = 'processed',
                        refund_amount = '$refund_amount',
                        refund_date = NOW(),
                        processed_by = 'admin' 
                        WHERE booking_id = '$booking_id'";
        
        if(!mysqli_query($conn, $update_query)) {
            throw new Exception("Failed to update refund status");
        }
        
        // Here you would typically integrate with your payment gateway to process the actual refund
        // This is just a placeholder - replace with your actual payment gateway integration
        $refund_successful = processPaymentGatewayRefund($booking_id, $refund_amount);
        
        if(!$refund_successful) {
            throw new Exception("Failed to process refund through payment gateway");
        }
        
        // If everything is successful, commit the transaction
        mysqli_commit($conn);
        
        // Send success response
        $response = array(
            'status' => 'success',
            'message' => 'Refund processed successfully',
            'refund_amount' => $refund_amount
        );
        
    } catch (Exception $e) {
        // If there's an error, rollback the transaction
        mysqli_rollback($conn);
        
        $response = array(
            'status' => 'error',
            'message' => $e->getMessage()
        );
    }
    
} else {
    $response = array(
        'status' => 'error',
        'message' => 'Invalid request'
    );
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);

// Function to process refund through payment gateway
function processPaymentGatewayRefund($booking_id, $amount) {
    // This is a placeholder function
    // Replace with your actual payment gateway integration code
    
    // For demonstration purposes, we'll just return true
    // In a real application, you would:
    // 1. Connect to your payment gateway API
    // 2. Send the refund request
    // 3. Handle the response
    // 4. Return true if successful, false if failed
    
    return true;
}
?> 