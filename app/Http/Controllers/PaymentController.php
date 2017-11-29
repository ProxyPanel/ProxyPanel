<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Models\Article;
use App\Http\Models\Coupon;
use App\Http\Models\CouponLog;
use App\Http\Models\Goods;
use App\Http\Models\Invite;
use App\Http\Models\Level;
use App\Http\Models\Order;
use App\Http\Models\OrderGoods;
use App\Http\Models\ReferralApply;
use App\Http\Models\ReferralLog;
use App\Http\Models\Ticket;
use App\Http\Models\TicketReply;
use App\Http\Models\User;
use App\Http\Models\UserBalanceLog;
use App\Http\Models\UserScoreLog;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserTrafficDaily;
use App\Http\Models\UserTrafficHourly;
use App\Http\Models\Verify;
use App\Http\Models\Payment;
use App\Mail\activeUser;
use App\Mail\resetPassword;
use Redirect;
use Response;
use Cache;
use Mail;
use DB;
use Log;

class PaymentController extends Controller
{
    protected static $config;
    /**
     * 构造函数
     */
    public function __construct()
    {
        self::$config = $this->systemConfig();
    }

    /**
     * 查询订单状态(ajax)
     * @param  Request $request [description]
     * @return Payment          订单 JSON
     */
    public function query(Request $request){
        return Payment::find($request->pid)->toarray();
    }
    /**
     * 发起订单创建请求
     * @param  Request $request 请求
     * @return JSON    订单信息
     */
    public function new(Request $request){
        $type = $request->type;
        $price = $request->price;
        if(self::$config[$type."_enabled"]==0){
            return json_encode(['errcode'=>-1,'errmsg'=>"非法的支付方式."]);
        }
        if($price <= 0){
            return json_encode(['errcode'=>-1,'errmsg'=>"非法的金额."]);
        }
        $user = $request->session()->get('user');
        $user = User::find($user['id']);
        $pl = new Payment();
        $pl->user_id = $user->id;
        $pl->money = $price;
        $pl->status=0;

        $pl->save();
        $settings = [
            'phone' => self::$config['payment_'.$type."_phone"],
            'mchid' => self::$config['payment_'.$type."_mchid"],
            'token' => self::$config['payment_'.$type."_token"],
        ];
        $data = [
            'trade' => $pl->id,
            'price' => $price,
            'phone' => $settings['phone'],
            'mchid' => $settings['mchid'],
            'subject' => self::$config['website_name']."充值".$price."元",
            'body' => self::$config['website_name']."充值".$price."元",
        ];
        $data = DoiAM::sign($data,$settings['token']);
        $ret = DoiAM::post("https://api.daimiyun.cn/v2/".$type."/create",$data);
        $result = json_decode($ret,true);
        if($result and $result['errcode']==0){
            $result['pid']=$pl->id;
            return json_Encode($result);
        }else{
            return json_encode([
                'errcode'=>-1,
                'errmsg' => "接口调用失败!".$ret,
            ]);
        }
        return $result;
    }
    /**
     * 支付宝接口返回
     * @param  Request $req  [description]
     * @param  [type]  $type [description]
     * @return [type]        [description]
     */
    public function return(Request $req, $type){
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
     * @param  Request  $request [description]
     * @param  [type]   $type    [description]
     * @return function          [description]
     */
    public function callback(Request $request, $type){
        $order_data = $_POST;
        $status    = $order_data['status'];         //获取传递过来的交易状态
        $invoiceid = $order_data['out_trade_no'];     //订单号
        $transid   = $order_data['trade_no'];       //转账交易号
        $amount    = $order_data['money'];          //获取递过来的总价格
        if(!DoiAM::checksign($_POST,self::$config['payment_'.$type."_token"])){
            return (json_encode(array('errcode'=>2333)));
        }
        if ($status == 'success') {
            $p=Payment::find($invoiceid);
            if($p->status==1){
                return json_encode(['errcode'=>0]);
            }
            $p->status=1;
            $p->save();
            $user = User::find($p->user_id);
            $user->balance += $p->money;
            $user->save();
            return json_encode(['errcode'=>0]);
        }else{
            return '';
        }
    }
}
class DoiAM{
    public static function sort(&$array){
        ksort($array);
    }
    public static function getsign($array,$key){
        unset($array['sign']);
        self::sort($array);
        $sss=http_build_query($array);
        $sign=hash("sha256",$sss.$key);
        $sign=sha1($sign.hash("sha256",$key));
        return $sign;
    }
    public static function sign($array,$key){
        $array['sign']=self::getSign($array,$key);
        return $array;
    }
    public static function checksign($array,$key){
        $new = $array;
        $new=self::sign($new,$key);
        if(!isset($array['sign'])){
            return false;
        }
        return $array['sign']==$new['sign'];
    }
    public static function post($url, $data = null){
    	$curl = curl_init();
    	curl_setopt($curl, CURLOPT_URL, $url);
    	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    	if (!empty($data)){
    	curl_setopt($curl, CURLOPT_POST, 1);
    	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    	}
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    	$output = curl_exec($curl);
    	curl_close($curl);
    	return $output;
    }
}
