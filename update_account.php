<?php
include 'db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    if (isset($_POST['email']) && isset($_POST['contact'])) {
        $email = $_POST['email'];
        $contact = $_POST['contact'];
        $address = $_POST['address'];
        $vehicle_name = $_POST['vehicle_name'];
        $vehicle_number = $_POST['vehicle_number'];
        $vehicle_type = $_POST['vehicle_type'];
        $vehicle_color = $_POST['vehicle_color'];

        // Check if a new profile picture is uploaded
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            // Set the target directory for uploads
            $targetDir = "uploads/";
            $filename = basename($_FILES["profile_pic"]["name"]);
            $targetFile = $targetDir . time() . "_" . $filename;  // Unique filename with timestamp
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            // Debugging: Check file upload error
            if ($_FILES['profile_pic']['error'] != 0) {
                echo "Error in file upload: " . $_FILES['profile_pic']['error'];
                exit;
            }

            // Allow only jpg, jpeg, png files
            $allowedTypes = ['jpg', 'jpeg', 'png'];
            if (!in_array($imageFileType, $allowedTypes)) {
                echo "Only JPG, JPEG, and PNG files are allowed.";
                exit;
            }

            // Attempt to move the uploaded file
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
                echo "File uploaded successfully: " . $targetFile;  // Debugging
                $_SESSION['profile_pic'] = $targetFile;
                // Save the new profile picture path in the database
                $profilePicName = $targetFile;  // Save full path (uploads/filename)

                // Update user details including profile picture
                $sql = "UPDATE drivers SET email=?, contact=?, address=?, vehicle_name=?, vehicle_number=?, vehicle_type=?, vehicle_color=?, profile_pic=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssssssssi', $email, $contact, $address, $vehicle_name, $vehicle_number, $vehicle_type, $vehicle_color, $profilePicName, $user_id);
            } else {
                echo "Error moving uploaded file.";
                exit;
            }
        } else {
            // If no new picture, update the other fields only
            echo "No new profile picture uploaded.";  // Debugging
            $sql = "UPDATE drivers SET email=?, contact=?, address=?, vehicle_name=?, vehicle_number=?, vehicle_type=?, vehicle_color=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssssssi', $email, $contact, $address, $vehicle_name, $vehicle_number, $vehicle_type, $vehicle_color, $user_id);
        }

        // Execute the query to update the profile
        if (isset($stmt) && $stmt->execute()) {
            // Success
            header("Location: driproedit.php?success=1");
            exit;
        } else {
            echo "Error updating record: " . $stmt->error;
        }
    } else {
        echo "Invalid form data.";
    }
} else {
    header("Location: driver_login.php");
    exit;
}
?>
