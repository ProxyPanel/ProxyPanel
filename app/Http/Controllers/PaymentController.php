<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Models\User;
use App\Http\Models\Payment;
use Redirect;
use Response;
use Log;

class PaymentController extends Controller
{
    protected static $config;
    protected static $url = 'https://api.daimiyun.cn/v2/';

    public function __construct()
    {
        self::$config = $this->systemConfig();
    }

    /**
     * 查询订单状态(ajax)
     * @param  Request $request [description]
     * @return Payment          订单 JSON
     */
    public function query(Request $request)
    {
        $pid = $request->get('pid');
        $payment = Payment::query()->find($pid);

        return Response::json($payment);
    }

    /**
     * 发起订单创建请求
     * @param  Request $request 请求
     * @return JSON    订单信息
     */
    public function new(Request $request)
    {
        $type = $request->get('type');
        $price = $request->get('price');

        if (empty($type)) {
            return Response::json(['errcode' => -1, 'errmsg'=> '请选择支付方式']);
        } else if (self::$config['dmf_' . $type] == 0) {
            return Response::json(['errcode' => -1, 'errmsg' => '支付方式不合法']);
        } else if ($price <= 0) {
            return Response::json(['errcode' => -1, 'errmsg' => '请输入正确的充值金额']);
        }

        $user = $request->session()->get('user');
        $user = User::query()->find($user['id']);

        $payment = new Payment();
        $payment->user_id = $user->id;
        $payment->pay_way = '黛米云-' . self::$config['dmf_' . $type];
        $payment->money = $price * 100;
        $payment->status = 0;
        $payment->save();

        $settings = [
            'phone' => self::$config['dmf_' . $type . "_phone"],
            'mchid' => self::$config['dmf_' . $type . "_mchid"],
            'token' => self::$config['dmf_' . $type . "_token"],
        ];

        $data = [
            'trade'   => $payment->id,
            'price'   => $price,
            'phone'   => $settings['phone'],
            'mchid'   => $settings['mchid'],
            'subject' => '[SSRPanel]' . self::$config['website_name'] . "充值" . $price . "元",
            'body'    => '[SSRPanel]' . self::$config['website_name'] . "充值" . $price . "元",
        ];

        $data = $this->sign($data, $settings['token']);
        $ret = $this->post(self::$url . $type . "/create", $data);
        $result = json_decode($ret, true);
        if ($result and $result['errcode'] == 0) {
            $result['pid'] = $payment->id;

            return Response::json($result);
        } else {
            return json_encode([
                'errcode' => -1,
                'errmsg'  => "接口调用失败!" . $ret,
            ]);
        }

        return $result;
    }

    /**
     * 支付宝接口返回
     * @param  Request $req [description]
     * @param  [type]  $type [description]
     * @return [type]        [description]
     */
    public function return (Request $req, $type)
    {
        $money = $_GET['money'];
        echo "您已经成功支付 $money 元,正在跳转..";
        echo <<<HTML
<script>
location.href="/user/payment";
</script>
HTML;

        return;
    }

    /**
     * 回调处理 标记订单状态
     * @param  Request $request [description]
     * @param  [type]   $type    [description]
     * @return function          [description]
     */
    public function callback(Request $request, $type)
    {
        $order_data = $_POST;
        $status = $order_data['status']; // 获取传递过来的交易状态
        $invoiceid = $order_data['out_trade_no']; // 订单号
        $transid = $order_data['trade_no']; // 转账交易号
        $amount = $order_data['money']; // 获取递过来的总价格

        if (!$this->checksign($_POST, self::$config['dmf_' . $type . "_token"])) {
            return Response::json(['errcode' => 2333]);
        }

        if ($status == 'success') {
            $payment = Payment::query()->find($invoiceid);
            if ($payment->status == 1) {
                return Response::json(['errcode' => 0]);
            }

            $payment->status = 1;
            $payment->save();

            // 用户加余额
            $user = User::query()->find($payment->user_id);
            $user->balance += $payment->money * 100;
            $user->save();

            return json_encode(['errcode' => 0]);
        } else {
            return '';
        }
    }

    private function getSign($array, $key)
    {
        unset($array['sign']);
        ksort($array);
        $sss = http_build_query($array);
        $sign = hash("sha256", $sss . $key);
        $sign = sha1($sign . hash("sha256", $key));

        return $sign;
    }

    private function sign($array, $key)
    {
        $array['sign'] = $this->getSign($array, $key);

        return $array;
    }

    private function checkSign($array, $key)
    {
        $new = $array;
        $new = $this->sign($new, $key);
        if (!isset($array['sign'])) {
            return false;
        }

        return $array['sign'] == $new['sign'];
    }

    private function post($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        return $output;
    }
}
