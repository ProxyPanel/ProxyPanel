## 项目描述
````
1.多节点账号管理面板，兼容SS、SSRR
2.需配合SSR或SSRR版后端使用
3.强大的管理后台、美观的界面、简单易用的开关、支持移动端自适应
4.内含简单的购物、卡券、邀请码、推广返利&提现、文章管理、工单等模块
5.用户、节点标签化，不同用户可见不同节点
6.SS配置转SSR配置，轻松一键导入SS账号
7.单机单节点日志分析功能
8.账号、节点24小时和近30天内的流量监控
9.所有邮件投递都有记录
10.账号临近到期、流量不够会自动发邮件提醒，自动禁用到期、流量异常的账号，自动清除日志等各种强大的定时任务
11.后台一键添加加密方式、混淆、协议、等级
12.强大的后台一键配置功能
13.屏蔽常见爬虫、屏蔽机器人
14.支持单端口多用户
15.支持节点订阅功能，可一键封禁账号订阅地址
16.节点宕机提醒（邮件、ServerChan微信提醒）
17.支持多国语言，自带英文语言包
18.订阅防投毒机制
19.自动释放端口机制，防止端口被大量长期占用
20.封IP段
````

## 演示&交流
````
官方站：http://www.ssrpanel.com
演示站：http://demo.ssrpanel.com （用户名：admin 密码：123456，请勿修改密码）
telegram订阅频道：https://t.me/ssrpanel
telegram千人讨论群已解散，不用加了，有问题提issues
````

## 捐赠
![支持作者](https://github.com/ssrpanel/ssrpanel/blob/master/public/assets/images/donate.jpeg?raw=true)

[VPS推荐&购买经验](https://github.com/ssrpanel/SSRPanel/wiki/VPS%E6%8E%A8%E8%8D%90&%E8%B4%AD%E4%B9%B0%E7%BB%8F%E9%AA%8C)
````
部署面板必须得用到VPS
强烈推荐使用1G以上内存的KVM架构的VPS
做节点则只需要512M+内存的KVM即可，但是还是推荐使用1G+内存的KVM
强烈不建议使用OVZ（OpenVZ），一无法加速二容易崩溃，512M以下内存的容易经常性宕机（低内存KVM也会宕机）
````

## 安装
#### 环境要求
````
PHP 7.1 （必须）
MYSQL 5.5 （推荐5.6+）
内存 1G+ 
磁盘空间 10G+
PHP必须开启curl、gd、fileinfo、openssl、mbstring组件
安装完成后记得编辑config/app.php中 'debug' => true, 改为 false
````

#### 拉取代码
````
cd /home/wwwroot/
git clone https://github.com/ssrpanel/ssrpanel.git
````

#### 安装面板
````
cd ssrpanel/
php composer.phar install
php artisan key:generate
chown -R www:www storage/
chmod -R 777 storage/
````

#### 配置数据库

##### 连接数据库
````
1.创建一个utf8mb4的数据库
2.编辑config/database.php，编辑mysql选项中如下配置值：host、port、database、username、password
````

##### 自动部署

###### 迁移(创建表结构)
````
php artisan migrate
````

###### 播种(填充数据)
````
php artisan db:seed --class=ConfigTableSeeder
php artisan db:seed --class=CountryTableSeeder
php artisan db:seed --class=LevelTableSeeder
php artisan db:seed --class=SsConfigTableSeeder
php artisan db:seed --class=UserTableSeeder
````

##### 手工部署
````
迁移未经完整测试，可能存在BUG，可以手动将sql/db.sql导入到创建好的数据库
````

#### 加入NGINX的URL重写规则
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

## 定时任务
````
crontab加入如下命令（请自行修改php、ssrpanel路径）：
* * * * * php /home/wwwroot/ssrpanel/artisan schedule:run >> /dev/null 2>&1

注意运行权限，必须跟ssrpanel项目权限一致：
例如用lnmp的话默认权限用户组是 www:www，则添加定时任务是这样的：
crontab -e -u www

如果用crontab -e 默认是root权限，则整个ssrpanel项目也要用root:root用户组
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

###### 发邮件失败处理
````
如果使用了逗比的ban_iptables.sh来防止用户发垃圾邮件
可能会导致出现 Connection could not be established with host smtp.exmail.qq.com [Connection timed out #110] 这样的错误
因为smtp发邮件必须用到25,26,465,587这四个端口，逗比的一键脚本会将这些端口一并封禁
可以编辑iptables，注释掉以下这段（前面加个#号就可以），然后保存并重启iptables
#-A OUTPUT -p tcp -m multiport --dports 25,26,465,587 -m state --state NEW,ESTABLISHED -j REJECT --reject-with icmp-port-unreachable
````

## 英文版
````
修改 config/app.php 下的 locale 值为 en
````

## 日志分析（仅支持单机单节点）
````
找到SSR服务端所在的ssserver.log文件
进入ssrpanel所在目录，建立一个软连接，并授权
cd /home/wwwroot/ssrpanel/storage/app
ln -S ssserver.log /root/shadowsocksr/ssserver.log
chown www:www ssserver.log
````

## SSR(R)部署
###### 手动部署(基于SSRR)
````
git clone https://github.com/ssrpanel/shadowsocksr.git
cd shadowsocksr
sh initcfg.sh
配置 usermysql.json 里的数据库链接，NODE_ID就是节点ID，对应面板后台里添加的节点的自增ID，所以请先把面板搭好，搭好后进后台添加节点
````

###### 一键自动部署(基于SSR3.4)
````
wget -N --no-check-certificate https://raw.githubusercontent.com/ssrpanel/ssrpanel/master/server/deploy_ssr.sh;chmod +x deploy_ssr.sh;./deploy_ssr.sh

或者使用另一个脚本

wget -N --no-check-certificate https://raw.githubusercontent.com/maxzh0916/Shadowsowcks1Click/master/Shadowsowcks1Click.sh;chmod +x Shadowsowcks1Click.sh;./Shadowsowcks1Click.sh
````

## 更新代码
````
进到ssrpanel目录下执行：
git pull

如果每次更新都会出现数据库文件被覆盖，请先执行一次：
chmod a+x fix_git.sh && sh fix_git.sh

如果本地自行改了文件，想用回原版代码，请先备份好 config/database.php，然后执行以下命令：
chmod a+x update.sh && sh update.sh

如果更新完代码各种错误，请先执行一遍 php composer.phar install
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
        "protocol": "统一认证协议", // 可选值：orgin、verify_deflate、auth_sha1_v4、auth_aes128_md5（推荐）、auth_aes128_sha1（推荐）、auth_chain_a（强烈推荐）
        "protocol_param": "#", // #号前面带上数字，则可以限制在线每个用户的最多在线设备数，仅限 auth_chain_a 协议下有效，例如： 3# 表示限制最多3个设备在线
        "obfs": "tls1.2_ticket_auth", // 可选值：plain、http_simple（该值下客户端可用http_post）、random_head、tls1.2_ticket_auth（强烈推荐）
        "obfs_param": ""
    },
    "443": {
        "passwd": "统一认证密码",
        "method": "统一认证加密方式",
        "protocol": "统一认证协议",
        "protocol_param": "#",
        "obfs": "tls1.2_ticket_auth",
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

经实测，节点后端使用auth_sha1_v4_compatible，可以兼容auth_chain_a
注意：如果想强制所有账号都走80、443这样自定义的端口的话，记得把 user-config.json 中的 additional_ports_only 设置为 true
警告：经实测单端口下如果用锐速没有效果，很可能是VPS供应商限制了这两个端口
提示：配置单端口最好先看下这个WIKI，防止才踩坑：https://github.com/ssrpanel/ssrpanel/wiki/%E5%8D%95%E7%AB%AF%E5%8F%A3%E5%A4%9A%E7%94%A8%E6%88%B7%E7%9A%84%E5%9D%91

````

## 校时
````
如果架构是“面板机-数据库机-多节点机”，请务必保持各个服务器之间的时间一致，否则会产生：节点的在线数不准确、产生最后使用时间异常、单端口多用户功能失效等。
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

