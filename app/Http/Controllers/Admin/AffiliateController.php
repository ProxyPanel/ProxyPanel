<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralApply;
use App\Models\ReferralLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

/**
 * 推广控制器.
 *
 * Class AffiliateController
 */
class AffiliateController extends Controller
{
    // 提现申请列表
    public function index(Request $request)
    {
        $email = $request->input('email');
        $status = $request->input('status');

        $query = ReferralApply::with('user:id,email');
        if (isset($email)) {
            $query->whereHas('user', static function ($q) use ($email) {
                $q->where('email', 'like', '%'.$email.'%');
            });
        }

        if ($status) {
            $query->whereStatus($status);
        }

        $view['applyList'] = $query->latest()->paginate(15)->appends($request->except('page'));

        return view('admin.aff.index', $view);
    }

    // 提现申请详情
    public function detail(Request $request, $id)
    {
        $view['basic'] = ReferralApply::with('user:id,email')->find($id);
        $view['commissions'] = [];
        if ($view['basic'] && $view['basic']->link_logs) {
            $view['commissions'] = ReferralLog::with(['invitee:id,email', 'order.goods:id,name'])
                ->whereIn('id', $view['basic']->link_logs)
                ->paginate(15)
                ->appends($request->except('page'));
        }

        return view('admin.aff.detail', $view);
    }

    // 设置提现申请状态
    public function setStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $status = (int) $request->input('status');

        $ret = ReferralApply::whereId($id)->update(['status' => $status]);
        if ($ret) {
            // 审核申请的时候将关联的
            $referralApply = ReferralApply::findOrFail($id);
            if ($referralApply && $status === 1) {
                ReferralLog::whereIn('id', $referralApply->link_logs)->update(['status' => 1]);
            } elseif ($referralApply && $status === 2) {
                ReferralLog::whereIn('id', $referralApply->link_logs)->update(['status' => 2]);
            }
        }

        return Response::json(['status' => 'success', 'message' => '操作成功']);
    }

    // 用户返利流水记录
    public function rebate(Request $request)
    {
        $invitee_email = $request->input('invitee_email');
        $inviter_email = $request->input('inviter_email');
        $status = $request->input('status');

        $query = ReferralLog::with(['invitee:id,email', 'inviter:id,email'])->orderBy('status')->latest();

        if (isset($invitee_email)) {
            $query->whereHas('invitee', static function ($q) use ($invitee_email) {
                $q->where('email', 'like', '%'.$invitee_email.'%');
            });
        }

        if (isset($inviter_email)) {
            $query->whereHas('inviter', static function ($q) use ($inviter_email) {
                $q->where('email', 'like', '%'.$inviter_email.'%');
            });
        }

        if (isset($status)) {
            $query->whereStatus($status);
        }

        $view['list'] = $query->paginate(15)->appends($request->except('page'));

        return view('admin.aff.rebate', $view);
    }
}
