#!/usr/bin/env bash
git fetch --all
git reset --hard origin/master
git pull
php artisan optimize:clear
composer install --prefer-dist --optimize-autoloader --no-dev
php artisan optimize
chown -R www:www ./