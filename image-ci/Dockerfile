FROM php:8

# Install git, the php image doesn't have installed
RUN apt update
RUN DEBIAN_FRONTEND=noninteractive TZ=America/Toronto apt install tzdata git emacs elpa-htmlize unzip python3 python3-pip libyaml-dev libzip-dev default-libmysqlclient-dev default-mysql-client npm -yqq  && apt autoclean
RUN pip3 install napkin progression_qc

#Ajout d'extensions PHP
RUN docker-php-ext-install -j$(nproc) mysqli zip

COPY docker-php-ext-get /usr/local/bin/

RUN docker-php-source extract &&\
    docker-php-ext-get yaml 2.2.2 &&\
    docker-php-ext-install yaml &&\
    docker-php-source delete

COPY init.el /root/.emacs.el
COPY install.el /tmp/
RUN emacs --no-init-file --script /tmp/install.el

# Install composer
RUN curl -sS https://getcomposer.org/installer | php

# Install prettier
RUN npm install --global prettier @prettier/plugin-php

RUN apt update && apt install -y locales
RUN echo fr_CA.UTF-8 UTF-8 >> /etc/locale.gen
RUN locale-gen

ENV LANG fr_CA.UTF-8
ENV LANGUAGE fr_CA:en
ENV LC_ALL fr_CA.UTF-8
