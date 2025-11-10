# 🔧 حل مشكلة المصادقة مع Microsoft 365

## ❌ المشكلة الحالية:
```
535 5.7.139 Authentication unsuccessful, the request did not meet the criteria 
to be authenticated successfully. Contact your administrator.
```

## ✅ الحلول المتاحة:

### الحل 1: تفعيل SMTP Authentication (حل فوري)

**هذا هو السبب الأكثر شيوعاً!**

#### الخطوات:

1. **تسجيل الدخول إلى Office 365 Admin:**
   - اذهب إلى: https://admin.microsoft.com
   - سجل الدخول بحساب المدير

2. **تفعيل SMTP Authentication:**
   - اذهب إلى: **Users** → **Active users**
   - ابحث عن: `no-reply@yasmarina.ae`
   - اضغط على الحساب
   - اذهب إلى تبويب: **Mail**
   - اضغط على: **Manage email apps**
   - ✅ فعّل: **Authenticated SMTP**
   - اضغط: **Save changes**

3. **الانتظار:**
   - ⏰ انتظر **15 دقيقة** حتى يتم تطبيق التغييرات
   - ثم جرب إرسال بريد إلكتروني مرة أخرى

#### أو عبر Exchange Admin Center:

1. اذهب إلى: https://admin.exchange.microsoft.com
2. **Recipients** → **Mailboxes**
3. اختر: `no-reply@yasmarina.ae`
4. **Mail flow settings** → **View details**
5. ✅ فعّل: **SMTP AUTH**
6. Save

---

### الحل 2: استخدام OAuth2 (الحل الدائم - موصى به)

Microsoft بدأ في إيقاف دعم Basic Authentication (اسم المستخدم/كلمة المرور) وبدأ يوصي بـ **OAuth2**.

#### لماذا OAuth2؟
- ✅ أكثر أماناً
- ✅ متوافق مع سياسات Microsoft الحديثة
- ✅ لا يحتاج App Password
- ✅ الحل الدائم

#### الخطوات:

1. **تثبيت المكتبات:**
   ```bash
   composer install
   ```

2. **تسجيل التطبيق في Azure AD:**
   - اتبع الدليل الكامل في: `OAUTH2-SETUP-GUIDE.md`
   - أو استخدم: `oauth-setup.php` للحصول على Refresh Token

3. **تحديث config.php:**
   ```php
   define('USE_OAUTH2', true);
   define('OAUTH_CLIENT_ID', 'your-client-id');
   define('OAUTH_CLIENT_SECRET', 'your-client-secret');
   define('OAUTH_REFRESH_TOKEN', 'your-refresh-token');
   ```

4. **اختبار الإرسال**

---

## 🔍 أسباب أخرى محتملة:

### 1. كلمة المرور خاطئة
- تأكد من أن كلمة المرور `Apple@2025` صحيحة
- جرب إعادة تعيين كلمة المرور

### 2. الحساب غير موجود
- تأكد من أن `no-reply@yasmarina.ae` موجود في Office 365
- يجب أن يكون **User Mailbox** وليس Shared Mailbox

### 3. Security Defaults
- إذا كان Security Defaults مفعل، قد يحتاج إلى تعطيله
- أو استخدام OAuth2 بدلاً منه

### 4. Conditional Access Policies
- تحقق من أن لا توجد سياسات Conditional Access تمنع SMTP
- أضف استثناء إذا لزم الأمر

---

## 📋 قائمة التحقق:

### للعميل:
- [ ] الحساب `no-reply@yasmarina.ae` موجود في Office 365
- [ ] كلمة المرور صحيحة
- [ ] **SMTP Authentication مفعل** (الأهم!)
- [ ] الحساب هو User Mailbox (ليس Shared)
- [ ] انتظر 15 دقيقة بعد التفعيل
- [ ] لا توجد Conditional Access policies تمنع SMTP

### للمطور:
- [ ] `config.php` محدث بالإعدادات الصحيحة
- [ ] `send-email-smtp.php` محدث
- [ ] `DEBUG_MODE` مفعل (لرؤية الأخطاء)
- [ ] الملفات مرفوعة على السيرفر
- [ ] جرب الإرسال بعد تفعيل SMTP Auth

---

## 🎯 الحل الأسرع:

**تفعيل SMTP Authentication في Office 365 Admin:**
1. https://admin.microsoft.com
2. Users → Active users → no-reply@yasmarina.ae
3. Mail → Manage email apps
4. ✅ Authenticated SMTP
5. Save
6. ⏰ انتظر 15 دقيقة
7. ✅ جرب مرة أخرى

---

## 📞 روابط مفيدة:

- Office 365 Admin: https://admin.microsoft.com
- Exchange Admin: https://admin.exchange.microsoft.com
- Azure Portal: https://portal.azure.com
- دليل OAuth2: `OAUTH2-SETUP-GUIDE.md`

---

**ملاحظة:** إذا استمرت المشكلة بعد تفعيل SMTP Auth، فالحل الوحيد هو استخدام **OAuth2**.

