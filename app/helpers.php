<?php

// 生成SS密码
if (!function_exists('makeRandStr')) {
    function makeRandStr($length = 6, $isNumbers = false)
    {
        // 密码字符集，可任意添加你需要的字符
        if (!$isNumbers) {
            $chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        } else {
            $chars = '0123456789';
        }

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
        if (abs($value) >= $pb) {
            return round($value / $pb, 2) . "PB";
        } elseif (abs($value) >= $tb) {
            return round($value / $tb, 2) . "TB";
        } elseif (abs($value) >= $gb) {
            return round($value / $gb, 2) . "GB";
        } elseif (abs($value) >= $mb) {
            return round($value / $mb, 2) . "MB";
        } elseif (abs($value) >= $kb) {
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

// 获取访客真实IP
if (!function_exists('getClientIP')) {
    function getClientIP()
    {
        /*
         * 访问时用localhost访问的，读出来的是“::1”是正常情况
         * ::1说明开启了IPv6支持，这是IPv6下的本地回环地址的表示
         * 使用IPv4地址访问或者关闭IPv6支持都可以不显示这个
         */
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
                $ip = $_SERVER['REMOTE_ADDR'];
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_ip'])) {
                $ip = $_SERVER['HTTP_CLIENT_ip'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            } else {
                $ip = 'unknown';
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_ip')) {
                $ip = getenv('HTTP_CLIENT_ip');
            } else {
                $ip = getenv('REMOTE_ADDR');
            }
        }

        if (trim($ip) == '::1') {
            $ip = '127.0.0.1';
        }

        return $ip;
    }
}

// 获取IPv6信息
if (!function_exists('getIPv6')) {
    /*
     * {
     *     "longitude": 105,
     *     "latitude": 35,
     *     "area_code": "0",
     *     "dma_code": "0",
     *     "organization": "AS23910 China Next Generation Internet CERNET2",
     *     "country": "China",
     *     "ip": "2001:da8:202:10::36",
     *     "country_code3": "CHN",
     *     "continent_code": "AS",
     *     "country_code": "CN"
     *  }
     */
    function getIPv6($ip)
    {
        $url = 'https://api.ip.sb/geoip/' . $ip;

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 500);
            // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
            // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 0);

            $result = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($result, true);
            if (!is_array($result) || isset($result['code'])) {
                throw new Exception('解析IPv6信息异常：' . $ip);
            }

            return $result;
        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return [];
        }
    }
}