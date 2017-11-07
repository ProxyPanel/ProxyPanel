## 安装步骤
#### 环境要求
````
PHP 7.1 （必须）
MYSQL 5.5 （推荐5.6+）
内存 1G+ 
磁盘空间 10G+
KVM

PHP必须开启gd2、fileinfo组件

小白建议使用LNMP傻瓜安装出php7.1 + mysql(5.5以上)
手动编译请看WIKI [编译安装PHP7.1.7环境（CentOS）]
使用LNMP部署时请到/usr/local/php/etc/php.ini下搜索disable_functions，把proc_开头的函数都删掉

telegram频道：https://t.me/ssrpanel
telegram群组：https://t.me/chatssrpanel
开发测试演示：http://www.ssrpanel.com
用户名：admin 密码：123456
(请大家勿改admin的密码)
````

![VPS推荐](https://github.com/ssrpanel/ssrpanel/wiki/VPS%E6%8E%A8%E8%8D%90)
````
部署面板必须得用到VPS，也就是服务器
强烈推荐使用1G以上内存的KVM架构的服务器
````

#### 打赏作者
````
如果你觉得这套代码好用，可以请我吃一个巨无霸汉堡，微信扫一下
我不以此谋生，所以你在使用过程中有发现问题就提issue，有空我会改的
别搞的我像是欠你钱似的，一个免费的开源的东西，你想要什么功能会的话自己加，不然就别哔哔
持续开发，喜欢请star一下
````
![打赏作者](https://github.com/ssrpanel/ssrpanel/blob/master/public/assets/images/donate.jpeg?raw=true)

### 打赏名单
|昵称|金额|
|:-------|--------:| 
|Law-杰|￥10| 
|Err| ￥51 | 
|緃噺開始 |￥5| 
|【要求匿名】|￥267|
|、无奈|￥5|
|Sunny Woon|￥10|
|aazzpp678|￥26|
|风云_1688|￥15|
|Royal|￥25|
|bingo|￥8|
|Eason|￥10|
|【要求匿名】|￥60|

截止目前收到的捐赠：￥492，实际到账：￥487.08 （提款手续费4.92）

这些捐赠的用途：
- 1.30刀买了1台VPS做开发测试用（后被干扰到几乎无法SSH）
- 2.30刀买了一个Beyond Compare 4 Standard的正版激活码
- 3.感谢`Jyo`提供一个台Azure给我开发测试用，需要代购VPS找在tg群里找他
- 4.感谢`izhangxm`提交了自定义等级的分支代码
- 5.感谢`Hao-Luo`提供的节点一键部署脚本


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

#### 配置一下
````
cd ssrpanel/
php composer.phar install
php artisan key:generate
chown -R www:www storage/
chmod -R 777 storage/
````

#### NGINX配置文件加入
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

## 定时任务（所有自动发邮件的地方都要用到，所以请务必配置）
````
编辑crontab
crontab -e

然后加入如下（请自行修改ssrpanel路径）
* * * * * php /home/wwwroot/ssrpanel/artisan schedule:run >> /dev/null 2>&1
````

#### 发送邮件配置
````
config\mail.php 修改其中的配置
````

## 日志分析（目前仅支持单机单节点）
````
找到SSR服务端所在的ssserver.log文件
进入ssrpanel所在目录，建立一个软连接，并授权
cd /home/wwwroot/ssrpanel/public/storage/app/public
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
chmod a+x update.sh && sh update.sh

如果每次更新都会出现数据库文件被覆盖
请先执行一次 chmod a+x fix_git.sh && sh fix_git.sh
````

## 网卡流量监控一键脚本
````
wget -N --no-check-certificate https://raw.githubusercontent.com/ssrpanel/ssrpanel/master/server/deploy_vnstat.sh;chmod +x deploy_vnstat.sh;./deploy_vnstat.sh
````


## 说明
````
1.多节点账号管理面板
2.需配合SSR 3.4 Python版后端使用
3.强大的管理后台、美观的界面、简单易用的开关、支持移动端自适应
4.内含简单的购物、优惠券、流量兑换、邀请码、推广返利&提现、文章管理、工单等系统
5.节点可以分组，不同级别的用户可以看到不同级别分组的节点
6.SS配置转SSR配置，方便使用SS后端一键把账号转入到系统
7.流量日志、单机单节点日志分析功能，知道用户最近都看了哪些网站
7.定时任务、所有邮件投递都有记录，账号临近到期、流量不够都会自动发邮件提醒，自动禁用到期账号
8.后台一键添加加密方式、混淆、协议
9.强大的后台配置功能
10.屏蔽常见爬虫
11.更多功能和开发排期请看WIKI
````

![Markdown](http://i4.bvimg.com/1949/aac73bf589fbd785.png)
![Markdown](http://i4.bvimg.com/1949/a7c21b7504805130.png)
![Markdown](http://i4.bvimg.com/1949/ee4e72cab0deb8b0.png)
![Markdown](http://i4.bvimg.com/1949/ee21b577359a638a.png)
![Markdown](http://i1.ciimg.com/1949/6741b88c5a02d550.png)
![Markdown](http://i1.ciimg.com/1949/a12612d57fdaa001.png)
![Markdown](http://i1.ciimg.com/1949/c5c80818393d585e.png)
![Markdown](http://i1.ciimg.com/1949/c52861d84ed70039.png)
![Markdown](http://i1.ciimg.com/1949/83354a1cd7fbd041.png)
![Markdown](http://i1.bvimg.com/1949/13b6e4713a6d29c2.png)