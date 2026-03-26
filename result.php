<?php
include 'header.php';
include 'db.php';

// Error message mapping
$error_messages = [
    'booking_failed' => 'Sorry, there was an error processing your booking. Please try again.',
    'insufficient_seats' => 'Sorry, there are not enough seats available for this trip.',
    'invalid_data' => 'Invalid booking data provided. Please try again.',
    'not_logged_in' => 'Please log in to book a ride.',
    'invalid_request' => 'Invalid request. Please try booking through the proper channel.',
    'seats_taken' => 'Sorry, these seats were just taken by another user. Please try again.',
    'invalid_trip' => 'The selected trip is no longer available.'
];

// Display error message if exists
if (isset($_GET['error']) && array_key_exists($_GET['error'], $error_messages)) {
    echo '<div class="alert alert-danger" style="
        margin: 20px auto;
        max-width: 800px;
        padding: 15px;
        border-radius: 8px;
        background-color: #fff8e6;
        border-left: 4px solid #ffc107;
        color: #856404;
        font-size: 16px;
        text-align: center;
    ">';
    echo '<i class="fas fa-exclamation-circle" style="margin-right: 10px;"></i>';
    echo $error_messages[$_GET['error']];
    echo '</div>';
}

// Get search criteria from POST data or URL parameters
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION['from_location'] = $_POST['from_location'] ?? '';
    $_SESSION['to_location'] = $_POST['to_location'] ?? '';
    $_SESSION['travel_date'] = $_POST['travel_date'] ?? '';
    
    // Reset any filter when a new search is performed
    unset($_SESSION['current_filter']);
    unset($_SESSION['price_filter']);
    unset($_SESSION['sort_by']);
} elseif (isset($_GET['from']) || isset($_GET['to']) || isset($_GET['date'])) {
    // Handle URL parameters from save_location.php redirect
    $_SESSION['from_location'] = $_GET['from'] ?? '';
    $_SESSION['to_location'] = $_GET['to'] ?? '';
    $_SESSION['travel_date'] = $_GET['date'] ?? '';
    
    // Reset any filter when a new search is performed
    unset($_SESSION['current_filter']);
    unset($_SESSION['price_filter']);
    unset($_SESSION['sort_by']);
}

// Handle the "all" filter explicitly to clear filters
if (isset($_GET['clear_all_filters']) && $_GET['clear_all_filters'] == 1) {
    // Clear any date or filter constraints but keep city selections
    unset($_SESSION['current_filter']);
    unset($_SESSION['travel_date']);
    unset($_SESSION['price_filter']);
    unset($_SESSION['sort_by']);
}

// Get filter from GET if present and store in session
if (isset($_GET['filter'])) {
    $_SESSION['current_filter'] = $_GET['filter'];
}
if (isset($_GET['price_filter'])) {
    $_SESSION['price_filter'] = $_GET['price_filter'];
}
if (isset($_GET['sort_by'])) {
    $_SESSION['sort_by'] = $_GET['sort_by'];
}

$from_location = $_SESSION['from_location'] ?? '';
$to_location = $_SESSION['to_location'] ?? '';
$travel_date = $_SESSION['travel_date'] ?? '';
$current_filter = $_SESSION['current_filter'] ?? '';
$price_filter = $_SESSION['price_filter'] ?? '';
$sort_by = $_SESSION['sort_by'] ?? 'departure_date';

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Base query for rides
$sql = "SELECT t.*, d.vehicle_name,
        (t.seats - COALESCE((
            SELECT SUM(seats_booked) 
            FROM bookings 
            WHERE trip_id = t.id 
            AND payment_status IN ('completed', 'pending')
        ), 0)) AS available_seats
        FROM trips t 
        LEFT JOIN drivers d ON t.driver_email = d.Email 
        WHERE 1=1";

// Apply search filters for cities
if (!empty($from_location)) {
    $from_location = mysqli_real_escape_string($conn, $from_location);
    $sql .= " AND t.departure_city = '$from_location'";
}

if (!empty($to_location)) {
    $to_location = mysqli_real_escape_string($conn, $to_location);
    $sql .= " AND t.destination_city = '$to_location'";
}

// Today's date in Y-m-d format
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$day_after_tomorrow = date('Y-m-d', strtotime('+2 days'));

// Calculate weekend dates more accurately
$current_day_of_week = date('w'); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

// Calculate this weekend (Saturday and Sunday)
if ($current_day_of_week == 0) { // If today is Sunday
    $this_weekend_start = date('Y-m-d', strtotime('next saturday'));
    $this_weekend_end = date('Y-m-d', strtotime('next sunday'));
} elseif ($current_day_of_week == 6) { // If today is Saturday
    $this_weekend_start = $today;
    $this_weekend_end = date('Y-m-d', strtotime('+1 day'));
} else { // Monday to Friday
    $days_to_saturday = 6 - $current_day_of_week;
    $this_weekend_start = date('Y-m-d', strtotime("+{$days_to_saturday} days"));
    $this_weekend_end = date('Y-m-d', strtotime("+{$days_to_saturday} days +1 day"));
}

// Calculate next week (Monday to Sunday)
$days_to_next_monday = (8 - $current_day_of_week) % 7;
if ($days_to_next_monday == 0) $days_to_next_monday = 7; // If today is Monday, next Monday is 7 days away
$next_week_start = date('Y-m-d', strtotime("+{$days_to_next_monday} days"));
$next_week_end = date('Y-m-d', strtotime("+{$days_to_next_monday} days +6 days"));

// Apply date filters - filter takes precedence over travel_date
if ($current_filter === 'today') {
    $sql .= " AND t.departure_date = '$today'";
    $applied_filter = 'Today';
    $display_travel_date = $today;
} 
elseif ($current_filter === 'tomorrow') {
    $sql .= " AND t.departure_date = '$tomorrow'";
    $applied_filter = 'Tomorrow';
    $display_travel_date = $tomorrow;
} 
elseif ($current_filter === 'day_after_tomorrow') {
    $sql .= " AND t.departure_date = '$day_after_tomorrow'";
    $applied_filter = 'Day After Tomorrow';
    $display_travel_date = $day_after_tomorrow;
}
elseif ($current_filter === 'this_weekend') {
    $sql .= " AND t.departure_date BETWEEN '$this_weekend_start' AND '$this_weekend_end'";
    $applied_filter = 'This Weekend';
    $display_travel_date = null;
}
elseif ($current_filter === 'next_week') {
    $sql .= " AND t.departure_date BETWEEN '$next_week_start' AND '$next_week_end'";
    $applied_filter = 'Next Week';
    $display_travel_date = null;
}
elseif ($current_filter === 'next_month') {
    $next_month_start = date('Y-m-01', strtotime('+1 month'));
    $next_month_end = date('Y-m-t', strtotime('+1 month'));
    $sql .= " AND t.departure_date BETWEEN '$next_month_start' AND '$next_month_end'";
    $applied_filter = 'Next Month';
    $display_travel_date = null;
}
elseif (!empty($travel_date)) {
    // Apply travel_date filter when user searches from index.php
    $travel_date = mysqli_real_escape_string($conn, $travel_date);
    $sql .= " AND t.departure_date = '$travel_date'";
    $display_travel_date = $travel_date;
    $applied_filter = 'Selected Date';
}

// Apply price filters
if ($price_filter === 'under_500') {
    $sql .= " AND t.price < 500";
} elseif ($price_filter === '500_1000') {
    $sql .= " AND t.price BETWEEN 500 AND 1000";
} elseif ($price_filter === '1000_2000') {
    $sql .= " AND t.price BETWEEN 1000 AND 2000";
} elseif ($price_filter === 'above_2000') {
    $sql .= " AND t.price > 2000";
}

// Apply vehicle type filter if selected
if (isset($_GET['vehicle_type']) && !empty($_GET['vehicle_type'])) {
    $vehicle_type = mysqli_real_escape_string($conn, $_GET['vehicle_type']);
    $sql .= " AND t.vehicle_type = '$vehicle_type'";
    error_log("Filtering by vehicle type: " . $vehicle_type);
    
    // Debug the actual values in the database
    $debug_query = "SELECT DISTINCT vehicle_type FROM trips";
    $debug_result = mysqli_query($conn, $debug_query);
    if ($debug_result) {
        $vehicle_types = [];
        while ($row = mysqli_fetch_assoc($debug_result)) {
            $vehicle_types[] = $row['vehicle_type'];
        }
        error_log("Available vehicle types in database: " . implode(", ", $vehicle_types));
    }
}

// Only show future trips and available seats
$sql .= " AND t.departure_date >= CURDATE() HAVING available_seats > 0";

// Apply sorting
switch ($sort_by) {
    case 'price_low':
        $sql .= " ORDER BY t.price ASC, t.departure_date ASC, t.departure_time ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY t.price DESC, t.departure_date ASC, t.departure_time ASC";
        break;
    case 'departure_time':
        $sql .= " ORDER BY t.departure_time ASC, t.departure_date ASC";
        break;
    case 'seats':
            $sql .= " ORDER BY available_seats DESC, t.departure_date ASC, t.departure_time ASC";
        break;
    default:
        $sql .= " ORDER BY t.departure_date ASC, t.departure_time ASC";
}

// Debug final query
error_log("Final SQL query: " . $sql);

// Execute the query
$result = mysqli_query($conn, $sql);

// Debug results
if (!$result) {
    error_log("Query error: " . mysqli_error($conn));
} else {
    error_log("Number of results: " . mysqli_num_rows($result));
    if (mysqli_num_rows($result) === 0 && isset($_GET['vehicle_type'])) {
        error_log("No results found for vehicle type: " . $_GET['vehicle_type']);
    }
}

// Debug: Check for query errors
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Debug info (can be removed in production)
$debug_info = [
    'filter' => $current_filter,
    'price_filter' => $price_filter,
    'sort_by' => $sort_by,
    'today' => $today,
    'tomorrow' => $tomorrow,
    'day_after_tomorrow' => $day_after_tomorrow,
    'this_weekend_start' => $this_weekend_start,
    'this_weekend_end' => $this_weekend_end,
    'next_week_start' => $next_week_start,
    'next_week_end' => $next_week_end,
    'travel_date' => $travel_date,
    'display_travel_date' => $display_travel_date ?? null,
    'sql' => $sql,
    'result_count' => mysqli_num_rows($result),
    'session' => $_SESSION
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Available Rides - PoolPal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --ppr-primary-color: #ffbf00;
      --ppr-primary-light: #fff8e6;
      --ppr-primary-dark: #e6ac00;
      --ppr-primary-gradient: linear-gradient(135deg, #ffbf00 0%, #ff9500 100%);
      --ppr-secondary-color: #f8fafc;
      --ppr-accent-color: #10b981;
      --ppr-accent-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
      --ppr-text-dark: #1e293b;
      --ppr-text-medium: #475569;
      --ppr-text-light: #64748b;
      --ppr-text-muted: #94a3b8;
      --ppr-border-color: #e2e8f0;
      --ppr-border-light: #f1f5f9;
      --ppr-white: #ffffff;
      --ppr-shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
      --ppr-shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 15px rgba(0, 0, 0, 0.08);
      --ppr-shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1), 0 20px 40px rgba(0, 0, 0, 0.06);
      --ppr-shadow-xl: 0 20px 40px rgba(0, 0, 0, 0.1), 0 25px 50px rgba(0, 0, 0, 0.08);
      --ppr-radius-sm: 6px;
      --ppr-radius-md: 12px;
      --ppr-radius-lg: 16px;
      --ppr-radius-xl: 24px;
      --ppr-radius-2xl: 32px;
      --ppr-transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      --ppr-transition-fast: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      --ppr-blur-backdrop: blur(12px) saturate(180%);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    .ppr-body-unique {
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      background-attachment: fixed;
      min-height: 100vh;
      color: var(--ppr-text-dark);
      font-family: 'Outfit', 'Inter', sans-serif;
      line-height: 1.6;
      overflow-x: hidden;
    }

    .ppr-body-unique::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="25" cy="25" r="0.3" fill="rgba(255,255,255,0.05)"/><circle cx="75" cy="75" r="0.4" fill="rgba(255,255,255,0.07)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      opacity: 0.6;
      pointer-events: none;
      z-index: -1;
    }

    .ppr-main-container-unique {
      max-width: 1440px;
      margin: 0 auto;
      padding: 20px;
      min-height: calc(100vh - 120px);
      position: relative;
      z-index: 1;
    }



    /* Header Section */
    .ppr-page-header-unique {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: var(--ppr-blur-backdrop);
      border-radius: var(--ppr-radius-2xl);
      padding: 40px;
      margin-bottom: 30px;
      box-shadow: var(--ppr-shadow-xl);
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.2);
      animation: ppr-slideInDown 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .ppr-page-header-unique::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: var(--ppr-primary-gradient);
      border-radius: var(--ppr-radius-2xl) var(--ppr-radius-2xl) 0 0;
    }

    .ppr-page-header-unique::after {
      content: '';
      position: absolute;
      top: -50%;
      right: -20%;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, rgba(255, 191, 0, 0.1) 0%, transparent 70%);
      border-radius: 50%;
      animation: ppr-pulse 4s ease-in-out infinite;
    }

    @keyframes ppr-pulse {
      0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.3; }
      50% { transform: scale(1.1) rotate(180deg); opacity: 0.6; }
    }

    .ppr-breadcrumb-unique {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      color: var(--ppr-text-light);
      margin-bottom: 25px;
      flex-wrap: wrap;
      font-weight: 500;
      position: relative;
      z-index: 2;
    }

    .ppr-breadcrumb-item-unique {
      display: flex;
      align-items: center;
      gap: 8px;
      transition: var(--ppr-transition-fast);
      text-decoration: none;
    }

    .ppr-breadcrumb-item-unique:hover {
      color: var(--ppr-primary-color);
      transform: translateY(-1px);
    }

    .ppr-breadcrumb-item-unique:not(:last-child)::after {
      content: '\f105';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      color: var(--ppr-text-muted);
      margin-left: 8px;
    }

    .ppr-page-title-unique {
      font-size: clamp(28px, 6vw, 42px);
      font-weight: 800;
      color: var(--ppr-text-dark);
      margin-bottom: 12px;
      font-family: 'Space Grotesk', sans-serif;
      letter-spacing: -0.02em;
      position: relative;
      z-index: 2;
      background: linear-gradient(135deg, var(--ppr-text-dark) 0%, var(--ppr-primary-color) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .ppr-page-subtitle-unique {
      font-size: 18px;
      color: var(--ppr-text-medium);
      margin-bottom: 30px;
      font-weight: 400;
      position: relative;
      z-index: 2;
      max-width: 600px;
    }

    /* Search Summary */
    .ppr-search-summary-unique {
      background: var(--ppr-primary-gradient);
      border-radius: var(--ppr-radius-xl);
      padding: 25px 30px;
      color: white;
      display: flex;
      flex-wrap: wrap;
      gap: 25px;
      align-items: center;
      position: relative;
      overflow: hidden;
      box-shadow: var(--ppr-shadow-lg);
      border: 1px solid rgba(255, 255, 255, 0.2);
      backdrop-filter: var(--ppr-blur-backdrop);
      animation: ppr-slideInUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.2s both;
    }

    .ppr-search-summary-unique::before {
      content: '';
      position: absolute;
      top: -100%;
      right: -100%;
      width: 300%;
      height: 300%;
      background: conic-gradient(from 0deg, transparent 0%, rgba(255,255,255,0.1) 10%, transparent 20%);
      animation: ppr-rotate 8s linear infinite;
    }

    @keyframes ppr-rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    .ppr-search-item-unique {
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 600;
      position: relative;
      z-index: 2;
      padding: 12px 20px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: var(--ppr-radius-lg);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: var(--ppr-transition-fast);
    }

    .ppr-search-item-unique:hover {
      background: rgba(255, 255, 255, 0.25);
      transform: translateY(-2px);
    }

    .ppr-search-item-unique i {
      font-size: 18px;
      opacity: 0.9;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Content Layout */
    .ppr-content-layout-unique {
      display: grid;
      grid-template-columns: 320px 1fr;
      gap: 30px;
      align-items: start;
    }

    /* Filters Sidebar */
    .ppr-filters-sidebar-unique {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: var(--ppr-blur-backdrop);
      border-radius: var(--ppr-radius-xl);
      padding: 30px;
      box-shadow: var(--ppr-shadow-lg);
      position: sticky;
      top: 20px;
      max-height: calc(100vh - 100px);
      overflow-y: auto;
      border: 1px solid rgba(255, 255, 255, 0.2);
      animation: ppr-slideInLeft 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.4s both;
    }

    .ppr-filters-sidebar-unique::-webkit-scrollbar {
      width: 6px;
    }

    .ppr-filters-sidebar-unique::-webkit-scrollbar-track {
      background: var(--ppr-border-light);
      border-radius: 3px;
    }

    .ppr-filters-sidebar-unique::-webkit-scrollbar-thumb {
      background: var(--ppr-primary-color);
      border-radius: 3px;
    }

    .ppr-filters-title-unique {
      font-size: 22px;
      font-weight: 700;
      color: var(--ppr-text-dark);
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      gap: 12px;
      font-family: 'Space Grotesk', sans-serif;
    }

    .ppr-filters-title-unique i {
      color: var(--ppr-primary-color);
      font-size: 20px;
    }

    .ppr-filter-section-unique {
      margin-bottom: 30px;
      padding-bottom: 25px;
      border-bottom: 2px solid var(--ppr-border-light);
      position: relative;
    }

    .ppr-filter-section-unique:last-child {
      border-bottom: none;
      margin-bottom: 0;
    }

    .ppr-filter-section-title-unique {
      font-size: 16px;
      font-weight: 600;
      color: var(--ppr-text-dark);
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-family: 'Space Grotesk', sans-serif;
    }

    .ppr-filter-section-title-unique i {
      color: var(--ppr-primary-color);
      font-size: 16px;
    }

    .ppr-filter-options-unique {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .ppr-filter-option-unique {
      display: flex;
      align-items: center;
      padding: 14px 16px;
      border-radius: var(--ppr-radius-lg);
      cursor: pointer;
      transition: var(--ppr-transition);
      border: 2px solid transparent;
      font-size: 14px;
      font-weight: 500;
      color: var(--ppr-text-medium);
      background: var(--ppr-secondary-color);
      position: relative;
      overflow: hidden;
      text-decoration: none !important;
    }

    .ppr-filter-option-unique:hover {
      background: rgba(255, 191, 0, 0.1);
      color: var(--ppr-text-dark);
      border-color: var(--ppr-primary-color);
      transform: translateX(8px);
      text-decoration: none !important;
    }

    .ppr-filter-option-unique.active {
      background: var(--ppr-primary-gradient);
      color: white;
      border-color: var(--ppr-primary-color);
      box-shadow: var(--ppr-shadow-md);
      transform: translateX(8px);
      text-decoration: none !important;
    }

    .ppr-filter-option-unique i {
      margin-right: 12px;
      width: 18px;
      text-align: center;
      font-size: 14px;
    }

    /* Sort Options */
    .ppr-sort-section-unique {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: var(--ppr-blur-backdrop);
      border-radius: var(--ppr-radius-xl);
      padding: 25px;
      margin-bottom: 25px;
      box-shadow: var(--ppr-shadow-md);
      border: 1px solid rgba(255, 255, 255, 0.2);
      animation: ppr-slideInRight 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.6s both;
    }

    .ppr-sort-title-unique {
      font-size: 18px;
      font-weight: 600;
      color: var(--ppr-text-dark);
      margin-bottom: 18px;
      font-family: 'Space Grotesk', sans-serif;
    }

    .ppr-sort-options-unique {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .ppr-sort-btn-unique {
      padding: 10px 16px;
      border: 2px solid var(--ppr-border-color);
      border-radius: var(--ppr-radius-lg);
      background: var(--ppr-white);
      color: var(--ppr-text-medium);
      cursor: pointer;
      transition: var(--ppr-transition);
      font-size: 14px;
      font-weight: 500;
      text-decoration: none !important;
      position: relative;
      overflow: hidden;
    }

    .ppr-sort-btn-unique::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: var(--ppr-primary-gradient);
      transition: var(--ppr-transition);
      z-index: -1;
    }

    .ppr-sort-btn-unique:hover::before,
    .ppr-sort-btn-unique.active::before {
      left: 0;
    }

    .ppr-sort-btn-unique:hover,
    .ppr-sort-btn-unique.active {
      color: white;
      border-color: var(--ppr-primary-color);
      transform: translateY(-2px);
      box-shadow: var(--ppr-shadow-md);
      text-decoration: none !important;
    }

    /* Results Section */
    .rp-results-section {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: var(--rp-blur-backdrop);
      border-radius: var(--rp-radius-xl);
      box-shadow: var(--rp-shadow-xl);
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.2);
      animation: rp-slideInUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.8s both;
    }

    .rp-results-header {
      padding: 25px 30px;
      border-bottom: 2px solid var(--rp-border-light);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 20px;
      background: linear-gradient(135deg, var(--rp-secondary-color) 0%, var(--rp-white) 100%);
    }

    .rp-results-count {
      font-size: 20px;
      font-weight: 700;
      color: var(--rp-text-dark);
      font-family: 'Space Grotesk', sans-serif;
    }

    .rp-results-count .rp-count {
      color: var(--rp-primary-color);
      font-size: 24px;
    }

    .rp-view-toggle,
    .rp-view-btn,
    .rp-view-btn.active,
    .ppr-view-toggle-unique,
    .ppr-view-btn-unique,
    .ppr-view-btn-unique.active {
      display: none !important;
    }

    .rp-rides-container {
      padding: 0;
    }

    .rp-ride-card {
      border-bottom: 1px solid var(--rp-border-light);
      padding: 0;
      transition: var(--rp-transition);
      position: relative;
      overflow: hidden;
      background: var(--rp-white);
    }

    .rp-ride-card:last-child {
      border-bottom: none;
    }

    .rp-ride-card:hover {
      background: var(--rp-secondary-color);
      box-shadow: var(--rp-shadow-lg);
    }

    .rp-ride-card-inner {
      padding: 25px 30px;
      position: relative;
      z-index: 2;
    }

    /* Professional Route Section */
    .rp-route-section {
      display: grid;
      grid-template-columns: 1fr auto 1fr auto;
      gap: 20px;
      align-items: center;
      margin-bottom: 20px;
      padding: 20px;
      background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
      border-radius: var(--rp-radius-lg);
      border: 1px solid var(--rp-border-light);
    }

    .rp-departure-info, .rp-arrival-info {
      text-align: center;
    }

    .rp-route-city {
      font-size: 18px;
      font-weight: 700;
      color: var(--rp-text-dark);
      font-family: 'Space Grotesk', sans-serif;
      margin-bottom: 5px;
      line-height: 1.2;
    }

    .rp-route-date {
      font-size: 14px;
      color: var(--rp-text-medium);
      font-weight: 500;
    }

    .rp-route-time {
      font-size: 16px;
      font-weight: 600;
      color: var(--rp-primary-color);
      margin-top: 2px;
    }

    .rp-route-connector {
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      min-width: 60px;
    }

    .rp-route-line {
      width: 100%;
      height: 2px;
      background: linear-gradient(90deg, var(--rp-primary-color), var(--rp-accent-color));
      border-radius: 1px;
      position: relative;
    }

    .rp-route-arrow {
      color: var(--rp-primary-color);
      font-size: 18px;
      position: absolute;
      right: -9px;
      top: 50%;
      transform: translateY(-50%);
      background: var(--rp-white);
      padding: 0 2px;
    }

    .rp-route-duration {
      font-size: 12px;
      color: var(--rp-text-light);
      text-align: center;
      margin-top: 8px;
      font-weight: 500;
      background: rgba(255, 191, 0, 0.1);
      padding: 4px 8px;
      border-radius: var(--rp-radius-sm);
      white-space: nowrap;
    }

    .rp-price-section {
      text-align: center;
      padding: 15px 20px;
      background: linear-gradient(135deg, var(--rp-accent-color), #059669);
      border-radius: var(--rp-radius-lg);
      color: white;
      min-width: 120px;
    }

    .rp-price-amount {
      font-size: 24px;
      font-weight: 900;
      line-height: 1;
      font-family: 'Space Grotesk', sans-serif;
    }

    .rp-price-label {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 600;
      opacity: 0.9;
      margin-top: 2px;
    }

    /* Details Grid */
    .rp-details-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }

    .rp-detail-card {
      background: var(--rp-secondary-color);
      padding: 15px 18px;
      border-radius: var(--rp-radius-lg);
      border: 1px solid var(--rp-border-light);
      transition: var(--rp-transition-fast);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .rp-detail-card:hover {
      background: var(--rp-primary-light);
      border-color: var(--rp-primary-color);
      transform: translateY(-2px);
    }

    .rp-detail-icon {
      width: 36px;
      height: 36px;
      background: var(--rp-primary-color);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--rp-text-dark);
      font-size: 16px;
      font-weight: 600;
    }

    .rp-detail-content {
      flex: 1;
    }

    .rp-detail-label {
      font-size: 12px;
      color: var(--rp-text-light);
      font-weight: 600;
    }

    .rp-detail-value {
      font-size: 14px;
      color: var(--rp-text-dark);
      font-weight: 600;
    }

    /* Feature Tags */
    .rp-features-section {
      margin-bottom: 25px;
    }

    .rp-features-title {
      font-size: 14px;
      font-weight: 600;
      color: var(--rp-text-dark);
      margin-bottom: 12px;
      font-family: 'Space Grotesk', sans-serif;
    }

    .rp-features-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .rp-feature-tag {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 14px;
      border-radius: var(--rp-radius-lg);
      font-size: 13px;
      font-weight: 600;
      border: 2px solid transparent;
      transition: var(--rp-transition-fast);
    }

    .rp-feature-tag.positive {
      background: rgba(16, 185, 129, 0.1);
      color: var(--rp-accent-color);
      border-color: rgba(16, 185, 129, 0.2);
    }

    .rp-feature-tag.negative {
      background: rgba(239, 68, 68, 0.1);
      color: #ef4444;
      border-color: rgba(239, 68, 68, 0.2);
    }

    .rp-feature-tag.neutral {
      background: var(--rp-secondary-color);
      color: var(--rp-text-medium);
      border-color: var(--rp-border-color);
    }

    .rp-feature-tag:hover {
      transform: translateY(-2px);
      box-shadow: var(--rp-shadow-sm);
    }

    .rp-feature-tag i {
      font-size: 12px;
    }

    /* Driver & Actions Section */
    .rp-bottom-section {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 25px;
      align-items: center;
      padding-top: 20px;
      border-top: 1px solid var(--rp-border-light);
    }

    .rp-driver-section {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .rp-driver-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: var(--rp-primary-gradient);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      color: white;
      border: 3px solid var(--rp-white);
      box-shadow: var(--rp-shadow-md);
      font-family: 'Space Grotesk', sans-serif;
      font-size: 18px;
      transition: var(--rp-transition);
    }

    .rp-driver-avatar:hover {
      transform: scale(1.05) rotate(5deg);
    }

    .rp-driver-info {
      flex: 1;
    }

    .rp-driver-name {
      font-size: 16px;
      font-weight: 600;
      color: var(--rp-text-dark);
      margin-bottom: 4px;
      font-family: 'Space Grotesk', sans-serif;
    }

    .rp-driver-rating {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: var(--rp-text-light);
    }

    .rp-driver-rating i {
      color: #fbbf24;
    }

    .rp-driver-meta {
      font-size: 12px;
      color: var(--rp-text-muted);
      margin-top: 2px;
    }

    .rp-actions-section {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .rp-btn {
      padding: 12px 24px;
      border-radius: var(--rp-radius-lg);
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--rp-transition);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      border: 2px solid transparent;
      position: relative;
      overflow: hidden;
      font-family: 'Space Grotesk', sans-serif;
      min-width: 120px;
    }

    .rp-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
      transition: var(--rp-transition);
    }

    .rp-btn:hover::before {
      left: 100%;
    }

    .rp-btn-primary {
      background: var(--rp-primary-gradient);
      color: var(--rp-text-dark);
      border-color: var(--rp-primary-color);
      box-shadow: var(--rp-shadow-md);
    }

    .rp-btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: var(--rp-shadow-lg);
    }

    .rp-btn-outline {
      background: var(--rp-white);
      color: var(--rp-text-medium);
      border-color: var(--rp-border-color);
    }

    .rp-btn-outline:hover {
      background: var(--rp-secondary-color);
      border-color: var(--rp-primary-color);
      color: var(--rp-primary-color);
      transform: translateY(-3px);
      box-shadow: var(--rp-shadow-md);
    }

    /* Empty State - Smaller buttons */
    .ppr-empty-state-unique {
      text-align: center;
      padding: 80px 20px;
      color: var(--ppr-text-light);
      animation: ppr-fadeIn 1s ease-out;
    }

    .ppr-empty-state-unique i {
      font-size: 80px;
      color: var(--ppr-text-muted);
      margin-bottom: 30px;
      opacity: 0.6;
      animation: ppr-bounce 2s ease-in-out infinite;
    }

    @keyframes ppr-bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-10px); }
      60% { transform: translateY(-5px); }
    }

    .ppr-empty-state-unique h3 {
      font-size: 28px;
      font-weight: 700;
      color: var(--ppr-text-medium);
      margin-bottom: 20px;
      font-family: 'Space Grotesk', sans-serif;
    }

    .ppr-empty-state-unique p {
      font-size: 16px;
      line-height: 1.6;
      max-width: 500px;
      margin: 0 auto 35px;
    }

    .ppr-empty-actions-unique {
      display: flex;
      gap: 15px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .ppr-empty-actions-unique .ppr-btn-unique {
      padding: 10px 18px !important;
      font-size: 13px !important;
      min-width: 90px !important;
      border-radius: var(--ppr-radius-md) !important;
    }

    .ppr-empty-actions-unique .ppr-btn-unique i {
      font-size: 12px !important;
    }

    .ppr-btn-unique {
      padding: 12px 24px;
      border-radius: var(--ppr-radius-lg);
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--ppr-transition);
      text-decoration: none !important;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      border: 2px solid transparent;
      position: relative;
      overflow: hidden;
      font-family: 'Space Grotesk', sans-serif;
      min-width: 120px;
    }

    .ppr-btn-unique::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
      transition: var(--ppr-transition);
    }

    .ppr-btn-unique:hover::before {
      left: 100%;
    }

    .ppr-btn-primary-unique {
      background: var(--ppr-primary-gradient);
      color: var(--ppr-text-dark);
      border-color: var(--ppr-primary-color);
      box-shadow: var(--ppr-shadow-md);
    }

    .ppr-btn-primary-unique:hover {
      transform: translateY(-3px);
      box-shadow: var(--ppr-shadow-lg);
      text-decoration: none !important;
    }

    .ppr-btn-outline-unique {
      background: var(--ppr-white);
      color: var(--ppr-text-medium);
      border-color: var(--ppr-border-color);
    }

    .ppr-btn-outline-unique:hover {
      background: var(--ppr-secondary-color);
      border-color: var(--ppr-primary-color);
      color: var(--ppr-primary-color);
      transform: translateY(-3px);
      box-shadow: var(--ppr-shadow-md);
      text-decoration: none !important;
    }

    /* Results Section and all missing classes */
    .ppr-results-section-unique {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: var(--ppr-blur-backdrop);
      border-radius: var(--ppr-radius-xl);
      box-shadow: var(--ppr-shadow-xl);
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.2);
      animation: ppr-slideInUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.8s both;
    }

    .ppr-results-header-unique {
      padding: 25px 30px;
      border-bottom: 2px solid var(--ppr-border-light);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 20px;
      background: linear-gradient(135deg, var(--ppr-secondary-color) 0%, var(--ppr-white) 100%);
    }

    .ppr-results-count-unique {
      font-size: 16px;
      color: var(--ppr-text-medium);
    }

    .ppr-results-count-unique .ppr-count-unique {
      color: var(--ppr-primary-color);
      font-size: 24px;
    }

    .ppr-view-toggle-unique {
      display: flex;
      border: 2px solid var(--ppr-border-color);
      border-radius: var(--ppr-radius-lg);
      overflow: hidden;
      background: var(--ppr-white);
    }

    .ppr-view-btn-unique {
      padding: 10px 14px;
      background: var(--ppr-white);
      border: none;
      cursor: pointer;
      transition: var(--ppr-transition);
      color: var(--ppr-text-medium);
      font-size: 16px;
    }

    .ppr-view-btn-unique.active {
      background: var(--ppr-primary-color);
      color: var(--ppr-text-dark);
    }

    /* Ride Cards */
    .ppr-rides-container-unique {
      padding: 0;
    }

    .ppr-ride-card-unique {
      border-bottom: 1px solid var(--ppr-border-light);
      padding: 0;
      transition: var(--ppr-transition);
      position: relative;
      overflow: hidden;
      background: var(--ppr-white);
    }

    .ppr-ride-card-unique:last-child {
      border-bottom: none;
    }

    .ppr-ride-card-unique:hover {
      background: var(--ppr-secondary-color);
      box-shadow: var(--ppr-shadow-lg);
    }

    .ppr-ride-card-inner-unique {
      padding: 25px 30px;
      position: relative;
      z-index: 2;
    }

    /* Route Section */
    .ppr-route-section-unique {
      display: grid;
      grid-template-columns: 1fr auto 1fr auto;
      gap: 20px;
      align-items: center;
      margin-bottom: 20px;
      padding: 20px;
      background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
      border-radius: var(--ppr-radius-lg);
      border: 1px solid var(--ppr-border-light);
    }

    .ppr-departure-info-unique, .ppr-arrival-info-unique {
      text-align: center;
    }

    .ppr-route-city-unique {
      font-size: 18px;
      font-weight: 700;
      color: var(--ppr-text-dark);
      font-family: 'Space Grotesk', sans-serif;
      margin-bottom: 5px;
      line-height: 1.2;
    }

    .ppr-route-date-unique {
      font-size: 14px;
      color: var(--ppr-text-medium);
      font-weight: 500;
    }

    .ppr-route-time-unique {
      font-size: 16px;
      font-weight: 600;
      color: var(--ppr-primary-color);
      margin-top: 2px;
    }

    .ppr-route-connector-unique {
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      min-width: 60px;
    }

    .ppr-route-line-unique {
      width: 100%;
      height: 2px;
      background: linear-gradient(90deg, var(--ppr-primary-color), var(--ppr-accent-color));
      border-radius: 1px;
      position: relative;
    }

    .ppr-route-arrow-unique {
      color: var(--ppr-primary-color);
      font-size: 18px;
      position: absolute;
      right: -9px;
      top: 50%;
      transform: translateY(-50%);
      background: var(--ppr-white);
      padding: 0 2px;
    }

    .ppr-route-duration-unique {
      font-size: 12px;
      color: var(--ppr-text-light);
      text-align: center;
      margin-top: 8px;
      font-weight: 500;
      background: rgba(255, 191, 0, 0.1);
      padding: 4px 8px;
      border-radius: var(--ppr-radius-sm);
      white-space: nowrap;
    }

    .ppr-price-section-unique {
      text-align: center;
      padding: 15px 20px;
      background: linear-gradient(135deg, var(--ppr-accent-color), #059669);
      border-radius: var(--ppr-radius-lg);
      color: white;
      min-width: 120px;
    }

    .ppr-price-amount-unique {
      font-size: 24px;
      font-weight: 900;
      line-height: 1;
      font-family: 'Space Grotesk', sans-serif;
    }

    .ppr-price-label-unique {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 600;
      opacity: 0.9;
      margin-top: 2px;
    }

    /* Details Grid */
    .ppr-details-grid-unique {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }

    .ppr-detail-card-unique {
      background: var(--ppr-secondary-color);
      padding: 15px 18px;
      border-radius: var(--ppr-radius-lg);
      border: 1px solid var(--ppr-border-light);
      transition: var(--ppr-transition-fast);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .ppr-detail-card-unique:hover {
      background: var(--ppr-primary-light);
      border-color: var(--ppr-primary-color);
      transform: translateY(-2px);
    }

    .ppr-detail-icon-unique {
      width: 36px;
      height: 36px;
      background: var(--ppr-primary-color);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--ppr-text-dark);
      font-size: 16px;
      font-weight: 600;
    }

    .ppr-detail-content-unique {
      flex: 1;
    }

    .ppr-detail-label-unique {
      font-size: 12px;
      color: var(--ppr-text-light);
      font-weight: 600;
    }

    .ppr-detail-value-unique {
      font-size: 14px;
      color: var(--ppr-text-dark);
      font-weight: 600;
    }

    /* Feature Tags */
    .ppr-features-section-unique {
      margin-bottom: 25px;
    }

    .ppr-features-title-unique {
      font-size: 14px;
      font-weight: 600;
      color: var(--ppr-text-dark);
      margin-bottom: 12px;
      font-family: 'Space Grotesk', sans-serif;
    }

    .ppr-features-grid-unique {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .ppr-feature-tag-unique {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 14px;
      border-radius: var(--ppr-radius-lg);
      font-size: 13px;
      font-weight: 600;
      border: 2px solid transparent;
      transition: var(--ppr-transition-fast);
    }

    .ppr-feature-tag-unique.positive {
      background: rgba(16, 185, 129, 0.1);
      color: var(--ppr-accent-color);
      border-color: rgba(16, 185, 129, 0.2);
    }

    .ppr-feature-tag-unique.negative {
      background: rgba(239, 68, 68, 0.1);
      color: #ef4444;
      border-color: rgba(239, 68, 68, 0.2);
    }

    .ppr-feature-tag-unique.neutral {
      background: var(--ppr-secondary-color);
      color: var(--ppr-text-medium);
      border-color: var(--ppr-border-color);
    }

    .ppr-feature-tag-unique:hover {
      transform: translateY(-2px);
      box-shadow: var(--ppr-shadow-sm);
    }

    .ppr-feature-tag-unique i {
      font-size: 12px;
    }

    /* Driver & Actions Section */
    .ppr-bottom-section-unique {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 25px;
      align-items: center;
      padding-top: 20px;
      border-top: 1px solid var(--ppr-border-light);
    }

    .ppr-driver-section-unique {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .ppr-driver-avatar-unique {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: var(--ppr-primary-gradient);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      color: white;
      border: 3px solid var(--ppr-white);
      box-shadow: var(--ppr-shadow-md);
      font-family: 'Space Grotesk', sans-serif;
      font-size: 18px;
      transition: var(--ppr-transition);
    }

    .ppr-driver-avatar-unique:hover {
      transform: scale(1.05) rotate(5deg);
    }

    .ppr-driver-info-unique {
      flex: 1;
    }

    .ppr-driver-name-unique {
      font-size: 16px;
      font-weight: 600;
      color: var(--ppr-text-dark);
      margin-bottom: 4px;
      font-family: 'Space Grotesk', sans-serif;
    }

    .ppr-driver-rating-unique {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: var(--ppr-text-light);
    }

    .ppr-driver-rating-unique i {
      color: #fbbf24;
    }

    .ppr-driver-meta-unique {
      font-size: 12px;
      color: var(--ppr-text-muted);
      margin-top: 2px;
    }

    .ppr-actions-section-unique {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    /* Mobile Filters */
    .ppr-mobile-filters-unique {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(8px);
      z-index: 9999;
      animation: ppr-fadeIn 0.3s ease-out;
    }

    .ppr-mobile-filters-unique.active {
      display: flex;
    }

    .ppr-mobile-filters-content-unique {
      background: var(--ppr-white);
      margin: auto;
      border-radius: var(--ppr-radius-xl);
      max-width: 90vw;
      max-height: 90vh;
      overflow-y: auto;
      position: relative;
      animation: ppr-slideInUp 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .ppr-mobile-filters-header-unique {
      padding: 25px;
      border-bottom: 2px solid var(--ppr-border-light);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: var(--ppr-secondary-color);
      border-radius: var(--ppr-radius-xl) var(--ppr-radius-xl) 0 0;
    }

    .ppr-mobile-filters-header-unique h3 {
      font-family: 'Space Grotesk', sans-serif;
      font-weight: 600;
      color: var(--ppr-text-dark);
    }

    .ppr-close-filters-unique {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: var(--ppr-text-light);
      padding: 8px;
      border-radius: 50%;
      transition: var(--ppr-transition);
    }

    .ppr-close-filters-unique:hover {
      background: var(--ppr-primary-color);
      color: white;
      transform: rotate(90deg);
    }

    /* Mobile Filter Button */
    .ppr-mobile-filter-btn-unique {
      display: none;
      width: 100%;
      padding: 18px;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: var(--ppr-blur-backdrop);
      border: 2px solid var(--ppr-border-color);
      border-radius: var(--ppr-radius-xl);
      margin-bottom: 25px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      color: var(--ppr-text-dark);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      transition: var(--ppr-transition);
      font-family: 'Space Grotesk', sans-serif;
    }

    .ppr-mobile-filter-btn-unique:hover {
      border-color: var(--ppr-primary-color);
      color: var(--ppr-primary-color);
      transform: translateY(-2px);
      box-shadow: var(--ppr-shadow-md);
    }

    /* Scroll to top button */
    .ppr-scroll-to-top-unique {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 50px;
      height: 50px;
      background: var(--ppr-primary-gradient);
      border: none;
      border-radius: 50%;
      color: white;
      font-size: 18px;
      cursor: pointer;
      transition: var(--ppr-transition);
      box-shadow: var(--ppr-shadow-lg);
      z-index: 1000;
      opacity: 0;
      transform: translateY(100px);
    }

    .ppr-scroll-to-top-unique.visible {
      opacity: 1;
      transform: translateY(0);
    }

    .ppr-scroll-to-top-unique:hover {
      transform: translateY(-5px) scale(1.1);
      box-shadow: var(--ppr-shadow-xl);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
      .ppr-content-layout-unique {
        grid-template-columns: 280px 1fr;
        gap: 25px;
      }
      
      .ppr-main-container-unique {
        padding: 15px;
      }
    }

    @media (max-width: 768px) {
      .ppr-main-container-unique {
        padding: 12px;
      }

      .ppr-content-layout-unique {
        grid-template-columns: 1fr;
        gap: 0;
      }

      .ppr-filters-sidebar-unique {
        display: none;
      }

      .ppr-mobile-filter-btn-unique {
        display: flex;
      }

      .ppr-page-header-unique {
        padding: 25px;
      }

      .ppr-search-summary-unique {
        padding: 20px;
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }

      .ppr-search-item-unique {
        padding: 10px 16px;
      }

      .ppr-results-header-unique {
        padding: 20px;
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }

      /* Mobile Ride Card Complete Redesign */
      .ppr-ride-card-unique {
        margin-bottom: 20px;
        border-radius: var(--ppr-radius-xl);
        overflow: hidden;
        box-shadow: var(--ppr-shadow-lg);
        border: 1px solid var(--ppr-border-light);
      }

      .ppr-ride-card-inner-unique {
        padding: 0;
      }

      /* Mobile Route Header */
      .ppr-route-section-unique {
        grid-template-columns: 1fr;
        gap: 0;
        padding: 20px;
        background: var(--ppr-primary-gradient);
        color: white;
        position: relative;
        border-radius: 0;
        margin-bottom: 0;
      }

      .ppr-route-section-unique::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.05) 100%);
        pointer-events: none;
      }

      .ppr-departure-info-unique, .ppr-arrival-info-unique {
        padding: 0;
        background: transparent;
        border-radius: 0;
        position: relative;
        z-index: 2;
      }

      .ppr-departure-info-unique {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      }

      .ppr-arrival-info-unique {
        margin-bottom: 20px;
      }

      .ppr-route-city-unique {
        font-size: 20px;
        font-weight: 700;
        color: white;
        margin-bottom: 5px;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
      }

      .ppr-route-date-unique, .ppr-route-time-unique {
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
      }

      .ppr-route-connector-unique {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 60px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 2px;
      }

      .ppr-route-connector-unique::before {
        content: '';
        position: absolute;
        top: -8px;
        left: -6px;
        width: 16px;
        height: 16px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      }

      .ppr-route-connector-unique::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: -6px;
        width: 16px;
        height: 16px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      }

      .ppr-route-line-unique, .ppr-route-arrow-unique, .ppr-route-duration-unique {
        display: none;
      }

      .ppr-price-section-unique {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.95);
        color: var(--ppr-text-dark);
        padding: 12px 16px;
        border-radius: var(--ppr-radius-lg);
        min-width: auto;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(10px);
      }

      .ppr-price-amount-unique {
        font-size: 18px;
        font-weight: 800;
      }

      .ppr-price-label-unique {
        font-size: 10px;
        opacity: 0.7;
      }

      /* Mobile Details Section */
      .ppr-details-grid-unique {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 20px;
        background: var(--ppr-white);
        margin-bottom: 0;
      }

      .ppr-detail-card-unique {
        background: var(--ppr-secondary-color);
        padding: 15px 12px;
        border-radius: var(--ppr-radius-lg);
        border: 1px solid var(--ppr-border-light);
        text-align: center;
        transition: var(--ppr-transition-fast);
      }

      .ppr-detail-icon-unique {
        width: 32px;
        height: 32px;
        background: var(--ppr-primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--ppr-text-dark);
        font-size: 14px;
        font-weight: 600;
        margin: 0 auto 8px;
      }

      .ppr-detail-label-unique {
        font-size: 11px;
        color: var(--ppr-text-light);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
      }

      .ppr-detail-value-unique {
        font-size: 13px;
        color: var(--ppr-text-dark);
        font-weight: 600;
        line-height: 1.2;
      }

      /* Mobile Features Section */
      .ppr-features-section-unique {
        padding: 0 20px 20px;
        background: var(--ppr-white);
        margin-bottom: 0;
      }

      .ppr-features-title-unique {
        font-size: 13px;
        font-weight: 600;
        color: var(--ppr-text-dark);
        margin-bottom: 12px;
        font-family: 'Space Grotesk', sans-serif;
      }

      .ppr-features-grid-unique {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
      }

      .ppr-feature-tag-unique {
        padding: 6px 12px;
        font-size: 11px;
        border-radius: var(--ppr-radius-md);
        font-weight: 600;
        flex: 1;
        min-width: calc(33.333% - 6px);
        text-align: center;
        white-space: nowrap;
      }

      .ppr-feature-tag-unique i {
        font-size: 10px;
        margin-right: 4px;
      }

      /* Mobile Bottom Section - Only Details button */
      .ppr-bottom-section-unique {
        grid-template-columns: 1fr;
        gap: 15px;
        padding: 20px;
        background: linear-gradient(135deg, var(--ppr-secondary-color) 0%, var(--ppr-white) 100%);
        border-top: 1px solid var(--ppr-border-light);
        margin: 0;
      }

      .ppr-driver-section-unique {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
        background: var(--ppr-white);
        border-radius: var(--ppr-radius-lg);
        border: 1px solid var(--ppr-border-light);
        box-shadow: var(--ppr-shadow-sm);
      }

      .ppr-driver-avatar-unique {
        width: 45px;
        height: 45px;
        font-size: 16px;
        flex-shrink: 0;
      }

      .ppr-driver-info-unique {
        flex: 1;
        min-width: 0;
      }

      .ppr-driver-name-unique {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .ppr-driver-rating-unique {
        font-size: 12px;
        color: var(--ppr-text-light);
      }

      .ppr-driver-meta-unique {
        font-size: 11px;
        color: var(--ppr-text-muted);
        margin-top: 2px;
      }

      .ppr-actions-section-unique {
        display: flex;
        gap: 10px;
        width: 100%;
        justify-content: center;
      }

      .ppr-btn-unique {
        flex: 1;
        padding: 14px 16px;
        font-size: 13px;
        font-weight: 600;
        border-radius: var(--ppr-radius-lg);
        min-width: auto;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        max-width: 200px;
      }

      .ppr-btn-unique i {
        font-size: 12px;
      }

      /* Mobile Sort Section */
      .ppr-sort-section-unique {
        padding: 20px;
        margin-bottom: 20px;
      }

      .ppr-sort-options-unique {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
      }

      .ppr-sort-btn-unique {
        padding: 12px 8px;
        font-size: 12px;
        text-align: center;
        border-radius: var(--ppr-radius-lg);
      }

      /* Empty State Mobile - Smaller buttons */
      .ppr-empty-state-unique {
        padding: 60px 20px;
      }

      .ppr-empty-state-unique i {
        font-size: 60px;
      }

      .ppr-empty-state-unique h3 {
        font-size: 24px;
      }

      .ppr-empty-actions-unique {
        flex-direction: column;
        align-items: center;
        gap: 10px;
      }

      .ppr-empty-actions-unique .ppr-btn-unique {
        width: 100%;
        max-width: 200px !important;
        padding: 12px 16px !important;
        font-size: 12px !important;
      }
    }

    .rp-mobile-filters.active {
      display: flex;
    }

    .rp-mobile-filters-content {
      background: var(--rp-white);
      margin: auto;
      border-radius: var(--rp-radius-xl);
      max-width: 90vw;
      max-height: 90vh;
      overflow-y: auto;
      position: relative;
      animation: rp-slideInUp 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .rp-mobile-filters-header {
      padding: 25px;
      border-bottom: 2px solid var(--rp-border-light);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: var(--rp-secondary-color);
      border-radius: var(--rp-radius-xl) var(--rp-radius-xl) 0 0;
    }

    .rp-mobile-filters-header h3 {
      font-family: 'Space Grotesk', sans-serif;
      font-weight: 600;
      color: var(--rp-text-dark);
    }

    .rp-close-filters {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: var(--rp-text-light);
      padding: 8px;
      border-radius: 50%;
      transition: var(--rp-transition);
    }

    .rp-close-filters:hover {
      background: var(--rp-primary-color);
      color: white;
      transform: rotate(90deg);
    }

    /* Mobile Filter Button */
    .rp-mobile-filter-btn {
      display: none;
      width: 100%;
      padding: 18px;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: var(--rp-blur-backdrop);
      border: 2px solid var(--rp-border-color);
      border-radius: var(--rp-radius-xl);
      margin-bottom: 25px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      color: var(--rp-text-dark);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      transition: var(--rp-transition);
      font-family: 'Space Grotesk', sans-serif;
    }

    .rp-mobile-filter-btn:hover {
      border-color: var(--rp-primary-color);
      color: var(--rp-primary-color);
      transform: translateY(-2px);
      box-shadow: var(--rp-shadow-md);
    }

    /* Animations */
    @keyframes ppr-slideInDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes ppr-slideInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes ppr-slideInLeft {
      from {
        opacity: 0;
        transform: translateX(-30px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes ppr-slideInRight {
      from {
        opacity: 0;
        transform: translateX(30px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes ppr-fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    /* Scroll to top button */
    .rp-scroll-to-top {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 50px;
      height: 50px;
      background: var(--rp-primary-gradient);
      border: none;
      border-radius: 50%;
      color: white;
      font-size: 18px;
      cursor: pointer;
      transition: var(--rp-transition);
      box-shadow: var(--rp-shadow-lg);
      z-index: 1000;
      opacity: 0;
      transform: translateY(100px);
    }

    .rp-scroll-to-top.visible {
      opacity: 1;
      transform: translateY(0);
    }

    .rp-scroll-to-top:hover {
      transform: translateY(-5px) scale(1.1);
      box-shadow: var(--rp-shadow-xl);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
      .rp-content-layout {
        grid-template-columns: 280px 1fr;
        gap: 25px;
      }
      
      .rp-main-container {
        padding: 15px;
      }
    }

    @media (max-width: 768px) {
      .rp-main-container {
        padding: 12px;
      }

      .rp-content-layout {
        grid-template-columns: 1fr;
        gap: 0;
      }

      .rp-filters-sidebar {
        display: none;
      }

      .rp-mobile-filter-btn {
        display: flex;
      }

      .rp-page-header {
        padding: 25px;
      }

      .rp-search-summary {
        padding: 20px;
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }

      .rp-search-item {
        padding: 10px 16px;
      }

      .rp-results-header {
        padding: 20px;
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }

      /* Mobile Ride Card Complete Redesign */
      .rp-ride-card {
        margin-bottom: 20px;
        border-radius: var(--rp-radius-xl);
        overflow: hidden;
        box-shadow: var(--rp-shadow-lg);
        border: 1px solid var(--rp-border-light);
      }

      .rp-ride-card-inner {
        padding: 0;
      }

      /* Mobile Route Header */
      .rp-route-section {
        grid-template-columns: 1fr;
        gap: 0;
        padding: 20px;
        background: var(--rp-primary-gradient);
        color: white;
        position: relative;
        border-radius: 0;
        margin-bottom: 0;
      }

      .rp-route-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.05) 100%);
        pointer-events: none;
      }

      .rp-departure-info, .rp-arrival-info {
        padding: 0;
        background: transparent;
        border-radius: 0;
        position: relative;
        z-index: 2;
      }

      .rp-departure-info {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      }

      .rp-arrival-info {
        margin-bottom: 20px;
      }

      .rp-route-city {
        font-size: 20px;
        font-weight: 700;
        color: white;
        margin-bottom: 5px;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
      }

      .rp-route-date, .rp-route-time {
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
      }

      .rp-route-connector {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 60px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 2px;
      }

      .rp-route-connector::before {
        content: '';
        position: absolute;
        top: -8px;
        left: -6px;
        width: 16px;
        height: 16px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      }

      .rp-route-connector::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: -6px;
        width: 16px;
        height: 16px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      }

      .rp-route-line, .rp-route-arrow, .rp-route-duration {
        display: none;
      }

      .rp-price-section {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.95);
        color: var(--rp-text-dark);
        padding: 12px 16px;
        border-radius: var(--rp-radius-lg);
        min-width: auto;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(10px);
      }

      .rp-price-amount {
        font-size: 18px;
        font-weight: 800;
      }

      .rp-price-label {
        font-size: 10px;
        opacity: 0.7;
      }

      /* Mobile Details Section */
      .rp-details-grid {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 20px;
        background: var(--rp-white);
        margin-bottom: 0;
      }

      .rp-detail-card {
        background: var(--rp-secondary-color);
        padding: 15px 12px;
        border-radius: var(--rp-radius-lg);
        border: 1px solid var(--rp-border-light);
        text-align: center;
        transition: var(--rp-transition-fast);
      }

      .rp-detail-icon {
        width: 32px;
        height: 32px;
        background: var(--rp-primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--rp-text-dark);
        font-size: 14px;
        font-weight: 600;
        margin: 0 auto 8px;
      }

      .rp-detail-label {
        font-size: 11px;
        color: var(--rp-text-light);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
      }

      .rp-detail-value {
        font-size: 13px;
        color: var(--rp-text-dark);
        font-weight: 600;
        line-height: 1.2;
      }

      /* Mobile Features Section */
      .rp-features-section {
        padding: 0 20px 20px;
        background: var(--rp-white);
        margin-bottom: 0;
      }

      .rp-features-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--rp-text-dark);
        margin-bottom: 12px;
        font-family: 'Space Grotesk', sans-serif;
      }

      .rp-features-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
      }

      .rp-feature-tag {
        padding: 6px 12px;
        font-size: 11px;
        border-radius: var(--rp-radius-md);
        font-weight: 600;
        flex: 1;
        min-width: calc(33.333% - 6px);
        text-align: center;
        white-space: nowrap;
      }

      .rp-feature-tag i {
        font-size: 10px;
        margin-right: 4px;
      }

      /* Mobile Bottom Section */
      .rp-bottom-section {
        grid-template-columns: 1fr;
        gap: 15px;
        padding: 20px;
        background: linear-gradient(135deg, var(--rp-secondary-color) 0%, var(--rp-white) 100%);
        border-top: 1px solid var(--rp-border-light);
        margin: 0;
      }

      .rp-driver-section {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
        background: var(--rp-white);
        border-radius: var(--rp-radius-lg);
        border: 1px solid var(--rp-border-light);
        box-shadow: var(--rp-shadow-sm);
      }

      .rp-driver-avatar {
        width: 45px;
        height: 45px;
        font-size: 16px;
        flex-shrink: 0;
      }

      .rp-driver-info {
        flex: 1;
        min-width: 0;
      }

      .rp-driver-name {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .rp-driver-rating {
        font-size: 12px;
        color: var(--rp-text-light);
      }

      .rp-driver-meta {
        font-size: 11px;
        color: var(--rp-text-muted);
        margin-top: 2px;
      }

      .rp-actions-section {
        display: flex;
        gap: 10px;
        width: 100%;
      }

      .rp-btn {
        flex: 1;
        padding: 14px 16px;
        font-size: 13px;
        font-weight: 600;
        border-radius: var(--rp-radius-lg);
        min-width: auto;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
      }

      .rp-btn i {
        font-size: 12px;
      }

      /* Mobile Sort Section */
      .rp-sort-section {
        padding: 20px;
        margin-bottom: 20px;
      }

      .rp-sort-options {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
      }

      .rp-sort-btn {
        padding: 12px 8px;
        font-size: 12px;
        text-align: center;
        border-radius: var(--rp-radius-lg);
      }

      /* Empty State Mobile */
      .rp-empty-state {
        padding: 60px 20px;
      }

      .rp-empty-state i {
        font-size: 60px;
      }

      .rp-empty-state h3 {
        font-size: 24px;
      }

      .rp-empty-actions {
        flex-direction: column;
        align-items: center;
        gap: 12px;
      }

      .rp-empty-actions .rp-btn {
        width: 100%;
        max-width: 280px;
        padding: 16px 24px;
      }
    }

    @media (max-width: 480px) {
      .rp-main-container {
        padding: 8px;
      }

      .rp-page-header {
        padding: 20px 16px;
        margin-bottom: 20px;
      }

      .rp-search-summary {
        padding: 16px;
      }

      .rp-ride-card {
        margin-bottom: 16px;
        border-radius: var(--rp-radius-lg);
      }

      .rp-route-section {
        padding: 16px;
      }

      .rp-route-city {
        font-size: 18px;
      }

      .rp-price-section {
        top: 16px;
        right: 16px;
        padding: 10px 12px;
      }

      .rp-price-amount {
        font-size: 16px;
      }

      .rp-details-grid {
        padding: 16px;
        gap: 10px;
      }

      .rp-detail-card {
        padding: 12px 8px;
      }

      .rp-detail-icon {
        width: 28px;
        height: 28px;
        font-size: 12px;
        margin-bottom: 6px;
      }

      .rp-detail-label {
        font-size: 10px;
        margin-bottom: 3px;
      }

      .rp-detail-value {
        font-size: 12px;
      }

      .rp-features-section {
        padding: 0 16px 16px;
      }

      .rp-feature-tag {
        padding: 5px 8px;
        font-size: 10px;
        min-width: calc(50% - 4px);
      }

      .rp-bottom-section {
        padding: 16px;
        gap: 12px;
      }

      .rp-driver-section {
        padding: 12px;
      }

      .rp-driver-avatar {
        width: 40px;
        height: 40px;
        font-size: 14px;
      }

      .rp-driver-name {
        font-size: 14px;
      }

      .rp-driver-rating {
        font-size: 11px;
      }

      .rp-btn {
        padding: 12px 14px;
        font-size: 12px;
      }

      .rp-sort-section {
        padding: 16px;
        margin-bottom: 16px;
      }

      .rp-sort-options {
        grid-template-columns: 1fr;
        gap: 8px;
      }

      .rp-sort-btn {
        padding: 12px;
        font-size: 13px;
      }
    }

    /* Mobile Animation Enhancements */
    @media (max-width: 768px) {
      .rp-ride-card {
        animation: rp-mobileSlideIn 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
      }

      .rp-ride-card:nth-child(odd) {
        animation-delay: 0.1s;
      }

      .rp-ride-card:nth-child(even) {
        animation-delay: 0.2s;
      }

      @keyframes rp-mobileSlideIn {
        from {
          opacity: 0;
          transform: translateY(20px) scale(0.95);
        }
        to {
          opacity: 1;
          transform: translateY(0) scale(1);
        }
      }

      .rp-price-section {
        animation: rp-priceFloat 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.3s both;
      }

      @keyframes rp-priceFloat {
        from {
          opacity: 0;
          transform: translateY(-10px) scale(0.8);
        }
        to {
          opacity: 1;
          transform: translateY(0) scale(1);
        }
      }

      .rp-detail-card {
        transition: var(--rp-transition-fast);
      }

      .rp-detail-card:hover,
      .rp-detail-card:active {
        transform: translateY(-2px);
        box-shadow: var(--rp-shadow-md);
        background: var(--rp-primary-light);
      }

      .rp-btn:active {
        transform: scale(0.98);
        transition: transform 0.1s ease;
      }

      /* Improved touch feedback */
      .rp-ride-card:active {
        transform: scale(0.99);
        transition: transform 0.1s ease;
      }

      .rp-feature-tag:active {
        transform: scale(0.95);
        transition: transform 0.1s ease;
      }
    }

    /* Loading States */
    .rp-loading-spinner {
      width: 50px;
      height: 50px;
      border: 4px solid var(--rp-border-light);
      border-top: 4px solid var(--rp-primary-color);
      border-radius: 50%;
      animation: rp-spin 1s linear infinite;
      margin: 40px auto;
    }

    @keyframes rp-spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Debug styles */
    .rp-debug-info {
      margin-top: 30px;
      padding: 20px;
      background: var(--rp-secondary-color);
      border: 2px solid var(--rp-border-color);
      border-radius: var(--rp-radius-lg);
      font-family: 'Courier New', monospace;
      font-size: 12px;
      color: var(--rp-text-medium);
    }

    .rp-debug-info h4 {
      margin-bottom: 15px;
      color: var(--rp-text-dark);
      font-family: 'Space Grotesk', sans-serif;
    }

    .rp-debug-info pre {
      background: var(--rp-white);
      padding: 15px;
      border-radius: var(--rp-radius-sm);
      overflow-x: auto;
      border: 1px solid var(--rp-border-color);
    }

    /* Add these new styles for the Book Now button */
    .ppr-btn-book-now-unique {
      background: var(--ppr-accent-gradient) !important;
      color: white !important;
      border: none !important;
      padding: 14px 28px !important;
      font-size: 15px !important;
      font-weight: 700 !important;
      letter-spacing: 0.5px !important;
      text-transform: uppercase !important;
      border-radius: var(--ppr-radius-lg) !important;
      box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
      position: relative !important;
      overflow: hidden !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 10px !important;
      min-width: 160px !important;
      text-decoration: none !important;
      font-family: 'Space Grotesk', sans-serif !important;
    }

    .ppr-btn-book-now-unique:hover {
      transform: translateY(-3px) scale(1.02) !important;
      box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4) !important;
      text-decoration: none !important;
    }

    .ppr-btn-book-now-unique:active {
      transform: translateY(-1px) scale(0.98) !important;
    }

    .ppr-btn-book-now-unique::before {
      content: '' !important;
      position: absolute !important;
      top: 0 !important;
      left: -100% !important;
      width: 100% !important;
      height: 100% !important;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent) !important;
      transition: 0.5s !important;
    }

    .ppr-btn-book-now-unique:hover::before {
      left: 100% !important;
    }

    .ppr-btn-book-now-unique i {
      font-size: 16px !important;
      transition: transform 0.3s ease !important;
    }

    .ppr-btn-book-now-unique:hover i {
      transform: scale(1.2) !important;
    }

    /* Enhanced mobile responsiveness */
    @media (max-width: 768px) {
      .ppr-btn-book-now-unique {
        width: 100% !important;
        max-width: none !important;
        padding: 16px 24px !important;
        font-size: 14px !important;
        border-radius: var(--ppr-radius-xl) !important;
      }

      .ppr-actions-section-unique {
        padding: 0 20px 20px !important;
      }

      .ppr-ride-card-unique {
        margin-bottom: 20px !important;
        border-radius: var(--ppr-radius-xl) !important;
        background: white !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
      }

      .ppr-ride-card-inner-unique {
        display: flex !important;
        flex-direction: column !important;
        gap: 15px !important;
      }
    }

    /* Additional responsive improvements */
    @media (max-width: 480px) {
      .ppr-btn-book-now-unique {
        padding: 14px 20px !important;
        font-size: 13px !important;
      }

      .ppr-ride-card-unique {
        margin: 10px !important;
        border-radius: var(--ppr-radius-lg) !important;
      }

      .ppr-actions-section-unique {
        padding: 0 15px 15px !important;
      }
    }

    /* Prevent button text wrapping */
    .ppr-btn-book-now-unique span {
      white-space: nowrap !important;
    }

    /* Add loading state */
    .ppr-btn-book-now-unique.loading {
      pointer-events: none !important;
      opacity: 0.8 !important;
    }

    .ppr-btn-book-now-unique.loading i {
      animation: spin 1s linear infinite !important;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Add success state */
    .ppr-btn-book-now-unique.success {
      background: var(--ppr-accent-color) !important;
    }

    .ppr-btn-book-now-unique.success i {
      transform: scale(1.2) !important;
    }

    /* Add these new responsive styles after the existing styles */

    /* Tablet Specific Styles (768px - 1024px) */
    @media (min-width: 768px) and (max-width: 1024px) {
      .ppr-content-layout-unique {
        grid-template-columns: 1fr !important;
        gap: 30px !important;
      }

      .ppr-filters-sidebar-unique {
        position: relative !important;
        top: 0 !important;
        width: 100% !important;
        max-height: none !important;
        margin-bottom: 30px !important;
      }

      .ppr-filter-options-unique {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 15px !important;
      }

      .ppr-filter-option-unique {
        text-align: center !important;
        justify-content: center !important;
      }

      .ppr-sort-section-unique {
        position: sticky !important;
        top: 20px !important;
        z-index: 10 !important;
        margin-bottom: 30px !important;
      }

      .ppr-sort-options-unique {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 10px !important;
      }

      .ppr-ride-card-unique {
        margin: 0 0 25px 0 !important;
      }

      .ppr-route-section-unique {
        padding: 25px !important;
      }

      .ppr-details-grid-unique {
        grid-template-columns: repeat(2, 1fr) !important;
      }
    }

    /* Enhanced Mobile Styles (up to 767px) */
    @media (max-width: 767px) {
      .ppr-main-container-unique {
        padding: 10px !important;
      }

      .ppr-page-header-unique {
        padding: 20px !important;
        margin-bottom: 20px !important;
      }

      .ppr-page-title-unique {
        font-size: 24px !important;
        margin-bottom: 10px !important;
      }

      .ppr-page-subtitle-unique {
        font-size: 14px !important;
        margin-bottom: 20px !important;
      }

      .ppr-search-summary-unique {
        flex-direction: column !important;
        gap: 10px !important;
        padding: 15px !important;
      }

      .ppr-search-item-unique {
        width: 100% !important;
        justify-content: flex-start !important;
      }

      .ppr-ride-card-unique {
        border-radius: 15px !important;
        margin: 0 0 20px 0 !important;
      }

      .ppr-route-section-unique {
        grid-template-columns: 1fr !important;
        gap: 20px !important;
        padding: 20px !important;
        position: relative !important;
      }

      .ppr-departure-info-unique, .ppr-arrival-info-unique {
        text-align: left !important;
        padding-left: 50px !important;
      }

      .ppr-route-connector-unique {
        position: absolute !important;
        left: 20px !important;
        top: 50% !important;
        height: calc(100% - 40px) !important;
        width: 4px !important;
        background: rgba(255, 255, 255, 0.2) !important;
        transform: translateY(-50%) !important;
      }

      .ppr-route-connector-unique::before,
      .ppr-route-connector-unique::after {
        content: '' !important;
        position: absolute !important;
        width: 12px !important;
        height: 12px !important;
        background: white !important;
        border-radius: 50% !important;
        left: -4px !important;
      }

      .ppr-route-connector-unique::before {
        top: 0 !important;
      }

      .ppr-route-connector-unique::after {
        bottom: 0 !important;
      }

      .ppr-price-section-unique {
        position: absolute !important;
        top: 20px !important;
        right: 20px !important;
        padding: 10px 15px !important;
        min-width: auto !important;
        border-radius: 12px !important;
      }

      .ppr-details-grid-unique {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
        padding: 15px !important;
      }

      .ppr-detail-card-unique {
        padding: 12px !important;
      }

      .ppr-detail-icon-unique {
        width: 32px !important;
        height: 32px !important;
        font-size: 14px !important;
      }

      .ppr-features-section-unique {
        padding: 15px !important;
      }

      .ppr-features-grid-unique {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 8px !important;
      }

      .ppr-feature-tag-unique {
        width: 100% !important;
        justify-content: center !important;
        padding: 8px !important;
      }

      .ppr-bottom-section-unique {
        grid-template-columns: 1fr !important;
        gap: 15px !important;
        padding: 15px !important;
      }

      .ppr-driver-section-unique {
        background: var(--ppr-secondary-color) !important;
        border-radius: 12px !important;
        padding: 12px !important;
      }

      .ppr-actions-section-unique {
        padding: 0 !important;
      }

      .ppr-btn-book-now-unique {
        width: 100% !important;
        height: 48px !important;
        font-size: 14px !important;
      }
    }

    /* Small Mobile Devices (up to 375px) */
    @media (max-width: 375px) {
      .ppr-details-grid-unique {
        grid-template-columns: 1fr !important;
      }

      .ppr-features-grid-unique {
        grid-template-columns: 1fr !important;
      }

      .ppr-route-city-unique {
        font-size: 16px !important;
      }

      .ppr-route-date-unique,
      .ppr-route-time-unique {
        font-size: 13px !important;
      }

      .ppr-price-section-unique {
        padding: 8px 12px !important;
      }

      .ppr-price-amount-unique {
        font-size: 18px !important;
      }
    }

    /* Add smooth transitions for better mobile experience */
    .ppr-ride-card-unique {
      transition: transform 0.3s ease, box-shadow 0.3s ease !important;
    }

    .ppr-ride-card-unique:active {
      transform: scale(0.98) !important;
    }

    /* Add touch feedback */
    @media (hover: none) {
      .ppr-btn-book-now-unique:active {
        transform: scale(0.95) !important;
        transition: transform 0.1s ease !important;
      }

      .ppr-detail-card-unique:active {
        background: var(--ppr-primary-light) !important;
        transform: scale(0.98) !important;
      }
    }

    /* Improve scrolling performance */
    .ppr-rides-container-unique {
      -webkit-overflow-scrolling: touch !important;
      scroll-behavior: smooth !important;
    }

    /* Add loading skeleton animation for better perceived performance */
    @keyframes ppr-skeleton-loading {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    .ppr-loading-skeleton {
      background: linear-gradient(90deg, 
        var(--ppr-border-light) 25%, 
        var(--ppr-secondary-color) 50%, 
        var(--ppr-border-light) 75%
      ) !important;
      background-size: 200% 100% !important;
      animation: ppr-skeleton-loading 1.5s infinite !important;
    }

    /* Add pull-to-refresh visual indicator */
    .ppr-pull-indicator {
      height: 0 !important;
      overflow: hidden !important;
      transition: height 0.3s ease !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      color: var(--ppr-text-medium) !important;
      font-size: 14px !important;
    }

    .ppr-pull-indicator.active {
      height: 50px !important;
    }

    /* Optimize images and icons for mobile */
    .ppr-driver-avatar-unique {
      -webkit-backface-visibility: hidden !important;
      backface-visibility: hidden !important;
    }

    /* Add mobile gesture hints */
    .ppr-gesture-hint {
      opacity: 0 !important;
      transition: opacity 0.3s ease !important;
      position: absolute !important;
      bottom: 20px !important;
      left: 50% !important;
      transform: translateX(-50%) !important;
      background: rgba(0, 0, 0, 0.8) !important;
      color: white !important;
      padding: 8px 16px !important;
      border-radius: 20px !important;
      font-size: 12px !important;
      pointer-events: none !important;
      z-index: 1000 !important;
    }

    .ppr-gesture-hint.visible {
      opacity: 1 !important;
    }

    /* Update the route section styles for mobile */
    @media (max-width: 767px) {
      .ppr-route-section-unique {
        grid-template-columns: 1fr !important;
        gap: 0 !important;
        padding: 25px !important;
        background: linear-gradient(135deg, var(--ppr-primary-color) 0%, #ff9500 100%) !important;
        position: relative !important;
        border-radius: 20px !important;
        overflow: hidden !important;
        box-shadow: 0 8px 20px rgba(255, 191, 0, 0.2) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
      }

      .ppr-departure-info-unique, 
      .ppr-arrival-info-unique {
        background: rgba(255, 255, 255, 0.15) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        border-radius: 15px !important;
        padding: 20px !important;
        margin: 0 0 15px 0 !important;
        position: relative !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        text-align: left !important;
        transition: transform 0.3s ease, box-shadow 0.3s ease !important;
      }

      .ppr-departure-info-unique:hover, 
      .ppr-arrival-info-unique:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
        transform: translateY(-2px) !important;
      }

      .ppr-arrival-info-unique {
        margin-bottom: 0 !important;
      }

      .ppr-route-city-unique {
        color: white !important;
        font-size: 20px !important;
        font-weight: 700 !important;
        margin-bottom: 8px !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        font-family: 'Space Grotesk', sans-serif !important;
      }

      .ppr-departure-info-unique .ppr-route-city-unique::before {
        content: '\f3c5' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 900 !important;
        font-size: 18px !important;
        color: white !important;
        background: rgba(255, 255, 255, 0.2) !important;
        padding: 8px !important;
        border-radius: 50% !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
      }

      .ppr-arrival-info-unique .ppr-route-city-unique::before {
        content: '\f041' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 900 !important;
        font-size: 18px !important;
        color: white !important;
        background: rgba(255, 255, 255, 0.2) !important;
        padding: 8px !important;
        border-radius: 50% !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
      }

      .ppr-route-date-unique,
      .ppr-route-time-unique {
        color: rgba(255, 255, 255, 0.9) !important;
        font-size: 14px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        margin-top: 4px !important;
        font-weight: 500 !important;
      }

      .ppr-route-date-unique::before {
        content: '\f133' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 400 !important;
        font-size: 14px !important;
        opacity: 0.9 !important;
      }

      .ppr-route-time-unique::before {
        content: '\f017' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 400 !important;
        font-size: 14px !important;
        opacity: 0.9 !important;
      }

      /* Remove the connector line */
      .ppr-route-connector-unique {
        display: none !important;
      }

      /* Enhanced price section */
      .ppr-price-section-unique {
        position: absolute !important;
        top: 15px !important;
        right: 15px !important;
        background: rgba(255, 255, 255, 0.95) !important;
        padding: 10px 15px !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
      }

      .ppr-price-amount-unique {
        color: var(--ppr-primary-color) !important;
        font-size: 20px !important;
        font-weight: 800 !important;
        font-family: 'Space Grotesk', sans-serif !important;
      }

      .ppr-price-label-unique {
        color: #64748b !important;
        font-size: 11px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        font-weight: 600 !important;
      }

      /* Add subtle pattern overlay */
      .ppr-route-section-unique::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        background-image: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%) !important;
        background-size: 20px 20px !important;
        opacity: 0.5 !important;
        pointer-events: none !important;
      }

      /* Add hover/active states */
      .ppr-departure-info-unique:active,
      .ppr-arrival-info-unique:active {
        transform: scale(0.98) !important;
        transition: transform 0.2s ease !important;
      }

      /* Add separation between cards */
      .ppr-departure-info-unique::after {
        content: '\f078' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 900 !important;
        position: absolute !important;
        bottom: -25px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        color: rgba(255, 255, 255, 0.5) !important;
        font-size: 16px !important;
        z-index: 2 !important;
      }
    }

    /* Additional enhancements for very small screens */
    @media (max-width: 375px) {
      .ppr-route-section-unique {
        padding: 20px !important;
        margin: 0 10px !important;
      }

      .ppr-departure-info-unique,
      .ppr-arrival-info-unique {
        padding: 15px !important;
      }

      .ppr-route-city-unique {
        font-size: 18px !important;
      }

      .ppr-route-date-unique,
      .ppr-route-time-unique {
        font-size: 13px !important;
      }

      .ppr-price-section-unique {
        padding: 8px 12px !important;
      }

      .ppr-price-amount-unique {
        font-size: 18px !important;
      }
    }

    /* Enhanced Mobile Styles */
    @media (max-width: 767px) {
      .ppr-route-section-unique {
        grid-template-columns: 1fr !important;
        gap: 15px !important;
        padding: 0 !important;
        background: transparent !important;
        position: relative !important;
        border-radius: 0 !important;
        overflow: visible !important;
        box-shadow: none !important;
        border: none !important;
      }

      .ppr-departure-info-unique, 
      .ppr-arrival-info-unique {
        background: var(--ppr-primary-gradient) !important;
        border-radius: 20px !important;
        padding: 20px !important;
        margin: 0 15px 15px 15px !important;
        position: relative !important;
        border: none !important;
        box-shadow: 0 4px 15px rgba(255, 191, 0, 0.2) !important;
        transition: transform 0.3s ease !important;
      }

      .ppr-departure-info-unique {
        margin-bottom: 40px !important;
      }

      .ppr-route-city-unique {
        color: var(--ppr-text-dark) !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        margin-bottom: 12px !important;
        display: flex !important;
        align-items: flex-start !important;
        gap: 12px !important;
        line-height: 1.3 !important;
        font-family: 'Space Grotesk', sans-serif !important;
      }

      .ppr-departure-info-unique .ppr-route-city-unique::before,
      .ppr-arrival-info-unique .ppr-route-city-unique::before {
        content: '\f3c5' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 900 !important;
        font-size: 20px !important;
        color: var(--ppr-text-dark) !important;
        background: white !important;
        padding: 10px !important;
        border-radius: 12px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
        min-width: 40px !important;
        height: 40px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
      }

      .ppr-arrival-info-unique .ppr-route-city-unique::before {
        content: '\f041' !important;
      }

      .ppr-route-date-unique,
      .ppr-route-time-unique {
        color: var(--ppr-text-dark) !important;
        font-size: 15px !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        margin-top: 8px !important;
        margin-left: 52px !important;
        font-weight: 500 !important;
        opacity: 0.8 !important;
      }

      .ppr-route-date-unique::before {
        content: '\f133' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 400 !important;
        font-size: 15px !important;
      }

      .ppr-route-time-unique::before {
        content: '\f017' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 400 !important;
        font-size: 15px !important;
      }

      /* Remove the connector line */
      .ppr-route-connector-unique {
        display: none !important;
      }

      /* Enhanced price section */
      .ppr-price-section-unique {
        position: absolute !important;
        top: 20px !important;
        right: 20px !important;
        background: white !important;
        padding: 8px 15px !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
      }

      .ppr-price-amount-unique {
        color: var(--ppr-text-dark) !important;
        font-size: 18px !important;
        font-weight: 800 !important;
        font-family: 'Space Grotesk', sans-serif !important;
      }

      .ppr-price-label-unique {
        color: var(--ppr-text-medium) !important;
        font-size: 11px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        font-weight: 600 !important;
      }

      /* Add separation between cards */
      .ppr-departure-info-unique::after {
        content: '\f078' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 900 !important;
        position: absolute !important;
        bottom: -30px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        color: var(--ppr-text-medium) !important;
        font-size: 16px !important;
        z-index: 2 !important;
        background: white !important;
        width: 30px !important;
        height: 30px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 50% !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
      }

      /* Add hover effects */
      .ppr-departure-info-unique:active,
      .ppr-arrival-info-unique:active {
        transform: scale(0.98) !important;
      }

      /* Add subtle pattern overlay */
      .ppr-departure-info-unique::before,
      .ppr-arrival-info-unique::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='rgba(0,0,0,0.03)' fill-rule='evenodd'/%3E%3C/svg%3E") !important;
        background-size: 30px 30px !important;
        opacity: 1 !important;
        pointer-events: none !important;
        border-radius: 20px !important;
      }
    }

    /* Additional enhancements for very small screens */
    @media (max-width: 375px) {
      .ppr-departure-info-unique,
      .ppr-arrival-info-unique {
        margin: 0 10px 15px 10px !important;
        padding: 15px !important;
      }

      .ppr-route-city-unique {
        font-size: 16px !important;
      }

      .ppr-route-date-unique,
      .ppr-route-time-unique {
        font-size: 13px !important;
        margin-left: 48px !important;
      }

      .ppr-price-section-unique {
        padding: 6px 12px !important;
        top: 15px !important;
        right: 15px !important;
      }

      .ppr-price-amount-unique {
        font-size: 16px !important;
      }

      .ppr-departure-info-unique .ppr-route-city-unique::before,
      .ppr-arrival-info-unique .ppr-route-city-unique::before {
        padding: 8px !important;
        min-width: 36px !important;
        height: 36px !important;
        font-size: 16px !important;
      }
    }

    @media (max-width: 767px) {
      /* Enhanced Price Section Styles */
      .ppr-price-section-unique {
        position: relative !important;
        top: auto !important;
        right: auto !important;
        margin: 15px 0 5px 52px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        background: linear-gradient(135deg, var(--ppr-primary-color) 0%, #ff9500 100%) !important;
        padding: 10px 20px !important;
        border-radius: 50px !important;
        box-shadow: 0 4px 15px rgba(255, 191, 0, 0.25) !important;
        border: 2px solid rgba(255, 255, 255, 0.1) !important;
      }

      .ppr-price-amount-unique {
        color: white !important;
        font-size: 20px !important;
        font-weight: 800 !important;
        font-family: 'Space Grotesk', sans-serif !important;
        letter-spacing: 0.5px !important;
      }

      .ppr-price-amount-unique::before {
        content: '₹' !important;
        margin-right: 2px !important;
        font-size: 18px !important;
        font-weight: 600 !important;
      }

      .ppr-price-label-unique {
        color: rgba(255, 255, 255, 0.9) !important;
        font-size: 12px !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;
        font-weight: 600 !important;
        background: rgba(255, 255, 255, 0.2) !important;
        padding: 4px 8px !important;
        border-radius: 20px !important;
      }

      /* Enhanced Card Styles */
      .ppr-departure-info-unique, 
      .ppr-arrival-info-unique {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
        border: 1px solid rgba(255, 191, 0, 0.1) !important;
        border-radius: 20px !important;
        padding: 20px !important;
        margin: 0 15px 15px 15px !important;
        position: relative !important;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05) !important;
        transition: transform 0.3s ease, box-shadow 0.3s ease !important;
      }

      .ppr-departure-info-unique:hover, 
      .ppr-arrival-info-unique:hover {
        box-shadow: 0 12px 30px rgba(255, 191, 0, 0.15) !important;
      }

      .ppr-route-city-unique {
        color: #2d3748 !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        margin-bottom: 12px !important;
        display: flex !important;
        align-items: flex-start !important;
        gap: 12px !important;
        line-height: 1.3 !important;
      }

      .ppr-departure-info-unique .ppr-route-city-unique::before,
      .ppr-arrival-info-unique .ppr-route-city-unique::before {
        content: '\f3c5' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 900 !important;
        font-size: 18px !important;
        color: var(--ppr-primary-color) !important;
        background: rgba(255, 191, 0, 0.1) !important;
        padding: 12px !important;
        border-radius: 12px !important;
        min-width: 42px !important;
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 4px 12px rgba(255, 191, 0, 0.15) !important;
      }

      .ppr-route-date-unique,
      .ppr-route-time-unique {
        color: #4a5568 !important;
        font-size: 14px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        margin-top: 8px !important;
        margin-left: 54px !important;
        font-weight: 500 !important;
      }

      .ppr-route-date-unique::before,
      .ppr-route-time-unique::before {
        color: var(--ppr-primary-color) !important;
        opacity: 0.8 !important;
      }

      /* Enhanced Separator */
      .ppr-departure-info-unique::after {
        content: '\f078' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 900 !important;
        position: absolute !important;
        bottom: -28px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        color: var(--ppr-primary-color) !important;
        font-size: 14px !important;
        z-index: 2 !important;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
        width: 28px !important;
        height: 28px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 50% !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        border: 2px solid rgba(255, 191, 0, 0.1) !important;
      }

      /* Add a subtle accent border */
      .ppr-departure-info-unique,
      .ppr-arrival-info-unique {
        position: relative !important;
        overflow: hidden !important;
      }

      .ppr-departure-info-unique::before,
      .ppr-arrival-info-unique::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 4px !important;
        height: 100% !important;
        background: linear-gradient(135deg, var(--ppr-primary-color) 0%, #ff9500 100%) !important;
        border-radius: 4px 0 0 4px !important;
      }

      /* Additional Enhancements */
      .ppr-route-section-unique {
        background: transparent !important;
        padding: 20px 0 !important;
      }

      /* Seat Availability Badge */
      .ppr-seats-available-unique {
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        background: rgba(16, 185, 129, 0.1) !important;
        color: #047857 !important;
        padding: 6px 12px !important;
        border-radius: 20px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        margin: 12px 0 0 52px !important;
      }

      .ppr-seats-available-unique::before {
        content: '\f5e4' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 900 !important;
        font-size: 12px !important;
        margin-right: 2px !important;
      }
    }

    /* Additional enhancements for very small screens */
    @media (max-width: 375px) {
      .ppr-price-section-unique {
        margin: 12px 0 5px 48px !important;
        padding: 8px 16px !important;
      }

      .ppr-price-amount-unique {
        font-size: 18px !important;
      }

      .ppr-price-label-unique {
        font-size: 11px !important;
        padding: 3px 6px !important;
      }

      .ppr-seats-available-unique {
        margin: 10px 0 0 48px !important;
        font-size: 12px !important;
      }
    }

    @media (max-width: 767px) {
      /* Refined Card Styles */
      .ppr-departure-info-unique, 
      .ppr-arrival-info-unique {
        background: #ffffff !important;
        border: 1px solid rgba(255, 191, 0, 0.08) !important;
        border-radius: 16px !important;
        padding: 16px !important;
        margin: 0 12px 12px 12px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03) !important;
        transition: all 0.2s ease !important;
      }

      /* Refined Location Text */
      .ppr-route-city-unique {
        color: #2d3748 !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        margin-bottom: 8px !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        line-height: 1.4 !important;
        letter-spacing: -0.2px !important;
      }

      /* Refined Location Icons */
      .ppr-departure-info-unique .ppr-route-city-unique::before,
      .ppr-arrival-info-unique .ppr-route-city-unique::before {
        content: '\f3c5' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 900 !important;
        font-size: 15px !important;
        color: var(--ppr-primary-color) !important;
        background: rgba(255, 191, 0, 0.08) !important;
        padding: 10px !important;
        border-radius: 10px !important;
        min-width: 35px !important;
        height: 35px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 2px 8px rgba(255, 191, 0, 0.1) !important;
      }

      /* Refined Date and Time */
      .ppr-route-date-unique,
      .ppr-route-time-unique {
        color: #64748b !important;
        font-size: 13px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin-top: 6px !important;
        margin-left: 45px !important;
        font-weight: 400 !important;
        margin-right: 15px !important;
      }

      .ppr-route-date-unique::before,
      .ppr-route-time-unique::before {
        color: var(--ppr-primary-color) !important;
        opacity: 0.7 !important;
        font-size: 13px !important;
      }

      /* Refined Price Section */
      .ppr-price-section-unique {
        margin: 12px 0 5px 45px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        background: linear-gradient(135deg, var(--ppr-primary-color) 0%, #ff9500 100%) !important;
        padding: 8px 16px !important;
        border-radius: 25px !important;
        box-shadow: 0 2px 10px rgba(255, 191, 0, 0.15) !important;
      }

      .ppr-price-amount-unique {
        color: white !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        letter-spacing: 0.2px !important;
      }

      .ppr-price-amount-unique::before {
        content: '₹' !important;
        margin-right: 1px !important;
        font-size: 15px !important;
        font-weight: 500 !important;
      }

      .ppr-price-label-unique {
        color: rgba(255, 255, 255, 0.95) !important;
        font-size: 11px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        font-weight: 500 !important;
        background: rgba(255, 255, 255, 0.15) !important;
        padding: 3px 8px !important;
        border-radius: 12px !important;
      }

      /* Refined Separator */
      .ppr-departure-info-unique::after {
        content: '\f107' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 900 !important;
        position: absolute !important;
        bottom: -24px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        color: #94a3b8 !important;
        font-size: 12px !important;
        z-index: 2 !important;
        background: #ffffff !important;
        width: 24px !important;
        height: 24px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 50% !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
      }

      /* Refined Accent Border */
      .ppr-departure-info-unique::before,
      .ppr-arrival-info-unique::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 3px !important;
        height: 100% !important;
        background: linear-gradient(135deg, var(--ppr-primary-color) 0%, #ff9500 100%) !important;
        border-radius: 3px 0 0 3px !important;
        opacity: 0.8 !important;
      }

      /* Refined Seats Badge */
      .ppr-seats-available-unique {
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        background: rgba(16, 185, 129, 0.08) !important;
        color: #047857 !important;
        padding: 4px 10px !important;
        border-radius: 12px !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        margin: 10px 0 0 45px !important;
      }

      .ppr-seats-available-unique::before {
        content: '\f5e4' !important;
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 900 !important;
        font-size: 11px !important;
        margin-right: 2px !important;
      }
    }

    /* Refined styles for very small screens */
    @media (max-width: 375px) {
      .ppr-departure-info-unique, 
      .ppr-arrival-info-unique {
        padding: 14px !important;
        margin: 0 10px 12px 10px !important;
      }

      .ppr-route-city-unique {
        font-size: 15px !important;
      }

      .ppr-route-date-unique,
      .ppr-route-time-unique {
        font-size: 12px !important;
        margin-left: 42px !important;
      }

      .ppr-price-section-unique {
        margin: 10px 0 5px 42px !important;
        padding: 6px 12px !important;
      }

      .ppr-price-amount-unique {
        font-size: 15px !important;
      }

      .ppr-seats-available-unique {
        margin: 8px 0 0 42px !important;
        font-size: 11px !important;
        padding: 3px 8px !important;
      }
    }

    .ppr-vehicle-filter-unique {
      background: #fff;
      padding: 1.5rem;
      border-radius: 15px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      margin-bottom: 2rem;
    }

    .vehicle-filter-container {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      justify-content: center;
      align-items: center;
    }

    .vehicle-type-btn {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 1rem;
      min-width: 100px;
      border-radius: 12px;
      background: #f8f9fa;
      border: 2px solid transparent;
      transition: all 0.3s ease;
      text-decoration: none;
      color: #495057;
    }

    .vehicle-type-btn:hover {
      transform: translateY(-2px);
      background: #fff;
      border-color: rgba(255, 193, 7, 0.3);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .vehicle-type-btn.active {
      background: #fff;
      border-color: #ffc107;
      color: #ffc107;
      box-shadow: 0 4px 15px rgba(255, 193, 7, 0.15);
    }

    .vehicle-icon {
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
      background: #fff;
      margin-bottom: 0.5rem;
      transition: all 0.3s ease;
    }

    .vehicle-icon i {
      font-size: 1.5rem;
      color: #495057;
      transition: all 0.3s ease;
    }

    .vehicle-type-btn.active .vehicle-icon {
      background: rgba(255, 193, 7, 0.1);
    }

    .vehicle-type-btn.active .vehicle-icon i {
      color: #ffc107;
    }

    .vehicle-type-btn span {
      font-size: 0.9rem;
      font-weight: 500;
      margin-top: 0.5rem;
    }

    /* Sub-options styling */
    #carOptions, #goodsOptions {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(255, 193, 7, 0.1);
      padding: 1rem;
      min-width: 200px;
    }

    .sub-option {
      display: flex;
      align-items: center;
      gap: 0.8rem;
      padding: 0.8rem 1rem;
      border-radius: 8px;
      color: #495057;
      text-decoration: none;
      transition: all 0.3s ease;
      margin-bottom: 0.5rem;
    }

    .sub-option:last-child {
      margin-bottom: 0;
    }

    .sub-option:hover {
      background: #f8f9fa;
      color: #ffc107;
    }

    .sub-option.active {
      background: rgba(255, 193, 7, 0.1);
      color: #ffc107;
    }

    .sub-option i {
      font-size: 1.2rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .vehicle-filter-container {
        gap: 0.8rem;
      }

      .vehicle-type-btn {
        min-width: 90px;
        padding: 0.8rem;
      }

      .vehicle-icon {
        width: 40px;
        height: 40px;
      }

      .vehicle-icon i {
        font-size: 1.2rem;
      }

      .vehicle-type-btn span {
        font-size: 0.8rem;
      }
    }

    @media (max-width: 480px) {
      .ppr-vehicle-filter-unique {
        padding: 1rem;
      }

      .vehicle-filter-container {
        gap: 0.5rem;
      }

      .vehicle-type-btn {
        min-width: 80px;
        padding: 0.6rem;
      }

      .vehicle-icon {
        width: 35px;
        height: 35px;
      }

      .vehicle-icon i {
        font-size: 1rem;
      }

      .vehicle-type-btn span {
        font-size: 0.75rem;
      }

      #carOptions, #goodsOptions {
        min-width: 180px;
      }
    }
  </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="ppr-body-unique" class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">


  <div class="ppr-main-container-unique">
    <!-- Page Header -->
    <div class="ppr-page-header-unique">
      <nav class="ppr-breadcrumb-unique">
        <div class="ppr-breadcrumb-item-unique">
          <i class="fas fa-home"></i>
          <span>Home</span>
        </div>
        <div class="ppr-breadcrumb-item-unique">
          <span>Search</span>
        </div>
        <div class="ppr-breadcrumb-item-unique">
          <span>Available Rides</span>
        </div>
      </nav>

      <h1 class="ppr-page-title-unique">Available Rides</h1>
      <p class="ppr-page-subtitle-unique">Discover the perfect ride that matches your schedule, budget, and travel preferences with our smart filtering system.</p>

      <?php if (!empty($from_location) || !empty($to_location) || !empty($display_travel_date) || !empty($applied_filter)): ?>
      <div class="ppr-search-summary-unique">
        <?php if (!empty($from_location)): ?>
        <div class="ppr-search-item-unique">
          <i class="fas fa-map-marker-alt"></i>
          <span>From: <?= htmlspecialchars($from_location) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($to_location)): ?>
        <div class="ppr-search-item-unique">
          <i class="fas fa-location-arrow"></i>
          <span>To: <?= htmlspecialchars($to_location) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($display_travel_date)): ?>
        <div class="ppr-search-item-unique">
          <i class="far fa-calendar-alt"></i>
          <span>Date: <?= date("l, M j, Y", strtotime($display_travel_date)) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($applied_filter) && empty($display_travel_date)): ?>
        <div class="ppr-search-item-unique">
          <i class="fas fa-filter"></i>
          <span>Filter: <?= htmlspecialchars($applied_filter) ?></span>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Vehicle Type Filter -->
    <div class="ppr-vehicle-filter-unique">
      <div class="vehicle-filter-container">
        <a href="?<?= http_build_query(array_merge($_GET, ['vehicle_type' => 'Bike'])) ?>" class="vehicle-type-btn <?= isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Bike' ? 'active' : '' ?>">
          <div class="vehicle-icon">
            <i class="fas fa-motorcycle"></i>
          </div>
          <span>Bike</span>
        </a>
        
        <a href="?<?= http_build_query(array_merge($_GET, ['vehicle_type' => 'Auto Rickshaw'])) ?>" class="vehicle-type-btn <?= isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Auto Rickshaw' ? 'active' : '' ?>">
          <div class="vehicle-icon">
            <i class="fas fa-taxi"></i>
          </div>
          <span>Auto</span>
        </a>

        <div style="position: relative;">
          <a href="#" class="vehicle-type-btn" id="carButton" onclick="toggleCarOptions(event)">
            <div class="vehicle-icon">
              <i class="fas fa-car"></i>
            </div>
            <span>Car</span>
          </a>
          <div id="carOptions" style="display: none; position: absolute; left: 50%; transform: translateX(-50%); background: white; padding: 10px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); margin-top: 10px; z-index: 100; width: max-content;">
            <a href="?<?= http_build_query(array_merge($_GET, ['vehicle_type' => 'Car-Pooling'])) ?>" class="vehicle-type-btn <?= isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Car-Pooling' ? 'active' : '' ?>" style="margin-bottom: 10px;">
              <div class="vehicle-icon">
                <i class="fas fa-users"></i>
              </div>
              <span>Carpooling</span>
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['vehicle_type' => 'Car-Taxi'])) ?>" class="vehicle-type-btn <?= isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Car-Taxi' ? 'active' : '' ?>">
              <div class="vehicle-icon">
                <i class="fas fa-taxi"></i>
              </div>
              <span>Taxi</span>
            </a>
          </div>
        </div>

        <div style="position: relative;">
          <a href="#" class="vehicle-type-btn" id="goodsButton" onclick="toggleGoodsOptions(event)">
            <div class="vehicle-icon">
              <i class="fas fa-truck"></i>
            </div>
            <span>Goods</span>
          </a>
          <div id="goodsOptions" style="display: none; position: absolute; left: 50%; transform: translateX(-50%); background: white; padding: 10px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); z-index: 100; margin-top: 10px;">
            <a href="?<?= http_build_query(array_merge($_GET, ['vehicle_type' => 'Goods-7ft'])) ?>" class="sub-option <?= isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Goods-7ft' ? 'active' : '' ?>">
              <i class="fas fa-truck-pickup"></i>
              <span>7ft Vehicle</span>
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['vehicle_type' => 'Goods-8ft'])) ?>" class="sub-option <?= isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Goods-8ft' ? 'active' : '' ?>">
              <i class="fas fa-truck-moving"></i>
              <span>8ft Vehicle</span>
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['vehicle_type' => 'Goods-3Wheeler'])) ?>" class="sub-option <?= isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Goods-3Wheeler' ? 'active' : '' ?>">
              <i class="fas fa-truck-loading"></i>
              <span>3 Wheeler Cargo</span>
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['vehicle_type' => 'Goods-Tata407'])) ?>" class="sub-option <?= isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Goods-Tata407' ? 'active' : '' ?>">
              <i class="fas fa-truck-monster"></i>
              <span>Tata 407</span>
            </a>
          </div>
        </div>

        <a href="?<?= http_build_query(array_merge($_GET, ['vehicle_type' => 'Bus'])) ?>" class="vehicle-type-btn <?= isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Bus' ? 'active' : '' ?>">
          <div class="vehicle-icon">
            <i class="fas fa-bus"></i>
          </div>
          <span>Bus</span>
        </a>
      </div>
    </div>

    <div class="ppr-content-layout-unique">
      <!-- Filters Sidebar -->
      <aside class="ppr-filters-sidebar-unique">
        <div class="ppr-filters-title-unique">
          <i class="fas fa-sliders-h"></i>
          <span>Smart Filters</span>
        </div>

        <!-- Date Filters -->
        <div class="ppr-filter-section-unique">
          <div class="ppr-filter-section-title-unique">
            <i class="far fa-calendar-alt"></i>
            <span>Departure Date</span>
          </div>
          <div class="ppr-filter-options-unique">
            <?php
            // Build base URL preserving search parameters
            $current_url = strtok($_SERVER["REQUEST_URI"], '?');
            $preserved_params = [];
            if (!empty($from_location)) $preserved_params['from_location'] = $from_location;
            if (!empty($to_location)) $preserved_params['to_location'] = $to_location;

            $date_filters = [
                'all' => ['title' => 'All Dates', 'icon' => 'fas fa-infinity', 'clear' => true],
                'today' => ['title' => 'Today', 'icon' => 'fas fa-calendar-day'],
                'tomorrow' => ['title' => 'Tomorrow', 'icon' => 'far fa-calendar-plus'],
                'day_after_tomorrow' => ['title' => 'Day After Tomorrow', 'icon' => 'far fa-calendar-check'],
                'this_weekend' => ['title' => 'This Weekend', 'icon' => 'fas fa-calendar-week'],
                'next_week' => ['title' => 'Next Week', 'icon' => 'fas fa-calendar-alt'],
                'next_month' => ['title' => 'Next Month', 'icon' => 'far fa-calendar']
            ];

            foreach ($date_filters as $filter_key => $filter_data) {
                if ($filter_key === 'all') {
                    $clear_params = $preserved_params;
                    $clear_params['clear_all_filters'] = 1;
                    $filter_url = $current_url . '?' . http_build_query($clear_params);
                    $is_active = empty($current_filter) && empty($travel_date);
                } else {
                    $filter_params = $preserved_params;
                    $filter_params['filter'] = $filter_key;
                    $filter_url = $current_url . '?' . http_build_query($filter_params);
                    $is_active = $current_filter === $filter_key;
                }
                ?>
                <a href="<?= $filter_url ?>" class="ppr-filter-option-unique <?= $is_active ? 'active' : '' ?>">
                    <i class="<?= $filter_data['icon'] ?>"></i>
                    <span><?= $filter_data['title'] ?></span>
                </a>
                <?php
            }
            ?>
          </div>
        </div>

        <!-- Price Filters -->
        <div class="ppr-filter-section-unique">
          <div class="ppr-filter-section-title-unique">
            <i class="fas fa-rupee-sign"></i>
            <span>Price Range</span>
          </div>
          <div class="ppr-filter-options-unique">
            <?php
            $price_filters = [
                'all' => ['title' => 'Any Price', 'icon' => 'fas fa-infinity'],
                'under_500' => ['title' => 'Under ₹500', 'icon' => 'fas fa-coins'],
                '500_1000' => ['title' => '₹500 - ₹1000', 'icon' => 'fas fa-coins'],
                '1000_2000' => ['title' => '₹1000 - ₹2000', 'icon' => 'fas fa-coins'],
                'above_2000' => ['title' => 'Above ₹2000', 'icon' => 'fas fa-coins']
            ];

            foreach ($price_filters as $price_key => $price_data) {
                $price_params = array_merge($preserved_params, $_GET);
                if ($price_key === 'all') {
                    unset($price_params['price_filter']);
                    $is_active = empty($price_filter);
                } else {
                    $price_params['price_filter'] = $price_key;
                    $is_active = $price_filter === $price_key;
                }
                $price_url = $current_url . '?' . http_build_query($price_params);
                ?>
                <a href="<?= $price_url ?>" class="ppr-filter-option-unique <?= $is_active ? 'active' : '' ?>">
                    <i class="<?= $price_data['icon'] ?>"></i>
                    <span><?= $price_data['title'] ?></span>
                </a>
                <?php
            }
            ?>
          </div>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="ppr-main-content-unique">
        <!-- Mobile Filter Button -->
        <button class="ppr-mobile-filter-btn-unique" onclick="toggleMobileFilters()">
          <i class="fas fa-filter"></i>
          <span>Filters & Sort</span>
        </button>

        <!-- Sort Options -->
        <div class="ppr-sort-section-unique">
          <div class="ppr-sort-title-unique">Sort by:</div>
          <div class="ppr-sort-options-unique">
            <?php
            $sort_options = [
                'departure_date' => 'Earliest Departure',
                'price_low' => 'Price: Low to High',
                'price_high' => 'Price: High to Low',
                'departure_time' => 'Departure Time',
                'seats' => 'Most Seats Available'
            ];

            foreach ($sort_options as $sort_key => $sort_title) {
                $sort_params = array_merge($preserved_params, $_GET);
                $sort_params['sort_by'] = $sort_key;
                $sort_url = $current_url . '?' . http_build_query($sort_params);
                $is_active = $sort_by === $sort_key;
                ?>
                <a href="<?= $sort_url ?>" class="ppr-sort-btn-unique <?= $is_active ? 'active' : '' ?>">
                    <?= $sort_title ?>
                </a>
                <?php
            }
            ?>
          </div>
        </div>

        <!-- Results Section -->
        <div class="ppr-results-section-unique">
          <div class="ppr-results-header-unique">
            <div class="ppr-results-count-unique">
              <span class="ppr-count-unique"><?= mysqli_num_rows($result) ?></span> rides found
            </div>
          </div>

          <div class="ppr-rides-container-unique">
            <?php if (mysqli_num_rows($result) > 0): ?>
              <?php $card_index = 0; ?>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php
                $departure_date = date("l, M j", strtotime($row["departure_date"]));
                $departure_time = date("g:i A", strtotime($row["departure_time"]));
                $arrival_time = date("g:i A", strtotime($row["arrival_time"]));
                $driver_initial = strtoupper(substr($row["driver_name"], 0, 1));
                $card_index++;
                ?>
                <div class="ppr-ride-card-unique" style="animation-delay: <?= $card_index * 0.1 ?>s;">
                  <div class="ppr-ride-card-inner-unique">
                    <!-- Professional Route Section -->
                    <div class="ppr-route-section-unique">
                      <div class="ppr-departure-info-unique">
                        <div class="ppr-route-city-unique"><?= htmlspecialchars($row["departure_city"]) ?></div>
                        <div class="ppr-route-date-unique"><?= $departure_date ?></div>
                        <div class="ppr-route-time-unique"><?= $departure_time ?></div>
                      </div>
                      
                      <div class="ppr-route-connector-unique">
                        <div class="ppr-route-line-unique">
                          <div class="ppr-route-arrow-unique">
                            <i class="fas fa-chevron-right"></i>
                          </div>
                        </div>
                        <?php if (!empty($row["duration"])): ?>
                        <div class="ppr-route-duration-unique"><?= htmlspecialchars($row["duration"]) ?></div>
                        <?php endif; ?>
                      </div>
                      
                      <div class="ppr-arrival-info-unique">
                        <div class="ppr-route-city-unique"><?= htmlspecialchars($row["destination_city"]) ?></div>
                        <div class="ppr-route-date-unique"><?= $departure_date ?></div>
                        <div class="ppr-route-time-unique"><?= $arrival_time ?></div>
                      </div>
                      
                      <div class="ppr-price-section-unique">
                        <div class="ppr-price-amount-unique">₹<?= number_format($row["price"]) ?></div>
                        <div class="ppr-price-label-unique">per seat</div>
                      </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="ppr-details-grid-unique">
                      <div class="ppr-detail-card-unique">
                        <div class="ppr-detail-icon-unique">
                          <i class="fas fa-users"></i>
                        </div>
                        <div class="ppr-detail-content-unique">
                          <div class="ppr-detail-label-unique">Available Seats</div>
            <div class="ppr-detail-value-unique"><?= $row["available_seats"] ?> seats</div>
                        </div>
                      </div>
                      
                      <div class="ppr-detail-card-unique">
                        <div class="ppr-detail-icon-unique">
                          <i class="fas fa-car"></i>
                        </div>
                        <div class="ppr-detail-content-unique">
                          <div class="ppr-detail-label-unique">Vehicle</div>
                          <div class="ppr-detail-value-unique"><?= htmlspecialchars($row["vehicle_name"]) ?></div>
                        </div>
                      </div>
                      
                      <?php if (!empty($row["distance"])): ?>
                      <div class="ppr-detail-card-unique">
                        <div class="ppr-detail-icon-unique">
                          <i class="fas fa-route"></i>
                        </div>
                        <div class="ppr-detail-content-unique">
                          <div class="ppr-detail-label-unique">Distance</div>
                          <div class="ppr-detail-value-unique"><?= htmlspecialchars($row["distance"]) ?></div>
                        </div>
                      </div>
                      <?php endif; ?>
                      
                      <div class="ppr-detail-card-unique">
                        <div class="ppr-detail-icon-unique">
                          <i class="fas fa-suitcase"></i>
                        </div>
                        <div class="ppr-detail-content-unique">
                          <div class="ppr-detail-label-unique">Luggage</div>
                          <div class="ppr-detail-value-unique"><?= htmlspecialchars($row["luggage_space"]) ?></div>
                        </div>
                      </div>
                    </div>

                    <!-- Features Section -->
                    <div class="ppr-features-section-unique">
                      <div class="ppr-features-title-unique">Ride Features</div>
                      <div class="ppr-features-grid-unique">
                        <?php if ($row["has_ac"] == 1): ?>
                        <div class="ppr-feature-tag-unique positive">
                          <i class="fas fa-snowflake"></i>
                          <span>AC Available</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="ppr-feature-tag-unique <?= $row["allow_smoking"] == 1 ? 'positive' : 'negative' ?>">
                          <i class="fas fa-<?= $row["allow_smoking"] == 1 ? 'smoking' : 'smoking-ban' ?>"></i>
                          <span><?= $row["allow_smoking"] == 1 ? 'Smoking OK' : 'No Smoking' ?></span>
                        </div>
                        
                        <div class="ppr-feature-tag-unique <?= $row["pets_allowed"] == 1 ? 'positive' : 'negative' ?>">
                          <i class="fas fa-<?= $row["pets_allowed"] == 1 ? 'paw' : 'ban' ?>"></i>
                          <span><?= $row["pets_allowed"] == 1 ? 'Pets OK' : 'No Pets' ?></span>
                        </div>
                      </div>
                    </div>

                    <!-- Driver & Actions Section -->
                    <div class="ppr-bottom-section-unique">
                      <div class="ppr-driver-section-unique">
                        <div class="ppr-driver-avatar-unique">
                          <?= $driver_initial ?>
                        </div>
                        <div class="ppr-driver-info-unique">
                          <div class="ppr-driver-name-unique"><?= htmlspecialchars($row["driver_name"]) ?></div>
                          <div class="ppr-driver-rating-unique">
                            <i class="fas fa-star"></i>
                            <span>4.8 (25 reviews)</span>
                          </div>
                        </div>
                      </div>
                      
                      <div class="ppr-actions-section-unique">
                        <a href="Ridedetails.php?id=<?= $row['id'] ?>" class="ppr-btn-unique ppr-btn-book-now-unique">
                          <i class="fas fa-check-circle"></i>
                          <span>Book Now</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="ppr-empty-state-unique">
                <i class="fas fa-search-location"></i>
                <h3>No rides found</h3>
                <p>We couldn't find any rides matching your criteria. Try adjusting your search filters, exploring different dates, or check back later for new listings.</p>
                <div class="ppr-empty-actions-unique">
                  <a href="findrides.php" class="ppr-btn-unique ppr-btn-primary-unique">
                    <i class="fas fa-search"></i>
                    <span>New Search</span>
                  </a>
                  <a href="ownride.php" class="ppr-btn-unique ppr-btn-outline-unique">
                    <i class="fas fa-plus-circle"></i>
                    <span>Post Ride Request</span>
                  </a>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </main>
    </div>

    <!-- Mobile Filters Modal -->
    <div class="ppr-mobile-filters-unique" id="pprMobileFilters">
      <div class="ppr-mobile-filters-content-unique">
        <div class="ppr-mobile-filters-header-unique">
          <h3>Filters & Sort</h3>
          <button class="ppr-close-filters-unique" onclick="toggleMobileFilters()">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div style="padding: 25px;">
          <!-- Copy filters content for mobile -->
          <div class="ppr-filter-section-unique">
            <div class="ppr-filter-section-title-unique">
              <i class="far fa-calendar-alt"></i>
              <span>Departure Date</span>
            </div>
            <div class="ppr-filter-options-unique">
              <?php
              foreach ($date_filters as $filter_key => $filter_data) {
                  if ($filter_key === 'all') {
                      $clear_params = $preserved_params;
                      $clear_params['clear_all_filters'] = 1;
                      $filter_url = $current_url . '?' . http_build_query($clear_params);
                      $is_active = empty($current_filter) && empty($travel_date);
                  } else {
                      $filter_params = $preserved_params;
                      $filter_params['filter'] = $filter_key;
                      $filter_url = $current_url . '?' . http_build_query($filter_params);
                      $is_active = $current_filter === $filter_key;
                  }
                  ?>
                  <a href="<?= $filter_url ?>" class="ppr-filter-option-unique <?= $is_active ? 'active' : '' ?>">
                      <i class="<?= $filter_data['icon'] ?>"></i>
                      <span><?= $filter_data['title'] ?></span>
                  </a>
                  <?php
              }
              ?>
            </div>
          </div>

          <div class="ppr-filter-section-unique">
            <div class="ppr-filter-section-title-unique">
              <i class="fas fa-rupee-sign"></i>
              <span>Price Range</span>
            </div>
            <div class="ppr-filter-options-unique">
              <?php
              foreach ($price_filters as $price_key => $price_data) {
                  $price_params = array_merge($preserved_params, $_GET);
                  if ($price_key === 'all') {
                      unset($price_params['price_filter']);
                      $is_active = empty($price_filter);
                  } else {
                      $price_params['price_filter'] = $price_key;
                      $is_active = $price_filter === $price_key;
                  }
                  $price_url = $current_url . '?' . http_build_query($price_params);
                  ?>
                  <a href="<?= $price_url ?>" class="ppr-filter-option-unique <?= $is_active ? 'active' : '' ?>">
                      <i class="<?= $price_data['icon'] ?>"></i>
                      <span><?= $price_data['title'] ?></span>
                  </a>
                  <?php
              }
              ?>
            </div>
          </div>

          <div class="ppr-filter-section-unique">
            <div class="ppr-filter-section-title-unique">
              <i class="fas fa-sort"></i>
              <span>Sort By</span>
            </div>
            <div class="ppr-filter-options-unique">
              <?php
              foreach ($sort_options as $sort_key => $sort_title) {
                  $sort_params = array_merge($preserved_params, $_GET);
                  $sort_params['sort_by'] = $sort_key;
                  $sort_url = $current_url . '?' . http_build_query($sort_params);
                  $is_active = $sort_by === $sort_key;
                  ?>
                  <a href="<?= $sort_url ?>" class="ppr-filter-option-unique <?= $is_active ? 'active' : '' ?>">
                      <i class="fas fa-sort"></i>
                      <span><?= $sort_title ?></span>
                  </a>
                  <?php
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Scroll to Top Button -->
    <button class="ppr-scroll-to-top-unique" onclick="pprScrollToTop()">
      <i class="fas fa-arrow-up"></i>
    </button>

    <?php if (isset($_GET['debug'])): ?>
    <!-- Debug info - remove in production -->
    <div class="ppr-debug-info-unique">
        <h4>Debug Information</h4>
        <pre><?php print_r($debug_info); ?></pre>
    </div>
    <?php endif; ?>
  </div>

  <script>
    // Mobile filters functionality
    function toggleMobileFilters() {
      const mobileFilters = document.getElementById('pprMobileFilters');
      mobileFilters.classList.toggle('active');
    }

    // Close mobile filters when clicking outside
    document.getElementById('pprMobileFilters').addEventListener('click', function(e) {
      if (e.target === this) {
        toggleMobileFilters();
      }
    });

    // Scroll to top functionality
    function pprScrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }

    // Show/hide scroll to top button
    window.addEventListener('scroll', function() {
      const scrollBtn = document.querySelector('.ppr-scroll-to-top-unique');
      if (window.pageYOffset > 300) {
        scrollBtn.classList.add('visible');
      } else {
        scrollBtn.classList.remove('visible');
      }
    });

    // Enhanced animations and interactions
    document.addEventListener('DOMContentLoaded', function() {
      // Add loading states for buttons
      const buttons = document.querySelectorAll('.ppr-btn-unique, .ppr-filter-option-unique, .ppr-sort-btn-unique');
      
      buttons.forEach(button => {
        button.addEventListener('click', function() {
          if (this.href) {
            this.style.opacity = '0.7';
            this.style.pointerEvents = 'none';
            
            // Create loading spinner
            const icon = this.querySelector('i');
            if (icon) {
              icon.className = 'fas fa-spinner fa-spin';
            }
          }
        });
      });

      // Intersection Observer for card animations
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };

      const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }
        });
      }, observerOptions);

      // Observe ride cards for animation
      document.querySelectorAll('.ppr-ride-card-unique').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
        observer.observe(card);
      });

      // Enhanced hover effects for ride cards
      document.querySelectorAll('.ppr-ride-card-unique').forEach(card => {
        card.addEventListener('mouseenter', function() {
          this.style.transform = 'translateY(-5px)';
          this.style.boxShadow = 'var(--ppr-shadow-xl)';
        });

        card.addEventListener('mouseleave', function() {
          this.style.transform = 'translateY(0)';
          this.style.boxShadow = 'var(--ppr-shadow-md)';
        });
      });

      // Smooth scrolling for anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        });
      });

      // Auto-hide mobile filters on orientation change
      window.addEventListener('orientationchange', function() {
        setTimeout(() => {
          const mobileFilters = document.getElementById('pprMobileFilters');
          mobileFilters.classList.remove('active');
        }, 100);
      });

      // Add ripple effect to buttons
      document.querySelectorAll('.ppr-btn-unique').forEach(button => {
        button.addEventListener('click', function(e) {
          const ripple = document.createElement('span');
          const rect = this.getBoundingClientRect();
          const size = Math.max(rect.width, rect.height);
          const x = e.clientX - rect.left - size / 2;
          const y = e.clientY - rect.top - size / 2;
          
          ripple.style.width = ripple.style.height = size + 'px';
          ripple.style.left = x + 'px';
          ripple.style.top = y + 'px';
          ripple.classList.add('ppr-ripple');
          
          this.appendChild(ripple);
          
          setTimeout(() => {
            ripple.remove();
          }, 600);
        });
      });


    });

    // Add touch support for mobile hover effects
    if ('ontouchstart' in window) {
      document.querySelectorAll('.ppr-ride-card-unique').forEach(card => {
        card.addEventListener('touchstart', function() {
          this.classList.add('ppr-touch-active');
        });

        card.addEventListener('touchend', function() {
          setTimeout(() => {
            this.classList.remove('ppr-touch-active');
          }, 150);
        });
      });
    }

    // Add CSS for ripple effect
    const style = document.createElement('style');
    style.textContent = `
      .ppr-ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ppr-ripple-animation 0.6s linear;
        pointer-events: none;
      }
      
      @keyframes ppr-ripple-animation {
        to {
          transform: scale(4);
          opacity: 0;
        }
      }
      
      .ppr-touch-active {
        background: var(--ppr-secondary-color) !important;
        transform: translateY(-2px) !important;
      }
    `;
    document.head.appendChild(style);

    document.addEventListener('DOMContentLoaded', function() {
      const carMainBtn = document.querySelector('.car-main-btn');
      const carSubOptions = document.querySelector('.car-sub-options');

      carMainBtn.addEventListener('click', function(e) {
        e.preventDefault();
        carMainBtn.classList.toggle('active');
        carSubOptions.classList.toggle('show');
      });

      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!carMainBtn.contains(e.target) && !carSubOptions.contains(e.target)) {
          carMainBtn.classList.remove('active');
          carSubOptions.classList.remove('show');
        }
      });
    });

    function toggleCarOptions(event) {
      event.preventDefault();
      const carOptions = document.getElementById('carOptions');
      const goodsOptions = document.getElementById('goodsOptions');
      
      // Close goods options if open
      goodsOptions.style.display = 'none';
      
      // Toggle car options
      if (carOptions.style.display === 'none' || !carOptions.style.display) {
          carOptions.style.display = 'flex';
          carOptions.style.flexDirection = 'column';
      } else {
          carOptions.style.display = 'none';
      }
    }

    // Close car options when clicking outside
    document.addEventListener('click', function(e) {
      const carButton = document.getElementById('carButton');
      const carOptions = document.getElementById('carOptions');
      const goodsOptions = document.getElementById('goodsOptions');
      
      if (!carButton.contains(e.target) && !carOptions.contains(e.target)) {
        carOptions.style.display = 'none';
      }
      
      if (!goodsButton.contains(e.target) && !goodsOptions.contains(e.target)) {
        goodsOptions.style.display = 'none';
      }
    });

    function toggleGoodsOptions(event) {
      event.preventDefault();
      const goodsOptions = document.getElementById('goodsOptions');
      const carOptions = document.getElementById('carOptions');
      
      // Close car options if open
      carOptions.style.display = 'none';
      
      // Toggle goods options
      if (goodsOptions.style.display === 'none' || !goodsOptions.style.display) {
          goodsOptions.style.display = 'flex';
          goodsOptions.style.flexDirection = 'column';
      } else {
          goodsOptions.style.display = 'none';
      }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
      const goodsOptions = document.getElementById('goodsOptions');
      const goodsButton = document.getElementById('goodsButton');
      const carOptions = document.getElementById('carOptions');
      const carButton = document.getElementById('carButton');
      
      if (!event.target.closest('#goodsButton') && !event.target.closest('#goodsOptions')) {
        goodsOptions.style.display = 'none';
        goodsButton.classList.remove('active');
      }
      
      if (!event.target.closest('#carButton') && !event.target.closest('#carOptions')) {
        carOptions.style.display = 'none';
        carButton?.classList.remove('active');
      }
    });
  </script>

  <?php include 'footer.php'; ?>
</div></body>
</html>

