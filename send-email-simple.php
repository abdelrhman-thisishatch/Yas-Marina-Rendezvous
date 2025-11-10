<?php
/**
 * Simple PHP Mail Handler
 * Uses PHP native mail() function
 */

mb_internal_encoding("UTF-8");

// Set JSON header for AJAX responses
header('Content-Type: application/json; charset=utf-8');

// Load configuration
require_once 'config-hostinger.php';

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get the form fields and remove whitespace
    
    // Yacht Name
    $yachtName = isset($_POST["yachtName"]) ? trim($_POST["yachtName"]) : "";
    
    // LOA Meters
    $loaMeters = isset($_POST["loaMeters"]) ? trim($_POST["loaMeters"]) : "";
    
    // Owner Name
    $ownerName = isset($_POST["ownerName"]) ? trim($_POST["ownerName"]) : "";
    
    // Mobile Number
    $mobileNumber = isset($_POST["mobileNumber"]) ? trim($_POST["mobileNumber"]) : "";
    
    // Contact Email
    $email = filter_var(trim($_POST["contactEmail"]), FILTER_SANITIZE_EMAIL);
    
    // Check that data was sent to the mailer
    if (empty($yachtName) || empty($loaMeters) || empty($ownerName) || empty($mobileNumber) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Set a 400 (bad request) response code
        http_response_code(400);
        echo json_encode([
            'alert' => 'alert-danger',
            'message' => VALIDATION_ERROR
        ]);
        exit;
    }
    
    // Set the recipient email address
    $recipient = RECIPIENT_EMAIL;
    
    // Set the email subject
    $subject = EMAIL_SUBJECT;
    
    // Email Header
    $head = SITE_NAME . " - New Yacht Registration\r\n\r\n";
    
    // Build the email content
    $email_content = "$head\r\n";
    $email_content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n";
    $email_content .= "YACHT DETAILS\r\n";
    $email_content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\r\n";
    $email_content .= "Name of Yacht: $yachtName\r\n";
    $email_content .= "LOA (meters): $loaMeters meters\r\n\r\n";
    $email_content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n";
    $email_content .= "OWNER CONTACT DETAILS\r\n";
    $email_content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\r\n";
    $email_content .= "Name of Owner: $ownerName\r\n";
    $email_content .= "Mobile Number: $mobileNumber\r\n";
    $email_content .= "Email Address: $email\r\n\r\n";
    $email_content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n";
    $email_content .= "SUBMISSION INFO\r\n";
    $email_content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\r\n\r\n";
    $email_content .= "Submitted at: " . date('Y-m-d H:i:s') . "\r\n";
    $email_content .= "IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\r\n";
    
    // Build the email headers
    $email_headers = "From: " . SITE_NAME . " <" . SMTP_USERNAME . ">\r\n";
    $email_headers .= "Reply-To: $ownerName <$email>\r\n";
    $email_headers .= "MIME-Version: 1.0\r\n";
    $email_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $email_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    // Send the email
    if (mail($recipient, $subject, $email_content, $email_headers)) {
        // Set a 200 (okay) response code
        http_response_code(200);
        echo json_encode([
            'alert' => 'alert-success',
            'message' => SUCCESS_MESSAGE
        ]);
    } else {
        // Set a 500 (internal server error) response code
        http_response_code(500);
        echo json_encode([
            'alert' => 'alert-danger',
            'message' => ERROR_MESSAGE
        ]);
    }
    
} else {
    // Not a POST request, set a 403 (forbidden) response code
    http_response_code(403);
    echo json_encode([
        'alert' => 'alert-danger',
        'message' => 'Invalid request method.'
    ]);
}

?>

