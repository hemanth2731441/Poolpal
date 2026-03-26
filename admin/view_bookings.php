<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include('../db.php');
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
                        <li class="breadcrumb-item active">Bookings</li>
                    </ol>

                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 mb-0 text-gray-800">All Bookings</h1>
                        <div class="d-flex">
                            <button class="btn btn-success mr-2" onclick="exportToExcel()">
                                <i class="fas fa-file-excel mr-2"></i>Export to Excel
                            </button>
                            <button class="btn btn-primary mr-2" onclick="exportToCSV()">
                                <i class="fas fa-file-csv mr-2"></i>Export to CSV
                            </button>
                            <button class="btn btn-danger" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf mr-2"></i>Export to PDF
                            </button>
                        </div>
                    </div>

                    <!-- Filters Section -->
                    <div class="filter-section mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date Range</label>
                                    <input type="text" class="form-control" id="daterange" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>User</label>
                                    <input type="text" class="form-control" id="userFilter" placeholder="Search by user name/email/phone">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Trip (From/To)</label>
                                    <input type="text" class="form-control" id="tripFilter" placeholder="Search by from/to">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Payment Status</label>
                                    <select class="form-control" id="paymentFilter">
                                        <option value="">All</option>
                                        <option value="pending">Pending</option>
                                        <option value="completed">Completed</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bookings Table -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fas fa-table"></i>
                            All Bookings
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="bookingsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>User Details</th>
                                            <th>Trip Details</th>
                                            <th>Seats</th>
                                            <th>Amount</th>
                                            <th>Payment Status</th>
                                            <th>Booking Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT 
                                            b.*,
                                            u.full_name as user_name,
                                            u.phone as user_phone,
                                            u.email as user_email,
                                            t.departure_city,
                                            t.destination_city,
                                            t.departure_date,
                                            t.departure_time,
                                            t.driver_name,
                                            t.vehicle_number
                                        FROM bookings b 
                                        LEFT JOIN users u ON b.user_id = u.id
                                        LEFT JOIN trips t ON b.trip_id = t.id
                                        ORDER BY b.booking_time DESC";
                                        
                                        $result = mysqli_query($conn, $query);

                                        if ($result && mysqli_num_rows($result) > 0) {
                                            while ($booking = mysqli_fetch_assoc($result)) {
                                                $paymentStatusClass = '';
                                                switch($booking['payment_status']) {
                                                    case 'completed':
                                                        $paymentStatusClass = 'badge-success';
                                                        break;
                                                    case 'pending':
                                                        $paymentStatusClass = 'badge-warning';
                                                        break;
                                                    case 'failed':
                                                        $paymentStatusClass = 'badge-danger';
                                                        break;
                                                    default:
                                                        $paymentStatusClass = 'badge-secondary';
                                                }
                                                ?>
                                                <tr>
                                                    <td>#<?php echo $booking['id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($booking['user_name'] ?? 'N/A'); ?></strong><br>
                                                        <small>Email: <?php echo htmlspecialchars($booking['user_email'] ?? 'N/A'); ?></small><br>
                                                        <small>Phone: <?php echo htmlspecialchars($booking['user_phone'] ?? 'N/A'); ?></small>
                                                    </td>
                                                    <td>
                                                        <strong>From:</strong> <?php echo htmlspecialchars($booking['departure_city'] ?? 'N/A'); ?><br>
                                                        <strong>To:</strong> <?php echo htmlspecialchars($booking['destination_city'] ?? 'N/A'); ?><br>
                                                        <small>Date: <?php echo $booking['departure_date'] ? date('d M Y', strtotime($booking['departure_date'])) : 'N/A'; ?></small><br>
                                                        <small>Time: <?php echo $booking['departure_time'] ? date('h:i A', strtotime($booking['departure_time'])) : 'N/A'; ?></small>
                                                    </td>
                                                    <td><?php echo $booking['seats_booked'] ?? 0; ?></td>
                                                    <td>₹<?php echo number_format($booking['total_amount'] ?? 0, 2); ?></td>
                                                    <td><span class="badge <?php echo $paymentStatusClass; ?>"><?php echo ucfirst($booking['payment_status']); ?></span></td>
                                                    <td><?php echo $booking['booking_time'] ? date('d M Y h:i A', strtotime($booking['booking_time'])) : 'N/A'; ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info view-details" data-booking-id="<?php echo $booking['id']; ?>" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if($booking['payment_status'] == 'pending'): ?>
                                                        <button class="btn btn-sm btn-success update-payment" data-booking-id="<?php echo $booking['id']; ?>" title="Update Payment">
                                                            <i class="fas fa-money-bill"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        <button class="btn btn-sm btn-danger cancel-booking" data-booking-id="<?php echo $booking['id']; ?>" title="Cancel Booking">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                        ?>
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

        <!-- Booking Details Modal -->
        <div class="modal fade" id="bookingDetailsModal" tabindex="-1" role="dialog" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bookingDetailsModalLabel">Booking Details</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body" id="bookingDetailsContent">
                        <!-- Content will be loaded dynamically -->
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
        <script src="vendor/js/sb-admin.min.js"></script>

        <!-- Demo scripts for this page-->
        <script src="vendor/js/demo/datatables-demo.js"></script>

        <!-- DataTables Buttons HTML5 Export Scripts -->
        <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" />

        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

        <script>
        $(document).ready(function() {
            // Helper: get column index by header text
            function getColIndex(header) {
                var idx = -1;
                $('#bookingsTable thead th').each(function(i) {
                    if ($(this).text().trim().toLowerCase() === header.toLowerCase()) idx = i;
                });
                return idx;
            }
            // Remove previous custom filters to avoid stacking
            $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) { return !fn._isCustomPoolpal; });

            // Initialize Date Range Picker
            $('#daterange').daterangepicker({
                opens: 'left',
                locale: { format: 'DD/MM/YYYY' }
            });
            // Initialize DataTable with export buttons
            var table = $('#bookingsTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy',
                    { extend: 'excelHtml5', title: 'All Bookings' },
                    { extend: 'csvHtml5', title: 'All Bookings' },
                    { extend: 'pdfHtml5', title: 'All Bookings', orientation: 'landscape', pageSize: 'A4' },
                    'print'
                ],
                pageLength: 25,
                order: [[getColIndex('Booking Date'), 'desc']]
            });
            // Custom filtering for all filters
            var customFilter = function(settings, data, dataIndex) {
                var dateIdx = getColIndex('Booking Date');
                var userIdx = getColIndex('User Details');
                var tripIdx = getColIndex('Trip Details');
                var paymentIdx = getColIndex('Payment Status');
                // Date Range
                var daterange = $('#daterange').val();
                if (daterange && dateIdx !== -1) {
                    var dateCol = data[dateIdx];
                    var range = daterange.split(' - ');
                    var min = moment(range[0], 'DD/MM/YYYY');
                    var max = moment(range[1], 'DD/MM/YYYY');
                    var bookingDate = moment(dateCol, 'DD MMM YYYY hh:mm A');
                    if (!bookingDate.isValid() || !bookingDate.isBetween(min, max, undefined, '[]')) return false;
                }
                // User
                var user = $('#userFilter').val().toLowerCase();
                if (user && userIdx !== -1) {
                    if (data[userIdx].toLowerCase().indexOf(user) === -1) return false;
                }
                // Trip (From/To)
                var trip = $('#tripFilter').val().toLowerCase();
                if (trip && tripIdx !== -1) {
                    if (data[tripIdx].toLowerCase().indexOf(trip) === -1) return false;
                }
                // Payment Status
                var payment = $('#paymentFilter').val();
                if (payment && paymentIdx !== -1) {
                    if (data[paymentIdx].toLowerCase().indexOf(payment) === -1) return false;
                }
                return true;
            };
            customFilter._isCustomPoolpal = true;
            $.fn.dataTable.ext.search.push(customFilter);
            $('#daterange, #userFilter, #tripFilter, #paymentFilter').on('change keyup', function() {
                table.draw();
            });

            // View Details Button Click
            $('.view-details').click(function() {
                var bookingId = $(this).data('booking-id');
                $.ajax({
                    url: 'get_booking_details.php',
                    type: 'POST',
                    data: {booking_id: bookingId},
                    success: function(response) {
                        $('#bookingDetailsContent').html(response);
                        $('#bookingDetailsModal').modal('show');
                    }
                });
            });

            // Update Payment Status
            $('.update-payment').click(function() {
                if(confirm('Are you sure you want to mark this payment as completed?')) {
                    var bookingId = $(this).data('booking-id');
                    $.ajax({
                        url: 'update_payment_status.php',
                        type: 'POST',
                        data: {
                            booking_id: bookingId,
                            status: 'completed'
                        },
                        success: function(response) {
                            location.reload();
                        }
                    });
                }
            });

            // Cancel Booking
            $('.cancel-booking').click(function() {
                if(confirm('Are you sure you want to cancel this booking?')) {
                    var bookingId = $(this).data('booking-id');
                    $.ajax({
                        url: 'cancel_booking.php',
                        type: 'POST',
                        data: {booking_id: bookingId},
                        success: function(response) {
                            location.reload();
                        }
                    });
                }
            });
        });

        // Export functions
        function exportToExcel() { $('.buttons-excel').click(); }
        function exportToCSV() { $('.buttons-csv').click(); }
        function exportToPDF() { $('.buttons-pdf').click(); }
        </script>
    </div>
</body>
</html>