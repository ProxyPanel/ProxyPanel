<?php

const MB = 1048576;
const GB = 1073741824;

const Minute = 60;
const Hour = 3600;
const Day = 86400;

const Mbps = 125000;

// base64加密（处理URL）
if (! function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}

// base64解密（处理URL）
if (! function_exists('base64url_decode')) {
    function base64url_decode(string $data): false|string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}

// 根据流量值自动转换单位输出
if (! function_exists('formatBytes')) {
    function formatBytes($bytes, int $precision = 2): string
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
        $bytes = max($bytes, 0);
        $power = floor(($bytes ? log($bytes) : 0) / log(1024));
        $power = min($power, count($units) - 1);
        $bytes /= 1024 ** $power;

        return round($bytes, $precision).' '.$units[$power];
    }
}

// 秒转时间
if (! function_exists('formatTime')) {
    function formatTime(int $seconds): string
    {
        $output = '';
        $units = [
            trans('validation.attributes.day') => 86400,
            trans('validation.attributes.hour') => 3600,
            trans('validation.attributes.minute') => 60,
            trans('validation.attributes.second') => 1,
        ];

        foreach ($units as $unit => $value) {
            if ($seconds >= $value) {
                $count = floor($seconds / $value);
                $output .= $count.$unit;
                $seconds %= $value;
            }
        }

        return $output;
    }
}

// 获取系统设置
if (! function_exists('sysConfig')) {
    function sysConfig(string $key = '', string $default = ''): array|string
    {
        return $key ? config("settings.$key", $default) : config('settings');
    }
}

// Array values and indexes clean
if (! function_exists('array_clean')) {
    function array_clean(array &$array): array
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = array_clean($value);
            }
            if (empty($value)) {
                unset($array[$key]);
            }
        }

        return $array;
    }
}

// string url safe sanitize
if (! function_exists('string_urlsafe')) {
    function string_urlsafe($string, $force_lowercase = true, $anal = false): string
    {
        $strip = [
            '~', '`', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '_', '=', '+', '[', '{', ']', '}', '\\', '|', ';', ':', '"', "'", '&#8216;', '&#8217;', '&#8220;',
            '&#8221;', '&#8211;', '&#8212;', 'â€”', 'â€“', ',', '<', '.', '>', '/', '?',
        ];
        $clean = trim(str_replace($strip, '_', strip_tags($string)));
        $clean = preg_replace('/\s+/', '-', $clean);
        $clean = ($anal) ? preg_replace('/[^a-zA-Z0-9]/', '', $clean) : $clean;

        if ($force_lowercase) {
            if (function_exists('mb_strtolower')) {
                $clean = mb_strtolower($clean, 'UTF-8');
            } else {
                $clean = strtolower($clean);
            }
        }

        return $clean;
    }
}
