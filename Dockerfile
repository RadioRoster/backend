# Stage 1: Build
FROM composer:lts@sha256:0e7405d851433b5bb61c1783833b65e6b71fdab006c1029f42f2da1d670d53a5 as build

WORKDIR /app

COPY . /app/

# Install dependencies
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction --no-progress

# Stage 2: Production
FROM php:8.3-apache@sha256:c55d99c94f804ee54177ba00961d2441333b277a67e6a6901341cb251b47f638 as production

ENV APP_ENV=production
ENV APP_DEBUG=false

# Install Postgres driver
RUN apt-get update && \
    apt-get install -y libpq-dev

# Install PHP extensions
RUN docker-php-ext-configure opcache --enable-opcache && \
    docker-php-ext-install pdo pdo_mysql pdo_pgsql

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy opcache config
COPY .docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy application
COPY --from=build /app /var/www/html

# Copy apache config
COPY .docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Copy entrypoint
COPY .docker/entrypoint.sh /entrypoint.sh

# Copy configure script
RUN chown -R www-data:www-data /var/www/ && \
    a2enmod rewrite

EXPOSE 80

# Run Entrypoint
ENTRYPOINT [ "/entrypoint.sh" ]
