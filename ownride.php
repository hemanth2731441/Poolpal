<?php
ob_start();
include 'header.php';
include 'db.php';

// Include PHPMailer files
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php?error=login_required");
  exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $pickup = $_POST['pickup_location'];
  $destination = $_POST['destination'];
  $ride_date = $_POST['ride_date'];
  $ride_time = $_POST['ride_time'];
  $seats = $_POST['seats_needed'];
  $notes = $_POST['additional_notes'];
  
  // Get user info
  $user_query = "SELECT full_name, email FROM users WHERE id = ?";
  $user_stmt = $conn->prepare($user_query);
  $user_stmt->bind_param("i", $user_id);
  $user_stmt->execute();
  $user_result = $user_stmt->get_result();
  $user = $user_result->fetch_assoc();
  $user_name = $user['full_name'];
  
  // Insert the ride request
  $sql = "INSERT INTO ride_requests (user_id, pickup_location, destination, ride_date, ride_time, seats_needed, additional_notes) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("issssis", $user_id, $pickup, $destination, $ride_date, $ride_time, $seats, $notes);
  
  if ($stmt->execute()) {
    // Send email notifications to all drivers
    $drivers_sql = "SELECT email, Full_Name FROM drivers";
    $result = $conn->query($drivers_sql);
    
    if ($result->num_rows > 0) {
      $emailSuccess = true;
      $emailErrors = [];
      
      while($driver = $result->fetch_assoc()) {
        try {
          $mail = new PHPMailer(true);
          
          // Server settings
          $mail->SMTPDebug = 0;
          $mail->isSMTP();
          $mail->Host       = 'smtp.gmail.com';
          $mail->SMTPAuth   = true;
          $mail->Username   = defined('SMTP_USERNAME') ? SMTP_USERNAME : 'your_email@gmail.com';
          $mail->Password   = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : 'your_app_password';
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
          $mail->Port       = 587;
          $mail->CharSet    = 'UTF-8';
          
          // Recipients
          $mail->setFrom('info@poolpal.in', 'PoolPal');
          $mail->addAddress($driver['email'], $driver['Full_Name']);
          
          // Content
          $mail->isHTML(true);
          $mail->Subject = "New Ride Request";
          
          // HTML Body
          $mail->Body = "
          <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
            <h2 style='color: #ffbf00;'>New Ride Request Details</h2>
            <p>Dear <strong>{$driver['Full_Name']}</strong>,</p>
            <p>A new ride has been requested by <strong>{$user_name}</strong> with the following details:</p>
            
            <div style='background-color: #f8f8f8; padding: 15px; border-radius: 8px; margin: 20px 0;'>
              <h3 style='margin-top: 0; color: #333;'>Ride Details:</h3>
              <table border='0' cellpadding='5' cellspacing='0' style='width: 100%;'>
                <tr>
                  <td style='font-weight: bold;'>Pickup Location:</td>
                  <td>" . htmlspecialchars($pickup) . "</td>
                </tr>
                <tr>
                  <td style='font-weight: bold;'>Destination:</td>
                  <td>" . htmlspecialchars($destination) . "</td>
                </tr>
                <tr>
                  <td style='font-weight: bold;'>Date:</td>
                  <td>" . htmlspecialchars($ride_date) . "</td>
                </tr>
                <tr>
                  <td style='font-weight: bold;'>Time:</td>
                  <td>" . htmlspecialchars($ride_time) . "</td>
                </tr>
                <tr>
                  <td style='font-weight: bold;'>Seats Needed:</td>
                  <td>" . htmlspecialchars($seats) . "</td>
                </tr>
                <tr>
                  <td style='font-weight: bold;'>Additional Notes:</td>
                  <td>" . htmlspecialchars($notes) . "</td>
                </tr>
              </table>
            </div>
            
            <p>Please log in to your dashboard to accept this ride request.</p>
            <p>Best Regards,<br/>PoolPal Team</p>
          </div>";
          
          // Plain text alternative
          $mail->AltBody = "Dear {$driver['Full_Name']},\n\n" .
                          "A new ride has been requested by {$user_name} with the following details:\n\n" .
                          "Pickup Location: $pickup\n" .
                          "Destination: $destination\n" .
                          "Date: $ride_date\n" .
                          "Time: $ride_time\n" .
                          "Seats Needed: $seats\n" .
                          "Additional Notes: $notes\n\n" .
                          "Please log in to your dashboard to accept this ride request.\n\n" .
                          "Best Regards,\nPoolPal Team";
          
          $mail->send();
        } catch (Exception $e) {
          $emailSuccess = false;
          $emailErrors[] = "Email could not be sent to {$driver['email']}. Error: " . $mail->ErrorInfo;
        }
      }
      
      // Log email errors if any
      if (!$emailSuccess && !empty($emailErrors)) {
        error_log("Ride request email errors: " . implode(", ", $emailErrors));
      }
    }
    
    // Redirect to success page
    header("Location: ownride.php?success=1");
    exit;

  } else {
    echo "<script>
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'Failed to submit your ride request. Please try again.'
      });
    </script>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Request a Ride - PoolPal</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  
  <style>
    :root {
      --primary: #ffbf00;
      --primary-dark: #e6ac00;
      --primary-light: #fff6d9;
      --text-dark: #333;
      --text-medium: #666;
      --text-light: #888;
      --background: #fff;
      --surface: #f9f9f9;
      --border: #eaeaea;
      --shadow: rgba(0, 0, 0, 0.05);
      --shadow-darker: rgba(0, 0, 0, 0.1);
      --success: #4CAF50;
      --error: #f44336;
      --radius: 12px;
      --transition: all 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #fff 0%, var(--surface) 100%);
      min-height: 100vh;
      color: var(--text-dark);
      overflow-x: hidden;
    }

    .hero-section {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      padding: 60px 20px 40px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .hero-section::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
      animation: float 20s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(180deg); }
    }

    .hero-content {
      position: relative;
      z-index: 2;
      max-width: 600px;
      margin: 0 auto;
    }

    .hero-title {
      font-size: 42px;
      font-weight: 700;
      color: white;
      margin-bottom: 16px;
      text-shadow: 0 2px 10px rgba(0,0,0,0.1);
      animation: fadeInUp 1s ease-out;
    }

    .hero-subtitle {
      font-size: 18px;
      color: rgba(255,255,255,0.9);
      margin-bottom: 30px;
      animation: fadeInUp 1s ease-out 0.2s both;
    }

    .form-container {
      max-width: 600px;
      margin: -40px auto 60px;
      padding: 0 20px;
      position: relative;
      z-index: 3;
    }

    .main-form {
      background: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 20px 60px var(--shadow-darker);
      transform: translateY(0);
      transition: var(--transition);
      animation: slideInUp 0.8s ease-out 0.4s both;
    }

    .main-form:hover {
      transform: translateY(-5px);
      box-shadow: 0 30px 80px var(--shadow-darker);
    }

    .form-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .form-title {
      font-size: 28px;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 8px;
    }

    .form-description {
      color: var(--text-medium);
      font-size: 16px;
    }

    .form-grid {
      display: grid;
      gap: 24px;
    }

    .input-group {
      position: relative;
      animation: fadeInUp 0.6s ease-out both;
    }

    .input-group:nth-child(1) { animation-delay: 0.1s; }
    .input-group:nth-child(2) { animation-delay: 0.2s; }
    .input-group:nth-child(3) { animation-delay: 0.3s; }
    .input-group:nth-child(4) { animation-delay: 0.4s; }
    .input-group:nth-child(5) { animation-delay: 0.5s; }
    .input-group:nth-child(6) { animation-delay: 0.6s; }

    .input-label {
      display: block;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 8px;
      font-size: 14px;
      transition: var(--transition);
    }

    .input-wrapper {
      position: relative;
    }

    .input-field {
      width: 100%;
      padding: 16px 20px 16px 50px;
      border: 2px solid var(--border);
      border-radius: var(--radius);
      font-size: 16px;
      background: var(--background);
      transition: var(--transition);
      outline: none;
    }

    .input-field:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(255, 191, 0, 0.1);
      transform: translateY(-2px);
    }

    .input-field::placeholder {
      color: #bbb;
    }

    .input-icon {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 18px;
      color: var(--text-light);
      transition: var(--transition);
      z-index: 2;
    }

    .input-group:focus-within .input-icon {
      color: var(--primary);
      transform: translateY(-50%) scale(1.1);
    }

    .input-group:focus-within .input-label {
      color: var(--primary);
    }

    .textarea-field {
      width: 100%;
      padding: 16px 20px;
      border: 2px solid var(--border);
      border-radius: var(--radius);
      font-size: 16px;
      background: var(--background);
      transition: var(--transition);
      outline: none;
      resize: vertical;
      min-height: 120px;
      font-family: inherit;
    }

    .textarea-field:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(255, 191, 0, 0.1);
      transform: translateY(-2px);
    }

    .date-time-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .button-group {
      display: flex;
      gap: 16px;
      margin-top: 32px;
      justify-content: center;
    }

    .btn {
      padding: 16px 32px;
      border-radius: var(--radius);
      font-size: 16px;
      font-weight: 600;
      border: none;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      min-width: 160px;
      text-decoration: none;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
      box-shadow: 0 4px 15px rgba(255, 191, 0, 0.3);
    }

    .btn-primary:hover {
      background: var(--primary-dark);
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(255, 191, 0, 0.4);
    }

    .btn-secondary {
      background: var(--surface);
      color: var(--text-medium);
      border: 2px solid var(--border);
    }

    .btn-secondary:hover {
      background: #f0f0f0;
      transform: translateY(-2px);
      color: var(--text-dark);
    }

    .feature-section {
      padding: 80px 20px;
      background: white;
    }

    .features-container {
      max-width: 1200px;
      margin: 0 auto;
      text-align: center;
    }

    .features-title {
      font-size: 36px;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 16px;
    }

    .features-subtitle {
      font-size: 18px;
      color: var(--text-medium);
      margin-bottom: 60px;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 40px;
      margin-top: 60px;
    }

    .feature-card {
      background: var(--surface);
      border-radius: 20px;
      padding: 40px 30px;
      text-align: center;
      transition: var(--transition);
      border: 1px solid transparent;
      position: relative;
      overflow: hidden;
    }

    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 191, 0, 0.1), transparent);
      transition: left 0.6s ease;
    }

    .feature-card:hover::before {
      left: 100%;
    }

    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px var(--shadow-darker);
      border-color: var(--primary);
    }

    .feature-icon {
      width: 80px;
      height: 80px;
      background: var(--primary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 24px;
      font-size: 32px;
      color: white;
      transition: var(--transition);
    }

    .feature-card:hover .feature-icon {
      transform: scale(1.1) rotate(5deg);
    }

    .feature-title {
      font-size: 22px;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 16px;
    }

    .feature-description {
      color: var(--text-medium);
      line-height: 1.6;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .pac-container {
      border-radius: var(--radius);
      border: none;
      box-shadow: 0 8px 30px var(--shadow-darker);
      margin-top: 8px;
    }

    .pac-item {
      border-bottom: 1px solid var(--border);
      padding: 12px 16px;
      cursor: pointer;
      transition: var(--transition);
    }

    .pac-item:hover {
      background-color: var(--primary-light);
    }

    .pac-item-selected {
      background-color: var(--primary-light);
    }

    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.9);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      opacity: 0;
      visibility: hidden;
      transition: var(--transition);
    }

    .loading-overlay.show {
      opacity: 1;
      visibility: visible;
    }

    .loading-spinner {
      width: 50px;
      height: 50px;
      border: 4px solid var(--border);
      border-top: 4px solid var(--primary);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .hero-title {
        font-size: 32px;
      }
      
      .hero-subtitle {
        font-size: 16px;
      }

      .main-form {
        padding: 30px 24px;
        margin: -30px auto 40px;
      }

      .form-title {
        font-size: 24px;
      }

      .date-time-grid {
        grid-template-columns: 1fr;
      }

      .button-group {
        flex-direction: column;
      }

      .btn {
        width: 100%;
      }

      .features-grid {
        grid-template-columns: 1fr;
        gap: 30px;
      }

      .feature-card {
        padding: 30px 20px;
      }
    }

    @media (max-width: 480px) {
      .hero-section {
        padding: 40px 16px 30px;
      }

      .form-container {
        padding: 0 16px;
      }

      .main-form {
        padding: 24px 20px;
      }

      .input-field, .textarea-field {
        padding: 14px 16px 14px 44px;
        font-size: 15px;
      }

      .input-icon {
        left: 16px;
        font-size: 16px;
      }
    }
  </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
    <link rel="stylesheet" href="css/places-autocomplete.css">
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
  <div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
  </div>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="hero-content">
      <h1 class="hero-title">Request Your Perfect Ride</h1>
      <p class="hero-subtitle">Connect with trusted drivers in your area and travel comfortably at affordable prices</p>
    </div>
  </section>

  <!-- Main Form -->
  <div class="form-container">
    <form class="main-form" id="rideRequestForm" method="POST">
      <div class="form-header">
        <h2 class="form-title">Where are you going?</h2>
        <p class="form-description">Fill in your travel details and we'll connect you with available drivers</p>
      </div>

      <div class="form-grid">
        <div class="input-group">
          <label class="input-label">Pickup Location</label>
          <div class="input-wrapper">
            <i class="fas fa-map-marker-alt input-icon"></i>
            <input type="text" name="pickup_location" id="pickupLocation" class="input-field" 
                   placeholder="Enter your pickup address" required>
          </div>
        </div>

        <div class="input-group">
          <label class="input-label">Destination</label>
          <div class="input-wrapper">
            <i class="fas fa-flag-checkered input-icon"></i>
            <input type="text" name="destination" id="destination" class="input-field" 
                   placeholder="Where do you want to go?" required>
          </div>
        </div>

        <div class="date-time-grid">
          <div class="input-group">
            <label class="input-label">Travel Date</label>
            <div class="input-wrapper">
              <i class="fas fa-calendar-day input-icon"></i>
              <input type="date" name="ride_date" id="rideDate" class="input-field" required>
            </div>
          </div>

          <div class="input-group">
            <label class="input-label">Departure Time</label>
            <div class="input-wrapper">
              <i class="fas fa-clock input-icon"></i>
              <input type="time" name="ride_time" id="rideTime" class="input-field" required>
            </div>
          </div>
        </div>

        <div class="input-group">
          <label class="input-label">Seats Needed</label>
          <div class="input-wrapper">
            <i class="fas fa-users input-icon"></i>
            <input type="number" name="seats_needed" id="seatsNeeded" class="input-field" 
                   placeholder="How many seats do you need?" min="1" max="8" required>
          </div>
        </div>

        <div class="input-group">
          <label class="input-label">Additional Notes (Optional)</label>
          <textarea name="additional_notes" id="additionalNotes" class="textarea-field" 
                    placeholder="Any special requirements or preferences? (e.g., no smoking, pet-friendly, specific pickup time)"></textarea>
        </div>
      </div>

      <div class="button-group">
        <button type="submit" class="btn btn-primary" id="submitBtn">
          <i class="fas fa-paper-plane"></i>
          <span>Request Ride</span>
        </button>
        <a href="dashboard.php" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i>
          <span>Back to Dashboard</span>
        </a>
      </div>
    </form>
  </div>

  <!-- Features Section -->
  <section class="feature-section">
    <div class="features-container">
      <h2 class="features-title">Why Choose PoolPal?</h2>
      <p class="features-subtitle">Experience the future of shared transportation with our comprehensive ride-sharing platform</p>
      
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h3 class="feature-title">Safe & Secure</h3>
          <p class="feature-description">All drivers are verified with background checks. Your safety is our top priority with 24/7 support.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <h3 class="feature-title">Affordable Rates</h3>
          <p class="feature-description">Save up to 60% compared to traditional transport. Split fuel costs and travel smart.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-leaf"></i>
          </div>
          <h3 class="feature-title">Eco-Friendly</h3>
          <p class="feature-description">Reduce carbon footprint by sharing rides. Together, we're making transportation sustainable.</p>
        </div>
      </div>
    </div>
  </section>

  <script>
    // Function to initialize Modern Google Places Autocomplete
    function initAutocomplete() {
        try {
            console.log('Initializing Modern Google Places Autocomplete for Own Ride');

            // Create instance of ModernPlacesAutocomplete
            const placesUtil = new ModernPlacesAutocomplete();

            // Initialize and setup autocomplete
            placesUtil.createAutocomplete({
                fromInputId: 'pickup_location',
                toInputId: 'destination_location',
                fromLatId: 'pickup_lat',
                fromLngId: 'pickup_lng',
                toLatId: 'destination_lat',
                toLngId: 'destination_lng'
            }).catch(error => {
                console.error('Failed to initialize places autocomplete:', error);
            });

            console.log('Modern Places Autocomplete initialized successfully');
        } catch (error) {
            console.error('Error initializing Modern Places Autocomplete:', error);
        }
    }

    // Load the Google Maps script with error handling
    function loadGoogleMapsScript() {
        const script = document.createElement('script');
        const apiKey = '<?php echo GOOGLE_MAPS_API_KEY; ?>';
        
        // Use the recommended loading pattern with web components
        script.src = `https://maps.googleapis.com/maps/api/js`;
        script.async = true;
        
        // Add URL parameters after setting async
        const params = new URLSearchParams({
            key: apiKey,
            libraries: 'places,webcomponents',
            callback: 'initAutocomplete',
            loading: 'async',
            v: 'weekly'
        });
        script.src += '?' + params.toString();
        
        // Add error handling
        script.onerror = function(error) {
            console.error('Failed to load Google Maps API:', error);
            alert('Unable to load location services. Please try again later.');
            // Disable the location inputs and show a message
            const inputs = document.querySelectorAll('#pickup_location, #destination_location');
            inputs.forEach(input => {
                input.disabled = true;
                input.placeholder = 'Location services temporarily unavailable';
            });
        };
        
        document.body.appendChild(script);
    }

    // Initialize the loading of Google Maps
    document.addEventListener('DOMContentLoaded', loadGoogleMapsScript);

    // Set minimum date to today
    document.addEventListener('DOMContentLoaded', function() {
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('rideDate').min = today;
      document.getElementById('rideDate').value = today;

      // Add input animations
      const inputs = document.querySelectorAll('.input-field, .textarea-field');
      inputs.forEach(input => {
        input.addEventListener('focus', function() {
          this.parentElement.style.transform = 'translateY(-2px)';
        });

        input.addEventListener('blur', function() {
          this.parentElement.style.transform = 'translateY(0)';
        });
      });

      // Validate seats input
      document.getElementById('seatsNeeded').addEventListener('input', function() {
        if (this.value < 1) this.value = 1;
        if (this.value > 8) this.value = 8;
      });

      // Form submission with validation
      document.getElementById('rideRequestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const requiredFields = this.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
          if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = '#f44336';
            field.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
              field.style.borderColor = '';
              field.style.animation = '';
            }, 500);
          }
        });

        if (isValid) {
          // Show loading
          document.getElementById('loadingOverlay').classList.add('show');
          document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Submitting...</span>';
          
          // Submit form
          setTimeout(() => {
            this.submit();
          }, 1000);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: 'Please fill in all required fields.',
            confirmButtonColor: '#ffbf00'
          });
        }
      });
    });

    // Add shake animation
    const style = document.createElement('style');
    style.textContent = `
      @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
      }
    `;
    document.head.appendChild(style);

    // Show success message if redirected
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
      Swal.fire({
        icon: 'success',
        title: 'Request Submitted!',
        text: 'Your ride request has been sent to available drivers. You\'ll be notified when a driver accepts.',
        confirmButtonColor: '#ffbf00',
        showClass: {
          popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
          popup: 'animate__animated animate__fadeOutUp'
        }
      });
      // Remove query param
      window.history.replaceState({}, document.title, window.location.pathname);
    }
  </script>

  <!-- Include autocomplete styles and module -->
  <link rel="stylesheet" href="css/places-autocomplete.css">
  <!-- Load Google Maps API first -->
  <!-- <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places" async></script> -->
  <!-- Load our utility class after -->
  

  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

  <?php include 'footer.php'; ?>
</div></body>
</html>
<?php ob_end_flush(); ?>
