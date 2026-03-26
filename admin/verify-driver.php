<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include('../db.php');
include('mail_helper.php');

// Function to send verification confirmation email
function sendVerificationEmail($email, $name) {
    // Email subject
    $subject = "PoolPal - Your Driver Account is Verified!";
    
    // Email template with modern, responsive design and yellow theme
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Your PoolPal Driver Account is Verified!</title>
        <style>
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
                background-color: #f9f9f9;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
                border-top: 5px solid #FFD700;
            }
            .email-header {
                background: linear-gradient(135deg, #FFD700 0%, #FFC107 100%);
                padding: 30px;
                text-align: center;
            }
            .logo {
                margin-bottom: 15px;
            }
            .email-header h1 {
                color: #333;
                margin: 0;
                font-size: 28px;
                font-weight: 700;
                text-shadow: 0px 1px 1px rgba(255, 255, 255, 0.5);
            }
            .email-content {
                padding: 35px;
            }
            .email-content p {
                margin-bottom: 20px;
                font-size: 16px;
                color: #444;
            }
            .highlight {
                font-weight: 600;
                color: #FFC107;
            }
            .button {
                display: inline-block;
                background: linear-gradient(135deg, #FFD700 0%, #FFC107 100%);
                color: #333;
                text-decoration: none;
                padding: 14px 32px;
                border-radius: 50px;
                font-weight: 700;
                margin: 25px 0;
                text-align: center;
                box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
                transition: all 0.3s ease;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .button:hover {
                background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%);
                box-shadow: 0 6px 18px rgba(255, 193, 7, 0.5);
                transform: translateY(-2px);
            }
            .features {
                background-color: #FFFCF0;
                padding: 25px;
                border-radius: 10px;
                margin: 25px 0;
                border-left: 4px solid #FFD700;
            }
            .feature-item {
                margin-bottom: 18px;
                display: flex;
                align-items: flex-start;
            }
            .feature-item:last-child {
                margin-bottom: 0;
            }
            .icon {
                width: 28px;
                height: 28px;
                background-color: #FFD700;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #333;
                margin-right: 15px;
                font-weight: bold;
                box-shadow: 0 2px 5px rgba(255, 215, 0, 0.3);
            }
            .stats-container {
                display: flex;
                justify-content: space-between;
                margin: 30px 0;
                text-align: center;
            }
            .stat-item {
                flex: 1;
                padding: 15px;
                background-color: #FFFCF0;
                border-radius: 8px;
                margin: 0 5px;
            }
            .stat-number {
                font-size: 24px;
                font-weight: 700;
                color: #FFC107;
                margin-bottom: 5px;
            }
            .stat-label {
                font-size: 14px;
                color: #666;
            }
            .testimonial {
                font-style: italic;
                background-color: #FFFCF0;
                padding: 20px;
                border-radius: 10px;
                position: relative;
                margin: 30px 0;
            }
            .testimonial:before {
                content: \'"\\201C\';
                font-size: 60px;
                color: #FFD700;
                position: absolute;
                top: -15px;
                left: 10px;
                opacity: 0.3;
            }
            .testimonial-content {
                position: relative;
                z-index: 1;
            }
            .testimonial-author {
                text-align: right;
                font-weight: 600;
                margin-top: 10px;
                color: #555;
            }
            .email-footer {
                background-color: #FFFCF0;
                padding: 25px;
                text-align: center;
                font-size: 14px;
                color: #777;
                border-top: 1px solid #FFE082;
            }
            .social-links {
                margin: 15px 0;
            }
            .social-link {
                display: inline-block;
                margin: 0 8px;
                width: 32px;
                height: 32px;
                background-color: #FFD700;
                border-radius: 50%;
                text-align: center;
                line-height: 32px;
                color: #333;
                text-decoration: none;
                font-weight: bold;
            }
            .divider {
                height: 1px;
                background-color: #FFE082;
                margin: 25px 0;
            }
            .tips-section {
                background-color: #FFFCF0;
                padding: 20px;
                border-radius: 10px;
                margin: 25px 0;
            }
            .tip-title {
                font-weight: 700;
                color: #FFC107;
                margin-bottom: 10px;
            }
            .app-badges {
                display: flex;
                justify-content: center;
                margin: 20px 0;
            }
            .app-badge {
                margin: 0 10px;
                display: inline-block;
            }
            @media only screen and (max-width: 600px) {
                .email-header, .email-content {
                    padding: 20px;
                }
                .stats-container {
                    flex-direction: column;
                }
                .stat-item {
                    margin: 5px 0;
                }
                .app-badges {
                    flex-direction: column;
                    align-items: center;
                }
                .app-badge {
                    margin: 10px 0;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <div class="logo">🚗 PoolPal</div>
                <h1>Your Driver Account is Verified!</h1>
            </div>
            <div class="email-content">
                <p>Hello <span class="highlight">'.htmlspecialchars($name).'</span>,</p>
                
                <p>Congratulations! 🎉 Your PoolPal driver account has been <span class="highlight">successfully verified</span> and is now active. Thank you for completing all the necessary steps in the verification process.</p>
                
                <p>You are now officially part of our growing community of trusted drivers. You can start posting rides, connecting with passengers, and earning money right away!</p>
                
                <center><a href="https://poolpal.com/login" class="button">Access Your Dashboard</a></center>
                
                <div class="stats-container">
                    <div class="stat-item">
                        <div class="stat-number">5000+</div>
                        <div class="stat-label">Active Drivers</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">10K+</div>
                        <div class="stat-label">Daily Rides</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">4.8/5</div>
                        <div class="stat-label">Driver Rating</div>
                    </div>
                </div>
                
                <div class="features">
                    <div class="feature-item">
                        <span class="icon">✓</span>
                        <div>
                            <strong>Post Unlimited Rides</strong> - Create and manage multiple ride listings
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="icon">✓</span>
                        <div>
                            <strong>Flexible Schedule</strong> - Drive whenever it fits your lifestyle
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="icon">✓</span>
                        <div>
                            <strong>Secure Payments</strong> - Get paid directly to your preferred account
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="icon">✓</span>
                        <div>
                            <strong>24/7 Support</strong> - Our team is always ready to assist you
                        </div>
                    </div>
                </div>
                
                <div class="testimonial">
                    <div class="testimonial-content">
                        "Joining PoolPal was one of the best decisions I\'ve made. I\'m saving on fuel costs and meeting interesting people on my daily commute. Plus, the extra income is a great bonus!"
                    </div>
                    <div class="testimonial-author">- Anmol reddy, PoolPal Driver since 2025</div>
                </div>
                
                <div class="tips-section">
                    <div class="tip-title">💡 Tips for Success:</div>
                    <ul>
                        <li>Keep your vehicle clean and well-maintained</li>
                        <li>Be punctual and follow your posted schedule</li>
                        <li>Communicate clearly with your passengers</li>
                        <li>Ask for reviews after successful rides</li>
                    </ul>
                </div>
                
                
                <p>If you have any questions or need assistance, our support team is available 24/7 at <a href="mailto:support@poolpal.in" style="color: #FFC107;">support@poolpal.com</a>.</p>
                
                <p>Safe travels,<br>
                <span class="highlight">The PoolPal Team</span></p>
                
                <div class="divider"></div>
                
                <p style="font-size: 14px; color: #888;">This is an automated message. Please do not reply to this email.</p>
            </div>
            <div class="email-footer">
                <div class="social-links">
                    <a href="https://facebook.com/poolpal" class="social-link">f</a>
                    <a href="https://twitter.com/poolpal" class="social-link">t</a>
                    <a href="https://instagram.com/poolpal" class="social-link">i</a>
                    <a href="https://linkedin.com/company/poolpal" class="social-link">in</a>
                </div>
                &copy; '.date('Y').' PoolPal. All rights reserved.<br>
                <a href="https://poolpal.com/privacy" style="color: #FFC107;">Privacy Policy</a> | 
                <a href="https://poolpal.com/terms" style="color: #FFC107;">Terms of Service</a><br>
                123 PoolPal Avenue, Carpool City, PC 12345
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Try to send email with PHPMailer first
    $sent = send_email($email, $subject, $message);
    
    // If PHPMailer fails, use the fallback method
    if (!$sent) {
        $sent = log_email_fallback($email, $subject, $message);
    }
    
    return $sent;
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $driver_email = $_GET['id'];
    $action = $_GET['action'];
    
    // Validate action
    if ($action == 'accept' || $action == 'reject') {
        // Update verification status
        $status = ($action == 'accept') ? 'accepted' : 'rejected';
        
        // Get driver details
        $driver_name = "";
        if ($action == 'accept') {
            $query = "SELECT Full_Name FROM drivers WHERE Email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $driver_email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $driver_name = $row['Full_Name'];
            }
            $stmt->close();
        }
        
        // Update the driver's verification status
        $sql = "UPDATE drivers SET verification_status = ? WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $status, $driver_email);
        
        if ($stmt->execute()) {
            // If driver is accepted, send confirmation email
            if ($action == 'accept' && !empty($driver_name)) {
                if (sendVerificationEmail($driver_email, $driver_name)) {
                    $_SESSION['success'] = "Driver " . ucfirst($status) . " successfully! Confirmation email sent or queued.";
                } else {
                    $_SESSION['success'] = "Driver " . ucfirst($status) . " successfully! However, email could not be processed.";
                }
            } else {
                // Success message
                $_SESSION['success'] = "Driver " . ucfirst($status) . " successfully!";
            }
        } else {
            // Error message
            $_SESSION['error'] = "Error updating driver status: " . $conn->error;
        }
        
        $stmt->close();
    } else {
        $_SESSION['error'] = "Invalid action";
    }
} else {
    $_SESSION['error'] = "Missing parameters";
}

// Redirect back to the drivers list
header("Location: admin-view-driver.php");
exit;
?>