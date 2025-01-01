<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReferralApply;
use App\Models\ReferralLog;
use App\Services\UserService;
use App\Utils\Helpers;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class AffiliateController extends Controller
{
    // 推广返利
    public function index(): View
    {
        if (ReferralLog::uid()->doesntExist() && Order::uid()->whereStatus(2)->doesntExist()) {
            return view('auth.error', ['message' => trans('user.purchase.required').'<a class="btn btn-sm btn-danger" href="/">'.trans('common.back').'</a>'], 402);
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
            'referralUserList' => auth()->user()->invitees()->select(['username', 'created_at'])->latest()->paginate(10, ['*'], 'user_page'),
        ]);
    }

    // 申请提现
    public function withdraw(): JsonResponse
    {
        // 判断账户是否过期
        if (auth()->user()->expiration_date < date('Y-m-d')) {
            return response()->json(['status' => 'fail', 'title' => trans('common.failed_item', ['attribute' => trans('common.request')]), 'message' => trans('user.referral.msg.account')]);
        }

        // 判断是否已存在申请
        if (ReferralApply::uid()->whereIn('status', [0, 1])->first()) {
            return response()->json(['status' => 'fail', 'title' => trans('common.failed_item', ['attribute' => trans('common.request')]), 'message' => trans('user.referral.msg.applied')]);
        }

        // 校验可以提现金额是否超过系统设置的阀值
        $referrals = ReferralLog::uid()->whereStatus(0)->get();
        $commission = $referrals->sum('commission');
        if ($commission < sysConfig('referral_money')) {
            return response()->json([
                'status' => 'fail', 'title' => trans('common.failed_item', ['attribute' => trans('common.request')]), 'message' => trans('user.referral.msg.unfulfilled', ['amount' => Helpers::getPriceTag(sysConfig('referral_money'))]),
            ]);
        }

        $ref = new ReferralApply;
        $ref->user_id = auth()->id();
        $ref->before = $commission;
        $ref->amount = $commission;
        $ref->link_logs = $referrals->pluck('id')->toArray();
        if ($ref->save()) {
            return response()->json(['status' => 'success', 'title' => trans('common.success_item', ['attribute' => trans('common.request')]), 'message' => trans('user.referral.msg.wait')]);
        }

        return response()->json(['status' => 'fail', 'title' => trans('common.failed_item', ['attribute' => trans('common.request')]), 'message' => trans('user.referral.msg.error')]);
    }
}
