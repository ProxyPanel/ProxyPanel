#!/usr/bin/env bash
git fetch --all
git reset --hard origin/master
git pull
php composer.phar install
php artisan key:generate
php artisan view:clear
php artisan route:cache
php artisan config:cache
chown -R www:www ./