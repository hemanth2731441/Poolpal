<?php
// Function to get the current page title
if (!function_exists('getPageTitle')) {
    function getPageTitle($page) {
        $titles = [
            'index' => 'PoolPal - Smart Carpooling for a Greener Commute',
            'book' => 'Book a Ride | PoolPal - Affordable & Eco-Friendly Carpooling',
            'profile' => 'My Profile | PoolPal - Manage Your Carpool Account',
            'dashboard' => 'Dashboard | PoolPal - Track Your Carpools & Rides',
            'findrides' => 'Find Rides Near You | PoolPal - Trusted Carpooling Across India',
            'mytrips' => 'My Trips | PoolPal - Your Ride History & Details',
            'login' => 'Login | PoolPal - Join the Smart Commute Revolution',
            'signup' => 'Sign Up | PoolPal - Create Your Carpool Account Today',
            'settings' => 'Account Settings | PoolPal - Personalize Your Ride Preferences',
            'default' => 'PoolPal - India\'s Trusted Carpooling Platform'
        ];
        return isset($titles[$page]) ? $titles[$page] : $titles['default'];
    }
}

// Function to get the current page description
if (!function_exists('getPageDescription')) {
    function getPageDescription($page) {
        $descriptions = [
            'index' => 'Join PoolPal for smart and eco-friendly carpooling across India. Save money, reduce traffic, and go green by sharing your ride.',
            'book' => 'Book affordable carpool rides instantly with PoolPal. Share rides with verified users and reduce your carbon footprint.',
            'profile' => 'View and update your PoolPal profile. Manage personal details, saved routes, and carpool preferences.',
            'dashboard' => 'Your PoolPal dashboard to manage carpools, track rides, view earnings, and monitor ride statistics.',
            'findrides' => 'Find and join carpool rides near you with PoolPal. Connect with commuters heading your way and save on daily travel.',
            'mytrips' => 'Access your carpool trip history. Review past rides and manage upcoming bookings.',
            'login' => 'Login to your PoolPal account to manage carpools, rides, payments, and more.',
            'signup' => 'Create your free PoolPal account. Join the carpooling movement and start sharing rides across India.',
            'settings' => 'Manage your carpool preferences, notification settings, and payment methods in PoolPal.',
            'default' => 'PoolPal is India\'s leading carpooling service, offering convenient and sustainable ride-sharing solutions from Hyderabad to across India.'
        ];
        return isset($descriptions[$page]) ? $descriptions[$page] : $descriptions['default'];
    }
}

// Get current page name and URL
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$page_title = getPageTitle($current_page);
$page_description = getPageDescription($current_page);
$canonical_url = 'https://poolpal.in/' . $current_page . '.php';
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<!-- Primary Meta Tags -->
<title><?php echo $page_title; ?></title>
<meta name="title" content="<?php echo $page_title; ?>">
<meta name="description" content="<?php echo $page_description; ?>">
<meta name="keywords" content="carpooling India, rideshare India, daily commute sharing, eco-friendly carpool, Hyderabad carpool, PoolPal, ride booking, carpool app India, carpool Hyderabad, carpool service">
<meta name="author" content="PoolPal">
<meta name="robots" content="index, follow">
<meta name="googlebot" content="index, follow">
<meta name="revisit-after" content="7 days">
<meta name="language" content="English">
<meta name="distribution" content="India">
<meta name="rating" content="General">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo $current_url; ?>">
<meta property="og:title" content="<?php echo $page_title; ?>">
<meta property="og:description" content="<?php echo $page_description; ?>">
<meta property="og:image" content="https://poolpal.in/banner/1.jpg">
<meta property="og:site_name" content="PoolPal">
<meta property="og:locale" content="en_IN">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="<?php echo $current_url; ?>">
<meta property="twitter:title" content="<?php echo $page_title; ?>">
<meta property="twitter:description" content="<?php echo $page_description; ?>">
<meta property="twitter:image" content="https://poolpal.in/banner/1.jpg">
<meta property="twitter:site" content="@poolpal">

<!-- Canonical URL -->
<link rel="canonical" href="<?php echo $canonical_url; ?>">

<!-- Favicon -->
<link rel="icon" type="image/png" href="images/favicon.jpg">
<link rel="apple-touch-icon" href="apple-touch-icon.png">

<!-- Additional SEO Meta Tags -->
<meta name="format-detection" content="telephone=no">
<meta name="theme-color" content="#ffbf00">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="PoolPal">

<!-- Structured Data for Local Business -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "PoolPal",
  "image": "https://poolpal.in/banner/1.jpg",
  "description": "Smart and affordable carpooling service based in Hyderabad, serving commuters across India.",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Your Office Address",
    "addressLocality": "Hyderabad",
    "addressRegion": "Telangana",
    "postalCode": "500001",
    "addressCountry": "India"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "17.385044",
    "longitude": "78.486671"
  },
  "url": "https://poolpal.in",
  "telephone": "+91-XXXXXXXXXX",
  "priceRange": "₹",
  "openingHoursSpecification": {
    "@type": "OpeningHoursSpecification",
    "dayOfWeek": [
      "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
    ],
    "opens": "09:00",
    "closes": "20:00"
  },
  "sameAs": [
    "https://www.facebook.com/poolpal",
    "https://twitter.com/poolpal",
    "https://www.instagram.com/poolpal"
  ]
}
</script>

<!-- Structured Data for Service -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Service",
  "serviceType": "Carpooling Service",
  "provider": {
    "@type": "LocalBusiness",
    "name": "PoolPal"
  },
  "areaServed": {
    "@type": "Country",
    "name": "India"
  },
  "hasOfferCatalog": {
    "@type": "OfferCatalog",
    "name": "Carpool Services",
    "itemListElement": [
      {
        "@type": "Offer",
        "itemOffered": {
          "@type": "Service",
          "name": "Daily Commute Carpool",
          "description": "Affordable carpooling for office and daily travel."
        }
      },
      {
        "@type": "Offer",
        "itemOffered": {
          "@type": "Service",
          "name": "Intercity Rideshare",
          "description": "Long-distance carpooling between cities in India."
        }
      },
      {
        "@type": "Offer",
        "itemOffered": {
          "@type": "Service",
          "name": "Weekend & Event Rides",
          "description": "Share rides for events, outings, and weekend getaways."
        }
      }
    ]
  }
}
</script>

<!-- Structured Data for BreadcrumbList -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "https://poolpal.in"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "<?php echo ucfirst($current_page); ?>",
      "item": "<?php echo $current_url; ?>"
    }
  ]
}
</script>

<!-- Structured Data for Organization -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "PoolPal",
  "url": "https://poolpal.in",
  "logo": "https://poolpal.in/images/poolpal.jpg",
  "contactPoint": {
    "@type": "ContactPoint",
    "telephone": "+91-XXXXXXXXXX",
    "contactType": "customer service",
    "availableLanguage": ["English", "Hindi", "Telugu", "Kannada", "Marathi", "Tamil" "malyalam"]
  }
}
</script>
