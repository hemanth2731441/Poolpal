<?php 
// Debug include chain
error_log('Including header.php');

// Include init.php if not already included
if (!defined('CONFIG_INCLUDED')) {
    require_once 'init.php';
}

// Only start session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check for "Remember Me" cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user']) && isset($_COOKIE['remember_token'])) {
    $user_id = $_COOKIE['remember_user'];
    $token = $_COOKIE['remember_token'];
    $current_time = date('Y-m-d H:i:s');
    
    // Check if token is valid
    $stmt = $conn->prepare("SELECT u.id, u.full_name, u.profile_photo, u.phone, u.email FROM users u
                            JOIN remember_tokens r ON u.id = r.user_id
                            WHERE u.id = ? AND r.token = ? AND r.expires > NOW()");
    $stmt->bind_param("is", $user_id, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['profile_photo'] = $user['profile_photo'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['phone'] = $user['phone'];
        
        // Refresh the token
        $new_token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        // Update token in database
        $update_stmt = $conn->prepare("UPDATE remember_tokens SET token = ?, expires = ? WHERE user_id = ? AND token = ?");
        $update_stmt->bind_param("ssis", $new_token, $expires, $user_id, $token);
        $update_stmt->execute();
        
        // Update cookies
        setcookie('remember_token', $new_token, time() + (86400 * 30), "/");
    } else {
        // Invalid or expired token, clear cookies
        setcookie('remember_user', '', time() - 3600, "/");
        setcookie('remember_token', '', time() - 3600, "/");
    }
}

if (isset($_SESSION['user_id'])) {
  $profilePhoto = isset($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.jpg'; // Default if no photo
} else {
  $profilePhoto = 'default.jpg'; // Default if not logged in
}
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php include 'seo_meta.php'; ?>
  <title>PoolPal - Carpool Service</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="responsive.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  
  <!-- Places Autocomplete -->
  <link rel="stylesheet" href="css/places-autocomplete.css">
  <script>
    // Global flag to track script loading
    window.modernPlacesScriptLoaded = false;
  </script>
  <script src="js/modern-places-autocomplete.js"></script>
  
  <style>
    :root {
      --primary-color: #ffbf00;
      --primary-dark: #e6ac00;
      --primary-light: #ffe07a;
      --primary-bg: #fffaf0;
      --text-dark: #2a2a2a;
      --text-light: #6c6c6c;
      --white: #ffffff;
      --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
      --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
      --transition-fast: 0.2s ease;
      --transition-normal: 0.3s ease;
      --border-radius-sm: 8px;
      --border-radius-md: 12px;
      --border-radius-lg: 24px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--white);
      color: var(--text-dark);
      overflow-x: hidden;
    }

    .custom-header {
      background-color: var(--white);
      box-shadow: var(--shadow-sm);
      width: 100%;
      position: sticky;
      top: 0;
      z-index: 1000;
      transition: all var(--transition-normal);
    }

    .custom-header.scrolled {
      box-shadow: var(--shadow-md);
    }

    .custom-nav-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 18px 40px;
      max-width: 1400px;
      margin: 0 auto;
      position: relative;
    }

    .custom-logo {
      display: flex;
      align-items: center;
      gap: 40px;
    }

    .custom-logo-link {
      display: block;
      transition: transform var(--transition-fast);
    }

  

    .custom-logo-link img {
      height: 55px;
      width: auto;
      
     
    }

    .custom-menu-toggle {
      display: none;
      font-size: 22px;
      cursor: pointer;
      color: var(--text-dark);
      background: transparent;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      transition: background var(--transition-fast);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .custom-menu-toggle:hover {
      background-color: rgba(0, 0, 0, 0.05);
    }

    .custom-nav-links {
      list-style: none;
      display: flex;
      gap: 40px;
      margin: 0;
      padding: 0;
    }

    .custom-nav-links li {
      position: relative;
    }

    .custom-nav-links li a {
      text-decoration: none;
      color: var(--text-dark);
      font-size: 17px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 4px;
      position: relative;
      transition: all 0.3s ease;
      letter-spacing: 0.3px;
    }

    .custom-nav-links li a i {
      font-size: 18px;
      transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
      position: relative;
      top: 0;
    }

    /* Hover effect with scale and color change */
    .custom-nav-links li a:hover {
      color: var(--primary-color);
      transform: translateY(-2px);
    }

    .custom-nav-links li a:hover i {
      transform: scale(1.2) translateY(-2px);
      color: var(--primary-color);
    }

    /* Modern underline effect */
    .custom-nav-links li a::before {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 2px;
      background: linear-gradient(to right, var(--primary-color), var(--primary-light));
      transform: scaleX(0);
      transform-origin: right;
      transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 2px;
    }

    .custom-nav-links li a:hover::before {
      transform: scaleX(1);
      transform-origin: left;
    }

    /* Glow effect on hover */
    .custom-nav-links li a::after {
      content: '';
      position: absolute;
      width: 100%;
      height: 100%;
      background: var(--primary-color);
      border-radius: 30px;
      filter: blur(20px);
      opacity: 0;
      z-index: -1;
      transition: all 0.4s ease;
    }

    .custom-nav-links li a:hover::after {
      opacity: 0.15;
    }

    /* Active state for current page */
    .custom-nav-links li a.active {
      color: var(--primary-color);
    }

    .custom-nav-links li a.active::before {
      transform: scaleX(1);
    }

    /* Floating animation for icons */
    @keyframes floating {
      0% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
      100% { transform: translateY(0); }
    }

    .custom-nav-links li a:hover i {
      animation: floating 1s ease infinite;
    }

    /* Click effect */
    .custom-nav-links li a:active {
      transform: scale(0.95);
    }

    /* Enhanced mobile styles */
    @media (max-width: 1024px) {
      .custom-nav-links {
        gap: 30px;
      }

      .custom-nav-links li a {
        font-size: 16px;
      }

      .custom-nav-links li a i {
        font-size: 17px;
      }
    }

    @media (max-width: 768px) {
      .custom-nav-links {
        display: none;
      }

      /* Enhanced mobile menu items */
      .pp-mobile-menu-item {
        font-size: 16px;
        font-weight: 500;
        letter-spacing: 0.3px;
      }

      .pp-mobile-menu-item i {
        font-size: 18px;
      }

      /* Add floating animation to mobile menu icons */
      .pp-mobile-menu-item:hover i {
        animation: floating 1s ease infinite;
      }

      /* Enhanced hover effect for mobile items */
      .pp-mobile-menu-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: var(--primary-color);
        transform: scaleY(0);
        transition: transform 0.3s ease;
        border-radius: 0 4px 4px 0;
      }

      .pp-mobile-menu-item:hover::before {
        transform: scaleY(1);
      }
    }

    /* Add shimmer effect for desktop links */
    @keyframes shimmer {
      0% { background-position: -100% 0; }
      100% { background-position: 100% 0; }
    }

    .custom-nav-links li a::after {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      left: 0;
      background: linear-gradient(
        120deg,
        transparent 0%,
        rgba(255, 191, 0, 0.1) 25%,
        transparent 50%
      );
      background-size: 200% 100%;
      border-radius: 30px;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .custom-nav-links li a:hover::after {
      opacity: 1;
      animation: shimmer 1.5s infinite;
    }

    /* Add pulse effect for active link */
    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(255, 191, 0, 0.4); }
      70% { box-shadow: 0 0 0 10px rgba(255, 191, 0, 0); }
      100% { box-shadow: 0 0 0 0 rgba(255, 191, 0, 0); }
    }

    .custom-nav-links li a.active {
      animation: pulse 2s infinite;
    }

    .custom-user-actions {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .custom-post-ride-btn, .ride-taker-btn, .ride-provider-btn {
      padding: 10px 20px;
      background-color: var(--primary-color);
      color: var(--text-dark);
      font-size: 15px;
      border-radius: var(--border-radius-lg);
      text-decoration: none;
      font-weight: 600;
      transition: all var(--transition-normal);
      display: inline-block;
      box-shadow: 0 4px 12px rgba(255, 191, 0, 0.2);
      border: none;
    }

    .custom-post-ride-btn:hover, .ride-taker-btn:hover, .ride-provider-btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(255, 191, 0, 0.3);
    }

    .custom-profile-wrapper {
      position: relative;
    }

    .custom-profile-icon {
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform var(--transition-fast);
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background-color: var(--primary-color);
      box-shadow: 0 3px 8px rgba(255, 191, 0, 0.25);
    }

    .custom-profile-icon:hover {
      transform: scale(1.05);
      background-color: var(--primary-dark);
      box-shadow: 0 5px 12px rgba(255, 191, 0, 0.35);
    }

    .custom-profile-icon i {
      font-size: 22px;
      color: var(--text-dark);
    }

    .profile-pic {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--primary-color);
      box-shadow: 0 3px 8px rgba(255, 191, 0, 0.25);
      transition: all var(--transition-normal);
    }

    .profile-pic:hover {
      border-color: var(--primary-dark);
      transform: scale(1.05);
      box-shadow: 0 5px 12px rgba(255, 191, 0, 0.35);
    }

    .custom-dropdown {
      display: none;
      position: absolute;
      background-color: var(--white);
      border-radius: var(--border-radius-md);
      box-shadow: var(--shadow-md);
      padding: 8px 0;
      min-width: 200px;
      z-index: 999;
      top: 50px;
      right: 0;
      transform-origin: top right;
      transition: all var(--transition-normal);
      opacity: 0;
      transform: scale(0.95);
      border-top: 3px solid var(--primary-color);
    }

    .custom-dropdown.active {
      display: block;
      opacity: 1;
      transform: scale(1);
      animation: fadeInDown 0.3s forwards;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-10px) scale(0.95);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .custom-dropdown a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 20px;
      font-size: 14px;
      color: var(--text-dark);
      text-decoration: none;
      transition: all var(--transition-fast);
      font-weight: 500;
      position: relative;
    }

    .custom-dropdown a:hover {
      background-color: var(--primary-bg);
      color: var(--primary-color);
      padding-left: 25px;
    }

    .custom-dropdown a i {
      width: 20px;
      text-align: center;
      color: var(--primary-color);
      font-size: 15px;
      opacity: 0.8;
      transition: all var(--transition-fast);
    }

    .custom-dropdown a:hover i {
      opacity: 1;
      transform: translateX(2px);
    }

    .custom-dropdown a::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      height: 100%;
      width: 0;
      background-color: var(--primary-color);
      opacity: 0.1;
      transition: width var(--transition-normal);
    }

    .custom-dropdown a:hover::before {
      width: 3px;
      opacity: 1;
    }

    .custom-dropdown-wrapper {
      display: none;
      padding: 0;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.5s ease;
    }

    .custom-header.show-nav .custom-dropdown-wrapper {
      max-height: 500px;
      padding: 15px 20px;
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.05);
      border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    #custom-whatsapp-button {
      position: fixed;
      bottom: 25px;
      right: 25px;
      z-index: 9999;
      background-color: #25d366;
      border-radius: 50%;
      padding: 14px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      border: 2px solid rgba(255, 255, 255, 0.5);
    }

    #custom-whatsapp-button img {
      width: 30px;
      height: 30px;
      display: block;
      transition: transform 0.3s ease;
    }

    #custom-whatsapp-button:hover {
      transform: scale(1.1) rotate(10deg);
      background-color: #1ea952;
      box-shadow: 0 12px 30px rgba(37, 211, 102, 0.4);
    }

    #custom-whatsapp-button:hover img {
      transform: rotate(-10deg);
    }

    .custom-message-popup {
      position: fixed;
      bottom: 25px;
      right: 90px;
      background-color: var(--white);
      color: var(--text-dark);
      padding: 12px 20px;
      border-radius: var(--border-radius-lg);
      font-size: 14px;
      font-weight: 500;
      box-shadow: var(--shadow-md);
      opacity: 0;
      z-index: 9999;
      border-left: 4px solid var(--primary-color);
      animation: messageWave 10s infinite;
    }

    @keyframes messageWave {
      0% { opacity: 0; transform: translateY(20px) scale(0.9); }
      5% { opacity: 1; transform: translateY(0) scale(1); }
      15% { transform: translateY(-5px); }
      20% { transform: translateY(0); }
      80% { opacity: 1; transform: translateY(0); }
      100% { opacity: 0; transform: translateY(20px) scale(0.9); }
    }

    .mobile-actions {
      display: none;
      align-items: center;
      gap: 12px;
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
    }

    .mobile-profile {
      display: none;
    }

    /* Enhanced responsive styles */
    @media (min-width: 769px) and (max-width: 1024px) {
      .custom-nav-container {
        padding: 15px 20px;
      }
      
      .custom-nav-links {
        gap: 20px;
      }
      
      .custom-nav-links li a {
        font-size: 14px;
      }
      
      .custom-user-actions {
        gap: 15px;
      }
      
      .custom-post-ride-btn {
        padding: 8px 16px;
        font-size: 14px;
      }
    }

    @media (max-width: 768px) {
      .custom-nav-container {
        padding: 15px 20px;
      }
      
      .custom-nav-links,
      .custom-user-actions {
        display: none;
      }

      .custom-menu-toggle {
        display: flex;
      }

      .mobile-actions {
        display: flex;
      }

      .mobile-profile {
        display: block;
      }
      
      .custom-logo-link img {
        height: 45px;
      }
      
      .profile-pic {
        width: 34px;
        height: 34px;
      }

      .custom-dropdown-wrapper {
        display: flex;
        flex-direction: column;
        gap: 15px;
        border-radius: 0;
      }

      .custom-dropdown-wrapper a {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 15px;
        padding: 12px 10px;
        color: var(--text-dark);
        text-decoration: none;
        border-radius: var(--border-radius-sm);
        transition: all var(--transition-fast);
      }

      .custom-dropdown-wrapper a:hover {
        background-color: var(--primary-bg);
        color: var(--primary-color);
        transform: translateX(5px);
      }

      .custom-dropdown-wrapper .custom-post-ride-btn {
        width: fit-content;
        margin: 10px 0;
      }
      
      #custom-whatsapp-button {
        bottom: 20px;
        right: 20px;
        padding: 12px;
      }
      
      #custom-whatsapp-button img {
        width: 26px;
        height: 26px;
      }
      
      .custom-message-popup {
        bottom: 80px;
        right: 20px;
        font-size: 13px;
      }
    }
    
    @media (max-width: 480px) {
      .custom-nav-container {
        padding: 12px 15px;
      }
      
      .custom-logo-link img {
        height: 38px;
      }
      
      .profile-pic {
        width: 30px;
        height: 30px;
      }
      
      .custom-dropdown-wrapper a {
        font-size: 14px;
        padding: 10px;
      }
      
      .custom-post-ride-btn {
        padding: 7px 14px;
        font-size: 14px;
      }
      
      #custom-whatsapp-button {
        padding: 10px;
      }
      
      #custom-whatsapp-button img {
        width: 24px;
        height: 24px;
      }
    }

    /* Enhanced Mobile Menu Styles */
    .pp-mobile-menu {
      position: fixed;
      top: 0;
      left: -100%;
      width: 300px;
      height: 100vh;
      background: linear-gradient(135deg, var(--white) 0%, var(--primary-bg) 100%);
      padding: 25px;
      transition: 0.5s cubic-bezier(0.77, 0, 0.175, 1);
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.15);
      z-index: 1001;
      overflow-y: auto;
      border-right: 1px solid rgba(255, 191, 0, 0.1);
    }

    .pp-mobile-menu.active {
      left: 0;
      transform: translateX(0);
    }

    .pp-mobile-menu-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 35px;
      padding-bottom: 20px;
      border-bottom: 2px solid rgba(255, 191, 0, 0.15);
      position: relative;
    }

    .pp-mobile-menu-header::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 50px;
      height: 2px;
      background: var(--primary-color);
    }

    .pp-mobile-menu-close {
      background: var(--white);
      border: none;
      font-size: 20px;
      color: var(--text-dark);
      cursor: pointer;
      width: 40px;
      height: 40px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .pp-mobile-menu-close:hover {
      background-color: var(--primary-color);
      color: var(--white);
      transform: rotate(90deg);
    }

    .pp-mobile-menu-items {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .pp-mobile-menu-item {
      display: flex;
      align-items: center;
      padding: 15px 20px;
      color: var(--text-dark);
      text-decoration: none;
      border-radius: 12px;
      transition: all 0.3s ease;
      opacity: 0;
      transform: translateX(-20px);
      background: var(--white);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
      border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .pp-mobile-menu.active .pp-mobile-menu-item {
      opacity: 1;
      transform: translateX(0);
    }

    .pp-mobile-menu-item i {
      margin-right: 15px;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--primary-bg);
      border-radius: 8px;
      color: var(--primary-color);
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .pp-mobile-menu-item:hover {
      background: var(--primary-color);
      color: var(--white);
      transform: translateX(5px);
      box-shadow: 0 4px 15px rgba(255, 191, 0, 0.2);
    }

    .pp-mobile-menu-item:hover i {
      background: rgba(255, 255, 255, 0.2);
      color: var(--white);
    }

    .pp-mobile-menu-divider {
      height: 2px;
      background: linear-gradient(to right, rgba(255, 191, 0, 0.1), transparent);
      margin: 20px 0;
      border-radius: 2px;
    }

    .pp-mobile-menu-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.3);
      opacity: 0;
      visibility: hidden;
      transition: all 0.4s ease;
      z-index: 1000;
      backdrop-filter: blur(4px);
    }

    .pp-mobile-menu-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    /* Enhanced Hamburger Icon */
    .pp-hamburger {
      width: 45px;
      height: 45px;
      display: none;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border-radius: 14px;
      border: none;
      background: var(--white);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      padding: 0;
      position: relative;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      border: 1px solid rgba(0, 0, 0, 0.04);
    }

    .pp-hamburger:hover {
      background: var(--primary-bg);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 191, 0, 0.15);
    }

    .pp-hamburger-box {
      width: 22px;
      height: 16px;
      position: relative;
      display: flex;
      align-items: center;
    }

    .pp-hamburger-inner {
      width: 100%;
      height: 2px;
      background-color: var(--primary-color);
      border-radius: 4px;
      position: relative;
      transition: all 0.3s ease;
    }

    .pp-hamburger-inner::before,
    .pp-hamburger-inner::after {
      content: '';
      position: absolute;
      width: 100%;
      height: 2px;
      background-color: var(--primary-color);
      border-radius: 4px;
      transition: all 0.4s cubic-bezier(0.645, 0.045, 0.355, 1);
    }

    .pp-hamburger-inner::before {
      top: -6px;
      left: 0;
      width: 70%;
    }

    .pp-hamburger-inner::after {
      bottom: -6px;
      right: 0;
      width: 85%;
    }

    .pp-hamburger:hover .pp-hamburger-inner::before,
    .pp-hamburger:hover .pp-hamburger-inner::after {
      width: 100%;
    }

    .pp-hamburger.active {
      background: var(--primary-color);
    }

    .pp-hamburger.active .pp-hamburger-inner,
    .pp-hamburger.active .pp-hamburger-inner::before,
    .pp-hamburger.active .pp-hamburger-inner::after {
      background-color: var(--white);
    }

    .pp-hamburger.active .pp-hamburger-inner {
      background-color: transparent;
    }

    .pp-hamburger.active .pp-hamburger-inner::before {
      top: 0;
      transform: rotate(45deg);
      width: 100%;
    }

    .pp-hamburger.active .pp-hamburger-inner::after {
      bottom: 0;
      transform: rotate(-45deg);
      width: 100%;
      left: 0;
    }

    /* Animation delays for menu items */
    .pp-mobile-menu-item:nth-child(1) { transition-delay: 0.1s; }
    .pp-mobile-menu-item:nth-child(2) { transition-delay: 0.15s; }
    .pp-mobile-menu-item:nth-child(3) { transition-delay: 0.2s; }
    .pp-mobile-menu-item:nth-child(4) { transition-delay: 0.25s; }
    .pp-mobile-menu-item:nth-child(5) { transition-delay: 0.3s; }
    .pp-mobile-menu-item:nth-child(6) { transition-delay: 0.35s; }

    @media (max-width: 768px) {
      .pp-hamburger {
        display: flex;
      }

      .pp-mobile-menu {
        width: 85%;
        max-width: 360px;
      }
    }

    @media (max-width: 480px) {
      .pp-mobile-menu {
        width: 100%;
        max-width: none;
      }

      .pp-mobile-menu-item {
        padding: 12px 16px;
      }
    }

    /* Mobile Actions Styles */
    .mobile-actions {
      display: none;
      align-items: center;
      gap: 12px;
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
    }

    .pp-profile-button {
      width: 45px;
      height: 45px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--white);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      border: 1px solid rgba(0, 0, 0, 0.04);
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      padding: 0;
    }

    .pp-profile-button:hover {
      background: var(--primary-bg);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 191, 0, 0.15);
    }

    .pp-profile-button img {
      width: 32px;
      height: 32px;
      border-radius: 10px;
      object-fit: cover;
      border: 2px solid var(--primary-color);
      transition: all 0.3s ease;
    }

    .pp-profile-button:hover img {
      transform: scale(1.1);
    }

    .pp-profile-button i {
      font-size: 20px;
      color: var(--primary-color);
    }

    .pp-profile-dropdown {
      position: absolute;
      top: calc(100% + 10px);
      right: 0;
      background: var(--white);
      border-radius: 14px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      padding: 8px;
      min-width: 200px;
      opacity: 0;
      visibility: hidden;
      transform: translateY(10px);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid rgba(0, 0, 0, 0.04);
    }

    .pp-profile-dropdown.active {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .pp-profile-dropdown a {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      color: var(--text-dark);
      text-decoration: none;
      border-radius: 8px;
      transition: all 0.3s ease;
      gap: 12px;
    }

    .pp-profile-dropdown a:hover {
      background: var(--primary-bg);
      color: var(--primary-color);
    }

    .pp-profile-dropdown a i {
      font-size: 16px;
      color: var(--primary-color);
      width: 20px;
      text-align: center;
    }

    @media (max-width: 768px) {
      .mobile-actions {
        display: flex;
      }
    }
  </style>
</head>
<body>

<header class="custom-header" id="mainHeader">
  <div class="custom-nav-container">
    <div class="custom-logo">
      <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php'; ?>" class="custom-logo-link animate__animated animate__fadeIn">
        <img src="images/logo/logo1.png" alt="POOL PAL Logo">
      </a>
      <ul class="custom-nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li><a href="findrides.php"><i class="fas fa-car-side"></i> Find Rides</a></li>
          <li><a href="mytripsu.php"><i class="fas fa-route"></i> My Trips</a></li>
        <?php else: ?>
          <li><a href="findrides.php"><i class="fas fa-car-side"></i> Find Rides</a></li>
          <li><a href="login.php"><i class="fas fa-route"></i> My Trips</a></li>
        <?php endif; ?>
        <li><a href="about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
        <li><a href="contactus.php"><i class="fas fa-phone"></i> Contact Us</a></li>
      </ul>
    </div>

    <div class="custom-user-actions">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="selectvehicledriver.php" class="ride-provider-btn animate__animated animate__pulse">Ride Provider</a>
        <a href="selectvehicleuser.php" class="ride-taker-btn animate__animated animate__pulse">Ride Taker</a>
      <?php else: ?>
        <div class="custom-profile-wrapper">
        <div class="custom-profile-icon" onclick="toggleDropdown(event)">
            <?php if (isset($_SESSION['profile_photo']) && !empty($_SESSION['profile_photo'])): ?>
                <img src="<?php echo htmlspecialchars($_SESSION['profile_photo']); ?>" alt="Profile Picture" class="profile-pic">
            <?php else: ?>
                <i class="fas fa-user-circle"></i>
            <?php endif; ?>
        </div>
      <?php endif; ?>

      
        <div class="custom-dropdown" id="rsDropdown">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="mytripsu.php"><i class="fas fa-route"></i> My Trips</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="signup.php"><i class="fas fa-user-plus"></i> Sign Up</a>
            <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="mobile-actions">
      <?php if (isset($_SESSION['user_id'])): ?>
        <button class="pp-profile-button" id="ppProfileButton">
          <?php if (isset($_SESSION['profile_photo']) && !empty($_SESSION['profile_photo'])): ?>
            <img src="<?php echo htmlspecialchars($_SESSION['profile_photo']); ?>" alt="Profile Picture">
          <?php else: ?>
            <i class="fas fa-user-circle"></i>
          <?php endif; ?>
        </button>
        <div class="pp-profile-dropdown" id="ppProfileDropdown">
          <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
          <a href="mytripsu.php"><i class="fas fa-route"></i> My Trips</a>
          <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
          <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      <?php endif; ?>
      <button class="pp-hamburger" id="ppHamburger" aria-label="Open menu">
        <div class="pp-hamburger-box">
          <div class="pp-hamburger-inner"></div>
        </div>
      </button>
    </div>
  </div>

  <!-- Mobile Dropdown Menu -->
  <div class="custom-dropdown-wrapper">
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="findrides.php"><i class="fas fa-car-side"></i> Find Rides</a>
      <a href="mytripsu.php"><i class="fas fa-route"></i> My Trips</a>
      <a href="postride.php"><i class="fas fa-plus-circle"></i> Post a Ride</a>
    <?php else: ?>
      <a href="login.php" class="ride-taker-btn"><i class="fas fa-car-side"></i> Ride Taker</a>
      <a href="driver_login.php" class="ride-provider-btn"><i class="fas fa-plus-circle"></i> Ride Provider</a>
    <?php endif; ?>
    <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
    <a href="contactus.php"><i class="fas fa-phone"></i> Contact Us</a>
  </div>

  <a id="custom-whatsapp-button" href="https://wa.me/+919948434347" target="_blank" aria-label="Chat on WhatsApp" title="Chat with us on WhatsApp">
    <img src="https://img.icons8.com/color/48/000000/whatsapp--v1.png" alt="WhatsApp">
  </a>

  <div class="custom-message-popup">Have A Query? <span style="color: var(--text-dark); font-weight: 600;">Chat Now!</span></div>

  <!-- Add the new mobile menu -->
  <div class="pp-mobile-menu" id="ppMobileMenu">
    <div class="pp-mobile-menu-header">
      <img src="images/poolpal.jpg" alt="POOL PAL Logo" style="height: 40px;">
      <button class="pp-mobile-menu-close" id="ppMobileMenuClose" aria-label="Close menu">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="pp-mobile-menu-items">
      <a href="findrides.php" class="pp-mobile-menu-item">
        <i class="fas fa-car-side"></i>
        Find Rides
      </a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="mytripsu.php" class="pp-mobile-menu-item">
          <i class="fas fa-route"></i>
          My Trips
        </a>
      <?php else: ?>
        <a href="login.php" class="pp-mobile-menu-item">
          <i class="fas fa-route"></i>
          My Trips
        </a>
      <?php endif; ?>
      <a href="about.php" class="pp-mobile-menu-item">
        <i class="fas fa-info-circle"></i>
        About Us
      </a>
      <a href="contactus.php" class="pp-mobile-menu-item">
        <i class="fas fa-phone"></i>
        Contact Us
      </a>
      <div class="pp-mobile-menu-divider"></div>
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="selectvehicledriver.php" class="pp-mobile-menu-item">
          <i class="fas fa-car"></i>
          Ride Provider
        </a>
        <a href="selectvehicleuser.php" class="pp-mobile-menu-item">
          <i class="fas fa-user"></i>
          Ride Taker
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Add the overlay -->
  <div class="pp-mobile-menu-overlay" id="ppMobileMenuOverlay"></div>
</header>

<script>
  // Toggle dropdown function
  function toggleDropdown(event) {
    event.stopPropagation();

    // Close all open dropdowns first
    document.querySelectorAll(".custom-dropdown").forEach(d => d.classList.remove("active"));

    // Toggle only the clicked one
    const dropdown = event.currentTarget.nextElementSibling;
    dropdown.classList.toggle("active");
  }

  // Close dropdowns if clicking outside
  document.addEventListener("click", function (e) {
    document.querySelectorAll(".custom-dropdown").forEach(dropdown => {
      if (!dropdown.contains(e.target) && !e.target.closest(".custom-profile-wrapper")) {
        dropdown.classList.remove("active");
      }
    });
  });

  // Toggle mobile navigation
  function toggleMobileNav() {
    const header = document.getElementById("mainHeader");
    header.classList.toggle("show-nav");
    
    // Toggle the button icon between bars and times
    const toggleBtn = document.querySelector(".custom-menu-toggle i");
    if (toggleBtn.classList.contains("fa-bars")) {
      toggleBtn.classList.remove("fa-bars");
      toggleBtn.classList.add("fa-times");
    } else {
      toggleBtn.classList.remove("fa-times");
      toggleBtn.classList.add("fa-bars");
    }
  }
  
  // Header scroll effect
  window.addEventListener("scroll", function() {
    const header = document.getElementById("mainHeader");
    if (window.scrollY > 10) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  });
  
  // Add subtle entrance animations to menu items
  document.addEventListener("DOMContentLoaded", function() {
    const navLinks = document.querySelectorAll(".custom-nav-links li");
    navLinks.forEach((link, index) => {
      link.style.opacity = "0";
      link.style.transform = "translateY(10px)";
      setTimeout(() => {
        link.style.transition = "all 0.3s ease";
        link.style.opacity = "1";
        link.style.transform = "translateY(0)";
      }, 100 + (index * 100));
    });
  });

  // Add this to your existing script section
  document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('ppHamburger');
    const mobileMenu = document.getElementById('ppMobileMenu');
    const mobileMenuClose = document.getElementById('ppMobileMenuClose');
    const overlay = document.getElementById('ppMobileMenuOverlay');

    function toggleMobileMenu() {
        mobileMenu.classList.toggle('active');
        overlay.classList.toggle('active');
        hamburger.classList.toggle('active');
        document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
    }

    hamburger.addEventListener('click', toggleMobileMenu);
    mobileMenuClose.addEventListener('click', toggleMobileMenu);
    overlay.addEventListener('click', toggleMobileMenu);

    // Close mobile menu on window resize if it's open
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && mobileMenu.classList.contains('active')) {
            toggleMobileMenu();
        }
    });

    // Close mobile menu when clicking a menu item
    const menuItems = document.querySelectorAll('.pp-mobile-menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            if (mobileMenu.classList.contains('active')) {
                toggleMobileMenu();
            }
        });
    });

    // Profile dropdown functionality
    const profileButton = document.getElementById('ppProfileButton');
    const profileDropdown = document.getElementById('ppProfileDropdown');

    if (profileButton && profileDropdown) {
        profileButton.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileButton.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });

        // Close dropdown when scrolling
        window.addEventListener('scroll', function() {
            profileDropdown.classList.remove('active');
        });
    }
  });
</script>

</body>
</html>
