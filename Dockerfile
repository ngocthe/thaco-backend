FROM php:7.4-fpm

RUN apt-get update -y \
    && apt-get install -y nginx

# PHP_CPPFLAGS are used by the docker-php-ext-* scripts
ENV PHP_CPPFLAGS="$PHP_CPPFLAGS -std=c++11"

RUN apt-get install -y \
        libonig-dev \
    && docker-php-ext-install iconv mbstring

RUN apt-get install -y \
        libzip-dev \
        zlib1g-dev \
    && docker-php-ext-install zip

RUN apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN apt-get install -y jq

RUN docker-php-ext-install pdo_mysql \
    && docker-php-ext-install opcache \
    && apt-get install -y \
                     libicu-dev \
                     libpq-dev \
                     zlib1g-dev \
                     libzip-dev \
                     libmcrypt-dev \
                     libxml2-dev \
                     libonig-dev \
                     curl \
                     git \
                     zip \
                     unzip \
                     vim \
                     supervisor \
    && rm -r /var/lib/apt/lists/* \
    && docker-php-ext-install \
          intl \
          mbstring \
          xml \
          pgsql \
          zip \
          opcache \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && apt-get remove libicu-dev icu-devtools -y

RUN { \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/php-opocache-cfg.ini

COPY .docker/nginx/default.conf /etc/nginx/sites-enabled/default

COPY .docker/php/php.ini /usr/local/etc/php/conf.d/uploads.ini

COPY .docker/supervisor/* /etc/supervisor/

COPY .docker/entrypoint.sh /etc/entrypoint.sh

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html
# Install and run composer commands
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
COPY composer.json /var/www/html
COPY composer.lock /var/www/html
RUN composer install --no-scripts --no-autoloader
RUN ls
RUN ls vendor
EXPOSE 80 443

RUN ["chmod", "+x", "/etc/entrypoint.sh"]

RUN chown -R www-data:www-data vendor/
COPY --chown=www-data:www-data . /var/www/html

RUN chmod +x artisan
RUN composer dump-autoload --optimize

ENTRYPOINT ["/etc/entrypoint.sh"]
