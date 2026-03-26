<?php
session_start();
include '../db.php'; // database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Admins table
  $stmt = $conn->prepare("SELECT id, full_name, password FROM admins WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();

    if (password_verify($password, $admin['password'])) {
      $_SESSION['admin_id'] = $admin['id'];
      $_SESSION['admin_name'] = $admin['full_name'];
      header("Location: admin_panel.php");
      exit;
    } else {
      header("Location: admin_login.php?error=Invalid Password");
      exit;
    }
  } else {
    header("Location: admin_login.php?error=Admin Not Found");
    exit;
  }
}
?>
