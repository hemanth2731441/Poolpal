<?php
  session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: admin_login.php");
  exit;
}

  include('../db.php');
    $aid=$_SESSION['admin_id'];

  // Fetch stats from database
  $total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
  $active_drivers = $conn->query("SELECT COUNT(*) as count FROM drivers WHERE status = 1 AND verification_status = 'accepted'")->fetch_assoc()['count'];
  $total_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
  $total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE payment_status = 'completed'")->fetch_assoc()['total'];

  // Get monthly booking stats for the last 6 months
  $monthly_bookings = array();
  $monthly_revenue = array();
  for($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $start_date = $month . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));
    
    $bookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE booking_time BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['count'];
    $revenue = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE payment_status = 'completed' AND booking_time BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['total'];
    
    $monthly_bookings[] = $bookings;
    $monthly_revenue[] = $revenue ?: 0;
  }

  // Get recent activities - Fixed query with correct column names
  $recent_activities = $conn->query("
    (SELECT 'booking' as type, booking_time as time, CONCAT(user_name, ' made a booking') as description 
     FROM bookings ORDER BY booking_time DESC LIMIT 3)
    UNION
    (SELECT 'driver' as type, member_since as time, CONCAT(Full_Name, ' registered as driver') as description 
     FROM drivers WHERE member_since IS NOT NULL ORDER BY member_since DESC LIMIT 3)
    UNION
    (SELECT 'user' as type, created_at as time, CONCAT(full_name, ' registered as user') as description 
     FROM users WHERE created_at IS NOT NULL ORDER BY created_at DESC LIMIT 3)
    ORDER BY time DESC LIMIT 5
  ");

  // Get vehicle type distribution for revenue
  $vehicle_revenue = $conn->query("
    SELECT t.vehicle_type, SUM(b.total_amount) as revenue 
    FROM bookings b 
    JOIN trips t ON b.trip_id = t.id 
    WHERE b.payment_status = 'completed'
    GROUP BY t.vehicle_type
  ");
?>
<!DOCTYPE html>
<html lang="en">

<!--Head-->
<?php include ('vendor/inc/head.php');?>
<!--End Head-->

<body id="page-top">
<div class="main-content">
<!--Navbar-->
  <?php include ('vendor/inc/nav.php');?>
<!--End Navbar-->  

  <div id="wrapper">

    <!-- Sidebar -->
    <?php include('vendor/inc/sidebar.php');?>
    <!--End Sidebar-->

    <div id="content-wrapper">

      <div class="container-fluid">

        <!-- Breadcrumbs-->
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="admin_panel.php">Dashboard</a>
          </li>
          <li class="breadcrumb-item active">Overview</li>
        </ol>

        <!-- Stats Cards-->
        <div class="row">
          <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card o-hidden h-100 stats-card">
              <div class="card-body">
                <div class="card-body-icon">
                  <i class="fas fa-fw fa-users"></i>
                </div>
                <div class="stats-info">
                  <div class="stats-number"><?php echo number_format($total_users); ?></div>
                  <div class="stats-text">Total Users</div>
                </div>
                <div class="stats-chart">
                  <canvas id="usersChart" width="100" height="40"></canvas>
                </div>
              </div>
              <a class="card-footer text-white clearfix small z-1" href="admin_users.php">
                <span class="float-left">View Details</span>
                <span class="float-right">
                  <i class="fas fa-angle-right"></i>
                </span>
              </a>
            </div>
          </div>
          <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card o-hidden h-100 stats-card">
              <div class="card-body">
                <div class="card-body-icon">
                  <i class="fas fa-fw fa-user"></i>
                </div>
                <div class="stats-info">
                  <div class="stats-number"><?php echo number_format($active_drivers); ?></div>
                  <div class="stats-text">Active Drivers</div>
                </div>
                <div class="stats-chart">
                  <canvas id="driversChart" width="100" height="40"></canvas>
                </div>
              </div>
              <a class="card-footer text-white clearfix small z-1" href="admin-view-driver.php">
                <span class="float-left">View Details</span>
                <span class="float-right">
                  <i class="fas fa-angle-right"></i>
                </span>
              </a>
            </div>
          </div>
          <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card o-hidden h-100 stats-card">
              <div class="card-body">
                <div class="card-body-icon">
                  <i class="fas fa-fw fa-clipboard"></i>
                </div>
                <div class="stats-info">
                  <div class="stats-number"><?php echo number_format($total_bookings); ?></div>
                  <div class="stats-text">Total Bookings</div>
                </div>
                <div class="stats-chart">
                  <canvas id="bookingsChart" width="100" height="40"></canvas>
                </div>
              </div>
              <a class="card-footer text-white clearfix small z-1" href="view_bookings.php">
                <span class="float-left">View Details</span>
                <span class="float-right">
                  <i class="fas fa-angle-right"></i>
                </span>
              </a>
            </div>
          </div>
          <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card o-hidden h-100 stats-card">
              <div class="card-body">
                <div class="card-body-icon">
                  <i class="fas fa-fw fa-dollar-sign"></i>
                </div>
                <div class="stats-info">
                  <div class="stats-number">₹<?php echo number_format($total_revenue); ?></div>
                  <div class="stats-text">Revenue</div>
                </div>
                <div class="stats-chart">
                  <canvas id="revenueChart" width="100" height="40"></canvas>
                </div>
              </div>
              <a class="card-footer text-white clearfix small z-1" href="#">
                <span class="float-left">View Details</span>
                <span class="float-right">
                  <i class="fas fa-angle-right"></i>
                </span>
              </a>
            </div>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="analytics-card">
              <div class="card-header">
                <i class="fas fa-link"></i> Quick Links
              </div>
              <div class="card-body">
                <div class="quick-links-container">
                  <a href="admin-view-driver.php" class="quick-link-card">
                    <i class="fas fa-user-plus"></i>
                    <span>Manage Drivers</span>
                  </a>
                  <a href="view_bookings.php" class="quick-link-card">
                    <i class="fas fa-calendar-check"></i>
                    <span>View Bookings</span>
                  </a>
                  <a href="admin_users.php" class="quick-link-card">
                    <i class="fas fa-users-cog"></i>
                    <span>Manage Users</span>
                  </a>
                  <a href="#" class="quick-link-card">
                    <i class="fas fa-chart-line"></i>
                    <span>Reports</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Analytics Charts -->
        <div class="row">
          <div class="col-xl-8 col-lg-7">
            <div class="analytics-card">
              <div class="card-header">
                <h5><i class="fas fa-chart-area"></i>Booking Analytics</h5>
                <div class="time-range-select">
                  <select id="timeRangeSelect">
                    <option value="7">Last 7 Days</option>
                    <option value="30">Last 30 Days</option>
                    <option value="90">Last 90 Days</option>
                  </select>
                </div>
              </div>
              <div class="chart-container">
                <canvas id="bookingAnalytics"></canvas>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-lg-5">
            <div class="analytics-card">
              <div class="card-header">
                <i class="fas fa-chart-pie"></i>
                Revenue Distribution
              </div>
              <div class="card-body">
                <canvas id="revenuePieChart" width="100%" height="100"></canvas>
              </div>
              <div class="card-footer">
                <span class="last-update">Updated just now</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Custom Analytics -->
        <div class="row">
          <div class="col-xl-6 col-lg-6">
            <div class="analytics-card">
              <div class="card-header">
                <i class="fas fa-map-marker-alt"></i>
                Popular Routes
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-custom">
                    <thead>
                      <tr>
                        <th>Route</th>
                        <th>Bookings</th>
                        <th>Revenue</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $popular_routes = $conn->query("
                          SELECT 
                            CONCAT(
                              CONVERT(t.departure_city USING utf8) COLLATE utf8_general_ci,
                              ' → ',
                              CONVERT(t.destination_city USING utf8) COLLATE utf8_general_ci
                            ) as route,
                            COUNT(*) as bookings,
                            SUM(b.total_amount) as revenue
                          FROM bookings b
                          JOIN trips t ON b.trip_id = t.id
                          WHERE b.payment_status = 'completed'
                          GROUP BY route
                          ORDER BY bookings DESC
                          LIMIT 5
                        ");
                        while($route = $popular_routes->fetch_assoc()) {
                          echo "<tr>
                            <td>{$route['route']}</td>
                            <td>{$route['bookings']}</td>
                            <td>₹" . number_format($route['revenue']) . "</td>
                          </tr>";
                        }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-6 col-lg-6">
            <div class="analytics-card">
              <div class="card-header">
                <i class="fas fa-chart-bar"></i>
                Vehicle Type Performance
              </div>
              <div class="card-body">
                <canvas id="vehicleTypeChart" width="100%" height="50"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="analytics-card">
          <div class="card-header">
            <h5><i class="fas fa-history"></i>Recent Activity</h5>
          </div>
          <div class="card-body p-0">
            <ul class="activity-list">
              <?php while($activity = $recent_activities->fetch_assoc()): ?>
                <li class="activity-item">
                  <div class="activity-icon">
                    <?php 
                      $icon = 'user';
                      if($activity['type'] == 'booking') $icon = 'calendar-check';
                      else if($activity['type'] == 'driver') $icon = 'car';
                    ?>
                    <i class="fas fa-<?php echo $icon; ?>"></i>
                  </div>
                  <div class="activity-content">
                    <div class="activity-text"><?php echo $activity['description']; ?></div>
                    <div class="activity-time">
                      <?php 
                        $time_diff = time() - strtotime($activity['time']);
                        if($time_diff < 60) echo "Just now";
                        else if($time_diff < 3600) echo floor($time_diff/60) . " minutes ago";
                        else if($time_diff < 86400) echo floor($time_diff/3600) . " hours ago";
                        else echo floor($time_diff/86400) . " days ago";
                      ?>
                    </div>
                  </div>
                </li>
              <?php endwhile; ?>
            </ul>
          </div>
        </div>

      </div>
      <!-- /.container-fluid -->

      <!-- Sticky Footer -->
     <?php include("vendor/inc/footer.php");?>

    </div>
    <!-- /.content-wrapper -->

  </div>
  <!-- /#wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          <a class="btn btn-danger" href="user-logout.php">Logout</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap core JavaScript-->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Page level plugin JavaScript-->
  <script src="vendor/chart.js/Chart.min.js"></script>
  <script src="vendor/datatables/jquery.dataTables.js"></script>
  <script src="vendor/datatables/dataTables.bootstrap4.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="vendor/js/sb-admin.min.js"></script>

<style>
/* Modern Dashboard Styling */
:root {
    --primary-color: #1e3c72;
    --secondary-color: #2a5298;
    --accent-1: #4CAF50;
    --accent-2: #FF5722;
    --accent-3: #2196F3;
    --accent-4: #9C27B0;
    --success: #4CAF50;
    --warning: #FFC107;
    --danger: #f44336;
    --text-primary: #2c3e50;
    --text-secondary: #6c757d;
    --bg-light: #f8f9fa;
    --border-radius: 12px;
    --transition: all 0.3s ease;
    --chart-color-1: #3498db;
    --chart-color-2: #2ecc71;
    --chart-color-3: #e74c3c;
    --chart-color-4: #f1c40f;
    --chart-color-5: #9b59b6;
}

body {
    background-color: #f5f7fa;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Enhanced Stats Cards */
.stats-card {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    border: none;
    border-radius: var(--border-radius);
    overflow: hidden;
    transition: var(--transition);
    position: relative;
    z-index: 1;
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    z-index: -1;
    transition: var(--transition);
    opacity: 0;
}

.stats-card:hover::before {
    opacity: 1;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(30,60,114,0.15);
}

.stats-info {
    padding: 1.5rem;
}

.stats-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
    margin-bottom: 0.5rem;
    letter-spacing: -0.5px;
    line-height: 1;
}

.stats-text {
    font-size: 1rem;
    color: rgba(255,255,255,0.9);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.card-body-icon {
    position: absolute;
    right: 0.5rem;
    top: 0.5rem;
    font-size: 2.5rem;
    opacity: 0.3;
    transform: rotate(15deg);
    transition: all 0.3s ease;
}

.stats-card:hover .card-body-icon {
    transform: rotate(0deg) scale(1.1);
    opacity: 0.4;
}

.stats-chart {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    opacity: 0.2;
}

/* Enhanced Quick Links */
.quick-links-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    padding: 1rem;
}

.quick-link-card {
    background: white;
    border-radius: 15px;
    padding: 2rem 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 4px 6px rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    text-decoration: none !important;
}

.quick-link-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    border-color: #1e3c72;
}

.quick-link-card i {
    font-size: 2.5rem;
    color: #1e3c72;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.quick-link-card span {
    font-size: 1.1rem;
    color: #2c3e50;
    font-weight: 600;
    transition: all 0.3s ease;
}

.quick-link-card:hover i {
    transform: scale(1.1);
    color: #2a5298;
}

.quick-link-card:hover span {
    color: #1e3c72;
}

/* Enhanced Analytics Cards */
.analytics-card {
    background: white;
    border-radius: var(--border-radius);
    border: 1px solid rgba(0,0,0,0.05);
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    transition: var(--transition);
    margin-bottom: 1.5rem;
}

.analytics-card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.analytics-card .card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.analytics-card .card-header h5 {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 1.1rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.analytics-card .card-header h5 i {
    color: var(--primary-color);
    font-size: 1.2rem;
}

/* Time Range Select */
.time-range-select {
    position: relative;
    min-width: 150px;
}

.time-range-select select {
    appearance: none;
    background: var(--bg-light);
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 20px;
    padding: 0.5rem 2rem 0.5rem 1rem;
    font-size: 0.9rem;
    color: var(--text-primary);
    width: 100%;
    cursor: pointer;
    transition: var(--transition);
}

.time-range-select select:hover {
    border-color: var(--primary-color);
    background: white;
}

.time-range-select::after {
    content: '';
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid var(--text-primary);
    pointer-events: none;
}

/* Table Styling */
.table-custom {
    width: 100%;
    margin: 0;
}

.table-custom thead th {
    background: var(--bg-light);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 1px;
    padding: 1rem 1.5rem;
    border: none;
    color: var(--text-secondary);
}

.table-custom tbody td {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    color: var(--text-primary);
    font-weight: 500;
    transition: var(--transition);
}

.table-custom tbody tr:last-child td {
    border-bottom: none;
}

.table-custom tbody tr {
    transition: var(--transition);
}

.table-custom tbody tr:hover {
    background: rgba(30,60,114,0.02);
}

/* Recent Activity */
.activity-list {
    padding: 0;
    margin: 0;
    list-style: none;
}

.activity-item {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: var(--transition);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--bg-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
}

.activity-content {
    flex: 1;
}

.activity-text {
    color: var(--text-primary);
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.activity-time {
    color: var(--text-secondary);
    font-size: 0.85rem;
}

/* Chart Customization */
.chart-container {
    position: relative;
    padding: 1.5rem;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.analytics-card {
    animation: fadeInUp 0.5s ease-out forwards;
}

.analytics-card:nth-child(1) { animation-delay: 0.1s; }
.analytics-card:nth-child(2) { animation-delay: 0.2s; }
.analytics-card:nth-child(3) { animation-delay: 0.3s; }
.analytics-card:nth-child(4) { animation-delay: 0.4s; }

/* Responsive Design */
@media (max-width: 768px) {
    .stats-number {
        font-size: 2rem;
    }
    
    .analytics-card .card-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .time-range-select {
        width: 100%;
    }
    
    .table-custom thead th,
    .table-custom tbody td {
        padding: 1rem;
    }
}

/* Enhanced Vehicle Performance Section */
.vehicle-performance {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.vehicle-stat {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    background: var(--bg-light);
    transition: var(--transition);
}

.vehicle-stat:hover {
    transform: translateX(5px);
    background: rgba(30, 60, 114, 0.05);
}

.vehicle-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.vehicle-icon i {
    color: white;
    font-size: 1.5rem;
}

.vehicle-info {
    flex: 1;
}

.vehicle-name {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.vehicle-metrics {
    display: flex;
    gap: 1.5rem;
}

.metric {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.metric-value {
    font-weight: 600;
    color: var(--primary-color);
}

.metric-label {
    color: var(--text-secondary);
    font-size: 0.85rem;
}

/* Enhanced Popular Routes Section */
.route-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: var(--transition);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.route-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-3px);
}

.route-header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.route-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--bg-light);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.route-icon i {
    color: var(--primary-color);
    font-size: 1.2rem;
}

.route-title {
    font-weight: 600;
    color: var(--text-primary);
    flex: 1;
}

.route-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-top: 1rem;
}

.route-stat {
    text-align: center;
    padding: 0.75rem;
    background: var(--bg-light);
    border-radius: 8px;
}

.route-stat-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.route-stat-label {
    font-size: 0.85rem;
    color: var(--text-secondary);
}

/* Enhanced Recent Activity */
.activity-feed {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    padding: 1.25rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    transition: var(--transition);
}

.activity-item:hover {
    background: rgba(30, 60, 114, 0.02);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.activity-icon.booking {
    background: rgba(76, 175, 80, 0.1);
    color: var(--accent-1);
}

.activity-icon.driver {
    background: rgba(33, 150, 243, 0.1);
    color: var(--accent-3);
}

.activity-icon.user {
    background: rgba(156, 39, 176, 0.1);
    color: var(--accent-4);
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.activity-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.activity-time {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.activity-time i {
    font-size: 0.9rem;
}

/* Enhanced Charts */
.chart-wrapper {
    position: relative;
    margin: 1rem 0;
}

.chart-legend {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 1rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 3px;
}

.legend-label {
    font-size: 0.9rem;
    color: var(--text-secondary);
}

/* Chart Tooltips */
.custom-tooltip {
    background: white !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
    padding: 0.75rem 1rem !important;
    border: 1px solid rgba(0, 0, 0, 0.05) !important;
}

.tooltip-header {
    font-weight: 600;
    color: var(--text-primary);
    padding-bottom: 0.5rem;
    margin-bottom: 0.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.tooltip-body {
    font-size: 0.9rem;
    color: var(--text-secondary);
}

/* Responsive Enhancements */
@media (max-width: 768px) {
    .route-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .activity-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script>
// Initialize Charts with dynamic data
document.addEventListener('DOMContentLoaded', function() {
    // PHP data for charts
    const monthlyBookings = <?php echo json_encode($monthly_bookings); ?>;
    const monthlyRevenue = <?php echo json_encode($monthly_revenue); ?>;
    const months = <?php echo json_encode(array_map(function($i) { 
        return date('M', strtotime("-$i months")); 
    }, range(5, 0))); ?>;

    // Mini Charts for Stats Cards
    const miniChartOptions = {
        type: 'line',
        options: {
            maintainAspectRatio: false,
            legend: { display: false },
            scales: { 
                xAxes: [{ display: false }],
                yAxes: [{ display: false }]
            },
            elements: {
                point: { radius: 0 },
                line: { tension: 0.4 }
            },
            tooltips: { enabled: false }
        },
        data: {
            labels: months,
            datasets: [{
                data: monthlyBookings,
                borderColor: 'rgba(255,255,255,0.8)',
                backgroundColor: 'rgba(255,255,255,0.2)',
                borderWidth: 2,
                fill: true
            }]
        }
    };

    new Chart(document.getElementById('usersChart'), miniChartOptions);
    new Chart(document.getElementById('driversChart'), miniChartOptions);
    new Chart(document.getElementById('bookingsChart'), miniChartOptions);
    new Chart(document.getElementById('revenueChart'), {
        ...miniChartOptions,
        data: {
            ...miniChartOptions.data,
            datasets: [{
                ...miniChartOptions.data.datasets[0],
                data: monthlyRevenue
            }]
        }
    });

    // Booking Analytics Chart
    const bookingCtx = document.getElementById('bookingAnalytics').getContext('2d');
    const gradientFill1 = bookingCtx.createLinearGradient(0, 0, 0, 350);
    gradientFill1.addColorStop(0, 'rgba(52, 152, 219, 0.3)');
    gradientFill1.addColorStop(1, 'rgba(52, 152, 219, 0)');

    const gradientFill2 = bookingCtx.createLinearGradient(0, 0, 0, 350);
    gradientFill2.addColorStop(0, 'rgba(46, 204, 113, 0.3)');
    gradientFill2.addColorStop(1, 'rgba(46, 204, 113, 0)');

    new Chart(bookingCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Bookings',
                data: monthlyBookings,
                borderColor: '#3498db',
                backgroundColor: gradientFill1,
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#3498db',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }, {
                label: 'Revenue',
                data: monthlyRevenue,
                borderColor: '#2ecc71',
                backgroundColor: gradientFill2,
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2ecc71',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#2c3e50',
                    bodyColor: '#2c3e50',
                    borderColor: 'rgba(0,0,0,0.1)',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 6,
                    usePointStyle: true,
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 1) {
                                label += '₹' + context.parsed.y.toLocaleString();
                            } else {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Revenue Distribution Pie Chart
    const vehicleTypes = [];
    const vehicleRevenue = [];
    <?php while($vr = $vehicle_revenue->fetch_assoc()): ?>
        vehicleTypes.push('<?php echo $vr['vehicle_type']; ?>');
        vehicleRevenue.push(<?php echo $vr['revenue']; ?>);
    <?php endwhile; ?>

    const revenueCtx = document.getElementById('revenuePieChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'doughnut',
        data: {
            labels: vehicleTypes,
            datasets: [{
                data: vehicleRevenue,
                backgroundColor: [
                    '#3498db',
                    '#2ecc71',
                    '#e74c3c',
                    '#f1c40f',
                    '#9b59b6'
                ],
                borderWidth: 0,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#2c3e50',
                    bodyColor: '#2c3e50',
                    borderColor: 'rgba(0,0,0,0.1)',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 6,
                    usePointStyle: true,
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const label = context.label;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ₹${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Vehicle Type Performance Chart
    const vehicleCtx = document.getElementById('vehicleTypeChart').getContext('2d');
    new Chart(vehicleCtx, {
        type: 'bar',
        data: {
            labels: vehicleTypes,
            datasets: [{
                label: 'Revenue',
                data: vehicleRevenue,
                backgroundColor: [
                    'rgba(52, 152, 219, 0.8)',
                    'rgba(46, 204, 113, 0.8)',
                    'rgba(231, 76, 60, 0.8)',
                    'rgba(241, 196, 15, 0.8)',
                    'rgba(155, 89, 182, 0.8)'
                ],
                borderColor: [
                    '#3498db',
                    '#2ecc71',
                    '#e74c3c',
                    '#f1c40f',
                    '#9b59b6'
                ],
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#2c3e50',
                    bodyColor: '#2c3e50',
                    borderColor: 'rgba(0,0,0,0.1)',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `Revenue: ₹${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});

// Time Range Select Handler
document.getElementById('timeRangeSelect').addEventListener('change', function(e) {
    // Add AJAX call to fetch data for selected time range
    const days = e.target.value;
    // Implement AJAX call here
});

// Update timestamps
function updateTimestamps() {
    const elements = document.querySelectorAll('.text-muted');
    elements.forEach(el => {
        el.textContent = 'Updated just now';
    });
}

// Call initially and set interval
updateTimestamps();
setInterval(updateTimestamps, 60000); // Update every minute
</script>
</body>

</html>
