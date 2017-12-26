## 演示站
````
http://www.ssrpanel.com
用户名：admin
密码：123456
````

## 安装步骤
#### 环境要求
````
PHP 7.1 （必须）
MYSQL 5.5 （推荐5.6+）
内存 1G+ 
磁盘空间 10G+
KVM

PHP必须开启curl、gd、fileinfo、openssl组件

小白建议使用LNMP傻瓜安装出php7.1 + mysql(5.5以上)
手动编译请看WIKI [编译安装PHP7.1.7环境（CentOS）]
使用LNMP部署时请到/usr/local/php/etc/php.ini下搜索disable_functions，把proc_开头的函数都删掉

telegram频道：https://t.me/ssrpanel
telegram群组：https://t.me/chatssrpanel
本人未实名微信小号：dxstx77 （请勿任何转账、红包行为）
严禁在TG群里喧哗、谈论政治、发色情信息，只聊技术不扯淡，更别刷屏惹众怒，否则踢3天，两次机会，第三次被踢你永远进不来，我都记得谁谁谁
````

[VPS推荐&购买经验](https://github.com/ssrpanel/SSRPanel/wiki/VPS%E6%8E%A8%E8%8D%90&%E8%B4%AD%E4%B9%B0%E7%BB%8F%E9%AA%8C)
````
部署面板必须得用到VPS
强烈推荐使用1G以上内存的KVM架构的VPS
做节点则只需要512M+内存的KVM即可，但是还是推荐使用1G+内存的KVM
强烈不建议使用OVZ（OpenVZ），一无法加速二容易崩溃，512M以下内存的容易经常性宕机（低内存KVM也会宕机）
````

![支持作者](https://github.com/ssrpanel/ssrpanel/blob/master/public/assets/images/donate.jpeg?raw=true)

#### 拉取代码
````
cd /home/wwwroot/
git clone https://github.com/ssrpanel/ssrpanel.git
````

#### 先配置数据库
````
mysql 创建一个数据库，然后自行导入sql\db.sql
config\database.php 中的mysql选项自行配置数据库
````

#### 其次配置一下运行环境
````
cd ssrpanel/
php composer.phar install
php artisan key:generate
chown -R www:www storage/
chmod -R 777 storage/
````

#### 然后NGINX配置文件加入
````
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
````

#### 编辑php.ini
````
找到php.ini
vim /usr/local/php/etc/php.ini

搜索disable_function
删除proc_开头的所有函数
````

#### 出现500错误
````
理论上操作到上面那些步骤完了应该是可以正常访问网站了，如果网站出现500错误，请看WIKI，很有可能是fastcgi的错误
请看WIKI：https://github.com/ssrpanel/ssrpanel/wiki/%E5%87%BA%E7%8E%B0-open_basedir%E9%94%99%E8%AF%AF
修改完记得重启NGINX和PHP-FPM
````

#### 重启NGINX和PHP-FPM
````
service nginx restart
service php-fpm restart
````

## 定时任务（发邮件、流量统计、自动任务全部需要用到）
````
编辑crontab
crontab -e

然后加入如下（请自行修改ssrpanel路径）：
（表示每分钟都执行定时任务，具体什么任务什么时候执行程序里已经定义了，请不要乱改，否则流量统计数据可能出错）
* * * * * php /home/wwwroot/ssrpanel/artisan schedule:run >> /dev/null 2>&1
````

## 邮件配置
###### SMTP
````
编辑 config\mail.php

请自行配置如下内容
'driver' => 'smtp',
'host' => 'smtp.exmail.qq.com',
'port' => 465,
'from' => [
    'address' => 'xxx@qq.com',
    'name' => 'SSRPanel',
],
'encryption' => 'ssl',
'username' => 'xxx@qq.com',
'password' => 'xxxxxx',
````

###### Mailgun
````
编辑 config\mail.php
将 driver 值改为 mailgun

编辑 config/services.php

请自行配置如下内容
'mailgun' => [
    'domain' => 'mailgun发件域名',
    'secret' => 'mailgun上申请到的secret',
],
````

###### 发件失败处理
````
如果使用了逗比的ban_iptables.sh来防止用户发垃圾邮件
可能会导致出现 Connection could not be established with host smtp.exmail.qq.com [Connection timed out #110] 这样的错误
因为smtp发邮件必须用到25,26,465,587这四个端口，逗比的一键脚本会将这些端口一并封禁
可以编辑iptables，注释掉以下这段（前面加个#号就可以），然后保存并重启iptables
#-A OUTPUT -p tcp -m multiport --dports 25,26,465,587 -m state --state NEW,ESTABLISHED -j REJECT --reject-with icmp-port-unreachable
````

## 日志分析（仅支持单机单节点）
````
找到SSR服务端所在的ssserver.log文件
进入ssrpanel所在目录，建立一个软连接，并授权
cd /home/wwwroot/ssrpanel/storage/app
ln -S ssserver.log /root/shadowsocksr/ssserver.log
chown www:www ssserver.log
````

## SSR部署
###### 手动部署
````
cp server/ssr-3.4.0.zip /root/
cd /root
unzip ssr-3.4.0.zip
cd shadowsocksr
sh initcfg.sh
把 userapiconfig.py 里的 API_INTERFACE 设置为 glzjinmod
把 user-config.json 里的 connect_verbose_info 设置为 1
配置 usermysql.json 里的数据库链接，NODE_ID就是节点ID，对应面板后台里添加的节点的自增ID，所以请先把面板搭好，搭好后进后台添加节点
````

###### 一键自动部署
````
wget -N --no-check-certificate https://raw.githubusercontent.com/ssrpanel/ssrpanel/master/server/deploy_ssr.sh;chmod +x deploy_ssr.sh;./deploy_ssr.sh
````

## 更新代码
````
进到ssrpanel目录下执行：
git pull

如果每次更新都会出现数据库文件被覆盖，请先执行一次：
chmod a+x fix_git.sh && sh fix_git.sh

如果本地自行改了文件，想用回原版代码，请先备份好 config/database.php，然后执行以下命令：
chmod a+x update.sh && sh update.sh
````

## 网卡流量监控一键脚本（Vnstat）
````
wget -N --no-check-certificate https://raw.githubusercontent.com/ssrpanel/ssrpanel/master/server/deploy_vnstat.sh;chmod +x deploy_vnstat.sh;./deploy_vnstat.sh
````

## 单端口多用户（推荐）
````
编辑节点的 user-config.json 文件：
vim user-config.json

将 "additional_ports" : {}, 改为以下内容：
"additional_ports" : {
    "80": {
        "passwd": "统一认证密码", // 例如 SSRP4ne1，推荐不要出现除大小写字母数字以外的任何字符
        "method": "统一认证加密方式", // 例如 aes-128-ctr
        "protocol": "统一认证协议", // 例如 auth_aes128_md5 或者 auth_aes128_sha1 或 auth_chain_a，其他没试过，请自行测试
        "protocol_param": "#",
        "obfs": "tls1.2_ticket_auth_compatible",
        "obfs_param": ""
    },
    "443": {
        "passwd": "统一认证密码",
        "method": "统一认证加密方式",
        "protocol": "统一认证协议",
        "protocol_param": "#",
        "obfs": "tls1.2_ticket_auth_compatible",
        "obfs_param": ""
    }
},

保存，然后重启SSR服务。
客户端设置：

远程端口：80
密码：password
加密方式：aes-128-ctr
协议：auth_aes128_md5
混淆插件：tls1.2_ticket_auth
协议参数：1026:@123 (SSR端口:SSR密码)

或

远程端口：443
密码：password
加密方式：aes-128-ctr
协议：auth_aes128_sha1
混淆插件：tls1.2_ticket_auth
协议参数：1026:SSRP4ne1 (SSR端口:SSR密码)

经实测账号的协议可以是：auth_chain_a
建议节点后端使用auth_sha1_v4_compatible，方便兼容

注意：如果想强制所有账号都走80、443这样自定义的端口的话，记得把 user-config.json 中的 additional_ports_only 设置为 true
警告：经实测单端口下如果用锐速没有效果，很可能是VPS供应商限制了这两个端口
提示：配置单端口最好先看下这个WIKI，防止才踩坑：https://github.com/ssrpanel/ssrpanel/wiki/%E5%8D%95%E7%AB%AF%E5%8F%A3%E5%A4%9A%E7%94%A8%E6%88%B7%E7%9A%84%E5%9D%91

````

## 架构
````
1.单面板机-单节点 - 一打全死
2.单面板机-多节点-数据库不分离 - 一打全死
3.单面板机-多节点-数据库分离 - 打哪个死哪个，数据尚在
4.多面板机-多节点-数据库分离 - 打不死，隐秘性强，数据通用
5.多棉板机-多节点-数据库主从 - 打不死，隐秘性强，数据通用，灾备

具体联系我运维，收费咨询，小白勿扰
````

## 校时
````
如果架构是“一面板机-一数据库机-多节点机”，请务必保持各个服务器之间的时间一致，否则会影响节点在线数的准确性和单端口多用户功能的正常使用。
推荐统一使用CST时间并安装校时服务：
vim /etc/sysconfig/clock 把值改为 Asia/Shanghai
cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

重启一下服务器，然后：
yum install ntp
ntpdate cn.pool.ntp.org
````

## 致敬
- [@breakwa11](https://github.com/breakwa11)
- [@glzjin](https://github.com/esdeathlove)
- [@orvice](https://github.com/orvice)
- [@ToyoDAdoubi](https://github.com/ToyoDAdoubi)
- [@91yun](https://github.com/91yun)
- [@Akkariiin](https://github.com/shadowsocksrr)

## 说明
````
1.多节点账号管理面板
2.需配合SSR 3.4 Python版后端使用
3.强大的管理后台、美观的界面、简单易用的开关、支持移动端自适应
4.内含简单的购物、卡券、邀请码、推广返利&提现、文章管理、工单等模块
5.节点支持分组，不同级别的用户可以看到不同级别分组的节点
6.SS配置转SSR配置，轻松一键导入SS账号
7.流量日志、单机单节点日志分析功能
8.强大的定时任务
9.所有邮件投递都有记录
10.账号临近到期、流量不够都会自动发邮件提醒，自动禁用到期、流量异常的账号，自动清除日志
11.后台一键添加加密方式、混淆、协议、等级
12.强大的后台一键配置功能
13.屏蔽常见爬虫
14.支持单端口多用户
15.账号、节点24小时和近30天内的流量监控
16.支持节点订阅功能，可一键封禁账号订阅地址
17.节点宕机提醒（邮件+ServerChan微信提醒）
18.Paypal在线支付接口
19.兼容SS、SSRR
````

## 预览
![Wpy0e.png](https://s1.ax1x.com/2017/11/24/Wpy0e.png)
![WpUYR.png](https://s1.ax1x.com/2017/11/24/WpUYR.png)
![WpNk9.png](https://s1.ax1x.com/2017/11/24/WpNk9.png)
![WpGmF.png](https://s1.ax1x.com/2017/11/24/WpGmF.png)
![WpJw4.png](https://s1.ax1x.com/2017/11/24/WpJw4.png)
![WpYTJ.png](https://s1.ax1x.com/2017/11/24/WpYTJ.png)
![Wpaf1.png](https://s1.ax1x.com/2017/11/24/Wpaf1.png)
![WpwSx.png](https://s1.ax1x.com/2017/11/24/WpwSx.png)
![Wp0l6.png](https://s1.ax1x.com/2017/11/24/Wp0l6.png)
![WpB6K.png](https://s1.ax1x.com/2017/11/24/WpB6K.png)
![WpDOO.png](https://s1.ax1x.com/2017/11/24/WpDOO.png)
![WpsmD.png](https://s1.ax1x.com/2017/11/24/WpsmD.png)