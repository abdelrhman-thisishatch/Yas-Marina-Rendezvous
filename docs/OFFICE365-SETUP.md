# 🏢 Office 365 SMTP Setup Guide

## Current Settings:
```php
SMTP Host: smtp.office365.com ✅
SMTP Port: 587 ✅
Username: no-reply@yasmarina.ae
Password: Apple@2025
Security: TLS ✅
```

---

## ❌ Current Error:
```
An error occurred while sending the message. Please try again.
```

---

## 🔍 Possible Problems & Solutions:

### Problem 1: Incorrect Password ⚠️

**Most Common Issue!**

The password `Apple@2025` might be incorrect.

**Solution:**
- Ask client to verify the password for `no-reply@yasmarina.ae`
- Client can reset it in Office 365 Admin Center

---

### Problem 2: Email Doesn't Exist in Office 365

The email `no-reply@yasmarina.ae` must exist in Office 365.

**To Verify:**
1. Login to: https://admin.microsoft.com
2. Go to: Users → Active users
3. Search for: no-reply@yasmarina.ae
4. If doesn't exist → Create it first

---

### Problem 3: SMTP Authentication Disabled

Office 365 might have SMTP authentication disabled for this account.

**Solution - Ask Client to:**
1. Login: https://admin.microsoft.com
2. Go to: Users → Active users
3. Click on: no-reply@yasmarina.ae
4. Go to: Mail → Manage email apps
5. ✅ Enable: **Authenticated SMTP**
6. Save changes

**OR via Exchange Admin:**
1. Go to: https://admin.exchange.microsoft.com
2. Recipients → Mailboxes
3. Select: no-reply@yasmarina.ae
4. Mail flow settings → View details
5. ✅ Enable: **SMTP AUTH**

---

### Problem 4: Modern Authentication Required

Office 365 might require App Password instead of regular password.

**Solution - Create App Password:**
1. Login: https://account.microsoft.com/security
2. Go to: Security → Advanced security options
3. Click: App passwords
4. Create new app password for "Mail"
5. Use this password in config.php instead of regular password

---

### Problem 5: Security Defaults Blocking SMTP

Microsoft Security Defaults might be blocking SMTP.

**Solution - Ask Client (Admin only):**
1. Login: https://aad.portal.azure.com
2. Go to: Azure Active Directory → Properties
3. Manage Security defaults
4. If enabled, you might need to disable it OR use modern auth

---

### Problem 6: Conditional Access Policies

Conditional Access might be blocking the connection.

**Solution - Ask Client (Admin only):**
1. Login: https://aad.portal.azure.com
2. Go to: Security → Conditional Access
3. Check if any policy is blocking SMTP
4. Add exception for SMTP if needed

---

### Problem 7: Account is Shared Mailbox

If `no-reply@yasmarina.ae` is a shared mailbox (not user mailbox), SMTP won't work.

**Solution:**
- Convert to user mailbox, OR
- Use a different user mailbox for sending

---

## ✅ What to Ask Client:

### Message to Client:

```
Hello,

The contact form is using Office 365 SMTP but getting authentication error.

Please verify:

1. ✅ Email exists: no-reply@yasmarina.ae
2. ✅ Password is correct: Apple@2025
3. ✅ SMTP Authentication is enabled for this account

To enable SMTP Authentication:
- Login to: https://admin.microsoft.com
- Go to: Users → Active users → no-reply@yasmarina.ae
- Mail tab → Manage email apps
- Enable: "Authenticated SMTP"
- Save

This is the most common issue with Office 365!

Thanks!
```

---

## 🔧 Testing Steps:

### After client enables SMTP Auth:

1. **Wait 15 minutes** (Office 365 needs time to apply changes)
2. Upload updated files
3. Try contact form
4. Check `email_log.txt` for detailed error

---

## 📊 Alternative Configurations:

### Option 1: Use Different Office 365 Account

If `no-reply@yasmarina.ae` has restrictions, use admin account:

```php
define('SMTP_USERNAME', 'admin@yasmarina.ae'); // Admin account
define('SMTP_PASSWORD', 'admin-password');
```

Then in PHPMailer, set:
```php
$mail->setFrom('admin@yasmarina.ae', 'Yas Marina Rendezvous');
$mail->addReplyTo($customerEmail, $customerName); // Customer can still reply
```

### Option 2: Use App-Specific Password

If regular password doesn't work:

1. Client generates App Password
2. Use that instead of regular password
3. Update config.php

### Option 3: Use SendGrid/Mailgun

If Office 365 restrictions are too complex:

```php
// Switch to SendGrid or Mailgun
define('SMTP_HOST', 'smtp.sendgrid.net');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'apikey');
define('SMTP_PASSWORD', 'your-sendgrid-api-key');
```

---

## 🎯 Most Likely Solution:

**Enable SMTP Authentication in Office 365 Admin:**

This is disabled by default in Office 365!

### Steps for Client:
1. https://admin.microsoft.com
2. Users → Active users
3. no-reply@yasmarina.ae
4. Mail tab
5. Manage email apps
6. ✅ Check "Authenticated SMTP"
7. Save
8. Wait 15 minutes
9. Test!

---

## 📝 Checklist:

```
For Client:
☐ Email no-reply@yasmarina.ae exists in Office 365
☐ Password Apple@2025 is correct
☐ SMTP Authentication is ENABLED
☐ Account is User Mailbox (not Shared Mailbox)
☐ No Conditional Access blocking SMTP
☐ Security Defaults not interfering
☐ Wait 15 minutes after enabling SMTP Auth

For You:
☐ config.php updated with Office 365 settings
☐ send-email-smtp.php updated
☐ DEBUG_MODE enabled
☐ Files uploaded to server
☐ Test form after client enables SMTP Auth
☐ Check email_log.txt for errors
```

---

## 🔍 Debug Errors:

After you test with DEBUG_MODE enabled, check `email_log.txt`:

### Error: "SMTP connect() failed"
**Solution:** Check firewall, port 587 must be open

### Error: "SMTP Authentication failed" 
**Solution:** Wrong password OR SMTP Auth not enabled

### Error: "Could not authenticate"
**Solution:** Enable SMTP Authentication in Office 365 Admin

### Error: "535 5.7.3 Authentication unsuccessful"
**Solution:** Enable SMTP Auth + wait 15 mins + verify password

---

## 🎊 Final Config (After Client Enables SMTP):

```php
// Office 365 Settings
define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'no-reply@yasmarina.ae');
define('SMTP_PASSWORD', 'correct-password-here');
define('SMTP_SECURE', 'tls');
```

**Expected Result:**
- ✅ Email sent successfully
- ✅ Shows in email_log.txt: "Email sent successfully via SMTP"
- ✅ Email arrives at recipient

---

## 📞 Support Links:

- Office 365 Admin: https://admin.microsoft.com
- Exchange Admin: https://admin.exchange.microsoft.com
- Azure AD Portal: https://aad.portal.azure.com
- Account Security: https://account.microsoft.com/security

---

**Status:** 🔴 Waiting for client to enable SMTP Authentication  
**Most Common Fix:** Enable "Authenticated SMTP" in Office 365  
**Wait Time:** 15 minutes after enabling  
**Last Updated:** November 5, 2025

