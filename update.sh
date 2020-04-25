#!/usr/bin/env bash
git fetch --all
git reset --hard origin/master
git pull
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php composer.phar install
php artisan key:generate
php artisan route:cache
php artisan config:cache
chown -R www:www ./