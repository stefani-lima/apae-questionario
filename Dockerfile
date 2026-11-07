# Imagem de produção: PHP 8.3 + Apache + extensão do PostgreSQL
FROM php:8.3-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends libpq-dev \
 && docker-php-ext-install pdo_pgsql \
 && apt-get purge -y --auto-remove \
 && rm -rf /var/lib/apt/lists/*

# Somente a pasta public/ fica exposta na web
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
 && a2enmod headers \
 && { echo 'ServerTokens Prod'; echo 'ServerSignature Off'; } >> /etc/apache2/conf-available/security.conf \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && { echo 'expose_php = Off'; echo 'session.cookie_httponly = 1'; } > "$PHP_INI_DIR/conf.d/apae.ini"

WORKDIR /var/www/html
COPY . .
RUN rm -f .env && chown -R www-data:www-data /var/www/html

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
