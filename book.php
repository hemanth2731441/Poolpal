<?php
session_start();
require_once 'config.php';
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get input from either POST or GET
$trip_id = isset($_POST['trip_id']) ? (int)$_POST['trip_id'] : (isset($_GET['trip_id']) ? (int)$_GET['trip_id'] : 0);
$seats = isset($_POST['seats']) ? (int)$_POST['seats'] : (isset($_GET['seats']) ? (int)$_GET['seats'] : 0);
$requests = isset($_POST['requests']) ? trim($_POST['requests']) : '';
$user_id = $_SESSION['user_id'];

// Get user details
$user_query = "SELECT full_name, email, phone FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Basic validation
if ($trip_id <= 0 || $seats <= 0) {
    header('Location: Ridedetails.php?id=' . $trip_id . '&error=invalid_input');
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get trip details and lock the row
    $trip_stmt = $conn->prepare("
        SELECT t.*,
        (t.seats - COALESCE((
            SELECT SUM(seats_booked) 
            FROM bookings 
            WHERE trip_id = t.id 
            AND payment_status = 'completed'
        ), 0)) AS available_seats
        FROM trips t 
        WHERE t.id = ? 
        FOR UPDATE");
    $trip_stmt->bind_param("i", $trip_id);
    $trip_stmt->execute();
    $trip_result = $trip_stmt->get_result();
    
    if ($trip_result->num_rows === 0) {
        throw new Exception("Trip not found");
    }
    
    $trip = $trip_result->fetch_assoc();
    
    // Verify available seats directly from the calculated value
    if ($trip['available_seats'] < $seats) {
        throw new Exception("Not enough seats available");
    }
    
    // Calculate total amount
    $total_amount = $trip['price'] * $seats;
    
    // Create the booking with pending status
    $booking_stmt = $conn->prepare("INSERT INTO bookings (trip_id, user_id, user_name, user_email, driver_email, seats_booked, total_amount, special_requests, payment_status) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    
    $booking_stmt->bind_param("iisssiis", 
        $trip_id, 
        $user_id, 
        $user['full_name'], 
        $user['email'],
        $trip['driver_email'],
        $seats, 
        $total_amount, 
        $requests
    );
    
    if (!$booking_stmt->execute()) {
        throw new Exception("Failed to create booking: " . $conn->error);
    }
    
    $booking_id = $conn->insert_id;
    
    // Commit the transaction
    $conn->commit();
    
    // Store booking details in session
    $_SESSION['booking_details'] = [
        'booking_id' => $booking_id,
        'amount' => $total_amount,
        'trip_id' => $trip_id,
        'user_name' => $user['full_name'],
        'user_email' => $user['email'],
        'user_phone' => $user['phone'],
        'seats_booked' => $seats,
        'driver_email' => $trip['driver_email'],
        'departure_city' => $trip['departure_city'],
        'destination_city' => $trip['destination_city'],
        'departure_date' => $trip['departure_date'],
        'departure_time' => $trip['departure_time']
    ];
    
    // Redirect to payment processing with POST data
    ?>
    <form id="redirect_form" action="process_payment.php" method="POST">
        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
        <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">
    </form>
    <script>
        document.getElementById('redirect_form').submit();
    </script>
    <?php
    exit();
    
} catch (Exception $e) {
    // Rollback the transaction
    $conn->rollback();
    
    error_log("Booking Error: " . $e->getMessage());
    header('Location: Ridedetails.php?id=' . $trip_id . '&error=' . urlencode($e->getMessage()));
    exit();
}

$conn->close();
?>
