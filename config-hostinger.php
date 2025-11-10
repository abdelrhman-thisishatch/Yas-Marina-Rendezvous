<?php
// Hostinger SMTP Configuration - Test File
// This is a separate config file for testing Hostinger SMTP

// Recipient Email
define('RECIPIENT_EMAIL', 'abdelrhman@thisishatch.com');

// Email Subject
define('EMAIL_SUBJECT', 'New Yacht Registration - Yas Marina Rendezvous');

// SMTP Settings - Hostinger (Test Configuration)
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587); // Port 587 with TLS
define('SMTP_USERNAME', 'no-reply@yasmarina.ae'); // Full email address
define('SMTP_PASSWORD', 'M[1t~/zhgkl'); // Email password
define('SMTP_SECURE', 'tls'); // TLS encryption
define('SMTP_AUTH', true);

// Site Settings
define('SITE_NAME', 'Yas Marina Rendezvous');
define('SITE_URL', 'https://yasrendezvous.ae');

// Success and Error Messages
define('SUCCESS_MESSAGE', 'Your message has been sent successfully! We will contact you soon.');
define('ERROR_MESSAGE', 'An error occurred while sending the message. Please try again.');
define('VALIDATION_ERROR', 'Please fill in all required fields.');
define('EMAIL_VALIDATION_ERROR', 'Please enter a valid email address.');

// Development Settings
define('DEBUG_MODE', true);
define('LOG_EMAILS', true);
define('LOG_FILE', 'email_log_hostinger.txt'); // Separate log file
?>

