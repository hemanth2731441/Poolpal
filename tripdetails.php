<?php include 'nav.php';
include 'db.php';
// Remove the locations query since we're using Google Places API
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trip Details</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Add SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <!-- Include autocomplete styles and module -->
  <link rel="stylesheet" href="css/places-autocomplete.css">
  <!-- Load Google Maps API first -->
  <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : 'YOUR_GOOGLE_MAPS_API_KEY'; ?>&libraries=places" async></script>
  <!-- Load our custom Places utility -->
  <!-- <script src="js/places-autocomplete-v2.js"></script> -->
  
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<style>
    :root {
      --primary: #ffbf00;
      --primary-dark: #e6ac00;
      --primary-light: #fff6d9;
      --text-dark: #333;
      --text-medium: #666;
      --text-light: #888;
      --background: #fff;
      --surface: #f9f9f9;
      --border: #eaeaea;
      --shadow: rgba(0, 0, 0, 0.05);
      --shadow-darker: rgba(0, 0, 0, 0.1);
      --success: #4CAF50;
      --error: #f44336;
      --radius: 12px;
      --transition: all 0.3s ease;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #fff;
      margin: 0;
      padding: 0;
      color: var(--text-dark);
    }

    .trip-container {
      max-width: 800px;
      margin: 40px auto;
      padding: 0 20px;
      box-sizing: border-box;
      animation: fadeIn 0.6s ease-out;
    }

    .breadcrumb {
      font-size: 14px;
      color: var(--text-light);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
    }

    .breadcrumb span {
      color: var(--primary);
      font-weight: 500;
    }

    .breadcrumb i {
      font-size: 12px;
      margin: 0 8px;
      color: var(--text-light);
    }

    h2.trip-title {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 8px;
      position: relative;
      display: inline-block;
    }
    
    h2.trip-title::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 40px;
      height: 3px;
      background-color: var(--primary);
      border-radius: 2px;
    }

    .subheading {
      font-size: 14px;
      color: var(--text-light);
      margin-bottom: 30px;
      margin-top: 16px;
    }

    .form-section {
      background-color: var(--surface);
      border-radius: var(--radius);
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 8px 16px var(--shadow);
      transition: var(--transition);
    }

    .form-section:hover {
      box-shadow: 0 20px 40px var(--shadow-darker);
      transform: translateY(-5px);
    }

    .form-section {
      position: relative;
      overflow: hidden;
    }

    .form-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 191, 0, 0.05), transparent);
      transition: left 0.6s ease;
    }

    .form-section:hover::before {
      left: 100%;
    }

    .section-title {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 24px;
      color: var(--text-dark);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .section-title i {
      color: var(--primary);
      font-size: 20px;
    }

    .input-group {
      display: flex;
      gap: 20px;
      margin-bottom: 24px;
      flex-wrap: wrap;
    }

    .input-field {
      flex: 1;
      display: flex;
      flex-direction: column;
      min-width: 200px;
      position: relative;
    }

    .input-field label {
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 8px;
      color: var(--text-dark);
    }

    .input-field input, 
    .input-field select, 
    .input-field textarea {
      padding: 14px 16px 14px 42px;
      border-radius: var(--radius);
      border: 1px solid var(--border);
      background: var(--background);
      font-size: 14px;
      resize: none;
      width: 100%;
      box-sizing: border-box;
      transition: var(--transition);
    }

    .input-field input:focus, 
    .input-field select:focus, 
    .input-field textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(255, 191, 0, 0.1);
    }

    .input-field input::placeholder,
    .input-field textarea::placeholder {
      color: #ccc;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      top: 38px;
      color: var(--text-light);
      font-size: 16px;
      transition: var(--transition);
    }

    .input-field:focus-within .input-icon {
      color: var(--primary);
    }

    .autocomplete-loading {
      position: absolute;
      right: 14px;
      top: 38px;
      color: var(--primary);
      font-size: 14px;
      opacity: 0;
      visibility: hidden;
      transition: var(--transition);
    }

    .autocomplete-loading.show {
      opacity: 1;
      visibility: visible;
    }

    /* Enhanced Google Places Autocomplete Styling */
    .pac-container {
      border-radius: var(--radius);
      border: none;
      box-shadow: 0 15px 35px var(--shadow-darker);
      margin-top: 8px;
      background: var(--background);
      overflow: hidden;
      z-index: 9999;
      animation: slideInDown 0.3s ease-out;
    }

    @keyframes slideInDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .pac-item {
      border-bottom: 1px solid var(--border);
      padding: 16px 20px;
      cursor: pointer;
      transition: var(--transition);
      background: var(--background);
      position: relative;
      overflow: hidden;
    }

    .pac-item::before {
      content: '';
      position: absolute;
      left: -100%;
      top: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 191, 0, 0.1), transparent);
      transition: left 0.4s ease;
    }

    .pac-item:hover::before {
      left: 100%;
    }

    .pac-item:hover {
      background-color: var(--primary-light);
      transform: translateX(5px);
    }

    .pac-item-selected {
      background-color: var(--primary-light);
      transform: translateX(5px);
    }

    .pac-item:last-child {
      border-bottom: none;
    }

    .pac-icon {
      background-image: none !important;
      background: var(--primary);
      border-radius: 50%;
      width: 24px !important;
      height: 24px !important;
      margin-right: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .pac-icon::after {
      content: '\f3c5';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      color: white;
      font-size: 12px;
    }

    .pac-item-query {
      color: var(--text-dark);
      font-weight: 600;
      font-size: 14px;
    }

    .pac-matched {
      color: var(--primary);
      font-weight: 700;
    }

    .pac-secondary-text {
      color: var(--text-light);
      font-size: 12px;
      margin-top: 2px;
    }

    /* Enhanced professional styling */
    body {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .trip-container {
      background: var(--background);
      border-radius: 20px;
      box-shadow: 0 20px 60px var(--shadow-darker);
      margin: 20px auto;
      overflow: hidden;
      position: relative;
    }

    .trip-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    }

    .trip-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      padding: 40px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .trip-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
      animation: float 20s ease-in-out infinite;
    }

    .trip-header-content {
      position: relative;
      z-index: 2;
    }

    .trip-title {
      color: white !important;
      font-size: 32px !important;
      margin-bottom: 12px !important;
      text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    .trip-title::after {
      display: none;
    }

    .subheading {
      color: rgba(255,255,255,0.9) !important;
      font-size: 16px !important;
      margin-bottom: 0 !important;
    }

    .breadcrumb {
      padding: 20px 40px;
      background: rgba(255, 191, 0, 0.05);
      border-bottom: 1px solid var(--border);
    }

    .form-content {
      padding: 40px;
    }

    .toggle-section {
      background: var(--background);
      padding: 30px;
      border-radius: var(--radius);
      margin: 30px 0;
      box-shadow: 0 8px 16px var(--shadow);
      transition: var(--transition);
    }

    .toggle-section:hover {
      box-shadow: 0 12px 24px var(--shadow-darker);
      transform: translateY(-3px);
    }

    .toggle-section .section-title {
      margin-bottom: 30px;
    }

    .toggle-options-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }

    .toggle-option {
      background: var(--surface);
      border-radius: var(--radius);
      padding: 16px;
      display: flex;
      align-items: center;
      position: relative;
      transition: var(--transition);
    }

    .toggle-option:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px var(--shadow);
    }

    .toggle-option i {
      font-size: 18px;
      background: var(--primary-light);
      padding: 12px;
      border-radius: var(--radius);
      margin-right: 14px;
      color: var(--primary);
      min-width: 18px;
      text-align: center;
    }

    .toggle-option .text {
      flex-grow: 1;
    }

    .toggle-option .text strong {
      display: block;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 4px;
    }

    .toggle-option .text span {
      font-size: 13px;
      color: var(--text-light);
    }

    .switch {
      position: relative;
      display: inline-block;
      width: 44px;
      height: 24px;
      margin-left: auto;
    }

    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: #e0e0e0;
      border-radius: 34px;
      transition: var(--transition);
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      border-radius: 50%;
      transition: var(--transition);
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .switch input:checked + .slider {
      background-color: var(--primary);
    }

    .switch input:checked + .slider:before {
      transform: translateX(20px);
    }

    .toggle-option select {
      padding: 10px 16px;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      background: var(--background);
      font-size: 14px;
      outline: none;
      transition: var(--transition);
      width: 120px;
      appearance: none;
      background-image: url('data:image/svg+xml;charset=US-ASCII,<svg width="16" height="16" fill="gray" xmlns="http://www.w3.org/2000/svg"><path d="M4 6l4 4 4-4z"/></svg>');
      background-repeat: no-repeat;
      background-position: right 12px center;
      background-size: 16px 16px;
    }

    .toggle-option select:hover {
      border-color: var(--primary-dark);
    }

    .toggle-option select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(255, 191, 0, 0.1);
    }

    .textarea-field {
      margin-bottom: 30px;
    }

    .textarea-field textarea {
      min-height: 120px;
      padding: 16px;
    }

    .button-row {
      display: flex;
      justify-content: flex-end;
      gap: 16px;
      flex-wrap: wrap;
    }

    .cancel-btn, .post-btn {
      padding: 14px 28px;
      border-radius: var(--radius);
      font-size: 14px;
      font-weight: 600;
      border: none;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .cancel-btn {
      background: var(--surface);
      color: var(--text-medium);
    }

    .cancel-btn:hover {
      background: #f0f0f0;
    }

    .post-btn {
      background: var(--primary);
      color: #fff;
    }

    .post-btn:hover {
      background: var(--primary-dark);
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(255, 191, 0, 0.4);
    }

    .post-btn:active {
      transform: translateY(-1px);
    }

    .post-btn.loading {
      pointer-events: none;
      opacity: 0.8;
    }

    .post-btn.loading i {
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .success-animation {
      animation: successPulse 0.6s ease-out;
    }

    @keyframes successPulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }

    @keyframes fadeOut {
      from { opacity: 1; }
      to { opacity: 0.3; }
    }

    /* Enhanced Input Focus Effects */
    .input-field:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(255, 191, 0, 0.15);
      transform: translateY(-2px);
    }

    /* Loading overlay for entire page */
    .page-loading {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.9);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      opacity: 0;
      visibility: hidden;
      transition: var(--transition);
    }

    .page-loading.show {
      opacity: 1;
      visibility: visible;
    }

    .loading-content {
      text-align: center;
      color: var(--text-dark);
    }

    .loading-spinner {
      width: 60px;
      height: 60px;
      border: 4px solid var(--border);
      border-top: 4px solid var(--primary);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 20px;
    }

    .loading-text {
      font-size: 18px;
      font-weight: 600;
      color: var(--text-dark);
    }

    .error-border {
      border-color: var(--error) !important;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes highlightField {
      0% { background-color: rgba(255, 191, 0, 0); }
      50% { background-color: rgba(255, 191, 0, 0.1); }
      100% { background-color: rgba(255, 191, 0, 0); }
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(180deg); }
    }
    
    /* Responsive styles */
    @media (max-width: 768px) {
      .trip-container {
        margin: 30px auto;
      }
      
      .form-section, .toggle-section {
        padding: 24px;
      }
      
      .toggle-options-grid {
        grid-template-columns: 1fr;
      }
      
      h2.trip-title {
        font-size: 24px;
      }
    }
    
    @media (max-width: 480px) {
      .trip-container {
        margin: 20px auto;
        padding: 0 16px;
      }
      
      .form-section, .toggle-section {
        padding: 20px;
      }
      
      .input-group {
        flex-direction: column;
        gap: 16px;
      }
      
      .input-field {
        width: 100%;
      }
      
      .toggle-option {
        padding: 12px;
      }
      
      .button-row {
        justify-content: center;
        flex-direction: column-reverse;
        width: 100%;
      }
      
      .cancel-btn, .post-btn {
        width: 100%;
        justify-content: center;
      }
      
      h2.trip-title {
        font-size: 22px;
      }
    }

    /* Enhanced Mobile Responsive Styles */
    @media screen and (max-width: 768px) {
      .trip-container {
        margin: 0;
        padding: 0;
        border-radius: 0;
        box-shadow: none;
      }

      .trip-header {
        padding: 25px 20px;
        text-align: left;
      }

      .trip-title {
        font-size: 24px !important;
        margin-bottom: 8px !important;
      }

      .subheading {
        font-size: 14px !important;
      }

      .form-content {
        padding: 20px;
      }

      .form-section, 
      .toggle-section {
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 12px;
      }

      .section-title {
        font-size: 16px;
        margin-bottom: 20px;
      }

      .input-group {
        flex-direction: column;
        gap: 15px;
      }

      .input-field {
        width: 100%;
        min-width: unset;
      }

      .input-field input,
      .input-field select,
      .input-field textarea {
        padding: 12px 12px 12px 40px;
        font-size: 14px;
      }

      .toggle-options-grid {
        grid-template-columns: 1fr;
        gap: 15px;
      }

      .toggle-option {
        padding: 15px;
        flex-wrap: nowrap;
        align-items: center;
      }

      .toggle-option i {
        padding: 10px;
        font-size: 16px;
        min-width: 16px;
      }

      .toggle-option .text {
        margin: 0 10px;
      }

      .toggle-option .text strong {
        font-size: 14px;
      }

      .toggle-option .text span {
        font-size: 12px;
      }

      .button-row {
        flex-direction: column;
        gap: 12px;
        margin-top: 25px;
      }

      .cancel-btn,
      .post-btn {
        width: 100%;
        padding: 14px;
        justify-content: center;
        font-size: 15px;
      }

      /* Fix for date/time inputs on mobile */
      input[type="date"],
      input[type="time"] {
        -webkit-appearance: none;
        min-height: 45px;
      }

      /* Enhanced mobile animations */
      @keyframes mobileSlideIn {
        from {
          transform: translateY(20px);
          opacity: 0;
        }
        to {
          transform: translateY(0);
          opacity: 1;
        }
      }

      .form-section {
        animation: mobileSlideIn 0.3s ease-out forwards;
      }

      /* Fix for Google Places Autocomplete dropdown on mobile */
      .pac-container {
        width: calc(100% - 40px) !important;
        left: 20px !important;
        top: auto !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        border-radius: 12px !important;
        margin-top: 5px !important;
      }

      .pac-item {
        padding: 12px 15px !important;
      }

      /* Fix for SweetAlert on mobile */
      .swal2-popup {
        width: 85% !important;
        padding: 20px !important;
      }

      .swal2-title {
        font-size: 20px !important;
      }

      .swal2-content {
        font-size: 14px !important;
      }
    }

    /* Additional mobile optimizations for very small screens */
    @media screen and (max-width: 380px) {
      .trip-header {
        padding: 20px 15px;
      }

      .trip-title {
        font-size: 22px !important;
      }

      .form-content {
        padding: 15px;
      }

      .form-section,
      .toggle-section {
        padding: 15px;
        margin-bottom: 15px;
      }

      .input-field input,
      .input-field select,
      .input-field textarea {
        padding: 10px 10px 10px 35px;
        font-size: 13px;
      }

      .toggle-option {
        padding: 12px;
      }

      .toggle-option i {
        padding: 8px;
        font-size: 14px;
      }

      .toggle-option .text strong {
        font-size: 13px;
      }

      .toggle-option .text span {
        font-size: 11px;
      }

      /* Fix for small screen input icons */
      .input-icon {
        font-size: 14px;
        left: 12px;
      }
    }

    /* Fix for landscape mode */
    @media screen and (max-height: 600px) and (orientation: landscape) {
      .trip-container {
        margin: 0;
      }

      .trip-header {
        padding: 15px;
      }

      .form-section,
      .toggle-section {
        margin-bottom: 15px;
      }

      .input-group {
        flex-direction: row;
        flex-wrap: wrap;
      }

      .input-field {
        flex: 0 0 calc(50% - 10px);
      }

      .toggle-options-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    /* Fix for notched displays */
    @supports (padding-top: env(safe-area-inset-top)) {
      .trip-container {
        padding-top: env(safe-area-inset-top);
        padding-bottom: env(safe-area-inset-bottom);
      }
    }

    /* Add these styles for better visual feedback */
    .input-field input.success {
      border-color: var(--success) !important;
      transition: border-color 0.3s ease;
    }

    .input-field input.error-border {
      border-color: var(--error) !important;
      animation: shake 0.5s ease-in-out;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }

    /* Style for disabled inputs */
    .input-field input:disabled {
      background-color: #f5f5f5;
      cursor: wait;
    }

    /* Loading indicator styles */
    .autocomplete-loading {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--primary);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .autocomplete-loading.show {
      opacity: 1;
    }

    /* Location field specific styles */
    .location-field {
      position: relative;
    }

    .location-field input {
      padding-right: 40px !important;
    }

    .location-field .autocomplete-loading {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-25%);
      z-index: 5;
    }

    /* Google Places Autocomplete customization */
    .pac-container {
      border-radius: 12px;
      border: 1px solid var(--border);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      margin-top: 5px;
      font-family: 'Inter', sans-serif;
      z-index: 9999;
    }

    .pac-item {
      padding: 12px 15px;
      cursor: pointer;
      font-family: 'Inter', sans-serif;
    }

    .pac-item:hover {
      background-color: var(--primary-light);
    }

    .pac-item-selected {
      background-color: var(--primary-light);
    }

    .pac-icon {
      display: none;
    }

    .pac-item-query {
      font-size: 14px;
      color: var(--text-dark);
    }

    .pac-matched {
      color: var(--primary);
      font-weight: 600;
    }

    /* Loading and validation states */
    .input-field input:disabled {
      background-color: #f8f8f8;
      cursor: not-allowed;
    }

    .input-field input.success {
      border-color: var(--success) !important;
    }

    .input-field input.error {
      border-color: var(--error) !important;
    }

    .autocomplete-loading {
      display: none;
    }

    .autocomplete-loading.show {
      display: block;
    }

    /* Mobile optimization for autocomplete */
    @media (max-width: 768px) {
      .pac-container {
        width: calc(100% - 30px) !important;
        left: 15px !important;
        right: 15px !important;
      }
    }
  </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
  <!-- Loading Overlay -->
  <div class="page-loading" id="pageLoading">
    <div class="loading-content">
      <div class="loading-spinner"></div>
      <div class="loading-text">Posting your ride...</div>
    </div>
  </div>

  <div class="trip-container">
    <div class="breadcrumb">
      Dashboard <i class="fas fa-chevron-right"></i> Offer Ride <i class="fas fa-chevron-right"></i> <span>Trip Details</span>
    </div>
    
    <div class="trip-header">
      <div class="trip-header-content">
        <h2 class="trip-title">Create Your Trip</h2>
        <p class="subheading">Share your journey and connect with fellow travelers</p>
      </div>
    </div>
    
    <div class="form-content">
      <form id="tripForm">
      <div class="form-section">
        <div class="section-title">
          <i class="fas fa-map-marker-alt"></i>
          <span>Route Information</span>
        </div>
        
        <div class="input-group">
          <div class="input-field location-field">
            <label for="departure_city">Departure City</label>
            <i class="fas fa-location-dot input-icon"></i>
            <input type="text" 
                   name="departure_city" 
                   id="departure_city" 
                   placeholder="Type to search departure city..." 
                   autocomplete="off"
                   required>
            <input type="hidden" name="departure_lat" id="departure_lat">
            <input type="hidden" name="departure_lng" id="departure_lng">
            <div class="autocomplete-loading" id="departure-loading">
              <i class="fas fa-spinner fa-spin"></i>
            </div>
          </div>

          <div class="input-field location-field">
            <label for="destination_city">Destination City</label>
            <i class="fas fa-flag-checkered input-icon"></i>
            <input type="text" 
                   name="destination_city" 
                   id="destination_city" 
                   placeholder="Type to search destination city..." 
                   autocomplete="off"
                   required>
            <input type="hidden" name="destination_lat" id="destination_lat">
            <input type="hidden" name="destination_lng" id="destination_lng">
            <div class="autocomplete-loading" id="destination-loading">
              <i class="fas fa-spinner fa-spin"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="form-section">
        <div class="section-title">
          <i class="fas fa-clock"></i>
          <span>Timing Details</span>
        </div>
        
        <div class="input-group">
          <div class="input-field">
            <label>Departure Date</label>
            <i class="fas fa-calendar input-icon"></i>
            <input type="date" name="departure_date" id="departure_date" required>
          </div>
          
          <div class="input-field">
            <label>Departure Time</label>
            <i class="fas fa-hourglass-start input-icon"></i>
            <input type="time" name="departure_time" id="departure_time" required>
          </div>
        </div>

        <div class="input-group">
          <div class="input-field">
            <label>Arrival Date</label>
            <i class="fas fa-calendar input-icon"></i>
            <input type="date" name="arrival_date" id="arrival_date" required>
          </div>
          
          <div class="input-field">
            <label>Arrival Time</label>
            <i class="fas fa-hourglass-end input-icon"></i>
            <input type="time" name="arrival_time" id="arrival_time" required>
          </div>
        </div>

        <div class="input-group">
          <div class="input-field">
            <label>Duration</label>
            <i class="fas fa-hourglass-half input-icon"></i>
            <input type="text" name="duration" id="duration" placeholder="e.g., 2h 30m" readonly required>
          </div>
          
          <div class="input-field">
            <label>Distance</label>
            <i class="fas fa-road input-icon"></i>
            <input type="text" name="distance" placeholder="e.g., 150 km" required>
          </div>
        </div>
      </div>

      <div class="form-section">
        <div class="section-title">
          <i class="fas fa-car"></i>
          <span>Trip Details</span>
        </div>
        
        <div class="input-group">
          <div class="input-field">
            <label>Available Seats</label>
            <i class="fas fa-users input-icon"></i>
            <input type="number" name="seats" placeholder="Number of available seats" required>
          </div>
          
          <div class="input-field">
            <label>Price per Seat</label>
            <i class="fas fa-rupee-sign input-icon"></i>
            <input type="number" name="price" placeholder="Enter price in ₹" required>
          </div>
        </div>
      </div>

      <div class="toggle-section">
        <div class="section-title">
          <i class="fas fa-cog"></i>
          <span>Trip Preferences</span>
        </div>

        <div class="toggle-options-grid">
          <div class="toggle-option">
            <i class="fas fa-snowflake"></i>
            <div class="text">
              <strong>Air Conditioning</strong>
              <span>Indicate if your vehicle has AC</span>
            </div>
            <label class="switch">
              <input type="checkbox" name="has_ac" value="1">
              <span class="slider"></span>
            </label>
          </div>

          <div class="toggle-option">
            <i class="fas fa-smoking"></i>
            <div class="text">
              <strong>Allow Smoking</strong>
              <span>Permit smoking during the trip</span>
            </div>
            <label class="switch">
              <input type="checkbox" name="allow_smoking" value="1">
              <span class="slider"></span>
            </label>
          </div>

          <div class="toggle-option">
            <i class="fas fa-paw"></i>
            <div class="text">
              <strong>Pets Allowed</strong>
              <span>Allow passengers to bring pets</span>
            </div>
            <label class="switch">
              <input type="checkbox" name="pets_allowed" value="1">
              <span class="slider"></span>
            </label>
          </div>

          <div class="toggle-option">
            <i class="fas fa-suitcase"></i>
            <div class="text">
              <strong>Luggage Space</strong>
              <span>Select available luggage space</span>
            </div>
            <select name="luggage_space" required>
              <option value="">Select</option>
              <option value="Small">Small</option>
              <option value="Medium">Medium</option>
              <option value="Large">Large</option>
            </select>
          </div>
        </div>
      </div>

      <div class="form-section">
        <div class="section-title">
          <i class="fas fa-comment"></i>
          <span>Additional Information</span>
        </div>
        
        <div class="textarea-field input-field">
          <label>Additional Notes</label>
          <textarea rows="4" name="notes" placeholder="Any additional information for passengers"></textarea>
        </div>

        <div class="button-row">
          <button type="reset" class="cancel-btn">
            <i class="fas fa-times"></i>
            <span>Cancel</span>
          </button>
          <button type="button" class="post-btn" onclick="submitTrip()">
            <i class="fas fa-paper-plane"></i>
            <span>Post Ride</span>
          </button>
        </div>
      </div>
    </form>
    </div>
  </div>

  <script>
// Only load the script if ModernPlacesAutocomplete is not already defined
if (typeof ModernPlacesAutocomplete === 'undefined') {
    const script = document.createElement('script');
    script.src = "js/modern-places-autocomplete.js";
    document.body.appendChild(script);
}

// Function to initialize Modern Google Places Autocomplete
function initTripDetailsGoogleMapsCallback() {
    try {
        console.log('Initializing Modern Google Places Autocomplete for Trip Details');

        // Create instance of ModernGooglePlacesUtil
        const placesUtil = new ModernPlacesAutocomplete();

        // Initialize and setup autocomplete
        placesUtil.init().then(() => {
            placesUtil.createAutocomplete({
                fromInputId: 'departure_city',
                toInputId: 'destination_city',
                fromLatId: 'departure_lat',
                fromLngId: 'departure_lng',
                toLatId: 'destination_lat',
                toLngId: 'destination_lng'
            });
            console.log('Modern Google Places Autocomplete initialized successfully for Trip Details');
        }).catch(error => {
            console.error('Failed to initialize places autocomplete:', error);
        });
    } catch (error) {
        console.error('Error initializing Modern Google Places Autocomplete:', error);
    }
}

// Initialize when the DOM is ready
document.addEventListener('DOMContentLoaded', initTripDetailsGoogleMapsCallback);

// Remove any duplicate initialization attempts
document.removeEventListener('DOMContentLoaded', loadGoogleMapsScript);

function validateForm() {
  const form = document.getElementById('tripForm');
  const requiredFields = form.querySelectorAll('[required]');
  let isValid = true;
  let emptyFields = [];

  // Check all required fields
  requiredFields.forEach(field => {
    if (!field.value.trim()) {
      isValid = false;
      emptyFields.push(field.previousElementSibling.textContent);
      field.classList.add('error-border');
      
      // Add highlight animation
      field.style.animation = 'highlightField 1s ease';
      setTimeout(() => {
        field.style.animation = '';
      }, 1000);
    } else {
      field.classList.remove('error-border');
    }
  });

  // Check if locations are selected from dropdown
  const fromLat = document.getElementById('departure_lat').value;
  const fromLng = document.getElementById('departure_lng').value;
  const toLat = document.getElementById('destination_lat').value;
  const toLng = document.getElementById('destination_lng').value;

  if (!fromLat || !fromLng || !toLat || !toLng) {
    isValid = false;
    Swal.fire({
      title: 'Invalid Locations!',
      text: 'Please select locations from the dropdown suggestions',
      icon: 'error',
      confirmButtonColor: '#ffbf00',
      showClass: {
        popup: 'animate__animated animate__fadeInDown'
      },
      hideClass: {
        popup: 'animate__animated animate__fadeOutUp'
      }
    });
    return false;
  }

  // Check if departure and destination cities are different
  const departureCity = form.querySelector('[name="departure_city"]').value;
  const destinationCity = form.querySelector('[name="destination_city"]').value;
  if (departureCity === destinationCity && departureCity !== '') {
    isValid = false;
    Swal.fire({
      title: 'Invalid Route!',
      text: 'Departure and destination cities cannot be the same.',
      icon: 'error',
      confirmButtonColor: '#ffbf00',
      showClass: {
        popup: 'animate__animated animate__fadeInDown'
      },
      hideClass: {
        popup: 'animate__animated animate__fadeOutUp'
      }
    });
    return false;
  }

  // Check if departure date is not in the past
  const departureDate = new Date(form.querySelector('[name="departure_date"]').value);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  if (departureDate < today) {
    isValid = false;
    Swal.fire({
      title: 'Invalid Date!',
      text: 'Departure date cannot be in the past.',
      icon: 'error',
      confirmButtonColor: '#ffbf00',
      showClass: {
        popup: 'animate__animated animate__fadeInDown'
      },
      hideClass: {
        popup: 'animate__animated animate__fadeOutUp'
      }
    });
    return false;
  }
  
  // Check if arrival datetime is not before departure datetime
  const departureDateTime = new Date(`${form.querySelector('[name="departure_date"]').value}T${form.querySelector('[name="departure_time"]').value}`);
  const arrivalDateTime = new Date(`${form.querySelector('[name="arrival_date"]').value}T${form.querySelector('[name="arrival_time"]').value}`);
  
  if (arrivalDateTime <= departureDateTime) {
    isValid = false;
    Swal.fire({
      title: 'Invalid Times!',
      text: 'Arrival date and time must be after departure date and time.',
      icon: 'error',
      confirmButtonColor: '#ffbf00',
      showClass: {
        popup: 'animate__animated animate__fadeInDown'
      },
      hideClass: {
        popup: 'animate__animated animate__fadeOutUp'
      }
    });
    return false;
  }

  if (!isValid) {
    Swal.fire({
      title: 'Missing Information!',
      html: `Please fill in the following required fields:<br><br>${emptyFields.join('<br>')}`,
      icon: 'error',
      confirmButtonColor: '#ffbf00',
      showClass: {
        popup: 'animate__animated animate__fadeInDown'
      },
      hideClass: {
        popup: 'animate__animated animate__fadeOutUp'
      }
    });
    return false;
  }

  return true;
}

function calculateDuration() {
  const departureDate = document.getElementById('departure_date').value;
  const departureTime = document.getElementById('departure_time').value;
  const arrivalDate = document.getElementById('arrival_date').value;
  const arrivalTime = document.getElementById('arrival_time').value;
  const durationInput = document.getElementById('duration');
  
  // Only calculate if all fields have values
  if (departureDate && departureTime && arrivalDate && arrivalTime) {
    const departureDateTime = new Date(`${departureDate}T${departureTime}`);
    const arrivalDateTime = new Date(`${arrivalDate}T${arrivalTime}`);
    
    // Calculate difference in milliseconds
    const diff = arrivalDateTime - departureDateTime;
    
    if (diff < 0) {
      durationInput.value = "Invalid: arrival before departure";
      return;
    }
    
    // Convert to hours and minutes
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    
    durationInput.value = `${hours}h ${minutes}m`;
  }
}

// Add event listeners to date and time inputs
document.getElementById('departure_date').addEventListener('change', calculateDuration);
document.getElementById('departure_time').addEventListener('change', calculateDuration);
document.getElementById('arrival_date').addEventListener('change', calculateDuration);
document.getElementById('arrival_time').addEventListener('change', calculateDuration);

function submitTrip() {
  if (!validateForm()) {
    return;
  }
  
  // Recalculate duration before submitting to ensure it's current
  calculateDuration();

  const form = document.getElementById('tripForm');
  const formData = new FormData(form);
  const postBtn = document.querySelector('.post-btn');
  const pageLoading = document.getElementById('pageLoading');

  // Show enhanced loading state
  postBtn.classList.add('loading');
  postBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Processing...</span>';
  postBtn.disabled = true;
  
  // Show page loading overlay
  pageLoading.classList.add('show');

  // Add subtle animation to form
  form.style.opacity = '0.7';
  form.style.pointerEvents = 'none';

  fetch('submit_trip.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    // Hide loading overlay
    pageLoading.classList.remove('show');
    form.style.opacity = '1';
    form.style.pointerEvents = 'auto';
    
    if (data.toLowerCase().includes("success")) {
      // Success animation
      postBtn.classList.add('success-animation');
      
      Swal.fire({
        title: 'Ride Posted Successfully!',
        text: 'Your trip has been created and passengers can now book it.',
        icon: 'success',
        confirmButtonColor: '#ffbf00',
        confirmButtonText: 'Great!',
        showClass: {
          popup: 'animate__animated animate__bounceIn'
        },
        hideClass: {
          popup: 'animate__animated animate__fadeOutUp'
        }
      }).then(() => {
        // Clear form with animation
        form.style.animation = 'fadeOut 0.5s ease-out';
        setTimeout(() => {
          form.reset();
          form.style.animation = '';
          
          // Reset minimum dates
          const today = new Date().toISOString().split('T')[0];
          document.getElementById('departure_date').value = today;
        }, 500);
      });
      
      // Restore button state after delay
      setTimeout(() => {
        postBtn.classList.remove('loading', 'success-animation');
        postBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span>Post Ride</span>';
        postBtn.disabled = false;
      }, 2000);
    } else {
      // Error handling
      postBtn.classList.remove('loading');
      postBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>Try Again</span>';
      postBtn.style.background = 'var(--error)';
      
      Swal.fire({
        title: 'Oops! Something went wrong',
        text: data || 'Please check your information and try again.',
        icon: 'error',
        confirmButtonColor: '#ffbf00',
        showClass: {
          popup: 'animate__animated animate__shakeX'
        },
        hideClass: {
          popup: 'animate__animated animate__fadeOutUp'
        }
      });
      
      // Restore button state after delay
      setTimeout(() => {
        postBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span>Post Ride</span>';
        postBtn.style.background = '';
        postBtn.disabled = false;
      }, 3000);
    }
  })
  .catch(err => {
    // Network error handling
    pageLoading.classList.remove('show');
    form.style.opacity = '1';
    form.style.pointerEvents = 'auto';
    
    postBtn.classList.remove('loading');
    postBtn.innerHTML = '<i class="fas fa-wifi"></i><span>Connection Error</span>';
    postBtn.style.background = 'var(--error)';
    
    Swal.fire({
      title: 'Connection Error',
      text: 'Please check your internet connection and try again.',
      icon: 'error',
      confirmButtonColor: '#ffbf00',
      showClass: {
        popup: 'animate__animated animate__shakeX'
      },
      hideClass: {
        popup: 'animate__animated animate__fadeOutUp'
      }
    });
    
    console.error('Network error:', err);
    
    // Restore button state after delay
    setTimeout(() => {
      postBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span>Post Ride</span>';
      postBtn.style.background = '';
      postBtn.disabled = false;
    }, 3000);
  });
}

document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('departure_date').setAttribute('min', today);
    
    // Form validation
    const searchForm = document.getElementById('rideSearchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fromCity = document.getElementById('departure_city').value;
            const toCity = document.getElementById('destination_city').value;
            const fromLat = document.getElementById('departure_lat').value;
            const fromLng = document.getElementById('departure_lng').value;
            const toLat = document.getElementById('destination_lat').value;
            const toLng = document.getElementById('destination_lng').value;
            
            if (!fromCity || !toCity) {
                alert('Please select both departure and destination cities');
                return;
            }
            
            if (!fromLat || !fromLng || !toLat || !toLng) {
                alert('Please select valid locations from the dropdown suggestions');
                return;
            }
            
            this.submit();
        });
    }
});
</script>
<?php include 'footer.php';?>
</div></body>
</html>