<?php

namespace App\Components;

use App\Http\Models\Config;
use Cache;
use Log;

class Yzy
{
    protected static $config;
    protected $accessToken;

    function __construct()
    {
        self::$config = $this->systemConfig();
        $this->accessToken = $this->getAccessToken();
    }

    // 获取accessToken
    public function getAccessToken()
    {
        if (Cache::has('YZY_TOKEN')) {
            $token = Cache::get('YZY_TOKEN');
            if (isset($token['error'])) { // 错误兼容
                Cache::forget('YZY_TOKEN');
            } else {
                return Cache::get('YZY_TOKEN')['access_token'];
            }
        }

        $keys['kdt_id'] = self::$config['kdt_id'];

        $token = (new \Youzan\Open\Token(self::$config['youzan_client_id'], self::$config['youzan_client_secret']))->getToken('self', $keys);

        if (isset($token['error'])) {
            Log::info('获取有赞云支付access_token失败：' . $token['error_description']);

            return '';
        } else {
            Cache::put('YZY_TOKEN', $token, 180);

            return $token['access_token'];
        }
    }

    // 生成收款二维码
    public function createQrCode($goodsName, $price, $orderSn)
    {
        $client = new \Youzan\Open\Client($this->accessToken);

        $params = [
            'qr_name'   => $goodsName, // 商品名
            'qr_price'  => $price, // 单位分
            'qr_source' => $orderSn, // 本地订单号
            'qr_type'   => 'QR_TYPE_DYNAMIC'
        ];

        return $client->get('youzan.pay.qrcode.create', '3.0.0', $params);
    }

    // 通过tid获取交易信息
    public function getTradeByTid($tid)
    {
        $client = new \Youzan\Open\Client($this->accessToken);

        return $client->post('youzan.trade.get', '3.0.0', ['tid' => $tid]);
    }

    // 系统配置
    private function systemConfig()
    {
        $config = Config::query()->get();
        $data = [];
        foreach ($config as $vo) {
            $data[$vo->name] = $vo->value;
        }

        return $data;
    }
}