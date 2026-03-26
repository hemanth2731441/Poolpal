<?php
include 'db.php';

// Sample vehicle names
$vehicleNames = [
    'Toyota Camry',
    'Honda Accord',
    'Ford Fusion',
    'Chevrolet Malibu',
    'Nissan Altima',
    'Hyundai Sonata',
    'Kia Optima',
    'Volkswagen Passat',
    'Tesla Model 3',
    'Mazda 6'
];

// Update driver records where vehicle_name is NULL with random vehicle names
$result = $conn->query("SELECT id FROM drivers WHERE vehicle_name IS NULL OR vehicle_name = ''");

if ($result->num_rows > 0) {
    echo "Updating vehicle names for " . $result->num_rows . " drivers:<br>";
    
    while ($row = $result->fetch_assoc()) {
        $driverId = $row['id'];
        $randomVehicle = $vehicleNames[array_rand($vehicleNames)];
        
        $updateStmt = $conn->prepare("UPDATE drivers SET vehicle_name = ? WHERE id = ?");
        $updateStmt->bind_param("si", $randomVehicle, $driverId);
        $updateStmt->execute();
        
        echo "Driver ID " . $driverId . " updated with vehicle: " . $randomVehicle . "<br>";
        $updateStmt->close();
    }
    
    echo "<br>All missing vehicle names have been updated.";
} else {
    echo "All drivers already have vehicle names assigned.";
}

$conn->close();
?> 