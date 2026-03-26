<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact Us | PoolPal</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
  <style>
    :root {
      --primary-color: #ffbf00;
      --primary-dark: #e6ac00;
      --primary-light: #ffe07a;
      --secondary-color: #4a4a4a;
      --text-color: #333;
      --light-bg: #f8f9fa;
      --card-bg: #ffffff;
      --border-radius: 12px;
      --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .contact-body {
      font-family: 'Inter', sans-serif;
      background-color: var(--light-bg);
      color: var(--text-color);
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }

    .page-header {
      position: relative;
      padding: 80px 0 40px;
      background: linear-gradient(135deg, #f5f7fa 0%, #fafbfd 100%);
      text-align: center;
      margin-bottom: 60px;
      overflow: hidden;
    }

    .page-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTQ0MCIgaGVpZ2h0PSI3NCIgdmlld0JveD0iMCAwIDE0NDAgNzQiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTAgNzRMMTYwIDQxLjdDMzIwIDkuMyA0ODAgLTEuNCA2NDAgMC4zQzgwMCAyIDk2MCAzNS43IDEyMDAgNDQuN0wxNDQwIDUzLjdWNzRIMFoiIGZpbGw9IndoaXRlIiBmaWxsLW9wYWNpdHk9IjAuOCIvPjwvc3ZnPg==');
      background-position: bottom center;
      background-repeat: no-repeat;
      background-size: 100%;
      z-index: 1;
      opacity: 0.7;
    }

    .header-content {
      position: relative;
      z-index: 2;
    }

    .contact-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px 60px;
    }

    .section-title {
      font-size: 2.2rem;
      font-weight: 700;
      margin-bottom: 10px;
      background: linear-gradient(to right, var(--primary-color), #f8d87a);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      display: inline-block;
    }

    .section-subtitle {
      font-size: 1rem;
      color: var(--secondary-color);
      max-width: 600px;
      margin: 0 auto 40px;
    }

    .contact-wrapper {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      margin-bottom: 40px;
    }

    .contact-info {
      flex: 1;
      min-width: 300px;
    }

    .contact-form {
      flex: 1.5;
      min-width: 350px;
      background: var(--card-bg);
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      padding: 30px;
      transition: transform 0.3s ease;
    }

    .contact-form:hover {
      transform: translateY(-5px);
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--text-color);
    }

    .form-control {
      width: 100%;
      padding: 14px 16px;
      font-size: 1rem;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      transition: all 0.3s ease;
      background-color: #f9f9f9;
      color: var(--text-color);
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(255, 191, 0, 0.2);
      outline: none;
      background-color: white;
    }

    textarea.form-control {
      min-height: 150px;
      resize: vertical;
    }

    .submit-btn {
      background-color: var(--primary-color);
      color: var(--text-color);
      font-size: 1rem;
      font-weight: 600;
      padding: 14px 30px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-block;
      margin-top: 10px;
      box-shadow: 0 4px 12px rgba(255, 191, 0, 0.2);
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .submit-btn:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 0;
      height: 100%;
      background-color: white;
      transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
      z-index: -1;
    }

    .submit-btn:hover {
      color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .submit-btn:hover:before {
      width: 100%;
    }

    .contact-item {
      display: flex;
      align-items: flex-start;
      gap: 15px;
      margin-bottom: 25px;
      transition: transform 0.3s ease;
    }

    .contact-item:hover {
      transform: translateX(5px);
    }

    .contact-icon {
      background: rgba(255, 191, 0, 0.1);
      color: var(--primary-color);
      width: 46px;
      height: 46px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 18px;
      transition: all 0.3s ease;
      flex-shrink: 0;
    }

    .contact-item:hover .contact-icon {
      background: var(--primary-color);
      color: white;
    }

    .contact-text {
      flex: 1;
    }

    .contact-text h4 {
      margin: 0 0 5px;
      font-size: 1.1rem;
      color: var(--text-color);
    }

    .contact-text p {
      margin: 0;
      font-size: 0.95rem;
      color: var(--secondary-color);
      line-height: 1.6;
    }

    .map-container {
      margin-top: 40px;
      height: 400px;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--box-shadow);
    }

    .map-container iframe {
      width: 100%;
      height: 100%;
      border: none;
    }

    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }

    .social-link {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 40px;
      height: 40px;
      background: white;
      border-radius: 50%;
      color: var(--secondary-color);
      font-size: 18px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .social-link:hover {
      background: var(--primary-color);
      color: white;
      transform: translateY(-5px);
    }

    .contact-card {
      background: var(--card-bg);
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      padding: 25px;
      margin-bottom: 30px;
      transition: transform 0.3s ease;
    }

    .contact-card:hover {
      transform: translateY(-5px);
    }

    .contact-card h3 {
      font-size: 1.3rem;
      margin-top: 0;
      margin-bottom: 20px;
      position: relative;
      padding-bottom: 10px;
    }

    .contact-card h3:after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      height: 3px;
      width: 40px;
      background: var(--primary-color);
      transition: width 0.3s ease;
    }

    .contact-card:hover h3:after {
      width: 60px;
    }

    .faq-section {
      margin-top: 60px;
      background: var(--card-bg);
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      padding: 30px;
    }

    .faq-item {
      margin-bottom: 15px;
      border-bottom: 1px solid #eee;
      padding-bottom: 15px;
    }

    .faq-question {
      font-weight: 600;
      color: var(--text-color);
      margin-bottom: 8px;
      font-size: 1.05rem;
    }

    .faq-answer {
      color: var(--secondary-color);
      line-height: 1.6;
      font-size: 0.95rem;
    }
    
    .alert {
      padding: 15px 20px;
      border-radius: var(--border-radius);
      margin-bottom: 30px;
      position: relative;
      font-size: 0.95rem;
      line-height: 1.6;
      animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .alert-success {
      background-color: rgba(76, 175, 80, 0.1);
      border-left: 4px solid #4CAF50;
      color: #2e7d32;
    }
    
    .alert-error {
      background-color: rgba(244, 67, 54, 0.1);
      border-left: 4px solid #F44336;
      color: #d32f2f;
    }
    
    .alert-error ul {
      margin: 10px 0 0 20px;
      padding: 0;
    }
    
    .alert-error li {
      margin-bottom: 5px;
    }

    @media (max-width: 768px) {
      .section-title {
        font-size: 1.8rem;
      }
      
      .contact-wrapper {
        flex-direction: column;
      }
      
      .contact-form,
      .contact-info {
        width: 100%;
      }
      
      .map-container {
        height: 300px;
      }
    }

    @media (max-width: 576px) {
      .section-title {
        font-size: 1.5rem;
      }
      
      .contact-form {
        padding: 20px;
      }
      
      .form-control {
        padding: 12px;
      }
      
      .submit-btn {
        width: 100%;
      }
      
      .map-container {
        height: 250px;
      }
    }
  </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="contact-body" class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
  <div class="page-header">
    <div class="header-content">
      <h1 class="section-title">Contact Us</h1>
      <p class="section-subtitle">Have questions or need assistance? We're here to help! Reach out to our team using any of the methods below.</p>
    </div>
  </div>

  <div class="contact-container">
    <?php 
    // Display success message if set
    if (isset($_SESSION['contact_success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['contact_success'] . '</div>';
        unset($_SESSION['contact_success']);
    }
    
    // Display error message if set
    if (isset($_SESSION['contact_error'])) {
        echo '<div class="alert alert-error">' . $_SESSION['contact_error'] . '</div>';
        unset($_SESSION['contact_error']);
    }
    
    // Display validation errors if any
    if (isset($_SESSION['contact_errors']) && !empty($_SESSION['contact_errors'])) {
        echo '<div class="alert alert-error"><ul>';
        foreach ($_SESSION['contact_errors'] as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul></div>';
        unset($_SESSION['contact_errors']);
    }
    ?>
    <div class="contact-wrapper">
      <div class="contact-info">
        <div class="contact-card">
          <h3>Get In Touch</h3>
          
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="contact-text">
              <h4>Our Location</h4>
              <p>13-5-154, rama rao nagar, Sanathnagar<br>Hyderabad- 500018, Telangana</p>
            </div>
          </div>
          
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fas fa-phone-alt"></i>
            </div>
            <div class="contact-text">
              <h4>Call Us</h4>
              <p>+91 9948434347<br>Monday-Friday, 9am-6pm</p>
            </div>
          </div>
          
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fas fa-envelope"></i>
            </div>
            <div class="contact-text">
              <h4>Email Us</h4>
              <p>support@poolpal.in<br>info@poolpal.in</p>
            </div>
          </div>
          
          <div class="social-links">
            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>
        
        <div class="contact-card">
          <h3>Business Hours</h3>
          <div class="contact-item">
            <div class="contact-icon">
              <i class="far fa-clock"></i>
            </div>
            <div class="contact-text">
              <p><strong>Monday-Friday:</strong> 9:00 AM - 6:00 PM<br>
                <strong>Saturday:</strong> 10:00 AM - 4:00 PM<br>
                <strong>Sunday:</strong> Closed</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="contact-form" data-aos="fade-up">
        <h3>Send Us a Message</h3>
        <form action="process_contact.php" method="POST">
          <div class="form-group">
            <label for="name">Your Name</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required>
          </div>
          
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address" required>
          </div>
          
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="form-control" placeholder="Enter your phone number">
          </div>
          
          <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" class="form-control" placeholder="What is this regarding?">
          </div>
          
          <div class="form-group">
            <label for="message">Your Message</label>
            <textarea id="message" name="message" class="form-control" placeholder="Write your message here..." required></textarea>
          </div>
          
          <button type="submit" class="submit-btn">Send Message <i class="fas fa-paper-plane"></i></button>
        </form>
      </div>
    </div>
    
    <div class="map-container" data-aos="fade-up">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3806.095881215598!2d78.41596777521198!3d17.455124383443653!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bcb911a76f1023d%3A0x40011feef47563!2s13-5-117%2C%20Rama%20Rao%20Nagar%2C%20Borabanda%2C%20Hyderabad%2C%20Telangana%20500018!5e0!3m2!1sen!2sin!4v1748879499343!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
    
    <div class="faq-section" data-aos="fade-up">
      <h3 class="section-title" style="font-size: 1.5rem;">Frequently Asked Questions</h3>
      
      <div class="faq-item">
        <div class="faq-question">How does PoolPal ensure safety during rides?</div>
        <div class="faq-answer">At PoolPal, safety is our top priority. We verify all drivers and passengers through a thorough identity verification process. Additionally, we offer real-time GPS tracking, driver ratings, and an emergency contact feature.</div>
      </div>
      
      <div class="faq-item">
        <div class="faq-question">Can I cancel my ride after booking?</div>
        <div class="faq-answer">Yes, you can cancel your ride through the platform. However, cancellation policies vary depending on how close to the departure time you cancel. Check our cancellation policy for detailed information.</div>
      </div>
      
      <div class="faq-item">
        <div class="faq-question">How are ride prices calculated?</div>
        <div class="faq-answer">Ride prices are set by drivers based on distance, fuel costs, and vehicle type. PoolPal ensures all prices remain fair and competitive compared to other transportation options.</div>
      </div>
      
      <div class="faq-item">
        <div class="faq-question">How can I become a driver on PoolPal?</div>
        <div class="faq-answer">To become a driver, you need to sign up on our platform, verify your identity, provide your vehicle details, and upload the necessary documents. Our team will review your application and get back to you within 1-2 business days.</div>
      </div>
    </div>
  </div>

  <?php include 'footer.php'; ?>

  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      AOS.init({
        duration: 800,
        offset: 100,
        once: true
      });
    });
  </script>
</div></body>
</html> 