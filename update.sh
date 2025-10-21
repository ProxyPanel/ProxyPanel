#!/bin/bash
# 设置工作目录为脚本所在的目录
cd "$(dirname "$0")" || exit 1

# 临时下载最新的 lib.sh 到当前目录（覆盖旧版本）
curl -o scripts/lib.sh https://raw.githubusercontent.com/ProxyPanel/ProxyPanel/master/scripts/lib.sh

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
    read -p "Reset demo database? [Y/N] " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        print_message "Resetting demo database..." "重置演示数据库..."
        php artisan migrate:fresh --seed --force
    fi
fi

# 优化缓存
print_message "Optimizing application cache..." "优化应用缓存..."
php artisan optimize

# ===============================================
# 系统依赖安装与检查
# ===============================================

# 确保 Supervisor 存在，供 Horizon/Reverb 使用
print_message "Checking and installing Supervisor..." "检查并安装 Supervisor..."
install_supervisor

# 确保 Node.js/npm 存在，供前端构建使用
print_message "Checking and installing Node.js/npm..." "检查并安装 Node.js/npm..."
install_nodejs_npm

# 检查Composer
print_message "Checking Composer..." "检查Composer..."
check_composer

# 执行Composer更新
print_message "Updating packages via Composer..." "通过Composer更新程序包..."
composer update --no-interaction --no-dev --optimize-autoloader

# ===============================================
# 服务配置和资源构建
# ===============================================

# 设置权限
set_permissions

# 更新旧的队列设置
update_old_queue

print_message "Updating .env configuration..." "更新 .env 配置..."
if [[ -f ".env" ]]; then
    # 将 FORCE_HTTPS 替换为 SESSION_SECURE_COOKIE
    if grep -q "^FORCE_HTTPS=" .env; then
        sed -i 's/^FORCE_HTTPS=/SESSION_SECURE_COOKIE=/' .env
        print_message "Updated FORCE_HTTPS to SESSION_SECURE_COOKIE in .env" ".env 中的 FORCE_HTTPS 已更新为 SESSION_SECURE_COOKIE"
    else
        print_message "No FORCE_HTTPS found in .env" ".env 中未找到 FORCE_HTTPS"
    fi
else
    print_message ".env file not found" "未找到 .env 文件"
fi

# 配置Reverb WebSocket服务
print_message "Configuring Reverb WebSocket service..." "配置Reverb WebSocket服务..."
configure_reverb

# 构建前端资源
print_message "Building frontend assets..." "构建前端资源..."
build_frontend_assets

# 检查最新的IP数据库文件
print_message "Updating IP database files..." "更新本地IP数据库文件..."
cd scripts/ && bash download_dbs.sh

print_message "Panel update completed successfully!" "面板更新完成！"