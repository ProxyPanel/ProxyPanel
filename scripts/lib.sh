#!/bin/bash

# 安装依赖
install_dependencies() {
  # 判断系统
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
    echo -e "\e[31m不支持的Linux发行版。\e[0m"
    exit 1
  fi

  if command -v supervisorctl >/dev/null; then
    echo -e "\e[32mSupervisor installed! | Supervisor 已完成!\e[0m"
  else
    echo -e "\e[31mSupervisor did not installed! | Supervisor 未安装!\e[0m"
    # 安装 Supervisor
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

    # 激活
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

# 清理不需要的文件
clean_files() {
  rm -rf .htaccess 404.html index.html
  if [ -f .user.ini ]; then
    chattr -i .user.ini
    rm -f .user.ini
  fi
}

# 检查软件是否安装
check_available() {
  tools=$1
  if command -v "$tools" >/dev/null 2>&1; then
    echo -e "\e[32m$tools Installed! | $tools 已安装!\e[0m"
  else
    echo -e "\e[31m$tools did not installed! | $tools 未安装!\e[0m"
  fi
}

# 检查环境
check_env() {
  check_available php
  check_available php-fpm
  check_available nginx
  check_available mysql
  check_available redis-cli
}

# 检查composer是否安装
check_composer() {
  if [ ! -f "/usr/bin/composer" ]; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
  else
    if [[ $(composer -n --version --no-ansi 2>/dev/null | cut -d" " -f3) < 2.2.0 ]]; then
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

# 设置定时任务
set_schedule() {
  cmd="php $PWD/artisan schedule:run >> /dev/null 2>&1"
  cronjob="* * * * * $cmd"

  if (crontab -u www -l | grep -q -F "$cmd"); then
    echo -e "\e[36m定时任务已存在，无需重复设置。\e[0m"
  else
    (
      crontab -u www -l
      echo "$cronjob"
    ) | crontab -u www -
    echo -e "\e[32m定时任务设置完成!\e[0m"
  fi
}

# 设置Horizon
set_horizon() {
  if [ ! -f /etc/supervisor/conf.d/horizon.conf ]; then
    cat <<EOF | sudo tee -a /etc/supervisor/conf.d/horizon.conf >/dev/null
[program:horizon]
process_name=%(program_name)s
command=php $PWD/artisan horizon
autostart=true
autorestart=true
user=www
redirect_stderr=true
stdout_logfile=$PWD/storage/logs/horizon.log
stopwaitsecs=3600
EOF
    sudo supervisorctl restart horizon
    echo -e "\e[32mHorizon configuration completed! | Horizon 配置完成!\e[0m"
  else
    echo -e "\e[36mHorizon already configured! | Horizon 已配置!\e[0m"
  fi
}

# 更新旧的队列设置
update_old_queue() {
  if crontab -l | grep -q "queue.sh"; then
    crontab_content=$(crontab -l | grep -v "queue.sh")
    echo "$crontab_content" | crontab -
    echo -e "\e[32mOld queue.sh cron job removed! | 旧的 queue.sh 定时任务已移除!\e[0m"
  fi

  set_horizon
}
