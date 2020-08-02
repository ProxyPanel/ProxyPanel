<?php

namespace App\Http\Controllers\User;

use App\Components\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReferralApply;
use App\Models\ReferralLog;
use App\Models\User;
use Auth;
use Illuminate\Http\JsonResponse;
use Response;

class AffiliateController extends Controller {
	protected static $systemConfig;

	public function __construct() {
		self::$systemConfig = Helpers::systemConfig();
	}

	// 推广返利
	public function referral(): \Illuminate\Http\Response {
		if(ReferralLog::uid()->doesntExist() && Order::uid()->whereStatus(2)->doesntExist()){
			return Response::view('auth.error',
				['message' => '本功能对非付费用户禁用！请 <a class="btn btn-sm btn-danger" href="/">返 回</a>']);
		}
		$view['referral_traffic'] = flowAutoShow(self::$systemConfig['referral_traffic'] * MB);
		$view['referral_percent'] = self::$systemConfig['referral_percent'];
		$view['referral_money'] = self::$systemConfig['referral_money'];
		$view['totalAmount'] = ReferralLog::uid()->sum('ref_amount') / 100;
		$view['canAmount'] = ReferralLog::uid()->whereStatus(0)->sum('ref_amount') / 100;
		$view['link'] = self::$systemConfig['website_url'].'/register?aff='.Auth::id();
		$view['referralLogList'] = ReferralLog::uid()->with('user')->latest()->paginate(10, ['*'], 'log_page');
		$view['referralApplyList'] = ReferralApply::uid()->with('user')->latest()->paginate(10, ['*'], 'apply_page');
		$view['referralUserList'] = User::query()
		                                ->select(['email', 'created_at'])
		                                ->whereReferralUid(Auth::id())
		                                ->latest()
		                                ->paginate(10, ['*'], 'user_page');

		return Response::view('user.referral', $view);
	}

	// 申请提现
	public function extractMoney(): JsonResponse {
		// 判断账户是否过期
		if(Auth::getUser()->expire_time < date('Y-m-d')){
			return Response::json(['status' => 'fail', 'message' => '申请失败：账号已过期，请先购买服务吧']);
		}

		// 判断是否已存在申请
		$referralApply = ReferralApply::uid()->whereIn('status', [0, 1])->first();
		if($referralApply){
			return Response::json(['status' => 'fail', 'message' => '申请失败：已存在申请，请等待之前的申请处理完']);
		}

		// 校验可以提现金额是否超过系统设置的阀值
		$ref_amount = ReferralLog::uid()->whereStatus(0)->sum('ref_amount');
		$ref_amount /= 100;
		if($ref_amount < self::$systemConfig['referral_money']){
			return Response::json([
				'status'  => 'fail',
				'message' => '申请失败：满'.self::$systemConfig['referral_money'].'元才可以提现，继续努力吧'
			]);
		}

		$ret = ReferralApply::query()->insert([
			'user_id'   => Auth::id(),
			'before'    => $ref_amount,
			'after'     => 0,
			'amount'    => $ref_amount,
			'link_logs' => implode(',', ReferralLog::uid()->whereStatus(0)->pluck('id')->toArray()),// 取出本次申请关联返利日志ID
			'status'    => 0
		]);
		if($ret){
			return Response::json(['status' => 'success', 'message' => '申请成功，请等待管理员审核']);
		}

		return Response::json(['status' => 'fail', 'message' => '申请失败，返利单建立失败，请稍后尝试或通知管理员']);
	}
}
