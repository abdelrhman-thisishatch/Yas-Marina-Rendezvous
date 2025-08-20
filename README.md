# نظام إرسال البريد الإلكتروني - Yas Marina Rendezvous

## نظرة عامة
تم إعداد نظام إرسال البريد الإلكتروني لإرسال البيانات المدخلة في نموذج الاتصال إلى البريد الإلكتروني: `abdelrhman.hassan510@gmail.com`

## الملفات المطلوبة
1. **`send-email.php`** - ملف معالجة إرسال البريد الإلكتروني
2. **`index.html`** - النموذج المعدل مع الإعدادات الصحيحة
3. **`css/style.css`** - الأنماط المضافة لرسائل النجاح والخطأ

## المتطلبات
- خادم ويب يدعم PHP (مثل Apache, Nginx)
- تفعيل وظيفة `mail()` في PHP
- أو استخدام مكتبة PHPMailer لإرسال البريد عبر SMTP

## كيفية العمل
1. **إدخال البيانات**: المستخدم يملأ النموذج (الاسم، البريد الإلكتروني، الاستفسار)
2. **التحقق من صحة البيانات**: يتم التحقق من أن جميع الحقول مملوءة والبريد الإلكتروني صحيح
3. **إرسال البيانات**: يتم إرسال البيانات إلى `send-email.php`
4. **معالجة البيانات**: يتم إرسال رسالة بريد إلكتروني إلى `abdelrhman.hassan510@gmail.com`
5. **عرض النتيجة**: يتم عرض رسالة نجاح أو خطأ للمستخدم

## الميزات
- ✅ التحقق من صحة البيانات
- ✅ رسائل خطأ واضحة
- ✅ تصميم متجاوب
- ✅ دعم اللغة العربية
- ✅ رسائل نجاح وخطأ جميلة

## استكشاف الأخطاء
إذا لم يعمل إرسال البريد الإلكتروني:

### 1. التحقق من إعدادات PHP
```bash
# في ملف php.ini
sendmail_path = /usr/sbin/sendmail -t -i
```

### 2. استخدام PHPMailer بدلاً من mail()
إذا لم تعمل وظيفة `mail()`، يمكن استخدام PHPMailer:

```php
// تثبيت PHPMailer
composer require phpmailer/phpmailer

// استخدام PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

### 3. التحقق من سجلات الخطأ
```bash
# في ملف error.log
tail -f /var/log/apache2/error.log
```

## الأمان
- تم إضافة التحقق من صحة البيانات
- تم إضافة حماية ضد طلبات GET
- تم إضافة التحقق من صحة البريد الإلكتروني

## الدعم
للمساعدة أو الاستفسارات، يرجى التواصل مع:
- البريد الإلكتروني: abdelrhman.hassan510@gmail.com

## الترخيص
هذا المشروع مملوك لـ Yas Marina Rendezvous
