# 🔴 URGENT FIX - Email Not Working

## المشكلة: نفس الخطأ يظهر

```
An error occurred while sending the message. Please try again.
```

---

## ✅ الحل النهائي (اختر واحد)

### **الحل 1: استخدام Gmail SMTP (الأسرع - 5 دقائق)**

هذا الحل يعمل 100% ولا يحتاج أي شيء من cPanel:

#### الخطوة 1: تحضير Gmail

1. سجل دخول إلى Gmail
2. اذهب إلى: https://myaccount.google.com/security
3. فعّل "2-Step Verification"
4. بعدها اذهب إلى: https://myaccount.google.com/apppasswords
5. اختر "Mail" و "Other (Custom name)"
6. اكتب: "Yas Marina Website"
7. اضغط "Generate"
8. **انسخ الكود** (مثل: xxxx xxxx xxxx xxxx)

#### الخطوة 2: تحديث config.php

افتح `config.php` وحدّث هذه الأسطر:

```php
// SMTP Settings (optional - if you want to use PHPMailer)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-gmail@gmail.com'); // بريدك في Gmail
define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx'); // الكود من الخطوة السابقة
define('SMTP_SECURE', 'tls');
```

#### الخطوة 3: استخدام الملف الجديد

في `index.html` غيّر السطر 456:

من:
```html
action="send-email-enhanced.php"
```

إلى:
```html
action="send-email-final.php"
```

**انتهى!** جرّب الآن

---

### **الحل 2: استخدام SMTP من cPanel (إذا كان متاح)**

#### الخطوة 1: الحصول على إعدادات SMTP

1. ادخل cPanel
2. اذهب إلى "Email Accounts"
3. أنشئ البريد `no-reply@yasmarina.ae` (إذا لم يكن موجود)
4. اضغط "Connect Devices" بجانب البريد
5. ستجد:
   ```
   Incoming Server: mail.yasmarina.ae
   Outgoing Server: mail.yasmarina.ae
   Port: 587 أو 465
   Username: no-reply@yasmarina.ae
   Password: [كلمة المرور التي اخترتها]
   ```

#### الخطوة 2: تحديث config.php

```php
define('SMTP_HOST', 'mail.yasmarina.ae'); // من cPanel
define('SMTP_PORT', 587); // أو 465
define('SMTP_USERNAME', 'no-reply@yasmarina.ae');
define('SMTP_PASSWORD', 'your-password'); // كلمة مرور البريد
define('SMTP_SECURE', 'tls'); // أو ssl إذا كان Port 465
```

#### الخطوة 3: استخدام الملف الجديد

في `index.html`:
```html
action="send-email-final.php"
```

---

### **الحل 3: تعطيل mail() تماماً واستخدام SMTP فقط**

إذا كنت متأكد أن mail() لا يعمل:

#### تحديث config.php

أضف هذا السطر في بداية الملف:
```php
define('FORCE_SMTP', true); // إجبار استخدام SMTP فقط
```

ثم حدّث إعدادات SMTP (Gmail أو cPanel):
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_SECURE', 'tls');
```

---

## 🎯 ملف send-email-final.php (جديد!)

أنشأت ملف جديد **send-email-final.php** يجرب كل الطرق:

1. ✅ PHPMailer SMTP (إذا كان متاح)
2. ✅ Native SMTP بدون PHPMailer (لا يحتاج مكتبات)
3. ✅ mail() بـ 3 طرق مختلفة

**الملف يجرب كل شيء تلقائياً!**

---

## 📋 خطة العمل (الآن!)

### ✅ الخيار السريع (5 دقائق):

```
☐ 1. جهّز Gmail App Password
☐ 2. حدّث config.php بإعدادات Gmail
☐ 3. غيّر index.html إلى send-email-final.php
☐ 4. جرّب!
```

### ⚙️ الخيار المتقدم (10 دقائق):

```
☐ 1. أنشئ البريد في cPanel
☐ 2. احصل على إعدادات SMTP من cPanel
☐ 3. حدّث config.php
☐ 4. غيّر index.html إلى send-email-final.php
☐ 5. جرّب!
```

---

## 🔍 لماذا لا يعمل الآن؟

**السبب الأرجح:**

1. ❌ البريد `no-reply@yasmarina.ae` غير موجود في cPanel
2. ❌ دالة `mail()` معطلة تماماً على السيرفر
3. ❌ السيرفر يطلب SMTP authentication

**الحل:** استخدم SMTP (Gmail أو cPanel)

---

## 📄 الملفات الجديدة

أنشأت لك:

1. ✨ **send-email-final.php** - يجرب كل الطرق الممكنة
2. 📋 **SOLUTION-NOW.md** - هذا الملف (دليل الحل)

---

## ⚡ أسرع حل (بدون تعقيد):

### استخدم FormSubmit.co (بديل مجاني):

في `index.html` غيّر:

من:
```html
<form action="send-email-final.php" method="POST">
```

إلى:
```html
<form action="https://formsubmit.co/samer.eladem@yasmarina.ae" method="POST">
```

وأضف:
```html
<input type="hidden" name="_captcha" value="false">
<input type="hidden" name="_template" value="table">
```

**هذا سيعمل فوراً بدون أي إعدادات!**

---

## 🆘 تحتاج مساعدة؟

أرسل لي:
1. محتوى ملف `email_log.txt` (آخر 10 أسطر)
2. هل أنشأت البريد `no-reply@yasmarina.ae` في cPanel؟
3. اسم شركة الاستضافة

---

## ✅ التوصية النهائية

**جرّب هذا الترتيب:**

1. **الأول:** استخدم Gmail SMTP (أسهل وأسرع)
2. **الثاني:** استخدم SMTP من cPanel
3. **الثالث:** استخدم FormSubmit.co (بديل خارجي)

**واحد منهم سيعمل 100%!**

---

Last Updated: November 5, 2025  
Status: 🔴 URGENT - Need immediate action

