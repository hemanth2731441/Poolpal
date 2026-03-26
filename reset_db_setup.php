<?php
require_once 'config.php';

// Function to log errors
function logError($message) {
    echo $message . "<br>";
    error_log("[" . date("Y-m-d H:i:s") . "] " . $message . "\n", 3, "db_setup_error.log");
}

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.<br>";
    
    // Check if password_resets table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'password_resets'");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Table doesn't exist, create it
        $sql = "CREATE TABLE password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            otp VARCHAR(10) NOT NULL,
            token VARCHAR(100) NOT NULL,
            verified TINYINT(1) DEFAULT 0,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "Created password_resets table.<br>";
    } else {
        echo "password_resets table already exists.<br>";
    }
    
    // Check if driver_password_resets table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'driver_password_resets'");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Table doesn't exist, create it
        $sql = "CREATE TABLE driver_password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            otp VARCHAR(10) NOT NULL,
            token VARCHAR(100) NOT NULL,
            verified TINYINT(1) DEFAULT 0,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "Created driver_password_resets table.<br>";
    } else {
        echo "driver_password_resets table already exists.<br>";
    }
    
    echo "Database setup completed successfully.";
    
} catch (PDOException $e) {
    logError("Database error: " . $e->getMessage());
} catch (Exception $e) {
    logError("General error: " . $e->getMessage());
}
?>
