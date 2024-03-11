#!/bin/bash

# 引入依赖脚本
source ./scripts/lib.sh

# 信号处理
trap 'rm -f .env; exit' SIGINT SIGTSTP SIGTERM

# 清理不需要的文件
clean_files

# 安装依赖
echo -e "\e[34m========= Checking server environment... | 检查服务器环境... =========\e[0m"
install_dependencies

# 检查环境
echo -e "\e[34m========= Checking the panel environment... | 检查面板运行环境... =========\e[0m"
check_env

# 设置权限
echo -e "\e[34m========= Setting Folder Permissions... | 设置文件夹权限... =========\e[0m"
set_permissions

# 检查Composer
echo -e "\e[34m========= Checking Composer... | 检查Composer... =========\e[0m"
check_composer

# 执行Composer安装
echo -e "\e[34m========= Installing packages via Composer... | 通过Composer安装程序包... =========\e[0m"
composer install --no-interaction --no-dev --optimize-autoloader

# 执行Panel安装
php artisan panel:install

# 设置定时任务
echo -e "\e[34m========= Enabling Panel schedule tasks... | 开启面板定时任务... =========\e[0m"
set_schedule

# 设置Horizon
echo -e "\e[34m========= Setting Horizon daemon... | 设置Horizon守护程序... =========\e[0m"
set_horizon

# 下载IP数据库文件
echo -e "\e[34m========= Downloading IP database files... | 下载IP数据库文件... =========\e[0m"
cd scripts/ && bash download_dbs.sh && cd ../
