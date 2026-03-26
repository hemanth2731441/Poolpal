<?php
  session_start();
  if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
  }
  
    include('../db.php');
      $aid=$_SESSION['admin_id'];

  if(isset($_GET['del']))
{
      $id=intval($_GET['del']);
      $adn="delete from drivers where ID=?";
      $stmt= $conn->prepare($adn);
      $stmt->bind_param('i',$id);
      $stmt->execute();
      $stmt->close();	 

        if($stmt)
        {
          $succ = "Driver Fired";
        }
          else
          {
            $err = "Try Again Later";
          }
  }
  
  // Handle status toggle if directly from URL (non-AJAX fallback)
  if(isset($_GET['toggle_status']) && isset($_GET['driver_id'])) {
    $driver_id = intval($_GET['driver_id']);
    $new_status = intval($_GET['toggle_status']);
    
    $stmt = $conn->prepare("UPDATE drivers SET status = ? WHERE ID = ?");
    $stmt->bind_param('ii', $new_status, $driver_id);
    $result = $stmt->execute();
    $stmt->close();
    
    if($result) {
      $status_msg = ($new_status == 1) ? "activated" : "suspended";
      $succ = "Driver account $status_msg successfully";
    } else {
      $err = "Failed to update driver status";
    }
    
    // Redirect to remove the GET parameters
    header("Location: admin-manage-driver.php");
    exit;
  }
?>
<!DOCTYPE html>
<html lang="en">

<?php include('vendor/inc/head.php');?>
<style>
  /* Toggle Switch CSS */
  .switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 30px;
    margin-bottom: 0;
  }
  
  .switch input { 
    opacity: 0;
    width: 0;
    height: 0;
  }
  
  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
    border-radius: 34px;
  }
  
  .slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
    border-radius: 50%;
  }
  
  input:checked + .slider {
    background-color: #28a745;
  }
  
  input:focus + .slider {
    box-shadow: 0 0 1px #28a745;
  }
  
  input:checked + .slider:before {
    -webkit-transform: translateX(30px);
    -ms-transform: translateX(30px);
    transform: translateX(30px);
  }
  
  .status-label {
    margin-left: 10px;
    font-weight: bold;
  }
  
  .status-active {
    color: #28a745;
  }
  
  .status-suspended {
    color: #dc3545;
  }
</style>

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
            <a href="admin-view-driver.php">Drivers</a>
          </li>
          <li class="breadcrumb-item active">Manage Drivers</li>
        </ol>
        <?php if(isset($succ)) {?>
                        <!--This code for injecting an alert-->
        <script>
                    setTimeout(function () 
                    { 
                        swal("Success!","<?php echo $succ;?>!","success");
                    },
                        100);
        </script>

        <?php } ?>
        <?php if(isset($err)) {?>
        <!--This code for injecting an alert-->
        <script>
                    setTimeout(function () 
                    { 
                        swal("Failed!","<?php echo $err;?>!","Failed");
                    },
                        100);
        </script>

        <?php } ?>


        <!-- DataTables Example -->
        <div class="card mb-3">
          <div class="card-header">
            <i class="fas fa-users"></i>
            Registered Users</div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Profile Picture</th>
                    <th>Driving License</th>
                    <th>Vehicle Number</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <?php

                    $ret="SELECT * FROM drivers"; 
                    $stmt= $conn->prepare($ret) ;
                    $stmt->execute() ;//ok
                    $res=$stmt->get_result();
                    $cnt=1;
                    while($row=$res->fetch_object())
                {
                ?>
                <tbody>
                  <tr>
                    <td><?php echo $cnt;?></td>
                    <td><?php echo $row->Full_Name;?></td>
                    <td><?php echo $row->Contact;?></td>
                    <td><?php echo $row->Email;?></td>
                    <td><?php echo $row->Address;?></td>
                    <td>
                        <img src="../<?php echo $row->Profile_Pic; ?>" alt="License" width="100" height="60">
                    </td>
                    <td>
                        <img src="../<?php echo $row->Driving_License; ?>" alt="License" width="100" height="60">
                    </td>
                    <td><?php echo $row->Vehicle_Number;?></td>
                    <td>
                      <?php 
                        $status = $row->status; 
                        $statusClass = $status == 1 ? 'status-active' : 'status-suspended';
                        $statusText = $status == 1 ? 'Active' : 'Suspended';
                      ?>
                      <label class="switch">
                        <input type="checkbox" class="status-toggle" data-id="<?php echo $row->ID; ?>" <?php echo $status == 1 ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                      </label>
                      <span class="status-label <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                    </td>
                    <td>
                      <a href="admin-manage-single-driver.php?u_id=<?php echo $row->ID;?>" class="badge badge-success">Update</a>
                      <a href="admin-manage-driver.php?del=<?php echo $row->ID;?>" class="badge badge-danger">Fire</a>
                    </td>
                  </tr>
                </tbody>
                <?php $cnt = $cnt+1; }?>

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

  <!-- Core plugin JavaScript-->
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Page level plugin JavaScript-->
  <script src="vendor/datatables/jquery.dataTables.js"></script>
  <script src="vendor/datatables/dataTables.bootstrap4.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="js/sb-admin.min.js"></script>

  <!-- Demo scripts for this page-->
  <script src="js/demo/datatables-demo.js"></script>
  
  <!-- SweetAlert for notifications -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  
  <!-- Toggle Status Script -->
  <script>
    $(document).ready(function() {
      $('.status-toggle').change(function() {
        const driverId = $(this).data('id');
        const isChecked = $(this).prop('checked');
        const status = isChecked ? 1 : 0;
        const statusText = isChecked ? 'Active' : 'Suspended';
        const statusClass = isChecked ? 'status-active' : 'status-suspended';
        
        // Update the status label immediately for better UX
        $(this).closest('td').find('.status-label')
          .removeClass('status-active status-suspended')
          .addClass(statusClass)
          .text(statusText);
        
        // Send AJAX request to update status
        $.ajax({
          url: 'toggle_driver_status.php',
          type: 'POST',
          data: {
            driver_id: driverId,
            status: status
          },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Success',
                text: `Driver account ${statusText.toLowerCase()} successfully!`,
                timer: 2000,
                showConfirmButton: false
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message || 'Failed to update driver status',
                timer: 2000,
                showConfirmButton: false
              });
              
              // Revert the toggle if there was an error
              $('.status-toggle[data-id="' + driverId + '"]').prop('checked', !isChecked);
              
              // Revert the status label
              const revertStatusText = !isChecked ? 'Active' : 'Suspended';
              const revertStatusClass = !isChecked ? 'status-active' : 'status-suspended';
              $('.status-toggle[data-id="' + driverId + '"]').closest('td').find('.status-label')
                .removeClass('status-active status-suspended')
                .addClass(revertStatusClass)
                .text(revertStatusText);
            }
          },
          error: function() {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Network error occurred. Please try again.',
              timer: 2000,
              showConfirmButton: false
            });
            
            // Revert the toggle if there was an error
            $('.status-toggle[data-id="' + driverId + '"]').prop('checked', !isChecked);
            
            // Revert the status label
            const revertStatusText = !isChecked ? 'Active' : 'Suspended';
            const revertStatusClass = !isChecked ? 'status-active' : 'status-suspended';
            $('.status-toggle[data-id="' + driverId + '"]').closest('td').find('.status-label')
              .removeClass('status-active status-suspended')
              .addClass(revertStatusClass)
              .text(revertStatusText);
          }
        });
      });
    });
  </script>

</div></body>

</html>