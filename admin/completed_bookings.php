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
                        <li class="breadcrumb-item active">Completed Trips</li>
                    </ol>

                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Completed Trips</h1>
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
                                    <label>Customer</label>
                                    <input type="text" class="form-control" id="customerFilter" placeholder="Search by customer name">
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
                                            echo "<option value='".$row['Full_Name']."'>".$row['Full_Name']."</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>From/To</label>
                                    <input type="text" class="form-control" id="locationFilter" placeholder="Search by from/to">
                                </div>
                            </div>
                        </div>
                        <div class="row">
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
                            Completed Trips
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="bookingsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Customer</th>
                                            <th>Driver</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Date & Time</th>
                                            <th>Amount</th>
                                            <th>Payment Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT b.*, 
                                                u.full_name as user_name, 
                                                d.Full_Name as driver_name, 
                                                t.departure_city, 
                                                t.destination_city, 
                                                t.departure_date, 
                                                t.departure_time 
                                                FROM bookings b 
                                                JOIN users u ON b.user_id = u.id 
                                                JOIN trips t ON b.trip_id = t.id 
                                                JOIN drivers d ON t.driver_email = d.Email 
                                                WHERE CONCAT(t.departure_date, ' ', t.departure_time) < NOW() 
                                                AND b.payment_status = 'completed'
                                                ORDER BY t.departure_date DESC, t.departure_time DESC";
                                        
                                        $result = mysqli_query($conn, $query);
                                        if (!$result) {
                                            echo "<tr><td colspan='9'>Error: ".mysqli_error($conn)."</td></tr>";
                                        } else {
                                            while($row = mysqli_fetch_assoc($result)) {
                                                $paymentStatusClass = 'badge-success';
                                                
                                                echo "<tr>";
                                                echo "<td>#".$row['id']."</td>";
                                                echo "<td>".$row['user_name']."</td>";
                                                echo "<td>".$row['driver_name']."</td>";
                                                echo "<td>".$row['departure_city']."</td>";
                                                echo "<td>".$row['destination_city']."</td>";
                                                echo "<td>".date('d M Y H:i', strtotime($row['departure_date'] . ' ' . $row['departure_time']))."</td>";
                                                echo "<td>₹".$row['total_amount']."</td>";
                                                echo "<td><span class='badge badge-success'>Completed</span></td>";
                                                echo "<td class='action-buttons'>
                                                        <button class='btn btn-info btn-sm' onclick='viewDetails(".$row['id'].")'><i class='fas fa-eye'></i></button>
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
                        <h5 class="modal-title">Trip Details</h5>
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

                $('#daterange').daterangepicker({
                    opens: 'left',
                    locale: { format: 'DD/MM/YYYY' }
                });
                var table = $('#bookingsTable').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy',
                        { extend: 'excelHtml5', title: 'Completed Trips' },
                        { extend: 'csvHtml5', title: 'Completed Trips' },
                        { extend: 'pdfHtml5', title: 'Completed Trips', orientation: 'landscape', pageSize: 'A4' },
                        'print'
                    ],
                    pageLength: 10,
                    order: [[getColIndex('Date & Time'), 'desc']]
                });
                // Custom filtering for all filters
                var customFilter = function(settings, data, dataIndex) {
                    var dateIdx = getColIndex('Date & Time');
                    var customerIdx = getColIndex('Customer');
                    var driverIdx = getColIndex('Driver');
                    var fromIdx = getColIndex('From');
                    var toIdx = getColIndex('To');
                    var paymentIdx = getColIndex('Payment Status');
                    // Date Range
                    var daterange = $('#daterange').val();
                    if (daterange && dateIdx !== -1) {
                        var dateCol = data[dateIdx];
                        var range = daterange.split(' - ');
                        var min = moment(range[0], 'DD/MM/YYYY');
                        var max = moment(range[1], 'DD/MM/YYYY');
                        var tripDate = moment(dateCol, 'DD MMM YYYY HH:mm');
                        if (!tripDate.isValid() || !tripDate.isBetween(min, max, undefined, '[]')) return false;
                    }
                    // Customer
                    var customer = $('#customerFilter').val().toLowerCase();
                    if (customer && customerIdx !== -1) {
                        if (data[customerIdx].toLowerCase().indexOf(customer) === -1) return false;
                    }
                    // Driver
                    var driver = $('#driverFilter').val();
                    if (driver && driverIdx !== -1) {
                        if (data[driverIdx].indexOf(driver) === -1) return false;
                    }
                    // From/To
                    var location = $('#locationFilter').val().toLowerCase();
                    if (location && fromIdx !== -1 && toIdx !== -1) {
                        if (data[fromIdx].toLowerCase().indexOf(location) === -1 && data[toIdx].toLowerCase().indexOf(location) === -1) return false;
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
                $('#daterange, #customerFilter, #driverFilter, #locationFilter, #paymentFilter').on('change keyup', function() {
                    table.draw();
                });
            });

            // Export functions
            function exportToExcel() { $('.buttons-excel').click(); }
            function exportToCSV() { $('.buttons-csv').click(); }
            function exportToPDF() { $('.buttons-pdf').click(); }

            function viewDetails(bookingId) {
                $.ajax({
                    url: 'get_booking_details.php',
                    type: 'POST',
                    data: { booking_id: bookingId },
                    success: function(response) {
                        $('#bookingDetailsContent').html(response);
                        $('#bookingDetailsModal').modal('show');
                    }
                });
            }
        </script>
    </div>
</body>
</html> 