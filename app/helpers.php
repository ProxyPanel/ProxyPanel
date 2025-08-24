<?php

use Carbon\Carbon;
use Carbon\CarbonInterval;

const MiB = 1048576;
const GiB = 1073741824;
const TiB = 1099511627776;

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
    function formatBytes(int $bytes, ?string $base = null, int $precision = 2): string
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
        $bytes = max($bytes, 0);
        $power = floor(($bytes ? log($bytes) : 0) / log(1024));
        $power = min($power, count($units) - 1);
        $bytes /= 1024 ** $power;

        if ($base) {
            $power += max(array_search($base, $units), 0);
        }

        return round($bytes, $precision).' '.$units[$power];
    }
}

// 秒转时间
if (! function_exists('formatTime')) {
    function formatTime(?int $seconds): string
    {
        if (! $seconds) {
            return '-';
        }
        $interval = CarbonInterval::seconds($seconds);

        return $interval->cascade()->forHumans();
    }
}

// 获取系统设置
if (! function_exists('sysConfig')) {
    function sysConfig(?string $key = null, ?string $default = null): array|string|int|null
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
    function string_urlsafe(string $string, bool $force_lowercase = true, bool $anal = false): string
    {
        $clean = preg_replace('/[~`!@#$%^&*()_=+\[\]{}\\|;:"\'<>,.?\/]/', '_', strip_tags($string));
        $clean = preg_replace('/\s+/', '-', $clean);
        $clean = ($anal) ? preg_replace('/[^a-zA-Z0-9]/', '', $clean) : $clean;

        if ($force_lowercase) {
            $clean = function_exists('mb_strtolower') ? mb_strtolower($clean, 'UTF-8') : strtolower($clean);
        }

        return $clean;
    }
}

if (! function_exists('localized_date')) {
    function localized_date($date): string
    {
        if (! $date) {
            return '';
        }

        $carbon = Carbon::parse($date);
        $locale = app()->getLocale();
        $carbon->setLocale($locale);

        // 获取原始字符串表示
        $dateStr = is_string($date) ? $date : $date->format('Y-m-d H:i:s');

        // 使用正则检测精度
        if (preg_match('/(\d{4}-\d{2}-\d{2}) (\d{2}):(\d{2}):(\d{2})/', $dateStr, $matches)) {
            $hours = (int) $matches[2];
            $minutes = (int) $matches[3];
            $seconds = (int) $matches[4];

            if ($seconds > 0) {
                return $carbon->isoFormat('LL LTS'); // 显示完整时间
            }

            if ($minutes > 0 || $hours > 0) {
                return $carbon->isoFormat('LL LT'); // 显示到分钟
            }
        }

        return $carbon->isoFormat('LL'); // 只显示日期
    }
}
