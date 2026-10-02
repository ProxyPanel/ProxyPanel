<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Invite;
use App\Models\Order;
use App\Models\User;
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

        try {
            // 名额用条件递减占住；invite_num 是 unsigned，超发会被静默截成 0
            if (! User::whereKey($user->id)->where('invite_num', '>', 0)->decrement('invite_num')) {
                return response()->json(['status' => 'fail', 'message' => trans('user.invite.generate_failed')]);
            }

            $invite = $user->invites()->create([
                'code' => strtoupper(Str::random(12)), // 简化邀请码生成逻辑
                'dateline' => now()->addDays((int) sysConfig('user_invite_days')),
            ]);

            if ($invite) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.generate')])]);
            }

            $user->increment('invite_num'); // 码没生成就把占掉的名额还回去
        } catch (Exception $e) {
            // 记录异常但不暴露给用户
            Log::error('Failed to generate invite code: '.$e->getMessage());
            $user->increment('invite_num');
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.generate')])]);
    }
}
