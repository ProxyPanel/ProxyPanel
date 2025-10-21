#!/bin/bash
# 设置工作目录为脚本所在的目录
cd "$(dirname "$0")" || exit 1

# 引入依赖脚本
source scripts/lib.sh

# 信号处理
trap 'rm -f .env; exit' SIGINT SIGTSTP SIGTERM

# ===============================================
# 1. 初始化和环境检查
# ===============================================
# 清理不需要的文件
clean_files

# 检查 Web 环境 (检查 PHP/Nginx/MySQL 等工具)
print_message "Checking the web environment..." "检查 Web 运行环境..."
check_web_environment

# ===============================================
# 2. 核心依赖安装 (Composer, Supervisor, Node.js/npm)
# ===============================================
print_message "Checking/Installing core system dependencies..." " 检查/安装核心系统依赖项..."
# 安装 Composer
install_composer
# 安装 Supervisor (Horizon/Reverb 所需)
install_supervisor
# 安装 Node.js/npm (前端构建所需)
install_nodejs_npm

# ===============================================
# 3. 应用程序安装与配置
# ===============================================
# 执行 Composer 安装
print_message "Installing packages via Composer..." "通过 Composer 安装程序包..."
composer install --no-interaction --no-dev --optimize-autoloader

# 执行 Panel 安装 (Handles .env, DB, key:generate, storage:link)
php artisan panel:install

# 设置权限 (在 storage:link 完成后设置权限)
print_message "Setting Folder Permissions..." "设置文件夹权限..."
set_permissions

# ===============================================
# 4. 服务和资源构建
# ===============================================
# 设置定时任务
print_message "Enabling Panel schedule tasks..." "开启面板定时任务..."
set_schedule

# 设置 Horizon
print_message "Setting Horizon daemon..." "设置 Horizon 守护程序..."
set_horizon

# 配置 Reverb WebSocket 服务
print_message "Configuring Reverb WebSocket service..." "配置 Reverb WebSocket 服务..."
configure_reverb

# 构建前端资源
print_message "Building frontend assets..." "构建前端资源..."
build_frontend_assets

# ===============================================
# 5. 最终步骤
# ===============================================
# 下载IP数据库文件
print_message "Downloading IP database files..." "下载 IP 数据库文件..."
cd scripts/ && bash download_dbs.sh
