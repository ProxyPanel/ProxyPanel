<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Models\Config;
use App\Http\Models\EmailLog;
use App\Http\Models\Level;
use App\Http\Models\SsConfig;
use App\Http\Models\User;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // 生成SS密码
    public function makeRandStr($length = 6)
    {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $char = '';
        for ($i = 0; $i < $length; $i++) {
            $char .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $char;
    }

    // base64加密（处理URL）
    function base64url_encode($data)
    {
        return strtr(base64_encode($data), array('+' => '-', '/' => '_', '=' => ''));
    }

    // base64解密（处理URL）
    function base64url_decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    // 根据流量值自动转换单位输出
    public function flowAutoShow($value = 0)
    {
        $kb = 1024;
        $mb = 1048576;
        $gb = 1073741824;
        $tb = $gb * 1024;
        $pb = $tb * 1024;
        if (abs($value) > $pb) {
            return round($value / $pb, 2) . "PB";
        } elseif (abs($value) > $tb) {
            return round($value / $tb, 2) . "TB";
        } elseif (abs($value) > $gb) {
            return round($value / $gb, 2) . "GB";
        } elseif (abs($value) > $mb) {
            return round($value / $mb, 2) . "MB";
        } elseif (abs($value) > $kb) {
            return round($value / $kb, 2) . "KB";
        } else {
            return round($value, 2) . "B";
        }
    }

    public function toMB($traffic)
    {
        $mb = 1048576;

        return $traffic * $mb;
    }

    public function toGB($traffic)
    {
        $gb = 1048576 * 1024;

        return $traffic * $gb;
    }

    public function flowToGB($traffic)
    {
        $gb = 1048576 * 1024;

        return $traffic / $gb;
    }

    // 加密方式
    public function methodList()
    {
        return SsConfig::query()->where('type', 1)->get();
    }

    // 协议
    public function protocolList()
    {
        return SsConfig::query()->where('type', 2)->get();
    }

    // 混淆
    public function obfsList()
    {
        return SsConfig::query()->where('type', 3)->get();
    }

    // 等级
    public function levelList()
    {
        return Level::query()->get()->sortBy('level');
    }

    // 系统配置
    public function systemConfig()
    {
        $config = Config::query()->get();
        $data = [];
        foreach ($config as $vo) {
            $data[$vo->name] = $vo->value;
        }

        return $data;
    }

    // 获取一个随机端口
    public function getRandPort()
    {
        $config = $this->systemConfig();

        $port = mt_rand($config['min_port'], $config['max_port']);
        $deny_port = [1068, 1109, 1434, 3127, 3128, 3129, 3130, 3332, 4444, 5554, 6669, 8080, 8081, 8082, 8181, 8282, 9996, 17185, 24554, 35601, 60177, 60179]; // 不生成的端口

        $exists_port = User::query()->pluck('port')->toArray();
        if (in_array($port, $exists_port) || in_array($port, $deny_port)) {
            $port = $this->getRandPort();
        }

        return $port;
    }

    // 类似Linux中的tail命令
    public function tail($file, $n, $base = 5)
    {
        $fileLines = $this->countLine($file);
        if ($fileLines < 15000) {
            return false;
        }

        $fp = fopen($file, "r+");
        assert($n > 0);
        $pos = $n + 1;
        $lines = array();
        while (count($lines) <= $n) {
            try {
                fseek($fp, -$pos, SEEK_END);
            } catch (\Exception $e) {
                fseek(0);
                break;
            }

            $pos *= $base;
            while (!feof($fp)) {
                array_unshift($lines, fgets($fp));
            }
        }

        return array_slice($lines, 0, $n);
    }

    /**
     * 计算文件行数
     */
    public function countLine($file)
    {
        $fp = fopen($file, "r");
        $i = 0;
        while (!feof($fp)) {
            //每次读取2M
            if ($data = fread($fp, 1024 * 1024 * 2)) {
                //计算读取到的行数
                $num = substr_count($data, "\n");
                $i += $num;
            }
        }

        fclose($fp);

        return $i;
    }

    /**
     * 文件大小转换
     *
     * @param int $bytes
     * @param int $precision
     *
     * @return string
     */
    public function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * 写入邮件发送日志
     * @param int $user_id 用户ID
     * @param string $title 标题
     * @param string $content 内容
     * @param int $status 投递状态
     * @param string $error 投递失败时记录的异常信息
     */
    public function sendEmailLog($user_id, $title, $content, $status = 1, $error = '')
    {
        $emailLogObj = new EmailLog();
        $emailLogObj->user_id = $user_id;
        $emailLogObj->title = $title;
        $emailLogObj->content = $content;
        $emailLogObj->status = $status;
        $emailLogObj->error = $error;
        $emailLogObj->created_at = date('Y-m-d H:i:s');
        $emailLogObj->save();
    }
}
