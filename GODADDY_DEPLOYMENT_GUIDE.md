# GoDaddy Deployment Guide
## Shreeram Aerospace & Defence LLP Website

### Pre-Deployment Checklist ✅

- [x] Updated all hardcoded database paths to relative paths
- [x] Updated file size limits to 25MB in php.ini, .htaccess, and PHP files
- [x] Verified database configuration uses SQLite with relative paths
- [x] Updated security headers in .htaccess
- [x] Configured file upload directories

---

### Files to Upload

Upload the following files/folders to your GoDaddy public HTML directory (usually `public_html`):

#### Core Files
- `index.html`
- `shreeram-aerospace.html`
- `admin.html`
- `demo-auto-services.html`
- `demo-auto-services-about-us.html`
- `demo-auto-services-contact.html`
- `demo-auto-services-capabilities.html`
- `demo-auto-services-products.html`
- `demo-auto-services-services.html`
- `demo-auto-services-services-detail.html`
- `demo-auto-services-blog.html`
- `demo-auto-services-blog-post.html`
- `demo-auto-services-appointment.html`

#### Configuration Files
- `.htaccess` (CRITICAL - contains PHP settings and security)
- `php.ini` (PHP configuration)

#### Directories
- `css/` (all CSS files)
- `js/` (all JavaScript files)
- `img/` (all images)
- `localimages/` (local images)
- `video/` (video files)
- `vendor/` (all vendor dependencies)
- `master/` (theme files)
- `ajax/` (AJAX files)
- `php/` (ALL PHP files and subdirectories)
- `uploads/` (file upload directory - must be writable)

#### Database
- `database.sqlite` (SQLite database file)

---

### Upload Methods

#### Option 1: Using cPanel File Manager

1. Log in to your GoDaddy account
2. Go to "My Products" → "Web Hosting" → "Manage"
3. Click on "cPanel Admin"
4. Navigate to "File Manager"
5. Go to `public_html` directory
6. Upload all files and folders listed above
7. Ensure file permissions are correct (see below)

#### Option 2: Using FTP

1. Get FTP credentials from GoDaddy cPanel:
   - FTP Host: `your-domain.com`
   - FTP Username: (from cPanel)
   - FTP Password: (from cPanel)
   - Port: 21

2. Use an FTP client (FileZilla, WinSCP, etc.) to connect
3. Navigate to `public_html` directory
4. Upload all files and folders
5. Set correct permissions (see below)

---

### File Permissions

Set the following permissions after upload:

#### Directories
- `uploads/` - `755` (writable by web server)
- `php/` - `755`
- All other directories - `755`

#### Files
- `database.sqlite` - `644` (readable by web server)
- All PHP files - `644`
- All HTML files - `644`
- `.htaccess` - `644`
- `php.ini` - `644`

**To set permissions via cPanel File Manager:**
1. Right-click on file/folder
2. Select "Change Permissions"
3. Set the numeric value
4. Click "Change Permissions"

**To set permissions via FTP:**
- Right-click → File permissions → Set numeric value

---

### Database Setup

The website uses SQLite database. The database file (`database.sqlite`) should already be in your project.

#### Initial Database Setup

1. Upload the existing `database.sqlite` file from your local project
2. Ensure it has correct permissions (`644`)
3. The database will work automatically with the relative paths configured

#### If Database Needs to be Recreated

1. Access your website via FTP or cPanel
2. Delete the existing `database.sqlite` file
3. Run the setup script in your browser:
   ```
   https://your-domain.com/php/setup-database.php
   ```
4. This will create a new database with default admin user:
   - Username: `admin`
   - Password: `admin123`
5. **IMPORTANT:** Change the default password immediately via admin panel

---

### PHP Configuration

The following PHP settings are already configured in your files:

#### In `.htaccess`:
```apache
php_value upload_max_filesize 25M
php_value post_max_size 30M
php_value max_execution_time 600
php_value max_input_time 600
php_value memory_limit 256M
```

#### In `php.ini`:
```ini
upload_max_filesize = 25M
post_max_size = 30M
max_execution_time = 600
max_input_time = 600
memory_limit = 256M
```

#### In PHP files:
- `php/contact-form.php` - Updated to 25MB limits
- All API files use relative database paths

**Note:** GoDaddy may have additional PHP limits. Check your hosting plan limits and adjust if needed.

---

### Security Configuration

The `.htaccess` file includes:

1. **Security Headers:**
   - X-Content-Type-Options: nosniff
   - X-Frame-Options: SAMEORIGIN
   - X-XSS-Protection: 1; mode=block

2. **Directory Browsing:** Disabled

3. **File Protection:**
   - Protects `.htaccess`, `.htpasswd`, `.ini`, `.log`, `.sh`, `.sql` files
   - Protects `database.sqlite` from direct access

---

### Email Configuration

The contact form uses PHPMailer. The email is configured in:

#### `php/contact-form.php`:
```php
$email = 'dondanaitik@gmail.com';
```

#### `php/submit-form.php`:
```php
$email = 'dondanaitik@gmail.com';
```

**To change the email:**
1. Edit these files via cPanel File Manager or FTP
2. Replace `dondanaitik@gmail.com` with your desired email
3. Save the changes

---

### Testing After Deployment

#### 1. Test Main Website
- Visit: `https://your-domain.com`
- Check if homepage loads correctly
- Navigate through all pages

#### 2. Test Contact Form
- Visit: `https://your-domain.com/demo-auto-services-contact.html`
- Fill out the form with test data
- Upload a small test file (under 25MB)
- Submit the form
- Check if email is received
- Check if submission is saved in database

#### 3. Test Admin Panel
- Visit: `https://your-domain.com/admin.html`
- Login with:
  - Username: `admin`
  - Password: `admin123` (or your changed password)
- Test viewing submissions
- Test editing submissions
- Test deleting submissions
- Test file downloads

#### 4. Test File Uploads
- Try uploading files of various sizes
- Verify files are saved in `uploads/` directory
- Check if files are accessible via admin panel

---

### Troubleshooting

#### Issue: 500 Internal Server Error

**Possible Causes:**
- File permissions incorrect
- PHP syntax error
- .htaccess configuration issue

**Solutions:**
1. Check file permissions (should be 644 for files, 755 for directories)
2. Check error logs in cPanel → "Errors" section
3. Temporarily rename `.htaccess` to test if it's the issue
4. Ensure PHP version is compatible (PHP 7.4 or higher recommended)

#### Issue: Database Connection Error

**Possible Causes:**
- Database file not uploaded
- Incorrect database path
- Database file permissions

**Solutions:**
1. Verify `database.sqlite` exists in the HTML directory
2. Check file permissions (should be 644)
3. Run setup script: `https://your-domain.com/php/setup-database.php`

#### Issue: File Upload Not Working

**Possible Causes:**
- Uploads directory not writable
- PHP upload limits too low
- File size exceeds limit

**Solutions:**
1. Set `uploads/` directory permissions to 755 or 777
2. Check PHP upload limits in cPanel → "Select PHP Version"
3. Verify file is under 25MB

#### Issue: Email Not Sending

**Possible Causes:**
- PHPMailer not configured
- SMTP settings missing
- Email blocked by spam filters

**Solutions:**
1. Check PHP error logs
2. Verify email configuration in PHP files
3. Test with different email address
4. Check spam folder

#### Issue: Admin Panel Not Loading

**Possible Causes:**
- Session not starting
- Database connection error
- JavaScript error

**Solutions:**
1. Clear browser cache
2. Check browser console for JavaScript errors
3. Verify database connection
4. Check if session.save_path is writable

---

### Post-Deployment Tasks

1. **Change Default Admin Password**
   - Login to admin panel
   - Update password immediately

2. **Update Email Configuration**
   - Change email to your business email
   - Test email functionality

3. **Configure SSL Certificate**
   - Ensure HTTPS is enabled
   - Update any hardcoded HTTP links to HTTPS

4. **Set Up Regular Backups**
   - Backup database regularly
   - Backup uploaded files
   - Use GoDaddy backup feature or manual backups

5. **Monitor Website Performance**
   - Check load times
   - Monitor error logs
   - Test all forms regularly

---

### GoDaddy-Specific Notes

1. **PHP Version:**
   - GoDaddy supports multiple PHP versions
   - Recommended: PHP 7.4 or PHP 8.0+
   - Change via cPanel → "Select PHP Version"

2. **File Manager Limits:**
   - GoDaddy File Manager may have upload size limits
   - Use FTP for large file uploads
   - Upload directories separately if needed

3. **Database Location:**
   - SQLite database file should be in the same directory as PHP files
   - Ensure it's not in a publicly accessible subdirectory

4. **Session Configuration:**
   - GoDaddy may require specific session.save_path
   - Check cPanel PHP settings if sessions don't work

5. **Email Sending:**
   - GoDaddy may block certain email ports
   - Use PHPMailer with SMTP for reliable email sending
   - Consider using GoDaddy's SMTP settings

---

### Support Resources

- **GoDaddy Support:** https://www.godaddy.com/help
- **cPanel Documentation:** https://docs.cpanel.net/
- **PHP Documentation:** https://www.php.net/docs.php

---

### Quick Reference

**Default Admin Credentials:**
- Username: `admin`
- Password: `admin123`

**File Upload Limit:** 25MB

**Database:** SQLite (database.sqlite)

**Email Configuration:** Edit in `php/contact-form.php` and `php/submit-form.php`

**PHP Version Required:** 7.4 or higher

---

### Deployment Summary

✅ All hardcoded paths updated to relative paths
✅ File size limits set to 25MB
✅ Database configuration uses SQLite with relative paths
✅ Security headers configured in .htaccess
✅ File upload directories configured
✅ Email configuration ready for customization

**Your website is ready for GoDaddy deployment!**

---

*Last Updated: June 2, 2026*
