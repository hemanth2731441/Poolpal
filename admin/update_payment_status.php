<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    die('Unauthorized access');
}

include('../db.php');

if (!isset($_POST['booking_id']) || !isset($_POST['status'])) {
    die('Required parameters not provided');
}

$booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
$status = mysqli_real_escape_string($conn, $_POST['status']);

// Update booking payment status
$update_query = "UPDATE bookings SET payment_status = '$status' WHERE id = '$booking_id'";
$result = mysqli_query($conn, $update_query);

if ($result) {
    // If payment is marked as completed, update the payments table
    if ($status == 'completed') {
        $payment_query = "UPDATE payments SET 
            status = 'success',
            payment_method = 'manual',
            updated_at = NOW()
            WHERE booking_id = '$booking_id'";
        mysqli_query($conn, $payment_query);
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
}
?> 