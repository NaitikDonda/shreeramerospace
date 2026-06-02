# GoDaddy Troubleshooting Guide
## Email and File Upload Issues

---

## Problem Summary
After deploying to GoDaddy:
- ✅ Database is getting updated (working)
- ❌ Not getting email or confirmation email (not working)
- ❌ Files uploaded by users are not being stored (not working)

---

## Solution 1: Fix Email Issues

### Changes Already Made
I've updated `php/contact-form.php` to use PHP's `mail()` function instead of SMTP for GoDaddy compatibility.

### Additional Steps Required on GoDaddy

#### Option 1: Use GoDaddy's SMTP Server
If PHP mail() doesn't work, configure GoDaddy's SMTP:

1. **Get GoDaddy SMTP Credentials:**
   - Log in to GoDaddy cPanel
   - Go to "Email Accounts"
   - Find your email account or create one
   - Note the SMTP settings (usually: `smtpout.secureserver.net` or `relay-hosting.secureserver.net`)

2. **Update `php/contact-form.php` (lines 353-363):**
```php
// For GoDaddy SMTP:
$mail->IsSMTP();
$mail->Host = 'smtpout.secureserver.net'; // or relay-hosting.secureserver.net
$mail->SMTPAuth = true;
$mail->Username = 'your-email@yourdomain.com';
$mail->Password = 'your-email-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587; // or 465 for SSL
```

#### Option 2: Use PHP mail() (Already Configured)
The file is already configured to use PHP mail(). If this doesn't work:

1. **Check if PHP mail() is enabled:**
   - Log in to cPanel
   - Go to "Select PHP Version"
   - Check if `mail` function is enabled

2. **Check email logs:**
   - In cPanel, go to "Email" → "Email Delivery Log"
   - Look for bounced or rejected emails

#### Option 3: Use Gmail SMTP with GoDaddy
If you prefer to keep using Gmail SMTP:

1. **Enable "Less Secure Apps" in Gmail** (deprecated) or use App Password
2. **Update the SMTP settings in `php/contact-form.php`** to uncomment the SMTP lines
3. **Note:** GoDaddy may block external SMTP ports

---

## Solution 2: Fix File Upload Issues

### Problem
Files are not being stored in the uploads directory on GoDaddy.

### Root Cause
The uploads directory likely doesn't have proper write permissions on GoDaddy.

### Steps to Fix

#### Step 1: Set Directory Permissions via cPanel

1. **Log in to GoDaddy cPanel**
2. **Go to File Manager**
3. **Navigate to `public_html/uploads/`**
4. **Right-click on the `uploads` folder**
5. **Select "Change Permissions"**
6. **Set permissions to `755` or `777`** (try 755 first, if that doesn't work try 777)
   - Owner: Read + Write + Execute (7)
   - Group: Read + Execute (5)
   - World: Read + Execute (5)

#### Step 2: Verify Directory Exists

1. **In File Manager, check if `uploads/` folder exists**
2. **If not, create it:**
   - Right-click in `public_html` folder
   - Select "New Folder"
   - Name it `uploads`
   - Set permissions to 755 or 777

#### Step 3: Check PHP Upload Settings

1. **Log in to cPanel**
2. **Go to "Select PHP Version"**
3. **Click "Switch to PHP Options"**
4. **Verify these settings:**
   - `upload_max_filesize`: 25M
   - `post_max_size`: 30M
   - `max_execution_time`: 600
   - `max_input_time`: 600
   - `memory_limit`: 256M

#### Step 4: Check Error Logs

1. **In cPanel, go to "Metrics" → "Errors"**
2. **Look for recent errors related to file uploads**
3. **Common errors:**
   - "Permission denied" → Fix directory permissions
   - "No such file or directory" → Create uploads directory
   - "File size exceeds limit" → Check PHP settings

#### Step 5: Test File Upload

1. **Go to your contact page**
2. **Try uploading a small test file (under 1MB)**
3. **Check if file appears in `uploads/` directory via File Manager**
4. **Check error logs if upload fails**

---

## Solution 3: Debug Mode

### Enable Debug Mode

To see what's happening, enable debug mode in `php/contact-form.php`:

**Line 33:**
```php
$debug = 2; // Change from 0 to 2
```

This will output detailed error information. Check the browser console and GoDaddy error logs.

---

## Solution 4: Check File Upload Path

The current upload path is:
```php
$uploadDir = __DIR__ . '/../uploads/';
```

This should work on GoDaddy, but if it doesn't, try:

**Option 1: Absolute Path**
```php
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
```

**Option 2: Relative Path**
```php
$uploadDir = 'uploads/';
```

Update line 138 in `php/contact-form.php` if needed.

---

## Quick Fix Checklist

### Email Issues:
- [ ] Changed to PHP mail() (already done)
- [ ] Checked if PHP mail() is enabled in cPanel
- [ ] Checked email delivery logs in cPanel
- [ ] Tried GoDaddy SMTP settings
- [ ] Enabled debug mode to see errors

### File Upload Issues:
- [ ] Created `uploads/` directory
- [ ] Set permissions to 755 or 777
- [ ] Verified PHP upload settings in cPanel
- [ ] Checked error logs for upload errors
- [ ] Tested with small file (< 1MB)
- [ ] Enabled debug mode to see errors

---

## Common GoDaddy Issues and Solutions

### Issue 1: "Permission Denied"
**Solution:** Set uploads directory permissions to 777

### Issue 2: "No such file or directory"
**Solution:** Create the uploads directory in File Manager

### Issue 3: "File size exceeds limit"
**Solution:** Check PHP settings in cPanel → Select PHP Version

### Issue 4: Email not sending
**Solution:** Use PHP mail() or GoDaddy SMTP instead of Gmail SMTP

### Issue 5: SMTP connection timeout
**Solution:** GoDaddy blocks external SMTP, use PHP mail() or GoDaddy SMTP

---

## Testing After Fixes

### Test Email:
1. Submit the contact form
2. Check if you receive email at dondanaitik@gmail.com
3. Check if user receives confirmation email
4. Check error logs if emails don't arrive

### Test File Upload:
1. Submit form with a small test file (< 1MB)
2. Check if file appears in `uploads/` directory via File Manager
3. Check if file is saved to database
4. Check error logs if upload fails

---

## Need Help?

If issues persist after trying these solutions:

1. **Enable debug mode** (`$debug = 2;`) and check error logs
2. **Share the error messages** from the logs
3. **Verify all file permissions** are correct
4. **Check PHP version** (PHP 7.4+ recommended)

---

*Last Updated: June 2, 2026*
