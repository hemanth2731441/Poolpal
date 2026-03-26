<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PoolPal - Professional Pool Services & Maintenance</title>
  
  <!-- Primary Meta Tags -->
  <meta name="title" content="PoolPal - Professional Pool Services & Maintenance">
  <meta name="description" content="Expert pool services, maintenance, and solutions. Professional pool cleaning, repair, and installation services for residential and commercial pools.">
  <meta name="keywords" content="pool services, pool maintenance, pool cleaning, pool repair, pool installation, swimming pool services, professional pool care">
  <meta name="author" content="PoolPal">
  <meta name="robots" content="index, follow">
  
  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://poolpal.com/">
  <meta property="og:title" content="PoolPal - Professional Pool Services & Maintenance">
  <meta property="og:description" content="Expert pool services, maintenance, and solutions. Professional pool cleaning, repair, and installation services for residential and commercial pools.">
  <meta property="og:image" content="https://poolpal.com/banner/1.jpg">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="https://poolpal.com/">
  <meta property="twitter:title" content="PoolPal - Professional Pool Services & Maintenance">
  <meta property="twitter:description" content="Expert pool services, maintenance, and solutions. Professional pool cleaning, repair, and installation services for residential and commercial pools.">
  <meta property="twitter:image" content="https://poolpal.com/banner/1.jpg">

  <!-- Canonical URL -->
  <link rel="canonical" href="https://poolpal.com/">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="favicon.png">
  <link rel="apple-touch-icon" href="apple-touch-icon.png">

  <!-- Existing styles and fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  
  <!-- Structured Data for Local Business -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "name": "PoolPal",
    "image": "https://poolpal.com/banner/1.jpg",
    "description": "Professional pool services, maintenance, and solutions for residential and commercial pools.",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "Your Street Address",
      "addressLocality": "Your City",
      "addressRegion": "Your State",
      "postalCode": "Your Postal Code",
      "addressCountry": "Your Country"
    },
    "geo": {
      "@type": "GeoCoordinates",
      "latitude": "YOUR_LATITUDE",
      "longitude": "YOUR_LONGITUDE"
    },
    "url": "https://poolpal.com",
    "telephone": "YOUR_PHONE_NUMBER",
    "priceRange": "$$",
    "openingHoursSpecification": {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": [
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday"
      ],
      "opens": "08:00",
      "closes": "18:00"
    }
  }
  </script>

  <style>
    :root {
      --primary-color: #00a8e8;
      --secondary-color: #0077b6;
      --accent-color: #ffbf00;
      --text-light: #ffffff;
      --text-dark: #2b2d42;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #000;
      overflow-x: hidden;
    }

    .sinit {
      padding: 0;
      box-sizing: border-box;
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      background: #000;
    }

    .cs-slider-container {
      position: relative;
      width: 100%;
      max-width: 1920px;
      margin: 0 auto;
      overflow: hidden;
      height: 500px; /* Fixed height for banner */
    }

    .cs-slider {
      display: flex;
      transition: transform 1.5s cubic-bezier(0.4, 0, 0.2, 1);
      height: 100%;
    }

    .cs-slide {
      min-width: 100%;
      height: 100%;
      opacity: 0;
      transform: scale(0.95);
      transition: opacity 1.5s cubic-bezier(0.4, 0, 0.2, 1),
                  transform 1.5s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      position: absolute;
      top: 0;
      left: 0;
    }

    .cs-slide img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .cs-slide.active {
      opacity: 1;
      transform: scale(1);
      position: relative;
      z-index: 1;
    }

    .cs-prev,
    .cs-next {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(255, 255, 255, 0.1);
      border: none;
      color: white;
      width: 50px;
      height: 50px;
      font-size: 1.5rem;
      cursor: pointer;
      border-radius: 50%;
      z-index: 2;
      backdrop-filter: blur(5px);
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .cs-prev {
      left: 20px;
    }

    .cs-next {
      right: 20px;
    }

    .cs-prev:hover,
    .cs-next:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateY(-50%) scale(1.1);
    }

    .slide-indicator {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 10px;
      z-index: 2;
    }

    .indicator-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.3);
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .indicator-dot.active {
      background: rgba(255, 255, 255, 0.8);
      transform: scale(1.2);
    }

    .slide-content {
      position: relative;
      z-index: 2;
      text-align: center;
      color: var(--text-light);
      padding: 2rem;
      max-width: 800px;
      opacity: 0;
      transform: translateY(30px);
      transition: opacity 1s ease, transform 1s ease;
    }

    .slide-content.active {
      opacity: 1;
      transform: translateY(0);
    }

    .slide-title {
      font-size: 4rem;
      font-weight: 700;
      margin-bottom: 1rem;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .slide-subtitle {
      font-size: 1.5rem;
      font-weight: 300;
      margin-bottom: 2rem;
      line-height: 1.6;
    }

    .cta-button {
      display: inline-block;
      padding: 1rem 2.5rem;
      background: var(--accent-color);
      color: var(--text-dark);
      text-decoration: none;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(255, 191, 0, 0.3);
      border: none;
      cursor: pointer;
    }

    .cta-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(255, 191, 0, 0.4);
      background: #ffd700;
    }

    @media (max-width: 1024px) {
      .cs-slider-container {
        height: 400px;
      }
      .slide-title {
        font-size: 3rem;
      }
      .slide-subtitle {
        font-size: 1.2rem;
      }
    }

    @media (max-width: 768px) {
      .cs-slider-container {
        height: 300px;
      }
      .slide-title {
        font-size: 2.5rem;
      }
      .slide-subtitle {
        font-size: 1.1rem;
      }
      .cs-prev,
      .cs-next {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
      }
    }

    @media (max-width: 480px) {
      .cs-slider-container {
        height: 200px;
      }
      .slide-title {
        font-size: 2rem;
      }
      .slide-subtitle {
        font-size: 1rem;
      }
      .cta-button {
        padding: 0.8rem 2rem;
        font-size: 1rem;
      }
    }
  </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
<div class="sinit">
  <div class="cs-slider-container">
    <div class="cs-slider">
      <div class="cs-slide active">
        <img src="banner/1.jpg" alt="PoolPal Banner 1">
      </div>
      <div class="cs-slide">
        <img src="banner/2.jpg" alt="PoolPal Banner 2">
      </div>
      <div class="cs-slide">
        <img src="banner/3.jpg" alt="PoolPal Banner 3">
      </div>
      <div class="cs-slide">
        <img src="banner/4.jpg" alt="PoolPal Banner 4">
      </div>
      <div class="cs-slide">
        <img src="banner/5.jpg" alt="PoolPal Banner 5">
      </div>
      <div class="cs-slide">
        <img src="banner/6.jpg" alt="PoolPal Banner 6">
      </div>
      <div class="cs-slide">
        <img src="banner/7.jpg" alt="PoolPal Banner 7">
      </div>
    </div>

    <button class="cs-prev">&#10094;</button>
    <button class="cs-next">&#10095;</button>
    <div class="slide-indicator"></div>
  </div>
</div>

<script>
const slides = document.querySelectorAll(".cs-slide");
const prevBtn = document.querySelector(".cs-prev");
const nextBtn = document.querySelector(".cs-next");
const indicatorContainer = document.querySelector(".slide-indicator");

let current = 0;
let slideInterval = setInterval(nextSlide, 5000);

// Create indicator dots
slides.forEach((_, index) => {
  const dot = document.createElement("div");
  dot.classList.add("indicator-dot");
  if (index === 0) dot.classList.add("active");
  dot.addEventListener("click", () => {
    current = index;
    showSlide(current);
    resetTimer();
  });
  indicatorContainer.appendChild(dot);
});

const dots = document.querySelectorAll(".indicator-dot");

function showSlide(index) {
  slides.forEach((slide, i) => {
    slide.classList.remove("active");
    dots[i].classList.remove("active");
    if (i === index) {
      slide.classList.add("active");
      dots[i].classList.add("active");
    }
  });
}

function nextSlide() {
  current = (current + 1) % slides.length;
  showSlide(current);
}

function prevSlide() {
  current = (current - 1 + slides.length) % slides.length;
  showSlide(current);
}

nextBtn.addEventListener("click", () => {
  nextSlide();
  resetTimer();
});

prevBtn.addEventListener("click", () => {
  prevSlide();
  resetTimer();
});

function resetTimer() {
  clearInterval(slideInterval);
  slideInterval = setInterval(nextSlide, 5000);
}

// Pause on hover
document.querySelector(".cs-slider-container").addEventListener("mouseover", () => {
  clearInterval(slideInterval);
});
document.querySelector(".cs-slider-container").addEventListener("mouseout", () => {
  slideInterval = setInterval(nextSlide, 5000);
});

// Add smooth fade-in for images
slides.forEach(slide => {
  const img = slide.querySelector("img");
  img.addEventListener("load", () => {
    img.style.opacity = "1";
  });
  img.style.opacity = "0";
  img.style.transition = "opacity 0.5s ease";
});
</script>

</div></body>
</html>
