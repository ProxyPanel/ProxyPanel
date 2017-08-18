## 安装步骤
#### 0.环境要求
````
PHP 7.1
MYSQL 5.7
内存 1G+
磁盘空间 10G+
KVM

使用 LNMP1.4 部署时请到/usr/local/php/etc/php.ini下搜索disable_functions，把proc_开头的函数都删掉

telegram：https://t.me/ssrpanel

默认管理账号
用户名：admin 密码：123456
````

#### 打赏作者一个巨无霸汉堡
````
哈哈，如果你觉得这套代码好用，可以请我吃一个巨无霸汉堡，微信扫一下
将持续开发，喜欢请star一下
````
![打赏作者一个巨无霸汉堡](https://raw.githubusercontent.com/ssrpanel/ssrpanel/723d6f9d35d10db7c57dab962c972035099a733f/public/assets/images/donate.jpeg)

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
把userapiconfig.py里的 API_INTERFACE 设置为 glzjinmod
把user-config.json里的 connect_verbose_info 设置为 1
````

## 日志分析（目前仅支持单节点）
````
找到SSR服务端所在的ssserver.log文件
进入ssrpanel所在目录，建立一个软连接，并授权
cd /home/wwwroot/ssrpanel/public/storage/app/public
ln -S ssserver.log /root/shadowsocksr/ssserver.log
chown www:www ssserver.log
````

## 说明
````
1.账号管理面板
2.需配合SSR后端使用
3.强大的管理后台
4.美观的界面
5.支持手机自适应，方便管理账号
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