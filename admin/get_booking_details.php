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

$query = "SELECT 
    b.*,
    u.full_name as user_name,
    u.phone as user_phone,
    u.email as user_email,
    t.departure_city,
    t.destination_city,
    t.departure_date,
    t.departure_time,
    t.arrival_time,
    t.arrival_date,
    t.driver_name,
    t.driver_phone,
    t.vehicle_number,
    t.vehicle_type,
    p.payment_id,
    p.order_id,
    p.payment_method,
    p.created_at as payment_date
    FROM bookings b 
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN trips t ON b.trip_id = t.id
    LEFT JOIN payments p ON b.id = p.booking_id
    WHERE b.id = '$booking_id'";

$result = mysqli_query($conn, $query);

if ($result && $row = mysqli_fetch_assoc($result)) {
    ?>
    <div class="booking-details">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">User Information</h5>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($row['user_name'] ?? 'N/A'); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($row['user_email'] ?? 'N/A'); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['user_phone'] ?? 'N/A'); ?></p>
            </div>
            <div class="col-md-6">
                <h5 class="mb-3">Trip Information</h5>
                <p><strong>From:</strong> <?php echo htmlspecialchars($row['departure_city'] ?? 'N/A'); ?></p>
                <p><strong>To:</strong> <?php echo htmlspecialchars($row['destination_city'] ?? 'N/A'); ?></p>
                <p><strong>Date:</strong> <?php echo $row['departure_date'] ? date('d M Y', strtotime($row['departure_date'])) : 'N/A'; ?></p>
                <p><strong>Departure:</strong> <?php echo $row['departure_time'] ? date('h:i A', strtotime($row['departure_time'])) : 'N/A'; ?></p>
                <p><strong>Arrival:</strong> <?php 
                    if ($row['arrival_date'] && $row['arrival_time']) {
                        echo date('d M Y h:i A', strtotime($row['arrival_date'] . ' ' . $row['arrival_time']));
                    } else {
                        echo 'N/A';
                    }
                ?></p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">Driver & Vehicle Details</h5>
                <p><strong>Driver Name:</strong> <?php echo htmlspecialchars($row['driver_name'] ?? 'N/A'); ?></p>
                <p><strong>Driver Phone:</strong> <?php echo htmlspecialchars($row['driver_phone'] ?? 'N/A'); ?></p>
                <p><strong>Vehicle Number:</strong> <?php echo htmlspecialchars($row['vehicle_number'] ?? 'N/A'); ?></p>
                <p><strong>Vehicle Type:</strong> <?php echo htmlspecialchars($row['vehicle_type'] ?? 'N/A'); ?></p>
            </div>
            <div class="col-md-6">
                <h5 class="mb-3">Booking Details</h5>
                <p><strong>Booking ID:</strong> #<?php echo $row['id']; ?></p>
                <p><strong>Seats Booked:</strong> <?php echo $row['seats_booked'] ?? 0; ?></p>
                <p><strong>Total Amount:</strong> ₹<?php echo number_format($row['total_amount'] ?? 0, 2); ?></p>
                <p><strong>Booking Date:</strong> <?php echo $row['booking_time'] ? date('d M Y h:i A', strtotime($row['booking_time'])) : 'N/A'; ?></p>
                <?php if (!empty($row['special_requests'])): ?>
                <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($row['special_requests']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <h5 class="mb-3">Payment Information</h5>
                <p><strong>Payment Status:</strong> 
                    <span class="badge <?php 
                        echo $row['payment_status'] == 'completed' ? 'badge-success' : 
                            ($row['payment_status'] == 'pending' ? 'badge-warning' : 'badge-danger'); 
                    ?>">
                        <?php echo ucfirst($row['payment_status'] ?? 'N/A'); ?>
                    </span>
                </p>
                <?php if ($row['payment_status'] == 'completed'): ?>
                <p><strong>Payment ID:</strong> <?php echo htmlspecialchars($row['payment_id'] ?? 'N/A'); ?></p>
                <p><strong>Order ID:</strong> <?php echo htmlspecialchars($row['order_id'] ?? 'N/A'); ?></p>
                <p><strong>Payment Method:</strong> <?php echo ucfirst(htmlspecialchars($row['payment_method'] ?? 'N/A')); ?></p>
                <p><strong>Payment Date:</strong> <?php echo $row['payment_date'] ? date('d M Y h:i A', strtotime($row['payment_date'])) : 'N/A'; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
} else {
    echo '<div class="alert alert-danger">Booking details not found.</div>';
}
?> 