# Pool Pal

A rideshare application for carpooling.

## Troubleshooting

### Forgot Password Issues

If you encounter a "Something went wrong" error when using the forgot password functionality, check the following:

1. Make sure the vendor/autoload.php file exists. If it's missing, you can run:
   ```
   composer install
   ```

2. If Composer installation fails, make sure the manually created autoload.php file includes the PHPMailer classes:
   ```php
   <?php
   require_once __DIR__ . '/phpmailer/phpmailer/src/Exception.php';
   require_once __DIR__ . '/phpmailer/phpmailer/src/PHPMailer.php';
   require_once __DIR__ . '/phpmailer/phpmailer/src/SMTP.php';
   ```

3. Check database connection settings in config.php.

4. Use the diagnostic page to troubleshoot system issues:
   ```
   http://your-site-url/forgot_password_test.php
   ```

## Testing

You can test the forgot password functionality using:

1. test_forgot_pw.html - A simple interface to test the forgot password functionality
2. forgot_password_test.php - A diagnostic page that checks all components of the system 