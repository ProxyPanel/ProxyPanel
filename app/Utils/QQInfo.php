<?php

namespace App\Utils;

use Http;

class QQInfo
{
    public static function getQQAvatar(string $qq): ?string
    {
        $ret = null;
        $source = 0;

        while ($source <= 4 && $ret === null) {
            $ret = match ($source) {
                0 => self::qZonePortrait("https://r.qzone.qq.com/fcg-bin/cgi_get_portrait.fcg?get_nick=1&uins=$qq", $qq),
                1 => self::qZonePortrait("https://users.qzone.qq.com/fcg-bin/cgi_get_portrait.fcg?uins=$qq", $qq),
                2 => self::qLogo("https://q.qlogo.cn/g?b=qq&nk=$qq&s=100"),
                3 => self::qLogo("https://thirdqq.qlogo.cn/g?b=qq&nk=$qq&s=100"),
                4 => self::qqLogin($qq),
            };
            $source++;
        }

        return $ret;
    }

    private static function qZonePortrait(string $url, string $qq): ?string
    { //向接口发起请求获取json数据
        $response = Http::timeout(15)->get($url);
        if ($response->ok()) {
            $message = mb_convert_encoding($response->body(), 'UTF-8', 'GBK');
            if (str_contains($message, $qq)) { // 接口是否异常
                $message = json_decode(substr($message, 17, -1), true); //对获取的json数据进行截取并解析成数组

                return stripslashes($message[$qq][0]);
            }
        }

        return null;
    }

    private static function qLogo(string $url): ?string
    {
        $response = Http::timeout(15)->get($url);
        if ($response->ok()) {
            return $url;
        }

        return null;
    }

    private static function qqLogin(string $qq): ?string
    {
        $response = Http::timeout(15)->get("https://ptlogin.qq.com/getface?imgtype=3&uin=$qq");
        if ($response->ok()) {
            $data = $response->body();
            if ($data) {
                return json_decode(substr($data, 13, -1), true)[$qq];
            }
        }

        return null;
    }
}
