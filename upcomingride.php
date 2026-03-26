<?php include 'nav.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trip Details & Rides</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #fff;
      margin: 0;
      padding: 0 20px;
      color: #111;
    }

    .trip-container {
      max-width: 720px;
      margin: 40px auto;
    }

    .breadcrumb {
      font-size: 14px;
      color: #888;
      margin-bottom: 20px;
    }

    .breadcrumb span {
      color: #000;
    }

    h2.trip-title {
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 24px;
    }

    .tab-buttons {
      display: flex;
      gap: 20px;
      border-bottom: 1px solid #ccc;
      margin-bottom: 10px;
    }

    .tab-buttons div {
      padding: 10px;
      cursor: pointer;
      font-weight: 500;
      color: #555;
    }

    .tab-buttons .active {
      color: #000;
      border-bottom: 2px solid #000;
    }

    .rides-container {
      display: none;
    }

    .rides-container.active {
      display: block;
    }

    .ride-section-title {
      font-size: 20px;
      font-weight: 600;
      margin: 20px 0;
    }

    .ride-entry {
      display: flex;
      align-items: center;
      gap: 20px;
      padding: 14px 0;
      border-bottom: 1px solid #eee;
    }

    .ride-entry i.fa-car {
      color: #000;
    }

    .ride-entry img {
      width: 36px;
      height: 36px;
      border-radius: 50%;
    }

    .ride-entry span {
      font-size: 14px;
    }

    .ride-entry .ride-text {
      flex-grow: 1;
    }

    .ride-entry .ride-text span {
      display: inline-block;
      margin-right: 16px;
      min-width: 90px;
    }

    .ride-entry .ride-actions {
      display: flex;
      gap: 10px;
    }

    .ride-entry .ride-actions i {
      cursor: pointer;
      color: #333;
    }

    .offer-button {
      display: flex;
      justify-content: center;
      margin-top: 20px;
    }

    .offer-button button {
      background-color: #ffbf00;
      color: white;
      padding: 8px 20px;
      font-size: 14px;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
    }

    .no-rides {
      text-align: center;
      font-size: 16px;
      color: #666;
      padding: 20px 0;
    }
  </style>
    <link rel="stylesheet" href="css/animated-bg.css" />
</head>
<body class="animated-background-wrapper">
<?php include_once 'includes/animated-background.php'; ?>
<div class="main-content">
  <div class="trip-container">
    <div class="breadcrumb">Dashboard > Offer Ride > <span>Trip Details</span></div>
    <h2 class="trip-title">Trip Details</h2>

    <div class="tab-buttons">
      <div onclick="showTab('upcoming')" id="tab-upcoming" class="active">Upcoming</div>
      <div onclick="showTab('past')" id="tab-past">Past</div>
      <div onclick="showTab('offered')" id="tab-offered">Offered</div>
    </div>

    <div class="rides-container active" id="upcoming">
      <h3 class="ride-section-title">Upcoming Rides</h3>
      <div class="ride-entry">
        <i class="fa fa-car"></i>
        <img src="https://i.imgur.com/ZcLLrkY.jpg">
        <div class="ride-text">
          <span>Tomorrow, 2:30 PM</span>
          <span>New York to Boston</span>
          <span>₹525</span>
          <span>Confirmed</span>
          <span>Sarah M.</span>
        </div>
        <div class="ride-actions">
          <i class="fa fa-comment"></i>
          <i class="fa fa-times"></i>
        </div>
      </div>
    </div>

    <div class="rides-container" id="past">
      <h3 class="ride-section-title">Past Rides</h3>
      <div class="ride-entry">
        <i class="fa fa-car"></i>
        <img src="https://i.imgur.com/OT1P68E.png">
        <div class="ride-text">
          <span>Dec 15, 2:30 PM</span>
          <span>New York to Boston</span>
          <span>₹295</span>
          <span>Completed</span>
          <span>Sarah M.</span>
        </div>
        <div class="ride-actions">
          <i class="fa fa-star"></i>
          <i class="fa fa-comment"></i>
        </div>
      </div>
    </div>

    <div class="rides-container" id="offered">
      <h3 class="ride-section-title">Offered Rides</h3>
      <div class="ride-entry">
        <i class="fa fa-car"></i>
        <img src="https://i.imgur.com/v0JXau2.png">
        <div class="ride-text">
          <span>Dec 20, 3:00 PM</span>
          <span>New York to Boston</span>
          <span>₹825</span>
          <span>2/4 seats</span>
        </div>
        <div class="ride-actions">
          <i class="fa fa-pen"></i>
          <i class="fa fa-trash"></i>
        </div>
      </div>
      <div class="ride-entry">
        <i class="fa fa-car"></i>
        <img src="https://i.imgur.com/BnJpV2C.png">
        <div class="ride-text">
          <span>Dec 22, 9:00 AM</span>
          <span>Boston to Providence</span>
          <span>₹618</span>
          <span>1/4 seats</span>
        </div>
        <div class="ride-actions">
          <i class="fa fa-pen"></i>
          <i class="fa fa-trash"></i>
        </div>
      </div>
      <div class="ride-entry">
        <i class="fa fa-car"></i>
        <img src="https://i.imgur.com/MK3eW3As.png">
        <div class="ride-text">
          <span>Dec 25, 11:30 AM</span>
          <span>Providence to New York</span>
          <span>₹130</span>
          <span>0/4 seats</span>
        </div>
        <div class="ride-actions">
          <i class="fa fa-pen"></i>
          <i class="fa fa-trash"></i>
        </div>
      </div>
      <div class="offer-button">
        <button>Offer Ride</button>
      </div>
    </div>
  </div>

  <script>
    function showTab(tabId) {
      document.querySelectorAll('.rides-container').forEach(tab => {
        tab.classList.remove('active');
      });
      document.querySelector(`#${tabId}`).classList.add('active');

      document.querySelectorAll('.tab-buttons div').forEach(btn => {
        btn.classList.remove('active');
      });
      document.querySelector(`#tab-${tabId}`).classList.add('active');
    }
  </script>
  <br><?php include  'footer.php';?>
</div></body>
</html>
