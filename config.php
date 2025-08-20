<?php
// ملف إعدادات البريد الإلكتروني
// يمكنك تعديل هذه القيم حسب احتياجاتك

// البريد الإلكتروني المستلم
define('RECIPIENT_EMAIL', 'abdelrhman.hassan510@gmail.com');

// عنوان الرسالة
define('EMAIL_SUBJECT', 'رسالة جديدة من موقع Yas Marina Rendezvous');

// إعدادات SMTP (اختياري - إذا كنت تريد استخدام PHPMailer)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // بريد المرسل
define('SMTP_PASSWORD', 'your-app-password'); // كلمة مرور التطبيق
define('SMTP_SECURE', 'tls');

// إعدادات الموقع
define('SITE_NAME', 'Yas Marina Rendezvous');
define('SITE_URL', 'https://your-domain.com');

// رسائل النجاح والخطأ
define('SUCCESS_MESSAGE', 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.');
define('ERROR_MESSAGE', 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.');
define('VALIDATION_ERROR', 'يرجى ملء جميع الحقول المطلوبة.');
define('EMAIL_VALIDATION_ERROR', 'يرجى إدخال بريد إلكتروني صحيح.');

// إعدادات الأمان
define('ENABLE_CSRF_PROTECTION', true);
define('ENABLE_RATE_LIMITING', true);
define('MAX_REQUESTS_PER_HOUR', 10);

// إعدادات التطوير
define('DEBUG_MODE', false);
define('LOG_EMAILS', true);
define('LOG_FILE', 'email_log.txt');
?>
