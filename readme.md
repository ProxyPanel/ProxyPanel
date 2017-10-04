## 安装步骤
#### 环境要求
````
PHP 7.1 （必须）
MYSQL 5.5 （推荐5.6+）
内存 1G+
磁盘空间 10G+
KVM

使用 LNMP1.4 部署时请到/usr/local/php/etc/php.ini下搜索disable_functions，把proc_开头的函数都删掉

telegram频道：https://t.me/ssrpanel
telegram群组：https://t.me/chatssrpanel

默认管理账号
用户名：admin 密码：123456
````

#### VPS推荐
````
部署面板必须得用到VPS，也就是服务器
强烈推荐使用1G以上内存的KVM架构的服务器

https://github.com/ssrpanel/ssrpanel/wiki/VPS%E6%8E%A8%E8%8D%90
````
![VPS推荐](https://github.com/ssrpanel/ssrpanel/wiki/VPS%E6%8E%A8%E8%8D%90)


#### 打赏作者
````
哈哈，如果你觉得这套代码好用，可以请我吃一个巨无霸汉堡，微信扫一下
将持续开发，喜欢请star一下
````
![打赏作者](https://github.com/ssrpanel/ssrpanel/blob/master/public/assets/images/donate.jpeg?raw=true)

### 捐赠名单
| 昵称      |    金额 |
| :------- | --------:| 
| Law-杰   | ￥10 | 
| Err      | ￥51 | 
| 緃噺開始 |  ￥5 | 
|【要求匿名】|￥267|
|、无奈 |￥5|
|Sunny Woon| ￥10|
|aazzpp678 | ￥26|
|风云_1688|￥15|
截止目前收到的捐赠：￥389
这些捐赠的用途：
1.买了1台VPS做开发测试用
2.一个Beyond Compare 4的正版激活码（2017-10-01）
3.谢谢大家及时反馈BUG，发现BUG请提到issue里


#### PHP7环境配置
````
Laravel 5.4 + Metronic 4.7
建议小白LNMP傻瓜安装出php7.1 + mysql(5.5以上)
手动编译请看WIKI [编译安装PHP7.1.7环境（CentOS）]
````

#### 拉取代码
````
cd /home/wwwroot/
git clone https://github.com/ssrpanel/ssrpanel.git
cd ssrpanel/
php composer.phar install
cp .env.example .env
php artisan key:generate
chown -R www:www storage/
chmod -R 777 storage/
````

#### 配置
````
mysql 创建一个数据库，然后自行导入sql\db.sql
config\app.php debug开始或者关闭调试模式
config\database.php mysql选项自行配置数据库
确保 storage/framework 下有 cache sessions views 三个目录，且 storage 有777权限
````

#### 发送邮件配置
````
config\mail.php 修改其中的配置
````

#### NGINX配置文件加入
````
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
````

#### 重新加载NGINX
````
service nginx reload
````

## SSR服务端
````
把 server 目录下的 ssr-3.4.0.zip 拷贝到 /root/，解压缩，怎么运行自己上网搜
把 userapiconfig.py 里的 API_INTERFACE 设置为 glzjinmod
把 user-config.json 里的 connect_verbose_info 设置为 1
````

## 日志分析（目前仅支持单机单节点）
````
找到SSR服务端所在的ssserver.log文件
进入ssrpanel所在目录，建立一个软连接，并授权
cd /home/wwwroot/ssrpanel/public/storage/app/public
ln -S ssserver.log /root/shadowsocksr/ssserver.log
chown www:www ssserver.log
````

## 定时任务（所有自动发邮件的地方都要用到，所以请务必配置）
````
编辑crontab
crontab -e

然后加入如下（请自行修改ssrpanel路径）
* * * * * php /home/wwwroot/ssrpanel/artisan schedule:run >> /dev/null 2>&1
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
7.定时任务、所有邮件投递都有记录
8.后台一键添加加密方式、混淆、协议
9.强大的后台配置功能
10.更多功能自己发掘
11.ssrpanel的定位：比sspanel强大，比sspanel mod弱鸡
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