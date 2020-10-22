FROM v2cc/proxypanel-base

# Give execution rights on the cron job
RUN mkdir /etc/app_crontab \
# Copy or create your cron file named crontab into the root directory crontab
 && chown -R root /etc/app_crontab && chmod -R 0644 /etc/app_crontab
COPY docker/crontab /etc/app_crontab/crontab
# Apply cron job
RUN crontab /etc/app_crontab/crontab

# System V Init scripts
COPY docker/init.d/caddy /etc/init.d/
COPY docker/init.d/queue-worker /etc/init.d/
RUN chmod a+x /etc/init.d/*

COPY --chown=www-data:www-data . /www/wwwroot/proxypanel

WORKDIR /www/wwwroot/proxypanel

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === 'c31c1e292ad7be5f49291169c0ac8f683499edddcfd4e42232982d0fd193004208a58ff6f353fde0012d35fdd72bc394') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && php composer.phar install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --no-suggest --optimize-autoloader \
    && php artisan vendor:publish -n

COPY docker/entrypoint.sh /etc/
COPY docker/wait-for-it.sh /etc/
COPY docker/Caddyfile /etc/caddy/

#Avoid using env_reset in sudoers file
RUN sed -i "s/env_reset/!env_reset/" /etc/sudoers

CMD ["/etc/entrypoint.sh"]
