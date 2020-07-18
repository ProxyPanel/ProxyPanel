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
use App\Models\Order;
use App\Models\SsNode;
use App\Models\SsNodeInfo;
use App\Models\SsNodeLabel;
use App\Models\SsNodePing;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Models\UserLoginLog;
use App\Models\UserSubscribe;
use App\Models\UserTrafficDaily;
use App\Models\UserTrafficHourly;
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
use Validator;

/**
 * 用户控制器
 *
 * Class UserController
 *
 * @package App\Http\Controllers
 */
class UserController extends Controller {
	protected static $systemConfig;

	public function __construct() {
		self::$systemConfig = Helpers::systemConfig();
	}

	public function index(): \Illuminate\Http\Response {
		$user = Auth::getUser();
		$totalTransfer = $user->transfer_enable;
		$usedTransfer = $user->u + $user->d;
		$unusedTransfer = $totalTransfer - $usedTransfer > 0? $totalTransfer - $usedTransfer : 0;
		$expireTime = $user->expire_time;
		$view['remainDays'] = $expireTime < date('Y-m-d')? -1 : (strtotime($expireTime) - strtotime(date('Y-m-d'))) / Day;
		$view['resetDays'] = $user->reset_time? round((strtotime($user->reset_time) - strtotime(date('Y-m-d'))) / Day) : 0;
		$view['unusedTransfer'] = $unusedTransfer;
		$view['expireTime'] = $expireTime;
		$view['banedTime'] = $user->ban_time? date('Y-m-d H:i:s', $user->ban_time) : 0;
		$view['unusedPercent'] = $totalTransfer > 0? round($unusedTransfer / $totalTransfer, 2) : 0;
		$view['noticeList'] = Article::type(2)->orderByDesc('id')->Paginate(1); // 公告
		//流量异常判断
		$hourlyTraffic = UserTrafficHourly::query()
		                                  ->whereUserId($user->id)
		                                  ->whereNodeId(0)
		                                  ->where('created_at', '>=', date('Y-m-d H:i:s', time() - Minute * 65))
		                                  ->sum('total');
		$view['isTrafficWarning'] = $hourlyTraffic >= (self::$systemConfig['traffic_ban_value'] * GB)?: 0;
		//付费用户判断
		$view['not_paying_user'] = Order::uid()
		                                ->whereStatus(2)
		                                ->whereIsExpire(0)
		                                ->where('origin_amount', '>', 0)
		                                ->doesntExist();
		$view['userLoginLog'] = UserLoginLog::query()->whereUserId($user->id)->orderByDesc('id')->first(); // 近期登录日志

		$dailyData = [];
		$hourlyData = [];

		// 节点一个月内的流量
		// TODO:有bug
		$userTrafficDaily = UserTrafficDaily::query()
		                                    ->whereUserId($user->id)
		                                    ->whereNodeId(0)
		                                    ->where('created_at', '<=', date('Y-m-d'))
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

		return Response::view('user.index', $view);
	}

	// 签到
	public function checkIn(): JsonResponse {
		$user = Auth::getUser();
		// 系统开启登录加积分功能才可以签到
		if(!self::$systemConfig['is_checkin']){
			return Response::json(['status' => 'fail', 'message' => '系统未开启签到功能']);
		}

		// 已签到过，验证是否有效
		if(Cache::has('userCheckIn_'.$user->id)){
			return Response::json(['status' => 'fail', 'message' => '已经签到过了，明天再来吧']);
		}

		$traffic = random_int((int) self::$systemConfig['min_rand_traffic'],
				(int) self::$systemConfig['max_rand_traffic']) * MB;
		$ret = User::uid()->increment('transfer_enable', $traffic);
		if(!$ret){
			return Response::json(['status' => 'fail', 'message' => '签到失败，系统异常']);
		}

		// 写入用户流量变动记录
		Helpers::addUserTrafficModifyLog($user->id, 0, $user->transfer_enable, $user->transfer_enable + $traffic,
			'[签到]');

		// 多久后可以再签到
		$ttl = self::$systemConfig['traffic_limit_time']? self::$systemConfig['traffic_limit_time'] * Minute : Day;
		Cache::put('userCheckIn_'.$user->id, '1', $ttl);

		return Response::json(['status' => 'success', 'message' => '签到成功，系统送您 '.flowAutoShow($traffic).'流量']);
	}

	// 节点列表
	public function nodeList(Request $request) {
		if($request->isMethod('POST')){
			$node_id = $request->input('id');
			$infoType = $request->input('type');

			$node = SsNode::query()->whereId($node_id)->first();
			// 生成节点信息
			if($node->type == 1){
				$proxyType = $node->compatible? 'SS' : 'SSR';
			}else{
				$proxyType = 'V2Ray';
			}
			$data = $this->getUserNodeInfo(Auth::id(), $node->id, $infoType !== 'text'? 0 : 1);

			return Response::json(['status' => 'success', 'data' => $data, 'title' => $proxyType]);
		}

		// 获取当前用户可用节点
		$nodeList = SsNode::query()
		                  ->whereStatus(1)
		                  ->where('level', '<=', Auth::getUser()->level)
		                  ->orderByDesc('sort')
		                  ->orderBy('id')
		                  ->get();

		foreach($nodeList as $node){
			$node->ct = number_format(SsNodePing::query()->whereNodeId($node->id)->where('ct', '>', '0')->avg('ct'), 1,
				'.', '');
			$node->cu = number_format(SsNodePing::query()->whereNodeId($node->id)->where('cu', '>', '0')->avg('cu'), 1,
				'.', '');
			$node->cm = number_format(SsNodePing::query()->whereNodeId($node->id)->where('cm', '>', '0')->avg('cm'), 1,
				'.', '');
			$node->hk = number_format(SsNodePing::query()->whereNodeId($node->id)->where('hk', '>', '0')->avg('hk'), 1,
				'.', '');

			// 节点在线状态
			$node->offline = SsNodeInfo::query()
			                           ->whereNodeId($node->id)
			                           ->where('log_time', '>=', strtotime("-10 minutes"))
			                           ->orderByDesc('id')
			                           ->doesntExist();
			// 节点标签
			$node->labels = SsNodeLabel::query()->whereNodeId($node->id)->get();
		}
		$view['nodeList'] = $nodeList?: [];


		return Response::view('user.nodeList', $view);
	}

	// 公告详情
	public function article(Request $request): \Illuminate\Http\Response {
		$view['info'] = Article::query()->findOrFail($request->input('id'));

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
				if($user->id == 1 && env('APP_DEMO')){
					return Redirect::to('profile#tab_1')->withErrors('演示环境禁止修改管理员密码');
				}

				$ret = User::uid()->update(['password' => Hash::make($new_password)]);
				if(!$ret){
					return Redirect::to('profile#tab_1')->withErrors('修改失败');
				}

				return Redirect::to('profile#tab_1')->with('successMsg', '修改成功');
				// 修改代理密码
			}

			if($passwd){
				$ret = User::uid()->update(['passwd' => $passwd]);
				if(!$ret){
					return Redirect::to('profile#tab_3')->withErrors('修改失败');
				}

				return Redirect::to('profile#tab_3')->with('successMsg', '修改成功');
			}

			// 修改联系方式
			if(empty($username)){
				return Redirect::to('profile#tab_2')->withErrors('修改失败,昵称不能为空值');
			}

			$ret = User::uid()->update(['username' => $username, 'wechat' => $wechat, 'qq' => $qq]);
			if(!$ret){
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
		$view['goodsList'] = Goods::query()
		                          ->whereStatus(1)
		                          ->where('type', '<=', '2')
		                          ->orderByDesc('type')
		                          ->orderByDesc('sort')
		                          ->paginate(10)
		                          ->appends($request->except('page'));
		$renewOrder = Order::query()
		                   ->with(['goods'])
		                   ->whereUserId($user->id)
		                   ->whereStatus(2)
		                   ->whereIsExpire(0)
		                   ->whereHas('goods', static function($q) {
			                   $q->whereType(2);
		                   })
		                   ->first();
		$renewPrice = $renewOrder? Goods::query()->whereId($renewOrder->goods_id)->first() : 0;
		$view['renewTraffic'] = $renewPrice? $renewPrice->renew : 0;
		// 有重置日时按照重置日为标准，否者就以过期日为标准
		$dataPlusDays = $user->reset_time?: $user->expire_time;
		$view['dataPlusDays'] = $dataPlusDays > date('Y-m-d')? round((strtotime($dataPlusDays) - strtotime(date('Y-m-d'))) / Day) : 0;

		return Response::view('user.services', $view);
	}

	//重置流量
	public function resetUserTraffic(): ?JsonResponse {
		$user = Auth::getUser();
		$temp = Order::uid()->whereStatus(2)->whereIsExpire(0)->with(['goods'])->whereHas('goods', static function($q) {
			$q->whereType(2);
		})->first();
		$renewCost = Goods::query()->whereId($temp->goods_id)->first()->renew;
		if($user->credit < $renewCost){
			return Response::json(['status' => 'fail', 'message' => '余额不足，请充值余额']);
		}

		User::uid()->update(['u' => 0, 'd' => 0]);

		// 扣余额
		User::query()->whereId($user->id)->decrement('credit', $renewCost * 100);

		// 记录余额操作日志
		Helpers::addUserCreditLog($user->id, '', $user->credit, $user->credit - $renewCost, -1 * $renewCost,
			'用户自行重置流量');

		return Response::json(['status' => 'success', 'message' => '重置成功']);
	}

	// 工单
	public function ticketList(Request $request): \Illuminate\Http\Response {
		$view['ticketList'] = Ticket::uid()->orderByDesc('id')->paginate(10)->appends($request->except('page'));

		return Response::view('user.ticketList', $view);
	}

	// 订单
	public function invoices(Request $request): \Illuminate\Http\Response {
		$view['orderList'] = Order::uid()
		                          ->with(['user', 'goods', 'coupon', 'payment'])
		                          ->orderByDesc('oid')
		                          ->paginate(10)
		                          ->appends($request->except('page'));

		return Response::view('user.invoices', $view);
	}

	public function activeOrder(Request $request): JsonResponse {
		$oid = $request->input('oid');
		$prepaidOrder = Order::query()->whereOid($oid)->first();
		if(!$prepaidOrder){
			return Response::json(['status' => 'fail', 'message' => '查无此单！']);
		}

		if($prepaidOrder->status != 3){
			return Response::json(['status' => 'fail', 'message' => '非预支付订单，无需再次启动！']);
		}

		(new ServiceController)->activePrepaidOrder($oid);

		return Response::json(['status' => 'success', 'message' => '激活成功']);
	}

	// 订单明细
	public function invoiceDetail($sn): \Illuminate\Http\Response {
		$view['order'] = Order::uid()->with(['goods', 'coupon', 'payment'])->whereOrderSn($sn)->firstOrFail();

		return Response::view('user.invoiceDetail', $view);
	}

	// 添加工单
	public function createTicket(Request $request): ?JsonResponse {
		$title = $request->input('title');
		$content = clean($request->input('content'));
		$content = str_replace(["atob", "eval"], "", $content);

		if(empty($title) || empty($content)){
			return Response::json(['status' => 'fail', 'message' => '请输入标题和内容']);
		}

		$obj = new Ticket();
		$obj->user_id = Auth::id();
		$obj->title = $title;
		$obj->content = $content;
		$obj->status = 0;
		$obj->save();

		if($obj->id){
			$emailTitle = "新工单提醒";
			$content = "标题：【".$title."】<br>用户：".Auth::getUser()->email."<br>内容：".$content;

			// 发邮件通知管理员
			if(self::$systemConfig['webmaster_email']){
				$logId = Helpers::addNotificationLog($emailTitle, $content, 1, self::$systemConfig['webmaster_email']);
				Mail::to(self::$systemConfig['webmaster_email'])->send(new newTicket($logId, $emailTitle, $content));
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
				if(self::$systemConfig['webmaster_email']){
					$logId = Helpers::addNotificationLog($title, $content, 1, self::$systemConfig['webmaster_email']);
					Mail::to(self::$systemConfig['webmaster_email'])->send(new replyTicket($logId, $title, $content));
				}

				PushNotification::send($title, $content);

				return Response::json(['status' => 'success', 'message' => '回复成功']);
			}

			return Response::json(['status' => 'fail', 'message' => '回复失败']);
		}

		$view['ticket'] = $ticket;
		$view['replyList'] = TicketReply::query()->whereTicketId($id)->with('user')->orderBy('id')->get();

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
		if(Order::uid()->whereStatus(2)->whereIsExpire(0)->where('origin_amount', '>', 0)->doesntExist()){
			return Response::view('auth.error',
				['message' => '本功能对非付费用户禁用！请 <a class="btn btn-sm btn-danger" href="/">返 回</a>']);
		}

		$view['num'] = Auth::getUser()->invite_num; // 还可以生成的邀请码数量
		$view['inviteList'] = Invite::uid()->with(['generator', 'user'])->paginate(10); // 邀请码列表
		$view['referral_traffic'] = flowAutoShow(self::$systemConfig['referral_traffic'] * MB);
		$view['referral_percent'] = self::$systemConfig['referral_percent'];

		return Response::view('user.invite', $view);
	}

	// 生成邀请码
	public function makeInvite(): JsonResponse {
		if(Auth::getUser()->invite_num <= 0){
			return Response::json(['status' => 'fail', 'message' => '生成失败：已无邀请码生成名额']);
		}

		$obj = new Invite();
		$obj->uid = Auth::id();
		$obj->fuid = 0;
		$obj->code = strtoupper(mb_substr(md5(microtime().makeRandStr()), 8, 12));
		$obj->status = 0;
		$obj->dateline = date('Y-m-d H:i:s', strtotime("+".self::$systemConfig['user_invite_days']." days"));
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

		$coupon = Coupon::query()->whereSn($coupon_sn)->whereIn('type', [1, 2])->first();
		if(!$coupon){
			return Response::json(['status' => 'fail', 'title' => '优惠券不存在', 'message' => '请确认优惠券是否输入正确！']);
		}

		if($coupon->status == 1){
			return Response::json(['status' => 'fail', 'title' => '抱歉', 'message' => '优惠券已被使用！']);
		}

		if($coupon->status == 2){
			return Response::json(['status' => 'fail', 'title' => '抱歉', 'message' => '优惠券已失效！']);
		}

		if($coupon->available_end < time()){
			$coupon->status = 2;
			$coupon->save();

			return Response::json(['status' => 'fail', 'title' => '抱歉', 'message' => '优惠券已失效！']);
		}

		if($coupon->available_start > time()){
			return Response::json(['status' => 'fail', 'title' => '优惠券尚未生效', 'message' => '请等待活动正式开启']);
		}

		if($good_price < $coupon->rule){
			return Response::json(['status' => 'fail', 'title' => '使用条件未满足', 'message' => '请购买价格更高的套餐']);
		}

		$data = [
			'name'     => $coupon->name,
			'type'     => $coupon->type,
			'amount'   => $coupon->amount,
			'discount' => $coupon->discount
		];

		return Response::json(['status' => 'success', 'data' => $data, 'message' => '优惠券有效']);
	}

	// 购买服务
	public function buy($goods_id) {
		$goods = Goods::query()->whereId($goods_id)->whereStatus(1)->first();
		if(empty($goods)){
			return Redirect::to('services');
		}
		// 有重置日时按照重置日为标准，否者就以过期日为标准
		$dataPlusDays = Auth::getUser()->reset_time?: Auth::getUser()->expire_time;
		$view['dataPlusDays'] = $dataPlusDays > date('Y-m-d')? round((strtotime($dataPlusDays) - strtotime(date('Y-m-d'))) / Day) : 0;
		$view['activePlan'] = Order::uid()
		                           ->with(['goods'])
		                           ->whereIsExpire(0)
		                           ->whereStatus(2)
		                           ->whereHas('goods', static function($q) {
			                           $q->whereType(2);
		                           })
		                           ->exists();
		$view['goods'] = $goods;

		return Response::view('user.buy', $view);
	}

	// 帮助中心
	public function help(): \Illuminate\Http\Response {
		//$view['articleList'] = Article::type(1)->orderByDesc('sort')->orderByDesc('id')->limit(10)->paginate(5);
		$data = [];
		if(SsNode::query()->whereIn('type',[1,4])->whereStatus(1)->exists()){
			$data[] = 'ss';
			//array_push
		}
		if(SsNode::query()->whereType(2)->whereStatus(1)->exists()){
			$data[] = 'v2';
		}
		if(SsNode::query()->whereType(3)->whereStatus(1)->exists()){
			$data[] = 'trojan';
		}

		$view['sub'] = $data;

		//付费用户判断
		$view['not_paying_user'] = Order::uid()
		                                ->whereStatus(2)
		                                ->whereIsExpire(0)
		                                ->where('origin_amount', '>', 0)
		                                ->doesntExist();
		//客户端安装
		$view['Shadowrocket_install'] = 'itms-services://?action=download-manifest&url='.self::$systemConfig['website_url'].'/clients/Shadowrocket.plist';
		$view['Quantumult_install'] = 'itms-services://?action=download-manifest&url='.self::$systemConfig['website_url'].'/clients/Quantumult.plist';
		// 订阅连接
		$subscribe = UserSubscribe::query()->whereUserId(Auth::id())->first();
		$view['subscribe_status'] = $subscribe->status;
		$subscribe_link = (self::$systemConfig['subscribe_domain']?: self::$systemConfig['website_url']).'/s/'.$subscribe->code;
		$view['link'] = $subscribe_link;
		$view['subscribe_link'] = 'sub://'.base64url_encode($subscribe_link);
		$view['Shadowrocket_link'] = 'shadowrocket://add/sub://'.base64url_encode($subscribe_link).'?remarks='.(self::$systemConfig['website_name'].'-'.self::$systemConfig['website_url']);
		$view['Shadowrocket_linkQrcode'] = 'sub://'.base64url_encode($subscribe_link).'#'.base64url_encode(self::$systemConfig['website_name']);
		$view['Quantumult_linkOut'] = 'quantumult://configuration?server='.base64url_encode($subscribe_link).'&filter='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Pro.conf').'&rejection='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Rejection.conf');
		$view['Quantumult_linkIn'] = 'quantumult://configuration?server='.base64url_encode($subscribe_link).'&filter='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/BacktoCN.conf').'&rejection='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Rejection.conf');

		return Response::view('user.help', $view);
	}

	// 更换订阅地址
	public function exchangeSubscribe(): ?JsonResponse {
		DB::beginTransaction();
		try{
			// 更换订阅码
			UserSubscribe::uid()->update(['code' => Helpers::makeSubscribeCode()]);

			// 更换连接密码
			User::uid()->update(['passwd' => makeRandStr()]);

			DB::commit();

			return Response::json(['status' => 'success', 'message' => '更换成功']);
		}catch(Exception $e){
			DB::rollBack();

			Log::info("更换订阅地址异常：".$e->getMessage());

			return Response::json(['status' => 'fail', 'message' => '更换失败'.$e->getMessage()]);
		}
	}

	// 转换成管理员的身份
	public function switchToAdmin(): JsonResponse {
		if(!Session::has('admin')){
			return Response::json(['status' => 'fail', 'message' => '非法请求']);
		}

		// 管理员信息重新写入user
		Auth::loginUsingId(Session::get('admin'));
		Session::forget('admin');

		return Response::json(['status' => 'success', 'message' => "身份切换成功"]);
	}

	// 卡券余额充值
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

		$coupon = Coupon::query()->whereSn($request->input('coupon_sn'))->first();

		try{
			DB::beginTransaction();
			// 写入日志
			$user = Auth::getUser();
			Helpers::addUserCreditLog($user->id, 0, $user->credit, $user->credit + $coupon->amount, $coupon->amount,
				'用户手动充值 - [充值券：'.$request->input('coupon_sn').']');

			// 余额充值
			User::uid()->increment('credit', $coupon->amount * 100);

			// 更改卡券状态
			Coupon::query()->whereId($coupon->id)->update(['status' => 1]);

			// 写入卡券日志
			Helpers::addCouponLog($coupon->id, 0, 0, '账户余额充值使用');

			DB::commit();

			return Response::json(['status' => 'success', 'message' => '充值成功']);
		}catch(Exception $e){
			Log::error($e);
			DB::rollBack();

			return Response::json(['status' => 'fail', 'message' => '充值失败']);
		}
	}
}
