<?php
// Include database connection
include 'db.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $fromLocation = isset($_POST['from_location']) ? mysqli_real_escape_string($conn, $_POST['from_location']) : '';
    $fromLat = isset($_POST['from_lat']) ? floatval($_POST['from_lat']) : 0;
    $fromLng = isset($_POST['from_lng']) ? floatval($_POST['from_lng']) : 0;
    
    $toLocation = isset($_POST['to_location']) ? mysqli_real_escape_string($conn, $_POST['to_location']) : '';
    $toLat = isset($_POST['to_lat']) ? floatval($_POST['to_lat']) : 0;
    $toLng = isset($_POST['to_lng']) ? floatval($_POST['to_lng']) : 0;
    
    $travelDate = isset($_POST['travel_date']) ? mysqli_real_escape_string($conn, $_POST['travel_date']) : '';
    
    // Basic validation for required fields
    if (empty($fromLocation) || empty($toLocation) || empty($travelDate)) {
        $response['message'] = 'Departure location, destination location, and travel date are required.';
    } 
    // Check if from and to locations are the same
    elseif ($fromLocation === $toLocation) {
        $response['message'] = 'Departure and destination locations cannot be the same.';
    } 
    else {
        // Check if travel_date column exists in the table
        $checkColumnQuery = "SHOW COLUMNS FROM ride_searches LIKE 'travel_date'";
        $columnResult = $conn->query($checkColumnQuery);
        $hasTravelDateColumn = $columnResult->num_rows > 0;
        
        if ($hasTravelDateColumn) {
            // Insert with travel_date column
            $query = "INSERT INTO ride_searches 
                     (from_location, from_lat, from_lng, to_location, to_lat, to_lng, travel_date) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sddsdds", 
                $fromLocation, 
                $fromLat, 
                $fromLng, 
                $toLocation, 
                $toLat, 
                $toLng, 
                $travelDate
            );
        } else {
            // Insert without travel_date column (using existing schema)
            $query = "INSERT INTO ride_searches 
                     (from_location, from_lat, from_lng, to_location, to_lat, to_lng) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sddsdd", 
                $fromLocation, 
                $fromLat, 
                $fromLng, 
                $toLocation, 
                $toLat, 
                $toLng
            );
        }
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Search saved successfully!';
            $response['redirect'] = 'result.php?from=' . urlencode($fromLocation) . '&to=' . urlencode($toLocation) . '&date=' . urlencode($travelDate);
        } else {
            $response['message'] = 'Error saving search: ' . $stmt->error;
        }
        
        $stmt->close();
    }
    
    // If it's an AJAX request, return JSON response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // For regular form submission, redirect or show message
    if ($response['success'] && !empty($response['redirect'])) {
        header('Location: ' . $response['redirect']);
        exit;
    } else {
        // Set error message in session and redirect back to form
        session_start();
        $_SESSION['error_message'] = $response['message'];
        header('Location: index.php');
        exit;
    }
} else {
    // Not a POST request, redirect to home page
    header('Location: index.php');
    exit;
}
?> 