# 📋 Developer Guide - Client Requirements for OAuth2

## What to Send to Client

Send the client this file: **`docs/CLIENT-OAUTH2-SETUP-GUIDE.md`** (English - from project root)  
Or the quick version: **`docs/CLIENT-QUICK-START.md`** (English - from project root)

---

## 📤 Information You Need from Client

After client completes the setup, you need these **4 pieces of information**:

### 1. OAUTH_CLIENT_ID
- **Format**: GUID (e.g., `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`)
- **Source**: Azure Portal → App Registration → Overview
- **Required**: ✅ Yes

### 2. OAUTH_CLIENT_SECRET
- **Format**: Long alphanumeric string
- **Source**: Azure Portal → App Registration → Certificates & secrets
- **Required**: ✅ Yes
- **Note**: Client must copy this immediately - it's shown only once!

### 3. OAUTH_TENANT_ID
- **Format**: GUID (e.g., `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`)
- **Source**: Azure Portal → Azure Active Directory → Overview
- **Required**: ⚠️ Optional (can use `common` instead)

### 4. Email Account Verification
- **Email**: `no-reply@yasmarina.ae`
- **Required**: ✅ Must exist and be active
- **Type**: Must be User Mailbox (NOT Shared Mailbox)

---

## ✅ Verification Checklist (Ask Client to Confirm)

Before proceeding, ask client to confirm:

- [ ] ✅ Application registered in Azure AD
- [ ] ✅ Client ID copied
- [ ] ✅ Client Secret copied and saved securely
- [ ] ✅ **SMTP.Send permission added** (Delegated)
- [ ] ✅ **offline_access permission added** (Delegated)
- [ ] ✅ **Admin consent GRANTED** (Green checkmarks ✅ visible)
- [ ] ✅ Redirect URI configured: `https://yasrendezvous.ae/oauth-callback.php`
- [ ] ✅ Tenant ID copied (or confirmed to use `common`)
- [ ] ✅ Email account `no-reply@yasmarina.ae` exists and is active

---

## 🔧 After Receiving Information from Client

### Step 1: Update config.php

```php
define('USE_OAUTH2', true); // Enable OAuth2
define('OAUTH_CLIENT_ID', 'client-provided-id');
define('OAUTH_CLIENT_SECRET', 'client-provided-secret');
define('OAUTH_TENANT_ID', 'client-provided-tenant-id-or-common');
define('OAUTH_USER_EMAIL', 'no-reply@yasmarina.ae'); // Already set ✅
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Get Refresh Token

1. Ensure `oauth-setup.php` is uploaded to server
2. Client should visit: `https://yasrendezvous.ae/oauth-setup.php`
3. Follow the steps to get Refresh Token
4. Add Refresh Token to config.php:
   ```php
   define('OAUTH_REFRESH_TOKEN', 'refresh-token-from-oauth-setup');
   ```

### Step 4: Test

1. Try sending email from form
2. Check `email_log.txt` for:
   - `🔐 Using OAuth2 authentication`
   - `✅ Email sent successfully via SMTP`

---

## ⚠️ Common Issues & Solutions

### Issue: "OAuth2 configuration incomplete"
**Cause**: Missing CLIENT_ID, CLIENT_SECRET, or REFRESH_TOKEN  
**Solution**: Verify all values are set in config.php

### Issue: "Insufficient privileges"
**Cause**: Admin consent not granted  
**Solution**: Ask client to go to API Permissions → Grant admin consent

### Issue: "Invalid redirect URI"
**Cause**: Redirect URI mismatch  
**Solution**: Verify Redirect URI in Azure Portal matches SITE_URL exactly

### Issue: "Invalid refresh token"
**Cause**: Token expired or incorrect  
**Solution**: Use oauth-setup.php to get new Refresh Token

### Issue: "OAuth2 library not installed"
**Cause**: Composer dependencies not installed  
**Solution**: Run `composer install`

---

## 📝 Email Template to Send to Client

```
Subject: OAuth2 Setup Required for Email Functionality

Hello,

To enable secure email sending from the website, we need to set up OAuth2 
authentication with Microsoft 365.

Please follow the attached guide: docs/CLIENT-OAUTH2-SETUP-GUIDE.md (from project root)

After completing the setup, please provide:
1. OAUTH_CLIENT_ID
2. OAUTH_CLIENT_SECRET  
3. OAUTH_TENANT_ID (or confirm to use 'common')

IMPORTANT: Please ensure:
- SMTP.Send permission is added (Delegated)
- offline_access permission is added (Delegated)
- Admin consent is GRANTED (you should see green checkmarks ✅)

Estimated time: 10-15 minutes

If you have any questions, please let me know.

Thank you!
```

---

## 🎯 Quick Reference

| Item | Location | Required |
|------|----------|----------|
| CLIENT_ID | Azure Portal → App Registration → Overview | ✅ Yes |
| CLIENT_SECRET | Azure Portal → App Registration → Certificates & secrets | ✅ Yes |
| TENANT_ID | Azure Portal → Azure AD → Overview | ⚠️ Optional |
| REFRESH_TOKEN | oauth-setup.php (after setup) | ✅ Yes |
| Permissions | Azure Portal → App Registration → API permissions | ✅ Yes |
| Admin Consent | Azure Portal → App Registration → API permissions | ✅ Yes |

---

## 📚 Related Files

- **docs/CLIENT-OAUTH2-SETUP-GUIDE.md** - Full guide for client (English) - from project root
- **docs/CLIENT-QUICK-START.md** - Quick start guide for client (English) - from project root
- **docs/OAUTH2-SETUP-GUIDE.md** - Technical setup guide (Arabic) - from project root
- **docs/OAUTH2-CHECKLIST.md** - Complete checklist (Arabic) - from project root

---

**Last Updated:** November 2025

