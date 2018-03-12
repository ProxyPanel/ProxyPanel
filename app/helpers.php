<?php

// 生成SS密码
if (!function_exists('makeRandStr')) {
    function makeRandStr($length = 8)
    {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $char = '';
        for ($i = 0; $i < $length; $i++) {
            $char .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $char;
    }
}

// base64加密（处理URL）
if (!function_exists('base64url_encode')) {
    function base64url_encode($data)
    {
        return strtr(base64_encode($data), ['+' => '-', '/' => '_', '=' => '']);
    }
}

// base64解密（处理URL）
if (!function_exists('base64url_decode')) {
    function base64url_decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

// 根据流量值自动转换单位输出
if (!function_exists('flowAutoShow')) {
    function flowAutoShow($value = 0)
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
}

if (!function_exists('toMB')) {
    function toMB($traffic)
    {
        $mb = 1048576;

        return $traffic * $mb;
    }
}

if (!function_exists('toGB')) {
    function toGB($traffic)
    {
        $gb = 1048576 * 1024;

        return $traffic * $gb;
    }
}

if (!function_exists('flowToGB')) {
    function flowToGB($traffic)
    {
        $gb = 1048576 * 1024;

        return $traffic / $gb;
    }
}

// 文件大小转换
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}