#!/usr/bin/env bash

# turn on bash's job control
set -m

/usr/local/bin/php artisan optimize:clear

/etc/wait-for-it.sh $DB_HOST:$DB_PORT -t 45

chmod -R 777 storage
sudo -u "www-data" mkdir -p storage/framework/{cache,sessions,testing,views}

service queue-worker start
service caddy start
service cron start

/usr/local/bin/php artisan --force migrate
/usr/local/bin/php artisan optimize

php-fpm
