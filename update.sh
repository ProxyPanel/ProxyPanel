#!/usr/bin/env bash
git fetch --all
git reset --hard origin/master
git pull
php artisan optimize:clear
composer install --prefer-dist
php artisan optimize
chown -R www:www ./