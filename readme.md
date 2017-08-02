## 安装步骤
#### 0.环境要求
````
PHP 5.6+
MYSQL 5.5+
内存 1G+
磁盘空间 10G+
KVM、OVZ都可以

建议：
PHP 7.1
MYSQL 5.7
内存 2G+
磁盘空间 20G+
KVM

使用 LNMP1.4 部署时请到/usr/local/php/etc/php.ini下搜索disable_functions，把proc_开头的函数都删掉

telegram：https://t.me/ssrpanel
````

#### 1.拉取代码
````
git clone https://github.com/ssrpanel/ssrpanel.git
cd ssrpanel/
chmod -R 777 storage/
php composer.phar install
php artisan key:generate
````

#### 2.配置
````
mysql 创建一个数据库，然后自行导入sql\db.sql
config\app.php debug开始或者关闭调试模式
config\database.php mysql选项自行配置数据库
````

#### 3.NGINX配置例子
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
用户名：admin
密码：123456
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