FROM php:8.3-apache

# --- System deps ---------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libicu-dev \
        default-mysql-client \
        git \
        unzip \
        vim \
    && rm -rf /var/lib/apt/lists/*

# --- PHP extensions ------------------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        mbstring \
        intl \
        opcache \
        exif

# --- Xdebug --------------------------------------------------------------
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# --- Composer ------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Apache --------------------------------------------------------------
RUN a2enmod rewrite headers expires
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# --- PHP config ----------------------------------------------------------
COPY php/php.ini    /usr/local/etc/php/conf.d/zz-app.ini
COPY php/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

# --- Entrypoint ----------------------------------------------------------
COPY php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
