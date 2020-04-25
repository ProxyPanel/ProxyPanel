<?php

namespace App\Http\Controllers\Admin;

use App\Components\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Models\Device;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserSubscribeLog;
use Illuminate\Http\Request;
use Response;

/**
 * 订阅控制器
 *
 * Class SubscribeController
 *
 * @package App\Http\Controllers\Controller
 */
class SubscribeController extends Controller
{
	protected static $systemConfig;

	function __construct()
	{
		self::$systemConfig = Helpers::systemConfig();
	}

	// 订阅码列表
	public function subscribeList(Request $request)
	{
		$user_id = $request->input('user_id');
		$email = $request->input('email');
		$status = $request->input('status');

		$query = UserSubscribe::with(['user:id,email']);

		if(isset($user_id)){
			$query->whereUserId($user_id);
		}

		if(isset($email)){
			$query->whereHas('user', function($q) use ($email){
				$q->where('email', 'like', '%'.$email.'%');
			});
		}

		if(isset($status)){
			$query->whereStatus($status);
		}

		$view['subscribeList'] = $query->orderBy('id', 'desc')->paginate(20)->appends($request->except('page'));

		return Response::view('admin.subscribe.subscribeList', $view);
	}

	//订阅记录
	public function subscribeLog(Request $request)
	{
		$id = $request->input('id');
		$query = UserSubscribeLog::with('user:email');

		if(isset($id)){
			$query->whereSid($id);
		}

		$view['subscribeLog'] = $query->orderBy('id', 'desc')->paginate(20)->appends($request->except('page'));

		return Response::view('admin.subscribe.subscribeLog', $view);
	}

	// 订阅设备列表
	public function deviceList(Request $request)
	{
		$type = $request->input('type');
		$platform = $request->input('platform');
		$name = $request->input('name');
		$status = $request->input('status');

		$query = Device::query();

		if(isset($type)){
			$query->whereType($type);
		}

		if(isset($platform)){
			$query->wherePlatform($platform);
		}

		if(isset($name)){
			$query->where('name', 'like', '%'.$name.'%');
		}

		if(isset($status)){
			$query->whereStatus($status);
		}

		$view['deviceList'] = $query->paginate(20)->appends($request->except('page'));

		return Response::view('admin.subscribe.deviceList', $view);
	}

	// 设置用户的订阅的状态
	public function setSubscribeStatus(Request $request)
	{
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

	// 设置设备是否允许订阅的状态
	public function setDeviceStatus(Request $request)
	{
		$id = $request->input('id');
		$status = $request->input('status', 0);

		if(empty($id)){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作异常']);
		}

		Device::query()->whereId($id)->update(['status' => $status]);

		return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
	}
}
