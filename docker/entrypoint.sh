#!/usr/bin/env bash

# turn on bash's job control
set -m

# export env from .env
export "$(grep DB_HOST .env)" \
&& export "$(grep DB_PORT .env)" \
&& /etc/wait-for-it.sh $DB_HOST:$DB_PORT -t 45

chmod -R 777 storage
sudo -u "www-data" mkdir -p storage/framework/{cache,sessions,testing,views}

service queue-worker start
service caddy start
service cron start

/usr/local/bin/php artisan --force migrate

php-fpm
