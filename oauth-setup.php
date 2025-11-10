<?php
/**
 * OAuth2 Setup Helper for Microsoft 365
 * Use this file to obtain Refresh Token for OAuth2 authentication
 * 
 * IMPORTANT: Delete this file after obtaining the Refresh Token for security!
 */

session_start();

// Load configuration
require_once 'config.php';

// Check if Composer autoload exists
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die('
    <html>
    <head><title>OAuth2 Setup - Error</title></head>
    <body style="font-family: Arial; padding: 20px;">
        <h2 style="color: red;">❌ Error: Composer dependencies not installed</h2>
        <p>Please run: <code>composer install</code></p>
        <p>Or download League OAuth2 Client manually and include it.</p>
    </body>
    </html>');
}

require_once $autoloadPath;

use League\OAuth2\Client\Provider\GenericProvider;

// Check if OAuth2 is configured
if (empty(OAUTH_CLIENT_ID) || empty(OAUTH_CLIENT_SECRET)) {
    die('
    <html>
    <head><title>OAuth2 Setup - Configuration Error</title></head>
    <body style="font-family: Arial; padding: 20px;">
        <h2 style="color: red;">❌ OAuth2 Configuration Missing</h2>
        <p>Please configure OAUTH_CLIENT_ID and OAUTH_CLIENT_SECRET in config.php first.</p>
        <p>See docs/OAUTH2-SETUP-GUIDE.md for instructions.</p>
    </body>
    </html>');
}

$tenantId = !empty(OAUTH_TENANT_ID) ? OAUTH_TENANT_ID : 'common';
$redirectUri = SITE_URL . '/oauth-setup.php';

$provider = new GenericProvider([
    'clientId' => OAUTH_CLIENT_ID,
    'clientSecret' => OAUTH_CLIENT_SECRET,
    'redirectUri' => $redirectUri,
    'urlAuthorize' => "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/authorize",
    'urlAccessToken' => "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token",
    'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',
    'scopes' => ['https://outlook.office.com/SMTP.Send', 'offline_access']
]);

?>
<!DOCTYPE html>
<html>
<head>
    <title>OAuth2 Setup - Microsoft 365</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #0078d4; }
        h2 { color: #333; margin-top: 30px; }
        .success { 
            background: #d4edda; 
            border: 1px solid #c3e6cb; 
            color: #155724; 
            padding: 15px; 
            border-radius: 4px; 
            margin: 20px 0;
        }
        .warning { 
            background: #fff3cd; 
            border: 1px solid #ffeaa7; 
            color: #856404; 
            padding: 15px; 
            border-radius: 4px; 
            margin: 20px 0;
        }
        .error { 
            background: #f8d7da; 
            border: 1px solid #f5c6cb; 
            color: #721c24; 
            padding: 15px; 
            border-radius: 4px; 
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #0078d4;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 10px 0;
        }
        .btn:hover {
            background: #106ebe;
        }
        textarea {
            width: 100%;
            padding: 10px;
            font-family: monospace;
            font-size: 12px;
            border: 2px solid #0078d4;
            border-radius: 4px;
            margin: 10px 0;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .step {
            background: #e8f4f8;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #0078d4;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 OAuth2 Setup for Microsoft 365</h1>
        
        <?php
        if (!isset($_GET['code'])) {
            // Step 1: Get authorization URL
            $authorizationUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            
            ?>
            <div class="step">
                <h2>Step 1: Authorize Application</h2>
                <p>Click the button below to authorize this application to access your Microsoft 365 account.</p>
                <p><strong>You will be redirected to Microsoft login page.</strong></p>
                <a href="<?php echo htmlspecialchars($authorizationUrl); ?>" class="btn">Authorize Application</a>
            </div>
            
            <div class="warning">
                <strong>⚠️ Security Warning:</strong>
                <ul>
                    <li>Make sure you're using HTTPS</li>
                    <li>Delete this file after obtaining the Refresh Token</li>
                    <li>Keep the Refresh Token secure</li>
                </ul>
            </div>
            
            <h2>Configuration Info</h2>
            <ul>
                <li><strong>Client ID:</strong> <?php echo htmlspecialchars(substr(OAUTH_CLIENT_ID, 0, 20)) . '...'; ?></li>
                <li><strong>Redirect URI:</strong> <code><?php echo htmlspecialchars($redirectUri); ?></code></li>
                <li><strong>Tenant ID:</strong> <?php echo htmlspecialchars($tenantId); ?></li>
                <li><strong>User Email:</strong> <?php echo htmlspecialchars(OAUTH_USER_EMAIL); ?></li>
            </ul>
            
        <?php
        } else {
            // Step 2: Handle callback
            try {
                // Check state
                if (empty($_GET['state']) || (!isset($_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state'])) {
                    unset($_SESSION['oauth2state']);
                    throw new Exception('Invalid state parameter. Possible CSRF attack.');
                }
                
                unset($_SESSION['oauth2state']);
                
                // Get access token
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);
                
                $refreshToken = $accessToken->getRefreshToken();
                
                if (empty($refreshToken)) {
                    throw new Exception('No refresh token received. Make sure you requested "offline_access" scope.');
                }
                
                ?>
                <div class="success">
                    <h2>✅ Success! Refresh Token Obtained</h2>
                    <p>Copy the Refresh Token below and add it to your <code>config.php</code> file.</p>
                </div>
                
                <div class="step">
                    <h3>Refresh Token:</h3>
                    <textarea readonly><?php echo htmlspecialchars($refreshToken); ?></textarea>
                    <p><strong>⚠️ Important:</strong> Copy this token now. You won't be able to see it again!</p>
                </div>
                
                <div class="step">
                    <h3>Next Steps:</h3>
                    <ol>
                        <li>Copy the Refresh Token above</li>
                        <li>Open <code>config.php</code></li>
                        <li>Set <code>OAUTH_REFRESH_TOKEN</code> to the copied token</li>
                        <li>Set <code>USE_OAUTH2</code> to <code>true</code></li>
                        <li>Save the file</li>
                        <li><strong>Delete this file (oauth-setup.php) for security</strong></li>
                        <li>Test email sending</li>
                    </ol>
                </div>
                
                <div class="warning">
                    <strong>Security Reminder:</strong>
                    <ul>
                        <li>Delete this file immediately after copying the token</li>
                        <li>Never commit the Refresh Token to version control</li>
                        <li>Keep your config.php file secure</li>
                    </ul>
                </div>
                
                <?php
                
            } catch (Exception $e) {
                ?>
                <div class="error">
                    <h2>❌ Error</h2>
                    <p><strong>Error:</strong> <?php echo htmlspecialchars($e->getMessage()); ?></p>
                    <p>Please try again or check your configuration.</p>
                </div>
                
                <div class="step">
                    <h3>Troubleshooting:</h3>
                    <ul>
                        <li>Make sure Redirect URI in Azure Portal matches: <code><?php echo htmlspecialchars($redirectUri); ?></code></li>
                        <li>Check that you granted admin consent for permissions</li>
                        <li>Verify Client ID and Client Secret are correct</li>
                        <li>Make sure "offline_access" scope is included</li>
                    </ul>
                </div>
                <?php
            }
        }
        ?>
        
        <hr style="margin: 30px 0;">
        <p style="color: #666; font-size: 12px;">
            For detailed setup instructions, see <code>docs/OAUTH2-SETUP-GUIDE.md</code>
        </p>
    </div>
</body>
</html>

