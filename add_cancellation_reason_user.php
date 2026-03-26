<?php
require_once 'db.php';

// Add cancellation_reason column if it doesn't exist
$sql = "SHOW COLUMNS FROM cancelled_bookings LIKE 'cancellation_reason'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $alter_table_sql = "ALTER TABLE cancelled_bookings ADD COLUMN cancellation_reason VARCHAR(255) DEFAULT NULL AFTER price";
    
    if ($conn->query($alter_table_sql) === TRUE) {
        echo "Successfully added cancellation_reason column to cancelled_bookings table.";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column cancellation_reason already exists.";
}

// Close connection
$conn->close();
?> 