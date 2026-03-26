<?php include 'nav.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Meet Our Founders | PoolPal</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
  <style>
    :root {
      --primary-color: #f5c849;
      --primary-dark: #e8ba3a;
      --secondary-color: #4a4a4a;
      --text-color: #333;
      --light-bg: #f8f9fa;
      --card-bg: #ffffff;
      --border-radius: 12px;
      --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .founders-body {
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

    .founders-container {
      max-width: 1100px;
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

    .founder-section {
      display: flex;
      gap: 40px;
      align-items: center;
      margin-bottom: 80px;
      position: relative;
      padding: 30px;
      border-radius: 20px;
      transition: all 0.3s ease;
    }

    .founder-section:hover {
      background: rgba(255, 255, 255, 0.7);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .founder-section.second {
      flex-direction: row-reverse;
    }

    .founder-section::after {
      content: '';
      position: absolute;
      bottom: -40px;
      left: 10%;
      right: 10%;
      height: 1px;
      background: linear-gradient(to right, transparent, rgba(0,0,0,0.08), transparent);
    }

    .founder-section:last-of-type::after {
      display: none;
    }

    .founder-profile {
      text-align: center;
      flex-shrink: 0;
      flex: 1;
      position: relative;
    }

    .founder-img-container {
      position: relative;
      width: 220px;
      height: 220px;
      margin: 0 auto;
    }

    .founder-img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      border-radius: 50%;
      border: 5px solid white;
      box-shadow: var(--box-shadow);
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      background-color: white;
    }

    .founder-img-bg {
      position: absolute;
      top: -10px;
      left: -10px;
      right: -10px;
      bottom: -10px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-color), transparent);
      opacity: 0.5;
      z-index: -1;
      transition: all 0.5s ease;
    }

    .founder-profile:hover .founder-img {
      transform: scale(1.05);
    }

    .founder-profile:hover .founder-img-bg {
      transform: scale(1.1) rotate(10deg);
      opacity: 0.7;
    }

    .founder-name {
      font-weight: 700;
      font-size: 1.5rem;
      margin: 20px 0 5px;
    }

    .founder-title {
      font-size: 0.9rem;
      color: var(--secondary-color);
      margin-top: 0;
      font-weight: 500;
      display: inline-block;
      padding: 5px 15px;
      background-color: rgba(245, 200, 73, 0.1);
      border-radius: 20px;
    }

    .founder-social {
      margin-top: 15px;
    }

    .social-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: white;
      color: var(--secondary-color);
      margin: 0 5px;
      font-size: 16px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
    }

    .social-icon:hover {
      background: var(--primary-color);
      color: white;
      transform: translateY(-3px);
    }

    .founder-description {
      flex: 2;
      font-size: 1rem;
      line-height: 1.8;
      color: var(--secondary-color);
      position: relative;
      padding: 20px;
      border-radius: 12px;
      transition: all 0.3s ease;
    }

    .founder-description p {
      margin-bottom: 15px;
      position: relative;
      z-index: 1;
    }

    .founder-description p strong {
      color: var(--text-color);
      position: relative;
      display: inline-block;
    }

    .founder-description p strong:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 6px;
      background-color: rgba(245, 200, 73, 0.3);
      z-index: -1;
      transform: translateY(2px);
    }

    .mission-values {
      margin-top: 80px;
      padding-top: 60px;
      position: relative;
    }

    .mission-values::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: linear-gradient(to right, transparent, rgba(0,0,0,0.08), transparent);
    }

    .mission-heading {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 25px;
      text-align: center;
      position: relative;
      display: inline-block;
    }

    .mission-heading::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 0;
      width: 60px;
      height: 3px;
      background: var(--primary-color);
    }

    .mission-text {
      font-size: 1rem;
      line-height: 1.8;
      margin-bottom: 40px;
      max-width: 800px;
    }

    .values-heading {
      font-size: 1.8rem;
      font-weight: 700;
      margin: 60px 0 30px;
      text-align: center;
    }

    .values-box {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 25px;
      margin-top: 40px;
    }

    .value-card {
      background-color: var(--card-bg);
      border-radius: var(--border-radius);
      padding: 30px;
      text-align: center;
      box-shadow: var(--box-shadow);
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      position: relative;
      z-index: 1;
      overflow: hidden;
    }

    .value-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, var(--primary-color), transparent);
      opacity: 0;
      z-index: -1;
      transition: opacity 0.5s ease;
    }

    .value-card:hover {
      transform: translateY(-15px);
    }

    .value-card:hover::before {
      opacity: 0.05;
    }

    .value-icon {
      font-size: 2rem;
      margin-bottom: 20px;
      display: inline-block;
      padding: 20px;
      border-radius: 50%;
      background-color: rgba(245, 200, 73, 0.1);
      color: var(--primary-dark);
    }

    .value-title {
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .value-heading {
      font-size: 1.2rem;
      font-weight: 700;
      margin-bottom: 15px;
      color: var(--text-color);
    }

    .value-text {
      font-size: 0.95rem;
      color: var(--secondary-color);
      line-height: 1.6;
    }

    .back-button-container {
      text-align: center;
      margin-top: 60px;
    }

    .back-button {
      background-color: var(--primary-color);
      color: #000;
      border: none;
      padding: 12px 32px;
      font-weight: 600;
      font-size: 0.95rem;
      border-radius: 30px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(245, 200, 73, 0.3);
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .back-button::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.7s ease;
      z-index: -1;
    }

    .back-button:hover {
      background-color: var(--primary-dark);
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(245, 200, 73, 0.4);
    }

    .back-button:hover::before {
      left: 100%;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      .founder-img-container {
        width: 180px;
        height: 180px;
      }
      
      .section-title {
        font-size: 1.8rem;
      }
      
      .mission-heading, .values-heading {
        font-size: 1.6rem;
      }
      
      .founder-section {
        padding: 20px;
      }
    }

    @media (max-width: 768px) {
      .page-header {
        padding: 60px 0 30px;
      }
      
      .founder-section {
        flex-direction: column;
        text-align: center;
        gap: 20px;
        padding: 25px 15px;
      }
      
      .founder-section.second {
        flex-direction: column;
      }
      
      .mission-text, .founder-description {
        text-align: center;
      }
      
      .founder-description {
        padding: 15px 10px;
      }
      
      .mission-heading::after {
        left: 50%;
        transform: translateX(-50%);
      }
      
      .founder-description p strong:after {
        left: 0;
        right: 0;
        margin: 0 auto;
      }
      
      .founder-description {
        padding-top: 10px;
      }
    }

    @media (max-width: 480px) {
      .section-title {
        font-size: 1.5rem;
      }
      
      .founder-img-container {
        width: 150px;
        height: 150px;
      }
      
      .founder-name {
        font-size: 1.3rem;
      }
      
      .founder-title {
        font-size: 0.8rem;
        padding: 4px 12px;
      }
      
      .founder-section {
        margin-bottom: 60px;
        padding: 20px 10px;
      }
      
      .social-icon {
        width: 32px;
        height: 32px;
        font-size: 14px;
      }
      
      .mission-heading, .values-heading {
        font-size: 1.4rem;
      }
      
      .values-box {
        grid-template-columns: 1fr;
      }
      
      .back-button {
        padding: 10px 25px;
        font-size: 0.9rem;
      }
    }

    /* Modern Animations */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(60px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes scaleIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }

    @keyframes shimmer {
      0% { background-position: -100% 0; }
      100% { background-position: 200% 0; }
    }

    /* Scroll indicator */
    .scroll-indicator {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 1.5rem;
      color: var(--primary-color);
      animation: bounce 2s infinite;
    }

    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(0) translateX(-50%); }
      40% { transform: translateY(-15px) translateX(-50%); }
      60% { transform: translateY(-7px) translateX(-50%); }
    }
  </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="founders-body" class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
  <div class="page-header">
    <div class="header-content">
      <h1 data-aos="fade-up" data-aos-duration="800" class="section-title">Meet Our Founders</h1>
      <p data-aos="fade-up" data-aos-delay="100" data-aos-duration="800" class="section-subtitle">Visionaries behind PoolPal who are transforming everyday commuting with innovation and purpose.</p>
      <div class="scroll-indicator">
        <i class="fas fa-chevron-down"></i>
      </div>
    </div>
  </div>

  <div class="founders-container">
    <div class="founder-section" data-aos="fade-up" data-aos-duration="1000">
      <div class="founder-profile">
        <div class="founder-img-container">
          <div class="founder-img-bg"></div>
          <img src="images/founder1.jpeg" alt="Singheetam Raghupathi Yadav" class="founder-img" />
        </div>
        <h3 class="founder-name">Singheetam Raghupathi Yadav</h3>
        <p class="founder-title">Founder & Chief Executive Officer(CEO)</p>
        <div class="founder-social">
          <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
          <a href="#" class="social-icon"><i class="fas fa-envelope"></i></a>
        </div>
      </div>
      <div class="founder-description" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
        <p>
          Pool Pal, founded by <strong>Singheetam Raghupathi Yadav</strong>,
          is a smart carpooling platform designed to make commuting more efficient,
          affordable, and eco-friendly. With a vision to reduce traffic congestion and
          carbon emissions, Singheetam launched Pool Pal to connect
          everyday travelers through safe, shared rides and build a greener tomorrow.
        </p>
        <p>
          His leadership and innovative approach have positioned Pool Pal at the forefront of sustainable transportation solutions, creating a positive impact on communities and the environment.
        </p>
      </div>
    </div>

    <div class="founder-section second" data-aos="fade-up" data-aos-duration="1000">
      <div class="founder-profile">
        <div class="founder-img-container">
          <div class="founder-img-bg"></div>
          <img src="images/founder4.jpg" alt="Bhumi Reddy Chinna Alluraiah" class="founder-img" />
        </div>
        <h3 class="founder-name">Bhumi Reddy Chinna Alluraiah</h3>
        <p class="founder-title">Co-Founder & Chief Business Officer(CBO)/Chief Growth Officer(CGO)</p>
        <div class="founder-social">
          <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
          <a href="#" class="social-icon"><i class="fas fa-envelope"></i></a>
        </div>
      </div>
      <div class="founder-description" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
        <p>
          <strong>Bhumi Reddy Chinna Alluraiah</strong> is the visionary Co-Founder & Chief Financial Officer(CFO)
          behind Pool Pal, a smart carpooling platform dedicated to transforming the way
          people commute. With a passion for sustainability and innovation,
          Bhumi Reddy helped launch Pool Pal to reduce traffic congestion,
          cut carbon emissions, and create a more connected commuting experience.
        </p>
        <p>
          His strategic insights and commitment to user experience have been instrumental in developing Pool Pal's intuitive platform that connects drivers and riders seamlessly.
        </p>
      </div>
    </div>

    <div class="founder-section" data-aos="fade-up" data-aos-duration="1000">
      <div class="founder-profile">
        <div class="founder-img-container">
          <div class="founder-img-bg"></div>
          <img src="images/founder3.jpeg" alt="Eslavath Kumar" class="founder-img" onerror="this.src='images/default.jpg'" />
        </div>
        <h3 class="founder-name">Eslavath Kumar</h3>
        <p class="founder-title">Chief Technology & Operations Officer(CTO) & Chief Operating Officer(COO)</p>
        <div class="founder-social">
          <a href="https://www.linkedin.com/in/eslavathkumar?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
          <a href="mailto:eslavathkumar50@gmail.com" class="social-icon"><i class="fas fa-envelope"></i></a>
        </div>
      </div>
      <div class="founder-description" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
        <p>
          <strong>Eslavath Kumar</strong> serves as the Chief Technology & Operations Officer(CTO) & Chief Operating Officer(COO) at PoolPal, bringing a dynamic blend of technical expertise and operational strategy to the forefront of sustainable commuting. With a mission to build a reliable, scalable, and user-centric platform, Eslavath plays a crucial role in driving PoolPal’s core technology and ensuring seamless ride-sharing experiences.
        </p>
      </div>
    </div>

    <div class="mission-values" data-aos="fade-up" data-aos-duration="1000">
      <div class="mission-section">
        <h3 class="mission-heading">Our Mission</h3>
        <p class="mission-text">
          Our mission at Pool Pal is to revolutionize everyday commuting by making carpooling
          convenient, cost-effective, and eco-friendly. We strive to build a trusted network
          of drivers and riders who value safety, sustainability, and social connection.
          Through innovation and community, we're driving the change toward a smarter, shared
          future — one ride at a time.
        </p>
      </div>

      <h3 class="values-heading" data-aos="fade-up" data-aos-duration="1000">Our Core Values</h3>
      <div class="values-box">
        <div class="value-card" data-aos="fade-up" data-aos-delay="100" data-aos-duration="800">
          <div class="value-icon">
            <i class="fas fa-users"></i>
          </div>
          <p class="value-title">Community</p>
          <h4 class="value-heading">People First</h4>
          <p class="value-text">We foster meaningful connections and build trust among riders and drivers, creating a community that travels better together.</p>
        </div>
        <div class="value-card" data-aos="fade-up" data-aos-delay="200" data-aos-duration="800">
          <div class="value-icon">
            <i class="fas fa-leaf"></i>
          </div>
          <p class="value-title">Sustainability</p>
          <h4 class="value-heading">Greener Rides</h4>
          <p class="value-text">We're committed to reducing carbon footprints by encouraging shared journeys that minimize environmental impact and maximize resources.</p>
        </div>
        <div class="value-card" data-aos="fade-up" data-aos-delay="300" data-aos-duration="800">
          <div class="value-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <p class="value-title">Safety</p>
          <h4 class="value-heading">Safe Travels</h4>
          <p class="value-text">We prioritize safety with verified profiles, secure payments, and transparent systems that create peace of mind for everyone in our network.</p>
        </div>
      </div>
    </div>
  </div>

  <?php include 'footer.php'; ?>
  
  <!-- AOS Animation Library -->
  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <script>
    AOS.init({
      once: true,
      offset: 100,
      duration: 800
    });
    
    // Smooth scroll for the indicator arrow
    document.querySelector('.scroll-indicator').addEventListener('click', function() {
      window.scrollTo({
        top: document.querySelector('.founders-container').offsetTop - 30,
        behavior: 'smooth'
      });
    });
  </script>
</div></body>
</html>
