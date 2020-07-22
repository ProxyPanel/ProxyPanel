<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\IPIP;
use App\Components\PushNotification;
use App\Components\QQWry;
use App\Models\Article;
use App\Models\Config;
use App\Models\Country;
use App\Models\Invite;
use App\Models\Label;
use App\Models\Level;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\ReferralApply;
use App\Models\ReferralLog;
use App\Models\SsConfig;
use App\Models\SsNode;
use App\Models\SsNodeIp;
use App\Models\SsNodeLabel;
use App\Models\SsNodeTrafficDaily;
use App\Models\User;
use App\Models\UserBanLog;
use App\Models\UserCreditLog;
use App\Models\UserLoginLog;
use App\Models\UserSubscribe;
use App\Models\UserTrafficDaily;
use App\Models\UserTrafficHourly;
use App\Models\UserTrafficLog;
use App\Models\UserTrafficModifyLog;
use Auth;
use DB;
use Exception;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Redirect;
use Response;
use Session;
use Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Validator;

/**
 * 管理员控制器
 *
 * Class AdminController
 *
 * @package App\Http\Controllers
 */
class AdminController extends Controller {
	protected static $systemConfig;

	public function __construct() {
		self::$systemConfig = Helpers::systemConfig();
	}

	public function index(): \Illuminate\Http\Response {
		$past = strtotime(date('Y-m-d', strtotime("-".self::$systemConfig['expire_days']." days")));

		$view['expireDays'] = self::$systemConfig['expire_days'];
		$view['totalUserCount'] = User::query()->count(); // 总用户数
		$view['enableUserCount'] = User::query()->whereEnable(1)->count(); // 有效用户数
		$view['activeUserCount'] = User::query()->where('t', '>=', $past)->count(); // 活跃用户数
		$view['unActiveUserCount'] = User::query()
		                                 ->where('t', '<=', $past)
		                                 ->whereEnable(1)
		                                 ->where('t', '>', 0)
		                                 ->count(); // 不活跃用户数
		$view['onlineUserCount'] = User::query()->where('t', '>=', time() - Minute * 10)->count(); // 10分钟内在线用户数
		$view['expireWarningUserCount'] = User::query()
		                                      ->where('expire_time', '>=', date('Y-m-d'))
		                                      ->where('expire_time', '<=', date('Y-m-d',
			                                      strtotime("+".self::$systemConfig['expire_days']." days")))
		                                      ->count(); // 临近过期用户数
		$view['largeTrafficUserCount'] = User::query()
		                                     ->whereRaw('(u + d) >= 107374182400')
		                                     ->whereIn('status', [0, 1])
		                                     ->count(); // 流量超过100G的用户

		$view['flowAbnormalUserCount'] = count($this->trafficAbnormal());// 1小时内流量异常用户
		$view['nodeCount'] = SsNode::query()->count();
		$view['unnormalNodeCount'] = SsNode::query()->whereStatus(0)->count();
		$flowCount = SsNodeTrafficDaily::query()
		                               ->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime("-30 days")))
		                               ->sum('total');
		$view['flowCount'] = flowAutoShow($flowCount);
		$totalFlowCount = SsNodeTrafficDaily::query()->sum('total');
		$view['totalFlowCount'] = flowAutoShow($totalFlowCount);
		$view['totalCredit'] = User::query()->sum('credit') / 100;
		$view['totalWaitRefAmount'] = ReferralLog::query()->whereIn('status', [0, 1])->sum('ref_amount') / 100;
		$view['totalRefAmount'] = ReferralApply::query()->whereStatus(2)->sum('amount') / 100;
		$view['totalOrder'] = Order::query()->count();
		$view['totalOnlinePayOrder'] = Order::query()->wherePayWay(2)->count();
		$view['totalSuccessOrder'] = Order::query()->whereStatus(2)->count();
		$view['todaySuccessOrder'] = Order::query()
		                                  ->whereStatus(2)
		                                  ->where('created_at', '>=', date('Y-m-d 00:00:00'))
		                                  ->where('created_at', '<=', date('Y-m-d 23:59:59'))
		                                  ->count();

		return Response::view('admin.index', $view);
	}

	// 1小时内流量异常用户
	private function trafficAbnormal(): array {
		$result = [];
		$userTotalTrafficList = UserTrafficHourly::query()
		                                         ->whereNodeId(0)
		                                         ->where('total', '>', 50 * MB)
		                                         ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900))
		                                         ->groupBy('user_id')
		                                         ->selectRaw("user_id, sum(total) as totalTraffic")
		                                         ->get(); // 只统计50M以上的记录，加快速度
		foreach($userTotalTrafficList as $user){
			if($user->totalTraffic > (self::$systemConfig['traffic_ban_value'] * GB)){
				$result[] = $user->user_id;
			}
		}
		return $result;
	}

	// 用户列表
	public function userList(Request $request): \Illuminate\Http\Response {
		$id = $request->input('id');
		$email = $request->input('email');
		$wechat = $request->input('wechat');
		$qq = $request->input('qq');
		$port = $request->input('port');
		$status = $request->input('status');
		$enable = $request->input('enable');
		$online = $request->input('online');
		$unActive = $request->input('unActive');
		$flowAbnormal = $request->input('flowAbnormal');
		$expireWarning = $request->input('expireWarning');
		$largeTraffic = $request->input('largeTraffic');

		$query = User::query()->with(['subscribe']);
		if(isset($id)){
			$query->whereId($id);
		}

		if(isset($email)){
			$query->where('email', 'like', '%'.$email.'%');
		}

		if(isset($wechat)){
			$query->where('wechat', 'like', '%'.$wechat.'%');
		}

		if(isset($qq)){
			$query->where('qq', 'like', '%'.$qq.'%');
		}

		if(isset($port)){
			$query->wherePort($port);
		}

		if(isset($status)){
			$query->whereStatus($status);
		}

		if(isset($enable)){
			$query->whereEnable($enable);
		}

		// 流量超过100G的
		if($largeTraffic){
			$query->whereIn('status', [0, 1])->whereRaw('(u + d) >= 107374182400');
		}

		// 临近过期提醒
		if($expireWarning){
			$query->where('expire_time', '>=', date('Y-m-d'))
			      ->where('expire_time', '<=',
				      date('Y-m-d', strtotime("+".self::$systemConfig['expire_days']." days")));
		}

		// 当前在线
		if($online){
			$query->where('t', '>=', time() - Minute * 10);
		}

		// 不活跃用户
		if($unActive){
			$query->where('t', '>', 0)
			      ->where('t', '<=',
				      strtotime(date('Y-m-d', strtotime("-".self::$systemConfig['expire_days']." days"))))
			      ->whereEnable(1);
		}

		// 1小时内流量异常用户
		if($flowAbnormal){
			$query->whereIn('id', $this->trafficAbnormal());
		}

		$userList = $query->orderByDesc('id')->paginate(15)->appends($request->except('page'));
		foreach($userList as $user){
			$user->transfer_enable = flowAutoShow($user->transfer_enable);
			$user->used_flow = flowAutoShow($user->u + $user->d);
			if($user->expire_time < date('Y-m-d')){
				$user->expireWarning = -1; // 已过期
			}elseif($user->expire_time == date('Y-m-d')){
				$user->expireWarning = 0; // 今天过期
			}elseif($user->expire_time > date('Y-m-d') && $user->expire_time <= date('Y-m-d', strtotime("+30 days"))){
				$user->expireWarning = 1; // 最近一个月过期
			}else{
				$user->expireWarning = 2; // 大于一个月过期
			}

			// 流量异常警告
			$time = date('Y-m-d H:i:s', time() - 3900);
			$totalTraffic = UserTrafficHourly::query()
			                                 ->whereUserId($user->id)
			                                 ->whereNodeId(0)
			                                 ->where('created_at', '>=', $time)
			                                 ->sum('total');
			$user->trafficWarning = $totalTraffic > (self::$systemConfig['traffic_ban_value'] * GB)? 1 : 0;

			// 订阅地址
			$user->link = (self::$systemConfig['subscribe_domain']?: self::$systemConfig['website_url']).'/s/'.$user->subscribe->code;
		}

		$view['userList'] = $userList;

		return Response::view('admin.user.userList', $view);
	}

	// 添加账号
	public function addUser(Request $request) {
		if($request->isMethod('POST')){
			// 校验email是否已存在
			$exists = User::query()->whereEmail($request->input('email'))->first();
			if($exists){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '用户名已存在，请重新输入']);
			}

			$user = new User();
			$user->username = $request->input('username');
			$user->email = $request->input('email');
			$user->password = Hash::make($request->input('password')?: makeRandStr());
			$user->port = $request->input('port')?: $this->makePort();
			$user->passwd = $request->input('passwd')?: makeRandStr();
			$user->vmess_id = $request->input('uuid')?: Str::uuid();
			$user->transfer_enable = toGB($request->input('transfer_enable')?: 0);
			$user->enable = $request->input('enable')?: 0;
			$user->method = $request->input('method');
			$user->protocol = $request->input('protocol');
			$user->obfs = $request->input('obfs');
			$user->speed_limit = $request->input('speed_limit');
			$user->wechat = $request->input('wechat');
			$user->qq = $request->input('qq');
			$user->enable_time = $request->input('enable_time')?: date('Y-m-d');
			$user->expire_time = $request->input('expire_time')?: date('Y-m-d', strtotime("+365 days"));
			$user->remark = str_replace(["atob", "eval"], "", $request->input('remark'));
			$user->level = $request->input('level')?: 0;
			$user->reg_ip = getClientIp();
			$user->reset_time = $request->input('reset_time') > date('Y-m-d')? $request->input('reset_time') : null;
			$user->invite_num = $request->input('invite_num')?: 0;
			$user->status = $request->input('status')?: 0;
			$user->save();

			if($user->id){
				// 生成订阅码
				$subscribe = new UserSubscribe();
				$subscribe->user_id = $user->id;
				$subscribe->code = Helpers::makeSubscribeCode();
				$subscribe->times = 0;
				$subscribe->save();

				// 写入用户流量变动记录
				Helpers::addUserTrafficModifyLog($user->id, 0, 0, toGB($request->input('transfer_enable', 0)),
					'后台手动添加用户');

				return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
			}

			return Response::json(['status' => 'fail', 'data' => '', 'message' => '添加失败']);
		}

		// 生成一个可用端口
		$view['method_list'] = Helpers::methodList();
		$view['protocol_list'] = Helpers::protocolList();
		$view['obfs_list'] = Helpers::obfsList();
		$view['level_list'] = Level::query()->orderBy('level')->get();

		return Response::view('admin.user.userInfo', $view);
	}

	// 生成端口
	public function makePort() {
		return self::$systemConfig['is_rand_port']? Helpers::getRandPort() : Helpers::getOnlyPort();
	}

	// 批量生成账号
	public function batchAddUsers(Request $request): ?JsonResponse {
		$amount = $request->input('amount');
		DB::beginTransaction();
		try{
			for($i = 0; $i < $amount; $i++){
				$uid = Helpers::addUser('批量生成-'.makeRandStr(), Hash::make(makeRandStr()), toGB(1024), 365);
				// 生成一个可用端口

				if($uid){
					// 生成订阅码
					$subscribe = new UserSubscribe();
					$subscribe->user_id = $uid;
					$subscribe->code = Helpers::makeSubscribeCode();
					$subscribe->times = 0;
					$subscribe->save();

					// 写入用户流量变动记录
					Helpers::addUserTrafficModifyLog($uid, 0, 0, toGB(1024), '后台批量生成用户');
				}
			}

			DB::commit();

			return Response::json(['status' => 'success', 'data' => '', 'message' => '批量生成账号成功']);
		}catch(Exception $e){
			DB::rollBack();

			return Response::json(['status' => 'fail', 'data' => '', 'message' => '批量生成账号失败：'.$e->getMessage()]);
		}
	}

	// 编辑账号
	public function editUser(Request $request, $id) {
		if($request->isMethod('POST')){
			$email = $request->input('email');
			$password = $request->input('password');
			$port = $request->input('port');
			$transfer_enable = $request->input('transfer_enable');
			$is_admin = $request->input('is_admin');
			$status = $request->input('status');

			// 校验email是否已存在
			$exists = User::query()->where('id', '<>', $id)->whereEmail($email)->first();
			if($exists){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '用户名已存在，请重新输入']);
			}

			// 校验端口是否已存在
			$exists = User::query()->where('id', '<>', $id)->where('port', '>', 0)->wherePort($port)->first();
			if($exists){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '端口已存在，请重新输入']);
			}

			// 禁止取消默认管理员
			if($id == 1 && $is_admin == 0){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '系统默认管理员不可取消']);
			}

			// 用户编辑前的信息
			$user = User::query()->whereId($id)->first();

			try{
				DB::beginTransaction();

				$data = [
					'username'        => $request->input('username'),
					'email'           => $email,
					'port'            => $port,
					'passwd'          => $request->input('passwd')?: makeRandStr(),
					'vmess_id'        => $request->input('uuid')?: Str::uuid(),
					'transfer_enable' => toGB($transfer_enable?: 0),
					'enable'          => $status < 0? 0 : $request->input('enable'),
					'method'          => $request->input('method'),
					'protocol'        => $request->input('protocol'),
					'obfs'            => $request->input('obfs'),
					'speed_limit'     => $request->input('speed_limit'),
					'wechat'          => $request->input('wechat'),
					'qq'              => $request->input('qq'),
					'enable_time'     => $request->input('enable_time')?: date('Y-m-d'),
					'expire_time'     => $request->input('expire_time')?: date('Y-m-d', strtotime("+365 days")),
					'remark'          => str_replace("eval", "", str_replace("atob", "", $request->input('remark'))),
					'level'           => $request->input('level'),
					'reset_time'      => $request->input('reset_time'),
					'status'          => $status
				];

				// 只有admin才有权限操作管理员属性
				if(Auth::getUser()->is_admin == 1){
					$data['is_admin'] = intval($is_admin);
				}

				// 非演示环境才可以修改管理员密码
				if(!empty($password) && !(env('APP_DEMO') && $id == 1)){
					$data['password'] = Hash::make($password);
				}

				User::query()->whereId($id)->update($data);

				// 写入用户流量变动记录
				if($user->transfer_enable != toGB($transfer_enable)){
					Helpers::addUserTrafficModifyLog($id, 0, $user->transfer_enable, toGB($transfer_enable),
						'后台手动编辑用户');
				}

				DB::commit();

				return Response::json(['status' => 'success', 'data' => '', 'message' => '编辑成功']);
			}catch(Exception $e){
				DB::rollBack();
				Log::error('编辑用户信息异常：'.$e->getMessage());

				return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败']);
			}
		}else{
			$user = User::query()->with(['referral'])->whereId($id)->first();
			if($user){
				$user->transfer_enable = flowToGB($user->transfer_enable);
			}

			$view['user'] = $user;
			$view['method_list'] = Helpers::methodList();
			$view['protocol_list'] = Helpers::protocolList();
			$view['obfs_list'] = Helpers::obfsList();
			$view['level_list'] = Level::query()->orderBy('level')->get();

			return view('admin.user.userInfo', $view)->with(compact('user'));
		}
	}

	// 删除用户
	public function delUser(Request $request): ?JsonResponse {
		$id = $request->input('id');

		if($id <= 1){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '系统管理员不可删除']);
		}

		try{
			DB::beginTransaction();

			User::query()->whereId($id)->delete();
			UserSubscribe::query()->whereUserId($id)->delete();
			UserBanLog::query()->whereUserId($id)->delete();
			UserCreditLog::query()->whereUserId($id)->delete();
			UserTrafficModifyLog::query()->whereUserId($id)->delete();
			UserLoginLog::query()->whereUserId($id)->delete();

			DB::commit();

			return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
		}catch(Exception $e){
			Log::error($e);
			DB::rollBack();

			return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
		}
	}

	// 文章列表
	public function articleList(Request $request): \Illuminate\Http\Response {
		$view['list'] = Article::query()->orderByDesc('sort')->paginate(15)->appends($request->except('page'));

		return Response::view('admin.article.articleList', $view);
	}

	// 添加文章
	public function addArticle(Request $request) {
		if($request->isMethod('POST')){
			$article = new Article();
			$article->title = $request->input('title');
			$article->type = $request->input('type', 1);
			$article->author = '管理员';
			$article->summary = $request->input('summary');
			// LOGO
			if($article->type == 4){
				$article->logo = $request->input('logo');
			}else{
				$logo = '';
				if($request->hasFile('logo')){
					$logo = $this->uploadFile($request->file('logo'));

					if(!$logo){
						Session::flash('errorMsg', 'LOGO不合法');

						return Redirect::back()->withInput();
					}
				}

				$article = new Article();
				$article->title = $request->input('title');
				$article->type = $request->input('type', 1);
				$article->author = '管理员';
				$article->summary = $request->input('summary');
				$article->logo = $logo;
			}
			$article->content = $request->input('content');
			$article->sort = $request->input('sort', 0);
			$article->save();

			if($article->id){
				Session::flash('successMsg', '添加成功');
			}else{
				Session::flash('errorMsg', '添加失败');
			}

			return Redirect::to('admin/articleList');
		}

		return Response::view('admin.article.addArticle');
	}

	// 编辑文章
	public function editArticle(Request $request) {
		$id = $request->input('id');

		if($request->isMethod('POST')){
			$title = $request->input('title');
			$type = $request->input('type');
			$summary = $request->input('summary');
			$content = $request->input('content');
			$sort = $request->input('sort');

			// 商品LOGO
			if($type == 4){
				$logo = $request->input('logo');
			}else{
				$logo = '';
				if($request->hasFile('logo')){
					$logo = $this->uploadFile($request->file('logo'));

					if(!$logo){
						Session::flash('errorMsg', 'LOGO不合法');

						return Redirect::back()->withInput();
					}
				}
			}

			$data = ['type' => $type, 'title' => $title, 'summary' => $summary, 'content' => $content, 'sort' => $sort];

			if($logo){
				$data['logo'] = $logo;
			}

			$ret = Article::query()->whereId($id)->update($data);
			if($ret){
				Session::flash('successMsg', '编辑成功');
			}else{
				Session::flash('errorMsg', '编辑失败');
			}

			return Redirect::to('admin/editArticle?id='.$id);
		}

		$view['article'] = Article::query()->whereId($id)->first();

		return Response::view('admin.article.editArticle', $view);
	}

	// 删除文章
	public function delArticle(Request $request): ?JsonResponse {
		$id = $request->input('id');

		$ret = Article::query()->whereId($id)->delete();
		if($ret){
			return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
		}

		return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
	}

	// 流量日志
	public function trafficLog(Request $request): \Illuminate\Http\Response {
		$port = $request->input('port');
		$user_id = $request->input('user_id');
		$email = $request->input('email');
		$nodeId = $request->input('nodeId');
		$startTime = $request->input('startTime');
		$endTime = $request->input('endTime');

		$query = UserTrafficLog::query()->with(['user', 'node']);

		if(isset($port)){
			$query->whereHas('user', static function($q) use ($port) {
				$q->wherePort($port);
			});
		}

		if(isset($user_id)){
			$query->whereUserId($user_id);
		}

		if(isset($email)){
			$query->whereHas('user', static function($q) use ($email) {
				$q->where('email', 'like', '%'.$email.'%');
			});
		}

		if(isset($nodeId)){
			$query->whereNodeId($nodeId);
		}

		if(isset($startTime)){
			$query->where('log_time', '>=', strtotime($startTime));
		}

		if(isset($endTime)){
			$query->where('log_time', '<=', strtotime($endTime));
		}

		// 已使用流量
		$view['totalTraffic'] = flowAutoShow($query->sum('u') + $query->sum('d'));

		$list = $query->orderByDesc('id')->paginate(20)->appends($request->except('page'));
		foreach($list as $vo){
			$vo->u = flowAutoShow($vo->u);
			$vo->d = flowAutoShow($vo->d);
			$vo->log_time = date('Y-m-d H:i:s', $vo->log_time);
		}

		$view['list'] = $list;
		$view['nodeList'] = SsNode::query()->whereStatus(1)->orderByDesc('sort')->orderByDesc('id')->get();

		return Response::view('admin.logs.trafficLog', $view);
	}

	// 导出配置信息
	public function export(Request $request, $id) {
		if(empty($id)){
			return Redirect::to('admin/userList');
		}

		$user = User::query()->whereId($id)->first();
		if(empty($user)){
			return Redirect::to('admin/userList');
		}

		if($request->isMethod('POST')){
			$node_id = $request->input('id');
			$infoType = $request->input('type');

			$node = SsNode::query()->whereId($node_id)->first();
			if($node->type == 1){
				if($node->compatible){
					$proxyType = 'SS';
				}else{
					$proxyType = 'SSR';
				}
			}else{
				$proxyType = 'V2Ray';
			}

			$data = $this->getUserNodeInfo($id, $node->id, $infoType !== 'text'? 0 : 1);

			return Response::json(['status' => 'success', 'data' => $data, 'title' => $proxyType]);

		}

		$view['nodeList'] = SsNode::query()
		                          ->whereStatus(1)
		                          ->orderByDesc('sort')
		                          ->orderBy('id')
		                          ->paginate(15)
		                          ->appends($request->except('page'));
		$view['user'] = $user;

		return Response::view('admin.user.export', $view);
	}

	// 导出原版SS用户配置信息
	public function exportSSJson(): BinaryFileResponse {
		$userList = User::query()->where('port', '>', 0)->get();

		$json = '';
		if(!$userList->isEmpty()){
			$userPassword = [];
			foreach($userList as $user){
				$userPassword[] = $user->port.":".$user->passwd;
			}

			$json = json_encode([
				'server'        => '0.0.0.0',
				'local_address' => '127.0.0.1',
				'local_port'    => 1080,
				'port_password' => $userPassword,
				'timeout'       => 300,
				'method'        => Helpers::getDefaultMethod(),
				'fast_open'     => false,
			]);
		}

		// 生成JSON文件
		$fileName = makeRandStr('16').'_shadowsocks.json';
		$filePath = public_path('downloads/'.$fileName);
		file_put_contents($filePath, $json);

		if(!file_exists($filePath)){
			exit('文件生成失败，请检查目录权限');
		}

		return Response::download($filePath);
	}

	// 修改个人资料
	public function profile(Request $request) {
		if($request->isMethod('POST')){
			$new_password = $request->input('new_password');

			if(!Hash::check($request->input('old_password'), Auth::getUser()->password)){
				return Redirect::back()->withErrors('旧密码错误，请重新输入');
			}

			if(Hash::check($new_password, Auth::getUser()->password)){
				return Redirect::back()->withErrors('新密码不可与旧密码一样，请重新输入');
			}

			$ret = User::uid()->update(['password' => Hash::make($new_password)]);
			if(!$ret){
				return Redirect::back()->withErrors('修改失败');
			}

			return Redirect::back()->with('successMsg', '修改成功');
		}

		return Response::view('admin.config.profile');
	}

	// 用户流量监控
	public function userMonitor(Request $request) {
		$id = $request->input('id');
		if(empty($id)){
			return Redirect::to('admin/userList');
		}

		$user = User::query()->whereId($id)->first();
		if(empty($user)){
			return Redirect::to('admin/userList');
		}

		// 30天内的流量
		$dailyData = [];
		$hourlyData = [];
		// 节点一个月内的流量
		$userTrafficDaily = UserTrafficDaily::query()
		                                    ->whereUserId($user->id)
		                                    ->whereNodeId(0)
		                                    ->where('created_at', '>=', date('Y-m'))
		                                    ->orderBy('created_at')
		                                    ->pluck('total')
		                                    ->toArray();

		$dailyTotal = date('d') - 1; // 今天不算，减一
		$dailyCount = count($userTrafficDaily);
		for($x = 0; $x < $dailyTotal - $dailyCount; $x++){
			$dailyData[$x] = 0;
		}
		for($x = $dailyTotal - $dailyCount; $x < $dailyTotal; $x++){
			$dailyData[$x] = round($userTrafficDaily[$x - ($dailyTotal - $dailyCount)] / GB, 3);
		}

		// 节点一天内的流量
		$userTrafficHourly = UserTrafficHourly::query()
		                                      ->whereUserId($user->id)
		                                      ->whereNodeId(0)
		                                      ->where('created_at', '>=', date('Y-m-d'))
		                                      ->orderBy('created_at')
		                                      ->pluck('total')
		                                      ->toArray();
		$hourlyTotal = date('H');
		$hourlyCount = count($userTrafficHourly);
		for($x = 0; $x < $hourlyTotal - $hourlyCount; $x++){
			$hourlyData[$x] = 0;
		}
		for($x = ($hourlyTotal - $hourlyCount); $x < $hourlyTotal; $x++){
			$hourlyData[$x] = round($userTrafficHourly[$x - ($hourlyTotal - $hourlyCount)] / GB, 3);
		}

		// 本月天数数据
		$monthDays = [];
		for($i = 1; $i <= date("d"); $i++){
			$monthDays[] = $i;
		}
		// 本日小时数据
		$dayHours = [];
		for($i = 1; $i <= date("H"); $i++){
			$dayHours[] = $i;
		}

		$view['trafficDaily'] = json_encode($dailyData);
		$view['trafficHourly'] = json_encode($hourlyData);
		$view['monthDays'] = json_encode($monthDays);
		$view['dayHours'] = json_encode($dayHours);
		$view['email'] = $user->email;

		return Response::view('admin.logs.userMonitor', $view);
	}

	// 加密方式、混淆、协议、等级、国家地区
	public function config(Request $request) {
		if($request->isMethod('POST')){
			$name = $request->input('name');
			$type = $request->input('type', 1); // 类型：1-加密方式（method）、2-协议（protocol）、3-混淆（obfs）
			$is_default = $request->input('is_default', 0);
			$sort = $request->input('sort', 0);

			if(empty($name)){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '配置名称不能为空']);
			}

			// 校验是否已存在
			$config = SsConfig::type($type)->whereName($name)->first();
			if($config){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '配置已经存在，请勿重复添加']);
			}

			$ssConfig = new SsConfig();
			$ssConfig->name = $name;
			$ssConfig->type = $type;
			$ssConfig->is_default = $is_default;
			$ssConfig->sort = $sort;
			$ssConfig->save();

			return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
		}

		$labelList = Label::query()->get();
		foreach($labelList as $label){
			$label->nodeCount = SsNodeLabel::query()->whereLabelId($label->id)->groupBy('label_id')->count();
		}

		$view['method_list'] = SsConfig::type(1)->get();
		$view['protocol_list'] = SsConfig::type(2)->get();
		$view['obfs_list'] = SsConfig::type(3)->get();
		$view['country_list'] = Country::query()->get();
		$view['level_list'] = Level::query()->get();
		$view['labelList'] = $labelList;

		return Response::view('admin.config.config', $view);
	}

	// 删除配置
	public function delConfig(Request $request): ?JsonResponse {
		$id = $request->input('id');

		$ret = SsConfig::query()->whereId($id)->delete();
		if($ret){
			return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
		}

		return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
	}

	// 设置默认配置
	public function setDefaultConfig(Request $request): JsonResponse {
		$id = $request->input('id');

		if(empty($id)){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '非法请求']);
		}

		$config = SsConfig::query()->whereId($id)->first();
		if(!$config){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '配置不存在']);
		}

		// 去除该配置所属类型的默认值
		SsConfig::default()->type($config->type)->update(['is_default' => 0]);

		// 将该ID对应记录值置为默认值
		SsConfig::query()->whereId($id)->update(['is_default' => 1]);

		return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
	}

	// 设置系统扩展信息，例如客服、统计代码
	public function setExtend(Request $request): ?RedirectResponse {
		$websiteAnalytics = $request->input('website_analytics');
		$websiteCustomerService = $request->input('website_customer_service');

		try{
			DB::beginTransaction();

			// 首页LOGO
			if($request->hasFile('website_home_logo')){
				$ret = $this->uploadFile($request->file('website_home_logo'));
				if(!$ret){
					Session::flash('errorMsg', 'LOGO不合法');
					return Redirect::back();
				}
				Config::query()->whereName('website_home_logo')->update(['value' => $ret]);
			}

			// 站内LOGO
			if($request->hasFile('website_logo')){
				$ret = $this->uploadFile($request->file('website_logo'));
				if(!$ret){
					Session::flash('errorMsg', 'LOGO不合法');
					return Redirect::back();
				}
				Config::query()->whereName('website_logo')->update(['value' => $ret]);
			}

			Config::query()->whereName('website_analytics')->update(['value' => $websiteAnalytics]);
			Config::query()->whereName('website_customer_service')->update(['value' => $websiteCustomerService]);

			Session::flash('successMsg', '更新成功');

			DB::commit();

			return Redirect::back();
		}catch(Exception $e){
			DB::rollBack();

			Session::flash('errorMsg', '更新失败');

			return Redirect::back();
		}
	}

	// 添加等级
	public function addLevel(Request $request): ?JsonResponse {
		$validator = Validator::make($request->all(), [
			'level'      => 'required|numeric|unique:level,level',
			'level_name' => 'required',
		]);

		if($validator->fails()){
			return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
		}

		$obj = new Level();
		$obj->level = $request->input('level');
		$obj->name = $request->input('level_name');
		$obj->save();

		if($obj->id){
			return Response::json(['status' => 'success', 'data' => '', 'message' => '提交成功']);
		}

		return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
	}

	// 编辑等级
	public function updateLevel(Request $request): JsonResponse {
		$id = $request->input('id');
		$level = $request->input('level');

		$validator = Validator::make($request->all(), [
			'id'         => 'required|numeric',
			'level'      => 'required|numeric',
			'level_name' => 'required',
		]);

		if($validator->fails()){
			return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
		}
		// 校验该等级下是否存在关联账号
		$levelCheck = Level::query()->where('id', '<>', $id)->whereLevel($level)->exists();
		if($levelCheck){
			return Response::json(['status' => 'fail', 'message' => '该等级已存在！']);
		}

		// 校验该等级下是否存在关联账号
		$userCount = User::query()->whereLevel($level)->count();
		if($userCount){
			return Response::json(['status' => 'fail', 'message' => '该等级下存在关联账号，请先取消关联！']);
		}

		Level::query()->whereId($id)->update(['level' => $level, 'name' => $request->input('level_name')]);

		return Response::json(['status' => 'success', 'message' => '操作成功']);
	}

	// 删除等级
	public function delLevel(Request $request): ?JsonResponse {
		$id = $request->input('id');

		$validator = Validator::make($request->all(), [
			'id' => 'required|numeric|exists:level,id',
		]);

		if($validator->fails()){
			return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
		}

		$level = Level::query()->whereId($id)->first();

		// 校验该等级下是否存在关联账号
		$userCount = User::query()->whereLevel($level->level)->count();
		if($userCount){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '该等级下存在关联账号，请先取消关联']);
		}
		$ret = false;
		try{
			$ret = Level::query()->whereId($id)->delete();
		}catch(Exception $e){
			Log::error('删除等级时报错：'.$e);
		}
		if($ret){
			return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
		}

		return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
	}

	// 添加国家/地区
	public function addCountry(Request $request): ?JsonResponse {
		$name = $request->input('country_name');
		$code = $request->input('country_code');

		if(empty($name)){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '国家/地区名称不能为空']);
		}

		if(empty($code)){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '国家/地区代码不能为空']);
		}

		$exists = Country::query()->whereName($name)->first();
		if($exists){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '该国家/地区名称已存在，请勿重复添加']);
		}

		$obj = new Country();
		$obj->name = $name;
		$obj->code = $code;
		$obj->save();

		if($obj->id){
			return Response::json(['status' => 'success', 'data' => '', 'message' => '提交成功']);
		}

		return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
	}

	// 编辑国家/地区
	public function updateCountry(Request $request): ?JsonResponse {
		$id = $request->input('id');
		$name = $request->input('country_name');
		$code = $request->input('country_code');

		if(empty($id)){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => 'ID不能为空']);
		}

		if(empty($name)){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '国家/地区名称不能为空']);
		}

		if(empty($code)){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '国家/地区代码不能为空']);
		}

		$country = Country::query()->whereId($id)->first();
		if($country){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '国家/地区不存在']);
		}

		// 校验该国家/地区下是否存在关联节点
		$existNode = SsNode::query()->whereCountryCode($country->code)->get();
		if(!$existNode){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '该国家/地区下存在关联节点，请先取消关联']);
		}

		$ret = Country::query()->whereId($id)->update(['name' => $name, 'code' => $code]);
		if($ret){
			return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
		}

		return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
	}

	// 删除国家/地区
	public function delCountry(Request $request): ?JsonResponse {
		$id = $request->input('id');

		if(empty($id)){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => 'ID不能为空']);
		}

		$country = Country::query()->whereId($id)->first();
		if(!$country){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '国家/地区不存在']);
		}

		// 校验该国家/地区下是否存在关联节点
		$existNode = SsNode::query()->whereCountryCode($country->code)->get();
		if(!$existNode){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '该国家/地区下存在关联节点，请先取消关联']);
		}
		$ret = false;
		try{
			$ret = Country::query()->whereId($id)->delete();
		}catch(Exception $e){
			Log::error('删除国家/地区时报错：'.$e);
		}
		if($ret){
			return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
		}

		return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
	}

	// 系统设置
	public function system(): \Illuminate\Http\Response {
		$view = self::$systemConfig;
		$view['label_list'] = Label::query()->orderByDesc('sort')->orderBy('id')->get();

		return Response::view('admin.config.system', $view);
	}

	// 设置某个配置项
	public function setConfig(Request $request): JsonResponse {
		$name = $request->input('name');
		$value = $request->input('value');

		if(!$name){
			return Response::json(['status' => 'fail', 'message' => '设置失败：请求参数异常']);
		}

		// 屏蔽异常配置
		if(!array_key_exists($name, self::$systemConfig)){
			return Response::json(['status' => 'fail', 'message' => '设置失败：配置不存在']);
		}

		// 如果开启用户邮件重置密码，则先设置网站名称和网址
		if($value != '0'
		   && in_array($name, ['is_reset_password', 'is_activate_account', 'expire_warning', 'traffic_warning'])){
			$config = Config::query()->whereName('website_name')->first();
			if($config->value == ''){
				return Response::json(['status' => 'fail', 'message' => '设置失败：启用该配置需要先设置【网站名称】']);
			}

			$config = Config::query()->whereName('website_url')->first();
			if($config->value == ''){
				return Response::json(['status' => 'fail', 'message' => '设置失败：启用该配置需要先设置【网站地址】']);
			}
		}

		// 支付设置判断
		if($value != '' && in_array($name, ['is_AliPay', 'is_QQPay', 'is_WeChatPay', 'is_otherPay'])){
			switch($value){
				case 'f2fpay':
					if(!self::$systemConfig['f2fpay_app_id'] || !self::$systemConfig['f2fpay_private_key']
					   || !self::$systemConfig['f2fpay_public_key']){
						return Response::json(['status' => 'fail', 'message' => '请先设置【支付宝F2F】必要参数']);
					}
					break;
				case 'codepay':
					if(!self::$systemConfig['codepay_url'] || !self::$systemConfig['codepay_id']
					   || !self::$systemConfig['codepay_key']){
						return Response::json(['status' => 'fail', 'message' => '请先设置【码支付】必要参数']);
					}
					break;
				case 'epay':
					if(!self::$systemConfig['epay_url'] || !self::$systemConfig['epay_mch_id'] || !self::$systemConfig['epay_key']){
						return Response::json(['status' => 'fail', 'message' => '请先设置【易支付】必要参数']);
					}
					break;
				case 'payjs':
					if(!self::$systemConfig['payjs_mch_id'] || !self::$systemConfig['payjs_key']){
						return Response::json(['status' => 'fail', 'message' => '请先设置【PayJs】必要参数']);
					}
					break;
				case 'bitpayx':
					if(!self::$systemConfig['bitpay_secret']){
						return Response::json(['status' => 'fail', 'message' => '请先设置【麻瓜宝】必要参数']);
					}
					break;
				case 'paypal':
					if(!self::$systemConfig['paypal_username'] || !self::$systemConfig['paypal_password']
					   || !self::$systemConfig['paypal_secret']){
						return Response::json(['status' => 'fail', 'message' => '请先设置【PayPal】必要参数']);
					}
					break;
				default:
					return Response::json(['status' => 'fail', 'message' => '未知支付渠道']);
					break;
			}
		}

		// 演示环境禁止修改特定配置项
		if(env('APP_DEMO')){
			$denyConfig = [
				'website_url',
				'min_rand_traffic',
				'max_rand_traffic',
				'push_bear_send_key',
				'push_bear_qrcode',
				'is_forbid_china',
				'website_security_code'
			];

			if(in_array($name, $denyConfig, true)){
				return Response::json(['status' => 'fail', 'message' => '演示环境禁止修改该配置']);
			}
		}

		// 如果是返利比例，则需要除100
		if($name === 'referral_percent'){
			$value = intval($value) / 100;
		}

		// 更新配置
		Config::query()->whereName($name)->update(['value' => $value]);

		return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
	}

	// 推送通知测试
	public function sendTestNotification(): JsonResponse {
		if(self::$systemConfig['is_notification']){
			$result = PushNotification::send('这是测试的标题', 'ProxyPanel测试内容');
			if($result === false){
				return Response::json(['status' => 'fail', 'message' => '发送失败，请重新尝试！']);
			}
			switch(self::$systemConfig['is_notification']){
				case 'serverChan':
					if(!$result['errno']){
						return Response::json(['status' => 'success', 'message' => '发送成功，请查看手机是否收到推送消息']);
					}

					return Response::json(['status' => 'fail', 'message' => $result? $result['errmsg'] : '未知']);
					break;
				case 'bark':
					if($result['code'] == 200){
						return Response::json(['status' => 'success', 'message' => '发送成功，请查看手机是否收到推送消息']);
					}

					return Response::json(['status' => 'fail', 'message' => $result['message']]);
					break;
				default:
			}
		}

		return Response::json(['status' => 'fail', 'message' => '请先选择【日志通知】渠道']);
	}

	// 邀请码列表
	public function inviteList(Request $request): \Illuminate\Http\Response {
		$view['inviteList'] = Invite::query()
		                            ->with(['generator', 'user'])
		                            ->orderBy('status')
		                            ->orderByDesc('id')
		                            ->paginate(15)
		                            ->appends($request->except('page'));

		return Response::view('admin.inviteList', $view);
	}

	// 生成邀请码
	public function makeInvite(): JsonResponse {
		for($i = 0; $i < 10; $i++){
			$obj = new Invite();
			$obj->uid = 0;
			$obj->fuid = 0;
			$obj->code = strtoupper(substr(md5(microtime().makeRandStr()), 8, 12));
			$obj->status = 0;
			$obj->dateline = date('Y-m-d H:i:s', strtotime("+".self::$systemConfig['admin_invite_days']." days"));
			$obj->save();
		}

		return Response::json(['status' => 'success', 'data' => '', 'message' => '生成成功']);
	}

	// 导出邀请码
	public function exportInvite(): void {
		$inviteList = Invite::query()->whereStatus(0)->orderBy('id')->get();

		$filename = '邀请码'.date('Ymd').'.xlsx';

		$spreadsheet = new Spreadsheet();
		$spreadsheet->getProperties()
		            ->setCreator('ProxyPanel')
		            ->setLastModifiedBy('ProxyPanel')
		            ->setTitle('邀请码')
		            ->setSubject('邀请码')
		            ->setDescription('')
		            ->setKeywords('')
		            ->setCategory('');

		try{
			$spreadsheet->setActiveSheetIndex(0);
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->setTitle('邀请码');
			$sheet->fromArray(['邀请码', '有效期'], null);

			foreach($inviteList as $k => $vo){
				$sheet->fromArray([$vo->code, $vo->dateline], null, 'A'.($k + 2));
			}

			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // 输出07Excel文件
			//header('Content-Type:application/vnd.ms-excel'); // 输出Excel03版本文件
			header('Content-Disposition: attachment;filename="'.$filename.'"');
			header('Cache-Control: max-age=0');
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}catch(\PhpOffice\PhpSpreadsheet\Exception $e){
			Log::error('导出优惠券时报错'.$e);
		}
	}

	// 订单列表
	public function orderList(Request $request): \Illuminate\Http\Response {
		$email = $request->input('email');
		$order_sn = $request->input('order_sn');
		$is_coupon = $request->input('is_coupon');
		$is_expire = $request->input('is_expire');
		$pay_way = $request->input('pay_way');
		$status = $request->input('status');
		$range_time = $request->input('range_time');
		$sort = $request->input('sort'); // 0-按创建时间降序、1-按创建时间升序
		$order_id = $request->input('oid');

		$query = Order::query()->with(['user', 'goods', 'coupon']);

		if(isset($email)){
			$query->whereHas('user', static function($q) use ($email) {
				$q->where('email', 'like', '%'.$email.'%');
			});
		}
		if(isset($order_sn)){
			$query->where('order_sn', 'like', '%'.$order_sn.'%');
		}

		if(isset($is_coupon)){
			if($is_coupon){
				$query->where('coupon_id', '<>', 0);
			}else{
				$query->whereCouponId(0);
			}
		}

		if(isset($is_expire)){
			$query->whereIsExpire($is_expire);
		}

		if(isset($pay_way)){
			$query->wherePayWay($pay_way);
		}

		if(isset($status)){
			$query->whereStatus($status);
		}

		if(isset($range_time) && $range_time !== ','){
			$range_time = explode(',', $range_time);
			$query->where('created_at', '>=', $range_time[0])->where('created_at', '<=', $range_time[1]);
		}

		if(isset($order_id)){
			$query->whereOid($order_id);
		}

		if($sort){
			$query->orderBy('oid');
		}else{
			$query->orderByDesc('oid');
		}

		$view['orderList'] = $query->paginate(15)->appends($request->except('page'));

		return Response::view('admin.logs.orderList', $view);
	}

	// 重置用户流量
	public function resetUserTraffic(Request $request): JsonResponse {
		$id = $request->input('id');

		User::query()->whereId($id)->update(['u' => 0, 'd' => 0]);

		return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
	}

	// 操作用户余额
	public function handleUserCredit(Request $request) {
		if($request->isMethod('POST')){
			$userId = $request->input('user_id');
			$amount = $request->input('amount');

			if(empty($userId) || empty($amount)){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '充值异常']);
			}

			try{
				DB::beginTransaction();

				$user = User::query()->whereId($userId)->first();

				// 写入余额变动日志
				Helpers::addUserCreditLog($userId, 0, $user->credit, $user->credit + $amount, $amount, '后台手动充值');

				// 加减余额
				if($amount < 0){
					$user->decrement('credit', abs($amount) * 100);
				}else{
					$user->increment('credit', abs($amount) * 100);
				}

				DB::commit();

				return Response::json(['status' => 'success', 'data' => '', 'message' => '充值成功']);
			}catch(Exception $e){
				DB::rollBack();

				return Response::json(['status' => 'fail', 'data' => '', 'message' => '充值失败：'.$e->getMessage()]);
			}
		}else{
			return Response::view('admin.handleUserCredit');
		}
	}

	// 用户余额变动记录
	public function userCreditLogList(Request $request): \Illuminate\Http\Response {
		$email = $request->input('email');

		$query = UserCreditLog::query()->with(['user'])->orderByDesc('id');

		if(isset($email)){
			$query->whereHas('user', static function($q) use ($email) {
				$q->where('email', 'like', '%'.$email.'%');
			});
		}

		$view['list'] = $query->paginate(15)->appends($request->except('page'));

		return Response::view('admin.logs.userCreditLogList', $view);
	}

	// 用户封禁记录
	public function userBanLogList(Request $request): \Illuminate\Http\Response {
		$email = $request->input('email');

		$query = UserBanLog::query()->with(['user'])->orderByDesc('id');

		if(isset($email)){
			$query->whereHas('user', static function($q) use ($email) {
				$q->where('email', 'like', '%'.$email.'%');
			});
		}

		$view['list'] = $query->paginate(15)->appends($request->except('page'));

		return Response::view('admin.logs.userBanLogList', $view);
	}

	// 用户流量变动记录
	public function userTrafficLogList(Request $request): \Illuminate\Http\Response {
		$email = $request->input('email');

		$query = UserTrafficModifyLog::query()->with(['user', 'order', 'order.goods']);

		if(isset($email)){
			$query->whereHas('user', static function($q) use ($email) {
				$q->where('email', 'like', '%'.$email.'%');
			});
		}

		$view['list'] = $query->orderByDesc('id')->paginate(15)->appends($request->except('page'));

		return Response::view('admin.logs.userTrafficLogList', $view);
	}

	// 用户在线IP记录
	public function userOnlineIPList(Request $request): \Illuminate\Http\Response {
		$email = $request->input('email');
		$port = $request->input('port');
		$wechat = $request->input('wechat');
		$qq = $request->input('qq');

		$query = User::query()->where('status', '>=', 0)->whereEnable(1);

		if(isset($email)){
			$query->where('email', 'like', '%'.$email.'%');
		}

		if(isset($wechat)){
			$query->where('wechat', 'like', '%'.$wechat.'%');
		}

		if(isset($qq)){
			$query->where('qq', 'like', '%'.$qq.'%');
		}

		if(isset($port)){
			$query->wherePort($port);
		}

		$userList = $query->paginate(15)->appends($request->except('page'));
		if(!$userList->isEmpty()){
			foreach($userList as $user){
				// 最近5条在线IP记录，如果后端设置为60秒上报一次，则为10分钟内的在线IP
				$user->onlineIPList = SsNodeIp::query()
				                              ->with(['node'])
				                              ->whereType('tcp')
				                              ->wherePort($user->port)
				                              ->where('created_at', '>=', strtotime("-10 minutes"))
				                              ->orderByDesc('id')
				                              ->limit(5)
				                              ->get();
			}
		}

		$view['userList'] = $userList;

		return Response::view('admin.logs.userOnlineIPList', $view);
	}

	// 转换成某个用户的身份
	public function switchToUser(Request $request): JsonResponse {
		$id = $request->input('user_id');

		$user = User::query()->find($id);
		if(!$user){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => "用户不存在"]);
		}

		// 存储当前管理员ID，并将当前登录信息改成要切换的用户的身份信息
		Session::put('admin', Auth::id());
		Auth::login($user);

		return Response::json(['status' => 'success', 'data' => '', 'message' => "身份切换成功"]);
	}

	// 添加标签
	public function addLabel(Request $request) {
		if($request->isMethod('POST')){
			$name = $request->input('name');
			$sort = $request->input('sort');

			$label = new Label();
			$label->name = $name;
			$label->sort = $sort;
			$label->save();

			return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
		}

		return Response::view('admin.label.addLabel');
	}

	// 编辑标签
	public function editLabel(Request $request) {
		if($request->isMethod('POST')){
			$id = $request->input('id');
			$name = $request->input('name');
			$sort = $request->input('sort');

			Label::query()->whereId($id)->update(['name' => $name, 'sort' => $sort]);

			return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
		}

		$id = $request->input('id');
		$view['label'] = Label::query()->whereId($id)->first();

		return Response::view('admin.label.editLabel', $view);
	}

	// 删除标签
	public function delLabel(Request $request): ?JsonResponse {
		$id = $request->input('id');

		DB::beginTransaction();
		try{
			Label::query()->whereId($id)->delete();
			SsNodeLabel::query()->whereLabelId($id)->delete(); // 删除节点关联

			DB::commit();

			return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
		}catch(Exception $e){
			DB::rollBack();

			return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败：'.$e->getMessage()]);
		}
	}

	// 邮件发送日志列表
	public function notificationLog(Request $request): \Illuminate\Http\Response {
		$email = $request->input('email');
		$type = $request->input('type');

		$query = NotificationLog::query();

		if(isset($email)){
			$query->where('address', 'like', '%'.$email.'%');
		}

		if(isset($type)){
			$query->whereType($type);
		}

		$view['list'] = $query->orderByDesc('id')->paginate(15)->appends($request->except('page'));

		return Response::view('admin.logs.notificationLog', $view);
	}

	// 在线IP监控（实时）
	public function onlineIPMonitor(Request $request): \Illuminate\Http\Response {
		$ip = $request->input('ip');
		$email = $request->input('email');
		$port = $request->input('port');
		$nodeId = $request->input('nodeId');
		$userId = $request->input('id');

		$query = SsNodeIp::query()->with(['node', 'user'])->where('created_at', '>=', strtotime("-120 seconds"));

		if(isset($ip)){
			$query->whereIp($ip);
		}

		if(isset($email)){
			$query->whereHas('user', static function($q) use ($email) {
				$q->where('email', 'like', '%'.$email.'%');
			});
		}

		if(isset($port)){
			$query->whereHas('user', static function($q) use ($port) {
				$q->wherePort($port);
			});
		}

		if(isset($nodeId)){
			$query->whereHas('node', static function($q) use ($nodeId) {
				$q->whereId($nodeId);
			});
		}

		if(isset($userId)){
			$query->whereHas('user', static function($q) use ($userId) {
				$q->whereId($userId);
			});
		}

		$list = $query->groupBy('user_id', 'node_id')->orderByDesc('id');
		foreach($list as $vo){
			// 跳过上报多IP的
			if($vo->ip == null || strpos($vo->ip, ',') === true){
				continue;
			}
			$ipInfo = QQWry::ip($vo->ip);
			if(isset($ipInfo['error'])){
				// 用IPIP的库再试一下
				$ipip = IPIP::ip($vo->ip);
				$ipInfo = [
					'country'  => $ipip['country_name'],
					'province' => $ipip['region_name'],
					'city'     => $ipip['city_name']
				];
			}

			$vo->ipInfo = $ipInfo['country'].' '.$ipInfo['province'].' '.$ipInfo['city'];
		}

		$view['list'] = $list->paginate(20)->appends($request->except('page'));
		$view['nodeList'] = SsNode::query()->whereStatus(1)->orderByDesc('sort')->orderByDesc('id')->get();

		return Response::view('admin.logs.onlineIPMonitor', $view);
	}
}
