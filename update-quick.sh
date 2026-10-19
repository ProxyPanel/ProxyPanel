#!/bin/bash
# 生产环境的日常快速更新：与 update.sh 同一条链，但不跑一次性的系统依赖检查/安装，也不下载 IP 库与客户端安装包。
# 步骤顺序上的差别：依赖与前端构建排在 migrate 之前，因为 artisan 跑的是 vendor 里的代码，而服务重启排在最后。

# 设置工作目录为脚本所在的目录
cd "$(dirname "$0")" || exit 1

# 引入依赖脚本（只用检出的 scripts/lib.sh，与本仓应用代码同版本）
source scripts/lib.sh

print_message "Quick update..." "开始快速更新..."

print_logo

# 拉取前的提交号，用来判断依赖清单有没有变动
before=$(git rev-parse HEAD)

# 更新代码
print_message "updating Code..." "更新代码..."
git fetch -f && git reset -q --hard origin/master && git pull
after=$(git rev-parse HEAD)

if [ "$before" = "$after" ]; then
    print_message "No new commits." "没有新提交，已结束。"
    exit 0
fi

# 后面的库函数改用本轮拉下来的版本，与刚更新的代码对齐
source scripts/lib.sh

# 本次变动的文件清单，决定依赖与前端要不要重建
changed=$(git diff --name-only "$before" "$after")

# ===============================================
# 系统依赖安装与检查
# ===============================================

# composer.lock 不入库，只有 composer.json 变动才重装依赖
if printf '%s\n' "$changed" | grep -qx 'composer.json'; then
    print_message "Updating packages via Composer..." "通过Composer更新程序包..."
    composer update --no-interaction --no-dev --optimize-autoloader
fi

# package-lock.json 同样不入库，前端只在 package.json 变动时重建
if printf '%s\n' "$changed" | grep -qx 'package.json'; then
    print_message "Building frontend assets..." "构建前端资源..."
    build_frontend_assets
fi

# ===============================================
# 数据库与应用缓存
# ===============================================

# 更新数据库
print_message "Updating database..." "更新数据库..."
php artisan migrate --force

# 优化缓存
print_message "Optimizing application cache..." "优化应用缓存..."
php artisan optimize

# ===============================================
# 权限与服务配置
# ===============================================

# 设置权限
set_permissions

# Horizon worker 常驻在内存里，拉到新代码后要重启才会跑新版本；没在跑时该命令安静返回
print_message "Restarting Horizon workers..." "重启 Horizon worker..."
php artisan horizon:terminate

# 配置Reverb WebSocket服务
print_message "Configuring Reverb WebSocket service..." "配置Reverb WebSocket服务..."
configure_reverb

# 资源下载不在本脚本里：IP 库与客户端安装包体积大且很少变
print_message "Panel quick update completed successfully!" "面板快速更新完成！"
