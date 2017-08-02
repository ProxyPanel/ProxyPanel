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