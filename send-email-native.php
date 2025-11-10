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
            $errorMsg = "Connection failed: $errstr ($errno)";
            logNative("❌ $errorMsg");
            
            $errorMessage = ERROR_MESSAGE;
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $errorMessage .= " (Debug: $errorMsg)";
            }
            
            return array(
                'alert' => 'alert-danger',
                'message' => $errorMessage
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
            $errorMsg = "Invalid server greeting: " . trim($greeting);
            logNative("❌ $errorMsg");
            
            $errorMessage = ERROR_MESSAGE;
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $errorMessage .= " (Debug: $errorMsg)";
            }
            
            return array(
                'alert' => 'alert-danger',
                'message' => $errorMessage
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
                $errorMsg = "STARTTLS failed. Server response: " . trim($starttlsResponse);
                logNative("❌ $errorMsg");
                
                $errorMessage = ERROR_MESSAGE . " (STARTTLS failed)";
                if (defined('DEBUG_MODE') && DEBUG_MODE) {
                    $errorMessage .= " - " . htmlspecialchars(trim($starttlsResponse));
                }
                
                return array(
                    'alert' => 'alert-danger',
                    'message' => $errorMessage
                );
            }
            
            // Enable crypto
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                $errorMsg = "TLS encryption failed. Cannot enable crypto.";
                logNative("❌ $errorMsg");
                
                $errorMessage = ERROR_MESSAGE . " (TLS encryption failed)";
                if (defined('DEBUG_MODE') && DEBUG_MODE) {
                    $errorMessage .= " - Cannot enable TLS encryption";
                }
                
                return array(
                    'alert' => 'alert-danger',
                    'message' => $errorMessage
                );
            }
            
            // Send EHLO again after STARTTLS
            writeLine($fp, "EHLO $serverName");
        }
        
        // Authenticate
        $authResponse = writeLine($fp, "AUTH LOGIN");
        if (substr($authResponse, 0, 3) != '334') {
            fclose($fp);
            $errorMsg = "AUTH LOGIN failed. Server response: " . trim($authResponse);
            logNative("❌ $errorMsg");
            
            $errorMessage = ERROR_MESSAGE . " (AUTH LOGIN failed)";
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $errorMessage .= " - " . htmlspecialchars(trim($authResponse));
            }
            
            return array(
                'alert' => 'alert-danger',
                'message' => $errorMessage
            );
        }
        
        // Send username
        $userResponse = writeLine($fp, base64_encode($user));
        if (substr($userResponse, 0, 3) != '334') {
            fclose($fp);
            $errorMsg = "Username rejected. Server response: " . trim($userResponse);
            logNative("❌ $errorMsg");
            logNative("❌ Username sent: $user");
            
            $errorMessage = ERROR_MESSAGE . " (Username rejected)";
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $errorMessage .= " - " . htmlspecialchars(trim($userResponse));
            }
            
            return array(
                'alert' => 'alert-danger',
                'message' => $errorMessage
            );
        }
        
        // Send password
        $passResponse = writeLine($fp, base64_encode($pass));
        if (substr($passResponse, 0, 3) != '235') {
            fclose($fp);
            $errorMsg = "Authentication failed. Server response: " . trim($passResponse);
            logNative("❌ $errorMsg");
            logNative("❌ Username: $user");
            logNative("❌ Password length: " . strlen($pass) . " characters");
            
            $errorMessage = ERROR_MESSAGE . " (Authentication failed)";
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $errorMessage .= " - Server: " . htmlspecialchars(trim($passResponse));
            }
            
            return array(
                'alert' => 'alert-danger',
                'message' => $errorMessage
            );
        }
        
        logNative("✅ Authentication successful");
        
        // Send MAIL FROM
        $mailFromResponse = writeLine($fp, "MAIL FROM:<$from>");
        if (substr($mailFromResponse, 0, 3) != '250') {
            fclose($fp);
            $errorMsg = "MAIL FROM failed. Server response: " . trim($mailFromResponse);
            logNative("❌ $errorMsg");
            logNative("❌ From address: $from");
            
            $errorMessage = ERROR_MESSAGE . " (MAIL FROM failed)";
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $errorMessage .= " - " . htmlspecialchars(trim($mailFromResponse));
            }
            
            return array(
                'alert' => 'alert-danger',
                'message' => $errorMessage
            );
        }
        
        // Send RCPT TO
        $rcptResponse = writeLine($fp, "RCPT TO:<$to>");
        if (substr($rcptResponse, 0, 3) != '250') {
            fclose($fp);
            $errorMsg = "RCPT TO failed. Server response: " . trim($rcptResponse);
            logNative("❌ $errorMsg");
            logNative("❌ To address: $to");
            
            $errorMessage = ERROR_MESSAGE . " (RCPT TO failed)";
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $errorMessage .= " - " . htmlspecialchars(trim($rcptResponse));
            }
            
            return array(
                'alert' => 'alert-danger',
                'message' => $errorMessage
            );
        }
        
        // Send DATA
        $dataResponse = writeLine($fp, "DATA");
        if (substr($dataResponse, 0, 3) != '354') {
            fclose($fp);
            $errorMsg = "DATA command failed. Server response: " . trim($dataResponse);
            logNative("❌ $errorMsg");
            
            $errorMessage = ERROR_MESSAGE . " (DATA command failed)";
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $errorMessage .= " - " . htmlspecialchars(trim($dataResponse));
            }
            
            return array(
                'alert' => 'alert-danger',
                'message' => $errorMessage
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
            $errorMsg = "Email sending failed. Server response: " . trim($dataEndResponse);
            logNative("❌ $errorMsg");
            
            $errorMessage = ERROR_MESSAGE . " (Email sending failed)";
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $errorMessage .= " - Server: " . htmlspecialchars(trim($dataEndResponse));
            }
            
            return array(
                'alert' => 'alert-danger',
                'message' => $errorMessage
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
        $errorDetails = $e->getMessage();
        $errorTrace = $e->getTraceAsString();
        
        logNative("❌ Exception: " . $errorDetails);
        logNative("❌ Trace: " . $errorTrace);
        
        // Return error with details if DEBUG_MODE is enabled
        $errorMessage = ERROR_MESSAGE;
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $errorMessage .= " (Error: " . htmlspecialchars($errorDetails) . ")";
        }
        
        return array(
            'alert' => 'alert-danger',
            'message' => $errorMessage
        );
    }
}
?>

