FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    libldap2-dev \
    libonig-dev \
    libicu-dev \
    unzip \
    git \
    cron \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu \
    && docker-php-ext-install -j$(nproc) \
        gd \
        ldap \
        mysqli \
        pdo_mysql \
        zip \
        xml \
        mbstring \
        intl \
        soap \
        bcmath \
        curl \
        opcache

# Enable Apache modules
RUN a2enmod rewrite headers expires

# PHP configuration
RUN { \
    echo 'memory_limit = 256M'; \
    echo 'upload_max_filesize = 60M'; \
    echo 'post_max_size = 60M'; \
    echo 'max_execution_time = 300'; \
    echo 'max_input_time = 300'; \
    echo 'date.timezone = UTC'; \
} > /usr/local/etc/php/conf.d/suitecrm.ini

# Apache virtualhost
RUN { \
    echo '<VirtualHost *:80>'; \
    echo '    DocumentRoot /var/www/html'; \
    echo '    <Directory /var/www/html>'; \
    echo '        Options Indexes FollowSymLinks'; \
    echo '        AllowOverride All'; \
    echo '        Require all granted'; \
    echo '    </Directory>'; \
    echo '    ErrorLog ${APACHE_LOG_DIR}/error.log'; \
    echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined'; \
    echo '</VirtualHost>'; \
} > /etc/apache2/sites-available/000-default.conf

# Copy SuiteCRM files
COPY SuiteCRM/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Add cron job for SuiteCRM scheduler
RUN echo "* * * * * www-data php -f /var/www/html/cron.php > /dev/null 2>&1" >> /etc/cron.d/suitecrm \
    && chmod 0644 /etc/cron.d/suitecrm \
    && crontab /etc/cron.d/suitecrm

EXPOSE 80
