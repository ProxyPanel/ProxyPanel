<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Invite;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Str;

class InviteController extends Controller
{
    public function index(): View
    { // 邀请页面
        if (Order::uid()->active()->where('origin_amount', '>', 0)->doesntExist()) {
            return view('auth.error', ['message' => trans('user.purchase.required').' <a class="btn btn-sm btn-danger" href="/">'.trans('common.back').'</a>'], 402);
        }

        return view('user.invite', [
            'num' => auth()->user()->invite_num, // 还可以生成的邀请码数量
            'inviteList' => Invite::uid()->with('invitee')->paginate(10), // 邀请码列表
            'referral_traffic' => formatBytes(sysConfig('referral_traffic'), 'MiB'),
            'referral_percent' => sysConfig('referral_percent'),
        ]);
    }

    public function store(): JsonResponse
    { // 生成邀请码
        $user = auth()->user();
        if ($user->invite_num <= 0) {
            return response()->json(['status' => 'fail', 'message' => trans('user.invite.generate_failed')]);
        }
        $invite = $user->invites()->create([
            'code' => strtoupper(mb_substr(md5(microtime().Str::random()), 8, 12)),
            'dateline' => date('Y-m-d H:i:s', strtotime(sysConfig('user_invite_days').' days')),
        ]);
        if ($invite) {
            $user->decrement('invite_num');

            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.generate')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.generate')])]);
    }
}
