<?php

namespace App\Http\Controllers\User;

use App\Components\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReferralApply;
use App\Models\ReferralLog;
use App\Models\User;
use Auth;
use Response;

class AffiliateController extends Controller {
	protected static $systemConfig;

	function __construct() {
		self::$systemConfig = Helpers::systemConfig();
	}

	// 推广返利
	public function referral() {
		if(Order::uid()->whereStatus(2)->doesntExist() && ReferralLog::uid()->doesntExist()){
			return Response::view('auth.error',
				['message' => '本功能对非付费用户禁用！请 <a class="btn btn-sm btn-danger" href="/">返 回</a>']);
		}
		$view['referral_traffic'] = flowAutoShow(self::$systemConfig['referral_traffic'] * 1048576);
		$view['referral_percent'] = self::$systemConfig['referral_percent'];
		$view['referral_money'] = self::$systemConfig['referral_money'];
		$view['totalAmount'] = ReferralLog::uid()->sum('ref_amount') / 100;
		$view['canAmount'] = ReferralLog::uid()->whereStatus(0)->sum('ref_amount') / 100;
		$view['link'] = self::$systemConfig['website_url'].'/register?aff='.Auth::id();
		$view['referralLogList'] = ReferralLog::uid()->with('user')->orderByDesc('id')->paginate(10, ['*'], 'log_page');
		$view['referralApplyList'] = ReferralApply::uid()
		                                          ->with('user')
		                                          ->orderByDesc('id')
		                                          ->paginate(10, ['*'], 'apply_page');
		$view['referralUserList'] = User::query()
		                                ->select(['email', 'created_at'])
		                                ->whereReferralUid(Auth::id())
		                                ->orderByDesc('id')
		                                ->paginate(10, ['*'], 'user_page');

		return Response::view('user.referral', $view);
	}

	// 申请提现
	public function extractMoney() {
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

		// 取出本次申请关联返利日志ID
		$link_logs = '';
		$referralLog = ReferralLog::uid()->whereStatus(0)->get();
		foreach($referralLog as $log){
			$link_logs .= $log->id.',';
		}
		$link_logs = rtrim($link_logs, ',');

		$obj = new ReferralApply();
		$obj->user_id = Auth::id();
		$obj->before = $ref_amount;
		$obj->after = 0;
		$obj->amount = $ref_amount;
		$obj->link_logs = $link_logs;
		$obj->status = 0;
		$obj->save();

		return Response::json(['status' => 'success', 'message' => '申请成功，请等待管理员审核']);
	}
}
