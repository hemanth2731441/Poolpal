<?php
include('vendor/inc/config.php');

if(isset($_POST['booking_id'])) {
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    
    $query = "SELECT b.*, u.user_name, u.user_phone, u.user_email,
              d.driver_name, d.driver_phone, d.driver_email,
              c.cancellation_reason, c.cancelled_by, c.cancelled_date,
              c.refund_status, c.refund_amount, c.refund_date,
              c.additional_notes
              FROM bookings b 
              JOIN users u ON b.user_id = u.user_id 
              JOIN drivers d ON b.driver_id = d.driver_id 
              JOIN cancellations c ON b.booking_id = c.booking_id
              WHERE b.booking_id = '$booking_id'";
              
    $result = mysqli_query($conn, $query);
    $booking = mysqli_fetch_assoc($result);
    
    if($booking) {
        ?>
        <div class="booking-details">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Booking Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <td class="font-weight-bold">Booking ID:</td>
                            <td>#<?php echo $booking['booking_id']; ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Original Booking Date:</td>
                            <td><?php echo date('d M Y H:i', strtotime($booking['booking_date'])); ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Cancelled Date:</td>
                            <td><?php echo date('d M Y H:i', strtotime($booking['cancelled_date'])); ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Pickup Location:</td>
                            <td><?php echo $booking['pickup_location']; ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Drop Location:</td>
                            <td><?php echo $booking['drop_location']; ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Amount:</td>
                            <td>$<?php echo $booking['amount']; ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Cancellation Details</h6>
                    <table class="table table-sm">
                        <tr>
                            <td class="font-weight-bold">Cancelled By:</td>
                            <td><?php echo ucfirst($booking['cancelled_by']); ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Reason:</td>
                            <td><?php echo $booking['cancellation_reason']; ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Refund Status:</td>
                            <td>
                                <span class="status-badge <?php echo $booking['refund_status'] == 'processed' ? 'status-active' : 'bg-warning text-dark'; ?>">
                                    <?php echo ucfirst($booking['refund_status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php if($booking['refund_status'] == 'processed'): ?>
                        <tr>
                            <td class="font-weight-bold">Refund Amount:</td>
                            <td>$<?php echo $booking['refund_amount']; ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Refund Date:</td>
                            <td><?php echo date('d M Y', strtotime($booking['refund_date'])); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if($booking['additional_notes']): ?>
                        <tr>
                            <td class="font-weight-bold">Additional Notes:</td>
                            <td><?php echo $booking['additional_notes']; ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Customer Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <td class="font-weight-bold">Name:</td>
                            <td><?php echo $booking['user_name']; ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Phone:</td>
                            <td><?php echo $booking['user_phone']; ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Email:</td>
                            <td><?php echo $booking['user_email']; ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Driver Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <td class="font-weight-bold">Name:</td>
                            <td><?php echo $booking['driver_name']; ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Phone:</td>
                            <td><?php echo $booking['driver_phone']; ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Email:</td>
                            <td><?php echo $booking['driver_email']; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo "<div class='alert alert-danger'>Booking not found.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Invalid request.</div>";
}
?> 