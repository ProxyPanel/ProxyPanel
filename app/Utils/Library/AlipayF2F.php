<?php
/*
 * 作者：BrettonYe
 * 功能：ProxyPanel 支付宝面对面【收单线下交易预创建】【收单交易查询】接口实现库
 * 更新时间：2023/10/29
 * 参考资料：https://opendocs.alipay.com/open/02ekfg?scene=19 riverslei/payment
 */

namespace App\Utils\Library;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AlipayF2F
{
    private static string $gatewayUrl = 'https://openapi.alipay.com/gateway.do'; //https://openapi-sandbox.dl.alipaydev.com/gateway.do

    private array $config;

    public function __construct(array $rawConfig)
    {
        $config = [
            'app_id' => $rawConfig['app_id'],
            'public_key' => '',
            'private_key' => '',
            'notify_url' => $rawConfig['notify_url'],
        ];

        if ($rawConfig['ali_public_key']) {
            $config['public_key'] = self::getRsaKeyValue($rawConfig['ali_public_key'], false);
        }
        if (empty($config['public_key'])) {
            throw new RuntimeException('please set ali public key');
        }

        // 初始 RSA私钥文件 需要检查该文件是否存在
        if ($rawConfig['rsa_private_key']) {
            $config['private_key'] = self::getRsaKeyValue($rawConfig['rsa_private_key']);
        }
        if (empty($config['private_key'])) {
            throw new RuntimeException('please set ali private key');
        }

        $this->config = $config;
    }

    /**
     * 获取rsa密钥内容.
     *
     * @param  string  $key  传入的密钥信息， 可能是文件或者字符串
     * @param  bool  $is_private  私钥/公钥
     */
    public static function getRsaKeyValue(string $key, bool $is_private = true): ?string
    {
        $keyStr = is_file($key) ? @file_get_contents($key) : $key;
        if (empty($keyStr)) {
            return null;
        }

        $keyStr = str_replace('\n', '', $keyStr);
        // 为了解决用户传入的密钥格式，这里进行统一处理
        if ($is_private) {
            $beginStr = "-----BEGIN RSA PRIVATE KEY-----\n";
            $endStr = "\n-----END RSA PRIVATE KEY-----";
        } else {
            $beginStr = "-----BEGIN PUBLIC KEY-----\n";
            $endStr = "\n-----END PUBLIC KEY-----";
        }

        return $beginStr.wordwrap($keyStr, 64, "\n", true).$endStr;
    }

    public function tradeQuery(array $content): array
    {
        $this->setMethod('alipay.trade.query');
        $this->setContent($content);

        return $this->send();
    }

    private function setMethod(string $method): void
    {
        $this->config['method'] = $method;
    }

    private function setContent(array $content): void
    {
        $content = array_filter($content);
        ksort($content);
        $this->config['biz_content'] = json_encode($content);
    }

    private function send(): array
    {
        $response = Http::timeout(15)->retry(2)->get(self::$gatewayUrl, $this->buildParams())->json();
        $resKey = str_replace('.', '_', $this->config['method']).'_response';
        if (! isset($response[$resKey])) {
            throw new RuntimeException('请求错误-看起来是请求失败');
        }

        if (! $this->validate_response_sign($response[$resKey], $response['sign'])) {
            throw new RuntimeException('验签错误-'.$response[$resKey]['msg'].' | '.($response[$resKey]['sub_msg'] ?? var_export($response, true)));
        }

        $response = $response[$resKey];
        if ($response['msg'] !== 'Success') {
            throw new RuntimeException($response[$resKey]['sub_msg'] ?? var_export($response, true));
        }

        return $response;
    }

    private function buildParams(): array
    {
        $params = [
            'app_id' => $this->config['app_id'],
            'method' => $this->config['method'],
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:m:s'),
            'biz_content' => $this->config['biz_content'],
            'version' => '1.0',
            'notify_url' => $this->config['notify_url'],
        ];
        $params = array_filter($params);
        $params['sign'] = $this->encrypt($this->buildQuery($params));

        return $params;
    }

    /**
     * RSA2签名.
     *
     * @param  string  $data  签名的数组
     *
     * @throws RuntimeException
     */
    private function encrypt(string $data): string
    {
        $privateKey = openssl_pkey_get_private($this->config['private_key']); // 私钥
        if (empty($privateKey)) {
            throw new RuntimeException('您使用的私钥格式错误，请检查RSA私钥配置');
        }

        openssl_sign($data, $sign, $privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($sign); // base64编码
    }

    private function buildQuery(array $params): string
    {
        ksort($params); // 排序

        return urldecode(http_build_query($params)); // 组合
    }

    /**
     * 同步返回验签.
     *
     * @param  array  $body  待签名数据
     *
     * @throws RuntimeException
     */
    public function validate_response_sign(array $body, string $sign): bool
    {
        unset($body['sign'], $body['sign_type']);
        $publicKey = openssl_pkey_get_public($this->config['public_key']);
        if (empty($publicKey)) {
            throw new RuntimeException('支付宝RSA公钥错误。请检查公钥文件格式是否正确');
        }

        return (bool) openssl_verify(json_encode($body), base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256);
    }

    /**
     * 异步通知验签.
     *
     * @param  array  $body  待签名数据
     *
     * @throws RuntimeException
     */
    public function validate_notification_sign(array $body, string $sign): bool
    {
        unset($body['sign'], $body['sign_type']);
        $publicKey = openssl_pkey_get_public($this->config['public_key']);
        if (empty($publicKey)) {
            throw new RuntimeException('支付宝RSA公钥错误。请检查公钥文件格式是否正确');
        }

        return (bool) openssl_verify($this->buildQuery($body), base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256);
    }

    public function qrCharge(array $content): array
    {
        $this->setMethod('alipay.trade.precreate');
        $this->setContent($content);

        return $this->send();
    }
}
