<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pool Pal Footer</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    .pp-universal, .pp-body, .pp-html {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    .pp-body, .pp-html {
      font-family: 'Inter', sans-serif;
      background: #fafafa;
      height: 100%;
    }

    footer.pp-footer {
      background: linear-gradient(to right, #f8f9fa, #f1f3f5);
      color: #444;
      font-size: 15px;
      padding: 60px 20px 20px;
      position: relative;
      overflow: hidden;
    }

    footer.pp-footer::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(to right, #ffbf00, #ffd966);
    }

    .pp-footer-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      max-width: 1200px;
      margin: 0 auto 40px;
      gap: 30px;
      position: relative;
      z-index: 2;
    }

    .pp-footer-section {
      display: flex;
      flex-direction: column;
      gap: 15px;
      transition: transform 0.3s ease;
    }

    .pp-footer-section:hover {
      transform: translateY(-5px);
    }

    .pp-logo {
      margin-bottom: 15px;
      position: relative;
      display: inline-block;
    }

    .pp-logo::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 0;
      width: 60px;
      height: 3px;
      background: #ffbf00;
      transition: width 0.3s ease;
    }

    .pp-logo:hover::after {
      width: 100px;
    }

    .pp-logo img {
      height: 70px;
      width: 160px;
      object-fit: contain;
  
      transition: transform 0.3s ease;
    }

    .pp-logo img:hover {
      transform: scale(1.05);
    }

    .pp-footer h3 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 15px;
      color: #333;
      position: relative;
      display: inline-block;
    }

    .pp-footer h3::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: -8px;
      width: 40px;
      height: 3px;
      background: #ffbf00;
      transition: width 0.3s ease;
    }

    .pp-footer-section:hover h3::after {
      width: 60px;
    }

    .pp-footer p,
    .pp-footer li,
    .pp-footer a {
      color: #555;
      text-decoration: none;
      line-height: 1.8;
      transition: all 0.3s ease;
      font-size: 15px;
    }

    .pp-footer p {
      margin: 0;
    }

    .pp-footer ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .pp-footer ul li {
      margin-bottom: 10px;
      position: relative;
      padding-left: 22px;
      transition: all 0.3s ease;
    }

    .pp-footer ul li::before {
      content: "→";
      position: absolute;
      left: 0;
      color: #ffbf00;
      font-weight: bold;
      transition: transform 0.3s ease;
    }

    .pp-footer ul li:hover::before {
      transform: translateX(5px);
    }

    .pp-quick-links a:hover,
    .pp-footer a:hover {
      color: #ffbf00;
      padding-left: 5px;
    }

    .contact-item {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 15px;
      transition: transform 0.3s ease;
    }

    .contact-item:hover {
      transform: translateX(5px);
    }

    .contact-icon {
      background: rgba(255, 191, 0, 0.1);
      color: #ffbf00;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 16px;
      transition: all 0.3s ease;
    }

    .contact-item:hover .contact-icon {
      background: #ffbf00;
      color: white;
    }

    .contact-text {
      flex: 1;
    }

    .contact-text h4 {
      margin: 0 0 5px;
      font-size: 16px;
      color: #333;
    }

    .contact-text p {
      margin: 0;
      font-size: 14px;
      color: #666;
    }

    .pp-social-wrap {
      width: 100%;
      display: flex;
      justify-content: center;
      margin-top: 20px;
    }

    .pp-social {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      justify-content: center !important;
    }

    .pp-footer-section h3 + .pp-social {
  justify-content: center;
}


    .pp-social a {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 40px;
      height: 40px;
      background: white;
      border-radius: 50%;
      color: #555;
      font-size: 18px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .pp-social a::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: #ffbf00;
      transform: scale(0);
      transition: transform 0.5s ease;
      border-radius: 50%;
      z-index: -1;
    }

    .pp-social a:hover {
      color: white;
      transform: translateY(-5px);
    }

    .pp-social a:hover::before {
      transform: scale(1);
    }

    .pp-footer-divider {
      width: 100%;
      height: 1px;
      background: linear-gradient(to right, transparent, rgba(0,0,0,0.1), transparent);
      margin: 20px 0;
    }

    .pp-footer-bottom {
      text-align: center;
      padding: 20px 0 10px;
      font-size: 14px;
      color: #666;
      position: relative;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .pp-footer-links {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 15px;
    }

    .pp-footer-links a {
      color: #555;
      font-size: 14px;
      transition: color 0.3s ease;
    }

    .pp-footer-links a:hover {
      color: #ffbf00;
    }

    .pp-copyright {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
    }

    .pp-copyright i {
      color: #ffbf00;
      font-size: 16px;
    }

    .pp-footer-badge {
      background: white;
      border-radius: 30px;
      padding: 10px 20px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      margin: 20px;
      transition: all 0.3s ease;
    }

    .pp-footer-badge:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .pp-footer-badge i {
      color: #ffbf00;
      font-size: 20px;
      
    }

    .pp-footer-badge span {
      font-weight: 600;
      color: #333;
    }

    .pp-back-to-top {
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 40px;
      height: 40px;
      background: #ffbf00;
      color: white;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 99;
    }

    .pp-back-to-top.visible {
      opacity: 1;
      visibility: visible;
    }

    .pp-back-to-top:hover {
      background: #e6ac00;
      transform: translateY(-5px);
    }

    @media (max-width: 768px) {
      .pp-footer-container {
        grid-template-columns: 1fr;
        gap: 30px;
      }

      .pp-footer-section {
        text-align: center;
        align-items: center;
      }

      .pp-footer h3::after {
        left: 50%;
        transform: translateX(-50%);
      }

      .pp-footer ul li {
        padding-left: 0;
      }

      .pp-footer ul li::before {
        display: none;
      }

      .contact-item {
        flex-direction: column;
        text-align: center;
      }

      .pp-footer-links {
        flex-direction: column;
        gap: 10px;
      }
    }

    @media (min-width: 769px) and (max-width: 1024px) {
      .pp-footer-container {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0,0,0,0);
      border: 0;
      white-space: nowrap;
    }
  </style>
</head>
<body class="pp-body">
  <footer class="pp-footer">
    <div class="pp-footer-container">
      <div class="pp-footer-section">
        <div class="pp-logo">
          <img src="images/poolpal.jpg" alt="POOL PAL Logo">
        </div>
        <p>Connecting commuters for a greener tomorrow. Share rides, save costs, and reduce carbon footprint together.</p>
        
        <div class="pp-footer-badge">
          <i class="fas fa-leaf"></i>
          <span>Eco-Friendly Travel</span>
        </div>
      </div>

      <div class="pp-footer-section pp-quick-links">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="findrides.php">Find Rides</a></li>
          <li><a href="mytrips.php">Offer Ride</a></li>
          <li><a href="dashboard.php">My Trips</a></li>
          <li><a href="index.php#how-it-works">How It Works</a></li>
          <li><a href="#">Safety Guidelines</a></li>
          <li><a href="#">FAQs</a></li>
        </ul>
      </div>

      <div class="pp-footer-section">
        <h3>Contact Us</h3>
        
        <div class="contact-item">
          <div class="contact-icon">
            <i class="fas fa-envelope"></i>
          </div>
          <div class="contact-text">
            <h4>Email</h4>
            <p>Support@Poolpal.com</p>
          </div>
        </div>
        
        <div class="contact-item">
          <div class="contact-icon">
            <i class="fas fa-phone-alt"></i>
          </div>
          <div class="contact-text">
            <h4>Phone</h4>
            <p>+91 9948434347</p>
          </div>
        </div>
        
        <div class="contact-item">
          <div class="contact-icon">
            <i class="fas fa-clock"></i>
          </div>
          <div class="contact-text">
            <h4>Working Hours</h4>
            <p>Monday-Friday: 9AM-6PM</p>
          </div>
        </div>
      </div>

      <div class="pp-footer-section">
        <h3>Our Address</h3>
        <div class="contact-item">
          <div class="contact-icon">
            <i class="fas fa-map-marker-alt"></i>
          </div>
          <div class="contact-text">
            <p>13-5-154 V, Ramaraonagar, Motinagar,<br>
              Balanagar Mandal, Medchal District,<br>
              Telangana, India<br>
              P.O.: Sanathnagar<br>
              PIN: 500018</p>
          </div>
        </div>
        
        <h3 style="margin-top: 20px;">Follow us</h3>
        <div class="pp-social">
          <a href="https://www.facebook.com/poolpal" aria-label="Facebook" title="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i><span class="sr-only">Facebook</span></a>
          <a href="https://x.com/poolpal" aria-label="Twitter (X)" title="Twitter (X)"><i class="fab fa-twitter" aria-hidden="true"></i><span class="sr-only">Twitter (X)</span></a>
          <a href="https://www.instagram.com/poolpal" aria-label="Instagram" title="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i><span class="sr-only">Instagram</span></a>
          <a href="https://www.linkedin.com/poolpal" aria-label="LinkedIn" title="LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i><span class="sr-only">LinkedIn</span></a>
          <a href="https://www.youtube.com/channel/poolpal" aria-label="YouTube" title="YouTube"><i class="fab fa-youtube" aria-hidden="true"></i><span class="sr-only">YouTube</span></a>
        </div>
      </div>
    </div>

    <div class="pp-footer-divider"></div>

    <div class="pp-footer-bottom">
      
      <div class="pp-copyright">
        <i class="far fa-copyright"></i>
        <span>2025 MacGInfotech. All rights reserved.</span>
      </div>
    </div>
    
    <a href="#" class="pp-back-to-top" id="back-to-top">
      <i class="fas fa-chevron-up"></i>
    </a>
  </footer>

  <script>
    // Back to top button functionality
    const backToTopButton = document.getElementById('back-to-top');
    
    window.addEventListener('scroll', () => {
      if (window.scrollY > 300) {
        backToTopButton.classList.add('visible');
      } else {
        backToTopButton.classList.remove('visible');
      }
    });
    
    backToTopButton.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  </script>
</body>
</html>
