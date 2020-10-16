<?php

use App\Components\Helpers;

define('KB', 1024);
define('MB', 1048576);
define('GB', 1073741824);
define('TB', 1099511627776);
define('PB', 1125899906842624);

define('Minute', 60);
define('Hour', 3600);
define('Day', 86400);

define('Mbps', 125000);

// base64加密（处理URL）
if (! function_exists('base64url_encode')) {
    function base64url_encode($data)
    {
        return strtr(base64_encode($data), ['+' => '-', '/' => '_', '=' => '']);
    }
}

// base64解密（处理URL）
if (! function_exists('base64url_decode')) {
    function base64url_decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

// 根据流量值自动转换单位输出
if (! function_exists('flowAutoShow')) {
    function flowAutoShow($value)
    {
        $value = abs($value);
        if ($value >= PB) {
            return round($value / PB, 2).'PB';
        }

        if ($value >= TB) {
            return round($value / TB, 2).'TB';
        }

        if ($value >= GB) {
            return round($value / GB, 2).'GB';
        }

        if ($value >= MB) {
            return round($value / MB, 2).'MB';
        }

        if ($value >= KB) {
            return round($value / KB, 2).'KB';
        }

        return round($value, 2).'B';
    }
}

// 秒转时间
if (! function_exists('seconds2time')) {
    function seconds2time($seconds)
    {
        $day = floor($seconds / Day);
        $hour = floor(($seconds % Day) / Hour);
        $minute = floor((($seconds % Day) % Hour) / Minute);
        if ($day > 0) {
            return $day.'天'.$hour.'小时'.$minute.'分';
        }

        if ($hour != 0) {
            return $hour.'小时'.$minute.'分';
        }

        return $minute.'分';
    }
}

// 过滤emoji表情
if (! function_exists('filterEmoji')) {
    function filterEmoji($str)
    {
        return preg_replace_callback('/./u', static function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $str);
    }
}

// 获取系统设置
if (! function_exists('sysConfig')) {
    function sysConfig($name)
    {
        $ret = Cache::tags('sysConfig')->get($name);
        if (is_null($ret)) {
            return Helpers::cacheSysConfig($name);
        }

        return $ret;
    }
}
