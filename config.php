<?php
// Email Configuration File
// You can modify these values according to your needs

// Recipient Email
define('RECIPIENT_EMAIL', 'abdelrhman.hassan510@gmail.com');

// Email Subject
define('EMAIL_SUBJECT', 'New Yacht Registration - Yas Marina Rendezvous');

// SMTP Settings - Office 365 (Microsoft)
define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'no-reply@yasmarina.ae'); // Must exist in Office 365
define('SMTP_PASSWORD', 'Apple@2025'); // Office 365 password
define('SMTP_SECURE', 'tls');
define('SMTP_AUTH', true); // Required for Office 365

// Site Settings
define('SITE_NAME', 'Yas Marina Rendezvous');
define('SITE_URL', 'https://hatchtestserver.com/Yas-Marina-Rendezvous');

// Success and Error Messages (English)
define('SUCCESS_MESSAGE', 'Your message has been sent successfully! We will contact you soon.');
define('ERROR_MESSAGE', 'An error occurred while sending the message. Please try again.');
define('VALIDATION_ERROR', 'Please fill in all required fields.');
define('EMAIL_VALIDATION_ERROR', 'Please enter a valid email address.');

// Security Settings
define('ENABLE_CSRF_PROTECTION', true);
define('ENABLE_RATE_LIMITING', true);
define('MAX_REQUESTS_PER_HOUR', 10);

// Development Settings
define('DEBUG_MODE', true); // Enable for detailed error logging
define('LOG_EMAILS', true);
define('LOG_FILE', 'email_log.txt');
?>
