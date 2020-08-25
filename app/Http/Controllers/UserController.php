<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\PushNotification;
use App\Mail\newTicket;
use App\Mail\replyTicket;
use App\Models\Article;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\Invite;
use App\Models\Node;
use App\Models\NodeHeartBeat;
use App\Models\NodePing;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Models\UserHourlyDataFlow;
use App\Models\UserLoginLog;
use App\Models\UserSubscribe;
use App\Services\UserService;
use Auth;
use Cache;
use DB;
use Exception;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;
use Mail;
use Redirect;
use Response;
use Session;
use Str;
use Validator;

/**
 * 用户控制器
 *
 * Class UserController
 *
 * @package App\Http\Controllers
 */
class UserController extends Controller {
	protected static $sysConfig;

	public function __construct() {
		self::$sysConfig = Helpers::sysConfig();
	}

	public function index(): \Illuminate\Http\Response {
		$user = Auth::getUser();
		$totalTransfer = $user->transfer_enable;
		$usedTransfer = $user->u + $user->d;
		$unusedTransfer = $totalTransfer - $usedTransfer > 0? $totalTransfer - $usedTransfer : 0;
		$expireTime = $user->expired_at;
		$view['remainDays'] = $expireTime < date('Y-m-d')? -1 : Helpers::daysToNow($expireTime);
		$view['resetDays'] = $user->reset_time? Helpers::daysToNow($user->reset_time) : 0;
		$view['unusedTransfer'] = $unusedTransfer;
		$view['expireTime'] = $expireTime;
		$view['banedTime'] = $user->ban_time? date('Y-m-d H:i:s', $user->ban_time) : 0;
		$view['unusedPercent'] = $totalTransfer > 0? round($unusedTransfer / $totalTransfer, 2) : 0;
		$view['noticeList'] = Article::type(2)->latest()->Paginate(1); // 公告
		//流量异常判断
		$hourlyTraffic = UserHourlyDataFlow::userRecentUsed($user->id)->sum('total');
		$view['isTrafficWarning'] = $hourlyTraffic >= (self::$sysConfig['traffic_ban_value'] * GB)?: 0;
		//付费用户判断
		$view['not_paying_user'] = Order::uid()->active()->where('origin_amount', '>', 0)->doesntExist();
		$view['userLoginLog'] = UserLoginLog::whereUserId($user->id)->latest()->first(); // 近期登录日志
		$view = array_merge($view, $this->dataFlowChart($user->id));

		return Response::view('user.index', $view);
	}

	// 签到
	public function checkIn(): JsonResponse {
		$user = Auth::getUser();
		// 系统开启登录加积分功能才可以签到
		if(!self::$sysConfig['is_checkin']){
			return Response::json(['status' => 'fail', 'message' => '系统未开启签到功能']);
		}

		// 已签到过，验证是否有效
		if(Cache::has('userCheckIn_'.$user->id)){
			return Response::json(['status' => 'fail', 'message' => '已经签到过了，明天再来吧']);
		}

		$traffic = random_int((int) self::$sysConfig['min_rand_traffic'],
				(int) self::$sysConfig['max_rand_traffic']) * MB;

		if(!(new UserService())->incrementData($traffic)){
			return Response::json(['status' => 'fail', 'message' => '签到失败，系统异常']);
		}

		// 写入用户流量变动记录
		Helpers::addUserTrafficModifyLog($user->id, 0, $user->transfer_enable, $user->transfer_enable + $traffic,
			'[签到]');

		// 多久后可以再签到
		$ttl = self::$sysConfig['traffic_limit_time']? self::$sysConfig['traffic_limit_time'] * Minute : Day;
		Cache::put('userCheckIn_'.$user->id, '1', $ttl);

		return Response::json(['status' => 'success', 'message' => '签到成功，系统送您 '.flowAutoShow($traffic).'流量']);
	}

	// 节点列表
	public function nodeList(Request $request) {
		$user = Auth::getUser();
		if($request->isMethod('POST')){
			$infoType = $request->input('type');

			$node = Node::find($request->input('id'));
			// 生成节点信息
			if($node->type == 1){
				$proxyType = $node->compatible? 'SS' : 'SSR';
			}else{
				$proxyType = 'V2Ray';
			}
			$data = $this->getUserNodeInfo($user->id, $node->id, $infoType !== 'text'? 0 : 1);

			return Response::json(['status' => 'success', 'data' => $data, 'title' => $proxyType]);
		}

		// 获取当前用户可用节点
		$nodeList = $user->userAccessNodes()->with(['labels', 'level_table'])->get();

		$view['nodesGeo'] = $nodeList->pluck('name', 'geo')->toArray();
		$onlineNode = NodeHeartBeat::recently()->distinct()->pluck('node_id')->toArray();
		$pingNodeLogs = NodePing::whereMonth('created_at', date('m'))->get(['node_id', 'ct', 'cu', 'cm', 'hk']);
		foreach($nodeList as $node){
			$data = $pingNodeLogs->where('node_id', $node->id);
			$node->ct = round($data->pluck('ct')->filter()->avg(), 2);
			$node->cu = round($data->pluck('cu')->filter()->avg(), 2);
			$node->cm = round($data->pluck('cm')->filter()->avg(), 2);
			$node->hk = round($data->pluck('hk')->filter()->avg(), 2);

			// 节点在线状态
			$node->offline = !in_array($node->id, $onlineNode);
		}
		$view['nodeList'] = $nodeList?: [];

		return Response::view('user.nodeList', $view);
	}

	// 公告详情
	public function article(Request $request): \Illuminate\Http\Response {
		$view['info'] = Article::findOrFail($request->input('id'));

		return Response::view('user.article', $view);
	}

	// 修改个人资料
	public function profile(Request $request) {
		$user = Auth::getUser();
		if($request->isMethod('POST')){
			$old_password = $request->input('old_password');
			$new_password = $request->input('new_password');
			$username = $request->input('username');
			$wechat = $request->input('wechat');
			$qq = $request->input('qq');
			$passwd = $request->input('passwd');

			// 修改密码
			if($old_password && $new_password){
				if(!Hash::check($old_password, $user->password)){
					return Redirect::to('profile#tab_1')->withErrors('旧密码错误，请重新输入');
				}

				if(Hash::check($new_password, $user->password)){
					return Redirect::to('profile#tab_1')->withErrors('新密码不可与旧密码一样，请重新输入');
				}

				// 演示环境禁止改管理员密码
				if($user->id === 1 && config('app.demo')){
					return Redirect::to('profile#tab_1')->withErrors('演示环境禁止修改管理员密码');
				}

				if(!$user->update(['password' => Hash::make($new_password)])){
					return Redirect::to('profile#tab_1')->withErrors('修改失败');
				}

				return Redirect::to('profile#tab_1')->with('successMsg', '修改成功');
				// 修改代理密码
			}

			if($passwd){
				if(!$user->update(['passwd' => $passwd])){
					return Redirect::to('profile#tab_3')->withErrors('修改失败');
				}

				return Redirect::to('profile#tab_3')->with('successMsg', '修改成功');
			}

			// 修改联系方式
			if(empty($username)){
				return Redirect::to('profile#tab_2')->withErrors('修改失败,昵称不能为空值');
			}

			if(!$user->update(['username' => $username, 'wechat' => $wechat, 'qq' => $qq])){
				return Redirect::to('profile#tab_2')->withErrors('修改失败');
			}

			return Redirect::to('profile#tab_2')->with('successMsg', '修改成功');
		}

		return Response::view('user.profile');
	}

	// 商品列表
	public function services(Request $request): \Illuminate\Http\Response {
		$user = Auth::getUser();
		// 余额充值商品，只取10个
		$view['chargeGoodsList'] = Goods::type(3)->whereStatus(1)->orderBy('price')->limit(10)->get();
		$view['goodsList'] = Goods::whereStatus(1)
		                          ->where('type', '<=', '2')
		                          ->orderByDesc('type')
		                          ->orderByDesc('sort')
		                          ->paginate(10)
		                          ->appends($request->except('page'));
		$renewOrder = Order::userActivePlan($user->id)->first();
		$renewPrice = $renewOrder? $renewOrder->goods : 0;
		$view['renewTraffic'] = $renewPrice? $renewPrice->renew : 0;
		// 有重置日时按照重置日为标准，否者就以过期日为标准
		$dataPlusDays = $user->reset_time?: $user->expired_at;
		$view['dataPlusDays'] = $dataPlusDays > date('Y-m-d')? Helpers::daysToNow($dataPlusDays) : 0;

		return Response::view('user.services', $view);
	}

	//重置流量
	public function resetUserTraffic(): ?JsonResponse {
		$user = Auth::getUser();
		$order = Order::userActivePlan()->first();
		$renewCost = $order->goods->renew;
		if($user->credit < $renewCost){
			return Response::json(['status' => 'fail', 'message' => '余额不足，请充值余额']);
		}

		$user->update(['u' => 0, 'd' => 0]);

		// 扣余额
		(new UserService($user))->updateCredit(-$renewCost);

		// 记录余额操作日志
		Helpers::addUserCreditLog($user->id, '', $user->credit, $user->credit - $renewCost, -1 * $renewCost,
			'用户自行重置流量');

		return Response::json(['status' => 'success', 'message' => '重置成功']);
	}

	// 工单
	public function ticketList(Request $request): \Illuminate\Http\Response {
		$view['ticketList'] = Ticket::uid()->latest()->paginate(10)->appends($request->except('page'));

		return Response::view('user.ticketList', $view);
	}

	// 订单
	public function invoices(Request $request): \Illuminate\Http\Response {
		$view['orderList'] = Order::uid()
		                          ->with(['goods', 'payment'])
		                          ->orderByDesc('id')
		                          ->paginate(10)
		                          ->appends($request->except('page'));
		$view['prepaidPlan'] = Order::userPrepay()->exists();

		return Response::view('user.invoices', $view);
	}

	public function closePlan(): JsonResponse {
		$activePlan = Order::userActivePlan()->first();
		$activePlan->is_expire = 1;

		if($activePlan->save()){
			// 关闭先前套餐后，新套餐自动运行
			if(Order::userActivePlan()->exists()){
				return Response::json(['status' => 'success', 'message' => '激活成功']);
			}
			return Response::json(['status' => 'success', 'message' => '关闭']);
		}

		return Response::json(['status' => 'fail', 'message' => '关闭失败']);
	}

	// 订单明细
	public function invoiceDetail($sn): \Illuminate\Http\Response {
		$view['order'] = Order::uid()->with(['goods', 'coupon', 'payment'])->whereOrderSn($sn)->firstOrFail();

		return Response::view('user.invoiceDetail', $view);
	}

	// 添加工单
	public function createTicket(Request $request): ?JsonResponse {
		$user = Auth::getUser();
		$title = $request->input('title');
		$content = clean($request->input('content'));
		$content = str_replace(["atob", "eval"], "", $content);

		if(empty($title) || empty($content)){
			return Response::json(['status' => 'fail', 'message' => '请输入标题和内容']);
		}

		$obj = new Ticket();
		$obj->user_id = $user->id;
		$obj->title = $title;
		$obj->content = $content;
		$obj->status = 0;
		$obj->save();

		if($obj->id){
			$emailTitle = "新工单提醒";
			$content = "标题：【".$title."】<br>用户：".$user->email."<br>内容：".$content;

			// 发邮件通知管理员
			if(self::$sysConfig['webmaster_email']){
				$logId = Helpers::addNotificationLog($emailTitle, $content, 1, self::$sysConfig['webmaster_email']);
				Mail::to(self::$sysConfig['webmaster_email'])->send(new newTicket($logId, $emailTitle, $content));
			}

			PushNotification::send($emailTitle, $content);

			return Response::json(['status' => 'success', 'message' => '提交成功']);
		}

		return Response::json(['status' => 'fail', 'message' => '提交失败']);
	}

	// 回复工单
	public function replyTicket(Request $request) {
		$id = $request->input('id');

		$ticket = Ticket::uid()->with('user')->whereId($id)->firstOrFail();

		if($request->isMethod('POST')){
			$content = clean($request->input('content'));
			$content = str_replace(["atob", "eval"], "", $content);
			$content = substr($content, 0, 300);

			if(empty($content)){
				return Response::json(['status' => 'fail', 'message' => '回复内容不能为空']);
			}

			if($ticket->status == 2){
				return Response::json(['status' => 'fail', 'message' => '错误：该工单已关闭']);
			}

			$obj = new TicketReply();
			$obj->ticket_id = $id;
			$obj->user_id = Auth::id();
			$obj->content = $content;
			$obj->save();

			if($obj->id){
				// 重新打开工单
				$ticket->status = 0;
				$ticket->save();

				$title = "工单回复提醒";
				$content = "标题：【".$ticket->title."】<br>用户回复：".$content;

				// 发邮件通知管理员
				if(self::$sysConfig['webmaster_email']){
					$logId = Helpers::addNotificationLog($title, $content, 1, self::$sysConfig['webmaster_email']);
					Mail::to(self::$sysConfig['webmaster_email'])->send(new replyTicket($logId, $title, $content));
				}

				PushNotification::send($title, $content);

				return Response::json(['status' => 'success', 'message' => '回复成功']);
			}

			return Response::json(['status' => 'fail', 'message' => '回复失败']);
		}

		$view['ticket'] = $ticket;
		$view['replyList'] = TicketReply::whereTicketId($id)->with('user')->oldest()->get();

		return Response::view('user.replyTicket', $view);
	}

	// 关闭工单
	public function closeTicket(Request $request): ?JsonResponse {
		$id = $request->input('id');

		$ret = Ticket::uid()->whereId($id)->update(['status' => 2]);
		if($ret){
			PushNotification::send('工单关闭提醒', '工单：ID'.$id.'用户已手动关闭');

			return Response::json(['status' => 'success', 'message' => '关闭成功']);
		}

		return Response::json(['status' => 'fail', 'message' => '关闭失败']);
	}

	// 邀请码
	public function invite(): \Illuminate\Http\Response {
		if(Order::uid()->active()->where('origin_amount', '>', 0)->doesntExist()){
			return Response::view('auth.error',
				['message' => '本功能对非付费用户禁用！请 <a class="btn btn-sm btn-danger" href="/">返 回</a>']);
		}

		$view['num'] = Auth::getUser()->invite_num; // 还可以生成的邀请码数量
		$view['inviteList'] = Invite::uid()->with(['invitee', 'inviter'])->paginate(10); // 邀请码列表
		$view['referral_traffic'] = flowAutoShow(self::$sysConfig['referral_traffic'] * MB);
		$view['referral_percent'] = self::$sysConfig['referral_percent'];

		return Response::view('user.invite', $view);
	}

	// 生成邀请码
	public function makeInvite(): JsonResponse {
		$user = Auth::getUser();
		if($user->invite_num <= 0){
			return Response::json(['status' => 'fail', 'message' => '生成失败：已无邀请码生成名额']);
		}

		$obj = new Invite();
		$obj->inviter_id = $user->id;
		$obj->invitee_id = 0;
		$obj->code = strtoupper(mb_substr(md5(microtime().Str::random()), 8, 12));
		$obj->status = 0;
		$obj->dateline = date('Y-m-d H:i:s', strtotime("+".self::$sysConfig['user_invite_days']." days"));
		$obj->save();

		User::uid()->decrement('invite_num', 1);

		return Response::json(['status' => 'success', 'message' => '生成成功']);
	}

	// 使用优惠券
	public function redeemCoupon(Request $request): JsonResponse {
		$coupon_sn = $request->input('coupon_sn');
		$good_price = $request->input('price');

		if(empty($coupon_sn)){
			return Response::json(['status' => 'fail', 'title' => '使用失败', 'message' => '请输入您的优惠劵！']);
		}

		$coupon = Coupon::whereSn($coupon_sn)->whereIn('type', [1, 2])->first();
		if(!$coupon){
			return Response::json(['status' => 'fail', 'title' => '优惠券不存在', 'message' => '请确认优惠券是否输入正确！']);
		}

		if($coupon->status == 1){
			return Response::json(['status' => 'fail', 'title' => '抱歉', 'message' => '优惠券已被使用！']);
		}

		if($coupon->status == 2){
			return Response::json(['status' => 'fail', 'title' => '抱歉', 'message' => '优惠券已失效！']);
		}

		if($coupon->end_time < time()){
			$coupon->status = 2;
			$coupon->save();

			return Response::json(['status' => 'fail', 'title' => '抱歉', 'message' => '优惠券已失效！']);
		}

		if($coupon->start_time > time()){
			return Response::json(['status' => 'fail', 'title' => '优惠券尚未生效', 'message' => '请等待活动正式开启']);
		}

		if($good_price < $coupon->rule){
			return Response::json(['status' => 'fail', 'title' => '使用条件未满足', 'message' => '请购买价格更高的套餐']);
		}

		$data = [
			'name'  => $coupon->name,
			'type'  => $coupon->type,
			'value' => $coupon->value
		];

		return Response::json(['status' => 'success', 'data' => $data, 'message' => '优惠券有效']);
	}

	// 购买服务
	public function buy($goods_id) {
		$user = Auth::getUser();
		$goods = Goods::whereId($goods_id)->whereStatus(1)->first();
		if(empty($goods)){
			return Redirect::to('services');
		}
		// 有重置日时按照重置日为标准，否者就以过期日为标准
		$dataPlusDays = $user->reset_time?: $user->expired_at;
		$view['dataPlusDays'] = $dataPlusDays > date('Y-m-d')? Helpers::daysToNow($dataPlusDays) : 0;
		$view['activePlan'] = Order::userActivePlan()->exists();
		$view['goods'] = $goods;

		return Response::view('user.buy', $view);
	}

	// 帮助中心
	public function help(): \Illuminate\Http\Response {
		//$view['articleList'] = Article::type(1)->orderByDesc('sort')->latest()->limit(10)->paginate(5);
		$data = [];
		if(Node::whereIn('type', [1, 4])->whereStatus(1)->exists()){
			$data[] = 'ss';
			//array_push
		}
		if(Node::whereType(2)->whereStatus(1)->exists()){
			$data[] = 'v2';
		}
		if(Node::whereType(3)->whereStatus(1)->exists()){
			$data[] = 'trojan';
		}

		$view['sub'] = $data;

		//付费用户判断
		$view['not_paying_user'] = Order::uid()->active()->where('origin_amount', '>', 0)->doesntExist();
		//客户端安装
		$view['Shadowrocket_install'] = 'itms-services://?action=download-manifest&url='.self::$sysConfig['website_url'].'/clients/Shadowrocket.plist';
		$view['Quantumult_install'] = 'itms-services://?action=download-manifest&url='.self::$sysConfig['website_url'].'/clients/Quantumult.plist';
		// 订阅连接
		$subscribe = UserSubscribe::whereUserId(Auth::id())->firstOrFail();
		$view['subscribe_status'] = $subscribe->status;
		$subscribe_link = (self::$sysConfig['subscribe_domain']?: self::$sysConfig['website_url']).'/s/'.$subscribe->code;
		$view['link'] = $subscribe_link;
		$view['subscribe_link'] = 'sub://'.base64url_encode($subscribe_link);
		$view['Shadowrocket_link'] = 'shadowrocket://add/sub://'.base64url_encode($subscribe_link).'?remarks='.(self::$sysConfig['website_name'].'-'.self::$sysConfig['website_url']);
		$view['Shadowrocket_linkQrcode'] = 'sub://'.base64url_encode($subscribe_link).'#'.base64url_encode(self::$sysConfig['website_name']);
		$view['Quantumult_linkOut'] = 'quantumult://configuration?server='.base64url_encode($subscribe_link).'&filter='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Pro.conf').'&rejection='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Rejection.conf');
		$view['Quantumult_linkIn'] = 'quantumult://configuration?server='.base64url_encode($subscribe_link).'&filter='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/BacktoCN.conf').'&rejection='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Rejection.conf');

		return Response::view('user.help', $view);
	}

	// 更换订阅地址
	public function exchangeSubscribe(): ?JsonResponse {
		try{
			DB::beginTransaction();

			// 更换订阅码
			Auth::getUser()->subscribe->update(['code' => Helpers::makeSubscribeCode()]);

			// 更换连接密码
			Auth::getUser()->update(['passwd' => Str::random()]);

			DB::commit();

			return Response::json(['status' => 'success', 'message' => '更换成功']);
		}catch(Exception $e){
			DB::rollBack();

			Log::error("更换订阅地址异常：".$e->getMessage());

			return Response::json(['status' => 'fail', 'message' => '更换失败'.$e->getMessage()]);
		}
	}

	// 转换成管理员的身份
	public function switchToAdmin(): JsonResponse {
		if(!Session::has('admin')){
			return Response::json(['status' => 'fail', 'message' => '非法请求']);
		}

		// 管理员信息重新写入user
		$user = Auth::loginUsingId(Session::get('admin'));
		Session::forget('admin');
		if($user){
			return Response::json(['status' => 'success', 'message' => "身份切换成功"]);
		}
		return Response::json(['status' => 'fail', 'message' => '身份切换失败']);
	}

	// Todo 卡券余额合并至CouponService
	public function charge(Request $request): ?JsonResponse {
		$validator = Validator::make($request->all(), [
			'coupon_sn' => [
				'required',
				Rule::exists('coupon', 'sn')->where(static function($query) {
					$query->whereType(3)->whereStatus(0);
				}),
			]
		], ['coupon_sn.required' => '券码不能为空', 'coupon_sn.exists' => '该券不可用']);

		if($validator->fails()){
			return Response::json(['status' => 'fail', 'message' => $validator->getMessageBag()->first()]);
		}

		$coupon = Coupon::whereSn($request->input('coupon_sn'))->firstOrFail();

		try{
			DB::beginTransaction();
			// 写入日志
			$user = Auth::getUser();
			Helpers::addUserCreditLog($user->id, 0, $user->credit, $user->credit + $coupon->value, $coupon->value,
				'用户手动充值 - [充值券：'.$request->input('coupon_sn').']');

			// 余额充值
			(new UserService($user))->updateCredit($coupon->value);

			// 更改卡券状态
			Coupon::find($coupon->id)->update(['status' => 1]);

			// 写入卡券日志
			Helpers::addCouponLog('账户余额充值使用', $coupon->id);

			DB::commit();

			return Response::json(['status' => 'success', 'message' => '充值成功']);
		}catch(Exception $e){
			Log::error('卡劵充值错误：'.$e->getMessage());
			DB::rollBack();

			return Response::json(['status' => 'fail', 'message' => '充值失败']);
		}
	}
}
