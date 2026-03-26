# PoolPal — Business Requirements Document (BRD)

**Version:** 1.0  
**Date:** March 27, 2026  
**Project:** PoolPal — Ridesharing & Carpooling Platform  
**Domain:** poolpal.in  

---

## 1. Executive Summary

PoolPal is a web-based ridesharing and carpooling platform that connects **drivers** offering rides with **passengers** seeking affordable travel. The platform enables trip creation, ride discovery, online booking with payment processing, and comprehensive management for users, drivers, and administrators. It primarily targets the Indian market (INR currency, Indian timezone, Razorpay payments).

---

## 2. Business Objectives

| # | Objective |
|---|-----------|
| BO-1 | Provide an affordable, shared-travel marketplace for passengers and drivers |
| BO-2 | Enable drivers to monetize empty seats/vehicles across 8 vehicle types |
| BO-3 | Ensure secure, verified rides through admin-approved driver onboarding |
| BO-4 | Facilitate seamless digital payments via Razorpay (UPI, cards, wallets, net banking) |
| BO-5 | Build trust through profile verification, ratings, and transparent cancellation policies |

---

## 3. Stakeholders

| Role | Description |
|------|-------------|
| **Passenger (User)** | Searches rides, books seats, pays online, manages bookings |
| **Driver** | Creates trips, sets pricing, manages vehicle details, earns from rides |
| **Admin** | Verifies driver applications, manages users, processes refunds, views analytics |
| **System** | Automated email notifications, payment verification, booking lifecycle management |

---

## 4. User Roles & Permissions

### 4.1 Passenger (User)
- Register/login via email+password or Google OAuth
- Search available rides by origin, destination, and date
- Book seats and pay via Razorpay
- View upcoming/past trips
- Cancel bookings (with reason tracking)
- Manage profile (name, email, phone, photo)
- Reset password via OTP email
- Persistent login via "Remember Me" tokens (30-day)

### 4.2 Driver
- Register with vehicle and document details (license, Aadhar, RC)
- Requires **admin verification** before accessing the platform
- Create trips with departure/arrival locations, times, seats, pricing, and preferences
- Manage ride bookings from passengers
- View earnings and booking history
- Select from 8 vehicle types
- Reset password via OTP email

### 4.3 Administrator
- Login to dedicated admin panel
- Verify/reject driver applications (review documents)
- View and manage all users, drivers, bookings
- Process refunds via Razorpay
- Toggle driver account status (active/inactive)
- Manage homepage sliders/banners
- View contact messages (mark read/replied)
- Access cancellation statistics and analytics

---

## 5. Functional Requirements

### 5.1 Authentication & Account Management

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-AUTH-01 | User registration with full name, email, phone, password, optional profile photo | High |
| FR-AUTH-02 | User login with email + password (bcrypt hashed) | High |
| FR-AUTH-03 | Google OAuth login/registration with automatic profile photo download | High |
| FR-AUTH-04 | Driver registration with vehicle type, documents (license, Aadhar, RC) | High |
| FR-AUTH-05 | Driver login with admin verification check (status must be "accepted") | High |
| FR-AUTH-06 | Password recovery via 6-digit OTP sent over email (SMTP/Gmail) | High |
| FR-AUTH-07 | "Remember Me" persistent login with secure tokens (30-day expiry) | Medium |
| FR-AUTH-08 | Email and phone uniqueness enforcement | High |
| FR-AUTH-09 | Social account detection (prevent password login on OAuth-only accounts) | Medium |
| FR-AUTH-10 | Admin login to dedicated admin panel | High |

### 5.2 Trip Management

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-TRIP-01 | Drivers can create trip with departure city, destination city, date, time | High |
| FR-TRIP-02 | Set available seats and price per seat | High |
| FR-TRIP-03 | Set ride preferences: smoking allowed, pets allowed, luggage space, AC | Medium |
| FR-TRIP-04 | Select vehicle type from 8 options | High |
| FR-TRIP-05 | Trips must auto-decrement available seats when bookings are confirmed | High |
| FR-TRIP-06 | Drivers can cancel trips (records reason in cancelled_trips table) | Medium |

### 5.3 Ride Search & Discovery

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-SEARCH-01 | Search rides by origin, destination, and travel date | High |
| FR-SEARCH-02 | Google Places autocomplete for location input | High |
| FR-SEARCH-03 | Display matching trips with driver info, vehicle, price, available seats | High |
| FR-SEARCH-04 | Log search queries with GPS coordinates for analytics (ride_searches table) | Low |
| FR-SEARCH-05 | View detailed ride information before booking | High |

### 5.4 Booking & Payment

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-BOOK-01 | Users can select number of seats and submit booking | High |
| FR-BOOK-02 | Booking confirmation page with trip summary before payment | High |
| FR-BOOK-03 | Razorpay checkout modal integration for payment | High |
| FR-BOOK-04 | Support payment methods: UPI, credit/debit cards, net banking, wallets | High |
| FR-BOOK-05 | Server-side payment signature verification | High |
| FR-BOOK-06 | Booking status lifecycle: pending → completed / failed | High |
| FR-BOOK-07 | Payment records stored with Razorpay order ID, payment ID, signature | High |
| FR-BOOK-08 | Success/failure pages after payment processing | High |
| FR-BOOK-09 | Users can cancel bookings with predefined reasons | Medium |
| FR-BOOK-10 | Admin can process refunds via Razorpay API | Medium |

### 5.5 Profile Management

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-PROF-01 | Users can update name, email, phone, profile photo | High |
| FR-PROF-02 | Drivers can view/update vehicle details and documents | High |
| FR-PROF-03 | Driver verification status visible on profile | Medium |
| FR-PROF-04 | Profile photo upload with server-side storage | High |

### 5.6 Notifications

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-NOTIF-01 | Notification settings: trip updates, promotions, account activity, ride reminders, driver messages | Medium |
| FR-NOTIF-02 | Email notifications for bookings, cancellations, payment receipts, OTPs | High |
| FR-NOTIF-03 | Contact form forwarding to admin email | Medium |

### 5.7 Admin Panel

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-ADMIN-01 | Dashboard with booking statistics and user metrics | High |
| FR-ADMIN-02 | Driver verification workflow (review docs, approve/reject) | High |
| FR-ADMIN-03 | View and manage all active, completed, and cancelled bookings | High |
| FR-ADMIN-04 | User account management | Medium |
| FR-ADMIN-05 | Homepage slider/banner management (add/edit/delete) | Low |
| FR-ADMIN-06 | Contact message inbox with read/unread/replied status | Medium |
| FR-ADMIN-07 | Cancellation statistics and analytics | Medium |
| FR-ADMIN-08 | Refund processing via Razorpay | High |
| FR-ADMIN-09 | Manage ride requests from users | Medium |

### 5.8 Contact & Support

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-CONTACT-01 | Public contact form with name, email, phone, subject, message | Medium |
| FR-CONTACT-02 | Contact messages stored in DB with status tracking | Medium |
| FR-CONTACT-03 | Admin notification email on new contact submission | Low |

---

## 6. Vehicle Types Supported

| # | Vehicle Type | Use Case |
|---|-------------|----------|
| 1 | Car-Pooling | Shared car rides (cost splitting) |
| 2 | Car-Taxi | Premium/dedicated car rides |
| 3 | Bike | Two-wheeler rides |
| 4 | Auto Rickshaw | Short-distance urban rides |
| 5 | Goods-7ft | Small goods transport |
| 6 | Goods-8ft | Medium goods transport |
| 7 | Goods-3Wheeler | Three-wheeler goods transport |
| 8 | Goods-Tata407 | Large goods transport |

---

## 7. Technical Architecture

### 7.1 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 7.2+ (procedural, server-side rendered) |
| Database | MySQL 8.3.0 (MyISAM engine) |
| Frontend | HTML5, CSS3, JavaScript, Bootstrap, FontAwesome 6.4.0 |
| Server | Apache (XAMPP for local, Hostinger for production) |
| Email | PHPMailer 6.10 via Gmail SMTP (TLS, port 587) |
| Payments | Razorpay PHP SDK 2.9 |
| Maps | Google Maps/Places JavaScript API |
| Environment | vlucas/phpdotenv 5.6 |
| SMS (planned) | Twilio SDK (configured but not active) |

### 7.2 Database Schema (16 Tables)

| Table | Purpose |
|-------|---------|
| `users` | Passenger accounts (email, password, phone, profile photo, Google OAuth) |
| `drivers` | Driver accounts (vehicle type, documents, verification status) |
| `admins` | Admin accounts |
| `trips` | Driver-created trips (route, date, seats, price, preferences) |
| `bookings` | User bookings with Razorpay payment tracking |
| `payments` | Payment transaction records |
| `cancelled_bookings` | Cancelled booking records with reasons |
| `cancelled_trips` | Cancelled trip records with reasons |
| `password_resets` | User password reset OTPs |
| `driver_password_resets` | Driver password reset OTPs |
| `remember_tokens` | Persistent login tokens |
| `notifications` | Notification records |
| `notification_settings` | User notification preferences |
| `ride_requests` | User-initiated ride requests |
| `ride_searches` | Search analytics with GPS coordinates |
| `sliders` | Homepage banner/carousel management |
| `contact_messages` | Contact form submissions |

### 7.3 External Integrations

| Service | Purpose | Status |
|---------|---------|--------|
| Razorpay | Payment processing (INR) | Active (Live keys) |
| Google Maps/Places API | Location autocomplete, geocoding | Active |
| Gmail SMTP | Transactional emails (OTP, confirmations) | Active |
| Google OAuth | Social login/registration | Active |
| Twilio WhatsApp/SMS | User notifications | Configured, not active |

### 7.4 Key Application Flows

**Booking Flow:**
1. User searches rides (origin, destination, date)
2. Browse matching results with driver/vehicle info
3. Select ride → View details
4. Confirm booking (seats, special requests)
5. Proceed to checkout (Razorpay modal)
6. Payment verification (server-side signature check)
7. Booking confirmed → seats decremented → email confirmation

**Driver Onboarding:**
1. Driver registers with personal + vehicle + document info
2. Admin reviews application in admin panel
3. Admin approves/rejects driver
4. Approved drivers can log in and create trips

**Cancellation Flow:**
1. User/Driver initiates cancellation with reason
2. Record stored in cancelled_bookings/cancelled_trips
3. Admin reviews and processes refund via Razorpay API
4. Seats restored on the trip

---

## 8. Non-Functional Requirements

| ID | Requirement | Details |
|----|-------------|---------|
| NFR-01 | **Security** | bcrypt password hashing, prepared SQL statements, input sanitization, session management |
| NFR-02 | **Responsiveness** | Mobile-friendly design via responsive.css and Bootstrap |
| NFR-03 | **SEO** | Dynamic meta tags (seo_meta.php), XML sitemap, robots.txt |
| NFR-04 | **Timezone** | Asia/Kolkata (IST) for all date/time operations |
| NFR-05 | **Currency** | INR (Indian Rupee) for all payments |
| NFR-06 | **Availability** | Hosted on Hostinger (production), XAMPP-compatible for local dev |
| NFR-07 | **Data Integrity** | UNIQUE constraints on email/phone, foreign key references on bookings → trips |

---

## 9. Cancellation Reasons (Pre-defined)

**User Cancellation Reasons:**
- Schedule conflict
- Emergency
- Found alternative transport
- Change of plans
- Driver-related concern
- Other (free text)

**Driver Cancellation Reasons:**
- Vehicle breakdown
- Personal emergency
- Route change
- No passengers
- Other (free text)

---

## 10. Current Status & Known Gaps

| Area | Status |
|------|--------|
| Core booking flow | Complete and functional |
| Payment processing | Live Razorpay integration working |
| Driver verification | Fully operational via admin panel |
| Google OAuth | Functional |
| Email system | Working (PHPMailer + Gmail SMTP) |
| Ride matching algorithm | `algoritma.php` exists but is empty — not yet implemented |
| In-app notifications | Database infrastructure ready, UI not fully implemented |
| Twilio SMS/WhatsApp | Configured with placeholder credentials, not active |
| Security hardening | `security.php` is empty; some credentials in config.php should move to .env |
| Rating system | Not yet implemented |
| Real-time tracking | Not implemented |
| Mobile app | Not available (web-only) |

---

## 11. Future Enhancement Opportunities

1. **Ride Matching Algorithm** — Implement intelligent route-matching (distance, time, waypoints)
2. **Rating & Review System** — Let passengers rate drivers and vice versa
3. **In-App Notifications** — Complete the notification UI and delivery system
4. **SMS/WhatsApp Notifications** — Activate Twilio integration for real-time alerts
5. **Real-Time Tracking** — Live ride tracking via Google Maps
6. **Mobile Application** — Native or PWA mobile app
7. **Recurring Trips** — Let drivers schedule repeating commute trips
8. **Chat System** — In-app messaging between driver and passenger
9. **Fare Estimation** — Distance-based fare calculator using Google Distance Matrix API
10. **JSON-LD Schema** — Structured data for better SEO

---

## 12. Glossary

| Term | Definition |
|------|-----------|
| **Trip** | A ride offered by a driver with specific route, date, time, and pricing |
| **Booking** | A passenger's reservation of seats on a driver's trip |
| **Ride Request** | A passenger-initiated request for a ride (may not match existing trips) |
| **Verification** | Admin approval process for driver accounts before they can operate |
| **Razorpay Order** | A payment order created server-side before checkout |
| **OTP** | One-Time Password used for password recovery (6-digit, email-delivered) |

---

*This document serves as a reference for the PoolPal platform's business requirements, technical architecture, and current implementation status.*
