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
    $name = isset($_POST['contactName']) ? trim($_POST['contactName']) : '';
    $email = isset($_POST['contactEmail']) ? trim($_POST['contactEmail']) : '';
    $enquiry = isset($_POST['contactEnquiry']) ? trim($_POST['contactEnquiry']) : '';
    
    // Validation
    if (empty($name) || empty($email) || empty($enquiry)) {
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
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $enquiry = htmlspecialchars($enquiry, ENT_QUOTES, 'UTF-8');
    
    // Prepare message
    $message = "New inquiry from " . SITE_NAME . " website:\n\n";
    $message .= "Name: " . $name . "\n";
    $message .= "Email: " . $email . "\n";
    $message .= "Message: " . $enquiry . "\n\n";
    $message .= "Sent at: " . date('Y-m-d H:i:s') . "\n";
    $message .= "IP Address: " . $clientIP . "\n";
    
    // Send email using PHPMailer
    $response = sendWithPHPMailer(RECIPIENT_EMAIL, EMAIL_SUBJECT, $message, $name, $email);
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
        
        // Additional SMTP options for reliability
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
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
