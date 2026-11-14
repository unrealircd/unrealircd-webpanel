FROM php:8.3-apache
RUN a2enmod rewrite headers \
 && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
 && cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
