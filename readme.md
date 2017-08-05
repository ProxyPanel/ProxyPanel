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

#### PHP7环境配置
````
建议小白用LNMP先傻瓜安装出php5.6 + mysql(5.5以上)
然后再编译安装PHP7.1，搭建版本环境

请看WIKI [编译安装PHP7.1.7环境（CentOS）]
````

#### 拉取代码
````
cd /home/wwwroot/
git clone https://github.com/ssrpanel/ssrpanel.git
cd ssrpanel/
php composer.phar install
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

## 代码解释
````
\app\Http\Controllers 控制器文件
\app\Http\Models 模型文件
\config 配置信息
\public 公共文件
\resources\views 视图文件
\storage 临时文件（页面缓存、日志），文件夹一个都不能少，少了必报错
\vendor 组件
\routes 路由
````

## SSR服务端
````
把userapiconfig.py里的 API_INTERFACE 设置为 glzjinmod
````

## 说明
````
1.纯账号管理后台
2.需要配合SSR后端使用
3.没有用户端
4.支持SS多用户json文件一键转换成SSR多用户json文件
5.支持SSR多用户json文件一键导入数据库
6.3天内使用过的都算活跃账号
7.60分钟内使用过的都算在账号
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