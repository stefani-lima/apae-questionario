#!/bin/sh
set -e

# Provedores como o Render informam a porta pela variável PORT.
if [ -n "$PORT" ] && [ "$PORT" != "80" ]; then
  sed -i "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
  sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

# Cria/atualiza as tabelas e o administrador inicial antes de subir o servidor.
php /var/www/html/scripts/migrate.php

exec "$@"
