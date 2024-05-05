#!/bin/bash

# 引入依赖脚本
source ./scripts/lib.sh

# 更新代码
echo -e "\e[34m========= Checking server environment... | 检查服务器环境... =========\e[0m"
git fetch -f && git reset -q --hard origin/master && git pull

# 检查Composer
echo -e "\e[34m========= Checking Composer... | 检查Composer... =========\e[0m"
check_composer

# 清理优化缓存
echo -e "\e[34m========= Cleaning panel cache... | 清理面板缓存... =========\e[0m"
php artisan optimize:clear

# 执行Composer更新
echo -e "\e[34m========= Updating packages via Composer... | 通过Composer更新程序包... =========\e[0m"
composer update --no-interaction --no-dev --optimize-autoloader

# 执行Panel更新
php artisan panel:update

# 设置权限
set_permissions

# 更新旧的队列设置
update_old_queue

# 检查最新的IP数据库文件
echo -e "\e[34m========= Updating IP database files... | 更新本地IP数据库文件... =========\e[0m"
cd scripts/ && bash download_dbs.sh && cd ../