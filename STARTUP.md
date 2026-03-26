# PoolPal — Local Setup Guide

Step-by-step instructions to get PoolPal running on your local machine.

---

## Prerequisites

| Software | Version | Download |
|----------|---------|----------|
| XAMPP | 8.2+ (includes Apache, MySQL, PHP) | https://www.apachefriends.org/ |
| Composer | 2.x | https://getcomposer.org/ |
| Git | Any | https://git-scm.com/ |
| Web Browser | Chrome/Firefox/Edge (modern) | — |

---

## 1. Install XAMPP

1. Download and install XAMPP from https://www.apachefriends.org/
2. Default install path: `C:\xampp`
3. Ensure **Apache** and **MySQL** modules are selected during installation

---

## 2. Clone / Place the Project

Place the project in the XAMPP web root:

```
C:\xampp\htdocs\Poolpal\
```

If cloning from Git:
```bash
cd C:\xampp\htdocs
git clone <repository-url> Poolpal
```

> **Note:** The folder name must be `Poolpal` (case-sensitive on some systems). The app URL is configured as `http://localhost/Pool%20Pal` in config.php — you may need to update `APP_URL` in `config.php` if your folder name differs.

---

## 3. Install PHP Dependencies

Open a terminal in the project root and run:

```bash
cd C:\xampp\htdocs\Poolpal
composer install
```

This installs:
- **PHPMailer** (email sending)
- **Razorpay SDK** (payment processing)
- **phpdotenv** (environment variable management)
- **Twilio SDK** (SMS/WhatsApp — not actively used)

If Composer is not installed globally, you can use XAMPP's PHP:
```bash
C:\xampp\php\php.exe composer.phar install
```

---

## 4. Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Start **Apache** (click "Start" next to Apache)
3. Start **MySQL** (click "Start" next to MySQL)
4. Both should show green status indicators

---

## 5. Create the Database

### Option A: Via phpMyAdmin (Recommended)

1. Open http://localhost/phpmyadmin in your browser
2. Click **"New"** in the left sidebar to create a new database
3. Enter database name: `poolpal`
4. Select collation: `utf8mb4_general_ci`
5. Click **"Create"**
6. Select the `poolpal` database in the sidebar
7. Click the **"Import"** tab at the top
8. Click **"Choose File"** and select `C:\xampp\htdocs\Poolpal\poolpal.sql`
9. Click **"Go"** to import

### Option B: Via MySQL CLI

```bash
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE poolpal;"
C:\xampp\mysql\bin\mysql.exe -u root poolpal < C:\xampp\htdocs\Poolpal\poolpal.sql
```

This creates all 16+ tables with sample data including a default admin account.

---

## 6. Configure Database Connection

The database connection is defined in `config.php`. For local development, ensure these values match your XAMPP MySQL setup:

**Edit `config.php`** — update the bottom section:

```php
$servername = "localhost";
$username = "root";        // Default XAMPP MySQL user
$password = "";            // Default XAMPP MySQL password (empty)
$database = "poolpal";
```

Also check that `db.php` (or wherever `$host`, `$dbname`, `$user`, `$pass` are defined) uses the same local credentials. If `db.php` doesn't exist, create it:

```php
<?php
$host = "localhost";
$dbname = "poolpal";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

---

## 7. Configure Environment Variables

The `.env` file in the project root contains Razorpay payment credentials. For local development/testing, you can use Razorpay test keys:

```env
RAZORPAY_KEY_ID=rzp_test_XXXXXXXXXXXX
RAZORPAY_KEY_SECRET=XXXXXXXXXXXXXXXXXXXXXXXX
RAZORPAY_CURRENCY=INR
MERCHANT_NAME=PoolPal
MERCHANT_ID=your_merchant_id
MERCHANT_LOGO_URL=http://localhost/Poolpal/images/logo.png
MERCHANT_UPI_ID=your_upi_id
WEBHOOK_SECRET=whsec_poolpal
WEBHOOK_URL=http://localhost/Poolpal/webhook.php
```

> **Important:** For local testing, use Razorpay **test mode** keys (prefix `rzp_test_`). Get them from https://dashboard.razorpay.com/

---

## 8. Update Application URL

In `config.php`, update the `APP_URL` to match your local setup:

```php
define('APP_URL', 'http://localhost/Poolpal');
```

---

## 9. Create Upload Directories

Ensure the `uploads/` directory exists and is writable:

```bash
mkdir -p C:\xampp\htdocs\Poolpal\uploads\profiles
mkdir -p C:\xampp\htdocs\Poolpal\uploads\drivers
mkdir -p C:\xampp\htdocs\Poolpal\uploads\banners
```

On Windows, these directories should be writable by default under XAMPP.

---

## 10. Access the Application

Open your browser and navigate to:

| Page | URL |
|------|-----|
| **Homepage** | http://localhost/Poolpal/fpage.php |
| **User Login** | http://localhost/Poolpal/login.php |
| **User Signup** | http://localhost/Poolpal/signup.php |
| **Driver Login** | http://localhost/Poolpal/driver_login.php |
| **Driver Signup** | http://localhost/Poolpal/driver_signup.php |
| **Admin Panel** | http://localhost/Poolpal/admin/admin_login.php |
| **phpMyAdmin** | http://localhost/phpmyadmin |

---

## 11. Default Accounts

The SQL dump includes a default admin account:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | *(bcrypt hashed — reset via phpMyAdmin if needed)* |

To create a new admin password, generate a bcrypt hash:
```php
php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"
```
Then update the `admins` table in phpMyAdmin with the generated hash.

---

## 12. Email Configuration (Optional for Local)

Email features (OTP, booking confirmations) require valid SMTP credentials in `config.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');  // Gmail App Password
```

To generate a Gmail App Password:
1. Go to https://myaccount.google.com/security
2. Enable 2-Step Verification
3. Go to App Passwords → Generate a new one for "Mail"
4. Use the 16-character password in the config

> If you skip this step, the app will work but email features (password reset, notifications) won't send.

---

## 13. Google Maps API (Optional for Local)

Location autocomplete uses the Google Maps/Places API. The key is embedded in the frontend files. For local development:

1. Get an API key from https://console.cloud.google.com/
2. Enable: Maps JavaScript API, Places API
3. Replace the API key in the relevant PHP/JS files

> The app will work without this, but location autocomplete fields won't function.

---

## Troubleshooting

| Issue | Solution |
|-------|---------|
| **"Connection failed" error** | Check MySQL is running in XAMPP; verify credentials in config.php/db.php |
| **Blank page / 500 error** | Check `C:\xampp\php\logs\php_error_log` for errors; ensure `display_errors = On` in php.ini |
| **"vendor/autoload.php not found"** | Run `composer install` in the project root |
| **Styles missing / broken layout** | Ensure the folder is named correctly and accessed via `http://localhost/Poolpal/` |
| **phpMyAdmin import fails** | Ensure database `poolpal` exists first; increase `upload_max_filesize` in php.ini if the SQL file is large |
| **Email not sending** | Check SMTP credentials in config.php; ensure "Less secure apps" or App Passwords are configured for Gmail |
| **Payment not working** | Use Razorpay test keys in `.env`; test mode doesn't charge real money |

---

## Project Structure Overview

```
Poolpal/
├── admin/              # Admin panel (driver verification, bookings, users)
├── config/             # Payment & mail configuration
├── css/                # Stylesheets (responsive.css, animated-bg.css, etc.)
├── includes/           # Shared classes (PaymentProcessor.php)
├── images/             # Static image assets
├── js/                 # JavaScript (places autocomplete, vehicle selection)
├── uploads/            # User-uploaded files (profiles, driver docs, banners)
├── vendor/             # Composer dependencies (auto-generated)
├── .env                # Environment variables (Razorpay keys)
├── config.php          # Main app configuration (DB, SMTP, API keys)
├── db.php              # Database connection
├── init.php            # Session + bootstrap (include this to start)
├── poolpal.sql         # Full database schema + sample data
├── composer.json       # PHP dependency definitions
└── fpage.php           # Homepage / landing page
```

---

*For detailed feature documentation, see [BRD.md](BRD.md).*
