<?php
/**
 * ملف اختبار إرسال البريد الإلكتروني
 * استخدم هذا الملف للتأكد من أن إعدادات البريد تعمل بشكل صحيح
 * 
 * تحذير: احذف هذا الملف بعد الانتهاء من الاختبار
 */

// تفعيل عرض الأخطاء للتشخيص
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html lang='ar' dir='rtl'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>اختبار إرسال البريد الإلكتروني</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }";
echo ".section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-right: 4px solid #007bff; }";
echo ".success { color: #28a745; font-weight: bold; }";
echo ".error { color: #dc3545; font-weight: bold; }";
echo ".info { color: #17a2b8; }";
echo "pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; }";
echo ".warning { background: #fff3cd; border-right-color: #ffc107; padding: 15px; margin: 20px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";

echo "<h1>🔍 اختبار إعدادات البريد الإلكتروني</h1>";

// 1. فحص إعدادات PHP
echo "<div class='section'>";
echo "<h2>1️⃣ إعدادات PHP</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>mail() function:</strong> ";
if (function_exists('mail')) {
    echo "<span class='success'>✅ متاحة</span>";
} else {
    echo "<span class='error'>❌ غير متاحة</span>";
}
echo "</p>";

echo "<p><strong>sendmail_path:</strong> " . ini_get('sendmail_path') . "</p>";
echo "<p><strong>SMTP:</strong> " . ini_get('SMTP') . "</p>";
echo "<p><strong>smtp_port:</strong> " . ini_get('smtp_port') . "</p>";
echo "</div>";

// 2. اختبار إرسال بريد تجريبي
echo "<div class='section'>";
echo "<h2>2️⃣ اختبار إرسال البريد</h2>";

$testEmail = "samer.eladem@yasmarina.ae"; // البريد المستهدف للاختبار
$fromEmail = "no-reply@yasmarina.ae";
$subject = "Test Email from Yas Marina Rendezvous - " . date('Y-m-d H:i:s');
$message = "This is a test email to verify that the mail configuration is working correctly.\n\n";
$message .= "Server: " . $_SERVER['SERVER_NAME'] . "\n";
$message .= "IP: " . $_SERVER['SERVER_ADDR'] . "\n";
$message .= "Time: " . date('Y-m-d H:i:s') . "\n";

$headers = "From: Yas Marina Rendezvous <" . $fromEmail . ">\r\n";
$headers .= "Reply-To: " . $fromEmail . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$additionalParams = "-f" . $fromEmail;

echo "<p><strong>Sending test email to:</strong> $testEmail</p>";
echo "<p><strong>From:</strong> $fromEmail</p>";

// محاولة إرسال البريد
$result = @mail($testEmail, $subject, $message, $headers, $additionalParams);

if ($result) {
    echo "<p class='success'>✅ تم إرسال البريد بنجاح!</p>";
    echo "<p class='info'>يرجى التحقق من صندوق البريد الوارد أو البريد المزعج (Spam)</p>";
} else {
    echo "<p class='error'>❌ فشل إرسال البريد</p>";
    $error = error_get_last();
    if ($error) {
        echo "<p class='error'>Error: " . htmlspecialchars($error['message']) . "</p>";
    }
}
echo "</div>";

// 3. معلومات السيرفر
echo "<div class='section'>";
echo "<h2>3️⃣ معلومات السيرفر</h2>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Server Name:</strong> " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Path:</strong> " . __DIR__ . "</p>";
echo "</div>";

// 4. فحص ملفات التكوين
echo "<div class='section'>";
echo "<h2>4️⃣ فحص الملفات</h2>";

$files = ['config.php', 'send-email-enhanced.php', 'send-email-smtp.php'];
foreach ($files as $file) {
    echo "<p><strong>$file:</strong> ";
    if (file_exists($file)) {
        echo "<span class='success'>✅ موجود</span>";
        if (is_readable($file)) {
            echo " <span class='info'>(قابل للقراءة)</span>";
        } else {
            echo " <span class='error'>(غير قابل للقراءة)</span>";
        }
    } else {
        echo "<span class='error'>❌ غير موجود</span>";
    }
    echo "</p>";
}
echo "</div>";

// 5. توصيات
echo "<div class='section'>";
echo "<h2>5️⃣ التوصيات</h2>";
echo "<ul>";
echo "<li>إذا لم يصل البريد، تحقق من مجلد Spam/Junk</li>";
echo "<li>تأكد من أن البريد <strong>no-reply@yasmarina.ae</strong> موجود في حساب cPanel</li>";
echo "<li>إذا استمرت المشكلة، تواصل مع الدعم الفني للسيرفر</li>";
echo "<li>يمكنك استخدام PHPMailer مع SMTP كبديل أكثر موثوقية</li>";
echo "<li><strong class='error'>⚠️ احذف هذا الملف بعد الانتهاء من الاختبار لأسباب أمنية</strong></li>";
echo "</ul>";
echo "</div>";

// 6. اختبار PHPMailer (إذا كان متاحاً)
if (file_exists('PHPMailer/PHPMailer.php')) {
    echo "<div class='section'>";
    echo "<h2>6️⃣ PHPMailer</h2>";
    echo "<p class='success'>✅ PHPMailer متاح</p>";
    echo "<p class='info'>يمكنك استخدام send-email-smtp.php للإرسال عبر SMTP</p>";
    echo "</div>";
}

echo "<div class='warning'>";
echo "<h3>⚠️ تحذير أمني</h3>";
echo "<p><strong>احذف هذا الملف (test-email.php) فوراً بعد الانتهاء من الاختبار!</strong></p>";
echo "<p>هذا الملف يعرض معلومات حساسة عن السيرفر ولا يجب أن يكون متاحاً للعامة.</p>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>

