#!/bin/bash
# Fix file permissions for the portfolio website
# Run this script if you encounter "Permission denied" errors

echo "Fixing file permissions for portfolio website..."
echo ""

# Fix PHP files in app directory
echo "Fixing PHP files in app directory..."
find /var/www/html/vuyaniM01/portfolio-website/app -type f -name "*.php" -exec chmod 644 {} \;

# Fix CSS files
echo "Fixing CSS files..."
find /var/www/html/vuyaniM01/portfolio-website/public/css -type f -name "*.css" -exec chmod 644 {} \;

# Fix JavaScript files
echo "Fixing JavaScript files..."
find /var/www/html/vuyaniM01/portfolio-website/public/js -type f -name "*.js" -exec chmod 644 {} \;

# Fix image files
echo "Fixing image files..."
find /var/www/html/vuyaniM01/portfolio-website/public/images -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.svg" -o -name "*.ico" \) -exec chmod 644 {} \;

# Fix directory permissions
echo "Fixing directory permissions..."
find /var/www/html/vuyaniM01/portfolio-website -type d -exec chmod 755 {} \;

echo ""
echo "✅ Permissions fixed successfully!"
echo ""
echo "Summary of permissions set:"
echo "  - PHP files: 644 (rw-r--r--)"
echo "  - CSS files: 644 (rw-r--r--)"
echo "  - JS files:  644 (rw-r--r--)"
echo "  - Images:    644 (rw-r--r--)"
echo "  - Directories: 755 (rwxr-xr-x)"
