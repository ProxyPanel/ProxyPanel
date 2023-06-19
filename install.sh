#!/usr/bin/env bash
#清理不需要的文件
clean_files() {
  if [ -f .user.ini ]; then
    chattr -i .user.ini
  fi
  rm -rf .htaccess 404.html index.html .user.ini
}

#检查系统
check_sys() {
  # shellcheck disable=SC2002
  if [[ -f /etc/redhat-release ]]; then
    release="centos"
  elif cat /etc/issue | grep -q -E -i "debian"; then
    release="debian"
  elif cat /etc/issue | grep -q -E -i "ubuntu"; then
    release="ubuntu"
  elif cat /etc/issue | grep -q -E -i "centos|red hat|redhat"; then
    release="centos"
  elif cat /proc/version | grep -q -E -i "debian"; then
    release="debian"
  elif cat /proc/version | grep -q -E -i "ubuntu"; then
    release="ubuntu"
  elif cat /proc/version | grep -q -E -i "centos|red hat|redhat"; then
    release="centos"
  fi

  echo "========= Checking for Software dependency | 检查依赖软件是否安装/运行 ========="
  if which redis-cli >/dev/null; then
    echo -e "\e[37;42m Redis Installed! | Redis 已安装!\e[0m"
    redis-cli ping
  else
    echo -e "\e[37;1;41m Redis did not installed! | redis 未安装!\e[0m"
  fi

  if which php >/dev/null; then
    echo -e "\e[37;42m PHP Installed! | PHP 已安装!\e[0m"
    php -v
  else
    echo -e "\e[37;1;41m PHP did not installed! | PHP 未安装!\e[0m"
  fi

  if which nginx >/dev/null || which httpd >/dev/null; then
    echo -e "\e[37;42m Nginx/Apache Installed! | Nginx 或 Apache 已安装!\e[0m"
    if which nginx >/dev/null; then
      nginx -v
    else
      httpd -v
    fi
  else
    echo -e "\e[37;1;41m Nginx/Apache did not installed! | Nginx 或 Apache 未安装!\e[0m"
  fi
}
#检查composer是否安装
check_composer() {
  if [ ! -f "/usr/bin/composer" ]; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
  else
    if [[ $(composer -n --version --no-ansi | cut -d" " -f3) < 2.2.0 ]]; then
      composer self-update
    fi
  fi
}

# 设置权限
set_permissions() {
  if [ ! -d "/home/www" ]; then
    mkdir -p /home/www
    chown www:www /home/www
  fi
  chown -R www:www ./
  chmod -R 755 ./
  chmod -R 777 storage/
}

set_schedule() {
  cmd="php $PWD/artisan schedule:run >> /dev/null 2>&1"
  cronjob="* * * * * $cmd"
  (
    crontab -u www -l | grep -v -F "$cmd"
    echo "$cronjob"
  ) | crontab -u www -
}

set_horizon() {
  if ! command -v supervisorctl &>/dev/null; then
    if [[ "${release}" == "centos" ]]; then
      yum install -y supervisor
    else
      apt-get install -y supervisor
    fi
  fi

  if [ ! -f /etc/supervisor/conf.d/horizon.conf ]; then
    echo "
      [program:horizon]
      process_name=%(program_name)s
      command=php $PWD/artisan horizon
      autostart=true
      autorestart=true
      user=www
      redirect_stderr=true
      stdout_logfile=$PWD/storage/logs/horizon.log
      stopwaitsecs=3600" >>/etc/supervisor/conf.d/horizon.conf

    supervisorctl reread
    supervisorctl update
    supervisorctl start horizon
  fi
}

clean_files
check_sys
check_composer
composer install
php artisan panel:install
set_permissions
set_schedule
set_horizon
