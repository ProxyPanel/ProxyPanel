#!/usr/bin/env bash
git fetch --all
git reset --hard origin/master
git pull
php composer.phar install
php artisan key:generate
php artisan cache:clear
php artisan view:clear