<?php
include 'header.php';
include 'db.php'; // make sure this file connects using $conn

if (isset($_GET['id'])) {
    $ride_id = $_GET['id'];

    // Fetch trip details with driver information and available seats
    $sql = "SELECT t.*, 
            t.driver_name, 
            t.driver_phone, 
            t.driver_email,
            t.vehicle_number,
            4.5 AS driver_rating,
            (t.seats - COALESCE((
                SELECT SUM(seats_booked) 
                FROM bookings 
                WHERE trip_id = t.id 
                AND payment_status = 'completed'
            ), 0)) AS available_seats,
            (SELECT COUNT(*) FROM bookings WHERE trip_id = t.id AND payment_status = 'completed') AS total_bookings
            FROM trips t
            WHERE t.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ride_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $ride = $result->fetch_assoc();

        // Fetch driver details if needed
        $driver_email = $ride['driver_email'];
        $driver_query = "SELECT * FROM drivers WHERE Email = ?";
        $dstmt = $conn->prepare($driver_query);
        $dstmt->bind_param("s", $driver_email);
        $dstmt->execute();
        $driver_result = $dstmt->get_result();
        $driver = $driver_result->fetch_assoc();
    } else {
        echo "Ride not found.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ride Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="css/ride-details.css">
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">

    <div class="container">
        <!-- Breadcrumb -->
        <div class="rd-breadcrumb">
            <span>Dashboard</span>
            <span>Search</span>
            <span>Results</span>
            <span class="active">Ride Details</span>
        </div>

        <!-- Trip Header -->
        <div class="rd-trip-header">
            <h2><i class="fas fa-route"></i> <?= htmlspecialchars($ride['departure_city']) . ' to ' . htmlspecialchars($ride['destination_city']) ?></h2>
            <div class="rd-trip-date"><i class="far fa-calendar-alt"></i> <?= date("l, F j, Y", strtotime($ride['departure_date'])) ?></div>
        </div>

        <!-- Trip Overview -->
        <div class="rd-trip-overview">
            <div class="rd-trip-card">
                <img src="images/icons/1.png" alt="Trip" class="rd-trip-image">
                <h3>Trip Details</h3>
                <div class="rd-trip-detail-item">
                    <i class="far fa-clock"></i> 
                    <span class="rd-detail-value"><?= date("g:i A", strtotime($ride['departure_time'])) ?></span>
                </div>
                <div class="rd-trip-detail-item">
                    <i class="fas fa-hourglass-half"></i>
                    <span class="rd-detail-value"><?php 
                        $duration = htmlspecialchars($ride['duration'] ?? 'N/A');
                        // Convert duration to hours and minutes format if it's just a number
                        if (is_numeric($duration)) {
                            $hours = floor($duration);
                            $minutes = round(($duration - $hours) * 60);
                            echo $hours . "h " . $minutes . "m";
                        } else {
                            echo $duration;
                        }
                    ?></span>
                </div>
                <div class="rd-trip-detail-item">
                    <i class="fas fa-road"></i>
                    <span class="rd-detail-value"><?php echo htmlspecialchars($ride['distance'] ?? 'N/A'); ?> km</span>
                </div>
                <div class="rd-trip-status">
                    <i class="fas fa-check-circle"></i> On Time
                </div>
            </div>
            
            <div class="rd-trip-card">
                <img src="images/icons/rupee.png" alt="Price" class="rd-trip-image">
                <h3>Price per Seat</h3>
                <div class="rd-price-value">₹<?= $ride['price'] ?></div>
            </div>
            
            <div class="rd-trip-card">
                <img src="images/icons/chair.png" alt="Seats" class="rd-trip-image">
                <h3>Available Seats</h3>
                <div class="rd-seats-value"><?= $ride['available_seats'] ?></div>
            </div>
        </div>

        <!-- Driver & Vehicle Info -->
        <div class="rd-driver-vehicle">
            <div class="rd-driver-info">
                <?php if (!empty($driver['Profile_Pic'])): ?>
                    <div class="rd-driver-profile-pic">
                        <img src="<?= htmlspecialchars($driver['Profile_Pic']) ?>" alt="Driver Profile" class="rd-driver-avatar">
                    </div>
                <?php else: ?>
                    <div class="rd-driver-profile-pic">
                        <img src="images/icons/default-avatar.png" alt="Default Profile" class="rd-driver-avatar">
                    </div>
                <?php endif; ?>
                <h3 class="rd-driver-name"><?= htmlspecialchars($ride['driver_name']) ?></h3>
                <p class="rd-driver-bio">Experienced PoolPal driver with excellent ratings for punctuality and safety.</p>
            </div>

            <div class="rd-vehicle-info">
                <img src="images/icons/vehicle.png" alt="Vehicle" class="rd-trip-image">
                <h3>Vehicle Details</h3>
                <div class="rd-vehicle-info-container">
                    <?php if (!empty($ride['vehicle_name'])): ?>
                    <div class="rd-vehicle-name"><i class="fas fa-car"></i> <?php echo htmlspecialchars($ride['vehicle_name']); ?></div>
                    <?php else: ?>
                    <div class="rd-vehicle-name"><i class="fas fa-car"></i> Vehicle</div>
                    <?php endif; ?>
                    <div class="rd-vehicle-number"><i class="fas fa-id-card"></i> <?php echo htmlspecialchars($ride['vehicle_number']); ?></div>
                </div>
                <p><i class="fas fa-check-circle"></i> Clean and comfortable ride</p>
                <p><i class="fas fa-check-circle"></i> Extra space for luggage</p>
                <p><i class="fas fa-check-circle"></i> Well-maintained vehicle</p>
            </div>
        </div>

        <!-- Route Information -->
        <div class="rd-info-section">
            <div class="rd-section-title">Route Information</div>
            <div class="rd-info-box">
                <div class="rd-info-left">
                    <img src="images/icons/start.png" alt="Departure">
                    <span>Departure</span>
                </div>
                <div class="rd-info-right">
                    <div class="rd-info-primary"><?= htmlspecialchars($ride['departure_city'])?></div>
                    <div class="rd-info-secondary"><?= date("D, M j, Y", strtotime($ride['departure_date'])) ?> at <?= date("g:i A", strtotime($ride['departure_time'])) ?></div>
                </div>
            </div>
            <div class="rd-info-box">
                <div class="rd-info-left">
                    <img src="images/icons/time.png" alt="Time">
                    <span>Estimated Travel Time</span>
                </div>
                <div class="rd-info-right">
                    <div class="rd-info-primary"><?php 
                        $duration = htmlspecialchars($ride['duration'] ?? 'N/A');
                        if (is_numeric($duration)) {
                            $hours = floor($duration);
                            $minutes = round(($duration - $hours) * 60);
                            echo $hours . " hours " . ($minutes > 0 ? $minutes . " minutes" : "");
                        } else {
                            echo $duration . " hours approx";
                        }
                    ?></div>
                </div>
            </div>
            <div class="rd-info-box">
                <div class="rd-info-left">
                    <img src="images/icons/destination.png" alt="Arrival">
                    <span>Arrival</span>
                </div>
                <div class="rd-info-right">
                    <div class="rd-info-primary"><?= htmlspecialchars($ride['destination_city']) ?></div>
                    <div class="rd-info-secondary">
                    <?php 
                        // Calculate arrival date based on departure date and time plus duration
                        if(isset($ride['departure_date']) && isset($ride['departure_time']) && isset($ride['duration'])) {
                            $departureDateTime = date_create($ride['departure_date'] . ' ' . $ride['departure_time']);
                            $durationHours = floatval($ride['duration']);
                            $durationInterval = new DateInterval('PT' . floor($durationHours) . 'H' . round(($durationHours - floor($durationHours)) * 60) . 'M');
                            $arrivalDateTime = date_add(clone $departureDateTime, $durationInterval);
                            echo date_format($arrivalDateTime, "D, M j, Y") . " at " . date_format($arrivalDateTime, "g:i A");
                        } else {
                            echo date("D, M j, Y", strtotime($ride['departure_date'])) . " at " . date("g:i A", strtotime($ride['arrival_time']));
                        }
                    ?>
                    </div>
                </div>
            </div>
        </div>
            
        <!-- Trip Policies -->
        <div class="rd-trip-policies">
            <div class="rd-section-title">Trip Policies</div>
            <div class="rd-policy-item">
                <div class="rd-policy-icon"><img src="images/icons/ban.png" alt="Cancellation"></div>
                <div class="rd-policy-content">
                    <div class="rd-policy-title">Cancellation Policy</div>
                    <div class="rd-policy-text">Free cancellation up to 24 hours before departure</div>
                </div>
            </div>
            <div class="rd-policy-item">
                <div class="rd-policy-icon"><img src="images/icons/suitcase.png" alt="Luggage"></div>
                <div class="rd-policy-content">
                    <div class="rd-policy-title">Luggage Allowance</div>
                    <div class="rd-policy-text">
                        <?php echo htmlspecialchars($ride['luggage_space']) ?: 'No luggage information available'; ?>
                    </div>
                </div>
            </div>
            <div class="rd-policy-item">
                <div class="rd-policy-icon"><img src="images/icons/pet.png" alt="Pets"></div>
                <div class="rd-policy-content">
                    <div class="rd-policy-title">Pets Policy</div>
                    <div class="rd-policy-text">
                        <?php echo ($ride['pets_allowed'] == 1) ? 'Pets are allowed on this trip' : 'No pets allowed on this trip'; ?>
                    </div>
                </div>
            </div>
            <div class="rd-policy-item">
                <div class="rd-policy-icon"><img src="images/icons/ac.png" alt="AC"></div>
                <div class="rd-policy-content">
                    <div class="rd-policy-title">Air Conditioning</div>
                    <div class="rd-policy-text">
                        <?php echo $ride['has_ac'] ? 'AC is available in this vehicle' : 'This is a non-AC vehicle'; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Testimonial (New Section) -->
        <div class="rd-testimonial">
            <div class="rd-testimonial-text">PoolPal makes carpooling so convenient and affordable. I've used it for my daily commute and saved thousands of rupees. The drivers are verified and the experience is always great!</div>
            <div class="rd-testimonial-author">
                <img src="images/icons/avatar.png" alt="User Avatar">
                <div class="rd-testimonial-author-info">
                    <div class="rd-testimonial-author-name">Rajesh Kumar</div>
                    <div class="rd-testimonial-author-title">Regular PoolPal User</div>
                </div>
            </div>
        </div>
    
        <!-- Booking Section -->
        <div class="rd-booking-section">
            <h3><i class="fas fa-ticket-alt"></i> Book Your Seat</h3>
            <div class="rd-booking-form">
                <?php if ($ride['available_seats'] > 0): ?>
                    <?php if ($is_logged_in): ?>
                        <!-- Logged in user - show booking form -->
                        <form action="book.php" method="POST" id="bookingForm">
                            <input type="hidden" name="trip_id" value="<?= htmlspecialchars($ride_id) ?>">
                            <div class="rd-form-group">
                                <label for="seats">Number of Seats</label>
                                <select id="seats" name="seats" class="rd-form-control" required>
                                    <?php
                                    $availableSeats = $ride['available_seats']; // Limit to maximum 4 seats per booking
                                    for ($i = 1; $i <= $availableSeats; $i++) {
                                        echo "<option value=\"$i\">$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="rd-form-group">
                                <label for="requests">Special Requests (Optional)</label>
                                <textarea id="requests" name="requests" class="rd-form-control" rows="3" maxlength="500" placeholder="Any special requirements or pickup/dropoff notes"></textarea>
                            </div>

                            <div class="rd-total">
                                <div class="rd-total-price">
                                    Total Amount: ₹<span id="totalPrice"><?= htmlspecialchars($ride['price']) ?></span>
                                </div>
                                <button type="submit" class="rd-book-btn">
                                    <i class="fas fa-check-circle"></i> Confirm Booking
                                </button>
                            </div>
                        </form>
                        
                        <script>
                        document.getElementById('bookingForm').addEventListener('submit', function(e) {
                            // Disable the submit button to prevent double booking
                            const submitBtn = this.querySelector('button[type="submit"]');
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                        });

                        // Update total amount when seats change
                        document.getElementById('seats').addEventListener('change', function() {
                            const seats = parseInt(this.value);
                            const pricePerSeat = <?= htmlspecialchars($ride['price']) ?>;
                            const total = seats * pricePerSeat;
                            document.getElementById('totalPrice').textContent = total;
                        });
                        </script>
                    <?php else: ?>
                        <!-- Not logged in - show login prompt -->
                        <div class="rd-login-prompt">
                            <div class="rd-login-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h3>Login Required</h3>
                            <p>Please log in to your account to book this ride. If you don't have an account, you can create one quickly.</p>
                            <div class="rd-login-actions">
                                <a href="login.php?redirect=<?= urlencode('Ridedetails.php?id=' . $ride_id) ?>" class="rd-btn rd-btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Login to Book
                                </a>
                                <a href="register.php?redirect=<?= urlencode('Ridedetails.php?id=' . $ride_id) ?>" class="rd-btn rd-btn-outline">
                                    <i class="fas fa-user-plus"></i> Create Account
                                </a>
                            </div>
                            <div class="rd-price-preview">
                                <strong>Price per seat: ₹<?= $ride['price'] ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="rd-ride-full">
                        <h3><i class="fas fa-exclamation-circle"></i> Ride Full</h3>
                        <p>Sorry, all seats for this ride have been booked.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Floating Action Button -->
        <div class="rd-fab">
            <i class="fas fa-phone"></i>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const seatSelect = document.getElementById("seats");
        const totalPriceElement = document.getElementById("totalPrice");
        const pricePerSeat = <?php echo $ride['price']; ?>;
        
        // Add animation classes to elements when they appear in viewport
        const animateOnScroll = function() {
            const elements = document.querySelectorAll('.rd-trip-card, .rd-driver-info, .rd-vehicle-info, .rd-info-section, .rd-trip-policies, .rd-booking-section, .rd-testimonial');
            
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementBottom = element.getBoundingClientRect().bottom;
                
                if (elementTop < window.innerHeight && elementBottom > 0) {
                    if (!element.classList.contains('rd-animated')) {
                        element.classList.add('rd-animated');
                        element.style.opacity = "0";
                        setTimeout(() => {
                            element.style.transition = "opacity 0.8s ease-out, transform 0.6s ease-out";
                            element.style.opacity = "1";
                            element.style.transform = "translateY(0)";
                        }, 100);
                    }
                }
            });
        };
        
        // Only run updateTotal if user is logged in and form exists
        if (seatSelect && totalPriceElement) {
            updateTotal();
            seatSelect.addEventListener("change", updateTotal);
        }
        
        // Animation on scroll
        window.addEventListener('scroll', animateOnScroll);
        animateOnScroll(); // Run once on load
        
        function updateTotal() {
            if (!seatSelect || !totalPriceElement) return;
            
            const seats = parseInt(seatSelect.value);
            const total = seats * pricePerSeat;
            
            // Animate the total price change
            const currentValue = parseInt(totalPriceElement.textContent);
            animateValue(totalPriceElement, currentValue, total, 500);
        }
        
        function animateValue(element, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const value = Math.floor(progress * (end - start) + start);
                element.textContent = value.toLocaleString('en-IN');
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }
        
        // Add pulse animation to the book button
        const bookBtn = document.querySelector('.rd-book-btn');
        if (bookBtn) {
            setTimeout(() => {
                bookBtn.style.animation = "pulse 2s infinite";
            }, 3000);
        }
        
        // Floating Action Button click handler
        const fab = document.querySelector('.rd-fab');
        if (fab) {
            fab.addEventListener('click', function() {
                alert('Calling driver... This feature will connect you directly with the driver.');
            });
        }
    });
    </script>

    <style>
    /* Updated styles for driver profile section */
    .rd-driver-info {
        padding: 2rem;
        text-align: center;
    }

    .rd-driver-profile-pic {
        width: 150px;
        height: 150px;
        margin: 0 auto 1.5rem;
        border-radius: 50%;
        overflow: hidden;
    }

    .rd-driver-avatar {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .rd-driver-name {
        font-size: 1.5rem;
        color: #2d3748;
        margin: 0.5rem 0;
        font-weight: 600;
    }

    .rd-driver-bio {
        color: #718096;
        line-height: 1.6;
        margin: 1rem 0;
    }

    /* Additional styles for login prompt */
    .rd-login-prompt {
        text-align: center;
        padding: 40px 20px;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-radius: 15px;
        border: 2px solid #e2e8f0;
    }

    .rd-login-icon {
        font-size: 48px;
        color: #64748b;
        margin-bottom: 20px;
    }

    .rd-login-prompt h3 {
        color: #1e293b;
        margin-bottom: 15px;
        font-size: 24px;
    }

    .rd-login-prompt p {
        color: #64748b;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .rd-login-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 25px;
    }

    .rd-login-actions .rd-btn {
        min-width: 150px;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .rd-btn-primary {
        background: #ffbf00;
        color: #1e293b;
        border: 2px solid #ffbf00;
    }

    .rd-btn-primary:hover {
        background: #e6ac00;
        border-color: #e6ac00;
        transform: translateY(-2px);
    }

    .rd-btn-outline {
        background: transparent;
        color: #64748b;
        border: 2px solid #e2e8f0;
    }

    .rd-btn-outline:hover {
        background: #f8fafc;
        border-color: #ffbf00;
        color: #ffbf00;
        transform: translateY(-2px);
    }

    .rd-price-preview {
        padding: 15px;
        background: rgba(255, 191, 0, 0.1);
        border-radius: 8px;
        color: #1e293b;
        font-size: 18px;
    }

    @media (max-width: 768px) {
        .rd-login-actions {
            flex-direction: column;
            align-items: center;
        }

        .rd-login-actions .rd-btn {
            width: 100%;
            max-width: 250px;
        }
    }
    </style>
</div></body>
</html>
