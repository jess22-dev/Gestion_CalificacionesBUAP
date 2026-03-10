FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpq-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

WORKDIR /var/www/html

COPY . .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 775 storage bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

RUN a2enmod rewrite

COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]

COPY apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80