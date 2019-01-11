<?php

namespace App\Components;

class Curl
{
    /**
     * @param string $url  请求地址
     * @param array  $data 数据，如果有数据则用POST请求
     *
     * @return mixed
     */
    public static function send($url, $data = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);

        if ($data) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
}