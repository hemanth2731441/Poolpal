<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    // Redirect to login with return URL
    header("Location: login.php?redirect=ride_view.php?id=" . $_POST['trip_id']);
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['trip_id']) || !isset($_POST['seats'])) {
    header("Location: dashboard.php");
    exit;
}

// Redirect to the main booking flow
header("Location: book.php?trip_id=" . $_POST['trip_id'] . "&seats=" . $_POST['seats']);
exit;
?> 