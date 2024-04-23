FROM composer:latest AS composer

COPY docker/composer.json /var/www/

RUN composer install -d /var/www/

FROM php:apache-bookworm as production

# Mise a jour de la liste de package
RUN apt update && apt upgrade -y && apt autoclean
RUN apt update && apt install -y default-libmysqlclient-dev default-mysql-client python3-yaml libldap2-dev libyaml-dev libzip-dev python3 python3-pip && apt autoclean php8-dom

# Installation des beautifiers pour la standardisation du code soumis
RUN apt install -y black
RUN apt install -y clang-format
RUN pip3 install --break-system-packages beautysh

# beautifier JS et TS
RUN apt install -y npm
RUN npm --global install prettier @prettier/plugin-php standard typescript ts-standard
RUN touch /tmp/tsconfig.eslint.json

# ktlint beautifier kotlin
RUN apt install -y openjdk-17-jre
RUN curl -sSL --output /usr/local/bin/ktlint https://github.com/pinterest/ktlint/releases/download/0.46.1/ktlint && chmod a+x /usr/local/bin/ktlint

# Installation du validateur
RUN pip3 install --break-system-packages progression_qc==3.0.1

# Ajout d'extensions PHP
RUN docker-php-ext-install -j$(nproc) mysqli ldap zip pdo pdo_mysql

COPY docker/docker-php-ext-get /usr/local/bin/

RUN docker-php-source extract &&\
    docker-php-ext-get yaml 2.2.2 &&\
    docker-php-ext-install yaml &&\
    docker-php-source delete

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
	
RUN echo AddDefaultCharset utf-8 >> /etc/apache2/apache2.conf
RUN sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/progression\/app\/html/' /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

RUN echo expose_php = off >> /usr/local/etc/php/conf.d/local.ini
RUN echo ServerTokens Prod >> /etc/apache2/conf-enabled/security.conf
RUN echo ServerSignature Off >> /etc/apache2/conf-enabled/security.conf

# Copie et configuration de l'application
RUN mkdir -p /var/www/progression/

WORKDIR /var/www/

COPY --chown=www-data:www-data progression progression/
COPY --from=composer --chown=www-data:www-data /var/www/vendor/ progression/vendor/

RUN mkdir -p progression/app/storage/framework/sessions
RUN mkdir -p progression/app/storage/framework/views
RUN mkdir -p progression/app/storage/framework/cache
RUN chmod -R 775 progression/app/storage/
RUN chown www-data:www-data progression/ progression/app/storage
RUN chown www-data:www-data -R progression/app/storage

RUN chmod +x progression/app/entrypoint.sh
CMD [ "bash", "progression/app/entrypoint.sh" ]
