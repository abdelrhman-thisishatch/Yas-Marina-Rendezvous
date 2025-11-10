# 🚀 Quick Start Guide - OAuth2 Setup for Microsoft 365

## What You Need to Provide

After completing the setup, provide these **4 pieces of information** to your developer:

1. **OAUTH_CLIENT_ID** - Application ID from Azure Portal
2. **OAUTH_CLIENT_SECRET** - Secret value from Azure Portal  
3. **OAUTH_TENANT_ID** - Tenant ID (or use `common`)
4. **Email Account** - `no-reply@yasmarina.ae` (already configured ✅)

---

## ⚡ Quick Setup Steps

### 1️⃣ Register Application (5 minutes)

1. Go to: https://portal.azure.com
2. **Azure Active Directory** → **App registrations** → **+ New registration**
3. Fill in:
   - **Name**: `Yas Marina Email Handler`
   - **Account types**: `Accounts in this organizational directory only`
   - **Redirect URI**: 
     - Type: **Web**
     - URL: `https://hatchtestserver.com/Yas-Marina-Rendezvous/oauth-callback.php`
4. Click **Register**
5. **Copy "Application (client) ID"** → This is `OAUTH_CLIENT_ID` ✅

---

### 2️⃣ Create Client Secret (2 minutes)

1. From app page → **Certificates & secrets**
2. Click **+ New client secret**
3. Enter description: `Email SMTP Secret`
4. Expires: **24 months**
5. Click **Add**
6. **⚠️ COPY THE VALUE IMMEDIATELY** → This is `OAUTH_CLIENT_SECRET` ✅
   - You won't see it again!

---

### 3️⃣ Configure Permissions ⚠️ CRITICAL STEP

**This step is ESSENTIAL - Without it, OAuth2 will NOT work!**

1. From app page → **API permissions**
2. Click **+ Add a permission**
3. Select **Microsoft Graph**
4. Select **Delegated permissions** (NOT Application permissions)

5. **Add these 2 permissions:**
   
   **Permission 1:**
   - Search: `SMTP.Send` or `Mail.Send`
   - ✅ Check the box
   
   **Permission 2:**
   - Search: `offline_access`
   - ✅ Check the box

6. Click **Add permissions**

7. **⚠️ CRITICAL: Grant Admin Consent**
   - Click **"Grant admin consent for [Your Organization]"** button
   - Confirm the action
   - Wait for **✅ Green checkmarks** to appear
   - If you see ⚠️ yellow warnings, permissions are NOT granted!

---

### 4️⃣ Get Tenant ID (1 minute)

1. Go to **Azure Active Directory** → **Overview**
2. Copy **Tenant ID** → This is `OAUTH_TENANT_ID` ✅
   - Or tell developer to use `common` if not available

---

## ✅ Verification Checklist

Before sending information to developer, check:

- [ ] ✅ Application registered
- [ ] ✅ Client ID copied
- [ ] ✅ Client Secret copied (and saved securely)
- [ ] ✅ **SMTP.Send permission added** (Delegated)
- [ ] ✅ **offline_access permission added** (Delegated)
- [ ] ✅ **Admin consent granted** (Green checkmarks ✅)
- [ ] ✅ Tenant ID copied (or note to use `common`)
- [ ] ✅ Email `no-reply@yasmarina.ae` exists and is active

---

## 📤 Send This Information to Developer

```
OAUTH_CLIENT_ID: [paste your Client ID here]
OAUTH_CLIENT_SECRET: [paste your Client Secret here]
OAUTH_TENANT_ID: [paste your Tenant ID here, or use 'common']
```

---

## ⚠️ Important Notes

### Security:
- ⚠️ **Never share** Client Secret publicly
- ⚠️ **Never commit** to version control
- ⚠️ Store securely

### Permissions:
- ✅ Must be **Delegated permissions** (NOT Application)
- ✅ Must **Grant admin consent** (Green checkmarks required)
- ✅ Without admin consent, OAuth2 will fail!

### Email Account:
- ✅ Must be **User Mailbox** (NOT Shared Mailbox)
- ✅ Must be active and enabled

---

## 🆘 Common Issues

### ❌ "Admin consent not granted"
**Fix:** Go to API Permissions → Click "Grant admin consent" → Wait for ✅

### ❌ "Cannot find SMTP.Send"
**Fix:** Search for "Mail.Send" instead, ensure Delegated permissions

### ❌ "Client Secret lost"
**Fix:** Create new secret in Certificates & secrets → Copy immediately

---

## 📚 Full Documentation

For detailed instructions, see: `CLIENT-OAUTH2-SETUP-GUIDE.md`

---

**Estimated Time:** 10-15 minutes  
**Difficulty:** Easy (with admin access)

---

**Questions?** Contact your developer or refer to the full guide.

