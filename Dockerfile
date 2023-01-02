FROM php:8.0

WORKDIR /app

RUN apt-get update && apt-get install -y \
    zip \
    unzip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

COPY ./bin ./bin
COPY ./src ./src
COPY ./composer.json ./composer.json

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN composer install
