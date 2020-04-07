FROM ubuntu:18.04
MAINTAINER redgoose <scripter@me.com>

WORKDIR /goose-api

RUN apt-get -qq update
RUN apt-get -y -qq install curl git zip software-properties-common

# ser timezone
ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# install php
RUN add-apt-repository ppa:ondrej/php -y
RUN apt-get -y -qq install php7.4-cli php7.4-fpm php7.4-curl php7.4-mysql php7.4-mbstring
RUN php --version

## install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# copy project files
COPY ./ .

# setting in project
RUN composer install
RUN ./cmd.sh ready

# play command
CMD service php7.4-fpm start && php -S 0.0.0.0:8000 server.php

# expose port
EXPOSE 8000
