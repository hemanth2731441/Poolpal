<?php
include 'nav.php';
include 'db.php';

// Create cancelled_trips table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS cancelled_trips (
    id INT NOT NULL,
    driver_email VARCHAR(100) NOT NULL,
    departure_city VARCHAR(100) NOT NULL,
    destination_city VARCHAR(100) NOT NULL,
    departure_date DATE NOT NULL,
    departure_time TIME NOT NULL, 
    arrival_time TIME NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    seats INT DEFAULT NULL,
    cancellation_reason TEXT,
    cancelled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id, driver_email)
)";
$conn->query($create_table_sql);

$driver_email = $_SESSION['email']; // assuming driver is logged in

// Query for completed trips
$sql = "SELECT 
            t.id AS trip_id,
            t.departure_city,
            t.destination_city,
            t.departure_date,
            t.departure_time,
            t.arrival_time,
            t.price AS price_per_seat,
            t.seats,
            COUNT(b.id) AS total_seats_booked,
            SUM(b.seats_booked * t.price) AS total_trip_price,
            MAX(b.booking_time) AS latest_booking_time
        FROM trips t
        JOIN bookings b ON t.id = b.trip_id
        WHERE 
            t.driver_email = ? AND
            CONCAT(t.departure_date, ' ', t.arrival_time) <= NOW()
        GROUP BY t.id, t.departure_city, t.destination_city, t.departure_date, 
                t.departure_time, t.arrival_time, t.price, t.seats
        ORDER BY t.departure_date DESC, t.departure_time DESC
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $driver_email);
$stmt->execute();
$result = $stmt->get_result();

// Query for upcoming trips
$sql_upcoming = "SELECT 
                    id AS trip_id,
                    departure_city,
                    destination_city,
                    departure_date,
                    departure_time,
                    arrival_time,
                    price,
                    seats
                FROM trips 
                WHERE 
                    driver_email = ? AND
                    CONCAT(departure_date, ' ', departure_time) > NOW()
                ORDER BY departure_date ASC, departure_time ASC";

$stmt_upcoming = $conn->prepare($sql_upcoming);
$stmt_upcoming->bind_param("s", $driver_email);
$stmt_upcoming->execute();
$result_upcoming = $stmt_upcoming->get_result();

// Query for cancelled trips
$sql_cancelled = "SELECT 
                    id AS trip_id,
                    departure_city,
                    destination_city,
                    departure_date,
                    departure_time,
                    arrival_time,
                    price,
                    seats,
                    cancelled_at,
                    cancellation_reason
                FROM cancelled_trips 
                WHERE 
                    driver_email = ?
                ORDER BY cancelled_at DESC";

$stmt_cancelled = $conn->prepare($sql_cancelled);
$stmt_cancelled->bind_param("s", $driver_email);
$stmt_cancelled->execute();
$result_cancelled = $stmt_cancelled->get_result();

$sql_earnings = "SELECT SUM(b.seats_booked * t.price) AS total_earnings
                 FROM bookings b
                 JOIN trips t ON b.trip_id = t.id
                 WHERE 
                    t.driver_email = ? AND
                    CONCAT(t.departure_date, ' ', t.arrival_time) <= NOW()";

$stmt_earn = $conn->prepare($sql_earnings);
$stmt_earn->bind_param("s", $driver_email);
$stmt_earn->execute();
$result_earn = $stmt_earn->get_result();
$row_earn = $result_earn->fetch_assoc();
$total_earnings = $row_earn['total_earnings'] ?? 0;

$sql_completed = "SELECT COUNT(DISTINCT t.id) AS completed_trips
                  FROM bookings b
                  JOIN trips t ON b.trip_id = t.id
                  WHERE 
                      t.driver_email = ? AND
                      CONCAT(t.departure_date, ' ', t.arrival_time) <= NOW()";

$stmt_completed = $conn->prepare($sql_completed);
$stmt_completed->bind_param("s", $driver_email);
$stmt_completed->execute();
$result_completed = $stmt_completed->get_result();
$row_completed = $result_completed->fetch_assoc();
$completed_trips = $row_completed['completed_trips'] ?? 0;

// Function to check if a trip has bookings
function checkTripBookings($tripId, $conn) {
    $check_bookings = "SELECT COUNT(*) as booking_count FROM bookings WHERE trip_id = ?";
    $stmt_check = $conn->prepare($check_bookings);
    $stmt_check->bind_param("i", $tripId);
    $stmt_check->execute();
    $bookings_result = $stmt_check->get_result();
    return $bookings_result->fetch_assoc()['booking_count'];
}

// Add a new AJAX endpoint to check bookings
if(isset($_POST['check_bookings']) && isset($_POST['trip_id'])) {
    $trip_id = $_POST['trip_id'];
    $booking_count = checkTripBookings($trip_id, $conn);
    echo json_encode(['has_bookings' => $booking_count > 0]);
    exit;
}

// Modify the trip cancellation process
if(isset($_POST['cancel_trip']) && isset($_POST['trip_id']) && isset($_POST['cancellation_reason'])) {
    $trip_id = $_POST['trip_id'];
    $cancellation_reason = $_POST['cancellation_reason'];
    
    // Get the trip details first
    $get_trip = "SELECT * FROM trips WHERE id = ? AND driver_email = ?";
    $stmt_get = $conn->prepare($get_trip);
    $stmt_get->bind_param("is", $trip_id, $driver_email);
    $stmt_get->execute();
    $trip_result = $stmt_get->get_result();
    
    if($trip_data = $trip_result->fetch_assoc()) {
        // Insert into cancelled_trips table
        $insert_cancelled = "INSERT INTO cancelled_trips 
                            (id, driver_email, departure_city, destination_city, 
                            departure_date, departure_time, arrival_time, price, seats, cancellation_reason) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_cancelled);
        $stmt_insert->bind_param(
            "issssssdis", 
            $trip_data['id'], 
            $trip_data['driver_email'], 
            $trip_data['departure_city'], 
            $trip_data['destination_city'], 
            $trip_data['departure_date'], 
            $trip_data['departure_time'], 
            $trip_data['arrival_time'], 
            $trip_data['price'],
            $trip_data['seats'],
            $cancellation_reason
        );
        
        if($stmt_insert->execute()) {
            // Delete the trip only after successful insertion into cancelled_trips
            $delete_trip = "DELETE FROM trips WHERE id = ? AND driver_email = ?";
            $stmt_delete = $conn->prepare($delete_trip);
            $stmt_delete->bind_param("is", $trip_id, $driver_email);
            
            if($stmt_delete->execute()) {
                $cancel_message = "Trip canceled successfully!";
                echo "<script>
                    window.onload = function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Trip cancelled successfully.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href='mytrips.php';
                        });
                    };
                </script>";
            } else {
                $cancel_message = "Error deleting trip. Please try again.";
            }
        } else {
            $cancel_message = "Error recording cancellation. Please try again.";
        }
    } else {
        $cancel_message = "Trip not found.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Overview</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .mini {
      font-family: 'Inter', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #fff;
      color: #000;
    }

    .trip-overview-container {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .trip-overview-header {
      display: none;
    }

    .trip-overview-stats {
      display: flex;
      gap: 20px;
      margin-bottom: 40px;
      flex-wrap: wrap;
    }

    .trip-overview-card {
      background-color: rgb(255, 255, 255);
      border: none;
      border-radius: 16px;
      flex: 1;
      min-width: 260px;
      padding: 25px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
      position: relative;
      overflow: hidden;
    }
    
    .trip-overview-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, #ffbf00, rgba(153, 122, 28, 0.69));
      transition: height 0.3s ease;
    }
    
    .trip-overview-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    
    .trip-overview-card:hover::before {
      height: 8px;
    }

    .trip-image {
      margin-bottom: 12px;
      width: 95px; 
      height: 75px;
      object-fit: contain;
      animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }

    .trip-overview-card-title {
      font-size: 14px;
      margin-top: 10px;
      color: #777;
      transition: color 0.3s ease;
    }

    .trip-overview-card-value {
      font-size: 20px;
      font-weight: 700;
      margin-top: 4px;
      transition: color 0.3s ease;
      background: linear-gradient(45deg, #ffbf00, rgba(153, 122, 28, 0.85));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    .trip-overview-card-sub {
      font-size: 12px;
      color: #999;
    }

    .trip-overview-recent {
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 14px;
      position: relative;
      display: inline-block;
      padding-bottom: 5px;
    }
    
    .trip-overview-recent:after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, #ffbf00, rgba(153, 122, 28, 0.69));
      bottom: 0;
      left: 0;
      transition: width 0.3s ease;
    }
    
    .trip-overview-recent:hover:after {
      width: 100%;
    }

    .trip-overview-table {
      width: 100%;
      display: flex;
      flex-direction: column;
      overflow-x: auto;
      margin-bottom: 30px;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      background: white;
    }

    .trip-overview-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px;
      border-bottom: 1px solid #f0f0f0;
      min-width: 600px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .trip-overview-row::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      height: 100%;
      width: 0;
      background: linear-gradient(90deg, rgba(108, 92, 231, 0.1), transparent);
      transition: width 0.4s ease;
    }
    
    .trip-overview-row:hover {
      background-color: #f9f9f9;
      cursor: pointer;
      transform: translateX(5px);
    }
    
    .trip-overview-row:hover::before {
      width: 100%;
    }
    
    .trip-overview-row:first-child {
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
    }
    
    .trip-overview-row:last-child {
      border-bottom-left-radius: 12px;
      border-bottom-right-radius: 12px;
      border-bottom: none;
    }

    .trip-overview-col-date,
    .trip-overview-col-time,
    .trip-overview-col-route,
    .trip-overview-col-price,
    .trip-overview-col-price-total,
    .trip-overview-col-seats,
    .trip-overview-col-status,
    .trip-overview-col-arrow {
      font-size: 14px;
      color: #333;
      flex-basis: 12.5%;
      display: flex;
      align-items: center;
      transition: all 0.3s ease;
    }
    
    .trip-overview-col-route {
      font-weight: 500;
      color: #0066cc;
    }
    
    .trip-overview-col-price,
    .trip-overview-col-price-total {
      font-weight: 500;
    }
    
    .trip-overview-col-price small,
    .trip-overview-col-price-total small {
      opacity: 0.7;
      margin-left: 2px;
    }

    .trip-overview-col-date i {
      margin-right: 8px;
      font-size: 14px;
      color: #6c5ce7;
      transition: transform 0.3s ease;
    }
    
    .trip-overview-row:hover .trip-overview-col-date i {
      transform: scale(1.2);
    }

    .trip-overview-col-arrow {
      justify-content: flex-end;
      color: #999;
      font-size: 18px;
      transition: transform 0.3s ease;
    }
    
    .trip-overview-row:hover .trip-overview-col-arrow {
      transform: translateX(5px);
      color: #0066cc;
    }

    .trip-overview-col-status {
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .trip-overview-col-status.completed {
      color: #2e7d32;
      font-weight: 500;
    }
    
    .trip-overview-col-status.cancelled {
      color: #e53935;
    }
    
    .trip-overview-upcoming {
      margin-top: 40px;
    }
    
    .button-cancel {
      background: linear-gradient(45deg, #ff3a3a, #ff7676);
      color: white;
      border: none;
      border-radius: 6px;
      padding: 8px 14px;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 10px rgba(255, 58, 58, 0.2);
    }
    
    .button-cancel:hover {
      background: linear-gradient(45deg, #e60000, #ff5252);
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(255, 58, 58, 0.3);
    }
    
    .fade-in {
      animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .cancel-message {
      padding: 12px 16px;
      margin-bottom: 20px;
      border-radius: 8px;
      font-size: 14px;
      animation: fadeIn 0.5s ease-in;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    
    .cancel-message.error {
      background-color: #ffebee;
      color: #c62828;
      border-left: 4px solid #f44336;
    }
    
    .cancel-message.success {
      background-color: #e8f5e9;
      color: #2e7d32;
      border-left: 4px solid #4caf50;
    }
    
    .trip-tab-container {
      display: flex;
      margin-bottom: 25px;
      border-bottom: 1px solid #eaeaea;
      overflow-x: auto;
      white-space: nowrap;
      gap: 8px;
    }
    
    .trip-tab {
      padding: 12px 20px;
      cursor: pointer;
      font-weight: 600;
      position: relative;
      transition: all 0.3s ease;
      border-radius: 6px 6px 0 0;
    }
    
    .trip-tab.active {
      color:  #ffbf00;
    }
    
    .trip-tab.active:after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, #ffbf00,rgba(153, 122, 28, 0.69));
      animation: slideIn 0.3s ease-in;
    }
    
    .trip-tab::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 0;
      background: linear-gradient(to bottom, rgba(255, 191, 0, 0.1), transparent);
      transition: height 0.3s ease;
    }
    
    .trip-tab:hover::before {
      height: 100%;
    }
    
    .trip-tab.active::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, #ffbf00,rgba(153, 122, 28, 0.69));
      animation: tabSlideIn 0.4s ease-out;
    }
    
    @keyframes tabSlideIn {
      0% {
        width: 0;
        left: 50%;
        transform: translateX(-50%);
      }
      100% {
        width: 100%;
        left: 0;
        transform: translateX(0);
      }
    }
    
    .trip-content {
      display: none;
    }
    
    .trip-content.active {
      display: block;
      animation: fadeIn 0.5s ease-in;
    }
    
    .no-trips {
      text-align: center;
      padding: 40px 20px;
      color: #666;
      font-style: italic;
      background-color: rgba(0,0,0,0.02);
      border-radius: 12px;
      border: 1px dashed #ddd;
      animation: pulseEmptyState 2s infinite;
    }
    
    @keyframes pulseEmptyState {
      0% {
        box-shadow: 0 0 0 0 rgba(255, 191, 0, 0.1);
      }
      70% {
        box-shadow: 0 0 0 10px rgba(255, 191, 0, 0);
      }
      100% {
        box-shadow: 0 0 0 0 rgba(255, 191, 0, 0);
      }
    }

    /* Responsive */
    @media (max-width: 992px) {
      .trip-overview-row {
        padding: 14px 12px;
      }
      
      .trip-overview-col-date,
      .trip-overview-col-time,
      .trip-overview-col-route,
      .trip-overview-col-price,
      .trip-overview-col-price-total,
      .trip-overview-col-seats,
      .trip-overview-col-status,
      .trip-overview-col-arrow {
        font-size: 13px;
      }
    }
    
    @media (max-width: 768px) {
      .trip-overview-stats {
        flex-direction: column;
        gap: 16px;
      }

      .trip-overview-card {
        width: 100%;
      }

      .trip-overview-recent {
        font-size: 15px;
      }

      .trip-overview-row {
        font-size: 13px;
        flex-wrap: wrap;
        padding: 16px 12px;
      }
      
      .trip-tab {
        padding: 10px 15px;
        font-size: 14px;
      }
      
      .trip-overview-col-date,
      .trip-overview-col-time,
      .trip-overview-col-route,
      .trip-overview-col-price,
      .trip-overview-col-price-total,
      .trip-overview-col-seats,
      .trip-overview-col-status,
      .trip-overview-col-arrow {
        flex-basis: 25%;
        margin-bottom: 10px;
      }
      
      .trip-overview-col-arrow {
        display: none;
      }
    }

    @media (max-width: 576px) {
      .trip-overview-col-date,
      .trip-overview-col-time,
      .trip-overview-col-route,
      .trip-overview-col-price,
      .trip-overview-col-price-total,
      .trip-overview-col-seats,
      .trip-overview-col-status {
        flex-basis: 33.33%;
        font-size: 12px;
        margin-bottom: 12px;
      }
      
      .trip-overview-card-value {
        font-size: 18px;
      }

      .trip-overview-card-sub {
        font-size: 11px;
      }
      
      .trip-overview-container {
        padding: 0 10px;
        margin: 20px auto;
      }
      
      .trip-tab {
        padding: 8px 12px;
        font-size: 13px;
      }
      
      .button-cancel {
        padding: 6px 10px;
        font-size: 12px;
      }
    }
    
    @media (max-width: 480px) {
      .trip-overview-col-date,
      .trip-overview-col-route,
      .trip-overview-col-seats,
      .trip-overview-col-status {
        flex-basis: 50%;
      }
      
      .trip-overview-col-time,
      .trip-overview-col-price,
      .trip-overview-col-price-total {
        flex-basis: 33.33%;
      }
    }

    /* Enhanced Table Styling */
    .trip-overview-table {
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
      border-radius: 12px;
      overflow: hidden;
      transition: box-shadow 0.3s ease;
    }

    .trip-overview-table:hover {
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }

    .trip-overview-row {
      position: relative;
      overflow: hidden;
      transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      border-left: 3px solid transparent;
    }

    .trip-overview-row:hover {
      transform: translateX(5px);
      border-left: 3px solid #ffbf00;
      background-color: rgba(255, 191, 0, 0.05);
    }

    .trip-overview-row::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 1px;
      background: linear-gradient(to right, rgba(255, 191, 0, 0.2), transparent);
    }

    .trip-overview-row:last-child::after {
      display: none;
    }

    .trip-overview-row::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      height: 100%;
      width: 0;
      background: linear-gradient(90deg, rgba(255, 191, 0, 0.05), transparent);
      transition: width 0.5s ease;
      z-index: -1;
    }

    .trip-overview-row:hover::before {
      width: 100%;
    }

    .trip-overview-col-date i,
    .trip-overview-col-time i,
    .trip-overview-col-seats i,
    .trip-overview-col-price i {
      margin-right: 6px;
      font-size: 14px;
      color: #ffbf00;
      transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
    }

    .trip-overview-row:hover .trip-overview-col-date i,
    .trip-overview-row:hover .trip-overview-col-time i,
    .trip-overview-row:hover .trip-overview-col-seats i,
    .trip-overview-row:hover .trip-overview-col-price i {
      transform: scale(1.2);
    }

    .trip-overview-col-route {
      font-weight: 500;
      position: relative;
      transition: color 0.3s ease;
    }

    .trip-overview-col-route::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, #ffbf00, transparent);
      transition: width 0.3s ease;
    }

    .trip-overview-row:hover .trip-overview-col-route {
      color: #ffbf00;
    }

    .trip-overview-row:hover .trip-overview-col-route::after {
      width: 100%;
    }

    .trip-overview-col-arrow {
      transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .trip-overview-row:hover .trip-overview-col-arrow {
      transform: translateX(5px) scale(1.2);
      color: #ffbf00;
    }

    .button-cancel {
      background: linear-gradient(45deg, #ff3a3a, #ff7676);
      color: white;
      border: none;
      border-radius: 6px;
      padding: 8px 12px;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 10px rgba(255, 58, 58, 0.2);
    }

    .button-cancel:hover {
      background: linear-gradient(45deg, #e60000, #ff5252);
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(255, 58, 58, 0.3);
    }

    /* Mobile Responsive Styles for Upcoming Trips */
    @media (max-width: 768px) {
      #upcoming-trips .trip-overview-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 16px 12px;
        min-width: unset;
        width: 100%;
      }
      
      #upcoming-trips .trip-overview-col-date,
      #upcoming-trips .trip-overview-col-time,
      #upcoming-trips .trip-overview-col-route,
      #upcoming-trips .trip-overview-col-price,
      #upcoming-trips .trip-overview-col-price-total,
      #upcoming-trips .trip-overview-col-seats,
      #upcoming-trips .trip-overview-col-status,
      #upcoming-trips .trip-overview-col-arrow {
        width: 100%;
        flex-basis: 100%;
        margin-bottom: 8px;
        justify-content: flex-start;
      }
      
      #upcoming-trips .trip-overview-col-route {
        grid-column: 1 / span 2;
        font-weight: 600;
        font-size: 15px;
        order: -1;
        margin-bottom: 10px;
      }
      
      #upcoming-trips .trip-overview-col-status {
        grid-column: 1 / span 2;
        margin-top: 10px;
      }
      
      #upcoming-trips .trip-overview-col-arrow {
        display: none;
      }
      
      #upcoming-trips .trip-overview-table {
        overflow-x: hidden;
      }
    }

    @media (max-width: 576px) {
      #upcoming-trips .trip-overview-row {
        grid-template-columns: 1fr;
        padding: 16px;
      }
      
      #upcoming-trips .trip-overview-col-route,
      #upcoming-trips .trip-overview-col-status {
        grid-column: 1;
      }
      
      #upcoming-trips .button-cancel {
        width: 100%;
        padding: 10px;
      }
      
      .trip-overview-table {
        border-radius: 8px;
      }
      
      .trip-tab {
        padding: 8px 15px;
        font-size: 14px;
      }
    }

    .no-trips i.fas.fa-route,
    .no-trips i.fas.fa-check-circle {
      color: #ffbf00;
    }

    /* Trip Modal Styles */
    .trip-modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.5);
      opacity: 0;
      transition: opacity 0.3s ease;
      backdrop-filter: blur(5px);
      -webkit-backdrop-filter: blur(5px);
    }
    
    .trip-modal.show {
      display: block;
      opacity: 1;
    }
    
    .trip-modal-content {
      background-color: #fff;
      margin: 10% auto;
      width: 80%;
      max-width: 500px;
      border-radius: 12px;
      box-shadow: 0 5px 30px rgba(0,0,0,0.15);
      position: relative;
      transform: translateY(-50px);
      opacity: 0;
      transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
      overflow: hidden;
      border-top: 5px solid #ffbf00;
    }
    
    .trip-modal.show .trip-modal-content {
      transform: translateY(0);
      opacity: 1;
    }
    
    .trip-modal-header {
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #eee;
    }
    
    .trip-modal-header h2 {
      margin: 0;
      color: #333;
      font-size: 20px;
      font-weight: 600;
      background: linear-gradient(45deg, #ffbf00, rgba(153, 122, 28, 0.85));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }
    
    .trip-modal-close {
      font-size: 28px;
      font-weight: bold;
      color: #aaa;
      cursor: pointer;
      transition: color 0.3s ease;
      width: 30px;
      height: 30px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 50%;
    }
    
    .trip-modal-close:hover {
      color: #ffbf00;
      background-color: rgba(255, 191, 0, 0.1);
      transform: rotate(90deg);
    }
    
    .trip-modal-body {
      padding: 20px;
    }
    
    .trip-detail-row {
      display: flex;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #f5f5f5;
    }
    
    .trip-detail-row:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }
    
    .trip-detail-label {
      width: 120px;
      font-weight: 600;
      color: #666;
    }
    
    .trip-detail-value {
      flex: 1;
      color: #333;
    }
    
    /* Make trip rows clickable */
    .trip-overview-row {
      cursor: pointer;
    }
    
    /* Responsive modal */
    @media (max-width: 768px) {
      .trip-modal-content {
        width: 90%;
        margin: 20% auto;
      }
      
      .trip-detail-row {
        flex-direction: column;
      }
      
      .trip-detail-label {
        width: 100%;
        margin-bottom: 5px;
      }
    }

    /* Enhanced SweetAlert2 Custom Styles */
    .swal2-popup {
        font-family: 'Inter', sans-serif !important;
        border-radius: 16px !important;
        padding: 2em !important;
    }

    .swal2-title {
        color: #333 !important;
        font-size: 1.5rem !important;
        font-weight: 600 !important;
        padding: 1em 0 0.5em !important;
    }

    .swal2-html-container {
        margin: 0.5em 1.6em 0.3em !important;
    }

    .cancellation-reasons {
        margin: 1.5rem 0;
        text-align: left;
    }

    .reason-option {
        display: flex;
        align-items: center;
        margin: 0.8rem 0;
        padding: 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fff;
    }

    .reason-option:hover {
        border-color: #ffbf00;
        background-color: rgba(255, 191, 0, 0.05);
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .reason-option.selected {
        border-color: #ffbf00;
        background-color: rgba(255, 191, 0, 0.1);
        box-shadow: 0 4px 15px rgba(255, 191, 0, 0.1);
    }

    .reason-option input[type="radio"] {
        margin-right: 12px;
        accent-color: #ffbf00;
        width: 18px;
        height: 18px;
    }

    .reason-option label {
        flex: 1;
        cursor: pointer;
        font-size: 0.95rem;
        color: #444;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .reason-option label i {
        color: #ffbf00;
        font-size: 1.1rem;
    }

    .custom-reason-container {
        margin-top: 1rem;
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .custom-reason-container.show {
        display: block;
    }

    .custom-reason-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-family: 'Inter', sans-serif;
        font-size: 0.9rem;
        color: #333;
        transition: all 0.3s ease;
        resize: vertical;
        min-height: 80px;
        margin-top: 0.5rem;
    }

    .custom-reason-input:focus {
        outline: none;
        border-color: #ffbf00;
        box-shadow: 0 0 0 3px rgba(255, 191, 0, 0.1);
    }

    .custom-reason-input::placeholder {
        color: #999;
    }

    .reason-category {
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }

    .reason-category-title {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .reason-category-title i {
        color: #ffbf00;
    }

    .swal2-actions {
        margin-top: 1.5rem !important;
        gap: 12px !important;
    }

    .swal2-confirm {
        background: linear-gradient(45deg, #ffbf00, rgba(153, 122, 28, 0.85)) !important;
        border: none !important;
        box-shadow: 0 4px 15px rgba(255, 191, 0, 0.3) !important;
        padding: 12px 24px !important;
        font-size: 1rem !important;
    }

    .swal2-cancel {
        background: #f5f5f5 !important;
        color: #333 !important;
        border: 1px solid #ddd !important;
        padding: 12px 24px !important;
        font-size: 1rem !important;
    }

    .swal2-confirm:focus, .swal2-cancel:focus {
        box-shadow: none !important;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .swal2-popup {
            padding: 1.5em !important;
            margin: 1em;
        }
        
        .reason-option {
            padding: 0.8rem;
        }
        
        .reason-option label {
            font-size: 0.9rem;
        }
    }

    /* Cancellation Modal Styles */
    .cancellation-modal {
        font-family: 'Inter', sans-serif;
    }

    .reason-category {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .reason-category-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .reason-category-title i {
        color: #ffbf00;
    }

    .cancellation-reasons {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .reason-option {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
    }

    .reason-option:hover {
        border-color: #ffbf00;
        background: #fff9e6;
    }

    .reason-option.selected {
        border-color: #ffbf00;
        background: #fff9e6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .reason-option input[type="radio"] {
        display: none;
    }

    .reason-option label {
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        margin: 0;
        color: #4a5568;
    }

    .reason-option label i {
        color: #ffbf00;
        font-size: 1.1rem;
        width: 20px;
    }

    .reason-option:hover label {
        color: #2d3748;
    }

    #customReasonContainer {
        display: none;
        margin-top: 1rem;
    }

    #customReasonContainer.show {
        display: block;
    }

    #customReason {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        resize: vertical;
        min-height: 100px;
        font-family: inherit;
        transition: border-color 0.2s ease;
    }

    #customReason:focus {
        outline: none;
        border-color: #ffbf00;
    }

    /* SweetAlert2 Custom Styles */
    .swal2-popup {
        border-radius: 16px !important;
        padding: 2rem !important;
    }

    .swal2-title {
        color: #2c3e50 !important;
        font-size: 1.5rem !important;
    }

    .swal2-html-container {
        margin-top: 1rem !important;
    }

    .swal2-confirm {
        background-color: #ffbf00 !important;
        border-radius: 8px !important;
        padding: 0.75rem 1.5rem !important;
    }

    .swal2-cancel {
        background-color: #e2e8f0 !important;
        color: #4a5568 !important;
        border-radius: 8px !important;
        padding: 0.75rem 1.5rem !important;
    }
  </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
  <div class="mini">
    <div class="trip-overview-container fade-in">
      <?php if(isset($cancel_message)): ?>
        <div class="cancel-message <?php echo strpos($cancel_message, 'successfully') !== false ? 'success' : 'error'; ?>">
            <?php echo $cancel_message; ?>
        </div>
      <?php endif; ?>
      
      <div class="trip-overview-stats">
        <div class="trip-overview-card">
          <img src="images/icons/complete.gif" alt="Trip" class="trip-image">
          <div class="trip-overview-card-title">Completed</div>
          <div class="trip-overview-card-value">Trips</div>
          <div class="trip-overview-card-sub"><?php echo $completed_trips; ?></div>
        </div>
        <div class="trip-overview-card">
          <img src="images/icons/wallets.gif" alt="Trip" class="trip-image">
          <div class="trip-overview-card-title">Total Earned</div>
          <div class="trip-overview-card-value">₹<?php echo number_format($total_earnings, 2); ?></div>
          <div class="trip-overview-card-sub">Till Now</div>
        </div>
        <div class="trip-overview-card">
          <img src="images/icons/rating.gif" alt="Trip" class="trip-image">
          <div class="trip-overview-card-title">Rating</div>
          <div class="trip-overview-card-value">4.9 ★</div>
          <div class="trip-overview-card-sub">Based on 120 reviews</div>
        </div>
      </div>

      <div class="trip-tab-container">
        <div class="trip-tab active" onclick="showTab('upcoming')">Upcoming Trips</div>
        <div class="trip-tab" onclick="showTab('completed')">Completed Trips</div>
        <div class="trip-tab" onclick="showTab('cancelled')">Cancelled Trips</div>
      </div>
      
      <div class="trip-content active" id="upcoming-trips">
        <div class="trip-overview-recent">Upcoming Trips</div>
        <div class="trip-overview-table">
          <?php if($result_upcoming->num_rows > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result_upcoming)) { ?>
              <div class="trip-overview-row">
                <div class="trip-overview-col-date">
                  <i class="fas fa-car"></i>
                  <?php echo date("M j, Y", strtotime($row['departure_date'])); ?>
                </div>
                <div class="trip-overview-col-time"><?php echo date("g:i A", strtotime($row['departure_time'])); ?></div>
                <div class="trip-overview-col-route"><?php echo $row['departure_city'] . " → " . $row['destination_city']; ?></div>
                <div class="trip-overview-col-price">₹<?php echo number_format($row['price'], 2); ?> <small>/seat</small></div>
                <div class="trip-overview-col-price-total">-</div>
                <div class="trip-overview-col-seats"><?php echo $row['seats']; ?> seats</div>
                <div class="trip-overview-col-status">
                  <form method="post" onsubmit="return showCancellationDialog(<?php echo $row['trip_id']; ?>);">
                    <button type="submit" name="cancel_trip" class="button-cancel">Cancel</button>
                  </form>
                </div>
                <div class="trip-overview-col-arrow">&#x203A;</div>
              </div>
            <?php } ?>
          <?php else: ?>
            <div class="no-trips">
              <i class="fas fa-route" style="font-size: 24px; margin-bottom: 10px; color: #0066cc;"></i>
              <p>You don't have any upcoming trips. Get started by posting a new trip!</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="trip-content" id="completed-trips">
        <div class="trip-overview-recent">Completed Trips</div>
        <div class="trip-overview-table">
          <?php if($result->num_rows > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)) { ?>
              <div class="trip-overview-row">
                <div class="trip-overview-col-date">
                  <i class="fas fa-car"></i>
                  <?php echo date("M j, Y", strtotime($row['departure_date'])); ?>
                </div>
                <div class="trip-overview-col-time"><?php echo date("g:i A", strtotime($row['departure_time'])); ?></div>
                <div class="trip-overview-col-route"><?php echo $row['departure_city'] . " → " . $row['destination_city']; ?></div>
                <div class="trip-overview-col-price">₹<?php echo number_format($row['price_per_seat'], 2); ?> <small>/seat</small></div>
                <div class="trip-overview-col-price-total">₹<?php echo number_format($row['total_trip_price'], 2); ?> <small>total</small></div>
                <div class="trip-overview-col-seats"><?php echo $row['total_seats_booked']; ?> seats</div>
                <div class="trip-overview-col-status completed">Completed</div>
                <div class="trip-overview-col-arrow">&#x203A;</div>
              </div>
            <?php } ?>
          <?php else: ?>
            <div class="no-trips">
              <i class="fas fa-check-circle" style="font-size: 24px; margin-bottom: 10px; color: #4caf50;"></i>
              <p>You don't have any completed trips yet.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="trip-content" id="cancelled-trips">
        <div class="trip-overview-recent">Cancelled Trips</div>
        <div class="trip-overview-table">
          <?php if($result_cancelled->num_rows > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result_cancelled)) { ?>
              <div class="trip-overview-row">
                <div class="trip-overview-col-date">
                  <i class="fas fa-car"></i>
                  <?php echo date("M j, Y", strtotime($row['departure_date'])); ?>
                </div>
                <div class="trip-overview-col-time"><?php echo date("g:i A", strtotime($row['departure_time'])); ?></div>
                <div class="trip-overview-col-route"><?php echo $row['departure_city'] . " → " . $row['destination_city']; ?></div>
                <div class="trip-overview-col-price">₹<?php echo number_format($row['price'], 2); ?> <small>/seat</small></div>
                <div class="trip-overview-col-price-total">-</div>
                <div class="trip-overview-col-seats"><?php echo $row['seats'] ?? 'N/A'; ?> seats</div>
                <div class="trip-overview-col-status cancelled">
                    Cancelled
                    <?php if (!empty($row['cancellation_reason'])): ?>
                        <small class="cancellation-reason">(<?php echo htmlspecialchars($row['cancellation_reason']); ?>)</small>
                    <?php endif; ?>
                </div>
                <div class="trip-overview-col-arrow">&#x203A;</div>
              </div>
            <?php } ?>
          <?php else: ?>
            <div class="no-trips">
              <i class="fas fa-ban" style="font-size: 24px; margin-bottom: 10px; color: #e53935;"></i>
              <p>You don't have any cancelled trips.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  
  <div id="tripDetailsModal" class="trip-modal">
    <div class="trip-modal-content">
      <div class="trip-modal-header">
        <h2>Trip Details</h2>
        <span class="trip-modal-close">&times;</span>
      </div>
      <div class="trip-modal-body">
        <div class="trip-detail-row">
          <div class="trip-detail-label">Route:</div>
          <div id="tripDetailRoute" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row">
          <div class="trip-detail-label">Date:</div>
          <div id="tripDetailDate" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row">
          <div class="trip-detail-label">Departure Time:</div>
          <div id="tripDetailDepartureTime" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row">
          <div class="trip-detail-label">Arrival Time:</div>
          <div id="tripDetailArrivalTime" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row">
          <div class="trip-detail-label">Price per Seat:</div>
          <div id="tripDetailPrice" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row" id="tripDetailTotalRow">
          <div class="trip-detail-label">Total Price:</div>
          <div id="tripDetailTotalPrice" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row">
          <div class="trip-detail-label">Seats:</div>
          <div id="tripDetailSeats" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row">
          <div class="trip-detail-label">Status:</div>
          <div id="tripDetailStatus" class="trip-detail-value"></div>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    function showTab(tabName) {
      // Hide all tabs
      document.querySelectorAll('.trip-content').forEach(tab => {
        tab.classList.remove('active');
      });
      
      // Deactivate all tab buttons
      document.querySelectorAll('.trip-tab').forEach(button => {
        button.classList.remove('active');
      });
      
      // Show selected tab
      document.getElementById(tabName + '-trips').classList.add('active');
      
      // Activate selected tab button
      event.target.classList.add('active');
    }

    // Get modal elements
    const modal = document.getElementById('tripDetailsModal');
    const modalClose = document.querySelector('.trip-modal-close');
    
    // Close modal when clicking on X or outside the modal
    modalClose.addEventListener('click', closeModal);
    window.addEventListener('click', function(event) {
      if (event.target === modal) {
        closeModal();
      }
    });
    
    // Escape key to close modal
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeModal();
      }
    });
    
    // Add click event to all trip rows
    const tripRows = document.querySelectorAll('.trip-overview-row');
    tripRows.forEach(row => {
      row.addEventListener('click', function() {
        showTripDetails(this);
      });
    });
    
    // Function to show trip details
    function showTripDetails(row) {
      // Get trip data from the row
      const route = row.querySelector('.trip-overview-col-route').textContent.trim();
      const date = row.querySelector('.trip-overview-col-date').textContent.trim().replace(/^[\s\S]*?([A-Z][a-z]{2} \d{1,2}, \d{4})[\s\S]*$/, '$1');
      const time = row.querySelector('.trip-overview-col-time').textContent.trim();
      
      // Get arrival time - this may not be directly visible, so we can estimate or leave blank
      const arrivalTime = "Estimated"; // This can be replaced with actual data if available
      
      // Get price
      const priceElement = row.querySelector('.trip-overview-col-price');
      const price = priceElement ? priceElement.textContent.trim() : 'N/A';
      
      // Get total price if available
      const totalPriceElement = row.querySelector('.trip-overview-col-price-total');
      const totalPrice = totalPriceElement ? totalPriceElement.textContent.trim() : 'N/A';
      
      // Show/hide total price row based on availability
      document.getElementById('tripDetailTotalRow').style.display = 
        (totalPrice && totalPrice !== '-') ? 'flex' : 'none';
      
      // Get seats
      const seatsElement = row.querySelector('.trip-overview-col-seats');
      const seats = seatsElement ? seatsElement.textContent.trim() : 'N/A';
      
      // Get status
      const statusElement = row.querySelector('.trip-overview-col-status');
      const status = statusElement ? 
        (statusElement.querySelector('form') ? 'Active' : statusElement.textContent.trim()) : 'N/A';
      
      // Populate modal with trip details
      document.getElementById('tripDetailRoute').textContent = route;
      document.getElementById('tripDetailDate').textContent = date;
      document.getElementById('tripDetailDepartureTime').textContent = time;
      document.getElementById('tripDetailArrivalTime').textContent = arrivalTime;
      document.getElementById('tripDetailPrice').textContent = price;
      document.getElementById('tripDetailTotalPrice').textContent = totalPrice;
      document.getElementById('tripDetailSeats').textContent = seats;
      document.getElementById('tripDetailStatus').textContent = status;
      
      // Set status color based on status text
      const statusValueElement = document.getElementById('tripDetailStatus');
      statusValueElement.className = 'trip-detail-value';
      
      if (status.toLowerCase().includes('completed')) {
        statusValueElement.classList.add('completed');
        statusValueElement.style.color = '#2e7d32';
      } else if (status.toLowerCase().includes('cancelled')) {
        statusValueElement.classList.add('cancelled');
        statusValueElement.style.color = '#e53935';
      } else if (status.toLowerCase().includes('active')) {
        statusValueElement.style.color = '#ffbf00';
      }
      
      // Show the modal
      modal.classList.add('show');
      
      // Prevent event bubbling
      event.stopPropagation();
    }
    
    // Function to close the modal
    function closeModal() {
      modal.classList.remove('show');
      
      // Clear modal data after animation completes
      setTimeout(() => {
        document.getElementById('tripDetailRoute').textContent = '';
        document.getElementById('tripDetailDate').textContent = '';
        document.getElementById('tripDetailDepartureTime').textContent = '';
        document.getElementById('tripDetailArrivalTime').textContent = '';
        document.getElementById('tripDetailPrice').textContent = '';
        document.getElementById('tripDetailTotalPrice').textContent = '';
        document.getElementById('tripDetailSeats').textContent = '';
        document.getElementById('tripDetailStatus').textContent = '';
      }, 300);
    }

    // Function to check bookings before showing cancellation dialog
    async function checkBookingsAndCancel(tripId) {
        try {
            const formData = new FormData();
            formData.append('check_bookings', '1');
            formData.append('trip_id', tripId);
            
            const response = await fetch('mytrips.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.has_bookings) {
                // Show error message if trip has bookings
                Swal.fire({
                    icon: 'error',
                    title: 'Cannot Cancel Trip',
                    html: `
                        <div style="text-align: left; padding: 1rem;">
                            <p style="color: #666; margin-bottom: 1rem;">This trip cannot be cancelled because it has active bookings.</p>
                            <p style="color: #666; margin-bottom: 1rem;">To cancel this trip:</p>
                            <ul style="color: #666; margin-left: 1.5rem;">
                                <li style="margin-bottom: 0.5rem;">Wait for all bookings to be completed</li>
                                <li style="margin-bottom: 0.5rem;">Contact support if you need immediate assistance</li>
                            </ul>
                        </div>
                    `,
                    confirmButtonText: 'Understood',
                    customClass: {
                        confirmButton: 'swal2-confirm'
                    }
                });
            } else {
                // Show cancellation dialog if no bookings
                showCancellationDialog(tripId);
            }
        } catch (error) {
            console.error('Error checking bookings:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while checking trip status. Please try again.',
                confirmButtonText: 'OK'
            });
        }
    }

    // Update the form submission handler
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.trip-overview-row').forEach(row => {
            const cancelButton = row.querySelector('button[name="cancel_trip"]');
            if (cancelButton) {
                const form = cancelButton.closest('form');
                const tripId = form.querySelector('input[name="trip_id"]').value;
                form.onsubmit = (e) => {
                    e.preventDefault();
                    checkBookingsAndCancel(tripId);
                    return false;
                };
            }
        });
    });

    // Replace the existing showCancellationDialog function with this enhanced version
    function showCancellationDialog(tripId) {
        Swal.fire({
            title: 'Cancel Trip',
            html: `
                <div class="cancellation-modal">
                    <p style="color: #666; margin-bottom: 1.5rem;">Please select a reason for cancelling this trip:</p>
                    
                    <div class="reason-category">
                        <div class="reason-category-title">
                            <i class="fas fa-exclamation-circle"></i>
                            Common Reasons
                        </div>
                        <div class="cancellation-reasons">
                            <div class="reason-option">
                                <input type="radio" id="reason1" name="cancellation_reason" value="Travel plans changed">
                                <label for="reason1">
                                    <i class="fas fa-calendar-alt"></i>
                                    Travel plans changed
                                </label>
                            </div>
                            <div class="reason-option">
                                <input type="radio" id="reason2" name="cancellation_reason" value="Vehicle maintenance/breakdown">
                                <label for="reason2">
                                    <i class="fas fa-car-crash"></i>
                                    Vehicle maintenance/breakdown
                                </label>
                            </div>
                            <div class="reason-option">
                                <input type="radio" id="reason3" name="cancellation_reason" value="Emergency situation">
                                <label for="reason3">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Emergency situation
                                </label>
                            </div>
                            <div class="reason-option">
                                <input type="radio" id="reason4" name="cancellation_reason" value="Weather conditions">
                                <label for="reason4">
                                    <i class="fas fa-cloud-rain"></i>
                                    Weather conditions
                                </label>
                            </div>
                            <div class="reason-option">
                                <input type="radio" id="reason5" name="cancellation_reason" value="custom">
                                <label for="reason5">
                                    <i class="fas fa-pen"></i>
                                    Other reason
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div id="customReasonContainer">
                        <textarea id="customReason" 
                                  placeholder="Please provide details about your cancellation reason (minimum 10 characters)"
                                  maxlength="200"></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Confirm Cancellation',
            cancelButtonText: 'Keep Trip',
            reverseButtons: true,
            customClass: {
                container: 'cancellation-modal-container',
                popup: 'cancellation-modal-popup',
                title: 'cancellation-modal-title',
                htmlContainer: 'cancellation-modal-content',
                confirmButton: 'cancellation-modal-confirm',
                cancelButton: 'cancellation-modal-cancel'
            },
            didOpen: () => {
                // Add click handlers for reason options
                document.querySelectorAll('.reason-option').forEach(option => {
                    const radio = option.querySelector('input[type="radio"]');
                    option.addEventListener('click', () => {
                        // Remove selected class from all options
                        document.querySelectorAll('.reason-option').forEach(opt => 
                            opt.classList.remove('selected'));
                        // Add selected class to clicked option
                        option.classList.add('selected');
                        radio.checked = true;
                        
                        // Show/hide custom reason textarea
                        const customContainer = document.getElementById('customReasonContainer');
                        if (radio.value === 'custom') {
                            customContainer.classList.add('show');
                            document.getElementById('customReason').focus();
                        } else {
                            customContainer.classList.remove('show');
                        }
                    });
                });
            },
            preConfirm: () => {
                const selectedReason = document.querySelector('input[name="cancellation_reason"]:checked');
                if (!selectedReason) {
                    Swal.showValidationMessage('Please select a reason for cancellation');
                    return false;
                }
                
                if (selectedReason.value === 'custom') {
                    const customReason = document.getElementById('customReason').value.trim();
                    if (customReason.length < 10) {
                        Swal.showValidationMessage('Please provide a detailed reason (minimum 10 characters)');
                        return false;
                    }
                    return customReason;
                }
                
                return selectedReason.value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Cancelling Trip...',
                    html: 'Please wait while we process your cancellation.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Create and submit form
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="cancel_trip" value="1">
                    <input type="hidden" name="trip_id" value="${tripId}">
                    <input type="hidden" name="cancellation_reason" value="${result.value}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
        return false;
    }

    // Add this to your existing JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Highlight the selected reason option
        document.querySelectorAll('.reason-option input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.reason-option').forEach(option => {
                    option.classList.remove('selected');
                });
                this.closest('.reason-option').classList.add('selected');
            });
        });
    });
  </script>
  
  <?php include 'footer.php'; ?>
</div></body>
</html>