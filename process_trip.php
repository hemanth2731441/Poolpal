<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $departure_city = trim($_POST['departure_city']);
    $destination_city = trim($_POST['destination_city']);
    $departure_date = trim($_POST['departure_date']);
    $departure_time = trim($_POST['departure_time']);
    $arrival_date = trim($_POST['arrival_date']);
    $arrival_time = trim($_POST['arrival_time']);
    $seats = (int)$_POST['seats'];
    $price = (float)$_POST['price'];
    $vehicle_type = trim($_POST['vehicle_type']);
    $allow_smoking = isset($_POST['allow_smoking']) ? 1 : 0;
    $pets_allowed = isset($_POST['pets_allowed']) ? 1 : 0;
    $luggage_space = trim($_POST['luggage_space']);
    $notes = trim($_POST['notes']);
    $has_ac = isset($_POST['has_ac']) ? 1 : 0;

    // Get driver details from session
    $driver_name = $_SESSION['driver_name'];
    $driver_email = $_SESSION['driver_email'];
    $driver_phone = $_SESSION['driver_phone'];
    $vehicle_number = $_SESSION['vehicle_number'];

    // Calculate duration and distance (you may want to use Google Maps API for accurate values)
    $duration = calculateDuration($departure_time, $arrival_time);
    $distance = '500'; // This should be calculated based on cities

    // Prepare SQL statement
    $sql = "INSERT INTO trips (departure_city, destination_city, departure_date, departure_time, 
            arrival_date, arrival_time, seats, price, vehicle_type, allow_smoking, pets_allowed, 
            luggage_space, notes, driver_name, driver_email, driver_phone, vehicle_number, 
            duration, distance, has_ac) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssidsiisssssssi", 
        $departure_city, $destination_city, $departure_date, $departure_time,
        $arrival_date, $arrival_time, $seats, $price, $vehicle_type, $allow_smoking,
        $pets_allowed, $luggage_space, $notes, $driver_name, $driver_email,
        $driver_phone, $vehicle_number, $duration, $distance, $has_ac
    );

    if ($stmt->execute()) {
        header("Location: dashboard.php?success=1");
        exit();
    } else {
        header("Location: create_trip.php?error=1");
        exit();
    }
}

function calculateDuration($departure_time, $arrival_time) {
    $departure = strtotime($departure_time);
    $arrival = strtotime($arrival_time);
    $diff = $arrival - $departure;
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    return $hours . "h " . $minutes . "m";
}
?> 