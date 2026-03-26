# Forgot Password Setup Guide

This guide will help you set up the forgot password functionality with email and WhatsApp OTP verification for Pool Pal.

## Prerequisites

1. PHP 7.2+ with PDO and MySQLi extensions enabled
2. MySQL/MariaDB database
3. Composer (for installing dependencies)
4. Twilio account for WhatsApp messaging

## Installation Steps

### 1. Install Required PHP Packages

Run the following command in your project directory:

```bash
composer require phpmailer/phpmailer twilio/sdk
```

This will install PHPMailer for sending emails and Twilio SDK for WhatsApp messaging.

### 2. Create the Password Resets Table

Execute the SQL script in your database:

```sql
-- Create password_resets table if it doesn't exist
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `token` varchar(64) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3. Configure Settings

Update the `config.php` file with your database credentials, SMTP settings for email, and Twilio credentials:

#### Database Settings
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');
```

#### Email (SMTP) Settings
```php
define('SMTP_HOST', 'smtp.gmail.com'); // Or your SMTP server
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_app_password'); // Use app password for Gmail
define('SMTP_FROM_EMAIL', 'your_email@gmail.com');
define('SMTP_FROM_NAME', 'Pool Pal');
```

#### Twilio WhatsApp Settings
```php
define('TWILIO_SID', 'your_twilio_sid');
define('TWILIO_TOKEN', 'your_twilio_token');
define('TWILIO_WHATSAPP_NUMBER', '+14155238886'); // Your Twilio WhatsApp number
```

### 4. Gmail App Password Setup (if using Gmail)

If you're using Gmail for sending emails, you'll need to create an App Password:

1. Go to your Google Account settings (https://myaccount.google.com/)
2. Select Security
3. Under "Signing in to Google," select 2-Step Verification
4. At the bottom of the page, select App passwords
5. Generate a new app password for "Mail" and your app
6. Use this password in the SMTP_PASS setting

### 5. Twilio WhatsApp Setup

To use Twilio for WhatsApp messages:

1. Sign up for a Twilio account at https://www.twilio.com
2. Activate the WhatsApp sandbox in the Twilio console
3. Connect your WhatsApp number to the Twilio sandbox by following their instructions
4. Get your Account SID and Auth Token from the Twilio dashboard
5. Update the Twilio settings in config.php with these credentials

### 6. Test the Functionality

1. Make sure all files are in place:
   - login.php (with the forgot password modal)
   - forgot_password.php
   - verify_otp.php
   - reset_password.php
   - config.php

2. Try the forgot password flow:
   - Click "Forgot password?" on the login page
   - Enter your email address
   - Check that you receive the OTP via email and WhatsApp
   - Enter the OTP to verify
   - Reset your password

## Troubleshooting

### Email Issues
- Check your SMTP settings
- Make sure your email provider allows SMTP access
- If using Gmail, ensure you're using an App Password, not your regular password
- Check your server's error logs for PHP mail errors

### WhatsApp Issues
- Verify your Twilio credentials are correct
- Make sure your WhatsApp number is connected to the Twilio sandbox
- Check if your Twilio account has sufficient balance
- Review Twilio's console for any error messages

### Database Issues
- Confirm your database credentials are correct
- Ensure the password_resets table exists with the correct structure
- Check if your database user has proper permissions

## Security Considerations

1. The OTP expires after 1 hour for security
2. The OTP is a 6-digit number for easy verification
3. Passwords are securely hashed using PHP's password_hash function
4. Invalid attempts are logged for security monitoring
5. Reset tokens are generated using cryptographically secure methods 