<?php
// Script to add social login fields to users table

include 'db.php';

echo "<h2>Updating Database for Social Login</h2>";

try {
    // Check if columns already exist
    $check = $conn->query("SHOW COLUMNS FROM users LIKE 'social_provider'");
    if ($check->num_rows > 0) {
        echo "<p>Social login fields already exist.</p>";
    } else {
        // Add the new columns
        $sql = "ALTER TABLE users 
                ADD COLUMN social_provider VARCHAR(20) NULL,
                ADD COLUMN social_id VARCHAR(100) NULL";
                
        if ($conn->query($sql) === TRUE) {
            echo "<p>Database updated successfully! Added social_provider and social_id columns to users table.</p>";
        } else {
            echo "<p>Error updating database: " . $conn->error . "</p>";
        }
    }
    
    echo "<p><a href='login.php'>Return to login page</a></p>";
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 