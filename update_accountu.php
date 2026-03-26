<?php
include 'db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    if (isset($_POST['email']) && isset($_POST['phone'])) {
        $email = $_POST['email'];
        $phone = $_POST['phone'];  // now matches form field name

        $profilePicName = null;

        // Check if a new profile picture is uploaded
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
            $targetDir = "uploads/";
            $filename = basename($_FILES["profile_photo"]["name"]);
            $targetFile = $targetDir . time() . "_" . $filename;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            $allowedTypes = ['jpg', 'jpeg', 'png'];
            if (in_array($imageFileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $targetFile)) {
                    $profilePicName = $targetFile;
                }
            }
        }

        // Prepare query
        if ($profilePicName) {
            $sql = "UPDATE users SET email=?, phone=?, profile_photo=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssi', $email, $phone, $profilePicName, $user_id);
        } else {
            $sql = "UPDATE users SET email=?, phone=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssi', $email, $phone, $user_id);
        }

        // Execute update
        if ($stmt && $stmt->execute()) {
            // Fetch updated profile picture after update
            $sql = "SELECT profile_photo FROM users WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->bind_result($updatedProfilePic);
            $stmt->fetch();
            $_SESSION['profile_photo'] = $updatedProfilePic; // Update session with the new profile photo
        
            header("Location: edituser.php?success=1");
            exit;
        } else {
            error_log("Update error: " . $stmt->error);
            header("Location: edituser.php?success=0");
            exit;
        }        
    } else {
        header("Location: edituser.php?success=0");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>
