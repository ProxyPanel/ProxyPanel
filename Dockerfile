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
COPY docker/entrypoint.sh /etc/
COPY docker/wait-for-it.sh /etc/
COPY docker/Caddyfile /etc/caddy/

RUN chmod a+x /etc/init.d/* /etc/entrypoint.sh /etc/wait-for-it.sh

COPY --chown=www-data:www-data . /www/wwwroot/proxypanel

WORKDIR /www/wwwroot/proxypanel

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === '756890a4488ce9024fc62c56153228907f1545c228516cbf63f885e036d37e9a59d27d63f46af1d4d07ee0f76181c7d3') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && php composer.phar install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --no-suggest --optimize-autoloader \
    && php artisan vendor:publish -n

CMD ["/etc/entrypoint.sh"]
