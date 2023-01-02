FROM composer:latest

WORKDIR /app

COPY ./bin ./bin
COPY ./src ./src
COPY ./composer.json ./composer.json

RUN composer install
