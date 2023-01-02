FROM php:8.0

RUN apt-get update && apt-get install -y \
    gosu \
    zip \
    unzip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN useradd -ms /bin/bash --no-user-group pollen

USER pollen

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN composer global require pollen-solutions/installer:dev-main

USER root

RUN ln -s /home/pollen/.composer/vendor/bin/pollen /usr/local/bin/pollen

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
