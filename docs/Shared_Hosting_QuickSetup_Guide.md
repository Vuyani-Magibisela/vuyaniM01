# 🚀 Shared Hosting Quick Setup Guide

## Step 1: Prepare Your Local Environment

### Create the new directory structure:
```
your-project/
├── private_files/              ← Will go outside public_html
│   ├── app/                   ← Your application code
│   ├── database/              ← Migrations and data
│   ├── logs/                  ← Error logs
│   ├── cache/                 ← Cache files
│   ├── backups/               ← Database backups
│   └── .env                   ← Environment variables
└── public_html/               ← Will go to your domain root
    ├── css/
    ├── js/
    ├── images/
    ├── uploads/
    ├── index.php
    └── .htaccess
```

### Move your existing files:
1. **Move to private_files/app/:**
   - `app/` folder (controllers, models, views, config, core)
   
2. **Move to public_html/:**
   - `public/css/` → `css/`
   - `public/js/` → `js/`
   - `public/images/` → `images/`
   - `public/index.php` → `index.php`

## Step 2: Update File Paths

### Update `public_html/index.php`:
```php
<?php
// Load environment configuration first
require_once __DIR__ . '/../private_files/app/config/config.php';

// Initialize security
require_once __DIR__ . '/../private_files/app/core/SharedHostingSecurity.php';
use App\core\SharedHostingSecurity;
SharedHostingSecurity::init();

// Load other core files
require_once __DIR__ . '/../private_files/app/core/App.php';
require_once __DIR__ . '/../private_files/app/core/Router.php';
require_once __DIR__ . '/../private_files/app/core/Database.php';
require_once __DIR__ . '/../private_files/app/core/Helpers.php';

use App\core\Router;

$router = new Router();
$router->dispatch();
```

### Update config files with correct paths:
In `private_files/app/config/config.development.php`, change:
```php
'url' => 'http://localhost/your-project/public_html',
```

## Step 3: Set Up cPanel Database

### In your hosting cPanel:

1. **Go to MySQL Databases**
2. **Create Database:** `username_vuyanim_prod`
3. **Create User:** `username_dbuser` with strong password
4. **Add User to Database** with ALL PRIVILEGES
5. **Note down:** Database name, username, password for config

### Update database config:
In `private_files/app/config/config.production.php`:
```php
'database' => [
    'host' => 'localhost',
    'dbname' => 'your_cpanel_username_vuyanim_prod',
    'user' => 'your_cpanel_username_dbuser', 
    'password' => 'your_db_password_from_cpanel',
    'charset' => 'utf8mb4'
],
```

## Step 4: Upload Files via FTP/File Manager

### Upload Structure:
```
Your Hosting Account/
├── public_html/               ← Your domain files
│   ├── css/
│   ├── js/
│   ├── images/
│   ├── uploads/               ← Create this, set to 755
│   ├── index.php
│   └── .htaccess
└── private_files/             ← Create this outside public_html
    ├── app/
    ├── database/
    ├── logs/                  ← Set to 755
    ├── cache/                 ← Set to 755
    ├── backups/               ← Set to 755
    └── .env
```

### File Permissions (via cPanel File Manager):
- **Folders:** 755 (uploads/, logs/, cache/, backups/)
- **Files:** 644 (all .php, .css, .js files)
- **Special:** .htaccess files (644)

## Step 5: Set Up Environment File

### Create `private_files/.env`:
```bash
# Production Environment
APP_ENV=production
APP_URL=https://yourdomain.com
APP_DEBUG=false

# Database (from cPanel)
DB_HOST=localhost
DB_NAME=your_cpanel_username_vuyanim_prod
DB_USER=your_cpanel_username_dbuser
DB_PASSWORD=your_db_password_from_cpanel

# Mail (from cPanel email accounts)
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=contact@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls

# Security Passwords
DEPLOY_PASSWORD=your_secure_deploy_password
BACKUP_PASSWORD=your_secure_backup_password
HEALTH_PASSWORD=your_secure_health_password
ADMIN_PASSWORD=your_secure_admin_password
```

## Step 6: Run Initial Setup

### 1. Run Database Migrations:
Visit: `https://yourdomain.com/private_files/database/web_migrate.php?run=migrations`

### 2. Check System Health:
Visit: `https://yourdomain.com/scripts/web_health_check.php`

### 3. Test Your Site:
Visit: `https://yourdomain.com` (should show your homepage)

## Step 7: Security Hardening

### Create `.htaccess` files for protection:

**`private_files/.htaccess`:**
```apache
Order Deny,Allow
Deny from all
```

**`private_files/app/.htaccess`:**
```apache
Order Deny,Allow
Deny from all
```

**`private_files/logs/.htaccess`:**
```apache
Order Deny,Allow
Deny from all
```

**`public_html/.htaccess`:**
```apache
# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Hide sensitive files
<FilesMatch "\.(env|log|sql|md)$">
    Require all denied
</FilesMatch>

# Prevent access to private files
RedirectMatch 404 /private_files
RedirectMatch 404 /\.git

# URL Rewriting
RewriteEngine On

# Force HTTPS (if SSL available)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Route requests through index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [L,QSA]
```

## Step 8: Development Workflow

### For Local Development:
1. Work on `localhost` with development database
2. Test all features locally
3. Export changes using `export_structure.php`

### For Production Updates:
1. **Upload changed files** via FTP/File Manager
2. **Run migrations** via web interface
3. **Clear cache** if needed
4. **Test functionality**
5. **Create backup** after successful update

## Step 9: Ongoing Maintenance

### Regular Tasks:
- **Weekly:** Check health dashboard
- **Monthly:** Create database backup
- **As needed:** Update files via FTP

### Available Tools:
- **Health Check:** `yourdomain.com/scripts/web_health_check.php`
- **Backups:** `yourdomain.com/scripts/web_backup.php`
- **Migrations:** `yourdomain.com/private_files/database/web_migrate.php?run=migrations`
- **Deployment:** `yourdomain.com/scripts/ftp_deploy.php`

## Troubleshooting Common Issues

### 1. "Permission Denied" Errors:
- Check folder permissions (755 for folders, 644 for files)
- Ensure uploads/, logs/, cache/ are writable

### 2. Database Connection Failed:
- Verify database name format: `username_databasename`
- Check user has ALL PRIVILEGES on database
- Confirm password is correct

### 3. 500 Internal Server Error:
- Check error logs in cPanel → Error Logs
- Verify .htaccess syntax
- Ensure all required files are uploaded

### 4. CSS/JS Not Loading:
- Check file paths in config
- Verify files uploaded to correct public_html folders
- Clear browser cache

### 5. Environment Not Detected Correctly:
- Check domain name in environment detection logic
- Update allowed domains in config files
- Verify .env file location and permissions

## File Upload Checklist

### ✅ Before uploading:
- [ ] Update all file paths for shared hosting structure
- [ ] Set correct database credentials
- [ ] Remove development files (.git, tests/, debug/)
- [ ] Update domain URLs in config
- [ ] Set strong passwords for admin tools

### ✅ After uploading:
- [ ] Set correct file permissions
- [ ] Create .htaccess protection files
- [ ] Run database migrations
- [ ] Test all main pages
- [ ] Check health dashboard
- [ ] Create initial backup

### ✅ Security verification:
- [ ] Verify private_files/ not accessible via web
- [ ] Test that .env file is not downloadable
- [ ] Confirm admin tools require passwords
- [ ] Check HTTPS is working
- [ ] Verify error logs go to private directory

## Emergency Recovery

### If site goes down:
1. **Check error logs** in cPanel
2. **Restore from backup** if needed
3. **Use health check** to identify issues
4. **Contact hosting support** if server-related

### Quick fixes:
- **500 Error:** Check .htaccess syntax
- **Database Error:** Verify credentials in .env
- **File Not Found:** Check file paths and uploads
- **Permission Error:** Reset folder permissions to 755

This setup gives you a professional, secure, and manageable shared hosting deployment! 🚀

Now create the essential files needed to implement this system:
<?php
// FILE 1: private_files/app/config/env.php
// Create this file exactly as shown in the main guide

class Environment {
    private static $environment = null;
    
    public static function detect() {
        if (self::$environment === null) {
            // Check for environment variable first
            if (isset($_ENV['APP_ENV'])) {
                self::$environment = $_ENV['APP_ENV'];
            } elseif (isset($_SERVER['APP_ENV'])) {
                self::$environment = $_SERVER['APP_ENV'];
            } else {
                // Auto-detect based on domain/server characteristics
                $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
                
                if (strpos($host, 'localhost') !== false || 
                    strpos($host, '127.0.0.1') !== false || 
                    strpos($host, '.local') !== false ||
                    strpos($host, '.test') !== false) {
                    self::$environment = 'development';
                } elseif (strpos($host, 'staging') !== false || 
                         strpos($host, 'test') !== false) {
                    self::$environment = 'staging';
                } else {
                    self::$environment = 'production';
                }
            }
        }
        
        return self::$environment;
    }
    
    public static function isDevelopment() {
        return self::detect() === 'development';
    }
    
    public static function isProduction() {
        return self::detect() === 'production';
    }
    
    public static function isStaging() {
        return self::detect() === 'staging';
    }
}

// =====================================

// FILE 2: private_files/app/config/config.development.php
<?php
return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'vuyanim_dev',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'Vuyani Portfolio (Dev)',
        'url' => 'http://localhost/your-project/public_html',
        'debug' => true,
        'log_level' => 'debug'
    ],
    'mail' => [
        'driver' => 'log',
        'host' => 'localhost',
        'port' => 1025,
        'username' => '',
        'password' => '',
        'encryption' => null,
        'from_address' => 'dev@vuyanim.local',
        'from_name' => 'Vuyani Dev'
    ],
    'storage' => [
        'uploads_path' => '/uploads/dev/',
        'max_file_size' => '10M'
    ]
];

// =====================================

// FILE 3: private_files/app/config/config.production.php
<?php
return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'cpanel_username_vuyanim_prod', // UPDATE THIS
        'user' => 'cpanel_username_dbuser',         // UPDATE THIS
        'password' => 'your_db_password',           // UPDATE THIS
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'Vuyani Magibisela Portfolio',
        'url' => 'https://yourdomain.com',          // UPDATE THIS
        'debug' => false,
        'log_level' => 'error'
    ],
    'mail' => [
        'driver' => 'smtp',
        'host' => 'mail.yourdomain.com',            // UPDATE THIS
        'port' => 587,
        'username' => 'contact@yourdomain.com',     // UPDATE THIS
        'password' => 'your_email_password',        // UPDATE THIS
        'encryption' => 'tls',
        'from_address' => 'contact@yourdomain.com', // UPDATE THIS
        'from_name' => 'Vuyani Magibisela'
    ],
    'storage' => [
        'uploads_path' => '/uploads/',
        'max_file_size' => '5M'
    ],
    'paths' => [
        'root' => '/home/username/public_html/',    // UPDATE THIS
        'logs' => '/home/username/private_files/logs/',
        'cache' => '/home/username/private_files/cache/',
        'backups' => '/home/username/private_files/backups/'
    ]
];

// =====================================

// FILE 4: private_files/app/config/config.php (REPLACE YOUR EXISTING)
<?php
require_once __DIR__ . '/env.php';

// Load environment-specific configuration
$environment = Environment::detect();
$config = require __DIR__ . '/config.' . $environment . '.php';

// Set global variables for backward compatibility
$baseUrl = $config['app']['url'];

// Define constants
define('APP_ENV', $environment);
define('APP_DEBUG', $config['app']['debug']);
define('APP_NAME', $config['app']['name']);
define('APP_URL', $config['app']['url']);

// Return config for use in other parts of the application
return $config;

// =====================================

// FILE 5: private_files/app/config/database.php (REPLACE YOUR EXISTING)
<?php
$config = require __DIR__ . '/config.php';
return $config['database'];

// =====================================

// FILE 6: public_html/index.php (REPLACE YOUR EXISTING)
<?php
// Load environment configuration first
require_once __DIR__ . '/../private_files/app/config/config.php';

// Initialize security
require_once __DIR__ . '/../private_files/app/core/SharedHostingSecurity.php';
use App\core\SharedHostingSecurity;
SharedHostingSecurity::init();

// Load other core files
require_once __DIR__ . '/../private_files/app/core/App.php';
require_once __DIR__ . '/../private_files/app/core/Router.php';
require_once __DIR__ . '/../private_files/app/core/Database.php';
require_once __DIR__ . '/../private_files/app/core/Helpers.php';

use App\core\Router;

$router = new Router();
$router->dispatch();

Required .htaccess Files: 
# FILE 1: public_html/.htaccess
# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Hide sensitive files
<FilesMatch "\.(env|log|sql|md)$">
    Require all denied
</FilesMatch>

# Hide directories
RedirectMatch 404 /\.git
RedirectMatch 404 /private_files
RedirectMatch 404 /logs
RedirectMatch 404 /cache
RedirectMatch 404 /backups

# Prevent access to PHP files in uploads
<Directory "uploads">
    <FilesMatch "\.php$">
        Require all denied
    </FilesMatch>
</Directory>

# URL Rewriting
RewriteEngine On

# Force HTTPS (if SSL is available)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Remove trailing slashes
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)/$ /$1 [L,R=301]

# Route all requests through index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [L,QSA]

# Set cache headers for static files
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
</IfModule>

# =====================================

# FILE 2: private_files/.htaccess
Order Deny,Allow
Deny from all

# =====================================

# FILE 3: private_files/app/.htaccess
Order Deny,Allow
Deny from all

# =====================================

# FILE 4: private_files/logs/.htaccess
Order Deny,Allow
Deny from all

# =====================================

# FILE 5: private_files/cache/.htaccess
Order Deny,Allow
Deny from all

# =====================================

# FILE 6: private_files/backups/.htaccess
Order Deny,Allow
Deny from all

Summary of What This System Has:
🎯 Key Benefits for Shared Hosting:

🔧 Automatic Environment Detection

No manual switching between dev/production configs
Based on domain name (localhost = dev, yourdomain.com = production)


🗄️ Web-Based Database Management

Run migrations through web interface
Create backups via browser
Export/import via phpMyAdmin integration


📁 Secure File Structure

Sensitive files outside public_html
.htaccess protection for all private directories
Proper permission management


🚀 FTP-Friendly Deployment

Clear instructions for manual file uploads
Automated file sync helpers
Post-upload verification tools


📊 Web-Based Monitoring

Health check dashboard accessible via browser
System status monitoring
Error log viewing



🛠️ Essential Tools s aquired:
ToolURLPurposeHealth Checkyourdomain.com/scripts/web_health_check.phpMonitor system statusDatabase Backupyourdomain.com/scripts/web_backup.phpCreate/download backupsRun Migrationsyourdomain.com/private_files/database/web_migrate.php?run=migrationsUpdate databaseDeployment Helperyourdomain.com/scripts/ftp_deploy.phpManage deployments
🔒 Security Features:

✅ All sensitive files outside public_html
✅ .htaccess protection on all private directories
✅ CSRF token protection
✅ XSS and injection prevention
✅ Secure session configuration
✅ HTTPS enforcement
✅ Password-protected admin tools

📋 Your Action Plan:

Create the directory structure as shown
Move your existing files to the new locations
Create the essential files from the artifacts above
Update the file paths in your existing code
Test locally before uploading
Upload to shared hosting via FTP/File Manager
Set folder permissions (755 for folders, 644 for files)
Run the web migration tool
Test all functionality
Create your first backup

This system is specifically designed to work within shared hosting limitations while giving you professional-level environment management and deployment capabilities.
