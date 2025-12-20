# Production Deployment Instructions for vuyanimagibisela.co.za

## ✅ Security Fixes Applied

All critical security issues have been fixed:
- ✅ Test files deleted (test-db.php, test_auth.php, create_test_user.php, etc.)
- ✅ Error display disabled (errors now log to `/logs/php-errors.log`)
- ✅ XSS vulnerability fixed in page titles
- ✅ Upload directories protected with .htaccess
- ✅ .htaccess updated for production (RewriteBase set to `/`)
- ✅ Database credentials secured (comments removed)
- ✅ Email consistency fixed (using .co.za domain)

## 📦 Files Ready for Deployment

Location: `/var/www/html/vuyaniM01/portfolio-website/`

This directory is now production-ready and can be uploaded to your hosting server.

---

## 🚀 Step-by-Step Deployment Guide

### Step 1: Create ZIP Package for Upload

Run this command to create a deployment package:

```bash
cd /var/www/html/vuyaniM01/portfolio-website
zip -r production-deploy.zip . -x "*.git*" -x "*.md" -x "COMMIT_MESSAGE.md" -x "logs/*"
```

The ZIP file will be created at: `/var/www/html/vuyaniM01/portfolio-website/production-deploy.zip`

### Step 2: Upload Files to cPanel

#### Option A: Using cPanel File Manager (Recommended)

1. **Log into cPanel** at your hosting provider's control panel
2. **Open File Manager**
3. **Navigate to `public_html/` directory**
4. **Upload the ZIP file**:
   - Click "Upload" button
   - Select `production-deploy.zip`
   - Wait for upload to complete
5. **Extract the ZIP**:
   - Right-click on `production-deploy.zip`
   - Select "Extract"
   - Extract to current directory (`public_html/`)
6. **Delete the ZIP file** after extraction
7. **Verify files**:
   - You should see: `css/`, `js/`, `images/`, `uploads/`, `index.php`, `.htaccess`, `app/`, etc.

#### Option B: Using FTP Client (FileZilla)

1. **Connect via FTP**:
   - Host: Your hosting server address
   - Username: Your cPanel username
   - Password: Your cPanel password
   - Port: 21 (or 22 for SFTP)

2. **Navigate to `public_html/` directory**

3. **Upload all files** from `/var/www/html/vuyaniM01/portfolio-website/` to `public_html/`
   - Upload `public/` contents to root of `public_html/`
   - Upload `app/` directory to `public_html/app/`
   - Upload `.htaccess` to `public_html/.htaccess`

### Step 3: Set Up Database

#### 3.1 Verify Database Exists

1. In cPanel, go to **MySQL® Databases**
2. Check if database `vuyanjcb_vuyanim` exists
3. If not, create it:
   - Database Name: `vuyanjcb_vuyanim`
4. Verify user `vuyanjcb_vuyaniM` exists and has **ALL PRIVILEGES** on the database

#### 3.2 Run Database Migrations

Since your database is currently empty, you'll need to create the tables. You have two options:

**Option A: Using migrate.php (if available in hosting-structure)**

1. Upload the `hosting-structure/public_html/migrate.php` to your server
2. Access it via browser: `https://vuyanimagibisela.co.za/migrate.php`
3. Enter admin password
4. Click "Run Migrations"

**Option B: Manual via phpMyAdmin**

1. In cPanel, open **phpMyAdmin**
2. Select database `vuyanjcb_vuyanim`
3. Click "SQL" tab
4. Run the following SQL to create the users table:

```sql
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

5. Create the admin user:

```sql
INSERT INTO users (username, email, password, first_name, last_name, role, is_active, created_at, updated_at)
VALUES ('admin', 'admin@vuyanimagibisela.co.za', '$2y$12$XtLfw4dGQBUCAtvkFaKRGOaSf3rI/mdYoadxgwRUEkh4QxYS9/ClK', 'Vuyani', 'Magibisela', 'admin', 1, NOW(), NOW());
```

**Admin Login Credentials:**
- Username: `admin`
- Password: `Admin@2025`

6. Run additional table creation scripts for:
   - `blog_posts`
   - `categories`
   - `projects`
   - `resources`
   - `contacts`
   - Other required tables

### Step 4: Set File Permissions

#### Via cPanel File Manager:

1. **For directories** - Set to `755`:
   - Right-click directory → Change Permissions
   - Set: Owner: Read+Write+Execute, Group: Read+Execute, World: Read+Execute
   - Apply to: This Directory and All Subdirectories

2. **For upload directories** - Set to `775`:
   - `/public_html/images/blog/uploads/`
   - `/public_html/images/projects/uploads/`
   - `/public_html/images/projects/gallery/`
   - `/public_html/uploads/resources/`

3. **For files** - Set to `644`:
   - Owner: Read+Write, Group: Read, World: Read

#### Via SSH (if available):

```bash
# Set directory permissions
find /home/vuyanjcb/public_html -type d -exec chmod 755 {} \;

# Set file permissions
find /home/vuyanjcb/public_html -type f -exec chmod 644 {} \;

# Set upload directory permissions
chmod 775 /home/vuyanjcb/public_html/images/blog/uploads/
chmod 775 /home/vuyanjcb/public_html/images/projects/uploads/
chmod 775 /home/vuyanjcb/public_html/images/projects/gallery/
chmod 775 /home/vuyanjcb/public_html/uploads/resources/

# Create logs directory (outside public_html if possible)
mkdir -p /home/vuyanjcb/logs
chmod 755 /home/vuyanjcb/logs
```

### Step 5: Install SSL Certificate

1. In cPanel, go to **SSL/TLS Status** or **Let's Encrypt SSL**
2. Select domain: `vuyanimagibisela.co.za`
3. Click **Install SSL Certificate**
4. Wait for installation (usually automatic with Let's Encrypt)
5. Verify HTTPS works: `https://vuyanimagibisela.co.za`

The `.htaccess` file is already configured to force HTTPS redirects.

### Step 6: Test Your Website

#### 6.1 Homepage Test
Visit: `https://vuyanimagibisela.co.za`
- ✅ Page should load without errors
- ✅ No error messages visible
- ✅ CSS and images loading correctly

#### 6.2 Test Admin Login
Visit: `https://vuyanimagibisela.co.za/auth`
- Login with: `admin` / `Admin@2025`
- ✅ Should redirect to admin dashboard
- ✅ Admin panel loads correctly

#### 6.3 Test Core Features
- ✅ Blog page loads: `/blog`
- ✅ Projects page loads: `/projects`
- ✅ Contact form loads: `/contact`
- ✅ About page loads: `/about`

#### 6.4 Test Admin Features
- ✅ Create blog post
- ✅ Upload images
- ✅ Create project
- ✅ Manage users
- ✅ View contact submissions

#### 6.5 Security Verification
- ❌ Test files should return 404: `/test-db.php`, `/test_auth.php`
- ✅ Error pages don't expose file paths
- ✅ Upload directories don't execute PHP: Try accessing `https://vuyanimagibisela.co.za/images/blog/uploads/test.php` (should be blocked)

### Step 7: Monitor Error Logs

Check for any errors after deployment:

1. In cPanel, go to **Error Log** or **Metrics → Errors**
2. Review recent errors
3. Or check the custom error log at: `/home/vuyanjcb/logs/php-errors.log`

---

## 📧 Email Configuration (Optional)

If contact form emails aren't sending:

### Via cPanel Email Settings:

1. **Create email account**: `admin@vuyanimagibisela.co.za` in cPanel
2. **Configure SPF record** in DNS:
   ```
   v=spf1 a mx include:yourhostingprovider.com ~all
   ```
3. **Test email sending** from contact form

### SMTP Configuration (if needed):

The code is already configured to use:
- Host: `mail.vuyanimagibisela.co.za`
- Port: `587` (TLS)
- Username: `admin@vuyanimagibisela.co.za`
- Password: (set in cPanel email account)

---

## 🔄 Post-Deployment Tasks

### Day 1:
- [ ] Test all website features
- [ ] Add your first blog post
- [ ] Add your projects
- [ ] Update About page content
- [ ] Test contact form submissions

### Week 1:
- [ ] Monitor error logs daily
- [ ] Check website performance
- [ ] Test on multiple browsers/devices
- [ ] Add Google Analytics (optional)

### Ongoing:
- [ ] Regular backups (weekly)
- [ ] Update blog content regularly
- [ ] Monitor and respond to contact form messages
- [ ] Review error logs weekly
- [ ] Update admin password periodically

---

## 🔧 Troubleshooting

### Issue: Website shows 500 Internal Server Error
**Solution:**
1. Check error logs in cPanel
2. Verify `.htaccess` syntax is correct
3. Check file permissions (directories: 755, files: 644)
4. Verify database credentials in `/app/config/database.php`

### Issue: Database connection failed
**Solution:**
1. Verify database `vuyanjcb_vuyanim` exists
2. Verify user `vuyanjcb_vuyaniM` has privileges
3. Check password is correct: `=bQw^WUglto@IhRJ`
4. Ensure database server is `localhost`

### Issue: Images not uploading
**Solution:**
1. Check upload directory permissions (should be 775)
2. Verify directories owned by web server user
3. Check PHP upload_max_filesize in cPanel PHP Settings
4. Verify `.htaccess` exists in upload directories

### Issue: Admin panel not accessible
**Solution:**
1. Verify admin user exists in database
2. Check password hash is correct
3. Clear browser cookies
4. Try password reset via database

### Issue: CSS/JS not loading
**Solution:**
1. Clear browser cache (Ctrl+F5)
2. Verify files uploaded to correct directories
3. Check `.htaccess` RewriteBase is `/`
4. Verify file permissions (644 for files)

---

## 📊 Production Configuration Summary

**Environment:** Production (auto-detected when not localhost)

**Database:**
- Host: `localhost`
- Database: `vuyanjcb_vuyanim`
- User: `vuyanjcb_vuyaniM`

**URLs:**
- Website: `https://vuyanimagibisela.co.za`
- Admin: `https://vuyanimagibisela.co.za/auth`
- Blog: `https://vuyanimagibisela.co.za/blog`

**Email:**
- Contact: `admin@vuyanimagibisela.co.za`
- SMTP: `mail.vuyanimagibisela.co.za:587`

**Security:**
- Error display: Disabled (logged to file)
- Test files: Deleted
- Upload execution: Blocked
- HTTPS: Enforced

---

## ✅ Deployment Checklist

- [ ] Files uploaded to `public_html/`
- [ ] Database `vuyanjcb_vuyanim` exists and configured
- [ ] Admin user created (admin / Admin@2025)
- [ ] File permissions set correctly
- [ ] SSL certificate installed
- [ ] Website accessible via HTTPS
- [ ] Admin login works
- [ ] Test files return 404
- [ ] No errors in error log
- [ ] Contact form tested
- [ ] Blog/Projects features tested
- [ ] Image uploads working

---

## 🎉 Success!

Once all checklist items are complete, your portfolio website is live at:
### https://vuyanimagibisela.co.za

Log in to the admin panel and start adding your content!

---

**Support:** If you encounter any issues, check the error logs first, then review the troubleshooting section above.

**Last Updated:** December 18, 2025
