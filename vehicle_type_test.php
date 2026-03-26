<?php
// This is a test script to verify database connection and insertion for the vehicle_type issue

// Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include('db.php');

echo "<h2>Database Connection Test</h2>";

// Test database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "<p style='color:green'>Database connection successful!</p>";
}

// Check if the vehicle_type column exists
$checkColumnQuery = "SELECT COUNT(*) as count FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = 'ride_app' 
                    AND TABLE_NAME = 'drivers' 
                    AND COLUMN_NAME = 'vehicle_type'";
$result = $conn->query($checkColumnQuery);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    echo "<p style='color:red'>The vehicle_type column doesn't exist in the drivers table.</p>";
    
    // Add the column
    $addColumnQuery = "ALTER TABLE drivers ADD COLUMN vehicle_type VARCHAR(50) DEFAULT NULL AFTER vehicle_name";
    if ($conn->query($addColumnQuery) === TRUE) {
        echo "<p style='color:green'>Successfully added vehicle_type column to drivers table!</p>";
    } else {
        echo "<p style='color:red'>Error adding vehicle_type column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:green'>The vehicle_type column exists in the drivers table.</p>";
}

// Test data insertion
echo "<h2>Test Data Insertion</h2>";

$testQuery = "INSERT INTO drivers (Full_Name, Gender, Contact, alt_phone, Email, Address, 
              Driving_License, Profile_Pic, Aadhar, vehicle_name, vehicle_type, Vehicle_Number, 
              RC, Languages, Password, member_since, verification_status, status) 
              VALUES ('Test User', 'Male', '1234567890', '', 'test@example.com', 'Test Address', 
              'test_license', 'test_profile', 'test_aadhar', 'Test Car', 'Car', 'TEST123', 
              'test_rc', 'English', 'password123', NOW(), 'pending', 1)";

try {
    // First, check if test email already exists
    $checkEmail = "SELECT * FROM drivers WHERE Email = 'test@example.com'";
    $emailCheck = $conn->query($checkEmail);
    
    if ($emailCheck->num_rows > 0) {
        echo "<p>Test email already exists, skipping insertion test.</p>";
    } else {
        if ($conn->query($testQuery) === TRUE) {
            echo "<p style='color:green'>Test data inserted successfully with vehicle_type!</p>";
            
            // Verify the insertion
            $verifyQuery = "SELECT * FROM drivers WHERE Email = 'test@example.com'";
            $verifyResult = $conn->query($verifyQuery);
            
            if ($verifyResult->num_rows > 0) {
                $driver = $verifyResult->fetch_assoc();
                echo "<p>Verification: Vehicle type saved as: <strong>" . $driver['vehicle_type'] . "</strong></p>";
            }
        } else {
            echo "<p style='color:red'>Error inserting test data: " . $conn->error . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Exception during test: " . $e->getMessage() . "</p>";
}

// Show structure of the drivers table
echo "<h2>Drivers Table Structure</h2>";
$tableStructure = "DESCRIBE drivers";
$structureResult = $conn->query($tableStructure);

if ($structureResult) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while($column = $structureResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color:red'>Error getting table structure: " . $conn->error . "</p>";
}

echo "<p><a href='select_vehicle_type.php'>Go to Vehicle Type Selection</a></p>";
?>
