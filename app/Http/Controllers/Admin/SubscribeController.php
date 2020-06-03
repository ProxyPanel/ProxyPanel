<?php

namespace App\Http\Controllers\Admin;

use App\Components\Helpers;
use App\Http\Controllers\Controller;
use App\Models\UserSubscribe;
use App\Models\UserSubscribeLog;
use Illuminate\Http\Request;
use Response;

/**
 * 订阅控制器
 *
 * Class SubscribeController
 *
 * @package App\Http\Controllers\Controller
 */
class SubscribeController extends Controller {
	protected static $systemConfig;

	function __construct() {
		self::$systemConfig = Helpers::systemConfig();
	}

	// 订阅码列表
	public function subscribeList(Request $request) {
		$user_id = $request->input('user_id');
		$email = $request->input('email');
		$status = $request->input('status');

		$query = UserSubscribe::with(['user:id,email']);

		if(isset($user_id)){
			$query->whereUserId($user_id);
		}

		if(isset($email)){
			$query->whereHas('user', function($q) use ($email) {
				$q->where('email', 'like', '%'.$email.'%');
			});
		}

		if(isset($status)){
			$query->whereStatus($status);
		}

		$view['subscribeList'] = $query->orderByDesc('id')->paginate(20)->appends($request->except('page'));

		return Response::view('admin.subscribe.subscribeList', $view);
	}

	//订阅记录
	public function subscribeLog(Request $request) {
		$id = $request->input('id');
		$query = UserSubscribeLog::with('user:email');

		if(isset($id)){
			$query->whereSid($id);
		}

		$view['subscribeLog'] = $query->orderByDesc('id')->paginate(20)->appends($request->except('page'));

		return Response::view('admin.subscribe.subscribeLog', $view);
	}

	// 设置用户的订阅的状态
	public function setSubscribeStatus(Request $request) {
		$id = $request->input('id');
		$status = $request->input('status', 0);

		if(empty($id)){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作异常']);
		}

		if($status){
			UserSubscribe::query()->whereId($id)->update(['status' => 1, 'ban_time' => 0, 'ban_desc' => '']);
		}else{
			UserSubscribe::query()->whereId($id)->update(['status' => 0, 'ban_time' => time(), 'ban_desc' => '后台手动封禁']);
		}

		return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
	}
}
