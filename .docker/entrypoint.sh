#!/bin/bash
set -e; # Exit immediately if a command exits with a non-zero status.

if [ "$APP_ENV" = "production" ]; then
    /usr/local/bin/php /var/www/html/artisan migrate --force;
else
    /usr/local/bin/php /var/www/html/artisan migrate;
fi

/usr/local/bin/php /var/www/html/artisan optimize;

exec apache2-foreground;
