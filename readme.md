## 安装步骤
#### 1.配置composer中文镜像
````
composer config repo.packagist composer https://packagist.phpcomposer.com
````

#### 2.安装
````
composer install
````

#### 3.生成key
````
php artisan key:generate
````

#### 4.配置
````
mysql 创建一个数据库，然后自行导入sql\db.sql
config\app.php debug开始或者关闭调试模式
config\database.php mysql选项自行配置数据库
````

#### 5.登录
````
用户名：admin
密码：123456
````

#### 6.nginx
````
chmod -R 777 /home/www/ssrpanel/storage
root /home/www/ssrpanel/public;

location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
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
该怎么配置还怎么配置
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