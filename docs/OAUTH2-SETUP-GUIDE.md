# دليل إعداد OAuth2 مع Microsoft 365

## نظرة عامة

Microsoft 365 قام بإيقاف دعم App Password (كلمة مرور التطبيق) وبدأ في التوصية باستخدام OAuth2 للمصادقة. هذا التطبيق يدعم الآن OAuth2 مع Microsoft 365.

## المتطلبات

1. حساب Microsoft 365 / Office 365 نشط
2. صلاحيات إدارية في Azure AD (Microsoft Entra)
3. Composer مثبت على السيرفر
4. PHP 7.4 أو أحدث

## الخطوات

### 1. تثبيت المكتبات المطلوبة

قم بتثبيت المكتبات المطلوبة عبر Composer:

```bash
composer install
```

أو إذا كان Composer غير متاح، قم بتحميل المكتبات يدوياً:
- `league/oauth2-client` من: https://github.com/thephpleague/oauth2-client

### 2. تسجيل التطبيق في Azure AD (Microsoft Entra)

1. اذهب إلى [Azure Portal](https://portal.azure.com)
2. ابحث عن **Azure Active Directory** أو **Microsoft Entra ID**
3. اذهب إلى **App registrations** → **New registration**
4. أدخل:
   - **Name**: Yas Marina Email Handler (أو أي اسم تفضله)
   - **Supported account types**: Accounts in this organizational directory only
   - **Redirect URI**: 
     - Type: Web
     - URI: `https://yourdomain.com/oauth-callback.php`
5. اضغط **Register**

### 3. الحصول على Client ID و Client Secret

بعد التسجيل:

1. من صفحة التطبيق، انسخ **Application (client) ID** - هذا هو `OAUTH_CLIENT_ID`
2. اذهب إلى **Certificates & secrets** → **New client secret**
3. أدخل وصف (مثل: "Email SMTP Secret")
4. اختر مدة الصلاحية (مثلاً: 24 months)
5. اضغط **Add**
6. **انسخ القيمة فوراً** - هذا هو `OAUTH_CLIENT_SECRET` (لن تتمكن من رؤيته مرة أخرى!)

### 4. إعداد الأذونات (Permissions)

1. اذهب إلى **API permissions**
2. اضغط **Add a permission**
3. اختر **Microsoft Graph**
4. اختر **Delegated permissions**
5. أضف الأذونات التالية:
   - `SMTP.Send` (تحت Mail)
   - `offline_access` (تحت OpenID permissions)
6. اضغط **Add permissions**
7. **مهم جداً**: اضغط **Grant admin consent** لتفعيل الأذونات

### 5. الحصول على Tenant ID (اختياري)

1. من Azure Portal، اذهب إلى **Azure Active Directory**
2. من **Overview**، انسخ **Tenant ID**
3. هذا هو `OAUTH_TENANT_ID` (يمكنك استخدام `common` بدلاً منه)

### 6. الحصول على Refresh Token

للحصول على Refresh Token، ستحتاج إلى:

#### الطريقة الأولى: استخدام ملف oauth-setup.php (موصى به)

قم بإنشاء ملف `oauth-setup.php` في نفس المجلد:

```php
<?php
require_once 'vendor/autoload.php';
require_once 'config.php';

use League\OAuth2\Client\Provider\GenericProvider;

$tenantId = !empty(OAUTH_TENANT_ID) ? OAUTH_TENANT_ID : 'common';
$provider = new GenericProvider([
    'clientId' => OAUTH_CLIENT_ID,
    'clientSecret' => OAUTH_CLIENT_SECRET,
    'redirectUri' => SITE_URL . '/oauth-callback.php',
    'urlAuthorize' => "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/authorize",
    'urlAccessToken' => "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token",
    'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',
    'scopes' => ['https://outlook.office.com/SMTP.Send', 'offline_access']
]);

if (!isset($_GET['code'])) {
    // Get authorization URL
    $authorizationUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    
    echo '<h2>خطوة 1: تفويض التطبيق</h2>';
    echo '<p>اضغط على الرابط أدناه للسماح للتطبيق بالوصول إلى حسابك:</p>';
    echo '<a href="' . htmlspecialchars($authorizationUrl) . '">تفويض التطبيق</a>';
} else {
    // Check state
    if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
        exit('Invalid state');
    }
    
    // Get access token
    $accessToken = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);
    
    echo '<h2>خطوة 2: تم الحصول على الرموز</h2>';
    echo '<h3>Refresh Token:</h3>';
    echo '<textarea style="width:100%;height:100px;">' . htmlspecialchars($accessToken->getRefreshToken()) . '</textarea>';
    echo '<p><strong>انسخ هذا الرمز وأضفه في config.php كـ OAUTH_REFRESH_TOKEN</strong></p>';
}
?>
```

ثم:
1. افتح `https://yourdomain.com/oauth-setup.php`
2. اضغط على رابط التفويض
3. سجل الدخول بحساب Microsoft 365
4. وافق على الأذونات
5. انسخ Refresh Token

#### الطريقة الثانية: استخدام PowerShell أو Azure CLI

يمكنك استخدام أدوات Microsoft للحصول على Refresh Token.

### 7. تحديث config.php

افتح `config.php` وقم بتحديث القيم التالية:

```php
define('USE_OAUTH2', true); // تفعيل OAuth2
define('OAUTH_CLIENT_ID', 'your-client-id-here');
define('OAUTH_CLIENT_SECRET', 'your-client-secret-here');
define('OAUTH_TENANT_ID', 'your-tenant-id-or-common');
define('OAUTH_REFRESH_TOKEN', 'your-refresh-token-here');
define('OAUTH_USER_EMAIL', 'no-reply@yasmarina.ae');
```

### 8. اختبار الإرسال

1. تأكد من أن `USE_OAUTH2` = `true` في `config.php`
2. تأكد من تثبيت المكتبات: `composer install`
3. جرب إرسال بريد إلكتروني من النموذج
4. تحقق من `email_log.txt` للأخطاء

## استكشاف الأخطاء

### خطأ: "OAuth2 library not installed"
**الحل**: قم بتشغيل `composer install`

### خطأ: "OAuth2 configuration incomplete"
**الحل**: تأكد من ملء جميع القيم في `config.php`:
- `OAUTH_CLIENT_ID`
- `OAUTH_CLIENT_SECRET`
- `OAUTH_REFRESH_TOKEN`
- `OAUTH_USER_EMAIL`

### خطأ: "Invalid refresh token"
**الحل**: 
- تأكد من نسخ Refresh Token بشكل صحيح
- قد تحتاج إلى الحصول على Refresh Token جديد
- تأكد من أن Client Secret لم ينتهِ صلاحيته

### خطأ: "Insufficient privileges"
**الحل**: 
- تأكد من منح الأذونات في Azure Portal
- تأكد من الضغط على **Grant admin consent**
- تأكد من إضافة `SMTP.Send` و `offline_access`

### خطأ: "Redirect URI mismatch"
**الحل**: 
- تأكد من تطابق Redirect URI في Azure Portal مع `SITE_URL` في `config.php`
- يجب أن يكون: `https://yourdomain.com/oauth-callback.php`

## الأمان

1. **لا تشارك Client Secret أو Refresh Token** مع أي شخص
2. **احفظ config.php** في مكان آمن ولا ترفعه إلى Git
3. **استخدم HTTPS** دائماً
4. **حدّث Client Secret** بانتظام (كل 6-12 شهر)
5. **راقب استخدام التطبيق** في Azure Portal

## المراجع

- [Microsoft OAuth2 Documentation](https://learn.microsoft.com/en-us/exchange/client-developer/legacy-protocols/how-to-authenticate-an-imap-pop-smtp-application-by-using-oauth)
- [PHPMailer OAuth2 Guide](https://github.com/PHPMailer/PHPMailer/wiki/Using-Gmail-with-XOAUTH2)
- [League OAuth2 Client](https://oauth2-client.thephpleague.com/)

## ملاحظات

- Refresh Token صالح لمدة طويلة (عادة سنة أو أكثر)
- Access Token يتم تجديده تلقائياً عند الحاجة
- إذا انتهت صلاحية Refresh Token، ستحتاج إلى الحصول على واحد جديد
- يمكنك استخدام `common` كـ Tenant ID إذا كان لديك عدة مستأجرين

---

**تم التحديث**: تم إضافة دعم OAuth2 في هذا التطبيق استجابة لإيقاف Microsoft لدعم App Password.

