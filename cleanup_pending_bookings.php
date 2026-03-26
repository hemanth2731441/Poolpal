<?php
require_once 'config.php';
include 'db.php';

try {
    // Start transaction
    $conn->begin_transaction();

    // Step 1: Prepare SELECT
    $expired_bookings_query = $conn->prepare("
        SELECT id, trip_id, seats_booked 
        FROM bookings 
        WHERE payment_status = 'pending' 
        AND created_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
    ");

    if (!$expired_bookings_query) {
        throw new Exception("SELECT prepare failed: " . $conn->error);
    }

    $expired_bookings_query->execute();
    $expired_bookings_result = $expired_bookings_query->get_result();

    // Step 2: Prepare UPDATE
    $update_status = $conn->prepare("
        UPDATE bookings 
        SET payment_status = 'expired' 
        WHERE id = ?
    ");

    if (!$update_status) {
        throw new Exception("UPDATE prepare failed: " . $conn->error);
    }

    // Loop through and update each expired booking
    while ($booking = $expired_bookings_result->fetch_assoc()) {
        $update_status->bind_param("i", $booking['id']);
        $update_status->execute();
    }

    // Commit transaction
    $conn->commit();

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    error_log("Cleanup Error at " . date('Y-m-d H:i:s') . ": " . $e->getMessage());
}

$conn->close();
?>
