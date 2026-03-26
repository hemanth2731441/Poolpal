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
                        <li class="breadcrumb-item active">Cancelled Bookings</li>
                    </ol>

                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Cancelled Bookings</h1>
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
                                    <label>Driver</label>
                                    <select class="form-control" id="driverFilter">
                                        <option value="">All Drivers</option>
                                        <?php
                                        $query = "SELECT ID, Full_Name FROM drivers WHERE status = 1";
                                        $result = mysqli_query($conn, $query);
                                        while($row = mysqli_fetch_assoc($result)) {
                                            echo "<option value='".$row['ID']."'>".$row['Full_Name']."</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" class="form-control" id="locationFilter" placeholder="Search by location">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Cancellation Reason</label>
                                    <select class="form-control" id="reasonFilter">
                                        <option value="">All</option>
                                        <option value="Schedule conflict">Schedule conflict</option>
                                        <option value="Emergency situation">Emergency situation</option>
                                        <option value="Change in travel plans">Change in travel plans</option>
                                        <option value="Found alternative transport">Found alternative transport</option>
                                        <option value="Weather conditions">Weather conditions</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cancellation Stats -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Cancellations</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalCancellations">Loading...</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Average Refund Time</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="avgRefundTime">Loading...</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Most Common Reason</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="commonReason">Loading...</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Refund Amount</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalRefund">Loading...</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cancelled Bookings Table -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fas fa-table"></i>
                            Cancelled Bookings
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="cancelledBookingsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Customer</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Date & Time</th>
                                            <th>Amount</th>
                                            <th>Reason</th>
                                            <th>Cancelled At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT cb.*, u.full_name as user_name 
                                                 FROM cancelled_bookings cb 
                                                 LEFT JOIN users u ON cb.user_email = u.email 
                                                 ORDER BY cb.cancelled_at DESC";
                                        
                                        $result = mysqli_query($conn, $query);
                                        
                                        if (!$result) {
                                            echo "Error: " . mysqli_error($conn);
                                        } else {
                                            while($row = mysqli_fetch_assoc($result)) {
                                                echo "<tr>";
                                                echo "<td>#".$row['id']."</td>";
                                                echo "<td>".($row['user_name'] ?? $row['user_email'])."</td>";
                                                echo "<td>".$row['departure_city']."</td>";
                                                echo "<td>".$row['destination_city']."</td>";
                                                echo "<td>".date('d M Y H:i', strtotime($row['departure_date'] . ' ' . $row['departure_time']))."</td>";
                                                echo "<td>₹".$row['price']."</td>";
                                                echo "<td>".$row['cancellation_reason']."</td>";
                                                echo "<td>".date('d M Y H:i', strtotime($row['cancelled_at']))."</td>";
                                                echo "<td class='action-buttons'>
                                                        <button class='btn btn-info btn-sm' onclick='viewDetails(".$row['id'].")'><i class='fas fa-eye'></i></button>
                                                        <button class='btn btn-success btn-sm' onclick='processRefund(".$row['id'].")'><i class='fas fa-sync'></i></button>
                                                      </td>";
                                                echo "</tr>";
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
        <div class="modal fade" id="bookingDetailsModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancellation Details</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
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

        <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" />
        <script>
            $(document).ready(function() {
                // Helper: get column index by header text
                function getColIndex(header) {
                    var idx = -1;
                    $('#cancelledBookingsTable thead th').each(function(i) {
                        if ($(this).text().trim().toLowerCase() === header.toLowerCase()) idx = i;
                    });
                    return idx;
                }
                // Remove previous custom filters to avoid stacking
                $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) { return !fn._isCustomPoolpal; });

                var table = $('#cancelledBookingsTable').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy',
                        { extend: 'excelHtml5', title: 'Cancelled Bookings' },
                        { extend: 'csvHtml5', title: 'Cancelled Bookings' },
                        { extend: 'pdfHtml5', title: 'Cancelled Bookings', orientation: 'landscape', pageSize: 'A4' },
                        'print'
                    ],
                    pageLength: 10,
                    order: [[getColIndex('Cancelled At'), 'desc']]
                });

                $('#daterange').daterangepicker({
                    opens: 'left',
                    locale: { format: 'DD/MM/YYYY' }
                });

                var customFilter = function(settings, data, dataIndex) {
                    var dateIdx = getColIndex('Date & Time');
                    var driverIdx = getColIndex('Customer'); // No driver column, so use Customer for now
                    var fromIdx = getColIndex('From');
                    var toIdx = getColIndex('To');
                    var reasonIdx = getColIndex('Reason');
                    var cancelledAtIdx = getColIndex('Cancelled At');
                    // Date Range
                    var daterange = $('#daterange').val();
                    if (daterange && dateIdx !== -1) {
                        var dateCol = data[dateIdx];
                        var range = daterange.split(' - ');
                        var min = moment(range[0], 'DD/MM/YYYY');
                        var max = moment(range[1], 'DD/MM/YYYY');
                        var bookingDate = moment(dateCol, 'DD MMM YYYY HH:mm');
                        if (!bookingDate.isValid() || !bookingDate.isBetween(min, max, undefined, '[]')) return false;
                    }
                    // Driver (not present, so skip or use Customer if needed)
                    // Location (From/To)
                    var location = $('#locationFilter').val().toLowerCase();
                    if (location && fromIdx !== -1 && toIdx !== -1) {
                        if (data[fromIdx].toLowerCase().indexOf(location) === -1 && data[toIdx].toLowerCase().indexOf(location) === -1) return false;
                    }
                    // Reason
                    var reason = $('#reasonFilter').val();
                    if (reason && reasonIdx !== -1) {
                        if (data[reasonIdx].toLowerCase().indexOf(reason.toLowerCase()) === -1) return false;
                    }
                    return true;
                };
                customFilter._isCustomPoolpal = true;
                $.fn.dataTable.ext.search.push(customFilter);
                $('#daterange, #driverFilter, #locationFilter, #reasonFilter').on('change keyup', function() {
                    table.draw();
                });

                loadCancellationStats();
            });

            function loadCancellationStats() {
                $.ajax({
                    url: 'get_cancellation_stats.php',
                    type: 'GET',
                    success: function(response) {
                        var stats = JSON.parse(response);
                        $('#totalCancellations').text(stats.total);
                        $('#avgRefundTime').text(stats.avgRefundTime);
                        $('#commonReason').text(stats.commonReason);
                        $('#totalRefund').text('₹' + stats.totalRefund);
                    }
                });
            }

            function viewDetails(bookingId) {
                $.ajax({
                    url: 'get_cancelled_booking_details.php',
                    type: 'POST',
                    data: { booking_id: bookingId },
                    success: function(response) {
                        $('#bookingDetailsContent').html(response);
                        $('#bookingDetailsModal').modal('show');
                    }
                });
            }

            function processRefund(bookingId) {
                if(confirm('Are you sure you want to process the refund for this booking?')) {
                    $.ajax({
                        url: 'process_refund.php',
                        type: 'POST',
                        data: { booking_id: bookingId },
                        success: function(response) {
                            alert('Refund processed successfully');
                            location.reload();
                        },
                        error: function() {
                            alert('Error processing refund');
                        }
                    });
                }
            }

            function exportToExcel() { $('.buttons-excel').click(); }
            function exportToCSV() { $('.buttons-csv').click(); }
            function exportToPDF() { $('.buttons-pdf').click(); }
        </script>
    </div>
</body>
</html> 