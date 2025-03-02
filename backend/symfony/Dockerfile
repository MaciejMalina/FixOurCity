FROM php:8.2-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql

COPY . /var/www/html

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]

EXPOSE 8000
