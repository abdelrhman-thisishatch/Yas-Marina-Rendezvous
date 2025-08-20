<?php
// إعدادات البريد الإلكتروني
$to_email = "abdelrhman.hassan510@gmail.com";
$subject = "رسالة جديدة من موقع Yas Marina Rendezvous";

// التحقق من أن الطلب POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // الحصول على البيانات من النموذج
    $name = isset($_POST['contactName']) ? trim($_POST['contactName']) : '';
    $email = isset($_POST['contactEmail']) ? trim($_POST['contactEmail']) : '';
    $enquiry = isset($_POST['contactEnquiry']) ? trim($_POST['contactEnquiry']) : '';
    
    // التحقق من صحة البيانات
    if (empty($name) || empty($email) || empty($enquiry)) {
        $response = array(
            'alert' => 'alert-danger',
            'message' => 'يرجى ملء جميع الحقول المطلوبة.'
        );
        echo json_encode($response);
        exit;
    }
    
    // التحقق من صحة البريد الإلكتروني
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = array(
            'alert' => 'alert-danger',
            'message' => 'يرجى إدخال بريد إلكتروني صحيح.'
        );
        echo json_encode($response);
        exit;
    }
    
    // حماية ضد حقن HTML
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $enquiry = htmlspecialchars($enquiry, ENT_QUOTES, 'UTF-8');
    
    // إنشاء محتوى الرسالة
    $message = "معلومات جديدة من موقع Yas Marina Rendezvous:\n\n";
    $message .= "الاسم: " . $name . "\n";
    $message .= "البريد الإلكتروني: " . $email . "\n";
    $message .= "الاستفسار: " . $enquiry . "\n\n";
    $message .= "تم إرسال هذه الرسالة في: " . date('Y-m-d H:i:s') . "\n";
    
    // محاولة إرسال البريد الإلكتروني باستخدام وظيفة mail()
    $headers = "From: " . $email . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    if (mail($to_email, $subject, $message, $headers)) {
        $response = array(
            'alert' => 'alert-success',
            'message' => 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.'
        );
    } else {
        // إذا فشل mail()، حاول استخدام PHPMailer
        $response = sendWithPHPMailer($to_email, $subject, $message, $name, $from_email);
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
}

/**
 * دالة إرسال البريد باستخدام PHPMailer
 */
function sendWithPHPMailer($to_email, $subject, $message, $name, $from_email) {
    // التحقق من وجود PHPMailer
    if (!file_exists('PHPMailer/PHPMailer.php')) {
        return array(
            'alert' => 'alert-danger',
            'message' => 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.'
        );
    }
    
    try {
        require_once 'PHPMailer/PHPMailer.php';
        require_once 'PHPMailer/SMTP.php';
        require_once 'PHPMailer/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // إعدادات SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // يمكن تغييرها حسب مزود البريد
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; // بريد المرسل
        $mail->Password = 'your-app-password'; // كلمة مرور التطبيق
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        // إعدادات المرسل والمستقبل
        $mail->setFrom($from_email, $name);
        $mail->addAddress($to_email);
        $mail->addReplyTo($from_email, $name);
        
        // محتوى الرسالة
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        $mail->send();
        
        return array(
            'alert' => 'alert-success',
            'message' => 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.'
        );
        
    } catch (Exception $e) {
        return array(
            'alert' => 'alert-danger',
            'message' => 'حدث خطأ أثناء إرسال الرسالة: ' . $mail->ErrorInfo
        );
    }
}
?>
