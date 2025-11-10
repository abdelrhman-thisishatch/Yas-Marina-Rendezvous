<?php
/**
 * SMTP Connection Test Script
 * Use this to diagnose SMTP connection issues
 */

require_once 'config.php';

echo "<h2>SMTP Connection Test</h2>";
echo "<pre>";

echo "SMTP Configuration:\n";
echo "===================\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "Secure: " . SMTP_SECURE . "\n";
echo "Username: " . SMTP_USERNAME . "\n";
echo "Password: " . (strlen(SMTP_PASSWORD) > 0 ? str_repeat('*', strlen(SMTP_PASSWORD)) : 'NOT SET') . "\n";
echo "\n";

// Test PHPMailer
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
    echo "Testing SMTP Connection...\n";
    echo "==========================\n\n";
    
    // Enable verbose debug output
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo $str;
    };
    
    // SMTP settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    
    // Set encryption based on port
    if (SMTP_PORT == 465) {
        $mail->SMTPSecure = 'ssl';
        echo "Using SSL encryption (port 465)\n";
    } elseif (SMTP_PORT == 587) {
        $mail->SMTPSecure = 'tls';
        echo "Using TLS encryption (port 587)\n";
    } else {
        $mail->SMTPSecure = SMTP_SECURE;
        echo "Using encryption: " . SMTP_SECURE . "\n";
    }
    
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    
    // SSL options
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    $mail->Timeout = 30;
    
    echo "\nAttempting to connect...\n";
    echo "------------------------\n";
    
    // Test connection (don't actually send email)
    $mail->smtpConnect();
    
    echo "\n✅ SUCCESS: SMTP connection established!\n";
    echo "\nConnection Details:\n";
    echo "- Host: " . SMTP_HOST . "\n";
    echo "- Port: " . SMTP_PORT . "\n";
    echo "- Encryption: " . $mail->SMTPSecure . "\n";
    echo "- Username: " . SMTP_USERNAME . "\n";
    echo "- Authentication: SUCCESS\n";
    
    $mail->smtpClose();
    
} catch (Exception $e) {
    echo "\n❌ ERROR: Failed to connect!\n";
    echo "\nError Details:\n";
    echo "==============\n";
    echo "Error Message: " . $e->getMessage() . "\n";
    echo "PHPMailer Error: " . $mail->ErrorInfo . "\n";
    echo "\nPossible Causes:\n";
    echo "1. Wrong SMTP host or port\n";
    echo "2. Wrong username or password\n";
    echo "3. Firewall blocking port " . SMTP_PORT . "\n";
    echo "4. SSL/TLS certificate issues\n";
    echo "5. Email account not activated in Hostinger\n";
    echo "6. SMTP not enabled for the account\n";
}

echo "</pre>";
?>

