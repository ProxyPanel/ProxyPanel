<?php

namespace App\Utils\Library;

class PaymentHelper
{
    /**
     * MD5验签.
     *
     * @param  array  $data  未加密的数组信息
     * @param  string  $key  密钥
     * @param  string  $signature  加密的签名
     * @param  bool  $filter  是否清理空值
     */
    public static function verify(array $data, string $key, string $signature, bool $filter = true): bool
    {
        return hash_equals(self::aliStyleSign($data, $key, $filter), $signature);
    }

    /**
     *  Alipay式数据MD5签名.
     *
     * @param  array  $data  需要加密的数组
     * @param  string  $key  尾部的密钥
     * @param  bool  $filter  是否清理空值
     * @return string md5加密后的数据
     */
    public static function aliStyleSign(array $data, string $key, bool $filter = true): string
    { // 依据: https://opendocs.alipay.com/open/common/104741
        unset($data['sign'], $data['sign_type']); // 剃离sign, sign_type
        if ($filter) {
            $data = array_filter($data); // 剃离空值
        }

        ksort($data, SORT_STRING); // 排序

        return md5(urldecode(http_build_query($data)).$key); // 拼接
    }
}
