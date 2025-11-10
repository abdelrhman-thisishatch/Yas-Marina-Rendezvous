<?php
/**
 * Hostinger SMTP Email Handler – نسخة مبسّطة
 * ---------------------------------------------------------------
 * هذا الملف يُرسل البريد ويُعيد JSON واحد فقط للمتصفّح
 * لا يوجد أي echo/print قبل أو بعد الـ JSON
 * ---------------------------------------------------------------
 */

/* ---------- إعدادات SMTP (أدخل بياناتك هنا) ---------- */
$smtpHost   = 'smtp.hostinger.com';
$smtpPort   = 465;               // 465=SSL  |  587=STARTTLS
$smtpUser   = 'no-reply@yasrendezvous.ae'; // بريدك
$smtpPass   = 'Yasrendezvous@2025!@';               // باسوورد البريد
$recipient  = 'abdelrhman@thisishatch.com';   // المستقبل
$siteName   = 'Yas Rendezvous';
/* ------------------------------------------------------- */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['alert' => 'alert-danger', 'message' => 'Method not allowed']);
    exit;
}

/* ---------- جلب البيانات ---------- */
$yachtName   = trim($_POST['yachtName']   ?? '');
$loaMeters   = trim($_POST['loaMeters']   ?? '');
$ownerName   = trim($_POST['ownerName']   ?? '');
$mobile      = trim($_POST['mobileNumber'] ?? '');
$email       = trim($_POST['contactEmail'] ?? '');

/* ---------- التحقق ---------- */
if (!$yachtName || !$loaMeters || !$ownerName || !$mobile || !$email) {
    echo json_encode(['alert' => 'alert-danger', 'message' => 'All fields are required']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['alert' => 'alert-danger', 'message' => 'Invalid email address']);
    exit;
}

/* ---------- بناء الرسالة ---------- */
$body = "
New Yacht Registration – {$siteName}\n
━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n
Yacht Name : {$yachtName}
LOA (m)    : {$loaMeters}
Owner      : {$ownerName}
Mobile     : {$mobile}
Email      : {$email}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Sent at    : " . date('Y-m-d H:i:s') . "
IP         : " . $_SERVER['REMOTE_ADDR'];

/* ---------- الإرسال بـ mail() ---------- */
$sent = mail(
    $recipient,
    'Yacht Registration – Yas Rendezvous',
    $body,
    [
        'From' => "{$siteName} <{$smtpUser}>",
        'Reply-To' => "{$ownerName} <{$email}>",
        'Content-Type' => 'text/plain; charset=UTF-8'
    ]
);

/* ---------- الرد النهائي ---------- */
if ($sent) {
    echo json_encode(['alert' => 'alert-success', 'message' => 'Thank you! Your registration has been submitted successfully.']);
} else {
    echo json_encode(['alert' => 'alert-danger', 'message' => 'Failed to send. Please try again later.']);
}
?>