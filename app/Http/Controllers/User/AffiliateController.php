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
	protected static $sysConfig;

	public function __construct() {
		self::$sysConfig = Helpers::sysConfig();
	}

	// 推广返利
	public function referral(): \Illuminate\Http\Response {
		if(ReferralLog::uid()->doesntExist() && Order::uid()->whereStatus(2)->doesntExist()){
			return Response::view('auth.error',
				['message' => '本功能对非付费用户禁用！请 <a class="btn btn-sm btn-danger" href="/">返 回</a>']);
		}
		$view['referral_traffic'] = flowAutoShow(self::$sysConfig['referral_traffic'] * MB);
		$view['referral_percent'] = self::$sysConfig['referral_percent'];
		$view['referral_money'] = self::$sysConfig['referral_money'];
		$view['totalAmount'] = ReferralLog::uid()->sum('ref_amount') / 100;
		$view['canAmount'] = ReferralLog::uid()->whereStatus(0)->sum('ref_amount') / 100;
		$view['link'] = self::$sysConfig['website_url'].'/register?aff='.Auth::id();
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
		if($ref_amount < self::$sysConfig['referral_money']){
			return Response::json([
				'status'  => 'fail',
				'message' => '申请失败：满'.self::$sysConfig['referral_money'].'元才可以提现，继续努力吧'
			]);
		}

		$ref = new ReferralApply();
		$ref->user_id = Auth::id();
		$ref->before = $ref_amount;
		$ref->after = 0;
		$ref->amount = $ref_amount;
		$ref->link_logs = ReferralLog::uid()->whereStatus(0)->pluck('id')->toArray();
		$ref->status = 0;
		if($ref->save()){
			return Response::json(['status' => 'success', 'message' => '申请成功，请等待管理员审核']);
		}

		return Response::json(['status' => 'fail', 'message' => '申请失败，返利单建立失败，请稍后尝试或通知管理员']);
	}
}
