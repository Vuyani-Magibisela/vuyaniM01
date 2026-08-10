#!/bin/bash
set -e

APP=/var/www/html

# Install PHP dependencies on first boot (vendor/ is git-ignored).
if [ ! -f "$APP/vendor/phpmailer/phpmailer/src/PHPMailer.php" ]; then
    echo "==> vendor/ incomplete — running composer install"
    composer install --working-dir="$APP" --no-interaction --prefer-dist --optimize-autoloader || \
        echo "!! composer install failed — check network, then run: docker compose exec app composer install"
fi

# Writable runtime directories.
mkdir -p "$APP/logs" \
         "$APP/public/images/blog/uploads" \
         "$APP/public/images/projects/uploads" \
         "$APP/public/images/projects/gallery" \
         "$APP/public/uploads/resources"
chown -R www-data:www-data "$APP/logs" "$APP/public/images" "$APP/public/uploads" 2>/dev/null || true

exec "$@"
