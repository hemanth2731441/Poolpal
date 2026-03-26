<?php
session_start();
include('../db.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Handle status update
if(isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE ride_requests SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $new_status, $request_id);
    
    if($stmt->execute()) {
        $success = "Status updated successfully";
    } else {
        $error = "Failed to update status: " . $conn->error;
    }
}

// Handle request deletion
if(isset($_GET['del'])) {
    $request_id = intval($_GET['del']);
    
    $stmt = $conn->prepare("DELETE FROM ride_requests WHERE id = ?");
    $stmt->bind_param('i', $request_id);
    
    if($stmt->execute()) {
        $success = "Request deleted successfully";
    } else {
        $error = "Failed to delete request: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Ride Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<?php include('vendor/inc/head.php');?>

<body id="page-top">
<div class="main-content">

 <?php include("vendor/inc/nav.php");?>

  <div id="wrapper">

    <!-- Sidebar -->
    <?php include('vendor/inc/sidebar.php');?>

    <div id="content-wrapper">

      <div class="container-fluid">
        <?php if(isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Breadcrumbs-->
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="manage_ride_requests.php">Ride Requests</a>
          </li>
          <li class="breadcrumb-item active">Manage Ride Requests</li>
        </ol>

        <!-- DataTables Example -->
        <div class="card mb-3">
          <div class="card-header">
            <i class="fas fa-car"></i>
            Manage User Ride Requests</div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>User Name</th>
                    <th>Pickup Location</th>
                    <th>Destination</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Seats</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                    // Join the ride_requests table with users table to get user details
                    $ret = "SELECT r.*, u.full_name FROM ride_requests r
                            JOIN users u ON r.user_id = u.id
                            ORDER BY r.created_at DESC";
                    $stmt = $conn->prepare($ret);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $cnt = 1;
                    
                    while($row = $res->fetch_object()) {
                ?>
                  <tr>
                    <td><?php echo $cnt; ?></td>
                    <td><?php echo $row->full_name; ?></td>
                    <td><?php echo $row->pickup_location; ?></td>
                    <td><?php echo $row->destination; ?></td>
                    <td><?php echo $row->ride_date; ?></td>
                    <td><?php echo $row->ride_time; ?></td>
                    <td><?php echo $row->seats_needed; ?></td>
                    <td>
                      <span class="badge <?php 
                        if($row->status == 'pending') echo 'bg-warning';
                        elseif($row->status == 'accepted') echo 'bg-success';
                        elseif($row->status == 'completed') echo 'bg-info';
                        else echo 'bg-danger';
                      ?>">
                        <?php echo ucfirst($row->status); ?>
                      </span>
                    </td>
                    <td>
                      <!-- Update Status Button -->
                      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $row->id; ?>">
                        Update Status
                      </button>
                      
                      <!-- Delete Button -->
                      <a href="manage_ride_requests.php?del=<?php echo $row->id; ?>" 
                         onclick="return confirm('Are you sure you want to delete this request?');" 
                         class="btn btn-sm btn-danger">
                        Delete
                      </a>
                      
                      <!-- Update Status Modal -->
                      <div class="modal fade" id="updateModal<?php echo $row->id; ?>" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="updateModalLabel">Update Ride Request Status</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="post" action="">
                              <div class="modal-body">
                                <input type="hidden" name="request_id" value="<?php echo $row->id; ?>">
                                
                                <div class="mb-3">
                                  <label for="status" class="form-label">Select New Status</label>
                                  <select class="form-select" name="status" required>
                                    <option value="pending" <?php if($row->status == 'pending') echo 'selected'; ?>>Pending</option>
                                    <option value="accepted" <?php if($row->status == 'accepted') echo 'selected'; ?>>Accepted</option>
                                    <option value="completed" <?php if($row->status == 'completed') echo 'selected'; ?>>Completed</option>
                                    <option value="cancelled" <?php if($row->status == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                                  </select>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php $cnt++; } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- /.container-fluid -->