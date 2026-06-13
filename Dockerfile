FROM php:8.2-apache

# Install PHP extensions needed by the app
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli gd zip

# Remove ALL mpm symlinks then add only prefork
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load \
          /etc/apache2/mods-enabled/mpm_*.conf \
    && ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf \
    && a2enmod rewrite \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

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

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]
