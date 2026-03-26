<?php
session_start();
include 'db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['full_name']) || !isset($_SESSION['email']) || !isset($_SESSION['phone'])) {
    echo "Driver details not available. Please log in.";
    exit;
}

$driver_name = $_SESSION['full_name'];
$driver_email = $_SESSION['email'];
$driver_phone = $_SESSION['phone'];

// Debug log for session variables
error_log("Driver Email: " . $driver_email);

// Get vehicle number and vehicle type
$vehicle_stmt = $conn->prepare("SELECT Vehicle_Number, vehicle_type FROM drivers WHERE Email = ?");
if (!$vehicle_stmt) {
    error_log("Prepare Error: " . $conn->error);
    die("Database error occurred");
}

$vehicle_stmt->bind_param("s", $driver_email);
if (!$vehicle_stmt->execute()) {
    error_log("Execute Error: " . $vehicle_stmt->error);
    die("Database error occurred");
}

$vehicle_stmt->bind_result($vehicle_number, $vehicle_type);
$vehicle_stmt->fetch();
$vehicle_stmt->close();

// Debug log for vehicle details
error_log("Vehicle Number: " . ($vehicle_number ?? 'null'));
error_log("Vehicle Type: " . ($vehicle_type ?? 'null'));

if (!$vehicle_number || !$vehicle_type) {
    echo "Vehicle details not found for this driver. Email: " . htmlspecialchars($driver_email);
    exit;
}

// Debug log for POST data
error_log("POST Data: " . print_r($_POST, true));

$departure_city = $_POST['departure_city'];
$destination_city = $_POST['destination_city'];
$departure_date = $_POST['departure_date'];
$departure_time = $_POST['departure_time'];
$arrival_date = $_POST['arrival_date'];
$arrival_time = $_POST['arrival_time'];
$duration = $_POST['duration'];
$distance = $_POST['distance'];
$seats = $_POST['seats'];
$price = $_POST['price'];
$allow_smoking = isset($_POST['allow_smoking']) ? 1 : 0;
$pets_allowed = isset($_POST['pets_allowed']) ? 1 : 0;
$has_ac = isset($_POST['has_ac']) ? 1 : 0;
$luggage_space = $_POST['luggage_space'];
$notes = $_POST['notes'];

// Debug log for processed data
error_log("Processed Data:");
error_log("Vehicle Type: $vehicle_type");
error_log("Departure City: $departure_city");
error_log("Destination City: $destination_city");
error_log("Vehicle Number: $vehicle_number");

try {
    // First, verify the vehicle type exists in the drivers table
    $check_stmt = $conn->prepare("SELECT vehicle_type FROM drivers WHERE Email = ? AND vehicle_type = ?");
    if (!$check_stmt) {
        throw new Exception("Check prepare failed: " . $conn->error);
    }
    
    $check_stmt->bind_param("ss", $driver_email, $vehicle_type);
    if (!$check_stmt->execute()) {
        throw new Exception("Check execute failed: " . $check_stmt->error);
    }
    
    $check_stmt->store_result();
    if ($check_stmt->num_rows == 0) {
        throw new Exception("Vehicle type mismatch or not found");
    }
    $check_stmt->close();

    $stmt = $conn->prepare("INSERT INTO trips (
        departure_city, destination_city, departure_date, departure_time, arrival_date, arrival_time,
        seats, price, vehicle_type, allow_smoking, pets_allowed, has_ac, luggage_space, notes,
        driver_name, driver_email, driver_phone, vehicle_number, duration, distance
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        throw new Exception("Insert prepare failed: " . $conn->error);
    }

    // Debug log the SQL and parameters
    error_log("About to execute INSERT with vehicle_type: " . $vehicle_type);

    $stmt->bind_param(
        "ssssssidsiiissssssss",
        $departure_city, $destination_city, $departure_date, $departure_time, $arrival_date, $arrival_time,
        $seats, $price, $vehicle_type, $allow_smoking, $pets_allowed, $has_ac, $luggage_space, $notes,
        $driver_name, $driver_email, $driver_phone, $vehicle_number, $duration, $distance
    );

    if (!$stmt->execute()) {
        throw new Exception("Insert execute failed: " . $stmt->error);
    }

    error_log("Insert successful. Last insert ID: " . $conn->insert_id);
    header("Location: dashboard.php?success=trip_created");
    exit();

} catch (Exception $e) {
    error_log("Trip creation error: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
    // Uncomment the line below for production
    // header("Location: create_trip.php?error=trip_creation_failed&message=" . urlencode($e->getMessage()));
    exit();
}
?>
