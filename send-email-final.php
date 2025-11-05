<?php
/**
 * Final Email Handler with Multiple Fallback Options
 * This version will try everything possible to send the email
 */

// Load configuration
require_once 'config.php';

// Start session
session_start();

// Enable error reporting for debugging (disable in production)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

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

// Function to send email using native mail()
function sendWithNativeMail($to, $subject, $message, $fromEmail, $replyTo) {
    // Try method 1: With -f parameter
    $headers = "From: " . SITE_NAME . " <{$fromEmail}>\r\n";
    $headers .= "Reply-To: {$replyTo}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    $additionalParams = "-f" . $fromEmail;
    if (@mail($to, $subject, $message, $headers, $additionalParams)) {
        return true;
    }
    
    // Try method 2: Without -f parameter
    if (@mail($to, $subject, $message, $headers)) {
        return true;
    }
    
    // Try method 3: Simple headers
    $simpleHeaders = "From: {$fromEmail}\r\n";
    $simpleHeaders .= "Reply-To: {$replyTo}\r\n";
    $simpleHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
    if (@mail($to, $subject, $message, $simpleHeaders)) {
        return true;
    }
    
    return false;
}

// Function to send email using SMTP with fsockopen (no PHPMailer needed)
function sendWithSMTP($to, $subject, $message, $fromEmail, $fromName, $replyTo) {
    if (!defined('SMTP_HOST') || !defined('SMTP_USERNAME') || !defined('SMTP_PASSWORD')) {
        return false;
    }
    
    $host = SMTP_HOST;
    $port = defined('SMTP_PORT') ? SMTP_PORT : 587;
    $username = SMTP_USERNAME;
    $password = SMTP_PASSWORD;
    
    // Try to connect
    $smtp = @fsockopen($host, $port, $errno, $errstr, 30);
    if (!$smtp) {
        logEvent("SMTP Connection failed: $errstr ($errno)");
        return false;
    }
    
    // Read greeting
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) != '220') {
        fclose($smtp);
        return false;
    }
    
    // Send EHLO
    fputs($smtp, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
    $response = fgets($smtp, 515);
    
    // Start TLS if needed
    if ($port == 587) {
        fputs($smtp, "STARTTLS\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '220') {
            fclose($smtp);
            return false;
        }
        
        // Enable crypto
        if (!@stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($smtp);
            return false;
        }
        
        // Send EHLO again after STARTTLS
        fputs($smtp, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
        $response = fgets($smtp, 515);
    }
    
    // Authenticate
    fputs($smtp, "AUTH LOGIN\r\n");
    $response = fgets($smtp, 515);
    
    fputs($smtp, base64_encode($username) . "\r\n");
    $response = fgets($smtp, 515);
    
    fputs($smtp, base64_encode($password) . "\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) != '235') {
        fclose($smtp);
        logEvent("SMTP Authentication failed");
        return false;
    }
    
    // Send email
    fputs($smtp, "MAIL FROM: <{$fromEmail}>\r\n");
    $response = fgets($smtp, 515);
    
    fputs($smtp, "RCPT TO: <{$to}>\r\n");
    $response = fgets($smtp, 515);
    
    fputs($smtp, "DATA\r\n");
    $response = fgets($smtp, 515);
    
    // Prepare email headers and body
    $emailData = "From: {$fromName} <{$fromEmail}>\r\n";
    $emailData .= "Reply-To: {$replyTo}\r\n";
    $emailData .= "To: {$to}\r\n";
    $emailData .= "Subject: {$subject}\r\n";
    $emailData .= "MIME-Version: 1.0\r\n";
    $emailData .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $emailData .= "X-Mailer: PHP-SMTP/" . phpversion() . "\r\n";
    $emailData .= "\r\n";
    $emailData .= $message . "\r\n";
    $emailData .= ".\r\n";
    
    fputs($smtp, $emailData);
    $response = fgets($smtp, 515);
    
    // Quit
    fputs($smtp, "QUIT\r\n");
    fclose($smtp);
    
    if (substr($response, 0, 3) == '250') {
        return true;
    }
    
    return false;
}

// Function to send using PHPMailer if available
function sendWithPHPMailer($to, $subject, $message, $name, $email) {
    if (!file_exists('PHPMailer/PHPMailer.php')) {
        return false;
    }
    
    try {
        require_once 'PHPMailer/PHPMailer.php';
        require_once 'PHPMailer/SMTP.php';
        require_once 'PHPMailer/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
        $mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $mail->CharSet = 'UTF-8';
        
        $mail->setFrom(SMTP_USERNAME, SITE_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo($email, $name);
        
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        logEvent("PHPMailer Error: " . $e->getMessage());
        return false;
    }
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
    
    $fromEmail = "no-reply@yasmarina.ae";
    $mailSent = false;
    $method = "";
    
    // Try all methods in order of preference
    
    // Method 1: PHPMailer with SMTP (most reliable)
    if (!$mailSent && file_exists('PHPMailer/PHPMailer.php')) {
        $mailSent = sendWithPHPMailer(RECIPIENT_EMAIL, EMAIL_SUBJECT, $message, $name, $email);
        if ($mailSent) {
            $method = "PHPMailer SMTP";
        }
    }
    
    // Method 2: Native SMTP (no library needed)
    if (!$mailSent && defined('SMTP_HOST') && defined('SMTP_USERNAME')) {
        $mailSent = sendWithSMTP(RECIPIENT_EMAIL, EMAIL_SUBJECT, $message, $fromEmail, SITE_NAME, $email);
        if ($mailSent) {
            $method = "Native SMTP";
        }
    }
    
    // Method 3: Native mail() function (last resort)
    if (!$mailSent) {
        $mailSent = sendWithNativeMail(RECIPIENT_EMAIL, EMAIL_SUBJECT, $message, $fromEmail, $email);
        if ($mailSent) {
            $method = "Native mail()";
        }
    }
    
    // Send response
    if ($mailSent) {
        echo json_encode([
            'alert' => 'alert-success',
            'message' => SUCCESS_MESSAGE
        ]);
        logEvent("✅ Email sent successfully using {$method} - IP: $clientIP, Email: $email");
    } else {
        echo json_encode([
            'alert' => 'alert-danger',
            'message' => ERROR_MESSAGE . ' Please contact us directly at ' . RECIPIENT_EMAIL
        ]);
        logEvent("❌ All email methods failed - IP: $clientIP, Email: $email");
    }
    
} else {
    echo json_encode([
        'alert' => 'alert-danger',
        'message' => 'Invalid request method.'
    ]);
    logEvent("Invalid request method from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}
?>

