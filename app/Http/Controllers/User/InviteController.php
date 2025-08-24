<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Invite;
use App\Models\Order;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Log;
use Str;

class InviteController extends Controller
{
    public function index(): Response|View
    { // 邀请页面
        if (Order::uid()->active()->where('origin_amount', '>', 0)->doesntExist()) {
            return response()->view('auth.error', ['message' => trans('user.purchase.required').' <a class="btn btn-sm btn-danger" href="/">'.trans('common.back').'</a>'], 402);
        }

        return view('user.invite', [
            'num' => auth()->user()->invite_num, // 还可以生成的邀请码数量
            'inviteList' => Invite::uid()->with('invitee')->paginate(10), // 邀请码列表
            'referral_reward_mode' => sysConfig('referral_reward_type', 0),
            'referral_traffic' => formatBytes(sysConfig('referral_traffic'), 'MiB'),
            'referral_percent' => sysConfig('referral_percent') * 100,
        ]);
    }

    public function store(): JsonResponse
    { // 生成邀请码
        $user = auth()->user();

        // 检查用户是否还有邀请码配额
        if ($user->invite_num <= 0) {
            return response()->json(['status' => 'fail', 'message' => trans('user.invite.generate_failed')]);
        }

        try {
            $invite = $user->invites()->create([
                'code' => strtoupper(Str::random(12)), // 简化邀请码生成逻辑
                'dateline' => now()->addDays((int) sysConfig('user_invite_days')),
            ]);

            if ($invite) {
                $user->decrement('invite_num');

                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.generate')])]);
            }
        } catch (Exception $e) {
            // 记录异常但不暴露给用户
            Log::error('Failed to generate invite code: '.$e->getMessage());
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.generate')])]);
    }
}
