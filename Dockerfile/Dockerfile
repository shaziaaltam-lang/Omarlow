FROM php:8.3-fpm AS build

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    nodejs \
    npm

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm install && npm run build

FROM php:8.3-fpm-alpine

WORKDIR /var/www

RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libzip-dev

RUN docker-php-ext-install pdo_mysql zip

COPY --from=build /var/www /var/www

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80
CMD ["php-fpm"]