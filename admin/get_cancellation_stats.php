<?php
include('vendor/inc/config.php');

// Get total cancellations
$total_query = "SELECT COUNT(*) as total FROM bookings WHERE status = 'cancelled'";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total = $total_row['total'];

// Get today's cancellations
$today_query = "SELECT COUNT(*) as today FROM bookings b 
                JOIN cancellations c ON b.booking_id = c.booking_id 
                WHERE DATE(c.cancelled_date) = CURDATE()";
$today_result = mysqli_query($conn, $today_query);
$today_row = mysqli_fetch_assoc($today_result);
$today = $today_row['today'];

// Get pending refunds
$refund_query = "SELECT COUNT(*) as pending FROM bookings b 
                 JOIN cancellations c ON b.booking_id = c.booking_id 
                 WHERE c.refund_status = 'pending'";
$refund_result = mysqli_query($conn, $refund_query);
$refund_row = mysqli_fetch_assoc($refund_result);
$pending_refunds = $refund_row['pending'];

// Calculate cancellation rate
$rate_query = "SELECT 
                (SELECT COUNT(*) FROM bookings WHERE status = 'cancelled') * 100.0 / 
                COUNT(*) as rate 
               FROM bookings";
$rate_result = mysqli_query($conn, $rate_query);
$rate_row = mysqli_fetch_assoc($rate_result);
$cancellation_rate = number_format($rate_row['rate'], 1);

// Prepare response
$response = array(
    'total' => $total,
    'today' => $today,
    'pending_refunds' => $pending_refunds,
    'cancellation_rate' => $cancellation_rate
);

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 