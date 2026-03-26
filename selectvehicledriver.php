<?php include_once 'header.php';?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Vehicle Type - PoolPal</title>
    <style>
        .vehicle-selection {
            --primary-color: #FF6B35;
            --secondary-color: #F7C59F;
            --accent-color: #EFEFD0;
            --hover-color: #e85a2c;
            --text-color: #2B2D42;
            --circle-size: 220px;
            --circle-size-mobile: 280px;
            
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            background: linear-gradient(135deg, #fff5ec 0%, #fff 100%);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        h1 {
            text-align: center;
            color: var(--text-color);
            margin-bottom: 50px;
            font-size: 2.8em;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            padding-bottom: 15px;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        .vehicle-grid {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            flex-wrap: wrap;
        }

        .vehicle-option {
            width: var(--circle-size);
            height: var(--circle-size);
            min-width: var(--circle-size);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: white;
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.1);
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            text-decoration: none;
            border: 2px solid transparent;
            margin-bottom: 20px;
            padding: 25px;
        }

        .vehicle-option::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            background: radial-gradient(circle, var(--secondary-color), transparent 60%);
            opacity: 0;
            transition: all 0.4s ease;
            z-index: 1;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.5);
        }

        .vehicle-option:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(255, 107, 53, 0.2);
            border-color: var(--primary-color);
        }

        .vehicle-option:hover::before {
            opacity: 0.15;
            transform: translate(-50%, -50%) scale(1);
        }

        .vehicle-option img {
            width: 85%;
            height: 85%;
            object-fit: contain;
            margin-bottom: 15px;
            transition: all 0.4s ease;
            z-index: 2;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.15));
            transform-origin: center;
        }

        .vehicle-option:hover img {
            transform: scale(1.05);
            filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.2));
        }

        .vehicle-option span {
            color: var(--text-color);
            font-size: 1.3em;
            font-weight: 600;
            text-align: center;
            z-index: 2;
            transition: all 0.3s ease;
            background: linear-gradient(120deg, transparent 0%, transparent 50%, var(--secondary-color) 50%, var(--secondary-color) 100%);
            background-size: 250% 100%;
            background-position: 100% 100%;
            padding: 8px 20px;
            border-radius: 25px;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .vehicle-option:hover span {
            color: var(--hover-color);
            background-position: 0% 100%;
        }

        @keyframes fadeInUp {
            from { 
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .vehicle-option.animate {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        @media (max-width: 1200px) {
            .vehicle-selection {
                --circle-size: 180px;
            }
            
            .vehicle-grid {
                gap: 15px;
            }
        }

        @media (max-width: 1024px) {
            .vehicle-selection {
                --circle-size: 160px;
            }
        }

        @media (max-width: 900px) {
            .vehicle-selection {
                --circle-size: 140px;
            }
            
            .vehicle-option span {
                font-size: 1.1em;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px 10px;
            }

            .vehicle-grid {
                flex-direction: column;
                gap: 25px;
                padding: 15px;
            }

            .vehicle-option {
                width: var(--circle-size-mobile);
                height: var(--circle-size-mobile);
                min-width: var(--circle-size-mobile);
                padding: 30px;
                margin-bottom: 15px;
            }

            .vehicle-option img {
                width: 80%;
                height: 80%;
                margin-bottom: 20px;
            }

            .vehicle-option span {
                font-size: 1.4em;
                padding: 10px 25px;
            }

            h1 {
                font-size: 2.2em;
                margin-bottom: 30px;
            }
        }

        @media (max-width: 640px) {
            .vehicle-selection {
                --circle-size: 110px;
                --circle-size-mobile: 110px;
            }

            .vehicle-grid {
                gap: 8px;
            }

            .vehicle-option span {
                font-size: 0.9em;
            }
        }

        @media (max-width: 480px) {
            .vehicle-selection {
                --circle-size-mobile: 260px;
            }

            .container {
                padding: 15px 10px;
            }

            .vehicle-grid {
                gap: 20px;
            }

            .vehicle-option {
                padding: 25px;
                margin-bottom: 10px;
            }

            .vehicle-option span {
                font-size: 1.3em;
                padding: 8px 20px;
            }

            h1 {
                font-size: 2em;
                margin-bottom: 25px;
            }
        }

        @media (max-width: 360px) {
            .vehicle-selection {
                --circle-size-mobile: 240px;
            }

            .vehicle-option {
                padding: 20px;
            }

            .vehicle-option span {
                font-size: 1.2em;
                padding: 7px 18px;
            }
        }

        .auto-img {
            width: 65% !important;
            height: 65% !important;
        }
    </style>
</head>
<body>
    <div class="vehicle-selection">
        <div class="container">
            <h1>Choose Your Vehicle</h1>
            <div class="vehicle-grid">
                <a href="driver_login.php" class="vehicle-option" style="animation-delay: 0.1s">
                    <img src="images/vehicle/bikes.png" alt="Bike">
                    <span>Bike</span>
                </a>
                <a href="driver_login.php" class="vehicle-option" style="animation-delay: 0.3s">
                    <img src="images/vehicle/auto.png" alt="Auto" class="auto-img">
                    <span>Auto</span>
                </a>
                <a href="driver_login.php" class="vehicle-option" style="animation-delay: 0.2s">
                    <img src="images/vehicle/car.png" alt="Car">
                    <span>Car</span>
                </a>
                <a href="driver_login.php" class="vehicle-option" style="animation-delay: 0.4s">
                    <img src="images/vehicle/bus.png" alt="Bus">
                    <span>Bus</span>
                </a>
                <a href="driver_login.php" class="vehicle-option" style="animation-delay: 0.5s">
                    <img src="images/vehicle/goods.png" alt="Goods Vehicle">
                    <span>Goods Vehicle</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = document.querySelectorAll('.vehicle-option');
            
            // Intersection Observer for animation
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.classList.add('animate');
                        }, index * 200); // 200ms delay between each animation
                    }
                });
            }, {
                threshold: 0.1
            });

            // Observe each vehicle option
            options.forEach((option) => {
                observer.observe(option);
            });
        });
    </script>
    <?php include 'footer.php';?>
</body>
</html> 