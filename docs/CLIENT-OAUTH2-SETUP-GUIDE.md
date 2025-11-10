# 📧 OAuth2 Setup Guide for Microsoft 365 Email

## Overview

This guide will help you set up OAuth2 authentication for sending emails from your website using Microsoft 365. OAuth2 is the modern, secure method recommended by Microsoft and replaces the deprecated App Password method.

---

## ⚠️ Important Prerequisites

Before starting, ensure you have:

- ✅ **Microsoft 365 Admin Account** - You need admin access to Azure AD
- ✅ **Email Account** - `no-reply@yasmarina.ae` must exist in your Office 365 tenant
- ✅ **Admin Permissions** - Ability to register applications and grant permissions in Azure AD

---

## 📋 Step-by-Step Instructions

### Step 1: Register Application in Azure AD

1. **Go to Azure Portal:**
   - Visit: https://portal.azure.com
   - Sign in with your Microsoft 365 admin account

2. **Navigate to App Registrations:**
   - Search for **"Azure Active Directory"** or **"Microsoft Entra ID"** in the search bar
   - Click on it
   - In the left menu, click **"App registrations"**
   - Click **"+ New registration"** at the top

3. **Register the Application:**
   - **Name**: `Yas Marina Email Handler` (or any name you prefer)
   - **Supported account types**: Select **"Accounts in this organizational directory only"**
   - **Redirect URI**:
     - **Platform**: Select **"Web"**
     - **URI**: `https://yasrendezvous.ae/oauth-callback.php`
   - Click **"Register"**

4. **Copy the Application (Client) ID:**
   - After registration, you'll see the application overview page
   - **Copy the "Application (client) ID"** - This is your `OAUTH_CLIENT_ID`
   - ⚠️ **Save this somewhere safe - you'll need it later!**

---

### Step 2: Create Client Secret

1. **Navigate to Certificates & Secrets:**
   - From the application overview page, click **"Certificates & secrets"** in the left menu

2. **Create New Client Secret:**
   - Click **"+ New client secret"**
   - **Description**: Enter `Email SMTP Secret` (or any description)
   - **Expires**: Select **24 months** (or your preferred expiration period)
   - Click **"Add"**

3. **Copy the Client Secret Value:**
   - ⚠️ **IMPORTANT**: Copy the **Value** immediately!
   - You will see it only once - if you close this page, you cannot retrieve it
   - This is your `OAUTH_CLIENT_SECRET`
   - ⚠️ **Save this securely - you'll need it later!**

---

### Step 3: Configure API Permissions

This is a **CRITICAL STEP** - Without proper permissions, OAuth2 will not work!

1. **Navigate to API Permissions:**
   - From the application overview page, click **"API permissions"** in the left menu

2. **Add Microsoft Graph Permissions:**
   - Click **"+ Add a permission"**
   - Select **"Microsoft Graph"**
   - Select **"Delegated permissions"** (NOT Application permissions)

3. **Add Required Permissions:**
   
   **Permission 1: SMTP.Send**
   - Search for: `SMTP.Send`
   - Select: **"Mail.Send"** or **"SMTP.Send"** (under Mail category)
   - ✅ Check the box next to it
   
   **Permission 2: offline_access**
   - Search for: `offline_access`
   - Select: **"offline_access"** (under OpenID permissions)
   - ✅ Check the box next to it

4. **Add Permissions:**
   - Click **"Add permissions"** at the bottom
   - You should now see both permissions listed

5. **Grant Admin Consent:**
   - ⚠️ **CRITICAL**: Click the **"Grant admin consent for [Your Organization]"** button
   - Confirm the action
   - Wait for the status to show **✅ Green checkmarks** next to both permissions
   - If you see **⚠️ Yellow warning icons**, the permissions are NOT granted - click "Grant admin consent" again

---

### Step 4: Get Tenant ID (Optional but Recommended)

1. **Navigate to Azure Active Directory:**
   - Go back to **"Azure Active Directory"** or **"Microsoft Entra ID"**
   - Click **"Overview"** in the left menu

2. **Copy Tenant ID:**
   - Find **"Tenant ID"** in the overview section
   - Copy this value - This is your `OAUTH_TENANT_ID`
   - ⚠️ **Note**: If you don't have this, you can use `common` instead

---

### Step 5: Verify Email Account Permissions

Ensure the email account has proper permissions:

1. **Check Email Account Exists:**
   - Go to: https://admin.microsoft.com
   - Navigate to: **Users** → **Active users**
   - Search for: `no-reply@yasmarina.ae`
   - ✅ Verify the account exists and is active

2. **Verify Account Type:**
   - The account must be a **User Mailbox** (NOT a Shared Mailbox)
   - Shared Mailboxes cannot send emails via SMTP

3. **Check Mailbox Permissions:**
   - Click on the user account
   - Go to **"Mail"** tab
   - Ensure the mailbox is enabled and active

---

## 📤 Information to Provide to Developer

After completing the above steps, provide the following information to your developer:

### Required Information:

1. **OAUTH_CLIENT_ID**
   - Location: Azure Portal → App Registration → Overview
   - Format: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx` (GUID format)

2. **OAUTH_CLIENT_SECRET**
   - Location: Azure Portal → App Registration → Certificates & secrets
   - Format: Long alphanumeric string (you copied this in Step 2)
   - ⚠️ **Important**: If you lost this, you'll need to create a new one

3. **OAUTH_TENANT_ID** (Optional)
   - Location: Azure Portal → Azure Active Directory → Overview
   - Format: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx` (GUID format)
   - ⚠️ **Note**: If not available, developer can use `common`

4. **OAUTH_USER_EMAIL**
   - Value: `no-reply@yasmarina.ae`
   - ✅ Already configured

---

## 🔐 Security Best Practices

### 1. Client Secret Security:
- ⚠️ **Never share** Client Secret publicly
- ⚠️ **Never commit** Client Secret to version control (Git)
- ⚠️ Store it securely
- ⚠️ If compromised, create a new one immediately

### 2. Application Permissions:
- ✅ Only grant **Delegated permissions** (NOT Application permissions)
- ✅ Only grant **minimum required permissions** (SMTP.Send and offline_access)
- ✅ Regularly review permissions in Azure Portal

### 3. Regular Maintenance:
- ⚠️ Client Secrets expire - set reminders to renew before expiration
- ⚠️ Review application access logs periodically
- ⚠️ Monitor for any suspicious activity

---

## ✅ Verification Checklist

Before providing information to developer, verify:

- [ ] Application registered in Azure AD
- [ ] Client ID copied and saved securely
- [ ] Client Secret created and copied securely
- [ ] Redirect URI configured correctly: `https://yasrendezvous.ae/oauth-callback.php`
- [ ] **SMTP.Send permission added** (Delegated)
- [ ] **offline_access permission added** (Delegated)
- [ ] **Admin consent granted** (✅ Green checkmarks visible)
- [ ] Tenant ID copied (or note that `common` can be used)
- [ ] Email account `no-reply@yasmarina.ae` exists and is active
- [ ] Email account is User Mailbox (not Shared Mailbox)

---

## 🆘 Troubleshooting

### Issue: "Admin consent not granted"
**Solution:**
- Go to API Permissions
- Click "Grant admin consent for [Your Organization]"
- Wait for green checkmarks ✅

### Issue: "Cannot find SMTP.Send permission"
**Solution:**
- Search for "Mail.Send" instead
- Or search for "SMTP" in the permission list
- Ensure you're adding **Delegated permissions**, not Application permissions

### Issue: "Client Secret not visible"
**Solution:**
- Client Secret is shown only once after creation
- If you lost it, create a new one:
  - Go to Certificates & secrets
  - Click "+ New client secret"
  - Copy the new value immediately

### Issue: "Redirect URI mismatch"
**Solution:**
- Ensure Redirect URI in Azure Portal exactly matches:
  - `https://yasrendezvous.ae/oauth-callback.php`
- Check for typos, trailing slashes, or protocol differences (http vs https)

### Issue: "Email account not found"
**Solution:**
- Verify account exists: https://admin.microsoft.com → Users → Active users
- Ensure account is active (not disabled)
- Ensure account is User Mailbox (not Shared Mailbox)

---

## 📞 Support Resources

- **Azure Portal**: https://portal.azure.com
- **Microsoft 365 Admin**: https://admin.microsoft.com
- **Microsoft Graph API Documentation**: https://docs.microsoft.com/en-us/graph/
- **OAuth2 Documentation**: https://learn.microsoft.com/en-us/exchange/client-developer/legacy-protocols/how-to-authenticate-an-imap-pop-smtp-application-by-using-oauth

---

## 📝 Summary

After completing these steps, you should have:

1. ✅ Application registered in Azure AD
2. ✅ Client ID (OAUTH_CLIENT_ID)
3. ✅ Client Secret (OAUTH_CLIENT_SECRET)
4. ✅ Permissions configured (SMTP.Send + offline_access)
5. ✅ Admin consent granted
6. ✅ Tenant ID (or note to use `common`)

**Next Steps:**
- Provide the above information to your developer
- Developer will configure the application and obtain Refresh Token
- Developer will test email sending functionality

---

**Questions?** Contact your developer or refer to the technical documentation: `OAUTH2-SETUP-GUIDE.md`

---

**Last Updated:** November 2025  
**Version:** 1.0

