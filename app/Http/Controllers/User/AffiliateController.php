<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReferralApply;
use App\Models\ReferralLog;
use App\Services\UserService;
use App\Utils\Helpers;
use Auth;
use Illuminate\Http\JsonResponse;
use Response;

class AffiliateController extends Controller
{
    // 推广返利
    public function referral()
    {
        if (ReferralLog::uid()->doesntExist() && Order::uid()->whereStatus(2)->doesntExist()) {
            return Response::view('auth.error', ['message' => trans('user.purchase_required').'<a class="btn btn-sm btn-danger" href="/">'.trans('common.back').'</a>'], 402);
        }

        return view('user.referral', [
            'referral_traffic' => formatBytes(sysConfig('referral_traffic'), 'MiB'),
            'referral_percent' => sysConfig('referral_percent'),
            'referral_money' => Helpers::getPriceTag(sysConfig('referral_money')),
            'totalAmount' => ReferralLog::uid()->sum('commission') / 100,
            'canAmount' => Helpers::getPriceTag(ReferralLog::uid()->whereStatus(0)->sum('commission') / 100),
            'aff_link' => (new UserService)->inviteURI(),
            'referralLogList' => ReferralLog::uid()->with('invitee:id,username')->latest()->paginate(10, ['*'], 'log_page'),
            'referralApplyList' => ReferralApply::uid()->latest()->paginate(10, ['*'], 'apply_page'),
            'referralUserList' => Auth::getUser()->invitees()->select(['username', 'created_at'])->latest()->paginate(10, ['*'], 'user_page'),
        ]);
    }

    // 申请提现
    public function extractMoney(): JsonResponse
    {
        // 判断账户是否过期
        if (Auth::getUser()->expiration_date < date('Y-m-d')) {
            return Response::json(['status' => 'fail', 'title' => trans('user.referral.failed'), 'message' => trans('user.referral.msg.account')]);
        }

        // 判断是否已存在申请
        if (ReferralApply::uid()->whereIn('status', [0, 1])->first()) {
            return Response::json(['status' => 'fail', 'title' => trans('user.referral.failed'), 'message' => trans('user.referral.msg.applied')]);
        }

        // 校验可以提现金额是否超过系统设置的阀值
        $commission = ReferralLog::uid()->whereStatus(0)->sum('commission');
        $commission /= 100;
        if ($commission < sysConfig('referral_money')) {
            return Response::json([
                'status' => 'fail', 'title' => trans('user.referral.failed'), 'message' => trans('user.referral.msg.unfulfilled', ['amount' => Helpers::getPriceTag(sysConfig('referral_money'))]),
            ]);
        }

        $ref = new ReferralApply();
        $ref->user_id = Auth::id();
        $ref->before = $commission;
        $ref->amount = $commission;
        $ref->link_logs = ReferralLog::uid()->whereStatus(0)->pluck('id')->toArray();
        if ($ref->save()) {
            return Response::json(['status' => 'success', 'title' => trans('user.referral.success'), 'message' => trans('user.referral.msg.wait')]);
        }

        return Response::json(['status' => 'fail', 'title' => trans('user.referral.failed'), 'message' => trans('user.referral.msg.error')]);
    }
}
