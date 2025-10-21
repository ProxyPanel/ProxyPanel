#!/bin/bash

# ===============================================
# 1. 基础输出函数
# ===============================================

print_logo() {
    cat << "EOF"
    ___                              ___                      _
   / _ \ _ __   ___  __  __ _   _   / _ \  __ _  _ __    ___ | |
  / /_)/| '__| / _ \ \ \/ /| | | | / /_)/ / _` || '_ \  / _ \| |
 / ___/ | |   | (_) | >  < | |_| |/ ___/ | (_| || | | ||  __/| |
 \/     |_|    \___/ /_/\_\ \__, |\/      \__,_||_| |_| \___||_|
                            |___/

EOF
}

print_message() {
    # 格式: 绿色中文 | 英文
    echo -e "\033[32m$2\033[0m | $1"
}

# ===============================================
# 2. 环境检查函数
# ===============================================

# 检查软件是否安装
check_available() {
  tools=$1
  if command -v "$tools" >/dev/null 2>&1; then
    print_message "$tools Installed!" "$tools 已安装!"
  else
    print_message "$tools did not installed!" "$tools 未安装!"
  fi
}

# 检查 Web 环境
check_web_environment() {
  print_message "Checking web environment..." "正在检查 Web 环境..."
  check_available php
  check_available php-fpm
  check_available nginx
  check_available apache2
  check_available mysql
  check_available redis-cli
}

# ===============================================
# 3. 依赖安装函数
# ===============================================

install_composer() {
    # 检查是否安装了 Composer
    if ! command -v composer &>/dev/null; then
        print_message "Composer not found, installing..." "未找到 Composer，正在安装..."
        EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

        if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
            >&2 print_message "ERROR: Invalid installer checksum" "错误: 安装程序校验和无效"
            rm composer-setup.php
            exit 1
        fi

        php composer-setup.php --quiet
        rm composer-setup.php
        mv composer.phar /usr/local/bin/composer
    else
        print_message "Composer is already installed." "Composer 已安装。"
        # 导入 lib_o.sh 的 composer self-update 逻辑
        if [[ $(composer -n --version --no-ansi 2>/dev/null | cut -d" " -f3) < 2.2.0 ]]; then
            print_message "Updating Composer..." "正在更新 Composer..."
            composer self-update
        fi
    fi
}

# 安装 Node.js 和 NPM (新增函数)
install_nodejs_npm() {
    if command -v node &>/dev/null && command -v npm &>/dev/null; then
        print_message "Node.js and npm are already installed." "Node.js 和 npm 已安装。"
        return 0
    fi

    print_message "Node.js or npm not found, attempting installation..." "未找到 Node.js 或 npm，正在尝试安装..."

    # Node.js LTS 版本
    NODE_VERSION=22

    if [[ -f /etc/debian_version ]]; then
        # Debian/Ubuntu (使用 NodeSource 仓库获取 LTS 版本)
        print_message "Using NodeSource repository for Debian/Ubuntu." "正在使用 NodeSource 仓库 (Debian/Ubuntu)。"
        curl -fsSL https://deb.nodesource.com/setup_$NODE_VERSION.x | sudo -E bash -
        sudo apt-get install -y nodejs
    elif [[ -f /etc/redhat-release ]]; then
        # RHEL/CentOS/Fedora
        print_message "Using NodeSource repository for RHEL/CentOS." "正在使用 NodeSource 仓库 (RHEL/CentOS)。"
        curl -fsSL https://rpm.nodesource.com/setup_$NODE_VERSION.x | sudo bash -
        sudo yum install -y nodejs
    elif [[ -f /etc/SuSE-release ]]; then
        # OpenSUSE/SLES
        print_message "Unsupported automatic Node.js installation for SUSE/OpenSUSE." "不支持 SUSE/OpenSUSE 的自动 Node.js 安装。"
        return 1
    elif [[ -f /etc/arch-release ]]; then
        # Arch Linux
        print_message "Installing Node.js via pacman for Arch Linux." "正在通过 pacman 安装 Node.js (Arch Linux)。"
        sudo pacman -S --noconfirm nodejs npm
    elif [[ -f /etc/alpine-release ]]; then
        # Alpine Linux
        print_message "Installing Node.js via apk for Alpine Linux." "正在通过 apk 安装 Node.js (Alpine Linux)。"
        sudo apk add nodejs npm
    else
        print_message "Unsupported Linux distribution. Please install Node.js/npm manually." "不支持的 Linux 发行版。请手动安装 Node.js/npm。"
        return 1
    fi

    if command -v node &>/dev/null && command -v npm &>/dev/null; then
        print_message "Node.js and npm installation completed!" "Node.js 和 npm 安装完成!"
        return 0
    else
        print_message "Node.js and npm installation failed! Please check system logs." "Node.js 和 npm 安装失败! 请检查系统日志。"
        return 1
    fi
}

# 安装 Supervisor
install_supervisor() {
    # 判断系统
    if [[ -f /etc/debian_version ]]; then
        PM=apt-get
    elif [[ -f /etc/redhat-release ]]; then
        PM=yum
    elif [[ -f /etc/SuSE-release ]]; then
        PM=zypper
    elif [[ -f /etc/arch-release ]]; then
        PM=pacman
    elif [[ -f /etc/alpine-release ]]; then
        PM=apk
    else
        print_message "Unsupported Linux distribution. Please install supervisor manually." "不支持的 Linux 发行版。请手动安装 supervisor。"
        return 1
    fi

    if command -v supervisorctl >/dev/null; then
        print_message "Supervisor installed!" "Supervisor 已安装!"
    else
        print_message "Supervisor not found, installing..." "未找到 Supervisor，正在安装..."
        # 安装 Supervisor
        case $PM in
        apt-get)
            sudo apt-get update
            sudo apt-get install -y supervisor
            ;;
        yum)
            sudo yum install -y epel-release
            sudo yum install -y supervisor
            ;;
        zypper)
            sudo zypper install -y supervisor
            ;;
        apk)
            sudo apk add supervisor
            ;;
        pacman)
            sudo pacman -S supervisor
            ;;
        esac

        # 激活
        case $PM in
        yum)
            sudo service supervisord start
            sudo chkconfig supervisord on
            ;;
        *)
            # 适用于大多数使用 systemd 的系统
            sudo systemctl start supervisor.service
            sudo systemctl enable supervisor.service
            ;;
        esac

        if command -v supervisorctl >/dev/null; then
            print_message "Supervisor installation completed!" "Supervisor 安装完成!"
        else
            print_message "Supervisor installation failed! Please check logs." "Supervisor 安装失败! 请检查日志。"
            return 1
        fi
    fi
    return 0
}

check_composer() {
  if [ ! -f "/usr/bin/composer" ]; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
  else
    if [[ $(composer -n --version --no-ansi 2>/dev/null | cut -d" " -f3) < 2.2.0 ]]; then
      composer self-update
    fi
  fi
}

# ===============================================
# 4. 配置和权限函数
# ===============================================

set_permissions() {
    print_message "Setting Laravel directory permissions (www)..." "正在设置 Laravel 目录权限 (www)..."
    if [ ! -d "/home/www" ]; then
        mkdir -p /home/www
        chown www:www /home/www
    fi
    chmod -R 755 storage bootstrap/cache public/assets
    chown -R www:www storage bootstrap/cache public/assets
}

# ===============================================
# 5. 服务配置函数
# ===============================================

set_schedule() {
    SCHEDULE_CMD="php $(pwd)/artisan schedule:run >> /dev/null 2>&1"

    # 尝试使用 www 用户的 crontab
    if command -v crontab >/dev/null && id -u www &>/dev/null; then
        USER_CRON="www"
    else
        # 如果 www 用户不存在，则使用当前用户
        USER_CRON=$(whoami)
    fi

    if crontab -u $USER_CRON -l 2>/dev/null | grep -q "$SCHEDULE_CMD"; then
        print_message "Schedule already exists in crontab for user $USER_CRON." "定时任务已存在于用户 $USER_CRON 的 crontab 中。"
    else
        # 添加定时任务
        (crontab -u $USER_CRON -l 2>/dev/null; echo "* * * * * $SCHEDULE_CMD") | crontab -u $USER_CRON -
        print_message "Schedule task added to crontab for user $USER_CRON." "定时任务已添加到用户 $USER_CRON 的 crontab。"
    fi
}

set_horizon() {
    # 创建 Horizon 配置文件 (用户使用 www 以匹配权限)
    if [ ! -f /etc/supervisor/conf.d/horizon.conf ]; then
        cat > /etc/supervisor/conf.d/horizon.conf <<EOF
[program:horizon]
process_name=%(program_name)s
command=php $(pwd)/artisan horizon
autostart=true
autorestart=true
user=www
redirect_stderr=true
stdout_logfile=$(pwd)/storage/logs/horizon.log
stopwaitsecs=3600
EOF

        # 重新加载 supervisor 配置
        supervisorctl reread
        supervisorctl update
        supervisorctl start horizon

        print_message "Horizon service configured and started." "Horizon 服务已配置并启动。"
    else
        # 优化：如果配置已存在，在更新后强制重启服务
        print_message "Horizon supervisor configuration already exists, attempting to restart service." "Horizon supervisor 配置已存在，尝试重启服务。"
        supervisorctl restart horizon
    fi
}

configure_reverb() {
    # 尝试加载 .env 文件，如果存在
    if [ -f ".env" ]; then
        # 从 .env 文件中获取 APP_URL 的值
        APP_URL=$(grep '^APP_URL=' .env | cut -d '=' -f2)
    fi

    # 检查 .env 文件中是否已存在 Reverb 配置
    if ! grep -q "REVERB_APP_KEY" .env || [ -z "$(grep "REVERB_APP_KEY=" .env | cut -d '=' -f2)" ]; then
        print_message "Adding Reverb configuration to .env file..." "正在向 .env 文件添加 Reverb 配置..."

        REVERB_APP_KEY=$(tr -dc 'a-zA-Z0-9' </dev/urandom | head -c 32)
        REVERB_APP_SECRET=$(tr -dc 'a-zA-Z0-9' </dev/urandom | head -c 32)
        REVERB_APP_ID=$(tr -dc '0-9' </dev/urandom | head -c 6)

        # 替换或添加 Reverb 配置到 .env 文件
        sed -i "/^BROADCAST_DRIVER=/d" .env
        sed -i "/^REVERB_APP_ID=/d" .env
        sed -i "/^REVERB_APP_KEY=/d" .env
        sed -i "/^REVERB_APP_SECRET=/d" .env
        sed -i "/^REVERB_SCHEME=/d" .env
        sed -i "/^REVERB_HOST=/d" .env
        sed -i "/^REVERB_PATH=/d" .env
        sed -i "/^VITE_REVERB_APP_KEY=/d" .env
        sed -i "/^VITE_REVERB_HOST=/d" .env
        sed -i "/^VITE_REVERB_PORT=/d" .env
        sed -i "/^VITE_REVERB_SCHEME=/d" .env
        sed -i "/^VITE_REVERB_PATH=/d" .env
        sed -i "/^REVERB_SERVER_PATH=/d" .env

        echo "BROADCAST_DRIVER=reverb" >> .env
        echo "REVERB_APP_ID=$REVERB_APP_ID" >> .env
        echo "REVERB_APP_KEY=$REVERB_APP_KEY" >> .env
        echo "REVERB_APP_SECRET=$REVERB_APP_SECRET" >> .env
        echo "REVERB_SCHEME=http" >> .env
        echo "REVERB_HOST=" .env
        echo "REVERB_PATH=" .env
        echo "VITE_REVERB_APP_KEY=\"\${REVERB_APP_KEY}\"" >> .env
        echo "VITE_REVERB_HOST=\"\${REVERB_HOST}\"" >> .env
        echo "VITE_REVERB_PORT=\"\${REVERB_PORT}\"" >> .env
        echo "VITE_REVERB_SCHEME=\"\${REVERB_SCHEME}\"" >> .env
        echo "VITE_REVERB_PATH=\"\${REVERB_PATH}\"" >> .env
        echo "REVERB_SERVER_PATH=\"\${REVERB_PATH}\"" >> .env
        print_message "Reverb configuration added to .env file." "Reverb 配置已添加到 .env 文件。"
    else
        print_message "Reverb configuration already exists in .env file." "Reverb 配置已存在于 .env 文件中。"
    fi

    # 创建 Reverb 服务的 supervisor 配置 (用户使用 www 以匹配权限)
    if [ ! -f "/etc/supervisor/conf.d/reverb.conf" ]; then
        cat > /etc/supervisor/conf.d/reverb.conf <<EOF
[program:reverb]
process_name=%(program_name)s
command=php $(pwd)/artisan reverb:start
autostart=true
autorestart=true
user=www
redirect_stderr=true
stdout_logfile=$(pwd)/storage/logs/reverb.log
stopwaitsecs=3600
EOF

        # 重新加载 supervisor 配置
        supervisorctl reread
        supervisorctl update
        supervisorctl start reverb

        print_message "Reverb supervisor configuration created and started." "Reverb supervisor 配置已创建并启动。"
    else
        print_message "Reverb supervisor configuration already exists, attempting to restart service." "Reverb supervisor 配置已存在，尝试重启服务。"
        supervisorctl restart reverb
    fi
}

build_frontend_assets() {
    # 检查 Node.js/npm 是否真的可用，以防安装失败
    if ! command -v node &>/dev/null || ! command -v npm &>/dev/null; then
        print_message "Cannot build frontend assets. Node.js/npm is not available." "无法构建前端资源。Node.js/npm 不可用。"
        return
    fi

    print_message "Installing frontend dependencies..." "安装前端依赖..."
    npm install

    print_message "Building frontend assets..." "构建前端资源..."
    npm run build
}

# ===============================================
# 6. 清理函数
# ===============================================

update_old_queue() {
    # 检查旧的队列设置
    if crontab -l 2>/dev/null | grep -q "artisan queue:work"; then
        print_message "Removing old queue worker from crontab..." "正在从 crontab 中移除旧的队列工作者..."
        crontab -l | grep -v "artisan queue:work" | crontab -
    fi

    # 检查旧的队列设置
    if crontab -l 2>/dev/null | grep -q "queue.sh"; then
        print_message "Removing old queue.sh cron job..." "正在移除旧的 queue.sh 定时任务..."
        crontab -l | grep -v "queue.sh" | crontab -
    fi

    set_horizon
}

clean_files() {
    print_message "Cleaning up unnecessary files..." "正在清理不必要的文件..."

    rm -f .gitignore .styleci.yml

    rm -rf .htaccess 404.html index.html

    if [ -f .user.ini ]; then
        # 尝试移除文件锁
        if command -v chattr >/dev/null; then
            chattr -i .user.ini
        fi
        rm -f .user.ini
        print_message "Cleaned up unnecessary files" "不必要的文件已清理。"
    fi
}