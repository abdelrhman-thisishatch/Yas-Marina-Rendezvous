<?php
// Email Configuration File
// You can modify these values according to your needs

// Recipient Email
define('RECIPIENT_EMAIL', 'abdelrhman@thisishatch.com');

// Email Subject
define('EMAIL_SUBJECT', 'New Yacht Registration - Yas Marina Rendezvous');

// SMTP Settings - Hostinger
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'no-reply@yasmarina.ae'); // Email account
define('SMTP_PASSWORD', 'M[1t~/zhgkl'); // Email password
define('SMTP_SECURE', 'ssl'); // SSL for port 465
define('SMTP_AUTH', true); // Required for Hostinger

// OAuth2 Settings for Microsoft 365 (Recommended - replaces App Password)
// Set USE_OAUTH2 to true to enable OAuth2 authentication
define('USE_OAUTH2', false); // Set to true to use OAuth2 instead of password
define('OAUTH_CLIENT_ID', ''); // Azure App Registration Client ID
define('OAUTH_CLIENT_SECRET', ''); // Azure App Registration Client Secret
define('OAUTH_TENANT_ID', ''); // Azure Tenant ID (optional, can use 'common')
define('OAUTH_REFRESH_TOKEN', ''); // OAuth2 Refresh Token (obtained after initial auth)
define('OAUTH_USER_EMAIL', 'no-reply@yasmarina.ae'); // Email address for OAuth2

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
