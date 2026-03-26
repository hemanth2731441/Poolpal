<style>
.sidebar {
    background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
    transition: all 0.3s ease;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar .nav-item {
    position: relative;
    margin-bottom: 5px;
}

.sidebar .nav-link {
    color: rgba(255,255,255,0.8) !important;
    padding: 1rem;
    transition: all 0.3s ease;
    border-radius: 0.5rem;
    margin: 0 0.5rem;
}

.sidebar .nav-link:hover {
    color: #fff !important;
    background: rgba(255,255,255,0.1);
    transform: translateX(5px);
}

.sidebar .nav-link i {
    transition: all 0.3s ease;
    margin-right: 0.5rem;
}

.sidebar .nav-link:hover i {
    transform: scale(1.2);
}

.sidebar .dropdown-menu {
    background: #ffffff;
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    animation: slideIn 0.3s ease-out;
}

.sidebar .dropdown-item {
    padding: 0.7rem 1.5rem;
    transition: all 0.2s ease;
}

.sidebar .dropdown-item:hover {
    background: rgba(30, 60, 114, 0.1);
    transform: translateX(5px);
}

@keyframes slideIn {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<ul class="sidebar navbar-nav">
      <li class="nav-item active">
        <a class="nav-link" href="admin_panel.php">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-fw fa-users"></i>
          <span>Users</span>
        </a>
        <div class="dropdown-menu" aria-labelledby="pagesDropdown">
          <h6 class="dropdown-header">User Management</h6>
          <a class="dropdown-item" href="admin_users.php"><i class="fas fa-list fa-sm mr-2"></i>View All</a>
          <a class="dropdown-item" href="admin-manage-user.php"><i class="fas fa-user-cog fa-sm mr-2"></i>Manage</a>
        </div>
      </li>

      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-fw fa-id-card"></i>
          <span>Drivers</span>
        </a>
        <div class="dropdown-menu" aria-labelledby="pagesDropdown">
          <h6 class="dropdown-header">Driver Management</h6>
          <a class="dropdown-item" href="admin-add-driver.php"><i class="fas fa-plus fa-sm mr-2"></i>Add New</a>
          <a class="dropdown-item" href="admin-view-driver.php"><i class="fas fa-list fa-sm mr-2"></i>View All</a>
          <a class="dropdown-item" href="admin-manage-driver.php"><i class="fas fa-user-cog fa-sm mr-2"></i>Manage</a>
        </div>
      </li>
      
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="ridesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-fw fa-car"></i>
          <span>Ride Requests</span>
        </a>
        <div class="dropdown-menu" aria-labelledby="ridesDropdown">
          <h6 class="dropdown-header">Ride Management</h6>
          <a class="dropdown-item" href="view_ride_requests.php"><i class="fas fa-list-alt fa-sm mr-2"></i>View Requests</a>
          <a class="dropdown-item" href="manage_ride_requests.php"><i class="fas fa-tasks fa-sm mr-2"></i>Manage Requests</a>
        </div>
      </li>

      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="slidersDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-fw fa-images"></i>
          <span>Sliders</span>
        </a>
        <div class="dropdown-menu" aria-labelledby="slidersDropdown">
          <h6 class="dropdown-header">Slider Management</h6>
          <a class="dropdown-item" href="add_sliders.php"><i class="fas fa-plus-circle fa-sm mr-2"></i>Add Slider</a>
          <a class="dropdown-item" href="manage_sliders.php"><i class="fas fa-cog fa-sm mr-2"></i>Manage Sliders</a>
        </div>
      </li>

      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="bookingsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-fw fa-calendar-check"></i>
          <span>Bookings</span>
        </a>
        <div class="dropdown-menu" aria-labelledby="bookingsDropdown">
          <h6 class="dropdown-header">Booking Management</h6>
          <a class="dropdown-item" href="view_bookings.php"><i class="fas fa-list fa-sm mr-2"></i>All Bookings</a>
          <a class="dropdown-item" href="active_bookings.php"><i class="fas fa-check-circle fa-sm mr-2"></i>Active Bookings</a>
          <a class="dropdown-item" href="completed_bookings.php"><i class="fas fa-hourglass-half fa-sm mr-2"></i>Completed Bookings</a>
          <a class="dropdown-item" href="cancelled_bookings.php"><i class="fas fa-times-circle fa-sm mr-2"></i>Cancelled Bookings</a>
        </div>
      </li>
    </ul>