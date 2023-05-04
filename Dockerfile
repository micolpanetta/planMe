FROM php:8.2.5-fpm

RUN apt-get update
RUN apt-get install -y libzip-dev zip
RUN docker-php-ext-install pdo pdo_mysql zip

#symfony-cli
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt install -y symfony-cli

#composer
COPY --from=composer:2.5 /usr/bin/composer /usr/local/bin/composer

EXPOSE 8000