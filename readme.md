## 项目描述
````
1.SSR多节点账号管理面板，兼容SS、SSRR，需配合SSR或SSRR版后端使用
2.支持v2ray（开发中）
3.开放API，方便自行定制改造客户端
4.内含简单的购物、卡券、邀请码、推广返利&提现、文章管理、工单（回复带邮件提醒）等模块
5.用户、节点标签化，不同用户可见不同节点
6.SS配置转SSR(R)配置，轻松一键导入导出SS账号
7.单机单节点日志分析功能
8.账号、节点24小时和本月的流量监控
9.流量异常、节点宕机邮件或ServerChan及时通知
10.账号临近到期、流量不够会自动发邮件提醒，自动禁用到期、流量异常的账号，自动清除日志等各种强大的定时任务
11.后台一键添加加密方式、混淆、协议、等级
12.屏蔽常见爬虫、屏蔽机器人
14.支持单端口多用户
15.支持节点订阅功能，可自由更换订阅地址、封禁账号订阅地址、禁止特定型号设备订阅
17.支持多国语言，自带英日韩繁语言包
18.订阅防投毒机制
19.自动释放端口机制，防止端口被大量长期占用
20.有赞云支付
21.封特定国家、地区、封IP段（开发中）
22.中转节点（开发中）
23.强大的营销管理：PushBear群发消息
24.telegram机器人（开发中）
25.防墙监测，节点被墙自动提醒（TCP阻断）
````

## 演示&交流
````
官方站：http://www.ssrpanel.com
演示站：http://demo.ssrpanel.com
telegram订阅频道：https://t.me/ssrpanel
````

## 捐赠
**以太坊钱包** : 0x968f797f194fcec05ea571723199748b58de38ba

![支持作者](https://github.com/ssrpanel/ssrpanel/blob/master/public/assets/images/donate.jpg?raw=true)

[VPS推荐&购买经验](https://github.com/ssrpanel/SSRPanel/wiki/VPS%E6%8E%A8%E8%8D%90&%E8%B4%AD%E4%B9%B0%E7%BB%8F%E9%AA%8C)

## 安装
#### 环境要求
````
PHP 7.1 （必须）
MYSQL 5.5 （推荐5.6+）
内存 1G+ 
磁盘空间 10G+
PHP必须开启zip、xml、curl、gd、gd2、fileinfo、openssl、mbstring组件
安装完成后记得编辑.env中 APP_DEBUG 改为 false
````

#### 拉取代码
````
cd /home/wwwroot/
git clone https://github.com/ssrpanel/SSRPanel.git
````

#### 配置数据库
````
1.创建一个utf8mb4的数据库
2.编辑 .env 文件，修改 DB_ 开头的值
3.导入 sql/db.sql 到数据库
````

#### 安装面板
````
cd SSRPanel/
cp .env.example .env
（然后 vi .env 修改数据库的连接信息）
php composer.phar install
php artisan key:generate
chown -R www:www storage/
chmod -R 777 storage/
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
* * * * * php /home/wwwroot/SSRPanel/artisan schedule:run >> /dev/null 2>&1

注意运行权限，必须跟ssrpanel项目权限一致，否则出现各种莫名其妙的错误
例如用lnmp的话默认权限用户组是 www:www，则添加定时任务是这样的：
crontab -e -u www
````

## 邮件配置
###### SMTP
````
编辑 .env 文件，修改 MAIL_ 开头的配置
````

###### 使用Mailgun发邮件
````
编辑 .env 文件
将 MAIL_DRIVER 值改为 mailgun

然后编辑 config/services.php

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
修改 .env 的 APP_LOCALE 值为 en
语言包位于 resources/lang 下，可自行更改
````

## 日志分析（仅支持单机单节点）
````
找到SSR服务端所在的ssserver.log文件
进入ssrpanel所在目录，建立一个软连接，并授权
cd /home/wwwroot/SSRPanel/storage/app
ln -S ssserver.log /root/shadowsocksr/ssserver.log
chown www:www ssserver.log
````

## SSR(R)部署
###### 手动部署(基于SSRR 3.2.2，推荐)
````
git clone https://github.com/ssrpanel/shadowsocksr.git
cd shadowsocksr
sh initcfg.sh
配置 usermysql.json 里的数据库链接，NODE_ID就是节点ID，对应面板后台里添加的节点的自增ID，所以请先把面板搭好，搭好后进后台添加节点
````

###### 一键自动部署(基于SSR3.4)(不推荐，该版本有内存溢出BUG)
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

如果本地自行改了文件，想用回原版代码，请直接执行以下命令：
chmod a+x update.sh && sh update.sh

如果更新完代码各种错误，请先执行一遍 php composer.phar install
````

## 网卡流量监控一键脚本（Vnstat）
````
wget -N --no-check-certificate https://raw.githubusercontent.com/ssrpanel/ssrpanel/master/server/deploy_vnstat.sh;chmod +x deploy_vnstat.sh;./deploy_vnstat.sh
````

## 单端口多用户
````
编辑节点的 user-config.json 文件：
vim user-config.json

将 "additional_ports" : {}, 改为以下内容：
"additional_ports" : {
    "80": {
        "passwd": "统一认证密码", // 例如 SSRP4ne1，推荐不要出现除大小写字母数字以外的任何字符
        "method": "统一认证加密方式", // 例如 aes-128-ctr
        "protocol": "统一认证协议", // 可选值：orgin、verify_deflate、auth_sha1_v4、auth_aes128_md5、auth_aes128_sha1、auth_chain_a
        "protocol_param": "#", // #号前面带上数字，则可以限制在线每个用户的最多在线设备数，仅限 auth_chain_a 协议下有效，例如： 3# 表示限制最多3个设备在线
        "obfs": "tls1.2_ticket_auth", // 可选值：plain、http_simple、http_post、random_head、tls1.2_ticket_auth
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

保存，然后重启SSR(R)服务。
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
提示：配置单端口最好先看下这个WIKI，防止踩坑：https://github.com/ssrpanel/ssrpanel/wiki/%E5%8D%95%E7%AB%AF%E5%8F%A3%E5%A4%9A%E7%94%A8%E6%88%B7%E7%9A%84%E5%9D%91

````
## 代码更新
````
进入到SSRPanel目录下
1.手动更新： git pull
2.强制更新： sh ./update.sh 

如果你更改了本地文件，手动更新会提示错误需要合并代码（自己搞定），强制更新会直接覆盖你本地所有更改过的文件
````

## 校时
````
如果架构是“面板机-数据库机-多节点机”，请务必保持各个服务器之间的时间一致，否则会产生：节点的在线数不准确、最后使用时间异常、单端口多用户功能失效等。
推荐统一使用CST时间并安装校时服务：
vim /etc/sysconfig/clock 把值改为 Asia/Shanghai
cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

重启一下服务器，然后：
yum install ntp
ntpdate cn.pool.ntp.org
````

## 二开规范
````
如果有小伙伴要基于本程序进行二次开发，自行定制，请谨记一下规则（如果愿意提PR我也很欢迎）
1.数据库表字段请务必使用蟒蛇法，严禁使用驼峰法
2.写完代码最好格式化，该空格一定要空格，该注释一定要注释，便于他人阅读代码
3.本项目中ajax返回格式都是 {"status":"fail 或者 success", "data":[数据], "message":"文本消息提示语"}
````

## 收费版
````
收费版代码混淆，不开源，具体请知识星球上私信我
````

## 致敬
- [@shadowsocks](https://github.com/shadowsocks)
- [@breakwa11](https://github.com/breakwa11)
- [@glzjin](https://github.com/esdeathlove)
- [@orvice](https://github.com/orvice)
- [@ToyoDAdoubi](https://github.com/ToyoDAdoubi)
- [@91yun](https://github.com/91yun)
- [@Akkariiin](https://github.com/shadowsocksrr)
- [@tonychanczm](https://github.com/tonychanczm)
- [ipcheck](https://ipcheck.need.sh)
- [check-host](https://www.check-host.net)

