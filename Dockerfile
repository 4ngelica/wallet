FROM php:8.1.0-apache

RUN apt-get update && apt-get upgrade -y && \
   apt-get -y update --fix-missing && \
   apt-get install -y \
   cron \
   supervisor && \
   docker-php-ext-install pdo pdo_mysql

RUN pecl install redis && docker-php-ext-enable redis

RUN a2enmod headers && sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Origin "*"\n\1\2/g' /etc/apache2/sites-available/*.conf
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --version=2.6.6 --filename=composer

COPY . /var/www/html

COPY ./php.ini /usr/local/etc/php/php.ini
COPY ./default.conf /etc/apache2/sites-enabled/000-default.conf

RUN a2enmod rewrite headers ssl
RUN service apache2 restart

WORKDIR /var/www/html

EXPOSE 80
EXPOSE 443

COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY crontab /etc/cron.d/app-cron

RUN chmod 0644 /etc/cron.d/app-cron

RUN crontab -u root /etc/cron.d/app-cron