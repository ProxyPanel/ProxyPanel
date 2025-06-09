#!/bin/bash
# 设置工作目录为脚本所在的目录
cd "$(dirname "$0")" || exit 1

# 引入依赖脚本
source scripts/lib.sh

# 更新代码
print_message "Checking server environment..." "检查服务器环境..."
git fetch -f && git reset -q --hard origin/master && git pull

# 检查Composer
print_message "Checking Composer..." "检查Composer..."
check_composer

# 清理优化缓存
print_message "Cleaning panel cache..." "清理面板缓存..."
php artisan optimize:clear

# 执行Composer更新
print_message "Updating packages via Composer..." "通过Composer更新程序包..."
composer update --no-interaction --no-dev --optimize-autoloader

# 执行Panel更新
php artisan panel:update

# 设置权限
set_permissions

# 更新旧的队列设置
update_old_queue

# 检查最新的IP数据库文件
print_message "Updating IP database files..." "更新本地IP数据库文件..."
cd scripts/ && bash download_dbs.sh