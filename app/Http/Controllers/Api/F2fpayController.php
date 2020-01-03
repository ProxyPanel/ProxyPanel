<?php

namespace App\Http\Controllers\Api;

use App\Components\Callback;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;
use Payment\Client\Query;
use Payment\Common\PayException;

/**
 * Class F2fpayController
 *
 * @author  heron
 *
 * @package App\Http\Controllers\Api
 */
class F2fpayController extends Controller
{
	use Callback;
	// 接收GET请求
	public function index(Request $request)
	{
		Log::info("【支付宝当面付】回调接口[GET]：".var_export($request->all(), TRUE).'['.getClientIp().']');
		exit("【支付宝当面付】接口正常");
	}

	// 接收POST请求
	public function store(Request $request)
	{
		Log::info("【支付宝当面付】回调接口[POST]：".var_export($request->all(), TRUE));

		$result = "fail";

		try{
			$verify_result = Query::run('ali_charge', [
				'use_sandbox'     => FALSE,
				"partner"         => self::$systemConfig['f2fpay_app_id'],
				'app_id'          => self::$systemConfig['f2fpay_app_id'],
				'sign_type'       => 'RSA2',
				'ali_public_key'  => self::$systemConfig['f2fpay_public_key'],
				'rsa_private_key' => self::$systemConfig['f2fpay_private_key'],
				'notify_url'      => self::$systemConfig['website_url']."/api/f2fpay", // 异步回调接口
				'return_url'      => self::$systemConfig['website_url'],
				'return_raw'      => FALSE
			], [
				'out_trade_no' => $request->input('out_trade_no'),
				'trade_no'     => $request->input('trade_no'),
			]);

			Log::info("【支付宝当面付】回调验证查询：".var_export($verify_result, TRUE));
		} catch(PayException $e){
			Log::info("【支付宝当面付】回调验证查询出错：".var_export($e->errorMessage(), TRUE));
			exit($result);
		}

		if($verify_result['is_success'] == 'T'){ // 验证成功
			$result = "success";
			if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS'){
				// 商户订单号
				$data = [];
				$data['out_trade_no'] = $request->input('out_trade_no');
				// 支付宝交易号
				$data['trade_no'] = $request->input('trade_no');
				// 交易状态
				$data['trade_status'] = $request->input('trade_status');
				// 交易金额(这里是按照结算货币汇率的金额，和rmb_fee不相等)
				$data['total_amount'] = $request->input('total_amount');

				$this->tradePaid($data, 5);
			}else{
				Log::info('支付宝当面付-POST:交易失败['.getClientIp().']');
			}
		}else{
			Log::info('支付宝当面付-POST:验证失败['.getClientIp().']');
		}

		// 返回验证结果
		exit($result);
	}
}
