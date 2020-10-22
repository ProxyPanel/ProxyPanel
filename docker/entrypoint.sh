#!/usr/bin/env bash

# turn on bash's job control
set -m

# extract environment variables for cron
printenv | grep -v " " | sed 's/^\(.*\)$/export \1/g' > /root/container_env.sh
printenv | grep -v " " > /root/env.txt

bash /etc/wait-for-it.sh $DB_HOST:$DB_PORT -t 45

sudo -u "www-data" mkdir -p storage/framework/{cache,sessions,testing,views}
chmod -R 777 storage

service queue-worker start
service caddy start
service cron start

/usr/local/bin/php artisan --force migrate

php-fpm
