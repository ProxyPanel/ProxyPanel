#!/bin/bash
#VNET 一键部署脚本
function check_system(){
    if [[ -f /etc/redhat-release ]]; then
		release="centos"
	elif cat /etc/issue | grep -q -E -i "debian"; then
		release="debian"
	elif cat /etc/issue | grep -q -E -i "ubuntu"; then
		release="ubuntu"
	elif cat /etc/issue | grep -q -E -i "centos|red hat|redhat"; then
		release="centos"
	elif cat /proc/version | grep -q -E -i "debian"; then
		release="debian"
	elif cat /proc/version | grep -q -E -i "ubuntu"; then
		release="ubuntu"
	elif cat /proc/version | grep -q -E -i "centos|red hat|redhat"; then
		release="centos"
    fi
	bit=`uname -m`
	if [[ ${release} == "centos" ]] && [[ ${bit} == "x86_64" ]]; then
	echo -e "当前系统为[${release} ${bit}],\033[32m  可以搭建\033[0m"
	else 
	echo -e "当前系统为[${release} ${bit}],\033[31m 不可以搭建 \033[0m"
	echo -e "\033[31m 脚本停止运行(●°u°●)​ 」，请更换centos7.x 64位系统运行此脚本 \033[0m"
	exit 0;
	fi
}

function install_vnet(){
    # 检测依赖
    if ! [ -x "$(command -v wget)" ]; then
    echo "缺少wget,自动安装"
    yum install wget -y
    fi

    echo '设置每天几点几分重启节点'
    read -p " 按下回车默认0时， 小时(0-23): " -r -e -i 7 hour
    read -p " 按下回车默认30分，分钟(0-59): " -r -e -i 30 minute
    read -p " 面板地址: " -r -e -i https://example.com  api_host
    read -p " 面板通讯密钥: " -r -e -i xxxxx api_key
    read -p " 节点id: " -r -e -i 1 node_id

    cd /root/
    #清理上次下载
    rm -rf vnet_latest.tar.gz vnet

    #下载vnet最新版本压缩包
    wget https://kitami-hk.oss-cn-hongkong.aliyuncs.com/vnet_v4.tar.gz -O vnet_latest.tar.gz
    mkdir -p /root/vnet
    tar -xzvf vnet_latest.tar.gz -C vnet

    cd /root/vnet
    chmod +x vnet

    # 生成配置文件
    cat > config.json << EOF
{
    "node_id":$node_id,
    "key": "$api_key",
    "api_host": "$api_host"
}
EOF
    echo "配置已生成"

    # 服务安装
    ln -P vnet.service /etc/systemd/system/
    systemctl daemon-reload
    systemctl enable vnet
    systemctl start vnet
    echo "服务已安装"

    # 关闭防火墙
    systemctl stop firewalld
	systemctl disable firewalld.service
    echo "防火墙已关闭"

	echo "$minute $hour * * * root /sbin/reboot" >> /etc/crontab
    echo "已设置自动重启"
}

check_system
install_vnet