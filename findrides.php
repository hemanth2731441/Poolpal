<?php 
require_once 'init.php';
require_once 'header.php';

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Get current date and time
$currentDateTime = date('Y-m-d H:i:s');

$sql = "SELECT t.*, 
        (t.seats - COALESCE((
            SELECT SUM(seats_booked) 
            FROM bookings 
            WHERE trip_id = t.id 
            AND payment_status = 'completed'
        ), 0)) AS available_seats
        FROM trips t
        WHERE STR_TO_DATE(CONCAT(departure_date, ' ', departure_time), '%Y-%m-%d %H:%i:%s') > ?
        HAVING available_seats > 0
        ORDER BY departure_date ASC, departure_time ASC 
        LIMIT 4";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentDateTime);
$stmt->execute();
$recentResult = $stmt->get_result();

if (!$recentResult) {
  echo "Query Error: " . mysqli_error($conn);
}

$googleMapsApiKey = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : 'YOUR_GOOGLE_MAPS_API_KEY';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Find Your Ride | PoolPal</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" />
  <style>
    :root {
      --primary: #e9b424;
      --primary-dark: #d9a11f;
      --secondary: #7bc19c;
      --accent: #ffb02e;
      --dark: #2c3e50;
      --light: #f7f9fa;
      --danger: #e74c3c;
      --gray: #95a5a6;
      --gray-light: #f5f7fa;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
    }
    
    body {
      background-color: var(--light);
      color: var(--dark);
      line-height: 1.6;
    }
    
    .container {
      max-width: 1300px;
      margin: 0 auto;
      padding: 20px;
    }
    
    .page-header {
      text-align: center;
      margin-bottom: 30px;
      padding-top: 40px;
    }
    
    .page-header h1 {
      font-size: 36px;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 15px;
      position: relative;
      display: inline-block;
    }
    
    .page-header h1::after {
      content: '';
      position: absolute;
      width: 80px;
      height: 4px;
      background: var(--primary);
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      border-radius: 2px;
    }
    
    .page-header p {
      font-size: 18px;
      color: var(--gray);
      max-width: 600px;
      margin: 0 auto;
      margin-top: 20px;
    }
    
    /* Search Section */
    .search-section {
      background: white;
      border-radius: 24px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      padding: 25px;
      margin-bottom: 30px;
    }
    
    .search-form {
      display: flex;
      gap: 20px;
      position: relative;
      max-width: 1200px;
      margin: 0 auto;
      align-items: center;
    }
    
    .location-inputs-container {
      display: flex;
      align-items: center;
      gap: 15px;
      position: relative;
      background: transparent;
      border-radius: 20px;
      padding: 10px;
      flex: 1;
    }
    
    .search-inputs-wrapper {
      display: flex;
      align-items: center;
      gap: 20px;
      width: 100%;
    }

    .locations-wrapper {
      display: flex;
      align-items: center;
      gap: 15px;
      flex: 1;
    }

    .form-group {
      position: relative;
      flex: 1;
      min-width: 0;
    }

    .form-group input {
      width: 100%;
      padding: 18px 18px 18px 50px;
      border: 1px solid #e0e0e0;
      border-radius: 15px;
      background-color: #fff;
      font-size: 16px;
      color: var(--dark);
      transition: all 0.3s ease;
    }
    
    .form-group input:hover {
      border-color: var(--primary);
    }
    
    .form-group input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .form-group i {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: #666;
      font-size: 18px;
      opacity: 0.8;
      transition: all 0.3s ease;
    }
    
    .form-group input:focus + i {
      opacity: 1;
      color: var(--primary);
    }
    
    .form-group.date-group {
      max-width: 200px;
    }
    
    .switch-btn-container {
      margin: 0 -10px;
      flex-shrink: 0;
      position: relative;
      z-index: 2;
    }
    
    .switch-locations-btn {
      width: 44px;
      height: 44px;
      background: white;
      border: 1px solid #e0e0e0;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .switch-locations-btn:hover {
      transform: rotate(180deg);
      background: var(--primary);
      border-color: var(--primary);
    }
    
    .switch-locations-btn:hover i {
      color: white;
    }
    
    .switch-locations-btn i {
      font-size: 18px;
      color: #666;
      transition: all 0.3s ease;
    }
    
    .form-btn {
      padding: 18px 36px;
      border-radius: 15px;
      font-size: 16px;
      font-weight: 600;
      background: var(--primary);
      color: white;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      min-width: 140px;
      white-space: nowrap;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    
    .form-btn:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
    }
    
    /* Recent Rides Section */
    .section-title {
      font-size: 24px;
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 20px;
      position: relative;
      padding-left: 15px;
      display: flex;
      align-items: center;
    }
    
    .section-title::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 5px;
      height: 25px;
      background: var(--primary);
      border-radius: 3px;
    }
    
    .rides-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }
    
    .ride-card {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: var(--shadow);
      transition: var(--transition);
      position: relative;
    }
    
    .ride-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
    
    .ride-card:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 5px;
      height: 100%;
      background: var(--primary);
    }
    
    .ride-card__header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px;
      background: var(--gray-light);
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .ride-route {
      font-weight: 600;
      font-size: 16px;
      color: var(--dark);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .ride-route i {
      color: var(--primary);
    }
    
    .ride-price {
      background: var(--secondary);
      color: white;
      font-weight: 700;
      padding: 5px 12px;
      border-radius: 25px;
      font-size: 14px;
    }
    
    .ride-card__body {
      padding: 20px;
    }
    
    .ride-details {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 20px;
    }
    
    .ride-detail {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .ride-detail i {
      color: var(--gray);
      font-size: 14px;
    }
    
    .ride-detail span {
      font-size: 14px;
      color: var(--dark);
    }
    
    .ride-card__footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px 20px;
    }
    
    .seats-left {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--secondary);
      font-weight: 600;
      font-size: 14px;
    }
    
    .book-btn {
      background: var(--accent);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 10px 20px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
    }
    
    .book-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(255, 176, 46, 0.3);
    }
    
    /* Alert styles */
    .alert {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 15px 20px;
      border-radius: 8px;
      background: white;
      color: var(--dark);
      box-shadow: var(--shadow);
      z-index: 1000;
      transform: translateX(200%);
      transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .alert.show {
      transform: translateX(0);
    }
    
    .alert i {
      font-size: 20px;
    }
    
    .alert.error {
      background: var(--danger);
      color: white;
    }
    
    .alert.error i {
      color: white;
    }
    
    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .fade-in {
      animation: fadeIn 0.6s ease forwards;
    }
    
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
    
    .pulse {
      animation: pulse 2s infinite;
    }
    
    /* Responsiveness */
    @media (min-width: 993px) {
      .search-section {
        padding: 25px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        margin: 0 auto;
        max-width: 1200px;
      }

      .location-inputs-container {
        padding: 8px 15px;
      }

      .form-group i {
        color: var(--primary);
        opacity: 0.8;
      }

      .form-group input {
        background: rgba(255, 255, 255, 0.9);
      }

      .form-group input:focus {
        background: white;
        transform: translateY(-1px);
      }

      .switch-btn-container {
        margin: 0;
        flex-shrink: 0;
        position: relative;
        z-index: 2;
      }

      .switch-locations-btn {
        width: 36px;
        height: 36px;
        margin: 0 -5px;
      }
    }

    /* Mobile-specific styles */
    @media (max-width: 992px) {
      .search-section {
        padding: 20px;
        margin: 15px;
        border-radius: 16px;
        background: #fff;
      }

      .search-form {
        flex-direction: column;
        gap: 15px;
        width: 100%;
      }

      .location-inputs-container {
        flex-direction: column;
        gap: 15px;
        width: 100%;
        padding: 0;
      }

      .form-group {
        width: 100%;
      }

      .form-group input {
        width: 100%;
        padding: 15px 15px 15px 45px;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        background: #fff;
        font-size: 16px;
      }

      .form-group i {
        left: 15px;
        color: #757575;
      }

      .switch-btn-container {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        margin: 0;
        z-index: 3;
      }

      .switch-locations-btn {
        width: 36px;
        height: 36px;
        background: #fff;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }

      .form-group.date-group {
        max-width: 100%;
        margin: 0;
      }

      .form-btn {
        width: 100%;
        padding: 15px;
        border-radius: 12px;
        margin: 0;
        background: #e9b424;
      }

      /* Hide the original date input styling */
      input[type="date"] {
        position: relative;
        background: transparent;
      }

      input[type="date"]::-webkit-calendar-picker-indicator {
        background: transparent;
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
      }

      /* Container adjustments */
      .container {
        padding: 10px;
      }

      /* Adjust header spacing */
      .page-header {
        padding-top: 20px;
        margin-bottom: 20px;
      }
    }

    @media (max-width: 576px) {
      .search-section {
        margin: 10px;
        padding: 15px;
      }

      .form-group input {
        font-size: 14px;
      }

      .form-btn {
        font-size: 15px;
      }
    }
  </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
<?php include_once 'header.php'; ?>

<div class="container">
  <header class="page-header" data-aos="fade-up">
    <h1>Find Your Perfect Ride</h1>
    <p>Search, compare, and book shared rides with trusted drivers in your area</p>
  </header>

  <section id="find-ride" class="search-section" data-aos="fade-up" data-aos-delay="100">
    <form id="rideSearchForm" method="POST" action="result.php">
      <div class="search-form">
        <div class="location-inputs-container">
          <div class="form-group">
            <input type="text" name="from_location" id="from_location" placeholder="Enter street, area, city or landmark" required>
            <input type="hidden" name="from_lat" id="from_lat">
            <input type="hidden" name="from_lng" id="from_lng">
            <i class="fas fa-map-marker-alt"></i>
          </div>

          <div class="switch-btn-container">
            <button type="button" id="switchLocations" class="switch-locations-btn" title="Switch Locations">
              <i class="fas fa-exchange-alt"></i>
            </button>
          </div>

          <div class="form-group">
            <input type="text" name="to_location" id="to_location" placeholder="Enter street, area, city or landmark" required>
            <input type="hidden" name="to_lat" id="to_lat">
            <input type="hidden" name="to_lng" id="to_lng">
            <i class="fas fa-location-arrow"></i>
          </div>
        </div>

        <div class="form-group date-group">
          <input type="date" name="travel_date" id="travel_date" min="<?php echo date('Y-m-d'); ?>" required>
          <i class="fas fa-calendar-alt"></i>
        </div>

        <button type="submit" id="searchButton" class="form-btn">
          <i class="fas fa-search"></i>
          Find Rides
        </button>
      </div>
    </form>
  </section>

  <!-- Add space and a visual separator before Recent Rides -->
  <div style="height: 60px;"></div>
  <hr style="border: none; border-top: 2px solid #e9b424; margin: 0 0 40px 0; width: 100px; background: transparent;">

  <div class="section-title" data-aos="fade-right">Recent Rides</div>
  
  <div class="rides-container">
    <?php if ($recentResult->num_rows > 0): ?>
      <?php while ($row = $recentResult->fetch_assoc()): ?>
        <div class="ride-card" data-aos="fade-up" data-aos-delay="150">
          <div class="ride-card__header">
            <div class="ride-route">
              <i class="fas fa-route"></i>
              <?= htmlspecialchars($row['departure_city']) ?> to <?= htmlspecialchars($row['destination_city']) ?>
            </div>
            <div class="ride-price">₹<?= htmlspecialchars($row['price']) ?></div>
          </div>
          <div class="ride-card__body">
            <div class="ride-details">
              <div class="ride-detail">
                <i class="fas fa-calendar"></i>
                <span><?= date('l', strtotime($row['departure_date'])) ?></span>
              </div>
              <div class="ride-detail">
                <i class="fas fa-clock"></i>
                <span><?= date('g:i A', strtotime($row['departure_time'])) ?></span>
              </div>
            </div>
          </div>
          <div class="ride-card__footer">
            <div class="seats-left">
              <i class="fas fa-user"></i>
              <span><?= htmlspecialchars($row['available_seats']) ?> seats left</span>
            </div>
            <?php if(isset($_SESSION['user_id'])): ?>
              <a href="Ridedetails.php?id=<?= htmlspecialchars($row['id']) ?>">
                <button class="book-btn">Book Now</button>
              </a>
            <?php else: ?>
              <a href="login.php?redirect=Ridedetails.php&id=<?= htmlspecialchars($row['id']) ?>">
                <button class="book-btn">Book Now</button>
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="no-rides" data-aos="fade-up">
        <p>No rides found for this week. Check back later or be the first to offer a ride!</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Alert for validation -->
<div id="alert" class="alert">
  <i class="fas fa-exclamation-circle"></i>
  <span id="alertMessage"></span>
</div>

<!-- Include autocomplete styles and module -->
<link rel="stylesheet" href="css/places-autocomplete.css">
<!-- Load Google Maps API dynamically -->
<script>
// Only load the script if ModernPlacesAutocomplete is not already defined
if (typeof ModernPlacesAutocomplete === 'undefined') {
    const script = document.createElement('script');
    script.src = "js/modern-places-autocomplete.js";
    document.body.appendChild(script);
}

function loadGoogleMapsScript() {
  const script = document.createElement('script');
  const apiKey = '<?php echo $googleMapsApiKey; ?>';
  script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=initFindRidesAutocomplete`;
  script.async = true;
  script.onerror = function() {
    alert('Failed to load Google Maps API. Please check your connection or API key.');
  };
  document.body.appendChild(script);
}

window.initFindRidesAutocomplete = function() {
  const placesUtil = window.modernPlacesAutocomplete;
  placesUtil.init().then(() => {
    placesUtil.createAutocomplete({
      fromInputId: 'from_location',
      toInputId: 'to_location',
      fromLatId: 'from_lat',
      fromLngId: 'from_lng',
      toLatId: 'to_lat',
      toLngId: 'to_lng'
    });

    // Move form validation here so we can use placesUtil
    const rideSearchForm = document.getElementById('rideSearchForm');
    const alert = document.getElementById('alert');
    const alertMessage = document.getElementById('alertMessage');
    const dateInput = document.getElementById('travel_date');

    function showAlert(message, isError = false) {
      alertMessage.textContent = message;
      alert.classList.add('show');
      if (isError) {
        alert.classList.add('error');
      } else {
        alert.classList.remove('error');
      }
      setTimeout(() => {
        alert.classList.remove('show');
      }, 3000);
    }

    rideSearchForm.addEventListener('submit', function(e) {
      try {
        // Validate date
        const selectedDate = new Date(dateInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (selectedDate < today) {
          e.preventDefault();
          showAlert('Please select a future date', true);
          return;
        }
        // Use the correct instance for validation
        placesUtil.validateInputs(
          'from_location', 'to_location',
          'from_lat', 'from_lng',
          'to_lat', 'to_lng'
        );
      } catch (error) {
        e.preventDefault();
        showAlert(error.message, true);
        return;
      }
    });
  }).catch(error => {
    console.error('Failed to initialize places autocomplete:', error);
  });
};

document.addEventListener('DOMContentLoaded', loadGoogleMapsScript);
</script>

<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize AOS animations
  AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true
  });
  
  // Set default date to today
  const today = new Date();
  const yyyy = today.getFullYear();
  const mm = String(today.getMonth() + 1).padStart(2, '0');
  const dd = String(today.getDate()).padStart(2, '0');
  const minDate = yyyy + '-' + mm + '-' + dd;
  
  const dateInput = document.getElementById('travel_date');
  dateInput.setAttribute('min', minDate);
  
  // Prevent selecting past dates
  dateInput.addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
      showAlert('Please select a future date', true);
      this.value = minDate;
    }
  });
  
  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      
      const targetId = this.getAttribute('href');
      const targetElement = document.querySelector(targetId);
      
      if (targetElement) {
        window.scrollTo({
          top: targetElement.offsetTop - 100,
          behavior: 'smooth'
        });
      }
    });
  });
});

function validateForm() {
    const fromLocation = document.getElementById('from_location').value;
    const toLocation = document.getElementById('to_location').value;
    const fromLat = document.getElementById('from_lat').value;
    const fromLng = document.getElementById('from_lng').value;
    const toLat = document.getElementById('to_lat').value;
    const toLng = document.getElementById('to_lng').value;

    if (!fromLocation || !toLocation) {
        alert('Please select both departure and destination locations');
        return false;
    }

    if (!fromLat || !fromLng || !toLat || !toLng) {
        alert('Please select locations from the dropdown suggestions');
        return false;
    }

    return true;
}

// Add form validation to the search form
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.onsubmit = function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            return true;
        };
    }
});

// Switch locations functionality
document.getElementById('switchLocations').addEventListener('click', function(e) {
  e.preventDefault();
  
  // Get all the elements
  const fromLocation = document.getElementById('from_location');
  const toLocation = document.getElementById('to_location');
  const fromLat = document.getElementById('from_lat');
  const fromLng = document.getElementById('from_lng');
  const toLat = document.getElementById('to_lat');
  const toLng = document.getElementById('to_lng');
  
  // Store the values
  const tempFromLocation = fromLocation.value;
  const tempFromLat = fromLat.value;
  const tempFromLng = fromLng.value;
  
  // Swap the values
  fromLocation.value = toLocation.value;
  fromLat.value = toLat.value;
  fromLng.value = toLng.value;
  
  toLocation.value = tempFromLocation;
  toLat.value = tempFromLat;
  toLng.value = tempFromLng;
});
</script>

<?php include 'footer.php';?>
</div></body>
</html>
