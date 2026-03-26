<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
include('../db.php');
$aid = $_SESSION['admin_id'];

if (isset($_POST['add_driver'])) {
  $u_fname = $_POST['u_fname'];
  $u_phone = $_POST['u_phone'];
  $u_email = $_POST['u_email'];
  $u_addr = $_POST['u_addr'];
  $u_vehicle = $_POST['u_vehicle'];
  $u_languages = $_POST['u_languages'];
  $u_pass = $_POST['password']; 
  $member_since = date('Y-m-d');

  // Handle image uploads
  $target_dir = "../uploads/";

  if (!is_dir($target_dir)) {
      mkdir($target_dir, 0755, true);
  }

  // Driving License upload
  $license_file_name = $_FILES["u_photo"]["name"];
  $license_file_tmp = $_FILES["u_photo"]["tmp_name"];
  $license_file_error = $_FILES["u_photo"]["error"];

  if ($license_file_error !== UPLOAD_ERR_OK) {
      die("❌ License upload error: " . $license_file_error);
  }

  $license_file_ext = strtolower(pathinfo($license_file_name, PATHINFO_EXTENSION));
  $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

  if (!in_array($license_file_ext, $allowed_types)) {
      die("❌ Only JPG, JPEG, PNG, and GIF files are allowed for license.");
  }

  $new_license_name = time() . '_license_' . uniqid() . '.' . $license_file_ext;
  $license_target_file = $target_dir . $new_license_name;
  $license_relative_path = "uploads/" . $new_license_name;

  // Profile Picture upload
  $profile_file_name = $_FILES["profile_pic"]["name"];
  $profile_file_tmp = $_FILES["profile_pic"]["tmp_name"];
  $profile_file_error = $_FILES["profile_pic"]["error"];

  if ($profile_file_error !== UPLOAD_ERR_OK) {
      die("❌ Profile picture upload error: " . $profile_file_error);
  }

  $profile_file_ext = strtolower(pathinfo($profile_file_name, PATHINFO_EXTENSION));

  if (!in_array($profile_file_ext, $allowed_types)) {
      die("❌ Only JPG, JPEG, PNG, and GIF files are allowed for profile picture.");
  }

  $new_profile_name = time() . '_profile_' . uniqid() . '.' . $profile_file_ext;
  $profile_target_file = $target_dir . $new_profile_name;
  $profile_relative_path = "uploads/" . $new_profile_name;

  if (move_uploaded_file($license_file_tmp, $license_target_file) && move_uploaded_file($profile_file_tmp, $profile_target_file)) {
      $query = "INSERT INTO drivers (Full_Name, Contact, Email, Address, Driving_License, Profile_Pic, Vehicle_Number, Languages, Password, member_since) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($query);

      if ($stmt) {
        $stmt->bind_param('ssssssssss', $u_fname, $u_phone, $u_email, $u_addr, $license_relative_path, $profile_relative_path, $u_vehicle, $u_languages, $u_pass, $member_since);
          if ($stmt->execute()) {
              $succ = "Driver Added Successfully!";
          } else {
              $err = "Execution Failed: " . $stmt->error;
          }
          $stmt->close();
      } else {
          $err = "Preparation Failed: " . $conn->error;
      }
  } else {
      $err = "❌ Error uploading files. Please check directory permissions.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<?php include('vendor/inc/head.php');?>

<body id="page-top">
<div class="main-content">
 <!--Start Navigation Bar-->
  <?php include("vendor/inc/nav.php");?>
  <!--Navigation Bar-->

  <div id="wrapper">

    <!-- Sidebar -->
    <?php include("vendor/inc/sidebar.php");?>
    <!--End Sidebar-->
    <div id="content-wrapper">

      <div class="container-fluid">
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

        <!-- Breadcrumbs-->
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="admin-view-driver.php">Drivers</a>
          </li>
          <li class="breadcrumb-item active">Add Driver</li>
        </ol>
        <hr>
        <div class="card">
        <div class="card-header">
          Add Driver
        </div>
        <div class="card-body">
          <!--Add User Form-->
          <form method ="POST" enctype="multipart/form-data"> 
            <div class="form-group">
                <label for="exampleInputEmail1">Enter Full Name</label>
                <input type="text" required class="form-control" id="exampleInputEmail1" name="u_fname">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1">Enter Contact Number</label>
                <input type="text" class="form-control" id="exampleInputEmail1" name="u_phone">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1">Email address</label>
                <input type="email" class="form-control" name="u_email">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1">Enter Address</label>
                <input type="text" class="form-control" id="exampleInputEmail1" name="u_addr">
            </div>
            <div class="form-group">
                <label for="profile_pic">Upload Profile Picture</label>
                <input type="file" class="form-control" name="profile_pic" id="profile_pic" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="u_photo">Upload Driving License</label>
                <input type="file" class="form-control" name="u_photo" id="u_photo" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="u_vehicle">Enter Vehicle Number</label>
                <input type="text" class="form-control" name="u_vehicle" id="u_vehicle" required>
            </div>
            <div class="form-group">
                <label for="u_languages">Enter Languages Known (Comma Separated)</label>
                <input type="text" class="form-control" name="u_languages" id="u_languages" placeholder="e.g., English, Hindi, Tamil" required>
            </div>
            <div class="form-group">
                <label for="password">Enter Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <button type="submit" name="add_driver" class="btn btn-success">Add Driver</button>
          </form>
          <!-- End Form-->
        </div>
      </div>
       
      <hr>
     

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
  <script src="vendor/chart.js/Chart.min.js"></script>
  <script src="vendor/datatables/jquery.dataTables.js"></script>
  <script src="vendor/datatables/dataTables.bootstrap4.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="vendor/js/sb-admin.min.js"></script>

  <!-- Demo scripts for this page-->
  <script src="vendor/js/demo/datatables-demo.js"></script>
  <script src="vendor/js/demo/chart-area-demo.js"></script>
 <!--INject Sweet alert js-->
 <script src="vendor/js/swal.js"></script>

</div></body>

</html>