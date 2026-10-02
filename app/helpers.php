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
        $bytes = round($bytes, $precision);

        // 四舍五入可能把值顶满 1024，凑满就进一格重算（最高单位不进）
        if ($bytes >= 1024 && $power < count($units) - 1) {
            $bytes = round($bytes / 1024, $precision);
            $power++;
        }

        if ($base) {
            $power += max(array_search($base, $units), 0);
        }

        return $bytes.' '.$units[$power];
    }
}

// 秒转时间
if (! function_exists('formatTime')) {
    function formatTime(?int $seconds, int $parts = -1): string
    {
        if (! $seconds) {
            return '-';
        }
        $interval = CarbonInterval::seconds($seconds);

        return $interval->cascade()->forHumans(['parts' => $parts]);
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
    // 只清没填的值：null、''、以及递归清完变空的数组；0、'0'、false 都是合法填写值。
    function array_clean(array &$array): array
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = array_clean($value);
            }
            if ($value === null || $value === '' || $value === []) {
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

// Reverb 子路径前缀（REVERB_PATH / REVERB_SERVER_PATH）的规范形式：
// 前导 / 且结尾不带 /（如 '/casting'），空串表示 Reverb 挂在独立端口或子域上。
// 这三处消费者需要的形式并不相同（见 reverb_client_path），所以只在 .env 里写规范形式，
// 各方拿到值后各自归一化，斜杠多一个少一个都不该出问题。
if (! function_exists('reverb_prefix')) {
    function reverb_prefix(?string $path): string
    {
        $path = trim((string) $path, "/ \t\n\r\0\x0B");

        return $path === '' ? '' : '/'.$path;
    }
}

// 后端广播客户端（Pusher SDK）用的 base_uri 前缀：规范形式 + 结尾 /。
// SDK 用相对路径拼请求地址，前缀没有尾斜杠时 Guzzle 会按 RFC 3986 把前缀当
// “最后一段”替掉，请求就落到 /apps/{app_id}/events（404）——而浏览器侧 pusher-js
// 的 wsPath 恰恰相反、不能带尾斜杠（否则拼出 /casting//app/{key}），两者不可共用同一个原值。
if (! function_exists('reverb_client_path')) {
    function reverb_client_path(?string $path): string
    {
        $prefix = reverb_prefix($path);

        return $prefix === '' ? '' : $prefix.'/';
    }
}
