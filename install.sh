#!/bin/bash

#安装依赖
install_dependencies() {
  #判断系统
  if [[ -f /etc/debian_version ]]; then
    PM=apt-get
  elif [[ -f /etc/redhat-release ]]; then
    PM=yum
  elif [[ -f /etc/SuSE-release ]]; then
    PM=zypper
  elif [[ -f /etc/arch-release ]]; then
    PM=pacman
  elif [[ -f /etc/alpine-release ]]; then
    PM=apk
  else
    echo "不支持的Linux发行版。"
    exit 1
  fi

  if which supervisorctl >/dev/null; then
    echo -e "\e[32mSupervisor installed! | Supervisor 已完成!\e[0m"
  else
    echo -e "\e[31mSupervisor did not installed! | Supervisor 未安装!\e[0m"
    # Install Supervisor
    case $PM in
    apt-get)
      sudo apt-get update
      sudo apt-get install -y supervisor
      ;;
    yum)
      sudo yum install -y epel-release
      sudo yum install -y supervisor
      ;;
    zypper)
      sudo zypper install -y supervisor
      ;;
    apk)
      sudo apk add supervisor
      ;;
    pacman)
      sudo pacman -S supervisor
      ;;
    esac

    #激活
    case $PM in
    yum)
      sudo service supervisord start
      sudo chkconfig supervisord on
      ;;
    *)
      sudo systemctl start supervisor.service
      sudo systemctl enable supervisor.service
      ;;
    esac
    echo -e "\e[32mSupervisor installation completed! | Supervisor 安装完成!\e[0m"
  fi
}

#清理不需要的文件
clean_files() {
  if [ -f .user.ini ]; then
    chattr -i .user.ini
  fi
  rm -rf .htaccess 404.html index.html .user.ini
}

check_available() {
  tools=$1
  available=$(command -v $tools >/dev/null 2>&1)

  if $available; then
    echo -e "\e[32m$tools Installed! | $tools 已安装!\e[0m"

    case $tools in
    redis-cli)
      redis-cli ping
      ;;
    php)
      php -v
      ;;
    esac
  else
    echo -e "\e[31m$tools did not installed! | $tools 未安装!\e[0m"
  fi
}

#检查环境
check_env() {
  echo "========= Checking for Software dependency | 检查依赖软件是否安装/运行 ========="
  # 需要检查的软件数组
  check_list=(redis-cli php)

  for item in "${check_list[@]}"; do
    check_available "$item"
  done

  if which nginx >/dev/null || which httpd >/dev/null; then
    echo -e "\e[32mNginx/Apache Installed! | Nginx 或 Apache 已安装!\e[0m"
    if which nginx >/dev/null; then
      nginx -v
    else
      httpd -v
    fi
  else
    echo -e "\e[31mNginx/Apache did not installed! | Nginx 或 Apache 未安装!\e[0m"
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

    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl start horizon
  fi
}

clean_files
install_dependencies
check_env
check_composer
composer install
php artisan panel:install
set_permissions
set_schedule
set_horizon
echo -e "\e[32mGoing to Download some IP database files... | 将下载一些IP数据附件文件...\e[0m"
cd scripts/ && bash download_dbs.sh && cd ../
