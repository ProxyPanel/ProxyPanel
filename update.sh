#!/bin/bash
# 设置工作目录为脚本所在的目录
cd "$(dirname "$0")" || exit 1

# 引入依赖脚本
source scripts/lib.sh

print_message "Starting panel update..." "开始面板更新..."

print_logo

# 更新代码
print_message "updating Code..." "更新代码..."
git fetch -f && git reset -q --hard origin/master && git pull

# 更新数据库
print_message "Updating database..." "更新数据库..."
php artisan migrate --force

# 如果是演示环境，询问是否重置数据库
if [[ $(grep -E '^APP_ENV=demo' .env) ]]; then
    read -p "Reset demo database? [y/N] " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        print_message "Resetting demo database..." "重置演示数据库..."
        php artisan migrate:fresh --seed --force
    fi
fi

# 优化缓存
print_message "Optimizing application cache..." "优化应用缓存..."
php artisan optimize

# 检查Composer
print_message "Checking Composer..." "检查Composer..."
check_composer

# 执行Composer更新
print_message "Updating packages via Composer..." "通过Composer更新程序包..."
composer update --no-interaction --no-dev --optimize-autoloader

# 设置权限
set_permissions

# 更新旧的队列设置
update_old_queue

# 检查最新的IP数据库文件
print_message "Updating IP database files..." "更新本地IP数据库文件..."
(cd scripts/ && bash download_dbs.sh)

print_message "Panel update completed successfully!" "面板更新完成！"