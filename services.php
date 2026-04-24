<?php
$page_title = 'Our Services — Taxi, Bus, Carpooling, Goods Transport, Hotels | PoolPal';
$page_description = 'Explore PoolPal services: taxi booking, goods transportation, carpooling (PoolPal Dosti), bus tickets, pilgrimage travel (Pool Yatra), and hotel booking. All-in-one travel app for India.';
$page_keywords = 'poolpal services, taxi booking india, goods transport, carpooling, bus ticket booking, pilgrimage travel, hotel booking, pool yatra, poolpal dosti, auto rickshaw';
$page_canonical = 'https://poolpal.in/services.php';
include 'nav.php';
?>

<!-- Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "PoolPal Services",
  "description": "All services offered by PoolPal — India's all-in-one mobility platform",
  "itemListElement": [
    {"@type": "ListItem", "position": 1, "name": "Taxi Booking", "description": "Book bikes, autos, and cars instantly for fast, safe, and affordable travel."},
    {"@type": "ListItem", "position": 2, "name": "Goods & Transportation", "description": "Reliable goods delivery with two-wheelers, tempos, mini trucks, and heavy trucks."},
    {"@type": "ListItem", "position": 3, "name": "PoolPal Dosti (Carpooling)", "description": "Share rides with co-travelers. Split costs, reduce traffic and emissions."},
    {"@type": "ListItem", "position": 4, "name": "Bus Ticket Booking", "description": "Book mini bus, semi-sleeper, sleeper, and AC luxury buses for group travel."},
    {"@type": "ListItem", "position": 5, "name": "Pool Yatra", "description": "Safe travel to pilgrimage sites with hotel bookings and local attractions."},
    {"@type": "ListItem", "position": 6, "name": "Hotel Booking", "description": "Find and book nearby hotels, homestays, and dharamshalas at the best prices."}
  ]
}
</script>

  <!-- ====== SERVICES HERO ====== -->
  <section class="svc-hero">
    <div class="container">
      <div class="svc-hero-content reveal">
        <div class="section-label"><i class="fas fa-concierge-bell"></i> Our Services</div>
        <h1 class="svc-hero-title">Your All-in-One App for<br/><span class="gradient-text">Smarter Travel & Mobility</span></h1>
        <p class="svc-hero-subtitle">Book rides, share trips, transport goods, plan pilgrimages & more — all from one powerful app.</p>
        <!-- Search Bar -->
        <div class="svc-search-wrap">
          <div class="svc-search-box">
            <i class="fas fa-search svc-search-icon"></i>
            <input type="text" id="svcSearch" class="svc-search-input" placeholder="Search services — taxi, bus, hotel, goods, carpooling..." autocomplete="off" />
            <button class="svc-search-clear" id="svcSearchClear" title="Clear search"><i class="fas fa-times"></i></button>
          </div>
          <p class="svc-search-hint" id="svcSearchHint"></p>
        </div>
      </div>
    </div>
  </section>

  <!-- ====== SERVICES GRID ====== -->
  <section class="svc-main section-padding">
    <div class="container">
      <div class="svc-grid">

        <!-- Taxi Booking -->
        <div class="svc-card reveal delay-1" data-service="taxi" data-keywords="taxi cab ride bike scooty auto car premium vehicle booking travel city commute pickup drop">
          <div class="svc-card-visual">
            <img src="images/Main Services/Taxi Services.png" alt="Taxi Booking" />
            <div class="svc-card-overlay"></div>
          </div>
          <div class="svc-card-body">
            <div class="svc-card-icon"><i class="fas fa-taxi"></i></div>
            <h3 class="svc-card-title">Taxi Booking</h3>
            <p class="svc-card-desc">Book bikes, autos, and cars instantly for fast, safe, and affordable travel — anytime, anywhere. Choose from 7 vehicle types including scooty, bike, 3-wheeler auto, non-AC car, AC car, premium car, and car XL.</p>
            <div class="svc-card-features">
              <span><i class="fas fa-bolt"></i> Instant Matching</span>
              <span><i class="fas fa-shield-alt"></i> Safe Rides</span>
              <span><i class="fas fa-wallet"></i> Multiple Payment Options</span>
            </div>
          </div>
        </div>

        <!-- Goods & Transportation -->
        <div class="svc-card reveal delay-2" data-service="goods" data-keywords="goods delivery transport parcel courier truck tempo mini logistics shipping freight cargo load">
          <div class="svc-card-visual">
            <img src="images/Main Services/Goods & Transportation.png" alt="Goods Transportation" />
            <div class="svc-card-overlay"></div>
          </div>
          <div class="svc-card-body">
            <div class="svc-card-icon"><i class="fas fa-truck"></i></div>
            <h3 class="svc-card-title">Goods & Transportation</h3>
            <p class="svc-card-desc">Reliable goods delivery with two-wheelers, tempos, mini trucks, and heavy trucks across locations. From a 20 kg parcel to 20-ton industrial shipments — we've got you covered with 11 vehicle categories.</p>
            <div class="svc-card-features">
              <span><i class="fas fa-box"></i> Parcels &amp; Packages</span>
              <span><i class="fas fa-industry"></i> Industrial Loads</span>
              <span><i class="fas fa-map-marker-alt"></i> Pan-India Coverage</span>
            </div>
          </div>
        </div>

        <!-- PoolPal Dosti (Carpooling) -->
        <div class="svc-card reveal delay-3" data-service="dosti" data-keywords="carpool rideshare share ride dosti pool friend co-traveler commute eco green split cost fuel save">
          <div class="svc-card-visual">
            <img src="images/Main Services/PoolPal Dosti.png" alt="PoolPal Dosti — Carpooling" />
            <div class="svc-card-overlay"></div>
          </div>
          <div class="svc-card-body">
            <div class="svc-card-icon"><i class="fas fa-car-side"></i></div>
            <h3 class="svc-card-title">PoolPal Dosti</h3>
            <p class="svc-card-desc">Share rides with friends and co-travelers heading your way. Split costs, reduce traffic & emissions. Our smart matching algorithm pairs you with verified co-riders on the same route for car, bike, and auto pooling.</p>
            <div class="svc-card-features">
              <span><i class="fas fa-user-check"></i> Verified Riders</span>
              <span><i class="fas fa-leaf"></i> Eco-Friendly</span>
              <span><i class="fas fa-hand-holding-usd"></i> Split Costs</span>
            </div>
          </div>
        </div>

        <!-- Bus Ticket Booking -->
        <div class="svc-card reveal delay-1" data-service="bus" data-keywords="bus ticket booking sleeper semi-sleeper ac luxury mini group travel long journey seats berth">
          <div class="svc-card-visual">
            <img src="images/Main Services/Bus Ticket Booking.png" alt="Bus Ticket Booking" />
            <div class="svc-card-overlay"></div>
          </div>
          <div class="svc-card-body">
            <div class="svc-card-icon"><i class="fas fa-bus-alt"></i></div>
            <h3 class="svc-card-title">Bus Ticket Booking</h3>
            <p class="svc-card-desc">Book mini bus, semi-sleeper, sleeper, and AC luxury buses for group travel and long journeys. Choose from 18-seater mini buses to premium 44-berth AC luxury sleepers for maximum comfort on the road.</p>
            <div class="svc-card-features">
              <span><i class="fas fa-couch"></i> Sleeper &amp; Semi-Sleeper</span>
              <span><i class="fas fa-snowflake"></i> AC Luxury Options</span>
              <span><i class="fas fa-users"></i> Group Travel</span>
            </div>
          </div>
        </div>

        <!-- Pool Yatra -->
        <div class="svc-card reveal delay-2" data-service="yatra" data-keywords="yatra pilgrimage temple tourism tour travel shrine holy darshan tourist attraction spiritual religious">
          <div class="svc-card-visual">
            <img src="images/Main Services/Pool Yatra.png" alt="Pool Yatra — Pilgrimage Travel" />
            <div class="svc-card-overlay"></div>
          </div>
          <div class="svc-card-body">
            <div class="svc-card-icon"><i class="fas fa-om"></i></div>
            <h3 class="svc-card-title">Pool Yatra</h3>
            <p class="svc-card-desc">Safe travel to pilgrimage sites with hotel bookings, tourist spots, and local attractions. A complete journey experience that combines spiritual travel with comfortable stays and guided local exploration.</p>
            <div class="svc-card-features">
              <span><i class="fas fa-gopuram"></i> Pilgrimage Sites</span>
              <span><i class="fas fa-camera"></i> Tourist Attractions</span>
              <span><i class="fas fa-map-signs"></i> Guided Exploration</span>
            </div>
          </div>
        </div>

        <!-- Hotel Booking -->
        <div class="svc-card reveal delay-3" data-service="hotel" data-keywords="hotel booking homestay dharamshala resort stay room accommodation lodge budget premium luxury">
          <div class="svc-card-visual">
            <img src="images/Main Services/Hotel Booking.png" alt="Hotel Booking" />
            <div class="svc-card-overlay"></div>
          </div>
          <div class="svc-card-body">
            <div class="svc-card-icon"><i class="fas fa-hotel"></i></div>
            <h3 class="svc-card-title">Hotel Booking</h3>
            <p class="svc-card-desc">Find and book nearby hotels, homestays, and dharamshalas at the best prices for your trips. Whether you need a budget room or a premium resort, we offer the best price guarantee across thousands of properties.</p>
            <div class="svc-card-features">
              <span><i class="fas fa-bed"></i> Hotels &amp; Resorts</span>
              <span><i class="fas fa-home"></i> Homestays</span>
              <span><i class="fas fa-tags"></i> Best Price Guarantee</span>
            </div>
          </div>
        </div>

      </div>
      <!-- No results message -->
      <div class="svc-no-results" id="svcNoResults" style="display:none;">
        <i class="fas fa-search"></i>
        <h3>No services found</h3>
        <p>Try a different search term like "taxi", "bus", "hotel", or "delivery".</p>
      </div>
    </div>
  </section>

  <!-- ====== SERVICE DETAILS (expanded panels) ====== -->
  <section class="svc-details section-padding">
    <div class="container">

      <!-- Taxi Detail -->
      <div class="svc-detail-panel reveal" id="detail-taxi" data-keywords="taxi cab ride bike scooty auto car premium vehicle booking">
        <div class="svc-detail-header">
          <div class="svc-detail-icon"><i class="fas fa-taxi"></i></div>
          <div>
            <h2 class="svc-detail-title">Taxi Booking</h2>
            <p class="svc-detail-subtitle">Choose from 7 vehicle types for every need</p>
          </div>
        </div>
        <div class="svc-detail-vehicles">
          <div class="svc-vehicle-chip"><i class="fas fa-motorcycle"></i> Scooty</div>
          <div class="svc-vehicle-chip"><i class="fas fa-motorcycle"></i> Bike</div>
          <div class="svc-vehicle-chip"><i class="fas fa-rickshaw"></i> 3-Wheeler Auto</div>
          <div class="svc-vehicle-chip"><i class="fas fa-car"></i> Non AC Car</div>
          <div class="svc-vehicle-chip"><i class="fas fa-car-side"></i> AC Car</div>
          <div class="svc-vehicle-chip"><i class="fas fa-car-alt"></i> Premium Car</div>
          <div class="svc-vehicle-chip"><i class="fas fa-shuttle-van"></i> Car XL</div>
        </div>
        <!-- Gallery -->
        <div class="svc-detail-gallery">
          <div class="svc-gallery-item"><img src="images/Services/City_Taxi_Ride.png" alt="City taxi ride" /><span>City Taxi Ride</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Two wheeler rides.png" alt="Two-wheeler rides" /><span>Two-Wheeler Rides</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Auto rikshaw.png" alt="Auto rickshaw" style="object-position: center 35%;" /><span>Auto Rickshaw</span></div>
          <div class="svc-gallery-item"><img src="images/Services/premium rides.png" alt="Premium rides" /><span>Premium Rides</span></div>
        </div>
        <div class="svc-detail-how">
          <h4><i class="fas fa-list-ol"></i> How It Works</h4>
          <div class="svc-steps">
            <div class="svc-step">
              <div class="svc-step-num">1</div>
              <div class="svc-step-info">
                <strong>Enter Location</strong>
                <span>Set your pickup & drop-off point</span>
              </div>
            </div>
            <div class="svc-step">
              <div class="svc-step-num">2</div>
              <div class="svc-step-info">
                <strong>Choose Vehicle</strong>
                <span>Pick from bikes, autos, or cars</span>
              </div>
            </div>
            <div class="svc-step">
              <div class="svc-step-num">3</div>
              <div class="svc-step-info">
                <strong>Get Matched</strong>
                <span>Our algorithm finds the nearest driver</span>
              </div>
            </div>
            <div class="svc-step">
              <div class="svc-step-num">4</div>
              <div class="svc-step-info">
                <strong>Ride & Pay</strong>
                <span>Track in real-time, pay securely</span>
              </div>
            </div>
          </div>
        </div>
        <div class="svc-detail-highlights">
          <span><i class="fas fa-check-circle"></i> GPS real-time tracking</span>
          <span><i class="fas fa-check-circle"></i> Verified drivers with ratings</span>
          <span><i class="fas fa-check-circle"></i> UPI, Card & Wallet payments</span>
          <span><i class="fas fa-check-circle"></i> 24/7 availability</span>
          <span><i class="fas fa-check-circle"></i> Fare estimates before booking</span>
          <span><i class="fas fa-check-circle"></i> SOS emergency button</span>
        </div>
      </div>

      <!-- Goods Detail -->
      <div class="svc-detail-panel reveal" id="detail-goods" data-keywords="goods delivery transport parcel courier truck tempo mini logistics">
        <div class="svc-detail-header">
          <div class="svc-detail-icon"><i class="fas fa-truck"></i></div>
          <div>
            <h2 class="svc-detail-title">Goods & Transportation</h2>
            <p class="svc-detail-subtitle">From small parcels to heavy industrial loads</p>
          </div>
        </div>
        <div class="svc-detail-grid">
          <div class="svc-detail-card">
            <i class="fas fa-motorcycle"></i>
            <strong>Two-Wheelers</strong>
            <span>Small parcels & urgent deliveries within city limits</span>
          </div>
          <div class="svc-detail-card">
            <i class="fas fa-truck-pickup"></i>
            <strong>Tempos</strong>
            <span>Medium-sized goods for vendors & local suppliers</span>
          </div>
          <div class="svc-detail-card">
            <i class="fas fa-truck-moving"></i>
            <strong>Mini Trucks</strong>
            <span>Bulky items over short to medium distances</span>
          </div>
          <div class="svc-detail-card">
            <i class="fas fa-truck"></i>
            <strong>Trucks</strong>
            <span>Heavy loads, agricultural & industrial shipments</span>
          </div>
        </div>
        <!-- Gallery -->
        <div class="svc-detail-gallery">
          <div class="svc-gallery-item"><img src="images/Services/Fast Delivery.png" alt="Fast delivery" /><span>Fast Delivery</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Heavy trucking.png" alt="Heavy trucking" /><span>Heavy Trucking</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Warehouse logistics.png" alt="Warehouse logistics" /><span>Warehouse Logistics</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Container shipping.png" alt="Container shipping" /><span>Container Shipping</span></div>
        </div>
        <div class="svc-detail-vehicles">
          <div class="svc-vehicle-chip">Parcel</div>
          <div class="svc-vehicle-chip">2W (20 kg)</div>
          <div class="svc-vehicle-chip">Mini 3W (50 kg)</div>
          <div class="svc-vehicle-chip">3W (500 kg)</div>
          <div class="svc-vehicle-chip">8 ft (1200 kg)</div>
          <div class="svc-vehicle-chip">9 ft (1700 kg)</div>
          <div class="svc-vehicle-chip">14 ft (3500 kg)</div>
          <div class="svc-vehicle-chip">17 ft (6000 kg)</div>
          <div class="svc-vehicle-chip">DCM (10 Tons)</div>
          <div class="svc-vehicle-chip">DCM (20 Tons)</div>
          <div class="svc-vehicle-chip">Containers</div>
        </div>
        <div class="svc-detail-highlights">
          <span><i class="fas fa-check-circle"></i> Real-time shipment tracking</span>
          <span><i class="fas fa-check-circle"></i> Insurance for high-value goods</span>
          <span><i class="fas fa-check-circle"></i> Same-day & scheduled delivery</span>
          <span><i class="fas fa-check-circle"></i> Transparent pricing</span>
        </div>
      </div>

      <!-- Dosti / Carpooling Detail -->
      <div class="svc-detail-panel reveal" id="detail-dosti" data-keywords="carpool rideshare share ride dosti pool friend co-traveler commute eco">
        <div class="svc-detail-header">
          <div class="svc-detail-icon"><i class="fas fa-car-side"></i></div>
          <div>
            <h2 class="svc-detail-title">PoolPal Dosti — Carpooling</h2>
            <p class="svc-detail-subtitle">Share rides, save money, build connections</p>
          </div>
        </div>
        <!-- Gallery -->
        <div class="svc-detail-gallery">
          <div class="svc-gallery-item"><img src="images/Services/Ride together.png" alt="Ride together" /><span>Ride Together</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Road trips.png" alt="Road trips" /><span>Road Trips</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Green commute.png" alt="Green commute" /><span>Green Commute</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Daily commuting.png" alt="Daily commuting" /><span>Daily Commuting</span></div>
        </div>
        <div class="svc-detail-how">
          <h4><i class="fas fa-list-ol"></i> In 4 Simple Steps</h4>
          <div class="svc-steps">
            <div class="svc-step">
              <div class="svc-step-num">1</div>
              <div class="svc-step-info">
                <strong>Enter Pickup & Drop</strong>
                <span>Tell us where you're going</span>
              </div>
            </div>
            <div class="svc-step">
              <div class="svc-step-num">2</div>
              <div class="svc-step-info">
                <strong>Get Matched Nearby</strong>
                <span>We find co-travelers on your route</span>
              </div>
            </div>
            <div class="svc-step">
              <div class="svc-step-num">3</div>
              <div class="svc-step-info">
                <strong>Track in Real-Time</strong>
                <span>Live GPS tracking for safety</span>
              </div>
            </div>
            <div class="svc-step">
              <div class="svc-step-num">4</div>
              <div class="svc-step-info">
                <strong>Pay Securely</strong>
                <span>Split costs via UPI, card, or wallet</span>
              </div>
            </div>
          </div>
        </div>
        <div class="svc-detail-highlights">
          <span><i class="fas fa-check-circle"></i> Verified co-riders</span>
          <span><i class="fas fa-check-circle"></i> Split fuel costs</span>
          <span><i class="fas fa-check-circle"></i> Eco-friendly commuting</span>
          <span><i class="fas fa-check-circle"></i> Car, Bike & Auto pooling</span>
          <span><i class="fas fa-check-circle"></i> Female-only ride option</span>
          <span><i class="fas fa-check-circle"></i> Route-match algorithm</span>
        </div>
      </div>

      <!-- Bus Detail -->
      <div class="svc-detail-panel reveal" id="detail-bus" data-keywords="bus ticket booking sleeper semi-sleeper ac luxury mini group travel">
        <div class="svc-detail-header">
          <div class="svc-detail-icon"><i class="fas fa-bus-alt"></i></div>
          <div>
            <h2 class="svc-detail-title">Bus Ticket Booking</h2>
            <p class="svc-detail-subtitle">Comfortable bus travel for groups & long trips</p>
          </div>
        </div>
        <div class="svc-detail-grid">
          <div class="svc-detail-card">
            <i class="fas fa-shuttle-van"></i>
            <strong>Mini Bus</strong>
            <span>18–24 seaters for small groups</span>
          </div>
          <div class="svc-detail-card">
            <i class="fas fa-bus"></i>
            <strong>Semi-Sleeper Bus</strong>
            <span>30–40 seats for comfortable travel</span>
          </div>
          <div class="svc-detail-card">
            <i class="fas fa-bus-alt"></i>
            <strong>Sleeper Bus</strong>
            <span>30–42 sleeper beds for long journeys</span>
          </div>
          <div class="svc-detail-card">
            <i class="fas fa-star"></i>
            <strong>AC Luxury Sleeper</strong>
            <span>36–44 berths with premium comfort</span>
          </div>
        </div>
        <!-- Gallery -->
        <div class="svc-detail-gallery">
          <div class="svc-gallery-item"><img src="images/Services/Comfortable travel.png" alt="Comfortable travel" /><span>Comfortable Travel</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Inter-city routes.png" alt="Inter-city routes" /><span>Inter-City Routes</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Group journeys.png" alt="Group journeys" /><span>Group Journeys</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Long distance travel.png" alt="Long distance travel" /><span>Long Distance Travel</span></div>
        </div>
        <div class="svc-detail-highlights">
          <span><i class="fas fa-check-circle"></i> Seat selection available</span>
          <span><i class="fas fa-check-circle"></i> Real-time bus tracking</span>
          <span><i class="fas fa-check-circle"></i> Free cancellation window</span>
          <span><i class="fas fa-check-circle"></i> Boarding point notifications</span>
        </div>
      </div>

      <!-- Yatra Detail -->
      <div class="svc-detail-panel reveal" id="detail-yatra" data-keywords="yatra pilgrimage temple tourism tour travel shrine holy darshan tourist">
        <div class="svc-detail-header">
          <div class="svc-detail-icon"><i class="fas fa-om"></i></div>
          <div>
            <h2 class="svc-detail-title">Pool Yatra</h2>
            <p class="svc-detail-subtitle">Pilgrimage + Tourism — a complete journey experience</p>
          </div>
        </div>
        <!-- Gallery -->
        <div class="svc-detail-gallery">
          <div class="svc-gallery-item"><img src="images/Services/Iconic Landmarks.png" alt="Iconic landmarks" /><span>Iconic Landmarks</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Sacred temples.png" alt="Sacred temples" /><span>Sacred Temples</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Heritage sites.png" alt="Heritage sites" /><span>Heritage Sites</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Hill pilgrimages.png" alt="Hill pilgrimages" /><span>Hill Pilgrimages</span></div>
        </div>
        <div class="svc-detail-highlights">
          <span><i class="fas fa-gopuram"></i> Pilgrimage sites</span>
          <span><i class="fas fa-camera"></i> Tourist places & temples</span>
          <span><i class="fas fa-hotel"></i> Hotel bookings included</span>
          <span><i class="fas fa-map-signs"></i> Local attractions & guides</span>
          <span><i class="fas fa-route"></i> Curated travel itineraries</span>
          <span><i class="fas fa-utensils"></i> Local food recommendations</span>
        </div>
      </div>

      <!-- Hotel Detail -->
      <div class="svc-detail-panel reveal" id="detail-hotel" data-keywords="hotel booking homestay dharamshala resort stay room accommodation lodge">
        <div class="svc-detail-header">
          <div class="svc-detail-icon"><i class="fas fa-hotel"></i></div>
          <div>
            <h2 class="svc-detail-title">Hotel Booking</h2>
            <p class="svc-detail-subtitle">Find the perfect stay for every budget</p>
          </div>
        </div>
        <div class="svc-detail-grid">
          <div class="svc-detail-card">
            <i class="fas fa-bed"></i>
            <strong>Hotels & Resorts</strong>
            <span>Premium comfort with modern amenities and room service</span>
          </div>
          <div class="svc-detail-card">
            <i class="fas fa-home"></i>
            <strong>Homestays</strong>
            <span>Authentic local experience with home-cooked meals</span>
          </div>
          <div class="svc-detail-card">
            <i class="fas fa-place-of-worship"></i>
            <strong>Dharamshalas</strong>
            <span>Affordable stays near pilgrimage and spiritual centers</span>
          </div>
          <div class="svc-detail-card">
            <i class="fas fa-mountain"></i>
            <strong>Lodges & Camps</strong>
            <span>Adventure stays in scenic hill stations and forests</span>
          </div>
        </div>
        <!-- Gallery -->
        <div class="svc-detail-gallery">
          <div class="svc-gallery-item"><img src="images/Services/Luxury stays.png" alt="Luxury stays" /><span>Luxury Stays</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Resort living.png" alt="Resort living" /><span>Resort Living</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Cozy rooms.png" alt="Cozy rooms" /><span>Cozy Rooms</span></div>
          <div class="svc-gallery-item"><img src="images/Services/Mountain retreats.png" alt="Mountain retreats" /><span>Mountain Retreats</span></div>
        </div>
        <div class="svc-detail-highlights">
          <span><i class="fas fa-check-circle"></i> Best price guarantee</span>
          <span><i class="fas fa-check-circle"></i> Free cancellation options</span>
          <span><i class="fas fa-check-circle"></i> Verified reviews & ratings</span>
          <span><i class="fas fa-check-circle"></i> Instant booking confirmation</span>
          <span><i class="fas fa-check-circle"></i> 24/7 customer support</span>
        </div>
      </div>

    </div>
  </section>

  <!-- ====== WHY POOLPAL SECTION ====== -->
  <section class="svc-why section-padding">
    <div class="container">
      <div class="section-label text-center reveal"><i class="fas fa-star"></i> Why Choose PoolPal</div>
      <h2 class="svc-why-title reveal">Everything You Need, One Platform</h2>
      <div class="svc-why-grid">
        <div class="svc-why-card reveal delay-1">
          <div class="svc-why-icon"><i class="fas fa-shield-alt"></i></div>
          <h4>Safe & Secure</h4>
          <p>Every ride is GPS-tracked with an SOS button. All drivers are background-verified for your peace of mind.</p>
        </div>
        <div class="svc-why-card reveal delay-2">
          <div class="svc-why-icon"><i class="fas fa-rupee-sign"></i></div>
          <h4>Transparent Pricing</h4>
          <p>No hidden charges. See fare estimates upfront before you book any ride, delivery, or hotel stay.</p>
        </div>
        <div class="svc-why-card reveal delay-3">
          <div class="svc-why-icon"><i class="fas fa-headset"></i></div>
          <h4>24/7 Support</h4>
          <p>Our customer support team is available around the clock via chat, call, or email for any assistance.</p>
        </div>
        <div class="svc-why-card reveal delay-1">
          <div class="svc-why-icon"><i class="fas fa-mobile-alt"></i></div>
          <h4>Easy to Use</h4>
          <p>Intuitive app design — book a ride, plan a trip, or reserve a hotel in under 60 seconds.</p>
        </div>
        <div class="svc-why-card reveal delay-2">
          <div class="svc-why-icon"><i class="fas fa-leaf"></i></div>
          <h4>Eco-Friendly</h4>
          <p>Our carpooling and route optimization reduce carbon emissions and traffic congestion in your city.</p>
        </div>
        <div class="svc-why-card reveal delay-3">
          <div class="svc-why-icon"><i class="fas fa-map-marked-alt"></i></div>
          <h4>Pan-India Network</h4>
          <p>Growing presence across cities, towns, and pilgrimage routes — with new locations added every week.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ====== CTA ====== -->
  <section class="cta-section section-padding">
    <div class="container">
      <div class="cta-card reveal-scale">
        <h2 class="cta-title">Ready to Experience PoolPal?</h2>
        <p class="cta-desc">Download the app now and unlock every service — from taxi rides to hotel stays.</p>
        <div class="cta-buttons">
          <a href="fpage.php#download" class="btn-dark">
            <i class="fas fa-download"></i> Get the App
          </a>
          <a href="aboutus.php" class="btn-outline-dark">
            <i class="fas fa-users"></i> Meet the Team
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Search & card interaction scripts -->
  <script>
  (function() {
    // === Search functionality ===
    const searchInput = document.getElementById('svcSearch');
    const searchClear = document.getElementById('svcSearchClear');
    const searchHint = document.getElementById('svcSearchHint');
    const noResults = document.getElementById('svcNoResults');
    const cards = document.querySelectorAll('.svc-card');
    const detailPanels = document.querySelectorAll('.svc-detail-panel');

    function filterServices() {
      const query = searchInput.value.trim().toLowerCase();
      searchClear.style.display = query ? 'flex' : 'none';

      if (!query) {
        cards.forEach(c => { c.style.display = ''; c.style.order = ''; });
        detailPanels.forEach(p => p.style.display = '');
        noResults.style.display = 'none';
        searchHint.textContent = '';
        return;
      }

      let visibleCount = 0;
      cards.forEach(card => {
        const text = (card.textContent + ' ' + (card.dataset.keywords || '')).toLowerCase();
        if (text.includes(query)) {
          card.style.display = '';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      detailPanels.forEach(panel => {
        const text = (panel.textContent + ' ' + (panel.dataset.keywords || '')).toLowerCase();
        panel.style.display = text.includes(query) ? '' : 'none';
      });

      noResults.style.display = visibleCount === 0 ? 'flex' : 'none';
      searchHint.textContent = visibleCount > 0 ? visibleCount + ' service' + (visibleCount > 1 ? 's' : '') + ' found' : '';
    }

    searchInput.addEventListener('input', filterServices);
    searchClear.addEventListener('click', function() {
      searchInput.value = '';
      filterServices();
      searchInput.focus();
    });

    // === Card click → scroll to detail ===
    cards.forEach(card => {
      card.addEventListener('click', function() {
        const id = 'detail-' + this.dataset.service;
        const panel = document.getElementById(id);
        if (panel && panel.style.display !== 'none') {
          panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
          panel.classList.add('svc-detail-flash');
          setTimeout(() => panel.classList.remove('svc-detail-flash'), 1200);
        }
      });
    });
  })();
  </script>

<?php include 'footer.php'; ?>
