#!/usr/bin/env bash
# Require Root Permission
# SSR node deploy script
# Author: Hao-Luo (https://github.com/Hao-Luo/Others/tree/master/apps/ssr)
# Editor: SSRPanel

[ $(id -u) != "0" ] && { echo "Error: You must be root to run this script"; exit 1; }

install_ssr(){
	cd /root/
	echo 'SSR下载中...'
	wget -c https://github.com/ssrpanel/ssrpanel/raw/master/server/ssr-3.4.0.tar.gz && tar -zxvf ssr-3.4.0.tar.gz && cd shadowsocksr &&  ./setup_cymysql.sh
	echo '开始配置节点连接信息...'
	stty erase '^H' && read -p "数据库服务器地址:" mysqlserver
	stty erase '^H' && read -p "数据库服务器端口:" port
	stty erase '^H' && read -p "数据库名称:" database
	stty erase '^H' && read -p "数据库用户名:" username
	stty erase '^H' && read -p "数据库密码:" pwd
	stty erase '^H' && read -p "本节点ID:" nodeid
	stty erase '^H' && read -p "本节点流量计算比例:" ratio
	sed -i -e "s/server_host/$mysqlserver/g" usermysql.json
	sed -i -e "s/server_port/$port/g" usermysql.json
	sed -i -e "s/server_db/$database/g" usermysql.json
	sed -i -e "s/server_user/$username/g" usermysql.json
	sed -i -e "s/server_password/$pwd/g" usermysql.json
	sed -i -e "s/nodeid/$nodeid/g" usermysql.json
	sed -i -e "s/noderatio/$ratio/g" usermysql.json
	echo -e "配置完成!\n如果无法连上数据库，请检查本机防火墙或者数据库防火墙!\n请自行编辑user-config.json，配置节点加密方式、混淆、协议等"
}

open_bbr(){
	cd
	wget --no-check-certificate https://github.com/teddysun/across/raw/master/bbr.sh
	chmod +x bbr.sh
	./bbr.sh
}

echo -e "1.Install SSR\n2.Open BBR"
stty erase '^H' && read -p "请输入数字进行安装[1-2]:" num
case "$num" in
	1)
	install_ssr
	;;
	2)
	open_bbr
	;;
	*)
	echo "请输入正确数字[1-2]:"
	;;
esac