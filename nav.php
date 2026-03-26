<?php
ob_start();
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pool Pal</title>
  
  <!-- External Libraries -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet" />
  
  <!-- Internal CSS -->
  <style>
    :root {
      --primary: #ffbf00;
      --primary-dark: #e6ac00;
      --primary-light: #ffcc33;
      --secondary: #333;
      --text-dark: #333;
      --text-light: #777;
      --white: #ffffff;
      --off-white: #f9f9f9;
      --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.05);
      --shadow-strong: 0 8px 30px rgba(0, 0, 0, 0.12);
      --transition-fast: 0.2s;
      --transition-normal: 0.3s;
      --border-radius: 12px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      overflow-x: hidden;
      width: 100%;
      position: relative;
    }

    body {
      font-family: 'Montserrat', sans-serif;
      background-color: var(--white);
      color: var(--text-dark);
      line-height: 1.5;
    }

    .custom-header {
      background-color: var(--white);
      box-shadow: var(--shadow-soft);
      position: sticky;
      top: 0;
      width: 100%;
      z-index: 1000;
      transition: all var(--transition-normal) ease;
    }
    
    .custom-header.scrolled {
      box-shadow: var(--shadow-strong);
      background-color: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(10px);
    }

    .custom-nav-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1400px;
      margin: 0 auto;
      padding: 18px 30px;
      position: relative;
    }

    .custom-logo {
      display: flex;
      align-items: center;
      gap: 40px;
      z-index: 101;
    }

    .custom-logo-link {
      display: flex;
      align-items: center;
      text-decoration: none;
      transition: transform var(--transition-fast) ease;
    }
    
    .custom-logo-link:hover {
      transform: scale(1.05);
    }

    .custom-logo-link img {
      height: 50px;
      width: auto;
      border-radius: 10px;
      filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }

    /* Enhanced Nav Links Styles */
    .custom-nav-links {
      list-style: none;
      display: flex;
      gap: 40px;
      margin: 0;
      padding: 0;
    }

    .custom-nav-links li {
      position: relative;
      perspective: 1000px;
    }

    .custom-nav-links li a {
      text-decoration: none;
      color: var(--text-dark);
      font-size: 17px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 16px;
      position: relative;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 12px;
      letter-spacing: 0.3px;
      background: transparent;
    }

    .custom-nav-links li a i {
      font-size: 18px;
      color: var(--primary);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
    }

    /* Hover Effects and Animations */
    .custom-nav-links li a::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(120deg, 
        rgba(255, 191, 0, 0.1) 0%,
        rgba(255, 191, 0, 0.05) 100%);
      border-radius: 12px;
      opacity: 0;
      transition: all 0.4s ease;
      z-index: -1;
      transform: scale(0.8);
    }

    .custom-nav-links li a:hover::before {
      opacity: 1;
      transform: scale(1);
    }

    /* Modern underline effect */
    .custom-nav-links li a::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, var(--primary), var(--primary-light));
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      transform: translateX(-50%);
      border-radius: 4px;
    }

    .custom-nav-links li a:hover::after {
      width: calc(100% - 32px);
    }

    /* Hover state */
    .custom-nav-links li a:hover {
      color: var(--primary);
      transform: translateY(-2px);
    }

    .custom-nav-links li a:hover i {
      transform: translateY(-2px) scale(1.1);
      color: var(--primary-dark);
    }

    /* Active state */
    .custom-nav-links li a.active {
      color: var(--primary);
      background: linear-gradient(120deg, 
        rgba(255, 191, 0, 0.1) 0%,
        rgba(255, 191, 0, 0.05) 100%);
    }

    .custom-nav-links li a.active::after {
      width: calc(100% - 32px);
    }

    /* Floating animation for icons */
    @keyframes floating {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-4px); }
    }

    .custom-nav-links li a:hover i {
      animation: floating 1.5s ease infinite;
    }

    /* Shimmer effect */
    @keyframes shimmer {
      0% { background-position: -100% 0; }
      100% { background-position: 100% 0; }
    }

    .custom-nav-links li a::before {
      background: linear-gradient(
        120deg,
        transparent 0%,
        rgba(255, 191, 0, 0.1) 25%,
        transparent 50%,
        rgba(255, 191, 0, 0.1) 75%,
        transparent 100%
      );
      background-size: 200% 100%;
      animation: shimmer 3s infinite;
    }

    /* Click effect */
    .custom-nav-links li a:active {
      transform: scale(0.95);
    }

    /* User Actions Area */
    .custom-user-actions {
      display: flex;
      align-items: center;
      gap: 20px;
      z-index: 101;
    }

    .custom-post-ride-btn {
      padding: 10px 22px;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: var(--white);
      font-size: 15px;
      font-weight: 600;
      border-radius: 30px;
      text-decoration: none;
      transition: all var(--transition-normal) ease;
      box-shadow: 0 4px 15px rgba(255, 191, 0, 0.2);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    
    .custom-post-ride-btn i {
      font-size: 14px;
      transition: transform var(--transition-fast) ease;
    }

    .custom-post-ride-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(255, 191, 0, 0.3);
    }
    
    .custom-post-ride-btn:hover i {
      transform: translateX(4px);
    }

    .custom-profile-wrapper {
      position: relative;
    }

    .custom-profile-icon {
      cursor: pointer;
      transition: transform var(--transition-fast) ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .custom-profile-icon:hover {
      transform: scale(1.08);
    }

    .custom-profile-icon i {
      font-size: 36px;
      color: var(--text-dark);
    }

    .custom-profile-img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--primary-light);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Dropdown Menu */
    .custom-dropdown {
      display: none;
      position: absolute;
      top: calc(100% + 15px);
      right: 0;
      background-color: var(--white);
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-strong);
      padding: 8px 0;
      min-width: 220px;
      z-index: 1000;
      transform-origin: top right;
      overflow: hidden;
    }
    
    .custom-dropdown::before {
      content: '';
      position: absolute;
      top: -8px;
      right: 15px;
      width: 16px;
      height: 16px;
      background-color: var(--white);
      transform: rotate(45deg);
      box-shadow: var(--shadow-soft);
      z-index: -1;
    }

    .custom-dropdown.active {
      display: block;
      animation: dropdownEnter var(--transition-normal) forwards;
    }
    
    @keyframes dropdownEnter {
      from {
        opacity: 0;
        transform: translateY(10px) scale(0.95);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .custom-dropdown a {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 12px 20px;
      font-size: 14px;
      font-weight: 500;
      color: var(--text-dark);
      text-decoration: none;
      transition: all var(--transition-normal) ease;
      position: relative;
    }
    
    .custom-dropdown a i {
      font-size: 16px;
      color: var(--primary);
      width: 24px;
      text-align: center;
    }
    
    .custom-dropdown a:hover {
      background-color: rgba(255, 191, 0, 0.08);
      color: var(--primary-dark);
    }
    
    .custom-dropdown a::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 20px;
      width: calc(100% - 40px);
      height: 1px;
      background-color: rgba(0, 0, 0, 0.05);
    }
    
    .custom-dropdown a:last-child::after {
      display: none;
    }

    /* Mobile Navigation */
    .mobile-actions {
      display: none;
      align-items: center;
      gap: 15px;
      margin-left: auto;
    }

    .mobile-profile {
      display: none;
    }

    .custom-menu-toggle {
      display: none;
      font-size: 24px;
      cursor: pointer;
      color: var(--text-dark);
      transition: all var(--transition-fast) ease;
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
    }
    
    .custom-menu-toggle:hover {
      background-color: rgba(255, 191, 0, 0.08);
      color: var(--primary);
    }
    
    .custom-menu-toggle.active i::before {
      content: '\f00d';
    }

    .custom-dropdown-wrapper {
      display: none;
      position: absolute;
      top: 100%;
      left: 0;
      width: 100%;
      background-color: var(--white);
      box-shadow: var(--shadow-soft);
      z-index: 100;
      padding: 15px 0;
      transform: translateY(-10px);
      opacity: 0;
      visibility: hidden;
      transition: all var(--transition-normal) ease;
    }
    
    .custom-header.show-nav .custom-dropdown-wrapper {
      display: block;
      transform: translateY(0);
      opacity: 1;
      visibility: visible;
    }
    
    .custom-dropdown-wrapper .mobile-nav-links {
      list-style: none;
      display: flex;
      flex-direction: column;
      padding: 0;
      margin: 0;
    }
    
    .custom-dropdown-wrapper .mobile-nav-links li {
      padding: 0;
    }
    
    .custom-dropdown-wrapper .mobile-nav-links li a {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 15px 30px;
      font-size: 15px;
      font-weight: 500;
      color: var(--text-dark);
      text-decoration: none;
      transition: all var(--transition-normal) ease;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .custom-dropdown-wrapper .mobile-nav-links li a:hover {
      background-color: rgba(255, 191, 0, 0.08);
      color: var(--primary-dark);
      padding-left: 35px;
    }
    
    .custom-dropdown-wrapper .mobile-nav-links li a i {
      font-size: 18px;
      color: var(--primary);
      width: 24px;
      text-align: center;
    }
    
    .custom-dropdown-wrapper .mobile-actions-buttons {
      padding: 20px 30px 10px;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    
    .custom-dropdown-wrapper .custom-post-ride-btn {
      display: inline-flex;
      justify-content: center;
      padding: 12px 20px;
    }

    /* Enhanced Mobile Styles */
    @media (max-width: 991px) {
      .custom-dropdown-wrapper .mobile-nav-links {
        padding: 15px 0;
      }

      .custom-dropdown-wrapper .mobile-nav-links li a {
        font-size: 16px;
        font-weight: 600;
        padding: 15px 25px;
        border-radius: 0;
        background: transparent;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
      }

      .custom-dropdown-wrapper .mobile-nav-links li a:hover {
        background: linear-gradient(120deg, 
          rgba(255, 191, 0, 0.1) 0%,
          transparent 100%);
        border-left: 4px solid var(--primary);
        padding-left: 30px;
      }

      .custom-dropdown-wrapper .mobile-nav-links li a i {
        font-size: 18px;
        width: 24px;
        text-align: center;
      }

      /* Add slide-in animation for mobile menu items */
      @keyframes slideIn {
        from {
          opacity: 0;
          transform: translateX(-20px);
        }
        to {
          opacity: 1;
          transform: translateX(0);
        }
      }

      .custom-dropdown-wrapper .mobile-nav-links li {
        opacity: 0;
        animation: slideIn 0.3s forwards;
      }

      .custom-dropdown-wrapper .mobile-nav-links li:nth-child(1) { animation-delay: 0.1s; }
      .custom-dropdown-wrapper .mobile-nav-links li:nth-child(2) { animation-delay: 0.2s; }
      .custom-dropdown-wrapper .mobile-nav-links li:nth-child(3) { animation-delay: 0.3s; }
      .custom-dropdown-wrapper .mobile-nav-links li:nth-child(4) { animation-delay: 0.4s; }

      /* Enhanced mobile dropdown animation */
      .custom-dropdown-wrapper {
        transform: translateY(-10px);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      }

      .custom-header.show-nav .custom-dropdown-wrapper {
        transform: translateY(0);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      }
    }

    /* Tablet Optimization */
    @media (min-width: 768px) and (max-width: 991px) {
      .custom-nav-links {
        gap: 30px;
      }

      .custom-nav-links li a {
        font-size: 16px;
        padding: 8px 12px;
      }
    }

    /* Mobile Optimization */
    @media (max-width: 767px) {
      .custom-dropdown-wrapper .mobile-nav-links li a {
        padding: 14px 20px;
        font-size: 15px;
      }

      .custom-dropdown-wrapper .mobile-nav-links li a:hover {
        padding-left: 25px;
      }
    }

    /* Animation for Notifications */
    .notification-indicator {
      position: absolute;
      top: 0;
      right: 0;
      width: 10px;
      height: 10px;
      background-color: #f44336;
      border-radius: 50%;
      border: 2px solid var(--white);
    }
    
    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(244, 67, 54, 0.4); }
      70% { box-shadow: 0 0 0 10px rgba(244, 67, 54, 0); }
      100% { box-shadow: 0 0 0 0 rgba(244, 67, 54, 0); }
    }
    
    .pulse {
      animation: pulse 1.5s infinite;
    }
    
    /* Entrance Animation */
    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .custom-header {
      animation: fadeInDown 0.5s ease forwards;
    }
    
    /* Image hover effect */
    .img-hover-effect {
      transition: all var(--transition-normal) ease;
    }
    
    .img-hover-effect:hover {
      transform: scale(1.08);
      filter: brightness(1.05);
    }

    /* Stylish Hamburger Menu */
    .nav-hamburger {
      width: 50px;
      height: 50px;
      position: relative;
      display: none;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      background: var(--white);
      border-radius: 15px;
      border: none;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .nav-hamburger:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(255, 191, 0, 0.15);
      background: var(--primary-bg);
    }

    .hamburger-lines {
      width: 24px;
      height: 20px;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .hamburger-lines span {
      display: block;
      width: 100%;
      height: 2px;
      background: var(--text-dark);
      border-radius: 4px;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hamburger-lines span:nth-child(1) {
      width: 50%;
      transform-origin: left;
    }

    .hamburger-lines span:nth-child(3) {
      width: 75%;
      transform-origin: left;
    }

    .nav-hamburger:hover .hamburger-lines span {
      background: var(--primary);
    }

    .nav-hamburger:hover .hamburger-lines span:nth-child(1),
    .nav-hamburger:hover .hamburger-lines span:nth-child(3) {
      width: 100%;
    }

    .nav-hamburger.active .hamburger-lines span:nth-child(1) {
      transform: rotate(45deg) translate(2px, 9px);
      width: 100%;
      background: var(--primary);
    }

    .nav-hamburger.active .hamburger-lines span:nth-child(2) {
      transform: scaleX(0);
      opacity: 0;
    }

    .nav-hamburger.active .hamburger-lines span:nth-child(3) {
      transform: rotate(-45deg) translate(2px, -9px);
      width: 100%;
      background: var(--primary);
    }

    /* Enhanced Mobile Menu */
    .mobile-nav-wrapper {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100vh;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(8px);
      visibility: hidden;
      opacity: 0;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      z-index: 999;
    }

    .mobile-nav-wrapper.active {
      visibility: visible;
      opacity: 1;
    }

    .mobile-nav-content {
      position: fixed;
      top: 0;
      right: -100%;
      width: 300px;
      height: 100vh;
      background: var(--white);
      padding: 80px 30px 30px;
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
      overflow-y: auto;
    }

    .mobile-nav-wrapper.active .mobile-nav-content {
      right: 0;
    }

    .mobile-nav-header {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      padding: 20px 30px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      background: var(--white);
    }

    .mobile-nav-close {
      width: 40px;
      height: 40px;
      border: none;
      background: var(--primary-bg);
      border-radius: 12px;
      color: var(--primary);
      font-size: 18px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .mobile-nav-close:hover {
      background: var(--primary);
      color: var(--white);
      transform: rotate(90deg);
    }

    .mobile-nav-links {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .mobile-nav-links li {
      opacity: 0;
      transform: translateX(30px);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .mobile-nav-wrapper.active .mobile-nav-links li {
      opacity: 1;
      transform: translateX(0);
    }

    .mobile-nav-links li:nth-child(1) { transition-delay: 0.1s; }
    .mobile-nav-links li:nth-child(2) { transition-delay: 0.2s; }
    .mobile-nav-links li:nth-child(3) { transition-delay: 0.3s; }
    .mobile-nav-links li:nth-child(4) { transition-delay: 0.4s; }

    .mobile-nav-links li a {
      display: flex;
      align-items: center;
      padding: 15px 20px;
      color: var(--text-dark);
      text-decoration: none;
      font-size: 16px;
      font-weight: 500;
      border-radius: 12px;
      transition: all 0.3s ease;
      margin-bottom: 8px;
      background: var(--white);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
      border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .mobile-nav-links li a i {
      margin-right: 15px;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--primary-bg);
      border-radius: 8px;
      color: var(--primary);
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .mobile-nav-links li a:hover {
      background: var(--primary);
      color: var(--white);
      transform: translateX(5px);
    }

    .mobile-nav-links li a:hover i {
      background: rgba(255, 255, 255, 0.2);
      color: var(--white);
    }

    /* Enhanced Responsive Design */
    @media (max-width: 1200px) {
      .custom-nav-container {
        padding: 15px 20px;
      }

      .custom-nav-links {
        gap: 30px;
      }
    }

    @media (max-width: 991px) {
      .custom-nav-links, 
      .custom-user-actions {
        display: none;
      }

      .nav-hamburger {
        display: flex;
      }

      .custom-logo {
        gap: 20px;
      }
    }

    @media (max-width: 768px) {
      .custom-nav-container {
        padding: 12px 16px;
      }

      .custom-logo-link img {
        height: 40px;
      }

      .mobile-nav-content {
        width: 100%;
      }
    }

    @media (max-width: 480px) {
      .nav-hamburger {
        width: 45px;
        height: 45px;
      }

      .hamburger-lines {
        width: 20px;
        height: 16px;
      }

      .mobile-nav-links li a {
        padding: 12px 16px;
        font-size: 15px;
      }
    }

    /* Profile Button Styles */
    .nav-profile-btn {
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--white);
      border-radius: 15px;
      border: none;
      cursor: pointer;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      position: relative;
      padding: 0;
    }

    .nav-profile-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(255, 191, 0, 0.15);
      background: var(--primary-bg);
    }

    .nav-profile-btn img {
      width: 32px;
      height: 32px;
      border-radius: 10px;
      object-fit: cover;
      border: 2px solid var(--primary);
      transition: all 0.3s ease;
    }

    .nav-profile-btn i {
      font-size: 24px;
      color: var(--primary);
      transition: all 0.3s ease;
    }

    .nav-profile-btn:hover img {
      transform: scale(1.1);
    }

    .nav-profile-btn:hover i {
      transform: scale(1.1);
      color: var(--primary-dark);
    }

    /* Profile Dropdown Styles */
    .nav-profile-dropdown {
      position: absolute;
      top: calc(100% + 10px);
      right: 0;
      background: var(--white);
      border-radius: 15px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      padding: 8px;
      min-width: 220px;
      opacity: 0;
      visibility: hidden;
      transform: translateY(10px);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      z-index: 1000;
    }

    .nav-profile-dropdown.active {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .nav-profile-dropdown a {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      color: var(--text-dark);
      text-decoration: none;
      font-size: 15px;
      font-weight: 500;
      border-radius: 10px;
      transition: all 0.3s ease;
      gap: 12px;
    }

    .nav-profile-dropdown a i {
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--primary-bg);
      border-radius: 8px;
      color: var(--primary);
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .nav-profile-dropdown a:hover {
      background: var(--primary);
      color: var(--white);
      transform: translateX(5px);
    }

    .nav-profile-dropdown a:hover i {
      background: rgba(255, 255, 255, 0.2);
      color: var(--white);
    }

    @media (max-width: 991px) {
      .mobile-actions {
        display: flex;
      }
    }

    @media (max-width: 480px) {
      .nav-hamburger,
      .nav-profile-btn {
        width: 45px;
        height: 45px;
      }

      .nav-profile-btn img {
        width: 28px;
        height: 28px;
      }

      .nav-profile-btn i {
        font-size: 22px;
      }
    }
  </style>
</head>

<body>

<header class="custom-header" id="mainHeader">
  <div class="custom-nav-container">
    <div class="custom-logo">
      <a href="<?php echo isset($_SESSION['user_id']) ? 'driver_dasb.php' : 'index.php'; ?>" class="custom-logo-link img-hover-effect">
      <img src="images/logo/logo1.png" alt="POOL PAL Logo">
      </a>
      <ul class="custom-nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li><a href="mytrips.php"><i class="fas fa-route"></i> My Trips</a></li>
        <?php else: ?>
          <li><a href="login.php"><i class="fas fa-route"></i> My Trips</a></li>
        <?php endif; ?>
        <li><a href="aboutus.php"><i class="fas fa-info-circle"></i> About Us</a></li>
        <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
      </ul>
    </div>

    <div class="custom-user-actions">
      <a href="<?php echo isset($_SESSION['user_id']) ? 'tripdetails.php' : 'driver_login.php'; ?>" class="custom-post-ride-btn">
        <i class="fas fa-car-side"></i> Post a Ride <i class="fas fa-arrow-right"></i>
      </a>

      <div class="custom-profile-wrapper">
        <div class="custom-profile-icon" onclick="toggleDropdown(event)">
          <?php if (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic'])): ?>
            <img src="<?php echo $_SESSION['profile_pic']; ?>" alt="Profile" class="custom-profile-img">
          <?php else: ?>
            <i class="fas fa-user-circle"></i>
          <?php endif; ?>
        </div>
        <div class="custom-dropdown" id="rsDropdown">
          <?php if (isset($_SESSION['user_id'])): ?>
            <a href="driverprofile.php"><i class="fas fa-user"></i> My Profile</a>
            <a href="mytrips.php"><i class="fas fa-route"></i> My Trips</a>
            <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
            <a href="usersetting.php"><i class="fas fa-cog"></i> Settings</a>
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
        <div class="nav-profile-wrapper">
          <button class="nav-profile-btn" id="navProfileBtn">
            <?php if (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic'])): ?>
              <img src="<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>" alt="Profile Picture">
            <?php else: ?>
              <i class="fas fa-user-circle"></i>
            <?php endif; ?>
          </button>
          <div class="nav-profile-dropdown" id="navProfileDropdown">
            <a href="driverprofile.php"><i class="fas fa-user"></i> My Profile</a>
            <a href="mytrips.php"><i class="fas fa-route"></i> My Trips</a>
            <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
            <a href="usersetting.php"><i class="fas fa-cog"></i> Settings</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </div>
        </div>
      <?php endif; ?>
      <button class="nav-hamburger" id="navHamburger">
        <div class="hamburger-lines">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </button>
    </div>

    <!-- Add new mobile navigation -->
    <div class="mobile-nav-wrapper" id="mobileNav">
      <div class="mobile-nav-content">
        <div class="mobile-nav-header">
          <img src="images/poolpal.jpg" alt="POOL PAL Logo" style="height: 40px;">
          <button class="mobile-nav-close" id="mobileNavClose">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <ul class="mobile-nav-links">
          <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="mytrips.php"><i class="fas fa-route"></i> My Trips</a></li>
          <?php else: ?>
            <li><a href="login.php"><i class="fas fa-route"></i> My Trips</a></li>
          <?php endif; ?>
          <li><a href="aboutus.php"><i class="fas fa-info-circle"></i> About Us</a></li>
          <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
          <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="tripdetails.php"><i class="fas fa-car-side"></i> Post a Ride</a></li>
          <?php else: ?>
            <li><a href="driver_login.php"><i class="fas fa-car-side"></i> Post a Ride</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>

  <!-- Add new mobile navigation -->
  <div class="mobile-nav-wrapper" id="mobileNav">
    <div class="mobile-nav-content">
      <div class="mobile-nav-header">
        <img src="images/poolpal.jpg" alt="POOL PAL Logo" style="height: 40px;">
        <button class="mobile-nav-close" id="mobileNavClose">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <ul class="mobile-nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li><a href="mytrips.php"><i class="fas fa-route"></i> My Trips</a></li>
        <?php else: ?>
          <li><a href="login.php"><i class="fas fa-route"></i> My Trips</a></li>
        <?php endif; ?>
        <li><a href="aboutus.php"><i class="fas fa-info-circle"></i> About Us</a></li>
        <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
          <li><a href="tripdetails.php"><i class="fas fa-car-side"></i> Post a Ride</a></li>
        <?php else: ?>
          <li><a href="driver_login.php"><i class="fas fa-car-side"></i> Post a Ride</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</header>

<!-- Internal JS -->
<script>
  function toggleDropdown(event) {
    event.stopPropagation();
    document.querySelectorAll(".custom-dropdown").forEach(d => {
      d.classList.remove("active");
    });
    const dropdown = event.currentTarget.nextElementSibling;
    dropdown.classList.toggle("active");
  }

  document.addEventListener("click", function (e) {
    document.querySelectorAll(".custom-dropdown").forEach(dropdown => {
      if (!dropdown.contains(e.target) && !e.target.closest(".custom-profile-wrapper")) {
        dropdown.classList.remove("active");
      }
    });
  });

  function toggleMobileNav() {
    const header = document.getElementById("mainHeader");
    const menuToggle = document.getElementById("menuToggle");
    header.classList.toggle("show-nav");
    menuToggle.classList.toggle("active");
  }
  
  // Add scroll effect to header
  window.addEventListener("scroll", function() {
    const header = document.getElementById("mainHeader");
    if (window.scrollY > 10) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  });
  
  // Add touch support for dropdown on mobile
  document.querySelectorAll('.custom-profile-icon').forEach(icon => {
    icon.addEventListener('touchstart', function(e) {
      e.stopPropagation();
      const dropdown = this.nextElementSibling;
      document.querySelectorAll(".custom-dropdown").forEach(d => {
        if (d !== dropdown) d.classList.remove("active");
      });
      dropdown.classList.toggle("active");
    }, { passive: true });
  });

  document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('navHamburger');
    const mobileNav = document.getElementById('mobileNav');
    const mobileNavClose = document.getElementById('mobileNavClose');
    const body = document.body;

    function toggleMobileNav() {
        hamburger.classList.toggle('active');
        mobileNav.classList.toggle('active');
        body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : '';
    }

    hamburger.addEventListener('click', toggleMobileNav);
    mobileNavClose.addEventListener('click', toggleMobileNav);
    mobileNav.addEventListener('click', function(e) {
        if (e.target === mobileNav) {
            toggleMobileNav();
        }
    });

    // Close mobile nav on window resize if open
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991 && mobileNav.classList.contains('active')) {
            toggleMobileNav();
        }
    });

    // Close mobile nav when clicking a link
    document.querySelectorAll('.mobile-nav-links a').forEach(link => {
        link.addEventListener('click', toggleMobileNav);
    });

    // Profile dropdown functionality
    const profileBtn = document.getElementById('navProfileBtn');
    const profileDropdown = document.getElementById('navProfileDropdown');

    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });

        // Close dropdown when scrolling
        window.addEventListener('scroll', function() {
            profileDropdown.classList.remove('active');
        });

        // Close dropdown when opening mobile menu
        const hamburger = document.getElementById('navHamburger');
        hamburger.addEventListener('click', function() {
            profileDropdown.classList.remove('active');
        });
    }
  });
</script>

</body>
</html>
<?php
ob_end_flush();
?>
