<?php
/**
 *ProxyPanel安装程序.
 *
 * 安装完成后建议删除此文件
 *
 * @author Heron
 */

// error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
// ini_set('display_errors', '1');
define('DS', DIRECTORY_SEPARATOR); // 定义目录分隔符
define('ROOT_PATH', __DIR__.DS.'..'.DS); // 定义根目录
define('DB_PATH', ROOT_PATH.'sql'.DS.'db.sql'); // 数据库

// 判断文件或目录是否有写的权限
function is_really_writable($file)
{
    if (DIRECTORY_SEPARATOR == '/' and @ini_get('safe_mode') == false) {
        return is_writable($file);
    }

    if (! is_file($file) or ($fp = @fopen($file, 'r+')) === false) {
        return false;
    }

    fclose($fp);

    return true;
}

$name = 'ProxyPanel';

// 检测依赖组件目录是否存在
$checkDirs = [
    'vendor',
];

// 错误信息
$errInfo = '';

// 数据库配置文件
$ConfigFile = ROOT_PATH.'.env';

// 数据库标准配置文件
$exampleConfigFile = ROOT_PATH.'.env.example';

// 锁定的文件
$lockFile = ROOT_PATH.'.env';
if (is_file($lockFile)) {
    $errInfo = '如果需要重新安装，请备份数据库后手动移除 .env 文件';
} elseif (version_compare(PHP_VERSION, '7.3.0', '<')) {
    $errInfo = '当前PHP版本('.PHP_VERSION.')过低，请使用PHP7.3.0及以上版本';
} elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $errInfo = '当前系统环境为Windows，无法进行安装';
} elseif (! is_file($exampleConfigFile)) {
    $errInfo = '缺失标准配置文件 .env.example';
} elseif (! extension_loaded('PDO')) {
    $errInfo = '当前PHP环境未启用PDO组件，无法进行安装';
} elseif (! is_really_writable(ROOT_PATH)) {
    $open_basedir = ini_get('open_basedir');
    if ($open_basedir) {
        $dirArr = explode(PATH_SEPARATOR, $open_basedir);
        if ($dirArr && in_array(__DIR__, $dirArr)) {
            $errInfo = '当前服务器因配置了open_basedir，导致无法读取应用根目录';
        }
    }

    if (! $errInfo) {
        $errInfo = '权限不足，无法写入配置文件.env';
    }
} else {
    $dirArr = [];
    foreach ($checkDirs as $k => $v) {
        if (! is_dir(ROOT_PATH.$v)) {
            $errInfo = '请先在'.$name.'根目录下执行<b>php composer.phar install</b> 安装依赖';
            break;
        }
    }
}

// 当前是POST请求
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($errInfo) {
        echo $errInfo;
        exit;
    }

    $err = '';
    $APP_KEY = md5(time().mt_rand(1, 1000000));
    $DB_HOST = isset($_POST['mysqlHost']) ? trim($_POST['mysqlHost']) : '127.0.0.1';
    $DB_PORT = isset($_POST['mysqlPort']) ? trim($_POST['mysqlPort']) : 3306;
    $hostArr = explode(':', $DB_HOST);
    if (count($hostArr) > 1) {
        $DB_HOST = $hostArr[0];
        $DB_PORT = $hostArr[1];
    }
    $DB_USERNAME = isset($_POST['mysqlUsername']) ? trim($_POST['mysqlUsername']) : 'proxypanel';
    $DB_PASSWORD = isset($_POST['mysqlPassword']) ? trim($_POST['mysqlPassword']) : 'proxypanel';
    $DB_DATABASE = isset($_POST['mysqlDatabase']) ? trim($_POST['mysqlDatabase']) : 'proxypanel';

    try {
        // 检测能否读取数据库文件
        $sql = @file_get_contents(DB_PATH);
        if (! $sql) {
            throw new Exception('无法读取所需的'.DB_PATH.'，请检查是否有读权限');
        }

        $config = @file_get_contents($exampleConfigFile);
        if (! $config) {
            throw new Exception('无法读取配置.env.example文件，请检查是否有读权限');
        }

        $pdo = new PDO("mysql:host={$DB_HOST};port={$DB_PORT}", $DB_USERNAME, $DB_PASSWORD, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        ]);

        // 检测是否支持innodb存储引擎
        $pdoStatement = $pdo->query("SHOW VARIABLES LIKE 'innodb_version'");
        $result = $pdoStatement->fetch();
        if (! $result) {
            throw new Exception('当前数据库不支持innodb存储引擎，请开启后再重新尝试安装');
        }

        $pdo->query("CREATE DATABASE IF NOT EXISTS `{$DB_DATABASE}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->query("USE `{$DB_DATABASE}`");
        $pdo->exec($sql);

        // 写入数据库配置到.env文件
        $callback = function ($matches) use ($APP_KEY, $DB_HOST, $DB_PORT, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE) {
            $field = $matches[1];
            $replace = ${"{$field}"};

            return "{$matches[1]}={$replace}".PHP_EOL;
        };
        $config = preg_replace_callback("/(APP_KEY|DB_HOST|DB_DATABASE|DB_USERNAME|DB_PASSWORD|DB_PORT)=(.*)(\s+)/",
            $callback, $config);
        $result = @file_put_contents($ConfigFile, $config);
        if (! $result) {
            throw new Exception('无法写入数据库信息到.env文件，请检查是否有写权限');
        }

        echo 'success';
    } catch (PDOException $e) {
        $err = $e->getMessage();
    } catch (Exception $e) {
        $err = $e->getMessage();
    }

    echo $err;
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>安装<?php
        echo $name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1">
    <meta name="renderer" content="webkit">

    <style>
        body {
            background: #5c97bd;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        body, input, button {
            font-family: 'Open Sans', sans-serif;
            font-size: 16px;
            color: #fff;
        }

        .container {
            max-width: 515px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }

        a {
            color: #fff7d0;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 28px;
            font-weight: normal;
            color: #fff;
            margin-bottom: 0;
        }

        form {
            margin-top: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group .form-field:first-child input {
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }

        .form-group .form-field:last-child input {
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
        }

        .form-field input {
            background: #6ba3c8;
            margin: 0 0 1px;
            border: 2px solid transparent;
            transition: background 0.2s, border-color 0.2s, color 0.2s;
            width: 100%;
            padding: 15px 15px 15px 180px;
            box-sizing: border-box;
        }

        .form-field input:focus {
            border-color: #e8f6ff;
            outline: none;
        }

        .form-field label {
            float: left;
            width: 160px;
            text-align: right;
            margin-right: -160px;
            position: relative;
            margin-top: 18px;
            font-size: 14px;
            pointer-events: none;
            opacity: 0.7;
        }

        button, .btn {
            background: #fff;
            color: #6ba3ca;
            border: 0;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            padding: 15px 30px;
            -webkit-appearance: none;
        }

        button[disabled] {
            opacity: 0.5;
        }

        #error, .error, #success, .success {
            background: #d66c6c;
            color: #fff;
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        #success {
            background: #3C5675;
        }

        #error a, .error a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>

<body>
<div class="container">
    <h2>安装 <?php
        echo $name; ?></h2>
    <div>
        <form method="post">
            <?php
            if ($errInfo) { ?>
                <div class="error">
                    <?php
                    echo $errInfo; ?>
                </div>
            <?php
            } ?>
            <div id="error" style="display:none"></div>
            <div id="success" style="display:none"></div>

            <div class="form-group">
                <div class="form-field">
                    <label>MySQL 数据库地址</label>
                    <input type="text" name="mysqlHost" value="127.0.0.1" required="">
                </div>

                <div class="form-field">
                    <label>MySQL 数据库名</label>
                    <input type="text" name="mysqlDatabase" value="proxypanel" required="">
                </div>

                <div class="form-field">
                    <label>MySQL 用户名</label>
                    <input type="text" name="mysqlUsername" value="proxypanel" required="">
                </div>

                <div class="form-field">
                    <label>MySQL 密码</label>
                    <input type="password" name="mysqlPassword">
                </div>

                <div class="form-field">
                    <label>MySQL 端口号</label>
                    <input type="number" name="mysqlPort" value="3306">
                </div>
            </div>

            <div class="form-buttons">
                <button type="submit" <?php
                echo $errInfo ? 'disabled' : '' ?>>安装
                </button>
            </div>
        </form>

        <script src="//cdn.staticfile.org/jquery/2.1.4/jquery.min.js" type="text/javascript"></script>
        <script>
            $(function() {

                $('form').on('submit', function(e) {
                    e.preventDefault();

                    var $button = $(this).find('button').text('安装中...').prop('disabled', true);

                    $.post('', $(this).serialize()).done(function(ret) {
                        if (ret === 'success') {
                            $('#error').hide();
                            $('#success').text('安装成功，请使用[用户名：test@test.com、密码：123456]登录').show();
                            $('<a class="btn" href="./admin/login">登录后台</a>').insertAfter($button);
                            $button.remove();
                            localStorage.setItem('fastep', 'installed');
                        }
                        else {
                            $('#error').show().text(ret);
                            $button.prop('disabled', false).text('点击安装');
                            $('html,body').animate({
                                scrollTop: 0,
                            }, 500);
                        }
                    }).fail(function(data) {
                        $('#error').show().text('发生错误:\n\n' + data.responseText);
                        $button.prop('disabled', false).text('点击安装');
                        $('html,body').animate({
                            scrollTop: 0,
                        }, 500);
                    });

                    return false;
                });
            });
        </script>
    </div>
</div>
</body>
</html>
