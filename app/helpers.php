<?php

const KB = 1024;
const MB = 1048576;
const GB = 1073741824;
const TB = 1099511627776;
const PB = 1125899906842624;

const Minute = 60;
const Hour = 3600;
const Day = 86400;

const Mbps = 125000;

// base64加密（处理URL）
if (! function_exists('base64url_encode')) {
    function base64url_encode($data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}

// base64解密（处理URL）
if (! function_exists('base64url_decode')) {
    function base64url_decode($data)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}

// 根据流量值自动转换单位输出
if (! function_exists('flowAutoShow')) {
    function flowAutoShow($value): string
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
    function seconds2time($seconds): string
    {
        $day = floor($seconds / Day);
        $hour = floor(($seconds % Day) / Hour);
        $minute = floor((($seconds % Day) % Hour) / Minute);
        if ($day > 0) {
            return $day.trans_choice('validation.attributes.day', 1).$hour.trans_choice('validation.attributes.hour', 1).$minute.trans('validation.attributes.minute');
        }

        if ($hour != 0) {
            return $hour.trans_choice('validation.attributes.hour', 1).$minute.trans('validation.attributes.minute');
        }

        return $minute.trans('validation.attributes.minute');
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
    function sysConfig(string $key = null, string $default = null)
    {
        return $key ? config('settings.'.$key, $default) : config('settings');
    }
}

// 字段加密
if (! function_exists('string_encrypt')) {
    function string_encrypt(string $data): string
    {
        $key = config('app.key');

        return base64url_encode(openssl_encrypt($data, 'aes-128-ctr', hash('sha256', $key), OPENSSL_RAW_DATA, substr(sha1($key), 0, 16)));
    }
}

// 字段解密
if (! function_exists('string_decrypt')) {
    function string_decrypt(string $data): string
    {
        $key = config('app.key');

        return openssl_decrypt(base64url_decode($data), 'aes-128-ctr', hash('sha256', $key), OPENSSL_RAW_DATA, substr(sha1($key), 0, 16));
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
    function string_urlsafe($string, $force_lowercase = true, $anal = false)
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
