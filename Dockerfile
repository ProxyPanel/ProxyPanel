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

RUN php composer.phar install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --no-suggest --optimize-autoloader \
    && php artisan vendor:publish -n

COPY docker/entrypoint.sh /etc/
COPY docker/wait-for-it.sh /etc/
COPY docker/Caddyfile /etc/caddy/

#Avoid using env_reset in sudoers file
RUN sed -i "s/env_reset/!env_reset/" /etc/sudoers

CMD ["/etc/entrypoint.sh"]
