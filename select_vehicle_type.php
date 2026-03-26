<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug session at the beginning
error_log("Session at start of select_vehicle_type.php: " . print_r($_SESSION, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vehicle_type'])) {
    // Store vehicle type in session
    $vehicle_type = trim($_POST['vehicle_type']);
    
    // Ensure vehicle_type is not empty
    if (empty($vehicle_type)) {
        $vehicle_type = 'Car'; // Default to Car if somehow empty
    }
    
    // Store in session
    $_SESSION['vehicle_type'] = $vehicle_type;
    
    // Log the selected vehicle type
    error_log("Vehicle type selected and stored in session: '" . $vehicle_type . "'");
    error_log("Session after setting vehicle_type: " . print_r($_SESSION, true));
    
    // Set a cookie as backup in case session fails
    setcookie('vehicle_type', $vehicle_type, time() + 3600, '/');
    
    // Redirect to driver signup page with vehicle type in URL as fallback
    header('Location: driver_signup.php?vt=' . urlencode($vehicle_type));
    exit();
}

// Include database connection to verify it's working
include('db.php');

// Test database connection
if ($conn->connect_error) {
    error_log("Database connection failed in select_vehicle_type.php: " . $conn->connect_error);
} else {
    error_log("Database connection successful in select_vehicle_type.php");
    
    // Check if vehicle_type column exists in drivers table
    try {
        $checkColumnQuery = "SELECT COUNT(*) as count FROM information_schema.COLUMNS 
                            WHERE TABLE_SCHEMA = 'ride_app' 
                            AND TABLE_NAME = 'drivers' 
                            AND COLUMN_NAME = 'vehicle_type'";
        $result = $conn->query($checkColumnQuery);
        
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['count'] == 0) {
                error_log("WARNING: vehicle_type column doesn't exist in drivers table");
            } else {
                error_log("vehicle_type column exists in drivers table");
            }
        } else {
            error_log("Error checking for vehicle_type column: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Exception checking vehicle_type column: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Vehicle Type - PoolPal</title>
    <!-- External CSS Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/vehicle_select.css">
    <style>
        /* Base Styles */
        .vtype-body { 
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Animated Background */
        .vtype-animated-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            background: linear-gradient(135deg, #f8f9fc 0%, #eef1f5 100%);
        }

        .vtype-shape {
            position: absolute;
            background: rgba(255, 193, 7, 0.1);
            border-radius: 50%;
            animation: vtypeFloat 15s infinite ease-in-out;
        }

        .vtype-shape-1 {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -100px;
            animation-delay: 0s;
        }

        .vtype-shape-2 {
            width: 200px;
            height: 200px;
            top: 70%;
            right: -50px;
            animation-delay: 2s;
        }

        .vtype-shape-3 {
            width: 150px;
            height: 150px;
            bottom: 10%;
            left: 15%;
            animation-delay: 4s;
        }

        .vtype-shape-4 {
            width: 80px;
            height: 80px;
            top: 20%;
            right: 20%;
            animation-delay: 6s;
        }

        .vtype-shape-5 {
            width: 120px;
            height: 120px;
            bottom: 30%;
            right: 35%;
            animation-delay: 8s;
        }

        .vtype-car-icon {
            position: absolute;
            color: rgba(255, 193, 7, 0.2);
            animation: vtypeDrive 20s infinite linear;
        }

        .vtype-car-icon-1 {
            font-size: 30px;
            top: 15%;
            left: -50px;
            animation-delay: 0s;
        }

        .vtype-car-icon-2 {
            font-size: 24px;
            top: 45%;
            left: -50px;
            animation-delay: 5s;
        }

        .vtype-car-icon-3 {
            font-size: 36px;
            top: 75%;
            left: -50px;
            animation-delay: 10s;
        }

        @keyframes vtypeFloat {
            0% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(10px, 15px) rotate(5deg);
            }
            50% {
                transform: translate(5px, 25px) rotate(10deg);
            }
            75% {
                transform: translate(15px, 5px) rotate(5deg);
            }
            100% {
                transform: translate(0, 0) rotate(0deg);
            }
        }

        @keyframes vtypeDrive {
            0% {
                transform: translateX(-50px);
            }
            100% {
                transform: translateX(calc(100vw + 50px));
            }
        }

        .vtype-main {
            max-width: 1200px;
            margin: 40px auto 2rem;
            padding: 0 1.5rem;
            position: relative;
            z-index: 1;
        }

        .vtype-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 2.5rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 193, 7, 0.1);
            position: relative;
            overflow: hidden;
        }

        /* Card highlight effect */
        .vtype-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            background: linear-gradient(
                125deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.3) 30%,
                rgba(255, 255, 255, 0.4) 50%,
                rgba(255, 255, 255, 0.3) 70%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: translateX(-100%);
            animation: cardShine 3s infinite;
        }

        @keyframes cardShine {
            0% {
                transform: translateX(-100%);
            }
            20%, 100% {
                transform: translateX(100%);
            }
        }

        .vtype-title {
            text-align: center;
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .vtype-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 3rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .vtype-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 2.5rem;
        }

        /* Vehicle Option Styles */
        .vtype-option {
            background: #ffffff;
            border-radius: 20px;
            padding: 1.8rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
            border: 2px solid transparent;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .vtype-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.15);
            border-color: rgba(255, 193, 7, 0.3);
        }

        .vtype-content, .vtype-subcontent {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            position: relative;
            z-index: 1;
        }

        .vtype-icon, .vtype-subicon {
            font-size: 2rem;
            color: #ffc107;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 193, 7, 0.1);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .vtype-name, .vtype-subname {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .vtype-description, .vtype-subdescription {
            font-size: 0.95rem;
            color: #666;
            margin-top: 0.5rem;
            line-height: 1.5;
        }

        .vtype-check {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: #ffc107;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
            font-size: 1.4rem;
        }

        .vtype-option.selected {
            border-color: #ffc107;
            background: rgba(255, 193, 7, 0.02);
        }

        .vtype-option.selected .vtype-check {
            opacity: 1;
        }

        .vtype-option.selected .vtype-icon {
            background: rgba(255, 193, 7, 0.2);
            transform: scale(1.1);
        }

        /* Sub-options Styles */
        .vtype-suboptions {
            margin-left: 3.5rem;
            margin-top: -0.5rem;
            margin-bottom: 1rem;
            padding-top: 0.8rem;
            animation: vtypeSlideDown 0.4s ease-in-out;
        }

        .vtype-suboption {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 15px;
            padding: 1.2rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            border: 2px solid transparent;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .vtype-suboption:hover {
            transform: translateX(8px);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.1);
        }

        .vtype-suboption.selected {
            border-color: #ffc107;
            background: rgba(255, 193, 7, 0.02);
        }

        /* Animation Keyframes */
        @keyframes vtypeSlideDown {
            from { 
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Button Styles */
        .vtype-btn {
            background: #ffc107;
            color: #2c3e50;
            border: none;
            padding: 1.2rem 2.5rem;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 2rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.2);
        }

        .vtype-btn:hover {
            background: #ffb300;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.3);
        }

        .vtype-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            color: #666;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .vtype-back:hover {
            color: #ffc107;
            gap: 0.8rem;
        }

        .vtype-back i {
            font-size: 1.1rem;
        }

        /* Hide Radio Buttons but Keep Functionality */
        .vtype-option input[type="radio"],
        .vtype-suboption input[type="radio"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .vtype-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .vtype-main {
                margin: 30px auto 1rem;
                padding: 0 1rem;
            }

            .vtype-card {
                padding: 1.5rem;
            }

            .vtype-title {
                font-size: 2rem;
            }

            .vtype-subtitle {
                font-size: 1rem;
                margin-bottom: 2rem;
            }

            .vtype-option {
                padding: 1.5rem;
            }

            .vtype-icon {
                width: 40px;
                height: 40px;
                font-size: 1.6rem;
            }

            .vtype-suboptions {
                margin-left: 2rem;
            }
        }

        @media (max-width: 480px) {
            .vtype-grid {
                grid-template-columns: 1fr;
            }

            .vtype-name {
                font-size: 1.2rem;
            }

            .vtype-description {
                font-size: 0.9rem;
            }

            .vtype-btn {
                padding: 1rem 2rem;
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body class="vtype-body">
    <!-- Animated Background -->
    <div class="vtype-animated-background">
        <div class="vtype-shape vtype-shape-1"></div>
        <div class="vtype-shape vtype-shape-2"></div>
        <div class="vtype-shape vtype-shape-3"></div>
        <div class="vtype-shape vtype-shape-4"></div>
        <div class="vtype-shape vtype-shape-5"></div>
        <div class="vtype-car-icon vtype-car-icon-1"><i class="fas fa-car"></i></div>
        <div class="vtype-car-icon vtype-car-icon-2"><i class="fas fa-car-side"></i></div>
        <div class="vtype-car-icon vtype-car-icon-3"><i class="fas fa-taxi"></i></div>
    </div>

    <?php include_once 'header.php'; ?>

    <!-- Main Content -->
    <main class="vtype-main">
        <div class="vtype-card vs-fade-in">
            <h1 class="vtype-title">Select Your Vehicle Type</h1>
            <p class="vtype-subtitle">Choose the type of vehicle you'll be driving with PoolPal to provide the best experience for your passengers</p>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="vehicleTypeForm">
                <div class="vtype-grid">
                    <!-- Car Option -->
                    <div class="vtype-option vs-delay-1">
                        <input type="radio" id="car" name="vehicle_type" value="Car" class="vehicle-main-option" data-has-suboptions="true">
                        <div class="vtype-content">
                            <span class="vtype-icon"><i class="fas fa-car-side"></i></span>
                            <div>
                                <span class="vtype-name">Car</span>
                                <p class="vtype-description">Choose between carpooling and taxi services</p>
                            </div>
                        </div>
                        <div class="vtype-check"><i class="fas fa-check-circle"></i></div>
                    </div>
                    
                    <!-- Car Sub Options -->
                    <div class="vtype-suboptions car-suboptions" style="display: none;">
                        <div class="vtype-suboption">
                            <input type="radio" id="carpooling" name="vehicle_type" value="Car-Pooling">
                            <div class="vtype-subcontent">
                                <span class="vtype-subicon"><i class="fas fa-users"></i></span>
                                <div>
                                    <span class="vtype-subname">Carpooling</span>
                                    <p class="vtype-subdescription">Share rides with other passengers</p>
                                </div>
                            </div>
                        </div>
                        <div class="vtype-suboption">
                            <input type="radio" id="taxi" name="vehicle_type" value="Car-Taxi">
                            <div class="vtype-subcontent">
                                <span class="vtype-subicon"><i class="fas fa-taxi"></i></span>
                                <div>
                                    <span class="vtype-subname">Taxi</span>
                                    <p class="vtype-subdescription">Private taxi service</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bike Option -->
                    <div class="vtype-option vs-delay-2">
                        <input type="radio" id="bike" name="vehicle_type" value="Bike">
                        <div class="vtype-content">
                            <span class="vtype-icon"><i class="fas fa-motorcycle"></i></span>
                            <div>
                                <span class="vtype-name">Bike</span>
                                <p class="vtype-description">Fast and efficient for solo passengers</p>
                            </div>
                        </div>
                        <div class="vtype-check"><i class="fas fa-check-circle"></i></div>
                    </div>
                    
                    <!-- Auto Rickshaw Option -->
                    <div class="vtype-option vs-delay-3">
                        <input type="radio" id="auto" name="vehicle_type" value="Auto Rickshaw">
                        <div class="vtype-content">
                            <span class="vtype-icon"><i class="fas fa-taxi"></i></span>
                            <div>
                                <span class="vtype-name">Auto Rickshaw</span>
                                <p class="vtype-description">Economical option for short city trips</p>
                            </div>
                        </div>
                        <div class="vtype-check"><i class="fas fa-check-circle"></i></div>
                    </div>
                    
                    <!-- Bus Option -->
                    <div class="vtype-option vs-delay-4">
                        <input type="radio" id="bus" name="vehicle_type" value="Bus">
                        <div class="vtype-content">
                            <span class="vtype-icon"><i class="fas fa-bus-alt"></i></span>
                            <div>
                                <span class="vtype-name">Bus</span>
                                <p class="vtype-description">Ideal for large groups and longer trips</p>
                            </div>
                        </div>
                        <div class="vtype-check"><i class="fas fa-check-circle"></i></div>
                    </div>
                    
                    <!-- Goods Service Option -->
                    <div class="vtype-option vs-delay-5">
                        <input type="radio" id="goods" name="vehicle_type" value="Goods Service" class="vehicle-main-option" data-has-suboptions="true">
                        <div class="vtype-content">
                            <span class="vtype-icon"><i class="fas fa-truck"></i></span>
                            <div>
                                <span class="vtype-name">Goods Service</span>
                                <p class="vtype-description">Choose vehicle type for cargo delivery</p>
                            </div>
                        </div>
                        <div class="vtype-check"><i class="fas fa-check-circle"></i></div>
                    </div>

                    <!-- Goods Service Sub Options -->
                    <div class="vtype-suboptions goods-suboptions" style="display: none;">
                        <div class="vtype-suboption">
                            <input type="radio" id="7ft" name="vehicle_type" value="Goods-7ft">
                            <div class="vtype-subcontent">
                                <span class="vtype-subicon"><i class="fas fa-truck-pickup"></i></span>
                                <div>
                                    <span class="vtype-subname">7ft Vehicle</span>
                                    <p class="vtype-subdescription">Suitable for small cargo</p>
                                </div>
                            </div>
                        </div>
                        <div class="vtype-suboption">
                            <input type="radio" id="8ft" name="vehicle_type" value="Goods-8ft">
                            <div class="vtype-subcontent">
                                <span class="vtype-subicon"><i class="fas fa-truck-moving"></i></span>
                                <div>
                                    <span class="vtype-subname">8ft Vehicle</span>
                                    <p class="vtype-subdescription">Medium cargo capacity</p>
                                </div>
                            </div>
                        </div>
                        <div class="vtype-suboption">
                            <input type="radio" id="3wheeler-cargo" name="vehicle_type" value="Goods-3Wheeler">
                            <div class="vtype-subcontent">
                                <span class="vtype-subicon"><i class="fas fa-truck-loading"></i></span>
                                <div>
                                    <span class="vtype-subname">3 Wheeler Cargo</span>
                                    <p class="vtype-subdescription">For fruits and vegetables</p>
                                </div>
                            </div>
                        </div>
                        <div class="vtype-suboption">
                            <input type="radio" id="tata407" name="vehicle_type" value="Goods-Tata407">
                            <div class="vtype-subcontent">
                                <span class="vtype-subicon"><i class="fas fa-truck-monster"></i></span>
                                <div>
                                    <span class="vtype-subname">Tata 407</span>
                                    <p class="vtype-subdescription">Large cargo transportation</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="vtype-btn">Continue to Registration</button>
                <a href="driver_login.php" class="vtype-back"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </form>
        </div>
    </main>

   <!-- Footer Section -->
   <footer class="pp-footer">
        <div class="pp-footer__container">
            <p class="pp-footer__text">&copy; <?php echo date('Y'); ?> PoolPal. All rights reserved.</p>
        </div>
    </footer>

    <!-- Custom JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('vehicleTypeForm');
        const mainOptions = document.querySelectorAll('.vehicle-main-option');
        const allOptions = document.querySelectorAll('input[type="radio"][name="vehicle_type"]');
        
        // Function to hide all suboptions
        function hideAllSuboptions() {
            document.querySelectorAll('.vtype-suboptions').forEach(sub => {
                sub.style.display = 'none';
            });
        }

        // Function to remove selected state from all options
        function removeAllSelected() {
            document.querySelectorAll('.vtype-option, .vtype-suboption').forEach(option => {
                option.classList.remove('selected');
            });
        }

        // Handle main option selection
        document.querySelectorAll('.vtype-option').forEach(option => {
            const mainRadio = option.querySelector('.vehicle-main-option');
            
            option.addEventListener('click', function(e) {
                const radio = this.querySelector('input[type="radio"]');
                if (!radio.checked) {
                    radio.checked = true;
                    
                    // If this is a main option with suboptions
                    if (mainRadio && mainRadio.dataset.hasSuboptions === 'true') {
                        hideAllSuboptions();
                        removeAllSelected();
                        this.classList.add('selected');

                        const suboptionsClass = radio.id + '-suboptions';
                        const suboptions = document.querySelector('.' + suboptionsClass);
                        if (suboptions) {
                            suboptions.style.display = 'block';
                            // Uncheck any previously selected suboptions
                            suboptions.querySelectorAll('input[type="radio"]').forEach(subRadio => {
                                subRadio.checked = false;
                            });
                        }
                    } else if (!mainRadio) {
                        // For options without suboptions
                        hideAllSuboptions();
                        removeAllSelected();
                        this.classList.add('selected');
                    }
                }
                e.stopPropagation();
            });
        });

        // Handle sub-option selection
        document.querySelectorAll('.vtype-suboption').forEach(suboption => {
            suboption.addEventListener('click', function(e) {
                const radio = this.querySelector('input[type="radio"]');
                if (!radio.checked) {
                    radio.checked = true;
                    
                    // Find and update parent option
                    const parentSuboptions = this.closest('.vtype-suboptions');
                    if (parentSuboptions) {
                        const parentOption = parentSuboptions.previousElementSibling;
                        removeAllSelected();
                        if (parentOption) {
                            parentOption.classList.add('selected');
                        }
                        this.classList.add('selected');
                    }
                }
                e.stopPropagation();
            });
        });

        // Form submission validation
        form.addEventListener('submit', function(e) {
            const selectedOption = document.querySelector('input[type="radio"][name="vehicle_type"]:checked');
            
            if (!selectedOption) {
                e.preventDefault();
                alert('Please select a vehicle type');
                return;
            }

            // Additional validation for main options with suboptions
            if (selectedOption.classList.contains('vehicle-main-option') && 
                selectedOption.dataset.hasSuboptions === 'true') {
                const suboptionsClass = selectedOption.id + '-suboptions';
                const suboptions = document.querySelector('.' + suboptionsClass);
                const hasSelectedSuboption = suboptions.querySelector('input[type="radio"]:checked');
                
                if (!hasSelectedSuboption) {
                    e.preventDefault();
                    alert('Please select a specific option under ' + selectedOption.value);
                }
            }
        });

        // Add animation classes on load
        document.querySelectorAll('.vtype-option').forEach((option, index) => {
            option.style.animationDelay = `${index * 0.1}s`;
            option.classList.add('animate__animated', 'animate__fadeInUp');
        });
    });
    </script>
</body>
</html>