#!/usr/bin/env bash
# Deploy vnstat-1.17
# Author:SSRPanel

yum install -y gcc gcc+
cd /root/
wget -c http://humdi.net/vnstat/vnstat-1.17.tar.gz && tar -zxvf vnstat-1.17.tar.gz && cd vnstat-1.17
echo '开始编译并安装vnstat'
./configure
make && make install
echo '开始配置vnstat'
mkdir /var/lib/vnstat
cp vnstat.conf.new /etc/vnstat.conf
vnstat --create -i eth0
vnstat -u -i eth0
echo '正在将vnstat加入自启动'
cp vnstatd /usr/sbin/vnstatd
cp vnstatd /usr/bin/vnstatd
cp examples/init.d/centos/vnstat /etc/init.d/
chmod +x /etc/init.d/vnstat
chkconfig --add vnstat
chkconfig vnstat on
service vnstat start
echo '更新一次网卡数据'
vnstat -u -i eth0
echo '安装完成'
