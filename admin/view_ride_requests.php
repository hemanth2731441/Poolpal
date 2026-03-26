<?php
session_start();
include('../db.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Ride Requests</title>
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

        <!-- Breadcrumbs-->
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="view_ride_requests.php">Ride Requests</a>
          </li>
          <li class="breadcrumb-item active">View Ride Requests</li>
        </ol>

        <!-- DataTables Example -->
        <div class="card mb-3">
          <div class="card-header">
            <i class="fas fa-car"></i>
            User Ride Requests</div>
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
                    <th>Requested On</th>
                    <th>Notes</th>
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
                    <td><?php echo date('M d, Y', strtotime($row->created_at)); ?></td>
                    <td>
                      <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#notesModal<?php echo $row->id; ?>">
                        View Notes
                      </button>
                      
                      <!-- Notes Modal -->
                      <div class="modal fade" id="notesModal<?php echo $row->id; ?>" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="notesModalLabel">Additional Notes</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <?php echo !empty($row->additional_notes) ? $row->additional_notes : 'No additional notes provided.'; ?>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
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
          <a class="btn btn-danger" href="admin-logout.php">Logout</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap core JavaScript-->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Page level plugin JavaScript-->
  <script src="vendor/datatables/jquery.dataTables.js"></script>
  <script src="vendor/datatables/dataTables.bootstrap4.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="js/sb-admin.min.js"></script>

  <!-- Demo scripts for this page-->
  <script src="js/demo/datatables-demo.js"></script>
</div></body>
</html> 