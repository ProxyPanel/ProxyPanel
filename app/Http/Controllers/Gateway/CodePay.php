<?php

namespace App\Http\Controllers\Gateway;

use App\Components\Curl;
use App\Http\Models\Payment;
use Auth;
use Response;

class CodePay extends AbstractPayment
{
	public function purchase($request)
	{
		$payment = new Payment();
		$payment->sn = self::generateGuid();
		$payment->user_id = Auth::user()->id;
		$payment->oid = $request->input('oid');
		$payment->amount = $request->input('amount');
		$payment->save();

		$data = [
			'id'         => parent::$systemConfig['codepay_id'],
			'pay_id'     => $payment->sn,
			'type'       => $request->input('type'),//1支付宝支付 2QQ钱包 3微信支付
			'price'      => $payment->amount,
			'page'       => 4,
			'outTime'    => 900,
			'param'      => '',
			'notify_url' => (parent::$systemConfig['website_callback_url']? : parent::$systemConfig['website_url']).'/payment/notify',//通知地址
			'return_url' => parent::$systemConfig['website_url'].'/payment/'.$payment->sn,//跳转地址
		];

		ksort($data); //重新排序$data数组
		reset($data); //内部指针指向数组中的第一个元素

		$sign = ''; //初始化需要签名的字符为空
		$urls = ''; //初始化URL参数为空

		foreach($data as $key => $val){ //遍历需要传递的参数
			if($val == '' || $key == 'sign'){
				continue;
			} //跳过这些不参数签名
			if($sign != ''){ //后面追加&拼接URL
				$sign .= '&';
				$urls .= '&';
			}
			$sign .= "$key=$val"; //拼接为url参数形式
			$urls .= "$key=".urlencode($val); //拼接为url参数形式并URL编码参数值
		}
		$query = $urls.'&sign='.md5($sign.parent::$systemConfig['codepay_key']); //创建订单所需的参数
		$url = parent::$systemConfig['codepay_url'].$query; //支付页面
		$result = json_decode(Curl::send($url));

		$payment->qr_code = $result->qrcode;// 获取收款二维码内容
		$payment->save();

		return Response::json(['status' => 'success', 'data' => $payment->sn, 'message' => '创建订单成功!']);
	}

	public function notify($request)
	{
		//以下五行无需更改
		ksort($_POST); //排序post参数
		reset($_POST); //内部指针指向数组中的第一个元素
		$sign = '';//初始化
		foreach($_POST as $key => $val){ //遍历POST参数
			if($val == '' || $key == 'sign'){
				continue;
			} //跳过这些不签名
			if($sign){
				$sign .= '&';
			} //第一个字符串签名不加& 其他加&连接起来参数
			$sign .= "$key=$val"; //拼接为url参数形式
		}
		if(!$_POST['pay_no'] || md5($sign.parent::$systemConfig['codepay_key']) != $_POST['sign']){ //不合法的数据
			exit('fail'); //返回失败，等待下次回调
		}

		$pay_id = $_POST['pay_id']; //需要充值的ID 或订单号 或用户名

		$this->postPayment($pay_id, '码支付');

		exit('success'); //返回成功 不要删除哦
	}

	public function getReturnHTML($request)
	{
		// TODO: Implement getReturnHTML() method.
	}

	public function getPurchaseHTML()
	{
		// TODO: Implement getReturnHTML() method.
	}
}