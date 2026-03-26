<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include('../db.php');
$aid = $_SESSION['admin_id'];

$title = $description = $alt_text = $link_url = '';
$sort_order = 0;
$is_active = 1;
$error = $success = '';
$edit_id = null;
$current_file = '';

// Handle Delete Request
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Get file path before deleting
    $file_query = "SELECT media_file FROM sliders WHERE id = '$delete_id'";
    $file_result = mysqli_query($conn, $file_query);
    if ($file_row = mysqli_fetch_assoc($file_result)) {
        $file_path = '../' . $file_row['media_file'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    mysqli_query($conn, "DELETE FROM sliders WHERE id = '$delete_id'");
    header("Location: manage_sliders.php?success=2");
    exit;
}

// Handle Edit Request
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $query = "SELECT * FROM sliders WHERE id = '$edit_id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $title = $row['title'];
        $description = $row['description'];
        $alt_text = $row['alt_text'];
        $link_url = $row['link_url'];
        $sort_order = $row['sort_order'];
        $is_active = $row['is_active'];
        $current_file = $row['media_file'];
    }
}

// Get max sort order
$max_sort_query = "SELECT MAX(sort_order) as max_sort FROM sliders";
$max_sort_result = mysqli_query($conn, $max_sort_query);
$max_sort_row = mysqli_fetch_assoc($max_sort_result);
$next_sort_order = ($max_sort_row['max_sort'] ?? 0) + 1;

if ($sort_order == 0) {
    $sort_order = $next_sort_order;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $alt_text = mysqli_real_escape_string($conn, $_POST['alt_text']);
    $link_url = mysqli_real_escape_string($conn, $_POST['link_url']);
    $sort_order = (int)$_POST['sort_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title)) {
        $error = "Title is required";
    } else {
        $upload_dir = '../uploads/sliders/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $media_file = '';
        $media_type = '';
        $file_extension = '';
        $file_size = 0;

        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] == 0) {
            $allowed_image_types = [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp', 
                'image/bmp', 'image/tiff', 'image/svg+xml'
            ];
            $allowed_video_types = [
                'video/mp4', 'video/webm', 'video/mpeg', 'video/quicktime', 
                'video/x-msvideo', 'video/x-ms-wmv', 'video/3gpp'
            ];
            $max_file_size = 100 * 1024 * 1024; // 100MB

            $file = $_FILES['media_file'];
            $file_type = $file['type'];
            $file_size = $file['size'];

            if (in_array($file_type, $allowed_image_types)) {
                $media_type = 'image';
            } elseif (in_array($file_type, $allowed_video_types)) {
                $media_type = 'video';
            } else {
                $error = "Invalid file type. Allowed types:<br>Images: JPG, JPEG, PNG, GIF, WEBP, BMP, TIFF, SVG<br>Videos: MP4, WEBM, MPEG, MOV, AVI, WMV, 3GP";
            }

            if ($file_size > $max_file_size) {
                $error = "File size too large. Maximum size allowed is 100MB";
            }

            if (empty($error)) {
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $unique_name = 'slider_' . uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $unique_name;

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $media_file = 'uploads/sliders/' . $unique_name;

                    // Delete old file if exists
                    if ($edit_id && !empty($current_file)) {
                        $old_file = '../' . $current_file;
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                } else {
                    $error = "Failed to upload file";
                }
            }
        }

        if (empty($error)) {
            if ($edit_id) {
                $update_query = "UPDATE sliders SET 
                    title = '$title',
                    description = '$description',
                    alt_text = '$alt_text',
                    link_url = '$link_url',
                    sort_order = $sort_order,
                    is_active = $is_active";
                
                if (!empty($media_file)) {
                    $update_query .= ",
                        media_file = '$media_file',
                        media_type = '$media_type',
                        file_extension = '$file_extension',
                        file_size = $file_size";
                }
                
                $update_query .= " WHERE id = '$edit_id'";
                
                if (mysqli_query($conn, $update_query)) {
                    $success = "Slider updated successfully";
                } else {
                    $error = "Error updating slider: " . mysqli_error($conn);
                }
            } else {
                if (empty($media_file)) {
                    $error = "Please upload a file";
                } else {
                    $insert_query = "INSERT INTO sliders (
                        title, description, media_file, media_type, 
                        file_extension, file_size, alt_text, link_url, 
                        sort_order, is_active
                    ) VALUES (
                        '$title', '$description', '$media_file', '$media_type',
                        '$file_extension', $file_size, '$alt_text', '$link_url',
                        $sort_order, $is_active
                    )";
                    
                    if (mysqli_query($conn, $insert_query)) {
                        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                            // AJAX request
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'message' => 'Slider added successfully!']);
                            exit;
                        } else {
                            // Regular form submission
                            $_SESSION['swal_success'] = "Slider added successfully!";
                            header("Location: manage_sliders.php?success=2");
                            exit;
                        }
                    } else {
                        $error = "Error adding slider: " . mysqli_error($conn);
                        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                            header('Content-Type: application/json');
                            http_response_code(500);
                            echo json_encode(['success' => false, 'message' => $error]);
                            exit;
                        }
                    }
                }
            }
        }
    }
}

// Handle SweetAlert2 Success Message
if (isset($_SESSION['swal_success'])) {
    $success = $_SESSION['swal_success'];
    unset($_SESSION['swal_success']);
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
?>

<!DOCTYPE html>
<html lang="en">

<!--Head-->
<?php include('vendor/inc/head.php'); ?>
<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    .preview-container {
        max-width: 300px;
        margin: 10px 0;
        position: relative;
    }
    .preview-container img,
    .preview-container video {
        max-width: 100%;
        height: auto;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .file-info {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 5px;
    }
    .custom-file-label::after {
        content: "Browse";
    }
    .form-group label {
        font-weight: 500;
    }
    .stats-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
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
                        <li class="breadcrumb-item">
                            <a href="manage_sliders.php">Sliders</a>
                        </li>
                        <li class="breadcrumb-item active"><?php echo $edit_id ? 'Edit' : 'Add'; ?> Slider</li>
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

                    <!-- Slider Form -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fas fa-image"></i>
                            <?php echo $edit_id ? 'Edit' : 'Add New'; ?> Slider
                        </div>
                        <div class="card-body">
                            <?php if ($error) : ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo $error; ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                            <?php if ($success) : ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo $success; ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <form method="post" enctype="multipart/form-data" id="sliderForm">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="title">Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label for="media_file">Media File <?php echo $edit_id ? '' : '<span class="text-danger">*</span>'; ?></label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="media_file" name="media_file" <?php echo $edit_id ? '' : 'required'; ?>>
                                                <label class="custom-file-label" for="media_file">Choose file</label>
                                            </div>
                                            <div class="file-info mt-2">
                                                <strong>Supported formats:</strong><br>
                                                Images: JPG, JPEG, PNG, GIF, WEBP, BMP, TIFF, SVG<br>
                                                Videos: MP4, WEBM, MPEG, MOV, AVI, WMV, 3GP<br>
                                                <strong>Maximum file size:</strong> 100MB
                                            </div>
                                        </div>

                                        <?php if ($edit_id && $current_file): ?>
                                            <div class="preview-container">
                                                <h6>Current File:</h6>
                                                <?php
                                                $file_extension = strtolower(pathinfo($current_file, PATHINFO_EXTENSION));
                                                $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg']);
                                                ?>
                                                <?php if ($is_image): ?>
                                                    <img src="../<?php echo $current_file; ?>" alt="Current slider">
                                                <?php else: ?>
                                                    <video controls>
                                                        <source src="../<?php echo $current_file; ?>" type="video/<?php echo $file_extension; ?>">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="alt_text">Alt Text</label>
                                            <input type="text" class="form-control" id="alt_text" name="alt_text" value="<?php echo htmlspecialchars($alt_text); ?>">
                                            <small class="form-text text-muted">Describe the image for SEO and accessibility</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="link_url">Link URL</label>
                                            <input type="url" class="form-control" id="link_url" name="link_url" value="<?php echo htmlspecialchars($link_url); ?>">
                                            <small class="form-text text-muted">Where should this slider link to?</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="sort_order">Sort Order</label>
                                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?php echo $sort_order; ?>">
                                            <small class="form-text text-muted">Lower numbers appear first</small>
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" <?php echo $is_active ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="is_active">Active</label>
                                            </div>
                                            <small class="form-text text-muted">Toggle to show/hide this slider</small>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save mr-2"></i><?php echo $edit_id ? 'Update' : 'Save'; ?> Slider
                                    </button>
                                </div>
                            </form>
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
            // SweetAlert for success
            <?php if ($success) : ?>
            Swal.fire({
                title: 'Success!',
                text: '<?php echo addslashes($success); ?>',
                icon: 'success',
                confirmButtonColor: '#28a745',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'manage_sliders.php';
            });
            <?php endif; ?>

            // SweetAlert for error
            <?php if ($error) : ?>
            Swal.fire({
                title: 'Error!',
                text: '<?php echo addslashes($error); ?>',
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'OK'
            });
            <?php endif; ?>

            // Update custom file input label
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
                // Preview image/video
                if (this.files && this.files[0]) {
                    let file = this.files[0];
                    let reader = new FileReader();
                    let previewContainer = $('.preview-container');
                    if (!previewContainer.length) {
                        previewContainer = $('<div class="preview-container mt-3"><h6>Preview:</h6></div>');
                        $(this).closest('.form-group').append(previewContainer);
                    }
                    reader.onload = function(e) {
                        if (file.type.startsWith('image/')) {
                            previewContainer.html(`
                                <h6>Preview:</h6>
                                <img src="${e.target.result}" class="img-fluid" alt="Preview">
                            `);
                        } else if (file.type.startsWith('video/')) {
                            previewContainer.html(`
                                <h6>Preview:</h6>
                                <video controls class="img-fluid">
                                    <source src="${e.target.result}" type="${file.type}">
                                    Your browser does not support the video tag.
                                </video>
                            `);
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Form validation and submission
            $('#sliderForm').on('submit', function(e) {
                e.preventDefault();
                
                let title = $('#title').val().trim();
                if (!title) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please enter a title.'
                    });
                    $('#title').focus();
                    return false;
                }
                
                let fileInput = $('#media_file');
                if (!<?php echo $edit_id ? 'false' : 'true'; ?> && fileInput[0].files.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select a file.'
                    });
                    return false;
                }

                // Show loading state
                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait while we save the slider',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit form via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Slider saved successfully!',
                            showConfirmButton: true,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = 'manage_sliders.php?success=2';
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Failed to save slider. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage
                        });
                    }
                });
            });
        </script>
    </div>
</body>

</html> 