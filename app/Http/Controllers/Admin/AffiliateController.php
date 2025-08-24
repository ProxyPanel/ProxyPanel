<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralApply;
use App\Models\ReferralLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    public function index(Request $request): View
    { // 提现申请列表
        $query = ReferralApply::with('user:id,username');

        $request->whenFilled('username', function ($username) use ($query) {
            $query->whereHas('user', function ($query) use ($username) {
                $query->where('username', 'like', "%$username%");
            });
        });

        $request->whenFilled('status', function ($status) use ($query) {
            $query->whereStatus($status);
        });

        return view('admin.aff.index', ['applyList' => $query->latest()->paginate(15)->appends($request->except('page'))]);
    }

    public function detail(Request $request, ReferralApply $aff): View
    { // 提现申请详情
        return view('admin.aff.detail', [
            'referral' => $aff->load('user:id,username'),
            'commissions' => $aff->referral_logs()->with(['invitee:id,username', 'order.goods:id,name'])->paginate()->appends($request->except('page')),
        ]);
    }

    public function setStatus(Request $request, ReferralApply $aff): JsonResponse
    { // 设置提现申请状态
        $status = (int) $request->input('status');

        if ($aff->update(['status' => $status])) {
            // 将关联的返现单更新状态
            if ($status === 1 || $status === 2) {
                $aff->referral_logs()->update(['status' => $status]);
            }

            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.action')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.action')])]);
    }

    public function rebate(Request $request): View
    { // 用户返利流水记录
        $query = ReferralLog::with(['invitee:id,username', 'inviter:id,username'])->orderBy('status')->latest();

        $request->whenFilled('invitee_username', function ($username) use ($query) {
            $query->whereHas('invitee', function ($query) use ($username) {
                $query->where('username', 'like', "%$username%");
            });
        });

        $request->whenFilled('inviter_username', function ($username) use ($query) {
            $query->whereHas('inviter', function ($query) use ($username) {
                $query->where('username', 'like', "%$username%");
            });
        });

        $request->whenFilled('status', function ($status) use ($query) {
            $query->whereStatus($status);
        });

        return view('admin.aff.rebate', ['referralLogs' => $query->paginate(15)->appends($request->except('page'))]);
    }
}
