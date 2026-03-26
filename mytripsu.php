<?php
require 'vendor/autoload.php';
include 'header.php';  // header.php already includes init.php which includes config.php and db.php

// Add PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$user_email = $_SESSION['user_email']; // assuming user is logged in

// Check if cancellation_reason column exists, if not create it
$check_column = "SHOW COLUMNS FROM cancelled_bookings LIKE 'cancellation_reason'";
$column_exists = $conn->query($check_column);
if ($column_exists->num_rows == 0) {
    $add_column = "ALTER TABLE cancelled_bookings ADD COLUMN cancellation_reason TEXT AFTER price";
    $conn->query($add_column);
}

// Create cancelled_bookings table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS cancelled_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,
    trip_id INT NOT NULL,
    seats_booked INT NOT NULL,
    departure_city VARCHAR(100) NOT NULL,
    destination_city VARCHAR(100) NOT NULL,
    departure_date DATE NOT NULL,
    departure_time TIME NOT NULL, 
    arrival_time TIME NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    cancellation_reason TEXT,
    cancelled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($create_table_sql);

// Process booking cancellation
if(isset($_POST['cancel_booking']) && isset($_POST['booking_id']) && isset($_POST['cancellation_reason'])) {
    $booking_id = $_POST['booking_id'];
    $cancellation_reason = $_POST['cancellation_reason'];
    
    // First, get the booking details to save in cancelled_bookings table
    $get_booking = "SELECT b.*, t.departure_city, t.destination_city, t.departure_date, t.departure_time, t.arrival_time, t.price
                    FROM bookings b
                    JOIN trips t ON b.trip_id = t.id
                    WHERE b.id = ? AND b.user_email = ?";
    $stmt_get = $conn->prepare($get_booking);
    $stmt_get->bind_param("is", $booking_id, $user_email);
    $stmt_get->execute();
    $booking_result = $stmt_get->get_result();
    
    if($booking_data = $booking_result->fetch_assoc()) {
        // Check if departure time is more than 24 hours away
        $departure_datetime = strtotime($booking_data['departure_date'] . ' ' . $booking_data['departure_time']);
        $current_time = time();
        $time_difference = $departure_datetime - $current_time;
        $hours_difference = $time_difference / 3600;
        
        if($hours_difference < 24) {
            // Too late to cancel
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Cannot Cancel Booking',
                    text: 'Bookings can only be cancelled at least 24 hours before departure.',
                    confirmButtonText: 'OK'
                });
            </script>";
        } else {
            // Insert into cancelled_bookings table
            $insert_cancelled = "INSERT INTO cancelled_bookings 
                          (user_email, trip_id, seats_booked, departure_city, destination_city, 
                          departure_date, departure_time, arrival_time, price, cancellation_reason) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_cancelled);
            $stmt_insert->bind_param(
                "siisssssds", 
                $user_email, 
                $booking_data['trip_id'], 
                $booking_data['seats_booked'], 
                $booking_data['departure_city'], 
                $booking_data['destination_city'], 
                $booking_data['departure_date'], 
                $booking_data['departure_time'], 
                $booking_data['arrival_time'], 
                $booking_data['price'],
                $cancellation_reason
            );
            
            if($stmt_insert->execute()) {
                // Update seats in trips table
                $update_seats = "UPDATE trips SET seats = seats + ? WHERE id = ?";
                $stmt_update_seats = $conn->prepare($update_seats);
                $stmt_update_seats->bind_param("ii", $booking_data['seats_booked'], $booking_data['trip_id']);
                $stmt_update_seats->execute();

                // Delete the booking
                $delete_booking = "DELETE FROM bookings WHERE id = ? AND user_email = ?";
                $stmt_delete = $conn->prepare($delete_booking);
                $stmt_delete->bind_param("is", $booking_id, $user_email);
                
                if($stmt_delete->execute()) {
                    try {
                        $mail = new PHPMailer(true);
                        
                        // Server settings
                        $mail->SMTPDebug = SMTP_DEBUG;
                        $mail->isSMTP();
                        $mail->Host = SMTP_HOST;
                        $mail->SMTPAuth = SMTP_AUTH;
                        $mail->Username = SMTP_USERNAME;
                        $mail->Password = SMTP_PASSWORD;
                        $mail->SMTPSecure = SMTP_SECURE;
                        $mail->Port = SMTP_PORT;
                        
                        // Additional SMTP options for better reliability
                        $mail->SMTPOptions = array(
                            'ssl' => array(
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true
                            )
                        );
                        
                        // Set timeout and keep-alive
                        $mail->Timeout = 60;
                        $mail->SMTPKeepAlive = true;
                        
                        // Set UTF-8 encoding
                        $mail->CharSet = 'UTF-8';
                        $mail->Encoding = 'base64';
                        
                        // Recipients
                        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
                        $mail->addAddress($user_email);
                        
                        $mail->isHTML(true);
                        $mail->Subject = 'Booking Cancellation Confirmation';
                        $mail->Body = "
                        <html>
                        <head>
                            <style>
                                .container {
                                    max-width: 600px;
                                    margin: 0 auto;
                                    padding: 20px;
                                    font-family: Arial, sans-serif;
                                }
                                .header {
                                    background: linear-gradient(45deg, #ffbf00, rgba(153, 122, 28, 0.85));
                                    padding: 20px;
                                    border-radius: 10px 10px 0 0;
                                    text-align: center;
                                }
                                .header h2 {
                                    color: white;
                                    margin: 0;
                                }
                                .content {
                                    padding: 20px;
                                    border: 1px solid #ddd;
                                    border-radius: 0 0 10px 10px;
                                }
                                .footer {
                                    text-align: center;
                                    margin-top: 20px;
                                    color: #666;
                                    font-size: 12px;
                                }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'><h2>Booking Cancellation Confirmation</h2></div>
                                <div class='content'>
                                    <p>Hello,</p>
                                    <p>Your booking has been successfully cancelled:</p>
                                    <p><strong>Trip Details:</strong> " . $booking_data['departure_city'] . " to " . $booking_data['destination_city'] . " on " . date("F j, Y", strtotime($booking_data['departure_date'])) . " at " . date("g:i A", strtotime($booking_data['departure_time'])) . "</p>
                                    <p><strong>Seats:</strong> " . $booking_data['seats_booked'] . "</p>
                                    <p><strong>Refund Amount:</strong> ₹" . number_format($booking_data['seats_booked'] * $booking_data['price'], 2) . "</p>
                                    <p><strong>Cancellation Reason:</strong> " . htmlspecialchars($cancellation_reason) . "</p>
                                    <p>Thank you for using PoolPal. We hope to see you again soon!</p>
                                </div>
                                <div class='footer'>
                                    This is an automated message. Please do not reply to this email.
                                </div>
                            </div>
                        </body>
                        </html>
                        ";
                        
                        // Plain text version
                        $mail->AltBody = "Booking Cancellation Confirmation\n\n" .
                                       "Your booking has been successfully cancelled:\n\n" .
                                       "Trip Details: " . $booking_data['departure_city'] . " to " . $booking_data['destination_city'] . "\n" .
                                       "Date: " . date("F j, Y", strtotime($booking_data['departure_date'])) . "\n" .
                                       "Time: " . date("g:i A", strtotime($booking_data['departure_time'])) . "\n" .
                                       "Seats: " . $booking_data['seats_booked'] . "\n" .
                                       "Refund Amount: ₹" . number_format($booking_data['seats_booked'] * $booking_data['price'], 2) . "\n" .
                                       "Cancellation Reason: " . $cancellation_reason . "\n\n" .
                                       "Thank you for using PoolPal. We hope to see you again soon!";
                        
                        // Send email with error logging
                        if (!$mail->send()) {
                            error_log("Failed to send email to {$user_email}. Error: " . $mail->ErrorInfo);
                            throw new Exception($mail->ErrorInfo);
                        }
                        
                        // Calculate refund amount
                        $refund_amount = number_format($booking_data['seats_booked'] * $booking_data['price'], 2);
                        
                        // Show success message with unique identifier
                        echo "
                        <div id='poolpal_cancel_alert'></div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const cancelAlert = Swal.mixin({
                                    customClass: {
                                        container: 'poolpal_alert_container',
                                        popup: 'poolpal_alert_popup',
                                        title: 'poolpal_alert_title',
                                        htmlContainer: 'poolpal_alert_html',
                                        confirmButton: 'poolpal_alert_confirm'
                                    }
                                });

                                cancelAlert.fire({
                                    icon: 'success',
                                    title: 'Booking Cancelled!',
                                    html: `
                                        <div class='poolpal_details_container'>
                                            <p class='poolpal_message'>Your booking has been cancelled successfully and a confirmation email has been sent.</p>
                                            <div class='poolpal_info_box'>
                                                <p><i class='fas fa-map-marker-alt'></i> From: {$booking_data['departure_city']}</p>
                                                <p><i class='fas fa-map-marker-alt'></i> To: {$booking_data['destination_city']}</p>
                                                <p><i class='fas fa-calendar-alt'></i> Date: {$booking_data['departure_date']}</p>
                                                <p><i class='fas fa-clock'></i> Time: {$booking_data['departure_time']}</p>
                                                <p><i class='fas fa-rupee-sign'></i> Refund Amount: ₹{$refund_amount}</p>
                                            </div>
                                        </div>
                                    `,
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = 'mytripsu.php';
                                    }
                                });
                            });
                        </script>
                        <style>
                            .poolpal_alert_container {
                                z-index: 9999;
                            }
                            .poolpal_alert_popup {
                                padding: 20px;
                                border-radius: 15px;
                                background: #fff;
                                box-shadow: 0 0 20px rgba(0,0,0,0.1);
                                border: none;
                            }
                            .poolpal_alert_title {
                                color: #2e7d32;
                                font-size: 24px;
                                margin-bottom: 15px;
                            }
                            .poolpal_details_container {
                                text-align: left;
                                padding: 10px;
                            }
                            .poolpal_message {
                                margin-bottom: 20px;
                                color: #333;
                                font-size: 16px;
                            }
                            .poolpal_info_box {
                                background: #f8f9fa;
                                padding: 15px;
                                border-radius: 8px;
                                border: 1px solid #eee;
                            }
                            .poolpal_info_box p {
                                margin: 10px 0;
                                color: #555;
                                font-size: 14px;
                            }
                            .poolpal_info_box i {
                                color: #ffbf00;
                                width: 25px;
                                margin-right: 10px;
                            }
                            .poolpal_alert_confirm {
                                background: #ffbf00 !important;
                                color: white !important;
                                padding: 12px 30px !important;
                                font-size: 16px !important;
                                border-radius: 8px !important;
                                margin-top: 20px !important;
                            }
                            .poolpal_alert_confirm:hover {
                                background: #e6ac00 !important;
                            }
                        </style>";
                        
                    } catch (Exception $e) {
                        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                        
                        // Show error message for email failure with retry option
                        echo "
                        <div id='poolpal_error_alert'></div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Booking Cancelled',
                                    html: `
                                        <div style='text-align: left;'>
                                            <p>Your booking has been cancelled successfully.</p>
                                            <p>However, there was a problem sending the confirmation email. This could be due to:</p>
                                            <ul style='list-style-type: disc; margin-left: 20px;'>
                                                <li>Temporary email server issues</li>
                                                <li>Network connectivity problems</li>
                                                <li>Invalid email address</li>
                                            </ul>
                                            <p>Don't worry! Your cancellation has been recorded in our system.</p>
                                            <p>You can try the following:</p>
                                            <ul style='list-style-type: disc; margin-left: 20px;'>
                                                <li>Check your email address in your profile</li>
                                                <li>Contact our support if you need the confirmation email</li>
                                            </ul>
                                        </div>
                                    `,
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        confirmButton: 'poolpal_alert_confirm'
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = 'mytripsu.php';
                                    }
                                });
                            });
                        </script>";
                    }
                } else {
                    // Show error message
                    echo "
                    <div id='poolpal_error_alert'></div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to cancel booking. Please try again.',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'poolpal_alert_confirm'
                                }
                            });
                        });
                    </script>";
                }
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to record cancellation. Please try again.',
                        confirmButtonText: 'OK'
                    });
                </script>";
            }
        }
    }
}

// Add SweetAlert library
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
?>
<script>
function showCancellationDialog(bookingId) {
    Swal.fire({
        title: 'Cancel Booking',
        html: `
            <div class="cancellation-modal">
                <p style="color: #666; margin-bottom: 1.5rem;">Please select a reason for cancelling this booking:</p>
                
                <div class="reason-category">
                    <div class="reason-category-title">
                        <i class="fas fa-exclamation-circle"></i>
                        Common Reasons
                    </div>
                    <div class="cancellation-reasons">
                        <div class="reason-option">
                            <input type="radio" id="reason1" name="cancellation_reason" value="Change in travel plans">
                            <label for="reason1">
                                <i class="fas fa-calendar-alt"></i>
                                Change in travel plans
                            </label>
                        </div>
                        <div class="reason-option">
                            <input type="radio" id="reason2" name="cancellation_reason" value="Found alternative transport">
                            <label for="reason2">
                                <i class="fas fa-exchange-alt"></i>
                                Found alternative transport
                            </label>
                        </div>
                        <div class="reason-option">
                            <input type="radio" id="reason3" name="cancellation_reason" value="Emergency situation">
                            <label for="reason3">
                                <i class="fas fa-exclamation-triangle"></i>
                                Emergency situation
                            </label>
                        </div>
                        <div class="reason-option">
                            <input type="radio" id="reason4" name="cancellation_reason" value="Schedule conflict">
                            <label for="reason4">
                                <i class="fas fa-clock"></i>
                                Schedule conflict
                            </label>
                        </div>
                        <div class="reason-option">
                            <input type="radio" id="reason5" name="cancellation_reason" value="custom">
                            <label for="reason5">
                                <i class="fas fa-pen"></i>
                                Other reason
                            </label>
                        </div>
                    </div>
                </div>
                
                <div id="customReasonContainer">
                    <textarea id="customReason" 
                              placeholder="Please provide details about your cancellation reason (minimum 10 characters)"
                              maxlength="200"></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Proceed with Cancellation',
        cancelButtonText: 'Keep Booking',
        reverseButtons: true,
        customClass: {
            container: 'cancellation-modal-container',
            popup: 'cancellation-modal-popup',
            title: 'cancellation-modal-title',
            htmlContainer: 'cancellation-modal-content',
            confirmButton: 'cancellation-modal-confirm',
            cancelButton: 'cancellation-modal-cancel'
        },
        didOpen: () => {
            // Add click handlers for reason options
            document.querySelectorAll('.reason-option').forEach(option => {
                const radio = option.querySelector('input[type="radio"]');
                option.addEventListener('click', () => {
                    document.querySelectorAll('.reason-option').forEach(opt => 
                        opt.classList.remove('selected'));
                    option.classList.add('selected');
                    radio.checked = true;
                    
                    const customContainer = document.getElementById('customReasonContainer');
                    if (radio.value === 'custom') {
                        customContainer.classList.add('show');
                        document.getElementById('customReason').focus();
                    } else {
                        customContainer.classList.remove('show');
                    }
                });
            });
        },
        preConfirm: () => {
            const selectedReason = document.querySelector('input[name="cancellation_reason"]:checked');
            if (!selectedReason) {
                Swal.showValidationMessage('Please select a reason for cancellation');
                return false;
            }
            
            if (selectedReason.value === 'custom') {
                const customReason = document.getElementById('customReason').value.trim();
                if (customReason.length < 10) {
                    Swal.showValidationMessage('Please provide a detailed reason (minimum 10 characters)');
                    return false;
                }
                return customReason;
            }
            
            return selectedReason.value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show confirmation dialog with countdown
            let timeLeft = 5;
            Swal.fire({
                title: 'Confirm Cancellation',
                html: `
                    <div class="confirmation-dialog">
                        <p>Are you absolutely sure you want to cancel this booking?</p>
                        <div class="countdown">Proceeding in <strong>${timeLeft}</strong> seconds...</div>
                        <div class="email-preview">
                            <div class="email-header">
                                <i class="fas fa-envelope"></i>
                                Email Notification Preview
                            </div>
                            <div class="email-content">
                                A cancellation confirmation email will be sent to your registered email address.
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Yes, Cancel Now',
                cancelButtonText: 'No, Keep Booking',
                reverseButtons: true,
                allowOutsideClick: false,
                didOpen: () => {
                    const countdownInterval = setInterval(() => {
                        timeLeft--;
                        const countdownEl = document.querySelector('.countdown strong');
                        if (countdownEl) {
                            countdownEl.textContent = timeLeft;
                        }
                        if (timeLeft <= 0) {
                            clearInterval(countdownInterval);
                            Swal.getConfirmButton().removeAttribute('disabled');
                            document.querySelector('.countdown').innerHTML = 
                                '<span style="color: #e53935;">Time\'s up! Please confirm your decision.</span>';
                        }
                    }, 1000);
                    
                    // Disable confirm button during countdown
                    Swal.getConfirmButton().setAttribute('disabled', '');
                }
            }).then((finalConfirm) => {
                if (finalConfirm.isConfirmed) {
                    // Show processing state
                    Swal.fire({
                        title: 'Processing Cancellation',
                        html: `
                            <div class="processing-animation">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Please wait while we process your cancellation...</p>
                            </div>
                        `,
                        allowOutsideClick: false,
                        showConfirmButton: false
                    });
                    
                    // Create and submit form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="cancel_booking" value="1">
                        <input type="hidden" name="booking_id" value="${bookingId}">
                        <input type="hidden" name="cancellation_reason" value="${result.value}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    });
    return false;
}

// Add this function to share trip details
function shareTrip(tripDetails) {
    if (navigator.share) {
        navigator.share({
            title: 'My PoolPal Trip',
            text: `Check out my trip from ${tripDetails.from} to ${tripDetails.to} on ${tripDetails.date}!`,
            url: window.location.href
        })
        .then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Shared Successfully!',
                text: 'Trip details have been shared.',
                timer: 2000,
                showConfirmButton: false
            });
        })
        .catch((error) => {
            console.log('Error sharing:', error);
        });
    } else {
        // Fallback for browsers that don't support Web Share API
        const shareUrl = window.location.href;
        const tempInput = document.createElement('input');
        document.body.appendChild(tempInput);
        tempInput.value = shareUrl;
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        
        Swal.fire({
            icon: 'success',
            title: 'Link Copied!',
            text: 'Trip link has been copied to clipboard.',
            timer: 2000,
            showConfirmButton: false
        });
    }
}

// Add this to your existing styles
const newStyles = `
    .confirmation-dialog {
        text-align: center;
        padding: 1rem;
    }

    .countdown {
        margin: 1rem 0;
        padding: 0.5rem;
        background: rgba(255, 191, 0, 0.1);
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .countdown strong {
        color: #e53935;
        font-size: 1.2rem;
    }

    .email-preview {
        margin-top: 1rem;
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
    }

    .email-header {
        background: #f5f5f5;
        padding: 0.5rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .email-content {
        padding: 1rem;
        font-size: 0.9rem;
        color: #666;
    }

    .processing-animation {
        text-align: center;
        padding: 2rem;
    }

    .processing-animation i {
        font-size: 3rem;
        color: #ffbf00;
        margin-bottom: 1rem;
    }

    .share-trip-button {
        background: linear-gradient(45deg, #ffbf00, rgba(153, 122, 28, 0.85));
        color: white;
        border: none;
        border-radius: 6px;
        padding: 8px 14px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .share-trip-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(255, 191, 0, 0.3);
    }
`;

// Add the new styles to the document
const styleSheet = document.createElement('style');
styleSheet.textContent = newStyles;
document.head.appendChild(styleSheet);

function checkCancellationTime(bookingId, departureDate, departureTime) {
    const departureDatetime = new Date(departureDate + "T" + departureTime);
    const currentTime = new Date();
    const timeDifferenceHours = (departureDatetime - currentTime) / (1000 * 60 * 60);
    
    if (isNaN(departureDatetime.getTime())) {
        Swal.fire({
            title: "Error",
            text: "Invalid date or time format. Please try again.",
            icon: "error",
            confirmButtonColor: "#ffbf00"
        });
        return false;
    }
    
    if (timeDifferenceHours < 24) {
        Swal.fire({
            title: "Cannot Cancel",
            html: `
                <p>Bookings can only be cancelled at least 24 hours before departure.</p>
                <p>Your trip departs in: <strong>${Math.floor(timeDifferenceHours)} hours and ${Math.floor((timeDifferenceHours % 1) * 60)} minutes</strong></p>
            `,
            icon: "error",
            confirmButtonColor: "#ffbf00"
        });
        return false;
    }
    
    // Show cancellation dialog with reasons
    showCancellationDialog(bookingId);
    return false;
}
</script>
<?php
// Get completed trips booked by the user (removing LIMIT)
$sql = "SELECT 
            b.id AS booking_id,
            t.id AS trip_id,
            t.departure_city,
            t.destination_city,
            t.departure_date,
            t.departure_time,
            t.arrival_time,
            t.price,
            b.seats_booked,
            b.booking_time
        FROM bookings b
        JOIN trips t ON b.trip_id = t.id
        WHERE 
            b.user_email = ? AND
            CONCAT(t.departure_date, ' ', t.arrival_time) <= NOW()
        ORDER BY b.booking_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

// Get upcoming trips booked by the user
$sql_upcoming = "SELECT 
                    b.id AS booking_id,
                    t.id AS trip_id,
                    t.departure_city,
                    t.destination_city,
                    t.departure_date,
                    t.departure_time,
                    t.arrival_time,
                    t.price,
                    b.seats_booked,
                    b.booking_time,
                    d.Full_Name AS driver_name
                FROM bookings b
                JOIN trips t ON b.trip_id = t.id
                LEFT JOIN drivers d ON t.driver_email = d.Email
                WHERE 
                    b.user_email = ? AND
                    CONCAT(t.departure_date, ' ', t.departure_time) > NOW()
                ORDER BY t.departure_date ASC, t.departure_time ASC";

$stmt_upcoming = $conn->prepare($sql_upcoming);
$stmt_upcoming->bind_param("s", $user_email);
$stmt_upcoming->execute();
$result_upcoming = $stmt_upcoming->get_result();

// Get cancelled trips by the user
$sql_cancelled = "SELECT 
                    id,
                    user_email,
                    trip_id,
                    seats_booked,
                    departure_city,
                    destination_city,
                    departure_date,
                    departure_time,
                    arrival_time,
                    price,
                    COALESCE(cancellation_reason, 'No reason provided') as cancellation_reason,
                    cancelled_at
                FROM cancelled_bookings 
                WHERE 
                    user_email = ?
                ORDER BY cancelled_at DESC";

$stmt_cancelled = $conn->prepare($sql_cancelled);
$stmt_cancelled->bind_param("s", $user_email);
$stmt_cancelled->execute();
$result_cancelled = $stmt_cancelled->get_result();

// Total amount spent by the user (only completed trips)
$sql_spent = "SELECT COALESCE(SUM(b.seats_booked * t.price), 0) AS total_spent
              FROM bookings b 
              JOIN trips t ON b.trip_id = t.id 
              WHERE b.user_email = ? AND CONCAT(t.departure_date, ' ', t.arrival_time) <= NOW()";

$stmt_spent = $conn->prepare($sql_spent);
$stmt_spent->bind_param("s", $user_email);
$stmt_spent->execute();
$result_spent = $stmt_spent->get_result();
$row_spent = $result_spent->fetch_assoc();
$total_spent = $row_spent['total_spent'] ?? 0;

// Total completed and cancelled trips
$sql_completed = "SELECT 
                    (SELECT COUNT(*) FROM bookings b
                     JOIN trips t ON b.trip_id = t.id
                     WHERE b.user_email = ? AND CONCAT(t.departure_date, ' ', t.arrival_time) <= NOW())
                    +
                    (SELECT COUNT(*) FROM cancelled_bookings WHERE user_email = ?)
                  AS completed_trips";

$stmt_completed = $conn->prepare($sql_completed);
$stmt_completed->bind_param("ss", $user_email, $user_email);
$stmt_completed->execute();
$result_completed = $stmt_completed->get_result();
$row_completed = $result_completed->fetch_assoc();
$completed_trips = $row_completed['completed_trips'] ?? 0;

// Get additional stats
$sql_upcoming_count = "SELECT COUNT(*) as upcoming_count 
                      FROM bookings b
                      JOIN trips t ON b.trip_id = t.id
                      WHERE 
                          b.user_email = ? AND
                          CONCAT(t.departure_date, ' ', t.departure_time) > NOW()";

$stmt_upcoming_count = $conn->prepare($sql_upcoming_count);
$stmt_upcoming_count->bind_param("s", $user_email);
$stmt_upcoming_count->execute();
$result_upcoming_count = $stmt_upcoming_count->get_result();
$row_upcoming_count = $result_upcoming_count->fetch_assoc();
$upcoming_count = $row_upcoming_count['upcoming_count'] ?? 0;

$sql_cancelled_count = "SELECT COUNT(*) as cancelled_count 
                        FROM cancelled_bookings
                        WHERE user_email = ?";

$stmt_cancelled_count = $conn->prepare($sql_cancelled_count);
$stmt_cancelled_count->bind_param("s", $user_email);
$stmt_cancelled_count->execute();
$result_cancelled_count = $stmt_cancelled_count->get_result();
$row_cancelled_count = $result_cancelled_count->fetch_assoc();
$cancelled_count = $row_cancelled_count['cancelled_count'] ?? 0;

// SweetAlert is now shown directly after cancellation

// Add script to update countdown for upcoming trips
echo '
<script>
function updateCountdowns() {
    document.querySelectorAll(".trip-countdown").forEach(function(element) {
        const departureTime = new Date(element.dataset.departure).getTime();
        const now = new Date().getTime();
        const timeLeft = departureTime - now;
        
        if (timeLeft <= 0) {
            element.innerHTML = "Departing now!";
            element.classList.add("departing-now");
        } else {
            const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
            const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            
            let countdownText = "";
            if (days > 0) {
                countdownText += days + "d ";
            }
            countdownText += hours + "h " + minutes + "m";
            
            if (timeLeft < 24 * 60 * 60 * 1000) {
                element.classList.add("leaving-soon");
            }
            
            element.innerHTML = countdownText;
        }
    });
}

// Update countdowns every minute
setInterval(updateCountdowns, 60000);
document.addEventListener("DOMContentLoaded", updateCountdowns);
</script>
';

// Add style for countdown
echo '
<style>
.trip-countdown {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    background-color: #f5f5f5;
    transition: all 0.3s ease;
}

.leaving-soon {
    color: white;
    background-color: #ff9800;
}

.departing-now {
    color: white;
    background-color: #f44336;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% { opacity: 0.7; }
    50% { opacity: 1; }
    100% { opacity: 0.7; }
}

.animate-entrance {
    animation: fadeSlideIn 0.5s ease-out forwards;
    opacity: 0;
    transform: translateY(20px);
}

@keyframes fadeSlideIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Overview</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <style>
    .mini {
      font-family: 'Inter', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #fff;
      color: #000;
    }

    .trip-overview-container {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .trip-overview-header {
      font-weight: 600;
      font-size: 20px;
      margin-bottom: 20px;
    }

    .trip-overview-stats {
      display: flex;
      gap: 20px;
      margin-bottom: 40px;
      flex-wrap: wrap;
    }

    .trip-overview-card {
      background-color: rgb(255, 255, 255);
      border: 4px solid rgb(243, 240, 240);
      border-radius: 14px;
      flex: 1;
      min-width: 260px;
      padding: 20px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .trip-overview-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .trip-image {
      margin-bottom: 12px;
      width: 95px; 
      height: 75px;
      object-fit: contain;
      animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }

    .trip-overview-card-title {
      font-size: 14px;
      margin-top: 10px;
      color: #777;
    }

    .trip-overview-card-value {
      font-size: 20px;
      font-weight: 700;
      margin-top: 4px;
      background: linear-gradient(45deg, #ffbf00, rgba(153, 122, 28, 0.85));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    .trip-overview-card-sub {
      font-size: 12px;
      color: #999;
    }

    .trip-overview-recent {
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 14px;
      position: relative;
      display: inline-block;
      padding-bottom: 5px;
    }
    
    .trip-overview-recent:after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, #ffbf00, rgba(153, 122, 28, 0.69));
      bottom: 0;
      left: 0;
      transition: width 0.3s ease;
    }
    
    .trip-overview-recent:hover:after {
      width: 100%;
    }

    .trip-overview-table {
      width: 100%;
      display: flex;
      flex-direction: column;
      overflow-x: auto;
      margin-bottom: 30px;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      background: white;
    }

    .trip-overview-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px;
      border-bottom: 1px solid #f0f0f0;
      min-width: 600px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .trip-overview-row::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      height: 100%;
      width: 0;
      background: linear-gradient(90deg, rgba(255, 191, 0, 0.1), transparent);
      transition: width 0.4s ease;
    }
    
    .trip-overview-row:hover {
      background-color: #f9f9f9;
      cursor: pointer;
      transform: translateX(5px);
    }
    
    .trip-overview-row:hover::before {
      width: 100%;
    }
    
    .trip-overview-row:first-child {
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
    }
    
    .trip-overview-row:last-child {
      border-bottom-left-radius: 12px;
      border-bottom-right-radius: 12px;
      border-bottom: none;
    }

    .trip-overview-col-date,
    .trip-overview-col-time,
    .trip-overview-col-route,
    .trip-overview-col-price,
    .trip-overview-col-status,
    .trip-overview-col-driver,
    .trip-overview-col-seats,
    .trip-overview-col-price-total,
    .trip-overview-col-arrow {
      font-size: 14px;
      color: #333;
      flex-basis: 11.11%;
      display: flex;
      align-items: center;
      transition: all 0.3s ease;
    }
    
    .trip-overview-col-route {
      font-weight: 500;
      color: #ffbf00;
    }
    
    .trip-overview-col-price small,
    .trip-overview-col-seats small,
    .trip-overview-col-price-total small {
      opacity: 0.7;
      margin-left: 2px;
      font-size: 11px;
    }

    .trip-overview-col-date i {
      margin-right: 8px;
      font-size: 14px;
      color: #ffbf00;
      transition: transform 0.3s ease;
    }
    
    .trip-overview-row:hover .trip-overview-col-date i {
      transform: scale(1.2);
    }

    .trip-overview-col-arrow {
      justify-content: flex-end;
      color: #999;
      font-size: 18px;
      transition: transform 0.3s ease;
    }
    
    .trip-overview-row:hover .trip-overview-col-arrow {
      transform: translateX(5px);
      color: #ffbf00;
    }

    .trip-overview-col-status {
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .trip-overview-col-status.completed {
      color: #2e7d32;
      font-weight: 500;
    }
    
    .trip-overview-col-status.cancelled {
      color: #e53935;
    }
    
    .button-cancel {
      background: linear-gradient(45deg, #ff3a3a, #ff7676);
      color: white;
      border: none;
      border-radius: 6px;
      padding: 8px 14px;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 10px rgba(255, 58, 58, 0.2);
    }
    
    .button-cancel:hover {
      background: linear-gradient(45deg, #e60000, #ff5252);
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(255, 58, 58, 0.3);
    }
    
    .trip-tab-container {
      display: flex;
      margin-bottom: 25px;
      border-bottom: 1px solid #eaeaea;
      overflow-x: auto;
      white-space: nowrap;
      gap: 8px;
    }
    
    .trip-tab {
      padding: 12px 20px;
      cursor: pointer;
      font-weight: 600;
      position: relative;
      transition: all 0.3s ease;
      border-radius: 6px 6px 0 0;
    }
    
    .trip-tab.active {
      color: #ffbf00;
    }
    
    .trip-tab.active:after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, #ffbf00, rgba(153, 122, 28, 0.69));
      animation: slideIn 0.3s ease-in;
    }
    
    .trip-tab::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 0;
      background: linear-gradient(to bottom, rgba(255, 191, 0, 0.1), transparent);
      transition: height 0.3s ease;
    }
    
    .trip-tab:hover::before {
      height: 100%;
    }
    
    .trip-tab.active::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, #ffbf00, rgba(153, 122, 28, 0.69));
      animation: tabSlideIn 0.4s ease-out;
    }
    
    @keyframes tabSlideIn {
      0% {
        width: 0;
        left: 50%;
        transform: translateX(-50%);
      }
      100% {
        width: 100%;
        left: 0;
        transform: translateX(0);
      }
    }
    
    .trip-content {
      display: none;
    }
    
    .trip-content.active {
      display: block;
      animation: fadeIn 0.5s ease-in;
    }
    
    .fade-in {
      animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .no-trips {
      text-align: center;
      padding: 40px 20px;
      color: #666;
      font-style: italic;
      background-color: rgba(0,0,0,0.02);
      border-radius: 12px;
      border: 1px dashed #ddd;
      animation: pulseEmptyState 2s infinite;
    }
    
    @keyframes pulseEmptyState {
      0% {
        box-shadow: 0 0 0 0 rgba(255, 191, 0, 0.1);
      }
      70% {
        box-shadow: 0 0 0 10px rgba(255, 191, 0, 0);
      }
      100% {
        box-shadow: 0 0 0 0 rgba(255, 191, 0, 0);
      }
    }

    /* Responsive */
    @media (max-width: 992px) {
      .trip-overview-row {
        padding: 14px 12px;
      }
      
      .trip-overview-col-date,
      .trip-overview-col-time,
      .trip-overview-col-route,
      .trip-overview-col-price,
      .trip-overview-col-status,
      .trip-overview-col-driver,
      .trip-overview-col-seats,
      .trip-overview-col-price-total,
      .trip-overview-col-arrow {
        font-size: 13px;
      }
    }
    
    @media (max-width: 768px) {
      .trip-overview-stats {
        flex-direction: column;
        gap: 16px;
      }

      .trip-overview-card {
        width: 100%;
      }

      .trip-overview-header {
        font-size: 18px;
      }

      .trip-overview-recent {
        font-size: 15px;
      }

      .trip-overview-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 16px 12px;
        min-width: unset;
        width: 100%;
      }
      
      .trip-overview-col-date,
      .trip-overview-col-time,
      .trip-overview-col-route,
      .trip-overview-col-price,
      .trip-overview-col-status,
      .trip-overview-col-driver,
      .trip-overview-col-seats,
      .trip-overview-col-price-total,
      .trip-overview-col-arrow {
        width: 100%;
        flex-basis: 100%;
        margin-bottom: 8px;
        justify-content: flex-start;
      }
      
      .trip-overview-col-route {
        grid-column: 1 / span 2;
        font-weight: 600;
        font-size: 15px;
        order: -1;
        margin-bottom: 10px;
      }
      
      .trip-overview-col-price-total {
        font-weight: 500;
        color: #ffbf00;
      }
      
      .trip-overview-col-status {
        grid-column: 1 / span 2;
        margin-top: 10px;
      }
      
      .trip-overview-col-arrow {
        display: none;
      }
      
      .trip-overview-table {
        overflow-x: hidden;
      }
      
      .trip-tab {
        padding: 10px 15px;
        font-size: 14px;
      }
      
      .button-cancel {
        padding: 6px 10px;
        font-size: 12px;
      }
    }

    @media (max-width: 576px) {
      .trip-overview-row {
        grid-template-columns: 1fr;
        padding: 16px;
      }
      
      .trip-overview-col-route,
      .trip-overview-col-status {
        grid-column: 1;
      }
      
      .button-cancel {
        width: 100%;
        padding: 10px;
      }
      
      .trip-overview-table {
        border-radius: 8px;
      }
      
      .trip-tab {
        padding: 8px 15px;
        font-size: 14px;
      }
      
      .trip-overview-card-value {
        font-size: 18px;
      }

      .trip-overview-card-sub {
        font-size: 11px;
      }
      
      .trip-overview-container {
        padding: 0 10px;
        margin: 20px auto;
      }
    }

    .tab-count {
      font-size: 12px;
      color: #666;
      font-weight: 400;
      margin-left: 5px;
    }
    
    .trip-tab.active .tab-count {
      color: #ffbf00;
    }

    /* Trip Modal Styles */
    .trip-modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.5);
      opacity: 0;
      transition: opacity 0.3s ease;
      backdrop-filter: blur(5px);
      -webkit-backdrop-filter: blur(5px);
    }
    
    .trip-modal.show {
      display: block;
      opacity: 1;
    }
    
    .trip-modal-content {
      background-color: #fff;
      margin: 10% auto;
      width: 80%;
      max-width: 500px;
      border-radius: 12px;
      box-shadow: 0 5px 30px rgba(0,0,0,0.15);
      position: relative;
      transform: translateY(-50px);
      opacity: 0;
      transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
      overflow: hidden;
      border-top: 5px solid #ffbf00;
    }
    
    .trip-modal.show .trip-modal-content {
      transform: translateY(0);
      opacity: 1;
    }
    
    .trip-modal-header {
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #eee;
    }
    
    .trip-modal-header h2 {
      margin: 0;
      color: #333;
      font-size: 20px;
      font-weight: 600;
      background: linear-gradient(45deg, #ffbf00, rgba(153, 122, 28, 0.85));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }
    
    .trip-modal-close {
      font-size: 28px;
      font-weight: bold;
      color: #aaa;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 30px;
      height: 30px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 50%;
    }
    
    .trip-modal-close:hover {
      color: #ffbf00;
      background-color: rgba(255, 191, 0, 0.1);
      transform: rotate(90deg);
    }
    
    .trip-modal-body {
      padding: 20px;
    }
    
    .trip-detail-row {
      display: flex;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #f5f5f5;
    }
    
    .trip-detail-label {
      font-weight: 600;
      width: 35%;
      color: #666;
      font-size: 14px;
    }
    
    .trip-detail-value {
      width: 65%;
      font-size: 14px;
    }
    
    .trip-detail-actions {
      margin-top: 20px;
      display: flex;
      justify-content: flex-end;
    }
    
    .modal-cancel-btn {
      padding: 10px 16px;
      font-size: 14px;
    }
    
    @media (max-width: 576px) {
      .trip-modal-content {
        width: 90%;
        margin: 15% auto;
      }
      
      .trip-detail-row {
        flex-direction: column;
      }
      
      .trip-detail-label,
      .trip-detail-value {
        width: 100%;
      }
      
      .trip-detail-label {
        margin-bottom: 5px;
      }
    }

    /* Add these styles after your existing CSS */
    .cancellation-modal {
        padding: 1rem;
    }

    .reason-category {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .reason-category-title {
        background: linear-gradient(45deg, #ffbf00, rgba(153, 122, 28, 0.85));
        color: white;
        padding: 1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .reason-category-title i {
        font-size: 1.1rem;
    }

    .cancellation-reasons {
        padding: 1rem;
    }

    .reason-option {
        position: relative;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .reason-option:hover {
        background: rgba(255, 191, 0, 0.05);
    }

    .reason-option.selected {
        border-color: #ffbf00;
        background: rgba(255, 191, 0, 0.1);
    }

    .reason-option input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .reason-option label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        color: #333;
        font-weight: 500;
    }

    .reason-option label i {
        color: #ffbf00;
        width: 20px;
        text-align: center;
    }

    #customReasonContainer {
        display: none;
        margin-top: 1rem;
    }

    #customReasonContainer.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    #customReason {
        width: 100%;
        padding: 1rem;
        border: 2px solid #eee;
        border-radius: 8px;
        resize: vertical;
        min-height: 100px;
        font-family: inherit;
        transition: all 0.3s ease;
    }

    #customReason:focus {
        border-color: #ffbf00;
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 191, 0, 0.1);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* SweetAlert2 Custom Styles */
    .cancellation-modal-popup {
        border-radius: 16px !important;
        padding: 0 !important;
    }

    .cancellation-modal-title {
        background: linear-gradient(45deg, #ffbf00, rgba(153, 122, 28, 0.85));
        color: white !important;
        padding: 1.5rem !important;
        font-size: 1.5rem !important;
    }

    .cancellation-modal-content {
        padding: 0 !important;
    }

    .cancellation-modal-confirm {
        background: linear-gradient(45deg, #ffbf00, rgba(153, 122, 28, 0.85)) !important;
        border: none !important;
        padding: 12px 30px !important;
        font-weight: 600 !important;
    }

    .cancellation-modal-cancel {
        border: 2px solid #eee !important;
        color: #666 !important;
        padding: 12px 30px !important;
        font-weight: 600 !important;
    }

    .cancellation-modal-cancel:hover {
        background: #f5f5f5 !important;
    }

    /* Enhanced Route Display */
    .route-with-icon {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        padding: 8px 12px;
        background: rgba(255, 191, 0, 0.05);
        border-radius: 8px;
        border: 1px solid rgba(255, 191, 0, 0.1);
        transition: all 0.3s ease;
    }

    .route-arrow {
        color: #ffbf00;
        margin: 0 12px;
        display: flex;
        align-items: center;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: translateX(0); }
        50% { transform: translateX(5px); }
        100% { transform: translateX(0); }
    }

    .source-icon {
        color: #4CAF50;
        font-size: 16px;
    }

    .destination-icon {
        color: #f44336;
        font-size: 16px;
    }

    /* Trip Status Badges */
    .trip-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 8px;
    }

    .status-completed {
        background-color: rgba(46, 125, 50, 0.1);
        color: #2e7d32;
    }

    .status-cancelled {
        background-color: rgba(229, 57, 53, 0.1);
        color: #e53935;
    }

    .status-upcoming {
        background-color: rgba(255, 191, 0, 0.1);
        color: #ffbf00;
    }

    /* Cancellation Details */
    .cancellation-details {
        background: #fff;
        border-radius: 12px;
        padding: 16px;
        margin-top: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(255, 191, 0, 0.2);
    }

    .cancellation-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        color: #e53935;
        font-weight: 600;
    }

    .cancellation-time {
        font-size: 12px;
        color: #666;
        margin-bottom: 8px;
    }

    .cancellation-reason {
        font-size: 14px;
        color: #333;
        line-height: 1.5;
        padding: 12px;
        background: rgba(255, 191, 0, 0.05);
        border-radius: 8px;
        border-left: 3px solid #ffbf00;
    }

    /* Trip Card Layout */
    .trip-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 191, 0, 0.1);
    }

    .trip-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        border-color: #ffbf00;
    }

    .trip-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .trip-date-time {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .trip-date {
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }

    .trip-time {
        font-size: 14px;
        color: #666;
    }

    .trip-price {
        text-align: right;
    }

    .price-per-seat {
        font-size: 18px;
        font-weight: 600;
        color: #ffbf00;
    }

    .total-price {
        font-size: 14px;
        color: #666;
        margin-top: 4px;
    }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .trip-card {
            padding: 16px;
        }

        .trip-header {
            flex-direction: column;
            gap: 12px;
        }

        .trip-price {
            text-align: left;
        }

        .route-with-icon {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .route-arrow {
            transform: rotate(90deg);
            margin: 8px 0;
        }

        .cancellation-details {
            margin-top: 16px;
        }
    }

    @media (max-width: 576px) {
        .trip-overview-container {
            padding: 10px;
        }

        .trip-card {
            padding: 12px;
            margin-bottom: 12px;
        }

        .trip-date {
            font-size: 14px;
        }

        .trip-time {
            font-size: 12px;
        }

        .price-per-seat {
            font-size: 16px;
        }

        .total-price {
            font-size: 12px;
        }

        .cancellation-reason {
            font-size: 13px;
            padding: 10px;
        }
    }

    /* Animation Effects */
    .fade-slide-in {
        animation: fadeSlideIn 0.5s ease-out forwards;
    }

    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Hover Effects */
    .trip-card:hover .route-with-icon {
        border-color: #ffbf00;
        background: rgba(255, 191, 0, 0.08);
    }

    .trip-card:hover .trip-status-badge {
        transform: scale(1.05);
    }

    .trip-card:hover .cancellation-details {
        border-color: #ffbf00;
    }

    /* Loading Skeleton */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        from {
            background-position: 200% 0;
        }
        to {
            background-position: -200% 0;
        }
    }
  </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
  <div class="mini">
    <div class="trip-overview-container">
      <div class="trip-overview-header">Your Overview</div>
      <div class="trip-overview-stats">
        <div class="trip-overview-card">
          <img src="images/icons/complete.gif" alt="Trip" class="trip-image">
          <div class="trip-overview-card-title">Completed</div>
          <div class="trip-overview-card-value"><?php echo $result->num_rows; ?> Trips</div>
          <div class="trip-overview-card-sub">Total completed trips</div>
        </div>
        <div class="trip-overview-card">
          <img src="images/icons/wallets.gif" alt="Trip" class="trip-image">
          <div class="trip-overview-card-title">Total Spent</div>
          <div class="trip-overview-card-value">₹<?php echo number_format($total_spent, 2); ?></div>
          <div class="trip-overview-card-sub">On completed trips</div>
        </div>
        <div class="trip-overview-card">
          <img src="images/icons/stats.gif" alt="Trip" class="trip-image">
          <div class="trip-overview-card-title">Stats</div>
          <div class="trip-overview-card-value"><?php echo $upcoming_count; ?> / <?php echo $cancelled_count; ?></div>
          <div class="trip-overview-card-sub">Upcoming / Cancelled</div>
        </div>
      </div>

      <div class="trip-tab-container">
        <div class="trip-tab active" onclick="showTab('upcoming')">Upcoming Rides <span class="tab-count">(<?php echo $result_upcoming->num_rows; ?>)</span></div>
        <div class="trip-tab" onclick="showTab('completed')">Completed Rides <span class="tab-count">(<?php echo $result->num_rows; ?>)</span></div>
        <div class="trip-tab" onclick="showTab('cancelled')">Cancelled Rides <span class="tab-count">(<?php echo $result_cancelled->num_rows; ?>)</span></div>
      </div>
      
      <div class="trip-content active" id="upcoming-trips">
        <div class="trip-overview-recent">Upcoming Rides</div>
        <div class="trip-overview-table">
          <?php if($result_upcoming->num_rows > 0): ?>
            <?php $i = 0; // Initialize counter for animation delay ?>
            <?php while($row = mysqli_fetch_assoc($result_upcoming)) { ?>
              <div class="trip-overview-row animate-entrance" style="animation-delay: <?php echo $i * 0.1; ?>s;">
              <?php $i++; // Increment counter ?>
                <div class="trip-overview-col-date">
                  <i class="fas fa-car"></i>
                  <?php echo date("M j, Y", strtotime($row['departure_date'])); ?>
                </div>
                <div class="trip-overview-col-time">
                  <?php echo date("g:i A", strtotime($row['departure_time'])); ?>
                  <div class="trip-countdown" data-departure="<?php echo date('Y-m-d', strtotime($row['departure_date'])) . 'T' . date('H:i:s', strtotime($row['departure_time'])); ?>">
                    Calculating...
                  </div>
                </div>
                <div class="trip-overview-col-route"><?php echo $row['departure_city'] . " → " . $row['destination_city']; ?></div>
                <div class="trip-overview-col-price">₹<?php echo number_format($row['price'], 2); ?> <small>/seat</small></div>
                <div class="trip-overview-col-seats"><?php echo $row['seats_booked']; ?> <small>seats</small></div>
                <div class="trip-overview-col-price-total">₹<?php echo number_format($row['seats_booked'] * $row['price'], 2); ?> <small>total</small></div>
                <div class="trip-overview-col-driver"><?php echo $row['driver_name'] ?: 'Driver'; ?></div>
                <div class="trip-overview-col-status">
                  <form method="post" onsubmit="return checkCancellationTime('<?php echo $row['booking_id']; ?>', '<?php echo $row['departure_date']; ?>', '<?php echo $row['departure_time']; ?>');">
                    <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                    <button type="button" onclick="checkCancellationTime('<?php echo $row['booking_id']; ?>', '<?php echo $row['departure_date']; ?>', '<?php echo $row['departure_time']; ?>')" class="button-cancel">Cancel</button>
                  </form>
                </div>
                <div class="trip-overview-col-arrow">&#x203A;</div>
              </div>
            <?php } ?>
          <?php else: ?>
            <div class="no-trips">
              <i class="fas fa-route" style="font-size: 24px; margin-bottom: 10px; color: #ffbf00;"></i>
              <p>You don't have any upcoming trips. Find a ride to get started!</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="trip-content" id="completed-trips">
        <div class="trip-overview-recent">Completed Rides</div>
        <div class="trip-overview-table">
          <?php if($result->num_rows > 0): ?>
            <?php $i = 0; // Initialize counter for animation delay ?>
            <?php while($row = mysqli_fetch_assoc($result)) { ?>
              <div class="trip-overview-row animate-entrance" style="animation-delay: <?php echo $i * 0.1; ?>s;">
              <?php $i++; // Increment counter ?>
                <div class="trip-overview-col-date">
                  <i class="fas fa-car"></i>
                  <?php echo date("M j, Y", strtotime($row['departure_date'])); ?>
                </div>
                <div class="trip-overview-col-time"><?php echo date("g:i A", strtotime($row['departure_time'])); ?></div>
                <div class="trip-overview-col-route"><?php echo $row['departure_city'] . " → " . $row['destination_city']; ?></div>
                <div class="trip-overview-col-price">₹<?php echo number_format($row['price'], 2); ?> <small>/seat</small></div>
                <div class="trip-overview-col-seats"><?php echo $row['seats_booked']; ?> <small>seats</small></div>
                <div class="trip-overview-col-price-total">₹<?php echo number_format($row['seats_booked'] * $row['price'], 2); ?> <small>total</small></div>
                <div class="trip-overview-col-status completed">Completed</div>
                <div class="trip-overview-col-arrow">&#x203A;</div>
              </div>
            <?php } ?>
          <?php else: ?>
            <div class="no-trips">
              <i class="fas fa-check-circle" style="font-size: 24px; margin-bottom: 10px; color: #2e7d32;"></i>
              <p>You don't have any completed trips yet.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="trip-content" id="cancelled-trips">
        <div class="trip-overview-recent">Cancelled Rides <span class="tab-count">(<?php echo $result_cancelled->num_rows; ?>)</span></div>
        <div class="trip-overview-table">
          <?php if($result_cancelled->num_rows > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result_cancelled)) { ?>
              <div class="trip-card fade-slide-in">
                <div class="trip-header">
                  <div class="trip-date-time">
                    <div class="trip-date">
                      <i class="fas fa-calendar-times"></i>
                      <?php echo date("M j, Y", strtotime($row['departure_date'])); ?>
                    </div>
                    <div class="trip-time">
                      <?php echo date("g:i A", strtotime($row['departure_time'])); ?>
                      <span class="trip-status-badge status-cancelled">
                        <i class="fas fa-times-circle"></i>
                        Cancelled on <?php echo date("M j, g:i A", strtotime($row['cancelled_at'])); ?>
                      </span>
                    </div>
                  </div>
                  <div class="trip-price">
                    <div class="price-per-seat">₹<?php echo number_format($row['price'], 2); ?> <small>/seat</small></div>
                    <div class="total-price">₹<?php echo number_format($row['seats_booked'] * $row['price'], 2); ?> total</div>
                  </div>
                </div>
                
                <div class="trip-route">
                  <div class="route-with-icon">
                    <div class="route-point">
                      <i class="fas fa-map-marker-alt source-icon"></i>
                      <span><?php echo $row['departure_city']; ?></span>
                    </div>
                    <div class="route-arrow">
                      <i class="fas fa-long-arrow-alt-right"></i>
                    </div>
                    <div class="route-point">
                      <i class="fas fa-map-marker-alt destination-icon"></i>
                      <span><?php echo $row['destination_city']; ?></span>
                    </div>
                  </div>
                </div>

                <div class="cancellation-details">
                  <div class="cancellation-header">
                    <i class="fas fa-info-circle"></i>
                    Cancellation Reason
                  </div>
                  <div class="cancellation-reason">
                    <?php echo htmlspecialchars($row['cancellation_reason']); ?>
                  </div>
                </div>
              </div>
            <?php } ?>
          <?php else: ?>
            <div class="no-trips">
              <i class="fas fa-ban"></i>
              <p>You don't have any cancelled trips.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Trip Details Modal -->
  <div id="tripDetailsModal" class="trip-modal">
    <div class="trip-modal-content">
      <div class="trip-modal-header">
        <h2>Trip Details</h2>
        <span class="trip-modal-close">&times;</span>
      </div>
      <div class="trip-modal-body">
        <div class="trip-detail-row">
          <div class="trip-detail-label">Route:</div>
          <div id="tripDetailRoute" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row">
          <div class="trip-detail-label">Date:</div>
          <div id="tripDetailDate" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row">
          <div class="trip-detail-label">Departure Time:</div>
          <div id="tripDetailDepartureTime" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row">
          <div class="trip-detail-label">Arrival Time:</div>
          <div id="tripDetailArrivalTime" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row">
          <div class="trip-detail-label">Price per Seat:</div>
          <div id="tripDetailPrice" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row" id="tripDetailSeatsRow">
          <div class="trip-detail-label">Seats Booked:</div>
          <div id="tripDetailSeats" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row" id="tripDetailTotalRow">
          <div class="trip-detail-label">Total Amount:</div>
          <div id="tripDetailTotalPrice" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row" id="tripDetailDriverRow">
          <div class="trip-detail-label">Driver:</div>
          <div id="tripDetailDriver" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row">
          <div class="trip-detail-label">Status:</div>
          <div id="tripDetailStatus" class="trip-detail-value"></div>
        </div>
        <div class="trip-detail-row" id="tripDetailCancellationRow">
          <div class="trip-detail-label">Cancellation:</div>
          <div class="trip-detail-value">
            <div id="tripDetailCancellationTime"></div>
            <div id="tripDetailCancellationReason" class="modal-cancellation-reason"></div>
          </div>
        </div>
        <div class="trip-detail-actions" id="tripDetailActions">
          <!-- Dynamic action buttons will be added here -->
        </div>
      </div>
    </div>
  </div>
  
  <script>
    function showTab(tabName) {
      // Hide all tabs
      document.querySelectorAll('.trip-content').forEach(tab => {
        tab.classList.remove('active');
      });
      
      // Deactivate all tab buttons
      document.querySelectorAll('.trip-tab').forEach(button => {
        button.classList.remove('active');
      });
      
      // Show selected tab
      document.getElementById(tabName + '-trips').classList.add('active');
      
      // Activate selected tab button
      event.target.classList.add('active');
    }
    
    // Modal functionality
    const modal = document.getElementById('tripDetailsModal');
    const modalClose = document.querySelector('.trip-modal-close');
    
    // Close modal when clicking on X or outside the modal
    modalClose.addEventListener('click', closeModal);
    window.addEventListener('click', function(event) {
      if (event.target === modal) {
        closeModal();
      }
    });
    
    // Escape key to close modal
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeModal();
      }
    });
    
    // Add click event to all trip rows
    document.querySelectorAll('.trip-overview-row').forEach(row => {
      row.addEventListener('click', function(e) {
        // Don't open modal if clicking on the cancel button or form
        if (e.target.classList.contains('button-cancel') || 
            e.target.tagName === 'FORM' || 
            e.target.tagName === 'INPUT') {
          return;
        }
        showTripDetails(this);
      });
    });
    
    // Function to show trip details
    function showTripDetails(row) {
      // Clear previous action buttons
      document.getElementById('tripDetailActions').innerHTML = '';
      
      // Get trip data from the row
      const route = row.querySelector('.trip-overview-col-route').textContent.trim();
      const date = row.querySelector('.trip-overview-col-date').textContent.trim().replace(/^[\s\S]*?([A-Z][a-z]{2} \d{1,2}, \d{4})[\s\S]*$/, '$1');
      const time = row.querySelector('.trip-overview-col-time').textContent.trim();
      
      // Get arrival time if available, otherwise display estimated
      let arrivalTime = "Estimated";
      
      // Get price details
      const priceElement = row.querySelector('.trip-overview-col-price');
      const price = priceElement ? priceElement.textContent.trim() : 'N/A';
      
      // Get seats booked
      const seatsElement = row.querySelector('.trip-overview-col-seats');
      const seats = seatsElement ? seatsElement.textContent.trim() : 'N/A';
      
      // Get total price
      const totalPriceElement = row.querySelector('.trip-overview-col-price-total');
      const totalPrice = totalPriceElement ? totalPriceElement.textContent.trim() : 'N/A';
      
      // Get driver if available
      const driverElement = row.querySelector('.trip-overview-col-driver');
      const driver = driverElement ? driverElement.textContent.trim() : 'N/A';
      
      // Show/hide driver row based on availability
      document.getElementById('tripDetailDriverRow').style.display = 
        (driver && driver !== 'N/A') ? 'flex' : 'none';
      
      // Get status
      const statusElement = row.querySelector('.trip-overview-col-status');
      let status = 'N/A';
      
      if (statusElement) {
        if (statusElement.querySelector('form')) {
          status = 'Active';
          
          // Add cancel button to modal actions
          const bookingIdInput = statusElement.querySelector('input[name="booking_id"]');
          if (bookingIdInput) {
            const bookingId = bookingIdInput.value;
            const cancelButton = document.createElement('button');
            cancelButton.className = 'button-cancel modal-cancel-btn';
            cancelButton.textContent = 'Cancel Booking';
            cancelButton.onclick = function() {
              // Get trip data for time check
              const tripDate = document.getElementById('tripDetailDate').textContent.trim();
              const tripTime = document.getElementById('tripDetailDepartureTime').textContent.trim();
              
              // Convert date format from "May 18, 2025" to "2025-05-18"
              const dateParts = new Date(tripDate);
              const formattedDate = dateParts.getFullYear() + '-' + 
                                  String(dateParts.getMonth() + 1).padStart(2, '0') + '-' + 
                                  String(dateParts.getDate()).padStart(2, '0');
              
              // Convert time format from "11:54 PM" to "23:54:00"
              let hours = parseInt(tripTime.split(':')[0]);
              const minutes = tripTime.split(':')[1].split(' ')[0];
              const ampm = tripTime.split(' ')[1];
              
              if (ampm === 'PM' && hours < 12) hours += 12;
              if (ampm === 'AM' && hours === 12) hours = 0;
              
              const formattedTime = String(hours).padStart(2, '0') + ':' + minutes + ':00';
              
              checkCancellationTime(bookingId, formattedDate, formattedTime);
            };
            document.getElementById('tripDetailActions').appendChild(cancelButton);
          }
        } else if (statusElement.classList.contains('completed')) {
          status = 'Completed';
        } else if (statusElement.classList.contains('cancelled')) {
          status = 'Cancelled';
        } else {
          status = statusElement.textContent.trim();
        }
      }
      
      // Populate modal with trip details
      document.getElementById('tripDetailRoute').textContent = route;
      document.getElementById('tripDetailDate').textContent = date;
      document.getElementById('tripDetailDepartureTime').textContent = time;
      document.getElementById('tripDetailArrivalTime').textContent = arrivalTime;
      document.getElementById('tripDetailPrice').textContent = price;
      document.getElementById('tripDetailSeats').textContent = seats;
      document.getElementById('tripDetailTotalPrice').textContent = totalPrice;
      document.getElementById('tripDetailDriver').textContent = driver;
      document.getElementById('tripDetailStatus').textContent = status;
      
      // Set status color based on status text
      const statusValueElement = document.getElementById('tripDetailStatus');
      statusValueElement.className = 'trip-detail-value';
      
      if (status.toLowerCase().includes('completed')) {
        statusValueElement.classList.add('completed');
        statusValueElement.style.color = '#2e7d32';
      } else if (status.toLowerCase().includes('cancelled')) {
        statusValueElement.classList.add('cancelled');
        statusValueElement.style.color = '#e53935';
      } else if (status.toLowerCase().includes('active')) {
        statusValueElement.style.color = '#ffbf00';
      }
      
      // Add cancellation reason to modal if available
      const cancellationRow = document.getElementById('tripDetailCancellationRow');
      const cancellationReason = row.querySelector('.cancellation-reason');
      const cancellationTime = row.querySelector('.trip-status-badge');
      
      if (cancellationReason && cancellationTime) {
        cancellationRow.style.display = 'flex';
        document.getElementById('tripDetailCancellationTime').textContent = 
          cancellationTime.textContent.trim();
        document.getElementById('tripDetailCancellationReason').textContent = 
          cancellationReason.textContent.trim();
      } else {
        cancellationRow.style.display = 'none';
      }
      
      // Show the modal
      modal.classList.add('show');
      
      // Prevent event bubbling
      event.stopPropagation();
    }
    
    // Function to close the modal
    function closeModal() {
      modal.classList.remove('show');
      
      // Clear modal data after animation completes
      setTimeout(() => {
        document.getElementById('tripDetailRoute').textContent = '';
        document.getElementById('tripDetailDate').textContent = '';
        document.getElementById('tripDetailDepartureTime').textContent = '';
        document.getElementById('tripDetailArrivalTime').textContent = '';
        document.getElementById('tripDetailPrice').textContent = '';
        document.getElementById('tripDetailSeats').textContent = '';
        document.getElementById('tripDetailTotalPrice').textContent = '';
        document.getElementById('tripDetailDriver').textContent = '';
        document.getElementById('tripDetailStatus').textContent = '';
        document.getElementById('tripDetailActions').innerHTML = '';
      }, 300);
    }
  </script>
  
  <?php include 'footer.php'; ?>
</div></body>
</html>
