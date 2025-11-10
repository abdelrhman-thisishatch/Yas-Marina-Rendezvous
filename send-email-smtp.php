<?php
/**
 * Email Handler with PHPMailer SMTP
 * Uses configuration from config.php
 */

// Set JSON header for AJAX responses
header('Content-Type: application/json; charset=utf-8');

// Load configuration
require_once 'config.php';

// Start session for rate limiting
session_start();

// Logging function
function logEvent($message) {
    if (defined('LOG_EMAILS') && LOG_EMAILS) {
        $logEntry = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
        $logFile = defined('LOG_FILE') ? LOG_FILE : 'email_log.txt';
        $result = @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        if ($result === false) {
            // Log to error log if file write fails
            error_log("Failed to write to log file: $logFile - Message: $message");
        }
    }
}

// Rate limiting function
function checkRateLimit($ip) {
    if (!defined('ENABLE_RATE_LIMITING') || !ENABLE_RATE_LIMITING) return true;
    
    $currentTime = time();
    $hourAgo = $currentTime - 3600;
    
    if (!isset($_SESSION['email_requests'])) {
        $_SESSION['email_requests'] = array();
    }
    
    $_SESSION['email_requests'] = array_filter($_SESSION['email_requests'], function($time) use ($hourAgo) {
        return $time > $hourAgo;
    });
    
    if (count($_SESSION['email_requests']) >= MAX_REQUESTS_PER_HOUR) {
        return false;
    }
    
    $_SESSION['email_requests'][] = $currentTime;
    return true;
}

// Main processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $clientIP = $_SERVER['REMOTE_ADDR'];
    
    // Rate limiting check
    if (!checkRateLimit($clientIP)) {
        echo json_encode([
            'alert' => 'alert-danger',
            'message' => 'You have exceeded the maximum number of requests. Please try again after one hour.'
        ]);
        logEvent("Rate limit exceeded for IP: $clientIP");
        exit;
    }
    
    // Get form data
    $yachtName = isset($_POST['yachtName']) ? trim($_POST['yachtName']) : '';
    $loaMeters = isset($_POST['loaMeters']) ? trim($_POST['loaMeters']) : '';
    $ownerName = isset($_POST['ownerName']) ? trim($_POST['ownerName']) : '';
    $mobileNumber = isset($_POST['mobileNumber']) ? trim($_POST['mobileNumber']) : '';
    $email = isset($_POST['contactEmail']) ? trim($_POST['contactEmail']) : '';
    
    // Validation
    if (empty($yachtName) || empty($loaMeters) || empty($ownerName) || empty($mobileNumber) || empty($email)) {
        echo json_encode([
            'alert' => 'alert-danger',
            'message' => VALIDATION_ERROR
        ]);
        logEvent("Validation failed for IP: $clientIP - Empty fields");
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'alert' => 'alert-danger',
            'message' => EMAIL_VALIDATION_ERROR
        ]);
        logEvent("Validation failed for IP: $clientIP - Invalid email: $email");
        exit;
    }
    
    // Sanitize input
    $yachtName = htmlspecialchars($yachtName, ENT_QUOTES, 'UTF-8');
    $loaMeters = htmlspecialchars($loaMeters, ENT_QUOTES, 'UTF-8');
    $ownerName = htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8');
    $mobileNumber = htmlspecialchars($mobileNumber, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    
    // Prepare message
    $message = "New Yacht Registration from " . SITE_NAME . " website:\n\n";
    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $message .= "YACHT DETAILS\n";
    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $message .= "Name of Yacht: " . $yachtName . "\n";
    $message .= "LOA (meters): " . $loaMeters . " meters\n\n";
    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $message .= "OWNER CONTACT DETAILS\n";
    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $message .= "Name of Owner: " . $ownerName . "\n";
    $message .= "Mobile Number: " . $mobileNumber . "\n";
    $message .= "Email Address: " . $email . "\n\n";
    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $message .= "SUBMISSION INFO\n";
    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $message .= "Submitted at: " . date('Y-m-d H:i:s') . "\n";
    $message .= "IP Address: " . $clientIP . "\n";
    
    // Send email using PHPMailer
    $response = sendWithPHPMailer(RECIPIENT_EMAIL, EMAIL_SUBJECT, $message, $ownerName, $email);
    echo json_encode($response);
    
} else {
    echo json_encode([
        'alert' => 'alert-danger',
        'message' => 'Invalid request method.'
    ]);
    logEvent("Invalid request method from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

/**
 * Function to send email using PHPMailer with SMTP
 */
function sendWithPHPMailer($to_email, $subject, $message, $name, $from_email) {
    // Check if PHPMailer is installed
    if (!file_exists('PHPMailer/PHPMailer.php')) {
        logEvent("❌ PHPMailer not found");
        return array(
            'alert' => 'alert-danger',
            'message' => ERROR_MESSAGE . ' (PHPMailer not installed)'
        );
    }
    
    try {
        require_once 'PHPMailer/PHPMailer.php';
        require_once 'PHPMailer/SMTP.php';
        require_once 'PHPMailer/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Enable verbose debug output (disable in production)
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str, $level) {
                logEvent("SMTP Debug: $str");
            };
        }
        
        // SMTP settings from config.php
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Set encryption based on port
        if (SMTP_PORT == 465) {
            $mail->SMTPSecure = 'ssl'; // SSL for port 465
        } elseif (SMTP_PORT == 587) {
            $mail->SMTPSecure = 'tls'; // TLS for port 587
        } else {
            $mail->SMTPSecure = SMTP_SECURE; // Use config value
        }
        
        // Check if OAuth2 is enabled
        $useOAuth2 = defined('USE_OAUTH2') && USE_OAUTH2 === true;
        
        if ($useOAuth2) {
            // OAuth2 Authentication (Recommended for Microsoft 365)
            logEvent("🔐 Using OAuth2 authentication");
            
            // Check if OAuth2 dependencies are available
            if (!file_exists('PHPMailer/OAuth.php')) {
                logEvent("❌ PHPMailer OAuth.php not found");
                return array(
                    'alert' => 'alert-danger',
                    'message' => ERROR_MESSAGE . ' (OAuth2 support not available)'
                );
            }
            
            // Check if League OAuth2 Client is installed
            if (!class_exists('League\OAuth2\Client\Provider\GenericProvider')) {
                logEvent("❌ League OAuth2 Client library not found. Please install via Composer.");
                return array(
                    'alert' => 'alert-danger',
                    'message' => ERROR_MESSAGE . ' (OAuth2 library not installed. Run: composer install)'
                );
            }
            
            require_once 'PHPMailer/OAuth.php';
            require_once 'PHPMailer/OAuthTokenProvider.php';
            
            // Validate OAuth2 configuration
            if (empty(OAUTH_CLIENT_ID) || empty(OAUTH_CLIENT_SECRET) || empty(OAUTH_REFRESH_TOKEN)) {
                logEvent("❌ OAuth2 configuration incomplete. Check config.php");
                return array(
                    'alert' => 'alert-danger',
                    'message' => ERROR_MESSAGE . ' (OAuth2 configuration incomplete)'
                );
            }
            
            // Create Microsoft OAuth2 Provider
            $tenantId = !empty(OAUTH_TENANT_ID) ? OAUTH_TENANT_ID : 'common';
            $provider = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId' => OAUTH_CLIENT_ID,
                'clientSecret' => OAUTH_CLIENT_SECRET,
                'redirectUri' => SITE_URL . '/oauth-callback.php',
                'urlAuthorize' => "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/authorize",
                'urlAccessToken' => "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token",
                'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',
                'scopes' => ['https://outlook.office.com/SMTP.Send', 'offline_access']
            ]);
            
            // Create OAuth instance
            $oauth = new PHPMailer\PHPMailer\OAuth([
                'provider' => $provider,
                'userName' => OAUTH_USER_EMAIL,
                'clientSecret' => OAUTH_CLIENT_SECRET,
                'clientId' => OAUTH_CLIENT_ID,
                'refreshToken' => OAUTH_REFRESH_TOKEN
            ]);
            
            // Set OAuth for PHPMailer
            $mail->setOAuth($oauth);
            $mail->AuthType = 'XOAUTH2';
            $mail->Username = OAUTH_USER_EMAIL;
            $mail->Password = ''; // Not used with OAuth2
            
        } else {
            // Traditional Username/Password Authentication
            logEvent("📧 Using SMTP authentication with Hostinger");
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
        }
        
        // Additional SMTP options for SSL/TLS connections
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Hostinger specific settings
        if (strpos(SMTP_HOST, 'hostinger.com') !== false) {
            $mail->Timeout = 30;
            logEvent("🔧 Hostinger SMTP settings applied");
        }
        
        // Office 365 specific settings (if using OAuth2)
        if (strpos(SMTP_HOST, 'office365.com') !== false || strpos(SMTP_HOST, 'outlook.com') !== false) {
            $mail->SMTPAutoTLS = true;
            $mail->Timeout = 30;
        }
        
        // Sender and recipient
        $fromEmail = $useOAuth2 ? OAUTH_USER_EMAIL : SMTP_USERNAME;
        $mail->setFrom($fromEmail, SITE_NAME);
        $mail->addAddress($to_email);
        $mail->addReplyTo($from_email, $name);
        
        // Message content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        // Send email
        $mail->send();
        
        logEvent("✅ Email sent successfully via SMTP to: $to_email, From: $from_email");
        
        return array(
            'alert' => 'alert-success',
            'message' => SUCCESS_MESSAGE
        );
        
    } catch (Exception $e) {
        $errorMsg = $mail->ErrorInfo;
        $exceptionMsg = $e->getMessage();
        
        // Log detailed error information
        logEvent("❌ PHPMailer Error: " . $errorMsg);
        logEvent("❌ Exception: " . $exceptionMsg);
        logEvent("❌ SMTP Host: " . SMTP_HOST . " Port: " . SMTP_PORT . " Secure: " . $mail->SMTPSecure);
        
        // Return error with more details if DEBUG_MODE is enabled
        $errorMessage = ERROR_MESSAGE;
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $errorMessage .= " (Debug: " . htmlspecialchars($errorMsg) . ")";
        }
        
        return array(
            'alert' => 'alert-danger',
            'message' => $errorMessage
        );
    }
}
?>
