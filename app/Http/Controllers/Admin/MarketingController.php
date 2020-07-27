<?php

namespace App\Http\Controllers\Admin;

use App\Components\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Marketing;
use DB;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use RuntimeException;

/**
 * 促销控制器
 *
 * Class MarketingController
 *
 * @package App\Http\Controllers\Controller
 */
class MarketingController extends Controller {
	protected static $systemConfig;

	public function __construct() {
		self::$systemConfig = Helpers::systemConfig();
	}

	// 邮件群发消息列表
	public function emailList(Request $request): \Illuminate\Http\Response {
		$status = $request->input('status');

		$query = Marketing::query()->whereType(1);

		if(isset($status)){
			$query->whereStatus($status);
		}

		$view['list'] = $query->paginate(15)->appends($request->except('page'));

		return Response::view('admin.marketing.emailList', $view);
	}

	// 消息通道群发列表
	public function pushList(Request $request): \Illuminate\Http\Response {
		$status = $request->input('status');

		$query = Marketing::query()->whereType(2);

		if(isset($status)){
			$query->whereStatus($status);
		}

		$view['list'] = $query->paginate(15);

		return Response::view('admin.marketing.pushList', $view);
	}

	// 添加推送消息
	public function addPushMarketing(Request $request): ?JsonResponse {
		$title = $request->input('title');
		$content = $request->input('content');

		if(!self::$systemConfig['is_push_bear']){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '推送失败：请先启用并配置PushBear']);
		}

		DB::beginTransaction();
		try{
			$response = (new Client())->get('https://pushbear.ftqq.com/sub', [
				'query' => [
					'sendkey' => self::$systemConfig['push_bear_send_key'],
					'text'    => $title,
					'desp'    => $content
				]
			]);

			$result = json_decode($response->getBody(), true);
			if($result->code){ // 失败
				$this->addMarketing(2, $title, $content, -1, $result->message);

				throw new RuntimeException($result->message);
			}

			$this->addMarketing(2, $title, $content, 1);

			DB::commit();

			return Response::json(['status' => 'success', 'data' => '', 'message' => '推送成功']);
		}catch(Exception $e){
			Log::info('PushBear消息推送失败：'.$e->getMessage());

			DB::rollBack();

			return Response::json(['status' => 'fail', 'data' => '', 'message' => '推送失败：'.$e->getMessage()]);
		}
	}

	private function addMarketing($type = 1, $title = '', $content = '', $status = 1, $error = '', $receiver = ''
	): bool {
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
