<?php
/**
 * Hostinger SMTP Email Handler - Standalone Version
 * This is a separate file for testing Hostinger SMTP
 */

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// Load Hostinger-specific configuration
require_once 'config-hostinger.php';

// Start session
session_start();

// Simple logging function
function logHostinger($message) {
    $logFile = defined('LOG_FILE') ? LOG_FILE : 'email_log_hostinger.txt';
    $logEntry = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Main processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $clientIP = $_SERVER['REMOTE_ADDR'];
    
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
        logHostinger("Validation failed - Empty fields");
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'alert' => 'alert-danger',
            'message' => EMAIL_VALIDATION_ERROR
        ]);
        logHostinger("Validation failed - Invalid email: $email");
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
    
    // Send email
    $response = sendEmailHostinger(RECIPIENT_EMAIL, EMAIL_SUBJECT, $message, $ownerName, $email);
    echo json_encode($response);
    
} else {
    echo json_encode([
        'alert' => 'alert-danger',
        'message' => 'Invalid request method.'
    ]);
    logHostinger("Invalid request method from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

/**
 * Send email using Hostinger SMTP
 */
function sendEmailHostinger($to_email, $subject, $message, $name, $from_email) {
    
    // Check if PHPMailer exists
    if (!file_exists('PHPMailer/PHPMailer.php')) {
        logHostinger("❌ PHPMailer not found");
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
        
        // Enable debug output
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $mail->SMTPDebug = 2; // Show detailed debug info
            $mail->Debugoutput = function($str, $level) {
                logHostinger("SMTP Debug: $str");
            };
        }
        
        // SMTP Configuration - Hostinger
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 30;
        
        // Set encryption - ensure lowercase
        $secure = strtolower(SMTP_SECURE);
        if ($secure == 'ssl' || $secure == 'tls') {
            $mail->SMTPSecure = $secure;
        } else {
            // Auto-detect based on port
            if (SMTP_PORT == 465) {
                $mail->SMTPSecure = 'ssl';
            } elseif (SMTP_PORT == 587) {
                $mail->SMTPSecure = 'tls';
            }
        }
        
        // Set credentials
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        
        // Log configuration
        logHostinger("📧 Hostinger SMTP Configuration:");
        logHostinger("   Host: " . SMTP_HOST);
        logHostinger("   Port: " . SMTP_PORT);
        logHostinger("   Secure: " . $mail->SMTPSecure);
        logHostinger("   Username: " . SMTP_USERNAME);
        logHostinger("   Password Length: " . strlen(SMTP_PASSWORD) . " characters");
        logHostinger("   Password contains special chars: " . (preg_match('/[^a-zA-Z0-9]/', SMTP_PASSWORD) ? 'Yes' : 'No'));
        
        // SSL/TLS Options
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
        
        logHostinger("✅ Email sent successfully via Hostinger SMTP");
        logHostinger("   To: $to_email");
        logHostinger("   From: $from_email");
        
        return array(
            'alert' => 'alert-success',
            'message' => SUCCESS_MESSAGE
        );
        
    } catch (Exception $e) {
        $errorMsg = $mail->ErrorInfo;
        $exceptionMsg = $e->getMessage();
        
        // Log detailed error
        logHostinger("❌ PHPMailer Error: " . $errorMsg);
        logHostinger("❌ Exception: " . $exceptionMsg);
        logHostinger("❌ SMTP Host: " . SMTP_HOST . " Port: " . SMTP_PORT);
        logHostinger("❌ Username: " . SMTP_USERNAME);
        
        // Return error
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

