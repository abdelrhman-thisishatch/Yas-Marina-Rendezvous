<?php
$to      = 'abdelrhman@thisishatch.com';
$subject = 'New Yacht Registration - Yas Marina Rendezvous';
$message = 'Hello, this is a test message';
$headers = "From: no-reply@yasmarina.ae\r\n" .
           "Reply-To: no-reply@yasmarina.ae\r\n" .
           "Content-Type: text/plain; charset=UTF-8";

if (mail($to, $subject, $message, $headers)) {
    echo 'Email sent successfully';
} else {
    echo 'Email sending failed';
}
?>