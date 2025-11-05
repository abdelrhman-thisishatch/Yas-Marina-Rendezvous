<?php
/**
 * Email Diagnostic Tool
 * This file will help identify why emails are not being sent
 * DELETE THIS FILE AFTER USE FOR SECURITY
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Diagnostic Tool</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container { 
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { 
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
            font-size: 32px;
        }
        .section {
            margin: 25px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 5px solid #667eea;
        }
        .section h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 22px;
        }
        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label {
            font-weight: bold;
            min-width: 200px;
            color: #555;
        }
        .info-value {
            color: #333;
            flex: 1;
        }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; font-weight: bold; }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 10px 0;
            font-size: 13px;
            line-height: 1.5;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-weight: bold;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .test-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #667eea;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: black; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Email Diagnostic Tool</h1>

<?php
// Load config if exists
if (file_exists('config.php')) {
    require_once 'config.php';
    echo '<div class="alert alert-success">✅ config.php loaded successfully</div>';
} else {
    echo '<div class="alert alert-danger">❌ config.php not found</div>';
}

// 1. PHP Configuration Check
echo '<div class="section">';
echo '<h2>1️⃣ PHP Mail Configuration</h2>';

echo '<div class="info-row">';
echo '<div class="info-label">PHP Version:</div>';
echo '<div class="info-value">' . phpversion() . '</div>';
echo '</div>';

echo '<div class="info-row">';
echo '<div class="info-label">mail() function:</div>';
if (function_exists('mail')) {
    echo '<div class="info-value"><span class="success">✅ Available</span></div>';
} else {
    echo '<div class="info-value"><span class="error">❌ Not Available</span></div>';
}
echo '</div>';

$mailSettings = [
    'sendmail_path' => ini_get('sendmail_path'),
    'SMTP' => ini_get('SMTP'),
    'smtp_port' => ini_get('smtp_port'),
    'sendmail_from' => ini_get('sendmail_from'),
    'disable_functions' => ini_get('disable_functions')
];

foreach ($mailSettings as $key => $value) {
    echo '<div class="info-row">';
    echo '<div class="info-label">' . $key . ':</div>';
    echo '<div class="info-value">' . ($value ? $value : '<span class="warning">Not Set</span>') . '</div>';
    echo '</div>';
}

echo '</div>';

// 2. Server Information
echo '<div class="section">';
echo '<h2>2️⃣ Server Information</h2>';

$serverInfo = [
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'Server Name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
    'Server IP' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'Current Directory' => __DIR__
];

foreach ($serverInfo as $key => $value) {
    echo '<div class="info-row">';
    echo '<div class="info-label">' . $key . ':</div>';
    echo '<div class="info-value">' . htmlspecialchars($value) . '</div>';
    echo '</div>';
}

echo '</div>';

// 3. Email Account Test Form
echo '<div class="section">';
echo '<h2>3️⃣ Test Email Sending</h2>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    echo '<div class="test-form">';
    echo '<h3>Test Results:</h3>';
    
    $testTo = $_POST['test_to'] ?? (defined('RECIPIENT_EMAIL') ? RECIPIENT_EMAIL : 'samer.eladem@yasmarina.ae');
    $testFrom = $_POST['test_from'] ?? 'no-reply@yasmarina.ae';
    $testSubject = "Email Test - " . date('Y-m-d H:i:s');
    $testMessage = "This is a test email from the diagnostic tool.\n\n";
    $testMessage .= "Server: " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "\n";
    $testMessage .= "Time: " . date('Y-m-d H:i:s') . "\n";
    $testMessage .= "PHP Version: " . phpversion() . "\n";
    
    echo '<p><strong>Testing email from:</strong> ' . htmlspecialchars($testFrom) . '</p>';
    echo '<p><strong>Testing email to:</strong> ' . htmlspecialchars($testTo) . '</p>';
    echo '<hr style="margin: 20px 0;">';
    
    // Test 1: With -f parameter
    echo '<h4>🧪 Test 1: With -f parameter</h4>';
    $headers1 = "From: Yas Marina Rendezvous <{$testFrom}>\r\n";
    $headers1 .= "Reply-To: {$testFrom}\r\n";
    $headers1 .= "MIME-Version: 1.0\r\n";
    $headers1 .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers1 .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    $additionalParams = "-f" . $testFrom;
    $result1 = @mail($testTo, $testSubject . " (Test 1)", $testMessage . "Test Method: With -f parameter", $headers1, $additionalParams);
    
    if ($result1) {
        echo '<p class="success">✅ Test 1 PASSED - Email sent with -f parameter</p>';
    } else {
        echo '<p class="error">❌ Test 1 FAILED - Could not send with -f parameter</p>';
        $error = error_get_last();
        if ($error) {
            echo '<pre>' . htmlspecialchars(print_r($error, true)) . '</pre>';
        }
    }
    
    // Test 2: Without -f parameter
    echo '<h4>🧪 Test 2: Without -f parameter</h4>';
    $result2 = @mail($testTo, $testSubject . " (Test 2)", $testMessage . "Test Method: Without -f parameter", $headers1);
    
    if ($result2) {
        echo '<p class="success">✅ Test 2 PASSED - Email sent without -f parameter</p>';
    } else {
        echo '<p class="error">❌ Test 2 FAILED - Could not send without -f parameter</p>';
        $error = error_get_last();
        if ($error) {
            echo '<pre>' . htmlspecialchars(print_r($error, true)) . '</pre>';
        }
    }
    
    // Test 3: Simple headers
    echo '<h4>🧪 Test 3: Simple headers</h4>';
    $headers3 = "From: {$testFrom}\r\n";
    $headers3 .= "Reply-To: {$testFrom}\r\n";
    $headers3 .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    $result3 = @mail($testTo, $testSubject . " (Test 3)", $testMessage . "Test Method: Simple headers", $headers3);
    
    if ($result3) {
        echo '<p class="success">✅ Test 3 PASSED - Email sent with simple headers</p>';
    } else {
        echo '<p class="error">❌ Test 3 FAILED - Could not send with simple headers</p>';
        $error = error_get_last();
        if ($error) {
            echo '<pre>' . htmlspecialchars(print_r($error, true)) . '</pre>';
        }
    }
    
    // Summary
    echo '<hr style="margin: 20px 0;">';
    echo '<h4>📊 Summary:</h4>';
    $passed = ($result1 ? 1 : 0) + ($result2 ? 1 : 0) + ($result3 ? 1 : 0);
    
    if ($passed > 0) {
        echo '<div class="alert alert-success">';
        echo "✅ {$passed} out of 3 tests passed!<br>";
        echo "Please check your inbox (and spam folder) at: <strong>{$testTo}</strong>";
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger">';
        echo '❌ All tests failed. Possible reasons:<br>';
        echo '1. The mail() function is disabled on your server<br>';
        echo '2. The email account ' . htmlspecialchars($testFrom) . ' does not exist in cPanel<br>';
        echo '3. Your hosting provider requires SMTP authentication<br>';
        echo '4. Sendmail is not configured properly<br><br>';
        echo '<strong>Recommended Action:</strong> Contact your hosting provider or use PHPMailer with SMTP.';
        echo '</div>';
    }
    
    echo '</div>';
}

// Email test form
echo '<div class="test-form">';
echo '<form method="POST">';
echo '<div class="form-group">';
echo '<label>From Email (must exist in cPanel):</label>';
echo '<input type="email" name="test_from" value="no-reply@yasmarina.ae" required>';
echo '</div>';

echo '<div class="form-group">';
echo '<label>To Email (where to receive test):</label>';
$defaultTo = defined('RECIPIENT_EMAIL') ? RECIPIENT_EMAIL : 'samer.eladem@yasmarina.ae';
echo '<input type="email" name="test_to" value="' . htmlspecialchars($defaultTo) . '" required>';
echo '</div>';

echo '<button type="submit" name="test_email" class="btn">🚀 Run Email Tests</button>';
echo '</form>';
echo '</div>';

echo '</div>';

// 4. Configuration Check
if (defined('RECIPIENT_EMAIL')) {
    echo '<div class="section">';
    echo '<h2>4️⃣ Configuration Check</h2>';
    
    $configs = [
        'RECIPIENT_EMAIL' => RECIPIENT_EMAIL ?? 'Not Set',
        'EMAIL_SUBJECT' => EMAIL_SUBJECT ?? 'Not Set',
        'SITE_NAME' => SITE_NAME ?? 'Not Set',
        'LOG_EMAILS' => LOG_EMAILS ? 'Enabled' : 'Disabled',
        'LOG_FILE' => LOG_FILE ?? 'Not Set'
    ];
    
    foreach ($configs as $key => $value) {
        echo '<div class="info-row">';
        echo '<div class="info-label">' . $key . ':</div>';
        echo '<div class="info-value">' . htmlspecialchars($value) . '</div>';
        echo '</div>';
    }
    
    // Check if log file exists
    if (defined('LOG_FILE') && file_exists(LOG_FILE)) {
        echo '<div class="info-row">';
        echo '<div class="info-label">Log File Status:</div>';
        echo '<div class="info-value"><span class="success">✅ Exists</span> ';
        echo '<a href="?view_log=1" style="color: #667eea;">View Last 20 Lines</a></div>';
        echo '</div>';
    }
    
    echo '</div>';
}

// 5. View Log
if (isset($_GET['view_log']) && defined('LOG_FILE') && file_exists(LOG_FILE)) {
    echo '<div class="section">';
    echo '<h2>5️⃣ Email Log (Last 20 lines)</h2>';
    $logContent = file(LOG_FILE);
    $lastLines = array_slice($logContent, -20);
    echo '<pre>' . htmlspecialchars(implode('', $lastLines)) . '</pre>';
    echo '</div>';
}

// 6. File Check
echo '<div class="section">';
echo '<h2>6️⃣ File Integrity Check</h2>';

$files = [
    'config.php' => 'Configuration file',
    'send-email-enhanced.php' => 'Main email handler',
    'send-email-smtp.php' => 'SMTP fallback handler',
    'index.html' => 'Main page'
];

foreach ($files as $file => $description) {
    echo '<div class="info-row">';
    echo '<div class="info-label">' . $file . ':</div>';
    echo '<div class="info-value">';
    if (file_exists($file)) {
        echo '<span class="badge badge-success">Exists</span> ';
        if (is_readable($file)) {
            echo '<span class="badge badge-success">Readable</span> ';
        } else {
            echo '<span class="badge badge-danger">Not Readable</span> ';
        }
        echo '<span style="color: #999; font-size: 12px;">(' . filesize($file) . ' bytes)</span>';
    } else {
        echo '<span class="badge badge-danger">Missing</span>';
    }
    echo '</div>';
    echo '</div>';
}

echo '</div>';

// 7. Recommendations
echo '<div class="section">';
echo '<h2>7️⃣ Recommendations</h2>';
echo '<ol style="line-height: 2;">';
echo '<li><strong>Create the email account</strong> <code>no-reply@yasmarina.ae</code> in cPanel → Email Accounts</li>';
echo '<li>Run the email tests above and check your inbox (including spam folder)</li>';
echo '<li>If all tests fail, contact your hosting provider to enable mail() function</li>';
echo '<li>As an alternative, use SMTP with PHPMailer (more reliable on shared hosting)</li>';
echo '<li><strong class="error">⚠️ DELETE THIS FILE after testing for security reasons!</strong></li>';
echo '</ol>';
echo '</div>';

?>

        <div class="alert alert-danger" style="margin-top: 30px;">
            <h3 style="margin-bottom: 10px;">⚠️ SECURITY WARNING</h3>
            <p><strong>DELETE THIS FILE (diagnose-email.php) IMMEDIATELY AFTER USE!</strong></p>
            <p>This file exposes sensitive server information and should not be accessible to the public.</p>
        </div>

    </div>
</body>
</html>

