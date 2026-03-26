<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    die('Unauthorized access');
}

include('../db.php');

if (!isset($_POST['booking_id'])) {
    die('Booking ID not provided');
}

$booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Get booking details
    $query = "SELECT b.*, t.departure_city, t.destination_city, t.departure_date, t.departure_time, 
            t.arrival_time, b.total_amount as price
            FROM bookings b
            LEFT JOIN trips t ON b.trip_id = t.id
            WHERE b.id = '$booking_id'";
    
    $result = mysqli_query($conn, $query);
    $booking = mysqli_fetch_assoc($result);

    if (!$booking) {
        throw new Exception("Booking not found");
    }

    // Insert into cancelled_bookings table
    $insert_query = "INSERT INTO cancelled_bookings 
        (user_email, trip_id, seats_booked, departure_city, destination_city, 
        departure_date, departure_time, arrival_time, price, cancellation_reason) 
        VALUES 
        ('" . mysqli_real_escape_string($conn, $booking['user_email']) . "',
        '" . mysqli_real_escape_string($conn, $booking['trip_id']) . "',
        '" . mysqli_real_escape_string($conn, $booking['seats_booked']) . "',
        '" . mysqli_real_escape_string($conn, $booking['departure_city']) . "',
        '" . mysqli_real_escape_string($conn, $booking['destination_city']) . "',
        '" . mysqli_real_escape_string($conn, $booking['departure_date']) . "',
        '" . mysqli_real_escape_string($conn, $booking['departure_time']) . "',
        '" . mysqli_real_escape_string($conn, $booking['arrival_time']) . "',
        '" . mysqli_real_escape_string($conn, $booking['price']) . "',
        'Cancelled by admin')";

    if (!mysqli_query($conn, $insert_query)) {
        throw new Exception("Failed to record cancellation");
    }

    // Update available seats in trips table
    $update_seats = "UPDATE trips SET seats = seats + " . $booking['seats_booked'] . " 
                    WHERE id = " . $booking['trip_id'];
    
    if (!mysqli_query($conn, $update_seats)) {
        throw new Exception("Failed to update trip seats");
    }

    // Delete the booking
    $delete_query = "DELETE FROM bookings WHERE id = '$booking_id'";
    if (!mysqli_query($conn, $delete_query)) {
        throw new Exception("Failed to delete booking");
    }

    // If everything is successful, commit the transaction
    mysqli_commit($conn);
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // If there's an error, rollback the transaction
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 