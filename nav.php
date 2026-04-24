<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo isset($page_title) ? $page_title : 'PoolPal — Smart Carpooling & Ridesharing Platform in India'; ?></title>
  <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'PoolPal is India\'s smart carpooling and ridesharing platform. Book taxis, share rides, transport goods, plan pilgrimages, and book hotels — all from one app. Save money, reduce traffic, go green.'; ?>" />
  <meta name="keywords" content="<?php echo isset($page_keywords) ? $page_keywords : 'poolpal, carpooling india, ridesharing, taxi booking, shared rides, bike taxi, auto rickshaw, goods transport, bus booking, hotel booking, pool yatra, eco-friendly travel'; ?>" />
  <meta name="author" content="MacGInfotech" />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="<?php echo isset($page_canonical) ? $page_canonical : 'https://poolpal.in/'; ?>" />

  <!-- Open Graph / Social Media -->
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?php echo isset($page_canonical) ? $page_canonical : 'https://poolpal.in/'; ?>" />
  <meta property="og:title" content="<?php echo isset($page_title) ? $page_title : 'PoolPal — Smart Carpooling & Ridesharing Platform in India'; ?>" />
  <meta property="og:description" content="<?php echo isset($page_description) ? $page_description : 'PoolPal is India\'s smart carpooling and ridesharing platform. Book rides, share trips, save money, and go green.'; ?>" />
  <meta property="og:image" content="https://poolpal.in/images/logo/logo.jpeg" />
  <meta property="og:site_name" content="PoolPal" />
  <meta property="og:locale" content="en_IN" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?php echo isset($page_title) ? $page_title : 'PoolPal — Smart Carpooling & Ridesharing'; ?>" />
  <meta name="twitter:description" content="<?php echo isset($page_description) ? $page_description : 'Book rides, share trips, save money. India\'s all-in-one mobility platform.'; ?>" />
  <meta name="twitter:image" content="https://poolpal.in/images/logo/logo.jpeg" />

  <link rel="icon" type="image/x-icon" href="images/favicon/favicon.ico" />
  <link rel="icon" type="image/svg+xml" href="images/favicon/favicon.svg" />
  <link rel="apple-touch-icon" href="images/favicon/apple-touch-icon.png" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/poolpal-marketing.css" />
</head>
<body>

<!-- Floating Particles -->
<div class="particle-bg" id="particleBg"></div>

<!-- Header -->
<header class="pp-header" id="ppHeader">
  <nav class="pp-nav">
    <a href="fpage.php" class="pp-logo-link">
      <img src="images/logo/logo.jpeg" alt="PoolPal" class="pp-logo-img" />
    </a>

    <div class="pp-nav-links">
      <a href="fpage.php" class="pp-nav-link"><i class="fas fa-home"></i> Home</a>
      <a href="services.php" class="pp-nav-link"><i class="fas fa-concierge-bell"></i> Services</a>
      <a href="aboutus.php" class="pp-nav-link"><i class="fas fa-users"></i> About Us</a>
      <a href="fpage.php#download" class="pp-nav-cta"><i class="fas fa-mobile-alt"></i> Get the App</a>
    </div>

    <button class="pp-hamburger" id="ppHamburger" aria-label="Open menu">
      <div class="pp-hamburger-lines">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </button>
  </nav>
</header>

<!-- Mobile Menu -->
<div class="pp-mobile-overlay" id="ppOverlay"></div>
<div class="pp-mobile-menu" id="ppMobileMenu">
  <button class="pp-mobile-close" id="ppMobileClose" aria-label="Close menu"><i class="fas fa-times"></i></button>
  <a href="fpage.php" class="pp-nav-link"><i class="fas fa-home"></i> Home</a>
  <a href="services.php" class="pp-nav-link"><i class="fas fa-concierge-bell"></i> Services</a>
  <a href="aboutus.php" class="pp-nav-link"><i class="fas fa-users"></i> About Us</a>
  <a href="fpage.php#download" class="pp-nav-cta" style="margin-top: 16px; width: 100%; justify-content: center;"><i class="fas fa-mobile-alt"></i> Get the App</a>
</div>

<script>
// Header scroll effect
const header = document.getElementById('ppHeader');
window.addEventListener('scroll', () => {
  header.classList.toggle('scrolled', window.scrollY > 50);
});

// Mobile menu
const hamburger = document.getElementById('ppHamburger');
const overlay = document.getElementById('ppOverlay');
const mobileMenu = document.getElementById('ppMobileMenu');
const mobileClose = document.getElementById('ppMobileClose');

function openMenu() {
  mobileMenu.classList.add('active');
  overlay.classList.add('active');
  hamburger.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeMenu() {
  mobileMenu.classList.remove('active');
  overlay.classList.remove('active');
  hamburger.classList.remove('active');
  document.body.style.overflow = '';
}

hamburger.addEventListener('click', openMenu);
overlay.addEventListener('click', closeMenu);
mobileClose.addEventListener('click', closeMenu);

// Close on nav link click
mobileMenu.querySelectorAll('.pp-nav-link, .pp-nav-cta').forEach(link => {
  link.addEventListener('click', closeMenu);
});

// Particles
(function createParticles() {
  const bg = document.getElementById('particleBg');
  if (!bg) return;
  for (let i = 0; i < 25; i++) {
    const p = document.createElement('div');
    p.className = 'particle ' + (Math.random() > 0.5 ? 'particle-gold' : 'particle-white');
    const size = Math.random() * 6 + 3;
    p.style.width = size + 'px';
    p.style.height = size + 'px';
    p.style.left = Math.random() * 100 + '%';
    p.style.animationDuration = (Math.random() * 15 + 15) + 's';
    p.style.animationDelay = (Math.random() * 20) + 's';
    bg.appendChild(p);
  }
})();

// Scroll reveal
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('revealed');
    }
  });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale').forEach(el => {
    revealObserver.observe(el);
  });
});
</script>
