FROM php:7.2-apache

# Update distrib
RUN apt update
RUN apt full-upgrade -y

# Required for java
RUN mkdir -p /usr/share/man/man1


# Install dependencies
RUN apt install -y git zip unzip libpng-dev nano wkhtmltopdf openjdk-11-jre  zlib1g-dev libicu-dev g++


# Update php.ini
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN echo "date.timezone = Europe/Paris" >> "$PHP_INI_DIR/php.ini"
RUN sed -ri -e "s!'memory_limit'!'; memory_limit'!g" $PHP_INI_DIR/php.ini
RUN sed -ri -e "s!'memory_limit'!'; memory_limit'!g" $PHP_INI_DIR/php.ini
RUN echo "memory_limit = 2048M" >> "$PHP_INI_DIR/php.ini"

# Install dependencies mods
RUN docker-php-ext-install gd
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install intl
RUN pecl install apcu
RUN pecl install apcu_bc
RUN echo "extension=apcu.so" >> "$PHP_INI_DIR/php.ini"
RUN echo "extension=apc.so" >> "$PHP_INI_DIR/php.ini"

# Configure apache2
ENV APACHE_DOCUMENT_ROOT /fairpay/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN rm composer-setup.php
RUN mv composer.phar /usr/bin/composer

# Add sources
COPY . /fairpay
WORKDIR /fairpay

# Install sources
RUN composer install

RUN app/console assets:install --env=prod

RUN mkdir web/bootstrap
RUN mkdir web/bootstrap/js
RUN cp vendor/twbs/bootstrap/dist/js/bootstrap.js web/bootstrap/js
RUN cp vendor/jquery/jquery/jquery-2.1.1.min.js web/bootstrap/js/jquery.js


RUN rm app/cache -rf

EXPOSE 80

CMD ["./init.sh"]