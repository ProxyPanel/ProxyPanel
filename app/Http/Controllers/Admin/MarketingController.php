<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class MarketingController extends Controller
{
    // 邮件群发消息列表
    public function emailList(Request $request)
    {
        $query = Marketing::whereType(1);

        $request->whenFilled('status', function ($value) use ($query) {
            $query->whereStatus($value);
        });

        return view('admin.marketing.emailList', ['emails' => $query->paginate(15)->appends($request->except('page'))]);
    }

    // 消息通道群发列表
    public function pushList(Request $request)
    {
        $query = Marketing::whereType(2);

        $request->whenFilled('status', function ($value) use ($query) {
            $query->whereStatus($value);
        });

        return view('admin.marketing.pushList', ['pushes' => $query->paginate(15)->appends($request->except('page'))]);
    }

    // 添加推送消息
    public function addPushMarketing(Request $request): JsonResponse
    {
        $title = $request->input('title');
        $content = $request->input('content');

        //        if (! sysConfig('is_push_bear')) {
        //            return Response::json(['status' => 'fail', 'message' => '推送失败：请先启用并配置PushBear']);
        //        }
        //
        //        Notification::send(PushBearChannel::class, new Custom($title, $content));

        return Response::json(['status' => 'fail', 'message' => '功能待开发']);
    }
}
