#!/usr/bin/env bash
git fetch --all
git reset --hard origin/master
git pull
php composer.phar update
php composer.phar dumpautoload
php artisan key:generate
php artisan view:clear
php artisan cache:clear
chown -R www:www ./