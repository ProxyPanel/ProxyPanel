#!/bin/bash

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
  chown -R www:www ./
  chmod -R 755 ./
  chmod -R 777 storage/
}

git fetch -f
git reset -q --hard origin/master
git pull -q
check_composer
php artisan optimize:clear
composer update
php artisan panel:update
set_permissions
echo -e "\e[32mCheck For newest IP database files | 检测IP数据附件文件最新版本\e[0m"
cd scripts/ && bash download_dbs.sh && cd ../