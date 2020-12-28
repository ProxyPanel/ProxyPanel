<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketing;
use DB;
use Exception;
use Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use RuntimeException;

/**
 * 促销控制器.
 *
 * Class MarketingController
 */
class MarketingController extends Controller
{
    // 邮件群发消息列表
    public function emailList(Request $request)
    {
        $status = $request->input('status');

        $query = Marketing::whereType(1);

        if (isset($status)) {
            $query->whereStatus($status);
        }

        return view('admin.marketing.emailList', ['emails' => $query->paginate(15)->appends($request->except('page'))]);
    }

    // 消息通道群发列表
    public function pushList(Request $request)
    {
        $status = $request->input('status');

        $query = Marketing::whereType(2);

        if (isset($status)) {
            $query->whereStatus($status);
        }

        return view('admin.marketing.pushList', ['pushes' => $query->paginate(15)->appends($request->except('page'))]);
    }

    // 添加推送消息
    public function addPushMarketing(Request $request): ?JsonResponse
    {
        $title = $request->input('title');
        $content = $request->input('content');

        if (! sysConfig('is_push_bear')) {
            return Response::json(['status' => 'fail', 'message' => '推送失败：请先启用并配置PushBear']);
        }

        try {
            DB::beginTransaction();

            $response = Http::timeout(15)->get('https://pushbear.ftqq.com/sub', [
                'sendkey' => sysConfig('push_bear_send_key'),
                'text' => $title,
                'desp' => $content,
            ]);

            $message = $response->json();
            if (! $message || ! $message['code'] === 0 || $response->failed()) { // 失败
                $this->addMarketing(2, $title, $content, -1, $message['message']);

                throw new RuntimeException($message['message']);
            }

            $this->addMarketing(2, $title, $content, 1);

            DB::commit();

            return Response::json(['status' => 'success', 'message' => '推送成功']);
        } catch (Exception $e) {
            Log::error('PushBear消息推送失败：'.$e->getMessage());

            DB::rollBack();

            return Response::json(['status' => 'fail', 'message' => '推送失败：'.$e->getMessage()]);
        }
    }

    private function addMarketing($type = 1, $title = '', $content = '', $status = 1, $error = '', $receiver = ''): bool
    {
        $marketing = new Marketing();
        $marketing->type = $type;
        $marketing->receiver = $receiver;
        $marketing->title = $title;
        $marketing->content = $content;
        $marketing->error = $error;
        $marketing->status = $status;

        return $marketing->save();
    }
}
