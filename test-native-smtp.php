<?php
/**
 * Test PHP Native SMTP Connection
 * Simple test script to verify SMTP connection
 */

require_once 'config-hostinger.php';

echo "<h2>PHP Native SMTP Test</h2>";
echo "<pre style='background: #f5f5f5; padding: 20px; border-radius: 5px; font-family: monospace;'>";

echo "Configuration:\n";
echo "==============\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "Username: " . SMTP_USERNAME . "\n";
echo "Password: " . str_repeat('*', strlen(SMTP_PASSWORD)) . "\n";
echo "\n";

// Test connection
$host = SMTP_HOST;
$port = SMTP_PORT;
$user = SMTP_USERNAME;
$pass = SMTP_PASSWORD;

try {
    echo "Connecting to $host:$port...\n";
    echo "============================\n\n";
    
    // Determine connection string
    if ($port == 465) {
        $connectionString = "ssl://$host";
        echo "Using SSL connection (port 465)\n";
    } else {
        $connectionString = $host;
        echo "Using connection: $connectionString\n";
    }
    
    // Open connection
    $fp = @fsockopen($connectionString, $port, $errno, $errstr, 30);
    
    if (!$fp) {
        die("❌ Connection failed: $errstr ($errno)\n\nPossible causes:\n- Port $port is blocked\n- SSL not supported\n- Wrong host");
    }
    
    echo "✅ Connection established!\n\n";
    
    // Helper functions
    function readLine($fp) {
        $line = fgets($fp, 512);
        echo "SERVER: " . trim($line) . "\n";
        return $line;
    }
    
    function writeLine($fp, $cmd) {
        fwrite($fp, $cmd . "\r\n");
        echo "CLIENT: " . trim($cmd) . "\n";
        $response = readLine($fp);
        return $response;
    }
    
    // Read greeting
    echo "\n--- Server Greeting ---\n";
    $greeting = readLine($fp);
    
    if (substr($greeting, 0, 3) != '220') {
        fclose($fp);
        die("\n❌ Invalid server greeting: $greeting");
    }
    
    // Send EHLO
    echo "\n--- EHLO Command ---\n";
    $serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
    $ehloResponse = writeLine($fp, "EHLO $serverName");
    
    // For port 587, STARTTLS
    if ($port == 587) {
        echo "\n--- STARTTLS Command ---\n";
        $starttlsResponse = writeLine($fp, "STARTTLS");
        if (substr($starttlsResponse, 0, 3) == '220') {
            if (stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                echo "✅ TLS encryption enabled\n";
                writeLine($fp, "EHLO $serverName");
            } else {
                die("\n❌ Failed to enable TLS encryption");
            }
        }
    }
    
    // Authenticate
    echo "\n--- Authentication ---\n";
    $authResponse = writeLine($fp, "AUTH LOGIN");
    
    if (substr($authResponse, 0, 3) != '334') {
        fclose($fp);
        die("\n❌ AUTH LOGIN failed: $authResponse");
    }
    
    echo "\n--- Sending Username ---\n";
    $userResponse = writeLine($fp, base64_encode($user));
    
    if (substr($userResponse, 0, 3) != '334') {
        fclose($fp);
        die("\n❌ Username rejected: $userResponse");
    }
    
    echo "\n--- Sending Password ---\n";
    $passResponse = writeLine($fp, base64_encode($pass));
    
    if (substr($passResponse, 0, 3) == '235') {
        echo "\n✅✅✅ AUTHENTICATION SUCCESSFUL! ✅✅✅\n";
        echo "\nSMTP connection is working correctly!\n";
        echo "You can now use send-email-native.php\n";
    } else {
        echo "\n❌❌❌ AUTHENTICATION FAILED ❌❌❌\n";
        echo "\nError: $passResponse\n";
        echo "\nPossible causes:\n";
        echo "1. Wrong username or password\n";
        echo "2. Account not activated in Hostinger\n";
        echo "3. SMTP not enabled for the account\n";
        echo "4. Password contains special characters causing issues\n";
    }
    
    // Quit
    echo "\n--- Quitting ---\n";
    writeLine($fp, "QUIT");
    fclose($fp);
    
} catch (Exception $e) {
    echo "\n❌ Exception: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>

