FROM composer/composer AS builder

WORKDIR /app

COPY . .

RUN composer install

FROM trafex/php-nginx

USER root

RUN apk add --no-cache php83-sodium

USER nobody

# Set working directory
WORKDIR /var/www/html

# Copy application files from the builder stage
COPY --from=builder /app /var/www/html
