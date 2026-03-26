<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include('../db.php');
$aid = $_SESSION['admin_id'];

// Get total sliders count
$total_query = "SELECT COUNT(*) as total FROM sliders";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_sliders = $total_row['total'];

// Get active sliders count
$active_query = "SELECT COUNT(*) as active FROM sliders WHERE is_active = 1";
$active_result = mysqli_query($conn, $active_query);
$active_row = mysqli_fetch_assoc($active_result);
$active_sliders = $active_row['active'];

// Fetch all sliders
$query = "SELECT * FROM sliders ORDER BY sort_order ASC, created_at DESC";
$result = mysqli_query($conn, $query);

// Handle SweetAlert2 Success Message
if (isset($_SESSION['swal_success'])) {
    $success = $_SESSION['swal_success'];
    unset($_SESSION['swal_success']);
}
?>

<!DOCTYPE html>
<html lang="en">

<!--Head-->
<?php include('vendor/inc/head.php'); ?>
<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    .preview-container {
        position: relative;
        width: 150px;
        height: 100px;
        overflow: hidden;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .preview-container img,
    .preview-container video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .stats-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
    }
    .table td {
        vertical-align: middle;
    }
</style>
<!--End Head-->

<body id="page-top">
    <div class="main-content">
        <!--Navbar-->
        <?php include('vendor/inc/nav.php'); ?>
        <!--End Navbar-->

        <div id="wrapper">
            <!-- Sidebar -->
            <?php include('vendor/inc/sidebar.php'); ?>
            <!--End Sidebar-->

            <div id="content-wrapper">
                <div class="container-fluid">
                    <!-- Breadcrumbs-->
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="admin_panel.php">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active">View Sliders</li>
                    </ol>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-6 col-md-6">
                            <div class="card stats-card bg-primary text-white mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">Total Sliders</h5>
                                            <h2 class="mb-0"><?php echo $total_sliders; ?></h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-images fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-md-6">
                            <div class="card stats-card bg-success text-white mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">Active Sliders</h5>
                                            <h2 class="mb-0"><?php echo $active_sliders; ?></h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sliders Table -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fas fa-images"></i>
                            Sliders List
                            <a href="add_sliders.php" class="btn btn-primary float-right">
                                <i class="fas fa-plus"></i> Add New Slider
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th width="150">Preview</th>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th>Sort Order</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                            <tr>
                                                <td>
                                                    <div class="preview-container">
                                                        <?php if ($row['media_type'] == 'image') : ?>
                                                            <img src="../<?php echo $row['media_file']; ?>" alt="<?php echo $row['alt_text']; ?>">
                                                        <?php else : ?>
                                                            <video>
                                                                <source src="../<?php echo $row['media_file']; ?>" type="video/<?php echo $row['file_extension']; ?>">
                                                            </video>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $row['is_active'] ? 'success' : 'danger'; ?>">
                                                        <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $row['sort_order']; ?></td>
                                                <td>
                                                    <a href="add_sliders.php?edit=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <button class="btn btn-danger btn-sm delete-slider" data-id="<?php echo $row['id']; ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

                <!-- Sticky Footer -->
                <?php include("vendor/inc/footer.php"); ?>
            </div>
            <!-- /.content-wrapper -->
        </div>
        <!-- /#wrapper -->

        <!-- Scroll to Top Button-->
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>

        <!-- Logout Modal-->
        <?php include("vendor/inc/logout.php"); ?>

        <!-- Bootstrap core JavaScript-->
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Core plugin JavaScript-->
        <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

        <!-- Custom scripts for all pages-->
        <script src="vendor/js/sb-admin.min.js"></script>

        <script>
            $(document).ready(function() {
                // Initialize DataTable
                $('#dataTable').DataTable();

                // Show success message if exists
                <?php if (isset($success)) : ?>
                Swal.fire({
                    title: 'Success!',
                    text: '<?php echo $success; ?>',
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                });
                <?php endif; ?>

                // Handle delete confirmation
                $('.delete-slider').click(function(e) {
                    e.preventDefault();
                    const sliderId = $(this).data('id');
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `add_sliders.php?delete=${sliderId}`;
                        }
                    });
                });
            });
        </script>
    </div>
</body>
</html> 