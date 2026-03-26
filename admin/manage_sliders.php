<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include('../db.php');
$aid = $_SESSION['admin_id'];

// Handle Delete Operation
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $slider_id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // First get the media file path to delete the file
    $query = "SELECT media_file FROM sliders WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $slider_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $media_file = '../' . $row['media_file'];
        if (file_exists($media_file)) {
            unlink($media_file); // Delete the file
        }
    }
    
    // Now delete the database record
    $delete_query = "DELETE FROM sliders WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $slider_id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: manage_sliders.php?success=1");
        exit;
    } else {
        $error = "Failed to delete slider. Please try again.";
    }
}

// Handle Edit Operation
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $slider_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $query = "SELECT * FROM sliders WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $slider_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $slider = mysqli_fetch_assoc($result);
}

// Handle Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_slider'])) {
    $slider_id = mysqli_real_escape_string($conn, $_POST['slider_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $link_url = mysqli_real_escape_string($conn, $_POST['link_url']);
    $sort_order = mysqli_real_escape_string($conn, $_POST['sort_order']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $alt_text = mysqli_real_escape_string($conn, $_POST['alt_text']);
    $error = null;
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

    // Check if new media file is uploaded
    if (!empty($_FILES['media_file']['name'])) {
        $file = $_FILES['media_file'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_error = $file['error'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm'];
        $max_file_size = 10 * 1024 * 1024; // 10MB
        
        if ($file_error !== 0) {
            $error = "File upload failed. Error code: " . $file_error;
        } elseif (!in_array($file_ext, $allowed_extensions)) {
            $error = "Invalid file type. Allowed types: " . implode(', ', $allowed_extensions);
        } elseif ($file_size > $max_file_size) {
            $error = "File is too large. Maximum size allowed is 10MB";
        } else {
            // Generate unique filename
            $new_filename = 'slider_' . uniqid() . '_' . time() . '.' . $file_ext;
            $upload_dir = '../uploads/sliders/';
            $upload_path = $upload_dir . $new_filename;
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    $error = "Failed to create upload directory";
                }
            }
            
            if (!$error) {
                // Delete old file if exists
                $query = "SELECT media_file FROM sliders WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $slider_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    $old_file = '../' . $row['media_file'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                
                // Upload new file
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $media_file = 'uploads/sliders/' . $new_filename;
                    $media_type = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image' : 'video';
                    
                    $update_query = "UPDATE sliders SET title=?, description=?, link_url=?, sort_order=?, is_active=?, 
                                   alt_text=?, media_file=?, media_type=?, file_extension=? WHERE id=?";
                    $stmt = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param($stmt, "sssiisssssi", $title, $description, $link_url, $sort_order, $is_active, 
                                         $alt_text, $media_file, $media_type, $file_ext, $slider_id);
                } else {
                    $error = "Failed to move uploaded file";
                }
            }
        }
    } else {
        // Update without changing media file
        $update_query = "UPDATE sliders SET title=?, description=?, link_url=?, sort_order=?, is_active=?, 
                       alt_text=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "sssiisi", $title, $description, $link_url, $sort_order, $is_active, 
                             $alt_text, $slider_id);
    }
    
    if (!$error && mysqli_stmt_execute($stmt)) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Slider updated successfully']);
            exit;
        } else {
            header("Location: manage_sliders.php?success=3");
            exit;
        }
    } else {
        $error = $error ?: "Failed to update slider. Please try again. " . mysqli_error($conn);
        if ($is_ajax) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }
    }
}

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
?>

<!DOCTYPE html>
<html lang="en">

<!--Head-->
<?php include('vendor/inc/head.php'); ?>
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
    .badge {
        font-size: 85%;
        padding: 0.5em 1em;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
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

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                            if ($_GET['success'] == 1) {
                                echo "Slider deleted successfully!";
                            } elseif ($_GET['success'] == 2) {
                                echo "Slider added successfully!";
                            } elseif ($_GET['success'] == 3) {
                                echo "Slider updated successfully!";
                            }
                            ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

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

                    <!-- Add this after the Stats Cards section and before the Sliders Table -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

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
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Sort Order</th>
                                            <th>Created At</th>
                                            <th width="120">Actions</th>
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
                                                            <video controls>
                                                                <source src="../<?php echo $row['media_file']; ?>" type="video/<?php echo $row['file_extension']; ?>">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                                                    <?php if ($row['description']) : ?>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($row['description']); ?></small>
                                                    <?php endif; ?>
                                                    <?php if ($row['link_url']) : ?>
                                                        <br>
                                                        <small><a href="<?php echo htmlspecialchars($row['link_url']); ?>" target="_blank"><i class="fas fa-external-link-alt"></i> View Link</a></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-<?php echo $row['media_type'] == 'image' ? 'image' : 'video'; ?>"></i>
                                                        <?php echo ucfirst($row['media_type']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $row['is_active'] ? 'success' : 'danger'; ?>">
                                                        <i class="fas fa-<?php echo $row['is_active'] ? 'check' : 'times'; ?>"></i>
                                                        <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $row['sort_order']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="#" class="btn btn-info edit-slider" data-id="<?php echo $row['id']; ?>" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="#" class="btn btn-danger delete-slider" data-id="<?php echo $row['id']; ?>" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
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

        <!-- Edit Slider Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Slider</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form action="" method="POST" enctype="multipart/form-data" id="editSliderForm">
                        <div class="modal-body">
                            <input type="hidden" name="edit_slider" value="1">
                            <input type="hidden" name="slider_id" id="edit_slider_id">
                            
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="edit_title" name="title" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="link_url">Link URL</label>
                                <input type="url" class="form-control" id="edit_link_url" name="link_url">
                            </div>
                            
                            <div class="form-group">
                                <label for="alt_text">Alt Text</label>
                                <input type="text" class="form-control" id="edit_alt_text" name="alt_text">
                            </div>
                            
                            <div class="form-group">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" class="form-control" id="edit_sort_order" name="sort_order" value="0" min="0">
                            </div>
                            
                            <div class="form-group">
                                <label for="media_file">Media File</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="edit_media_file" name="media_file" accept="image/*,video/*">
                                    <label class="custom-file-label" for="media_file">Choose file</label>
                                </div>
                                <small class="form-text text-muted">Leave empty to keep current file</small>
                                <div id="preview_container" class="mt-2" style="display: none;">
                                    <img id="preview_image" src="" alt="Preview" style="max-width: 200px;">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active">
                                    <label class="custom-control-label" for="edit_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">Are you sure you want to delete this slider? This action cannot be undone.</div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <a class="btn btn-danger" id="confirmDelete" href="#">Delete</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logout Modal-->
        <?php include("vendor/inc/logout.php"); ?>

        <!-- Bootstrap core JavaScript-->
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- Core plugin JavaScript-->
        <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

        <!-- Page level plugin JavaScript-->
        <script src="vendor/datatables/jquery.dataTables.js"></script>
        <script src="vendor/datatables/dataTables.bootstrap4.js"></script>

        <!-- Custom scripts for all pages-->
        <script src="vendor/js/sb-admin.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Demo scripts for this page-->
        <script src="vendor/js/demo/datatables-demo.js"></script>

        <script>
            $(document).ready(function() {
                // Check if DataTable is already initialized
                if (!$.fn.DataTable.isDataTable('#dataTable')) {
                    $('#dataTable').DataTable({
                        "order": [[4, "asc"], [5, "desc"]], 
                        "pageLength": 25,
                        "language": {
                            "lengthMenu": "Show _MENU_ sliders per page",
                            "zeroRecords": "No sliders found",
                            "info": "Showing page _PAGE_ of _PAGES_",
                            "infoEmpty": "No sliders available",
                            "infoFiltered": "(filtered from _MAX_ total sliders)"
                        }
                    });
                }

                // Handle delete confirmation
                $('.delete-slider').on('click', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'manage_sliders.php?delete=' + id;
                        }
                    });
                });

                // Handle edit button click
                $('.edit-slider').on('click', function(e) {
                    e.preventDefault();
                    var row = $(this).closest('tr');
                    var id = $(this).data('id');
                    
                    // Populate the form with current values
                    $('#edit_slider_id').val(id);
                    $('#edit_title').val(row.find('td:eq(1) strong').text().trim());
                    $('#edit_description').val(row.find('td:eq(1) .text-muted').text().trim());
                    $('#edit_link_url').val(row.find('td:eq(1) a').attr('href'));
                    $('#edit_sort_order').val(row.find('td:eq(4)').text().trim());
                    $('#edit_is_active').prop('checked', row.find('td:eq(3) .badge-success').length > 0);
                    $('#edit_alt_text').val(row.find('td:eq(0) img').attr('alt'));
                    
                    // Reset file input and preview
                    $('#edit_media_file').val('');
                    $('.custom-file-label').text('Choose file');
                    $('#preview_container').hide();
                    
                    // Show the modal
                    $('#editModal').modal('show');
                });

                // File input change handler
                $('#edit_media_file').on('change', function() {
                    var file = this.files[0];
                    if (file) {
                        // Validate file size (10MB max)
                        if (file.size > 10 * 1024 * 1024) {
                            Swal.fire({
                                icon: 'error',
                                title: 'File Too Large',
                                text: 'Maximum file size allowed is 10MB'
                            });
                            this.value = '';
                            return;
                        }

                        // Validate file type
                        var validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm'];
                        if (!validTypes.includes(file.type)) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid File Type',
                                text: 'Please upload an image (JPG, PNG, GIF, WEBP) or video (MP4, WEBM)'
                            });
                            this.value = '';
                            return;
                        }

                        $('.custom-file-label').text(file.name);
                        
                        // Show preview for images
                        if (file.type.startsWith('image/')) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                $('#preview_image').attr('src', e.target.result);
                                $('#preview_container').show();
                            }
                            reader.readAsDataURL(file);
                        }
                    }
                });

                // Handle form submission
                $('#editSliderForm').on('submit', function(e) {
                    e.preventDefault();
                    
                    var formData = new FormData(this);
                    
                    // Show loading state
                    Swal.fire({
                        title: 'Uploading...',
                        text: 'Please wait while we update the slider',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: $(this).attr('action'),
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            // Close the modal
                            $('#editModal').modal('hide');
                            
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Slider updated successfully',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                // Reload the page to show updated data
                                window.location.reload();
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to update slider. Please try again.'
                            });
                        }
                    });
                });

                // Auto-hide alerts after 5 seconds
                setTimeout(function() {
                    $('.alert').alert('close');
                }, 5000);
            });
        </script>
    </div>
</body>

</html> 