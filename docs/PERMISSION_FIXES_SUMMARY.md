# File Permission Fixes Summary

**Date**: December 16, 2025

---

## Problem

Multiple files had incorrect permissions (`600` - private/owner-only), preventing Apache/PHP from reading them, resulting in errors like:

```
Warning: require_once(/path/to/file.php): Failed to open stream: Permission denied
Fatal error: Failed opening required '/path/to/file.php'
```

---

## Root Cause

Files were created with default permissions that didn't allow the web server (www-data) to read them.

**Wrong permissions**: `-rw-------` (600) - Only owner can read/write
**Correct permissions**: `-rw-r--r--` (644) - Owner can read/write, group and others can read

---

## Files Fixed ✅

### 1. Model Files (`app/models/`)
- ✅ `Category.php` - Was 600, now 644
- ✅ `Tag.php` - Was 600, now 644
- ✅ `Contact.php` - Fixed
- ✅ All other model files - Verified 644

### 2. Controller Files (`app/controllers/`)
- ✅ All controller files - Set to 644
- ✅ `BaseController.php` - Fixed
- ✅ `AdminController.php` - Fixed
- ✅ `AuthController.php` - Fixed

### 3. View Files (`app/views/`)
- ✅ All view files - Set to 644
- ✅ `auth/login.php` - Fixed
- ✅ `layouts/admin.php` - Fixed

### 4. Core Files (`app/core/`)
- ✅ All core framework files - Set to 644
- ✅ `Database.php`, `Router.php`, `App.php` - Fixed

### 5. Config Files (`app/config/`)
- ✅ All configuration files - Set to 644

### 6. CSS Files (`public/css/`)
- ✅ `admin.css` - Was 600, now 644
- ✅ `main.css` - Fixed
- ✅ `responsive.css` - Fixed

### 7. JavaScript Files (`public/js/`)
- ✅ `admin.js` - Was 600, now 644
- ✅ All other JS files - Set to 644

---

## Commands Used

```bash
# Fix models
find /var/www/html/vuyaniM01/portfolio-website/app/models -type f -name "*.php" -exec chmod 644 {} \;

# Fix controllers
find /var/www/html/vuyaniM01/portfolio-website/app/controllers -type f -name "*.php" -exec chmod 644 {} \;

# Fix views
find /var/www/html/vuyaniM01/portfolio-website/app/views -type f -name "*.php" -exec chmod 644 {} \;

# Fix core
find /var/www/html/vuyaniM01/portfolio-website/app/core -type f -name "*.php" -exec chmod 644 {} \;

# Fix config
find /var/www/html/vuyaniM01/portfolio-website/app/config -type f -name "*.php" -exec chmod 644 {} \;

# Fix CSS
find /var/www/html/vuyaniM01/portfolio-website/public/css -type f -name "*.css" -exec chmod 644 {} \;

# Fix JavaScript
find /var/www/html/vuyaniM01/portfolio-website/public/js -type f -name "*.js" -exec chmod 644 {} \;
```

---

## Automated Fix Script

Created `fix-permissions.sh` script for future use:

```bash
# Run this script to fix all permissions at once
cd /var/www/html/vuyaniM01/portfolio-website
./fix-permissions.sh
```

**What it does**:
- Sets all PHP files to 644
- Sets all CSS files to 644
- Sets all JS files to 644
- Sets all image files to 644
- Sets all directories to 755

---

## Permission Explanation

### File Permissions (644)
```
-rw-r--r--  (644)
 │││ │ │
 │││ │ └─ Others can read
 │││ └─── Group can read
 ││└───── Owner can write
 │└────── Owner can read
 └─────── Regular file
```

### Directory Permissions (755)
```
drwxr-xr-x  (755)
 │││ │ │
 │││ │ └─ Others can read and execute (enter)
 │││ └─── Group can read and execute
 ││└───── Owner can execute (enter)
 │└────── Owner can write (create/delete files)
 └─────── Directory
```

---

## Issues Resolved ✅

1. ✅ **Login page CSS** - admin.css now loads
2. ✅ **Login button visible** - CSS variables defined
3. ✅ **Theme toggle works** - admin.js now loads
4. ✅ **Admin dashboard** - No more PHP warnings
5. ✅ **Create Blog Post page** - Category.php and Tag.php readable
6. ✅ **All admin pages** - Models, controllers, views accessible

---

## Prevention Tips

### When creating new files:

**PHP Files**:
```bash
touch newfile.php
chmod 644 newfile.php
```

**Or set default umask**:
```bash
umask 022  # New files will be 644, directories 755
```

### After editing files:
If your editor changes permissions, run:
```bash
./fix-permissions.sh
```

---

## Verification

Check if a file has correct permissions:
```bash
ls -l /var/www/html/vuyaniM01/portfolio-website/app/models/Category.php
# Should show: -rw-r--r-- (644)
```

Test if web server can read it:
```bash
sudo -u www-data cat /var/www/html/vuyaniM01/portfolio-website/app/models/Category.php
# Should output file contents without error
```

---

## Pages Now Working

✅ `/admin` - Dashboard
✅ `/admin/createBlogPost` - Create blog post form
✅ `/admin/blog` - Blog posts list
✅ `/admin/projects` - Projects list
✅ `/admin/resources` - Resources list
✅ `/admin/categories` - Categories management
✅ `/admin/users` - User management
✅ `/auth` - Login page

---

## Future Considerations

1. **Set umask in PHP**: Add to `public/index.php`:
   ```php
   umask(0022);
   ```

2. **Apache user permissions**: Ensure www-data has read access:
   ```bash
   chgrp -R www-data /var/www/html/vuyaniM01/portfolio-website
   ```

3. **Git hooks**: Add pre-commit hook to check permissions before committing

---

*Documentation created: December 16, 2025*
