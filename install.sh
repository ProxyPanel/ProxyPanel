#!/usr/bin/env bash
#检查系统
check_sys(){
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
}
#检查composer是否安装
check_composer(){
  if [ ! -f "/usr/bin/composer" ]; then
      if [[ "${release}" == "centos" ]]; then
      		yum install -y composer
      	else
      		apt-get install -y composer
      fi
  fi
}

# 设置权限
set_permissions(){
  if [ ! -d "/home/www" ]; then
    mkdir -p /home/www
    chown www:www /home/www
  fi
    chown -R www:www ./
    chmod -R 755 ./
    chmod -R 777 storage/
}

set_crontab(){
  cmd="php $(dirname "$path")/artisan schedule:run >> /dev/null 2>&1"
  cronjob="* * * * * $cmd"
  ( crontab -u www -l | grep -v -F "$cmd" ; echo "$cronjob" ) | crontab -u www -


  cmd="bash $(dirname "$path")/queue.sh"
  cronjob="*/10 * * * * $cmd"
  ( crontab -l | grep -v -F "$cmd" ; echo "$cronjob" ) | crontab -
}

check_sys
check_composer
composer install
php artisan panel:install
set_permissions
set_crontab