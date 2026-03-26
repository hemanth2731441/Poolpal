<?php
include 'db.php';

// Create remember_tokens table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Remember tokens table created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?> 