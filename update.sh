#!/usr/bin/env bash
#检查系统
check_sys(){
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
    chown -R www:www ./
    chmod -R 755 ./
    chmod -R 777 storage/
}

git fetch --all && git reset --hard origin/master && git pull
check_sys
check_composer
php artisan optimize:clear
composer install
php artisan panel:update
set_permissions