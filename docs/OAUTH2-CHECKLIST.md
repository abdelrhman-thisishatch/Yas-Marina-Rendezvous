# ✅ قائمة تحقق OAuth2 - Microsoft 365

## 📋 المتطلبات الأساسية:

### 1. البيانات المطلوبة من Azure AD:

- [x] **OAUTH_CLIENT_ID** - Application (client) ID من Azure Portal
- [x] **OAUTH_CLIENT_SECRET** - Client Secret من Azure Portal
- [x] **OAUTH_REFRESH_TOKEN** - Refresh Token (يتم الحصول عليه من oauth-setup.php)
- [ ] **OAUTH_TENANT_ID** - Tenant ID (اختياري - يمكن استخدام `common`)
- [x] **OAUTH_USER_EMAIL** - موجود بالفعل: `no-reply@yasmarina.ae` ✅

---

## 🔧 المتطلبات التقنية:

### 1. تثبيت المكتبات:
```bash
composer install
```

**المكتبات المطلوبة:**
- `league/oauth2-client` (يتم تثبيتها تلقائياً عبر composer.json)

### 2. إعدادات Azure AD:

- [ ] **تسجيل التطبيق** في Azure Portal
- [ ] **إعداد Redirect URI**: `https://hatchtestserver.com/Yas-Marina-Rendezvous/oauth-callback.php`
- [ ] **إضافة الأذونات**:
  - [ ] `SMTP.Send` (Delegated permission)
  - [ ] `offline_access` (Delegated permission)
- [ ] **Grant Admin Consent** للأذونات

---

## 📝 خطوات الإعداد الكاملة:

### الخطوة 1: تسجيل التطبيق في Azure AD

1. اذهب إلى: https://portal.azure.com
2. **Azure Active Directory** → **App registrations** → **New registration**
3. أدخل:
   - **Name**: Yas Marina Email Handler
   - **Supported account types**: Accounts in this organizational directory only
   - **Redirect URI**: 
     - Type: **Web**
     - URI: `https://hatchtestserver.com/Yas-Marina-Rendezvous/oauth-callback.php`
4. اضغط **Register**
5. انسخ **Application (client) ID** → هذا هو `OAUTH_CLIENT_ID`

### الخطوة 2: إنشاء Client Secret

1. من صفحة التطبيق → **Certificates & secrets**
2. اضغط **New client secret**
3. أدخل:
   - **Description**: Email SMTP Secret
   - **Expires**: 24 months (أو حسب الحاجة)
4. اضغط **Add**
5. **انسخ القيمة فوراً** → هذا هو `OAUTH_CLIENT_SECRET` ⚠️ (لن تراه مرة أخرى!)

### الخطوة 3: إضافة الأذونات

1. من صفحة التطبيق → **API permissions**
2. اضغط **Add a permission**
3. اختر **Microsoft Graph**
4. اختر **Delegated permissions**
5. ابحث وأضف:
   - `SMTP.Send` (تحت Mail)
   - `offline_access` (تحت OpenID permissions)
6. اضغط **Add permissions**
7. **مهم جداً**: اضغط **Grant admin consent for [Your Organization]**
8. تأكد من ظهور ✅ بجانب الأذونات

### الخطوة 4: الحصول على Tenant ID (اختياري)

1. من Azure Portal → **Azure Active Directory**
2. من **Overview** → انسخ **Tenant ID**
3. هذا هو `OAUTH_TENANT_ID` (أو استخدم `common`)

### الخطوة 5: تثبيت المكتبات

```bash
cd /path/to/your/project
composer install
```

**أو إذا كان Composer غير متاح:**
- قم بتحميل `league/oauth2-client` يدوياً من: https://github.com/thephpleague/oauth2-client
- ضعها في مجلد `vendor/`

### الخطوة 6: الحصول على Refresh Token

1. تأكد من تحديث `config.php` بـ:
   ```php
   define('OAUTH_CLIENT_ID', 'your-client-id');
   define('OAUTH_CLIENT_SECRET', 'your-client-secret');
   define('OAUTH_TENANT_ID', 'your-tenant-id-or-common');
   ```

2. افتح المتصفح واذهب إلى:
   ```
   https://hatchtestserver.com/Yas-Marina-Rendezvous/oauth-setup.php
   ```

3. اتبع الخطوات:
   - اضغط على رابط التفويض
   - سجل الدخول بحساب Microsoft 365
   - وافق على الأذونات
   - انسخ Refresh Token

### الخطوة 7: تحديث config.php

```php
// OAuth2 Settings
define('USE_OAUTH2', true); // ✅ تفعيل OAuth2
define('OAUTH_CLIENT_ID', 'your-client-id-here');
define('OAUTH_CLIENT_SECRET', 'your-client-secret-here');
define('OAUTH_TENANT_ID', 'your-tenant-id-or-common');
define('OAUTH_REFRESH_TOKEN', 'your-refresh-token-here');
define('OAUTH_USER_EMAIL', 'no-reply@yasmarina.ae'); // موجود بالفعل ✅
```

### الخطوة 8: الاختبار

1. جرب إرسال بريد إلكتروني من النموذج
2. تحقق من `email_log.txt`:
   - يجب أن ترى: `🔐 Using OAuth2 authentication`
   - يجب أن ترى: `✅ Email sent successfully via SMTP`

---

## ⚠️ ملاحظات مهمة:

### 1. Redirect URI يجب أن يتطابق تماماً:
- في Azure Portal: `https://hatchtestserver.com/Yas-Marina-Rendezvous/oauth-callback.php`
- في config.php: `SITE_URL` يجب أن يكون: `https://hatchtestserver.com/Yas-Marina-Rendezvous`

### 2. Client Secret:
- ⚠️ **انسخه فوراً** بعد إنشائه - لن تتمكن من رؤيته مرة أخرى!
- إذا فقدته، ستحتاج إلى إنشاء واحد جديد

### 3. Refresh Token:
- صالح لمدة طويلة (عادة سنة أو أكثر)
- إذا انتهت صلاحيته، استخدم `oauth-setup.php` للحصول على واحد جديد

### 4. الأذونات:
- يجب **Grant Admin Consent** - بدونها لن يعمل!
- تأكد من ظهور ✅ بجانب الأذونات

### 5. المكتبات:
- تأكد من تثبيت `composer install` قبل الاستخدام
- المكتبة المطلوبة: `league/oauth2-client`

---

## 🔍 استكشاف الأخطاء:

### خطأ: "OAuth2 library not installed"
**الحل**: قم بتشغيل `composer install`

### خطأ: "OAuth2 configuration incomplete"
**الحل**: تأكد من ملء جميع القيم في `config.php`

### خطأ: "Invalid redirect URI"
**الحل**: تأكد من تطابق Redirect URI في Azure Portal مع `SITE_URL` في config.php

### خطأ: "Insufficient privileges"
**الحل**: 
- تأكد من إضافة الأذونات: `SMTP.Send` و `offline_access`
- تأكد من **Grant Admin Consent**

### خطأ: "Invalid refresh token"
**الحل**: 
- تأكد من نسخ Refresh Token بشكل صحيح
- احصل على Refresh Token جديد من `oauth-setup.php`

---

## 📊 ملخص البيانات المطلوبة:

| البيانات | المصدر | مطلوب/اختياري |
|---------|--------|---------------|
| **OAUTH_CLIENT_ID** | Azure Portal → App Registration | ✅ مطلوب |
| **OAUTH_CLIENT_SECRET** | Azure Portal → Certificates & secrets | ✅ مطلوب |
| **OAUTH_REFRESH_TOKEN** | oauth-setup.php | ✅ مطلوب |
| **OAUTH_TENANT_ID** | Azure Portal → Azure AD Overview | ⚠️ اختياري (استخدم `common`) |
| **OAUTH_USER_EMAIL** | موجود: `no-reply@yasmarina.ae` | ✅ موجود |

---

## ✅ بعد الإعداد:

1. ✅ تأكد من `USE_OAUTH2 = true` في config.php
2. ✅ تأكد من تثبيت المكتبات: `composer install`
3. ✅ تأكد من تحديث جميع القيم في config.php
4. ✅ احذف `oauth-setup.php` بعد الحصول على Refresh Token (للأمان)
5. ✅ اختبر الإرسال

---

**جاهز للبدء!** 🚀

راجع `OAUTH2-SETUP-GUIDE.md` للتفاصيل الكاملة.

