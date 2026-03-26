<?php
// Basic security check - allow access from any legitimate page
if (basename($_SERVER['SCRIPT_FILENAME']) === 'config.php') {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access not permitted');
}

// Guard against multiple inclusions
if (defined('CONFIG_INCLUDED')) {
    return;
}
define('CONFIG_INCLUDED', true);

// Include database connection
require_once 'db.php';

// Define database constants from db.php values for compatibility
define('DB_HOST', $host);
define('DB_NAME', $dbname);
define('DB_USER', $user);
define('DB_PASS', $pass);

// Email configuration (SMTP)
define('SMTP_HOST', 'smtp.gmail.com');    // SMTP Host
define('SMTP_PORT', 587);                 // SMTP Port (587 for TLS)
define('SMTP_SECURE', 'tls');             // SMTP Security (tls, ssl, or '')
define('SMTP_AUTH', true);                // SMTP Authentication required

// IMPORTANT: Replace these with your actual email credentials
// For Gmail users: You will need to create an "App Password" if you have 2FA enabled
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password'); // Gmail App Password

// Sender Information
define('EMAIL_FROM', 'your_email@gmail.com');
define('EMAIL_FROM_NAME', 'PoolPal');

// Set to true to enable SMTP debugging (only during development)
define('SMTP_DEBUG', 0);

// Twilio configuration for WhatsApp
// These are placeholder values - if you want to use Twilio, replace with your actual credentials
// For now, we'll focus on fixing the email functionality
define('TWILIO_SID', 'your_twilio_sid'); 
define('TWILIO_TOKEN', 'your_twilio_token');
define('TWILIO_WHATSAPP_NUMBER', '+14155238886');

// Application configuration
define('APP_URL', 'http://localhost/Poolpal'); // URL to your application
define('APP_NAME', 'Pool Pal');

// Set the timezone
date_default_timezone_set('Asia/Kolkata'); // Set to Indian timezone 

// Database configuration
$servername = "localhost";
$username = "root"; 
$password = "";
$database = "poolpal";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Google Maps API Configuration
if (!defined('GOOGLE_MAPS_API_KEY')) {
    define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY');
}

// Verify API key is not empty
if (empty(GOOGLE_MAPS_API_KEY)) {
    error_log('Google Maps API key is not set properly');
}

define('GOOGLE_MAPS_COUNTRY_RESTRICTION', 'in'); // India
define('GOOGLE_MAPS_PLACE_TYPES', ['(cities)']); // Cities only

// Google Maps API Settings
$google_maps_config = [
    'api_key' => GOOGLE_MAPS_API_KEY,
    'libraries' => ['places'],
    'country_restriction' => GOOGLE_MAPS_COUNTRY_RESTRICTION,
    'place_types' => GOOGLE_MAPS_PLACE_TYPES,
    'fields' => ['formatted_address', 'geometry', 'name', 'place_id']
];

// Function to generate Google Maps API URL
if (!function_exists('getGoogleMapsApiUrl')) {
    function getGoogleMapsApiUrl($callback = 'initGoogleMapsCallback') {
        global $google_maps_config;
        $url = 'https://maps.googleapis.com/maps/api/js?';
        $params = [
            'key' => $google_maps_config['api_key'],
            'libraries' => implode(',', $google_maps_config['libraries']),
            'callback' => $callback
        ];
        return $url . http_build_query($params);
    }
}

// Function to get autocomplete options
if (!function_exists('getAutocompleteOptions')) {
    function getAutocompleteOptions() {
        global $google_maps_config;
        return [
            'types' => $google_maps_config['place_types'],
            'componentRestrictions' => ['country' => $google_maps_config['country_restriction']],
            'fields' => $google_maps_config['fields']
        ];
    }
}

// Include email functions
require_once 'email_functions.php';
?> 