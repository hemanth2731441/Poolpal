<?php
  session_start();
  if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
  }
  
    include('../db.php');
      $aid=$_SESSION['admin_id'];
  
  // Define a base64 data URI for fallback no-image
  $noImageBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAA8CAYAAACQPx/OAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAGbSURBVHhe7dxBTsMwEIVh99QcAXEBuAGr3qYrEJdA3XYJbsEVWPUG9QbQE7QnQEwJdmhAIGideTb5/8WTUZXFi6enJEpncwAAAAAAAAAAYPS+bde/7/N8cbgohXVd7zVNjYUQ7kIInbWY/Lxrt9l6E3RQftls3h/z/DEPI92I0yXrjzhQbmjd5Cl9hNqXj3tlfYh5LOXNrqxbo70+5Uw3Iy/S80Mn61OW19qXj3sfsieMeOkn9OXj3ofsCSOeHmL78nHvQ/aEkfZ9w6q1Lx/3PmQvjLhYr6f9+Lj3IUfCiJcY+vJx70P2hJE8fdW+fNz7kL0w4rrRvnzc+5Az3YyTvwOjL19L5k6R2SB+le/VNHVcSvw6HP7+HVL9ivtqdSvGxk4n61KM3dE6hmVZLuVhRCvtM55OJ+tUjIW5VtUoJhwb49BrG6Vy66b6FRKTGpow4mIwsjRhxEWJMS/CiJcY84UmjLjThBEXW1PNhBGnCSMuUrhrJYy4GIysDnUYmTz8Qe0Od1EDAAAAAAAAgNGazf4A8TxG5wXuDx8AAAAASUVORK5CYII=';
?>
<!DOCTYPE html>
<html lang="en">

<?php include('vendor/inc/head.php');?>
<style>
  /* Modern Button Styles with Advanced Effects */
  .verification-controls {
    display: flex;
    gap: 10px;
  }
  
  .btn-modern {
    position: relative;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    letter-spacing: 0.5px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    text-transform: uppercase;
    font-size: 12px;
  }
  
  .btn-modern:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transform: translateX(-100%);
    transition: transform 0.6s;
  }
  
  .btn-modern:hover:before {
    transform: translateX(100%);
  }
  
  .btn-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 7px 14px rgba(0, 0, 0, 0.15);
  }
  
  .btn-modern:active {
    transform: translateY(1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  }
  
  .btn-accept {
    background: linear-gradient(45deg, #00b09b, #96c93d);
    color: white;
  }
  
  .btn-reject {
    background: linear-gradient(45deg, #ff5f6d, #ffc371);
    color: white;
  }
  
  .btn-modern i {
    margin-right: 6px;
    position: relative;
    z-index: 2;
    transition: transform 0.3s ease;
  }
  
  .btn-modern:hover i {
    transform: scale(1.2);
  }
  
  .btn-modern span {
    position: relative;
    z-index: 2;
  }
  
  /* Status Badges */
  .status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 13px;
    letter-spacing: 0.3px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
  }
  
  .status-badge i {
    margin-right: 6px;
    animation: pulse 1.5s infinite;
  }
  
  .status-accepted {
    background: linear-gradient(135deg, #43c6ac, #28a745);
    color: white;
  }
  
  .status-rejected {
    background: linear-gradient(135deg, #ff4b1f, #dc3545);
    color: white;
  }
  
  @keyframes pulse {
    0% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.2);
    }
    100% {
      transform: scale(1);
    }
  }
  
  /* Hover animations */
  @keyframes buttonGlow {
    0% {
      box-shadow: 0 0 5px rgba(0, 177, 106, 0.5);
    }
    50% {
      box-shadow: 0 0 20px rgba(0, 177, 106, 0.7);
    }
    100% {
      box-shadow: 0 0 5px rgba(0, 177, 106, 0.5);
    }
  }
  
  .btn-accept:hover {
    animation: buttonGlow 1.5s infinite;
  }
  
  @keyframes rejectGlow {
    0% {
      box-shadow: 0 0 5px rgba(220, 53, 69, 0.5);
    }
    50% {
      box-shadow: 0 0 20px rgba(220, 53, 69, 0.7);
    }
    100% {
      box-shadow: 0 0 5px rgba(220, 53, 69, 0.5);
    }
  }
  
  .btn-reject:hover {
    animation: rejectGlow 1.5s infinite;
  }
  
  /* Confetti animation */
  .confetti {
    position: absolute;
    width: 8px;
    height: 8px;
    background-color: #f2d74e;
    opacity: 0;
    z-index: 9999;
    animation: confetti-fall 3s ease-out forwards;
    pointer-events: none;
  }
  
  @keyframes confetti-fall {
    0% {
      opacity: 1;
      transform: translateY(0) rotateZ(0deg);
    }
    100% {
      opacity: 0;
      transform: translateY(100px) rotateZ(360deg);
    }
  }
  
  /* Image Lightbox Styles */
  .image-lightbox-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
  }
  
  .lightbox-content {
    position: relative;
    margin: 5% auto;
    width: 80%;
    max-width: 800px;
  }
  
  .lightbox-close {
    position: absolute;
    top: -40px;
    right: -40px;
    color: white;
    font-size: 35px;
    font-weight: bold;
    cursor: pointer;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: rgba(0, 0, 0, 0.4);
  }
  
  .lightbox-close:hover {
    background-color: rgba(255, 255, 255, 0.2);
  }
  
  .lightbox-image-container {
    width: 100%;
    padding-bottom: 10px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    border-radius: 10px;
    background-color: #000;
  }
  
  .lightbox-image-container img {
    display: block;
    width: 100%;
    height: auto;
    object-fit: contain;
  }
  
  /* Image thumbnail hover effect */
  .license-thumbnail {
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 6px;
    border: 2px solid transparent;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
  
  .license-thumbnail:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    border-color: #5e72e4;
  }
  
  /* Add animation for image zoom effect */
  @keyframes pulse-glow {
    0% {
      box-shadow: 0 0 10px rgba(94, 114, 228, 0.5);
    }
    50% {
      box-shadow: 0 0 20px rgba(94, 114, 228, 0.8);
    }
    100% {
      box-shadow: 0 0 10px rgba(94, 114, 228, 0.5);
    }
  }
  
  .license-thumbnail:hover {
    animation: pulse-glow 1.5s infinite;
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
          <li class="breadcrumb-item active">View Drivers</li>
        </ol>
        
        <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']);
          ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <?php 
            echo $_SESSION['error']; 
            unset($_SESSION['error']);
          ?>
        </div>
        <?php endif; ?>

        <!-- DataTables Example -->
        <div class="card mb-3">
          <div class="card-header">
            <i class="fas fa-users"></i>
            Registered Users</div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Profile Picture</th>
                    <th>Aadhar Card</th>
                    <th>Driving License</th>
                    <th>RC</th>
                    <th>Vehicle Number</th>
                    <th>Verification</th>
                  </tr>
                </thead>
                <?php

                    $ret="SELECT * FROM drivers ORDER BY RAND() LIMIT 1000  "; 
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
                        <?php if(!empty($row->Profile_Pic)): ?>
                        <img src="../<?php echo $row->Profile_Pic; ?>" alt="Profile Picture" width="100" height="60" onerror="this.onerror=null; this.src='<?php echo $noImageBase64; ?>'; this.style.opacity=0.7;">
                        <?php else: ?>
                        <img src="<?php echo $noImageBase64; ?>" alt="No Profile Picture" width="100" height="60" style="opacity: 0.7;">
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!empty($row->Aadhar)): ?>
                        <img src="../<?php echo $row->Aadhar; ?>" alt="Aadhar Card" width="100" height="60" class="license-thumbnail" data-full-image="../<?php echo $row->Aadhar; ?>" onerror="this.onerror=null; this.src='<?php echo $noImageBase64; ?>'; this.style.opacity=0.7;">
                        <?php else: ?>
                        <img src="<?php echo $noImageBase64; ?>" alt="No Aadhar Card" width="100" height="60" style="opacity: 0.7;">
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!empty($row->Driving_License)): ?>
                        <img src="../<?php echo $row->Driving_License; ?>" alt="License" width="100" height="60" class="license-thumbnail" data-full-image="../<?php echo $row->Driving_License; ?>" onerror="this.onerror=null; this.src='<?php echo $noImageBase64; ?>'; this.style.opacity=0.7;">
                        <?php else: ?>
                        <img src="<?php echo $noImageBase64; ?>" alt="No License" width="100" height="60" style="opacity: 0.7;">
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!empty($row->RC)): ?>
                        <img src="../<?php echo $row->RC; ?>" alt="RC" width="100" height="60" class="license-thumbnail" data-full-image="../<?php echo $row->RC; ?>" onerror="this.onerror=null; this.src='<?php echo $noImageBase64; ?>'; this.style.opacity=0.7;">
                        <?php else: ?>
                        <img src="<?php echo $noImageBase64; ?>" alt="No RC" width="100" height="60" style="opacity: 0.7;">
                        <?php endif; ?>
                    </td>
                    <td><?php echo $row->Vehicle_Number;?></td>
                    <td>
                        <?php if(isset($row->verification_status) && $row->verification_status == 'accepted'): ?>
                            <span class="status-badge status-accepted">
                                <i class="fas fa-check-circle"></i>
                                <span>Accepted</span>
                            </span>
                        <?php elseif(isset($row->verification_status) && $row->verification_status == 'rejected'): ?>
                            <span class="status-badge status-rejected">
                                <i class="fas fa-times-circle"></i>
                                <span>Rejected</span>
                            </span>
                        <?php else: ?>
                            <div class="verification-controls">
                                <a href="verify-driver.php?id=<?php echo $row->Email; ?>&action=accept" class="btn-modern btn-accept" data-toggle="tooltip" title="Accept Driver">
                                    <i class="fas fa-check"></i>
                                    <span>Accept</span>
                                </a>
                                <a href="verify-driver.php?id=<?php echo $row->Email; ?>&action=reject" class="btn-modern btn-reject" data-toggle="tooltip" title="Reject Driver">
                                    <i class="fas fa-times"></i>
                                    <span>Reject</span>
                                </a>
                            </div>
                        <?php endif; ?>
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
  
  <!-- Advanced interactive effects -->
  <script>
    $(function () {
      // Initialize tooltips with custom animation
      $('[data-toggle="tooltip"]').tooltip({
        animation: true,
        delay: { show: 100, hide: 300 }
      });
      
      // Add hover effect on rows with smooth animation
      $('.table-hover tbody tr').hover(
        function() {
          $(this).addClass('bg-light');
          $(this).css('transition', 'background-color 0.3s ease');
        },
        function() {
          $(this).removeClass('bg-light');
        }
      );
      
      // Add click animation for buttons
      $('.btn-modern').on('mousedown', function() {
        $(this).css('transform', 'scale(0.95)');
      }).on('mouseup mouseleave', function() {
        $(this).css('transform', '');
      });
      
      // Add subtle animation to status badges
      $('.status-badge').each(function() {
        $(this).on('mouseenter', function() {
          $(this).css('transform', 'scale(1.05)');
        }).on('mouseleave', function() {
          $(this).css('transform', 'scale(1)');
        });
      });
      
      // Confetti effect for Accept button
      $('.btn-accept').on('click', function(e) {
        // Create confetti
        createConfetti(e.clientX, e.clientY);
      });
      
      function createConfetti(x, y) {
        const colors = ['#00b09b', '#96c93d', '#ffeb3b', '#4CAF50', '#2196F3'];
        
        // Create 30 confetti particles
        for (let i = 0; i < 30; i++) {
          const confetti = $('<div class="confetti"></div>');
          $('body').append(confetti);
          
          // Randomize confetti properties
          const color = colors[Math.floor(Math.random() * colors.length)];
          const size = Math.floor(Math.random() * 10) + 5;
          const angle = Math.random() * 360;
          const spread = 100;
          
          // Random position around click
          const posX = x + (Math.random() - 0.5) * spread;
          const posY = y + (Math.random() - 0.5) * spread;
          
          // Apply random styles
          confetti.css({
            'background-color': color,
            'width': size + 'px',
            'height': size + 'px',
            'left': posX + 'px',
            'top': posY + 'px',
            'transform': 'rotate(' + angle + 'deg)'
          });
          
          // Remove after animation completes
          setTimeout(function() {
            confetti.remove();
          }, 3000);
        }
      }
      
      // Simple lightbox functionality for driving license images
      $('.license-thumbnail').on('click', function() {
        const fullImageSrc = $(this).data('full-image');
        $('#lightboxImage').attr('src', fullImageSrc);
        
        // Add error handling for the lightbox image
        $('#lightboxImage').on('error', function() {
          $(this).attr('src', '<?php echo $noImageBase64; ?>');
          $(this).css('opacity', '0.7');
        });
        
        $('#imageLightboxModal').css('display', 'block');
      });
      
      // Close the lightbox when clicking the close button or outside the image
      $('.lightbox-close, .image-lightbox-modal').on('click', function(e) {
        if (e.target === this || $(e.target).hasClass('lightbox-close')) {
          $('#imageLightboxModal').css('display', 'none');
        }
      });
      
      // Prevent clicks on the image itself from closing the modal
      $('.lightbox-image-container').on('click', function(e) {
        e.stopPropagation();
      });
      
      // Add keyboard support
      $(document).keydown(function(e) {
        if (e.key === "Escape" && $('#imageLightboxModal').css('display') === 'block') {
          $('#imageLightboxModal').css('display', 'none');
        }
      });
    });
  </script>

  <!-- Image Lightbox Modal -->
  <div class="image-lightbox-modal" id="imageLightboxModal">
    <div class="lightbox-content">
      <span class="lightbox-close">&times;</span>
      <div class="lightbox-image-container">
        <img src="" id="lightboxImage" alt="Full size image">
      </div>
    </div>
  </div>

</div></body>

</html>