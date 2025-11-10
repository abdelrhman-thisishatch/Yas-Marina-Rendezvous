<?php
/**
 * Hostinger SMTP Test - Standalone
 * Test SMTP connection with Hostinger
 */

require_once 'config-hostinger.php';

echo "<h2>Hostinger SMTP Connection Test</h2>";
echo "<pre style='background: #f5f5f5; padding: 20px; border-radius: 5px;'>";

echo "Configuration:\n";
echo "==============\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "Secure: " . SMTP_SECURE . "\n";
echo "Username: " . SMTP_USERNAME . "\n";
echo "Password: " . str_repeat('*', strlen(SMTP_PASSWORD)) . " (" . strlen(SMTP_PASSWORD) . " chars)\n";
echo "\n";

if (!file_exists('PHPMailer/PHPMailer.php')) {
    die("❌ ERROR: PHPMailer not found!\n");
}

require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    echo "Testing Connection...\n";
    echo "====================\n\n";
    
    // Enable verbose debug
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo htmlspecialchars($str);
    };
    
    // SMTP settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port = SMTP_PORT;
    $mail->Timeout = 30;
    
    // SSL options
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    echo "\nConnecting to " . SMTP_HOST . ":" . SMTP_PORT . "...\n";
    echo "Using " . strtoupper(SMTP_SECURE) . " encryption\n\n";
    
    // Test connection
    $mail->smtpConnect();
    
    echo "\n";
    echo "✅ SUCCESS: Connected and authenticated!\n";
    echo "\nConnection Details:\n";
    echo "-------------------\n";
    echo "Host: " . SMTP_HOST . "\n";
    echo "Port: " . SMTP_PORT . "\n";
    echo "Encryption: " . strtoupper(SMTP_SECURE) . "\n";
    echo "Username: " . SMTP_USERNAME . "\n";
    echo "Authentication: ✅ SUCCESS\n";
    
    $mail->smtpClose();
    
} catch (Exception $e) {
    echo "\n";
    echo "❌ ERROR: Connection failed!\n";
    echo "\nError Details:\n";
    echo "-------------\n";
    echo "Exception: " . $e->getMessage() . "\n";
    echo "PHPMailer Error: " . $mail->ErrorInfo . "\n";
    echo "\nPossible Solutions:\n";
    echo "1. Check username and password\n";
    echo "2. Verify email account exists in Hostinger\n";
    echo "3. Ensure SMTP is enabled for the account\n";
    echo "4. Try port 465 with SSL instead\n";
    echo "5. Check firewall settings\n";
}

echo "</pre>";
?>

