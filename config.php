<?php
// Email Configuration File
// You can modify these values according to your needs

// Recipient Email
define('RECIPIENT_EMAIL', 'samer.eladem@yasmarina.ae');

// Email Subject
define('EMAIL_SUBJECT', 'New Message from Yas Marina Rendezvous Website');

// SMTP Settings (optional - if you want to use PHPMailer)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'no-reply@yasmarina.ae'); // Sender email
define('SMTP_PASSWORD', 'Apple@2025'); // Application password
define('SMTP_SECURE', 'tls');

// Site Settings
define('SITE_NAME', 'Yas Marina Rendezvous');
define('SITE_URL', 'https://hatchtestserver.com/Yas-Marina-Rendezvous');

// Success and Error Messages
define('SUCCESS_MESSAGE', 'Your message has been sent successfully! We will contact you soon.');
define('ERROR_MESSAGE', 'An error occurred while sending the message. Please try again.');
define('VALIDATION_ERROR', 'Please fill in all required fields.');
define('EMAIL_VALIDATION_ERROR', 'Please enter a valid email address.');

// Security Settings
define('ENABLE_CSRF_PROTECTION', true);
define('ENABLE_RATE_LIMITING', true);
define('MAX_REQUESTS_PER_HOUR', 10);

// Development Settings
define('DEBUG_MODE', false);
define('LOG_EMAILS', true);
define('LOG_FILE', 'email_log.txt');
?>
