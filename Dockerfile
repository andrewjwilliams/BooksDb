# Stage 1: Build frontend assets
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm install
COPY resources/ resources/
COPY vite.config.js ./
COPY public/ public/
RUN npm run build

# Stage 2: Install PHP dependencies
FROM composer:2 AS composer-builder
WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist
COPY . .
RUN mkdir -p bootstrap/cache && composer dump-autoload --optimize --no-dev

# Stage 3: PHP-FPM runtime
FROM php:8.4-fpm-alpine AS app
RUN apk add --no-cache \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        opcache
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini
WORKDIR /var/www/html
COPY --from=composer-builder /app .
COPY --from=node-builder /app/public/build public/build
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache
USER www-data
EXPOSE 9000
CMD ["php-fpm"]

# Stage 4: nginx serving static assets
FROM nginx:alpine AS nginx
COPY --from=app /var/www/html/public /var/www/html/public
COPY nginx/default.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
