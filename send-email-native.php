<?php
/**
 * Hostinger SMTP Email Handler - PHP Native Method
 * Uses fsockopen with SSL for direct SMTP connection
 */

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// Load Hostinger-specific configuration
require_once 'config-hostinger.php';

// Start session
session_start();

// Simple logging function
function logNative($message) {
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
        logNative("Validation failed - Empty fields");
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'alert' => 'alert-danger',
            'message' => EMAIL_VALIDATION_ERROR
        ]);
        logNative("Validation failed - Invalid email: $email");
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
    
    // Send email using PHP Native SMTP
    $response = sendEmailNative(
        SMTP_HOST,
        SMTP_PORT,
        SMTP_USERNAME,
        SMTP_PASSWORD,
        SMTP_USERNAME, // from
        RECIPIENT_EMAIL, // to
        EMAIL_SUBJECT,
        $message,
        $email, // reply-to
        $ownerName
    );
    
    echo json_encode($response);
    
} else {
    echo json_encode([
        'alert' => 'alert-danger',
        'message' => 'Invalid request method.'
    ]);
    logNative("Invalid request method from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

/**
 * Send email using PHP Native SMTP (fsockopen)
 */
function sendEmailNative($host, $port, $user, $pass, $from, $to, $subject, $body, $replyTo = '', $replyToName = '') {
    
    logNative("📧 Starting Native SMTP send");
    logNative("   Host: $host");
    logNative("   Port: $port");
    logNative("   Username: $user");
    logNative("   From: $from");
    logNative("   To: $to");
    
    try {
        // Determine connection string based on port
        if ($port == 465) {
            // SSL connection
            $connectionString = "ssl://$host";
            logNative("   Using SSL connection (port 465)");
        } elseif ($port == 587) {
            // TLS connection (STARTTLS)
            $connectionString = $host;
            logNative("   Using TLS connection (port 587)");
        } else {
            $connectionString = $host;
        }
        
        // Open connection
        $fp = @fsockopen($connectionString, $port, $errno, $errstr, 30);
        
        if (!$fp) {
            logNative("❌ Connection failed: $errstr ($errno)");
            return array(
                'alert' => 'alert-danger',
                'message' => ERROR_MESSAGE . " (Connection failed: $errstr)"
            );
        }
        
        // Helper functions
        function readLine($fp) {
            $line = fgets($fp, 512);
            logNative("SERVER: " . trim($line));
            return $line;
        }
        
        function writeLine($fp, $cmd) {
            fwrite($fp, $cmd . "\r\n");
            logNative("CLIENT: " . trim($cmd));
            $response = readLine($fp);
            return $response;
        }
        
        // Read server greeting
        $greeting = readLine($fp);
        if (substr($greeting, 0, 3) != '220') {
            fclose($fp);
            logNative("❌ Invalid server greeting: $greeting");
            return array(
                'alert' => 'alert-danger',
                'message' => ERROR_MESSAGE . " (Invalid server response)"
            );
        }
        
        // Send EHLO
        $serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
        $ehloResponse = writeLine($fp, "EHLO $serverName");
        
        // For port 587, we need STARTTLS
        if ($port == 587) {
            $starttlsResponse = writeLine($fp, "STARTTLS");
            if (substr($starttlsResponse, 0, 3) != '220') {
                fclose($fp);
                logNative("❌ STARTTLS failed");
                return array(
                    'alert' => 'alert-danger',
                    'message' => ERROR_MESSAGE . " (STARTTLS failed)"
                );
            }
            
            // Enable crypto
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                logNative("❌ TLS encryption failed");
                return array(
                    'alert' => 'alert-danger',
                    'message' => ERROR_MESSAGE . " (TLS encryption failed)"
                );
            }
            
            // Send EHLO again after STARTTLS
            writeLine($fp, "EHLO $serverName");
        }
        
        // Authenticate
        $authResponse = writeLine($fp, "AUTH LOGIN");
        if (substr($authResponse, 0, 3) != '334') {
            fclose($fp);
            logNative("❌ AUTH LOGIN failed: $authResponse");
            return array(
                'alert' => 'alert-danger',
                'message' => ERROR_MESSAGE . " (Authentication failed)"
            );
        }
        
        // Send username
        $userResponse = writeLine($fp, base64_encode($user));
        if (substr($userResponse, 0, 3) != '334') {
            fclose($fp);
            logNative("❌ Username rejected");
            return array(
                'alert' => 'alert-danger',
                'message' => ERROR_MESSAGE . " (Username rejected)"
            );
        }
        
        // Send password
        $passResponse = writeLine($fp, base64_encode($pass));
        if (substr($passResponse, 0, 3) != '235') {
            fclose($fp);
            logNative("❌ Password rejected: $passResponse");
            return array(
                'alert' => 'alert-danger',
                'message' => ERROR_MESSAGE . " (Authentication failed - check username/password)"
            );
        }
        
        logNative("✅ Authentication successful");
        
        // Send MAIL FROM
        $mailFromResponse = writeLine($fp, "MAIL FROM:<$from>");
        if (substr($mailFromResponse, 0, 3) != '250') {
            fclose($fp);
            logNative("❌ MAIL FROM failed: $mailFromResponse");
            return array(
                'alert' => 'alert-danger',
                'message' => ERROR_MESSAGE . " (MAIL FROM failed)"
            );
        }
        
        // Send RCPT TO
        $rcptResponse = writeLine($fp, "RCPT TO:<$to>");
        if (substr($rcptResponse, 0, 3) != '250') {
            fclose($fp);
            logNative("❌ RCPT TO failed: $rcptResponse");
            return array(
                'alert' => 'alert-danger',
                'message' => ERROR_MESSAGE . " (RCPT TO failed)"
            );
        }
        
        // Send DATA
        $dataResponse = writeLine($fp, "DATA");
        if (substr($dataResponse, 0, 3) != '354') {
            fclose($fp);
            logNative("❌ DATA command failed: $dataResponse");
            return array(
                'alert' => 'alert-danger',
                'message' => ERROR_MESSAGE . " (DATA command failed)"
            );
        }
        
        // Build email headers and body
        $emailData = "Subject: $subject\r\n";
        $emailData .= "From: " . SITE_NAME . " <$from>\r\n";
        $emailData .= "To: $to\r\n";
        if (!empty($replyTo)) {
            $replyToHeader = !empty($replyToName) ? "$replyToName <$replyTo>" : $replyTo;
            $emailData .= "Reply-To: $replyToHeader\r\n";
        }
        $emailData .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $emailData .= "Date: " . date('r') . "\r\n";
        $emailData .= "X-Mailer: PHP Native SMTP\r\n";
        $emailData .= "\r\n";
        $emailData .= $body;
        $emailData .= "\r\n.\r\n";
        
        // Send email data
        fwrite($fp, $emailData);
        logNative("CLIENT: [Email data sent]");
        
        $dataEndResponse = readLine($fp);
        if (substr($dataEndResponse, 0, 3) != '250') {
            fclose($fp);
            logNative("❌ Email sending failed: $dataEndResponse");
            return array(
                'alert' => 'alert-danger',
                'message' => ERROR_MESSAGE . " (Email sending failed)"
            );
        }
        
        // Quit
        writeLine($fp, "QUIT");
        fclose($fp);
        
        logNative("✅ Email sent successfully via Native SMTP");
        logNative("   To: $to");
        logNative("   From: $from");
        
        return array(
            'alert' => 'alert-success',
            'message' => SUCCESS_MESSAGE
        );
        
    } catch (Exception $e) {
        logNative("❌ Exception: " . $e->getMessage());
        return array(
            'alert' => 'alert-danger',
            'message' => ERROR_MESSAGE . " (" . $e->getMessage() . ")"
        );
    }
}
?>

