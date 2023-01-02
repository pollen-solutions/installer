FROM php:8.0

WORKDIR /app

RUN apt-get update && apt-get install -y \
    zip \
    unzip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN composer global require pollen-solutions/installer:dev-master
