<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define security constants
define('INCLUDED_FROM_INDEX', true);
define('INCLUDED_FROM_SLIDERS', true);

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Include essential files in correct order
require_once 'db.php';      // Include db.php first
require_once 'config.php';  // Then include config.php which may use db.php variables

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?> 