<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Marketing;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserHourlyDataFlow;
use App\Notifications\Custom;
use Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Notification;
use Response;
use Validator;

class MarketingController extends Controller
{
    // 群发消息列表
    public function index(Request $request)
    {
        $query = Marketing::query();

        $request->whenFilled('status', function ($value) use ($query) {
            $query->whereStatus($value);
        });

        return view('admin.article.marketing', [
            'marketingMessages' => $query->latest()->paginate(15)->appends($request->except('page')),
            'userGroups' => UserGroup::all()->pluck('name', 'id')->toArray(),
            'levels' => Level::all()->pluck('name', 'level')->toArray(),
        ]);
    }

    // 推送消息
    public function create(string $type, Request $request): JsonResponse
    {
        if ($request->isMethod('GET')) {
            return Response::json(['status' => 'success', 'count' => $this->userStat($request)]);
        }

        $validator = Validator::make($request->all(), ['title' => 'required|string', 'content' => 'required|string']);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->getMessageBag()->first()]);
        }

        $title = $request->input('title');
        $content = $request->input('content');

        if ($type === 'push') {
            //            if (! sysConfig('is_push_bear')) {
            //                return Response::json(['status' => 'fail', 'message' => '推送失败：请先启用并配置PushBear']);
            //            }
            //
            //            Notification::send(PushBearChannel::class, new Custom($title, $content));
            //            return Response::json(['status' => 'success', 'message' => '发送完成']);
            return Response::json(['status' => 'fail', 'message' => trans('common.developing')]);
        }

        if ($type === 'email') {
            $users = $this->userStat($request);
            if ($users->isNotEmpty()) {
                Notification::send($users, new Custom($title, $content, ['mail']));
                Helpers::addMarketing($users->pluck('id')->toJson(), '1', $title, $content);

                return Response::json(['status' => 'success', 'message' => trans('admin.marketing.processed')]);
            }

            return Response::json(['status' => 'fail', 'message' => trans('admin.marketing.targeted_users_not_found')]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('admin.marketing.unknown_sending_type')]);
    }

    private function userStat(Request $request)
    {
        $users = User::query();

        foreach (['id', 'username', 'status', 'enable', 'user_group_id', 'level'] as $field) {
            $request->whenFilled($field, function ($value) use ($users, $field) {
                $users->whereIn($field, array_map('trim', explode(',', $value)));
            });
        }

        // 流量使用超过N%
        $request->whenFilled('traffic', function (int $value) use ($users) {
            $users->whereRaw('(u + d)/transfer_enable >= ?', [$value / 100]);
        });

        // 过期日期
        $request->whenFilled('expire_start', function ($value) use ($users) {
            $users->where('expired_at', '>=', $value);
        });
        $request->whenFilled('expire_end', function ($value) use ($users) {
            $users->where('expired_at', '<=', $value);
        });

        // 最近N分钟活跃过
        $request->whenFilled('lastAlive', function ($value) use ($users) {
            $users->where('t', '>=', now()->subMinutes($value)->timestamp);
        });

        $paidOrderCondition = function ($query) {
            $query->whereStatus(2)->whereNotNull('goods_id')->where('amount', '>', 0);
        };

        // 付费服务中
        $request->whenFilled('paying', function () use ($users) {
            $users->whereHas('orders', function ($query) {
                $query->whereStatus(2)->whereNotNull('goods_id')->whereIsExpire(0)->where('amount', '>', 0);
            });
        });

        // 曾付费但当前无服务
        $request->whenFilled('notPaying', function () use ($users, $paidOrderCondition) {
            $users->whereHas('orders', $paidOrderCondition)->whereDoesntHave('orders', function ($query) use ($paidOrderCondition) {
                $query->where($paidOrderCondition)->whereIsExpire(0);
            });
        });

        // 付费购买过
        $request->whenFilled('paid', function () use ($users, $paidOrderCondition) {
            $users->whereHas('orders', $paidOrderCondition);
        });

        // 从未付费购买过
        $request->whenFilled('neverPay', function () use ($users, $paidOrderCondition) {
            $users->whereDoesntHave('orders', $paidOrderCondition);
        });

        // 1小时内流量异常用户
        $request->whenFilled('flowAbnormal', function () use ($users) {
            $users->whereIn('id', (new UserHourlyDataFlow)->trafficAbnormal());
        });

        return $request->isMethod('POST') ? $users->get() : $users->count();
    }
}
