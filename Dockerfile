ARG ALPINE_VERSION=3.16.2
FROM alpine:${ALPINE_VERSION}
MAINTAINER redgoose <scripter@me.com>, original by <https://github.com/TrafeX/docker-php-nginx>

WORKDIR /app

# Install packages and remove default server definition
RUN apk add --no-cache \
  curl nginx supervisor \
  php81 php81-ctype php81-curl php81-dom php81-fpm \
  php81-pdo php81-pdo_mysql php81-gd php81-intl php81-mbstring \
  php81-opcache php81-openssl php81-phar php81-session php81-xml php81-xmlreader

# Create symlink so programs depending on `php` still function
RUN ln -s /usr/bin/php81 /usr/bin/php

# Configure nginx
COPY resource/docker/nginx.conf /etc/nginx/nginx.conf

# Configure PHP-FPM
COPY resource/docker/fpm-pool.conf /etc/php81/php-fpm.d/www.conf
COPY resource/docker/php.ini /etc/php81/conf.d/custom.ini

# Configure supervisord
COPY resource/docker/supervisord.conf /etc/supervisord.conf

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R nobody.nobody /app /run /var/lib/nginx /var/log/nginx

# Switch to use a non-root user from here on
USER nobody

# Add application
COPY --chown=nobody ./ /app

## composer install
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer install --optimize-autoloader --no-interaction --no-progress --ignore-platform-reqs

RUN ./cmd.sh ready

# Expose the port nginx is reachable on
EXPOSE 4040

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:4040/fpm-ping
