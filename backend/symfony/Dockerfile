FROM php:8.2-cli

# Instalacja zależności systemowych
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    libsodium-dev \
    && docker-php-ext-install pdo pdo_pgsql intl zip sodium

# Instalacja Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Ustawienie uprawnień
RUN chown -R www-data:www-data /var/www

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
