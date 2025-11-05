<?php
/**
 * Email Handler with PHPMailer SMTP
 * Uses configuration from config.php
 */

// Load configuration
require_once 'config.php';

// Start session for rate limiting
session_start();

// Logging function
function logEvent($message) {
    if (defined('LOG_EMAILS') && LOG_EMAILS) {
        $logEntry = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
        @file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
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
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Additional SMTP options for Office 365 and other servers
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Office 365 specific settings
        if (strpos(SMTP_HOST, 'office365.com') !== false || strpos(SMTP_HOST, 'outlook.com') !== false) {
            $mail->SMTPAutoTLS = true;
            $mail->Timeout = 30;
        }
        
        // Sender and recipient
        $mail->setFrom(SMTP_USERNAME, SITE_NAME);
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
        logEvent("❌ PHPMailer Error: " . $errorMsg);
        
        return array(
            'alert' => 'alert-danger',
            'message' => ERROR_MESSAGE
        );
    }
}
?>
