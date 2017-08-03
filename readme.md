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
下面是我的安装编译历史命令

yum -y install libevent-devel
yum -y install gcc g++ gcc+ gcc-c++
yum -y install git autoconf
yum -y install pcre-devel
yum -y install openssl openssl-devel
yum -y install pcre-devel openssl openssl-devel
yum -y install vim pv rsync
yum -y install  libxml2 libxml2-devel
yum -y install libxml2-devel openssl-devel libcurl-devel libjpeg-devel libpng-devel libicu-devel openldap-devel
yum -y install curl gd2 gd libevent-devel
yum -y install freetype-devel
yum -y install libmcrypt
yum -y install libXpm-devel
yum -y install libc-client-devel
yum -y install unixODBC-devel
yum -y install aspell-devel
yum -y install readline-devel
yum -y install net-snmp-devel
yum -y install libxslt-devel
yum -y install enchant-devel
yum -y install bzip2 bzip2-devel
yum -y install gmp-devel
yum -y install readline-devel
yum -y install net-snmp-devel
yum -y install libxslt-devel

wget http://am1.php.net/get/php-7.1.7.tar.gz/from/this/mirror
mv mirror.1 php-7.1.7.tar.gz
tar zxvf php-7.1.7.tar.gz
cd php-7.1.7

./configure \
--prefix=/usr/local/php7 \
--enable-fpm \
--with-fpm-user=apache  \
--with-fpm-group=apache \
--enable-inline-optimization \
--disable-debug \
--disable-rpath \
--enable-shared  \
--enable-soap \
--with-libxml-dir \
--with-xmlrpc \
--with-openssl \
--with-mcrypt \
--with-mhash \
--enable-pcntl \
--with-pcre-regex \
--with-sqlite3 \
--with-zlib \
--enable-bcmath \
--with-iconv \
--with-bz2 \
--enable-calendar \
--with-curl \
--with-cdb \
--enable-dom \
--enable-exif \
--enable-fileinfo \
--enable-filter \
--with-pcre-dir \
--enable-ftp \
--with-gd \
--with-openssl-dir \
--with-jpeg-dir \
--with-png-dir \
--with-zlib-dir  \
--with-freetype-dir \
--enable-gd-native-ttf \
--enable-gd-jis-conv \
--with-gettext \
--with-gmp \
--with-mhash \
--enable-json \
--enable-mbstring \
--enable-mbregex \
--enable-mbregex-backtrack \
--with-libmbfl \
--with-onig \
--enable-pdo \
--with-mysqli=mysqlnd \
--with-pdo-mysql=mysqlnd \
--with-zlib-dir \
--with-pdo-sqlite \
--with-readline \
--enable-session \
--enable-shmop \
--enable-simplexml \
--enable-sockets  \
--enable-sysvmsg \
--enable-sysvsem \
--enable-sysvshm \
--enable-wddx \
--with-libxml-dir \
--enable-xml \
--with-xsl \
--enable-zip \
--with-snmp \
--enable-mysqlnd-compression-support \
--with-pear \
--enable-opcache

make && make install

touch /usr/local/php7/etc/php-fpm.conf
vim /usr/local/php7/etc/php-fpm.conf

黏贴如下内容并保存
[global]
pid = /usr/local/php7/var/run/php-fpm.pid
error_log = /usr/local/php7/var/log/php-fpm.log
log_level = notice

[www]
listen = /tmp/php7-cgi.sock
listen.backlog = -1
listen.allowed_clients = 127.0.0.1
listen.owner = www
listen.group = www
listen.mode = 0666
user = www
group = www
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 6
request_terminate_timeout = 100
request_slowlog_timeout = 0
slowlog = var/log/slow.log

vim /usr/local/nginx/conf/vhost/xxx.com.conf
将 fastcgi_pass  unix:/tmp/php-cgi.sock; 改为 fastcgi_pass  unix:/tmp/php7-cgi.sock;

加入 
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}


确保 storage/framework 下有 cache sessions views 三个目录，并且这个storage 777权限
chown -R www:www storage/
chmod -R 777 storage/

service nginx reload
````

#### 拉取代码
````
git clone https://github.com/ssrpanel/ssrpanel.git
cd ssrpanel/
chmod -R 777 storage/
php composer.phar install
php artisan key:generate
````

#### 配置
````
mysql 创建一个数据库，然后自行导入sql\db.sql
config\app.php debug开始或者关闭调试模式
config\database.php mysql选项自行配置数据库
````

#### NGINX配置例子
````
server {
    listen       80;
    server_name  xxx.com ;
    root         "/home/wwwroot/xxx.com/public";
    
    location / {
        index  start.html index.html index.htm index.php;
        try_files $uri $uri/ /index.php$is_args$args;
    }
    
    location ~ \.php(.*)$ {
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_split_path_info  ^((?U).+\.php)(/?.+)$;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        fastcgi_param  PATH_INFO  $fastcgi_path_info;
        fastcgi_param  PATH_TRANSLATED  $document_root$fastcgi_path_info;
        include        fastcgi_params;
    }
}
````

#### 登录
````

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
把apiconfig.py里的 API_INTERFACE 设置为 glzjinmod
该怎么配置还怎么配置，别问我，我不懂
````
## 说明
````
1.纯账号管理后台
2.需要配合SSR后端使用
3.没有用户端
4.支持SS多用户json文件一键转换成SSR多用户json文件
5.支持SSR多用户json文件一键导入数据库
````

![Markdown](http://i1.ciimg.com/1949/9a144d614a97e76c.png)
![Markdown](http://i1.ciimg.com/1949/16a7397810f8819d.png)
![Markdown](http://i1.ciimg.com/1949/6741b88c5a02d550.png)
![Markdown](http://i1.ciimg.com/1949/a12612d57fdaa001.png)
![Markdown](http://i1.ciimg.com/1949/c5c80818393d585e.png)
![Markdown](http://i1.ciimg.com/1949/c52861d84ed70039.png)
![Markdown](http://i1.ciimg.com/1949/83354a1cd7fbd041.png)