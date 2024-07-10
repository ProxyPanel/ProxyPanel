<?php

namespace App\Utils;

use Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Avatar
{
    private static PendingRequest $basicRequest;

    public static function getAvatar(Request $request): JsonResponse
    {
        $username = $request->input('username');
        $qq = $request->input('qq');
        if ($qq) {
            $url = self::getQQAvatar($qq);
        } elseif ($username && stripos(strtolower($request->input('username')), '@qq.com') !== false) {
            $url = self::getQQAvatar($username);
        } else {
            $url = self::getRandomAvatar($username);
        }

        return response()->json($url);
    }

    public static function getQQAvatar(string $qq): ?string
    {
        self::$basicRequest = Http::timeout(15)->withOptions(['http_errors' => false])->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36')->replaceHeaders(['Referer' => null]);
        $ret = null;
        $source = 1;

        while ($source <= 4 && $ret === null) {
            $ret = match ($source) {
                1 => self::qLogo("https://q.qlogo.cn/g?b=qq&nk=$qq&s=100"),
                2 => self::qZonePortrait("https://users.qzone.qq.com/fcg-bin/cgi_get_portrait.fcg?uins=$qq", $qq),
                3 => self::qLogo("https://thirdqq.qlogo.cn/g?b=qq&nk=$qq&s=100"),
                4 => self::qqLogin($qq),
            };
            $source++;
        }

        return $ret;
    }

    private static function qLogo(string $url): ?string
    {
        $response = self::$basicRequest->get($url);
        if ($response->ok()) {
            return $url;
        }

        return null;
    }

    private static function qZonePortrait(string $url, string $qq): ?string
    { //向接口发起请求获取json数据
        $response = self::$basicRequest->get($url);
        if ($response->ok()) {
            $message = mb_convert_encoding($response->body(), 'UTF-8', 'GBK');
            if (str_contains($message, $qq)) { // 接口是否异常
                $message = json_decode(substr($message, 17, -1), true); //对获取的json数据进行截取并解析成数组

                return stripslashes($message[$qq][0]);
            }
        }

        return null;
    }

    private static function qqLogin(string $qq): ?string
    {
        $response = self::$basicRequest->get("https://ptlogin.qq.com/getface?imgtype=3&uin=$qq");
        if ($response->ok()) {
            $data = $response->body();
            if ($data) {
                return json_decode(substr($data, 13, -1), true)[$qq];
            }
        }

        return null;
    }

    public static function getRandomAvatar(string $username): string
    {
        // 'https://api.sretna.cn/kind/ar.php','https://api.qjqq.cn/api/MiYouShe',
        // 'https://api.uomg.com/api/rand.avatar?sort=%E5%8A%A8%E6%BC%AB%E5%A5%B3&format=images','https://api.uomg.com/api/rand.avatar?sort=%E5%8A%A8%E6%BC%AB%E7%94%B7&format=images',
        // 'https://zt.sanzhixiongnet.cn/api.php','https://api.vvhan.com/api/avatar/dm',
        $apiUrls = [
            'https://www.loliapi.com/acg/pp/',
            'https://api.dicebear.com/9.x/thumbs/svg?seed='.$username.'&radius=50',
            'https://www.cuteapi.com/api/acg/head-portrait/',
            'https://api.lolimi.cn/API/dmtx/',
            'https://t.alcy.cc/tx/',
        ];

        return $apiUrls[array_rand($apiUrls)];
    }
}
