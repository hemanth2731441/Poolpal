<?php
include '../db.php';// Make sure this file connects to your MySQL DB

$name = "Admin User";
$email = "admin@example.com";
$password = password_hash("admin123", PASSWORD_DEFAULT); // Secure hash

$stmt = $conn->prepare("INSERT INTO admins (full_name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $password);

if ($stmt->execute()) {
    echo "Admin inserted successfully!";
} else {
    echo "Error: " . $stmt->error;
}
?>
