#!/bin/bash
# 设置工作目录为脚本所在的目录
cd "$(dirname "$0")" || exit 1

# 引入依赖脚本
source scripts/lib.sh

# 信号处理
trap 'rm -f .env; exit' SIGINT SIGTSTP SIGTERM

# 清理不需要的文件
clean_files

# 安装依赖
print_message "Checking server environment..." "检查服务器环境..."
install_dependencies

# 检查环境
print_message "Checking the panel environment..." "检查面板运行环境..."
check_env

# 设置权限
print_message "Setting Folder Permissions..." "设置文件夹权限..."
set_permissions

# 检查Composer
print_message "Checking Composer..." "检查Composer..."
check_composer

# 执行Composer安装
print_message "Installing packages via Composer..." "通过Composer安装程序包..."
composer install --no-interaction --no-dev --optimize-autoloader

# 执行Panel安装
php artisan panel:install

# 设置定时任务
print_message "Enabling Panel schedule tasks..." "开启面板定时任务..."
set_schedule

# 设置Horizon
print_message "Setting Horizon daemon..." "设置Horizon守护程序..."
set_horizon

# 下载IP数据库文件
print_message "Downloading IP database files..." "下载IP数据库文件..."
cd scripts/ && bash download_dbs.sh
