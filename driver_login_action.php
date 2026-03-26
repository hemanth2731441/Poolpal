<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Note: Using the correct case for column names as in the database
    $stmt = $conn->prepare("SELECT ID, Full_Name, Email, Contact, Profile_Pic, Password, verification_status, status FROM drivers WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // First verify the password
        if (password_verify($password, $user['Password'])) {
            // After password verification, check if user is verified/accepted by admin
            if (!isset($user['verification_status']) || $user['verification_status'] !== 'accepted') {
                header("Location: driver_login.php?error=not_verified");
                exit;
            }
            
            // After password verification, check if account is suspended
            if (isset($user['status']) && (int)$user['status'] === 0) {
                header("Location: driver_login.php?error=account_suspended");
                exit;
            }
            
            // Using correct case for session variables based on database column names
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['full_name'] = $user['Full_Name'];
            $_SESSION['email'] = $user['Email'];
            $_SESSION['phone'] = $user['Contact'];
            $_SESSION['profile_pic'] = $user['Profile_Pic'];
            $_SESSION['status'] = $user['status'];

            header("Location: driver_dasb.php");
            exit;
        } else {
            header("Location: driver_login.php?error=invalid_password");
            exit;
        }
    } else {
        header("Location: driver_login.php?error=user_not_found");
        exit;
    }
}
?>
