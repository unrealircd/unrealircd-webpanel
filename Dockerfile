FROM composer/composer AS builder

WORKDIR /app

COPY . .

# --no-dev: the test tools (PHPUnit) do not belong in the image
RUN composer install --no-dev

FROM trafex/php-nginx

USER root

RUN apk add --no-cache php83-sodium

# nginx ignores the .htaccess files of the panel: block the same things here
# (secrets in config/ and data/, backup copies, and .git), see doc/webserver-security.md
COPY doc/docker-nginx-panel.conf /etc/nginx/conf.d/default.conf

USER nobody

# Set working directory
WORKDIR /var/www/html

# Copy application files from the builder stage
COPY --chown=nobody --from=builder /app /var/www/html

# All settings and panel accounts live here. Keep them on volumes: without them,
# a rebuilt container has no configuration and the installer is open again
# for whoever reaches the panel first. Example:
#   docker run -v panel-config:/var/www/html/config -v panel-data:/var/www/html/data ...
VOLUME ["/var/www/html/config", "/var/www/html/data"]
