<?php
// إعدادات البريد الإلكتروني
$to_email = "abdelrhman.hassan510@gmail.com";
$subject = "رسالة جديدة من موقع Yas Marina Rendezvous";

// التحقق من أن الطلب POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // الحصول على البيانات من النموذج
    $name = isset($_POST['contactName']) ? $_POST['contactName'] : '';
    $email = isset($_POST['contactEmail']) ? $_POST['contactEmail'] : '';
    $enquiry = isset($_POST['contactEnquiry']) ? $_POST['contactEnquiry'] : '';
    
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
    
    // إنشاء محتوى الرسالة
    $message = "معلومات جديدة من موقع Yas Marina Rendezvous:\n\n";
    $message .= "الاسم: " . $name . "\n";
    $message .= "البريد الإلكتروني: " . $email . "\n";
    $message .= "الاستفسار: " . $enquiry . "\n\n";
    $message .= "تم إرسال هذه الرسالة في: " . date('Y-m-d H:i:s') . "\n";
    
    // إعدادات الرأس
    $headers = "From: " . $email . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // محاولة إرسال البريد الإلكتروني
    if (mail($to_email, $subject, $message, $headers)) {
        $response = array(
            'alert' => 'alert-success',
            'message' => 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.'
        );
    } else {
        $response = array(
            'alert' => 'alert-danger',
            'message' => 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.'
        );
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
?>
