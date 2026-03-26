<?php
include 'db.php';

// Read the SQL file
$sql = file_get_contents('setup_bookings.sql');

// Execute the SQL
try {
    if ($conn->multi_query($sql)) {
        do {
            // Store first result set
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    }
    echo "Database setup completed successfully!";
} catch (Exception $e) {
    echo "Error setting up database: " . $e->getMessage();
}

$conn->close();
?> 