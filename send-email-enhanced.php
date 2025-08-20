<?php
// تضمين ملف التكوين
require_once 'config.php';

// بدء الجلسة للأمان
session_start();

// دالة تسجيل الأحداث
function logEvent($message) {
    if (LOG_EMAILS) {
        $logEntry = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
        file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// دالة التحقق من معدل الطلبات
function checkRateLimit($ip) {
    if (!ENABLE_RATE_LIMITING) return true;
    
    $currentTime = time();
    $hourAgo = $currentTime - 3600;
    
    if (!isset($_SESSION['email_requests'])) {
        $_SESSION['email_requests'] = array();
    }
    
    // إزالة الطلبات القديمة
    $_SESSION['email_requests'] = array_filter($_SESSION['email_requests'], function($time) use ($hourAgo) {
        return $time > $hourAgo;
    });
    
    // التحقق من عدد الطلبات
    if (count($_SESSION['email_requests']) >= MAX_REQUESTS_PER_HOUR) {
        return false;
    }
    
    // إضافة الطلب الحالي
    $_SESSION['email_requests'][] = $currentTime;
    return true;
}

// دالة إنشاء رمز CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// دالة التحقق من رمز CSRF
function verifyCSRFToken($token) {
    if (!ENABLE_CSRF_PROTECTION) return true;
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// التحقق من أن الطلب POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // التحقق من معدل الطلبات
    $clientIP = $_SERVER['REMOTE_ADDR'];
    if (!checkRateLimit($clientIP)) {
        $response = array(
            'alert' => 'alert-danger',
            'message' => 'لقد تجاوزت الحد الأقصى للطلبات. يرجى المحاولة بعد ساعة.'
        );
        echo json_encode($response);
        logEvent("Rate limit exceeded for IP: $clientIP");
        exit;
    }
    
    // التحقق من رمز CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $response = array(
            'alert' => 'alert-danger',
            'message' => 'رمز الأمان غير صحيح. يرجى تحديث الصفحة والمحاولة مرة أخرى.'
        );
        echo json_encode($response);
        logEvent("CSRF token verification failed for IP: $clientIP");
        exit;
    }
    
    // الحصول على البيانات من النموذج
    $name = isset($_POST['contactName']) ? trim($_POST['contactName']) : '';
    $email = isset($_POST['contactEmail']) ? trim($_POST['contactEmail']) : '';
    $enquiry = isset($_POST['contactEnquiry']) ? trim($_POST['contactEnquiry']) : '';
    
    // التحقق من صحة البيانات
    if (empty($name) || empty($email) || empty($enquiry)) {
        $response = array(
            'alert' => 'alert-danger',
            'message' => VALIDATION_ERROR
        );
        echo json_encode($response);
        logEvent("Validation failed for IP: $clientIP - Empty fields");
        exit;
    }
    
    // التحقق من صحة البريد الإلكتروني
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = array(
            'alert' => 'alert-danger',
            'message' => EMAIL_VALIDATION_ERROR
        );
        echo json_encode($response);
        logEvent("Validation failed for IP: $clientIP - Invalid email: $email");
        exit;
    }
    
    // حماية ضد حقن HTML
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $enquiry = htmlspecialchars($enquiry, ENT_QUOTES, 'UTF-8');
    
    // إنشاء محتوى الرسالة
    $message = "معلومات جديدة من موقع " . SITE_NAME . ":\n\n";
    $message .= "الاسم: " . $name . "\n";
    $message .= "البريد الإلكتروني: " . $email . "\n";
    $message .= "الاستفسار: " . $enquiry . "\n\n";
    $message .= "تم إرسال هذه الرسالة في: " . date('Y-m-d H:i:s') . "\n";
    $message .= "عنوان IP المرسل: " . $clientIP . "\n";
    
    // محاولة إرسال البريد الإلكتروني
    $headers = "From: " . $email . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    if (mail(RECIPIENT_EMAIL, EMAIL_SUBJECT, $message, $headers)) {
        $response = array(
            'alert' => 'alert-success',
            'message' => SUCCESS_MESSAGE
        );
        logEvent("Email sent successfully from IP: $clientIP, Email: $email");
    } else {
        $response = array(
            'alert' => 'alert-danger',
            'message' => ERROR_MESSAGE
        );
        logEvent("Email sending failed for IP: $clientIP, Email: $email");
    }
    
    // إرجاع الاستجابة كـ JSON
    echo json_encode($response);
    
} else {
    // إذا لم يكن الطلب POST
    $response = array(
        'alert' => 'alert-danger',
        'message' => 'طريقة طلب غير صحيحة.'
    );
    echo json_encode($response);
    logEvent("Invalid request method from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}
?>
