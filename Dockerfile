FROM php:8.2-apache

# Install PHP extensions needed by the app
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli gd zip

# Allow .htaccess overrides
RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

COPY . /var/www/html/

# Create upload dirs in case they weren't committed
RUN mkdir -p /var/www/html/ar \
             /var/www/html/profile_images \
             /var/www/html/studentworks \
             /var/www/html/pdfparser

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/ar \
                    /var/www/html/profile_images \
                    /var/www/html/studentworks \
                    /var/www/html/pdfparser

EXPOSE 80
