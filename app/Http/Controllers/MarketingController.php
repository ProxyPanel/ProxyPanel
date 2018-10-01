<?php
namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Http\Models\Marketing;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Response;
use Log;
use DB;

class MarketingController extends Controller
{
    protected static $systemConfig;

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
    }

    // 邮件群发消息列表
    public function emailList(Request $request)
    {
        $view['list'] = Marketing::query()->where('type', 1)->paginate(15);

        return Response::view('marketing.emailList', $view);
    }

    // 消息通道群发列表
    public function pushList(Request $request)
    {
        $status = $request->get('status');

        $query = Marketing::query()->where('type', 2);

        if ($status != '') {
            $query->where('status', $status);
        }

        $view['list'] = $query->paginate(15);

        return Response::view('marketing.pushList', $view);
    }

    // 添加推送消息
    public function addPushMarketing(Request $request)
    {
        $title = trim($request->get('title'));
        $content = $request->get('content');

        if (!self::$systemConfig['is_push_bear']) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '推送失败：请先启用并配置PushBear']);
        }

        DB::beginTransaction();
        try {
            $client = new Client();
            $response = $client->request('GET', 'https://pushbear.ftqq.com/sub', [
                'query' => [
                    'sendkey' => self::$systemConfig['push_bear_send_key'],
                    'text'    => $title,
                    'desp'    => $content
                ]
            ]);

            $result = json_decode($response->getBody());
            if ($result->code) { // 失败
                $this->addMarketing(2, $title, $content, -1, $result->message);

                throw new \Exception($result->message);
            }

            $this->addMarketing(2, $title, $content, 1);

            DB::commit();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '推送成功']);
        } catch (\Exception $e) {
            Log::info('PushBear消息推送失败：' . $e->getMessage());

            DB::rollBack();

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '推送失败：' . $e->getMessage()]);
        }
    }

    private function addMarketing($type = 1, $title = '', $content = '', $status = 1, $error = '', $receiver = '')
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