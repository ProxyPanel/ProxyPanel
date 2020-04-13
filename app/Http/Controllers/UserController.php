<?php

namespace App\Http\Controllers;

use App\Components\Callback;
use App\Components\Helpers;
use App\Components\PushNotification;
use App\Http\Models\Article;
use App\Http\Models\Coupon;
use App\Http\Models\Goods;
use App\Http\Models\Invite;
use App\Http\Models\Order;
use App\Http\Models\ReferralApply;
use App\Http\Models\ReferralLog;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeInfo;
use App\Http\Models\SsNodeLabel;
use App\Http\Models\SsNodePing;
use App\Http\Models\Ticket;
use App\Http\Models\TicketReply;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use App\Http\Models\UserLoginLog;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserTrafficDaily;
use App\Http\Models\UserTrafficHourly;
use App\Mail\newTicket;
use App\Mail\replyTicket;
use Auth;
use Cache;
use DB;
use Exception;
use Hash;
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
class UserController extends Controller
{
	use Callback;
	protected static $systemConfig;

	function __construct()
	{
		self::$systemConfig = Helpers::systemConfig();
	}

	public function index()
	{
		$totalTransfer = Auth::user()->transfer_enable;
		$usedTransfer = Auth::user()->u+Auth::user()->d;
		$unusedTransfer = $totalTransfer-$usedTransfer > 0? $totalTransfer-$usedTransfer : 0;
		$expireTime = Auth::user()->expire_time;
		$view['remainDays'] = $expireTime < date('Y-m-d')? -1 : (strtotime($expireTime)-strtotime(date('Y-m-d')))/86400;
		$view['resetDays'] = Auth::user()->reset_time? round((strtotime(Auth::user()->reset_time)-strtotime(date('Y-m-d')))/86400) : 0;
		$view['unusedTransfer'] = $unusedTransfer;
		$view['expireTime'] = $expireTime;
		$view['banedTime'] = Auth::user()->ban_time? date('Y-m-d H:i:s', Auth::user()->ban_time) : 0;
		$view['unusedPercent'] = $totalTransfer > 0? round($unusedTransfer/$totalTransfer, 2) : 0;
		$view['noticeList'] = Article::type(2)->orderBy('id', 'desc')->Paginate(1); // 公告
		//流量异常判断
		$hourlyTraffic = UserTrafficHourly::query()->where('user_id', Auth::user()->id)->where('node_id', 0)->where('created_at', '>=', date('Y-m-d H:i:s', time()-3900))->sum('total');
		$view['isTrafficWarning'] = $hourlyTraffic >= (self::$systemConfig['traffic_ban_value']*1073741824)? : 0;
		//付费用户判断
		$view['not_paying_user'] = Order::uid()->where('status', 2)->where('is_expire', 0)->where('origin_amount', '>', 0)->doesntExist();
		$view['userLoginLog'] = UserLoginLog::query()->where('user_id', Auth::user()->id)->orderBy('id', 'desc')->first(); // 近期登录日志

		$dailyData = [];
		$hourlyData = [];

		// 节点一个月内的流量
		// TODO:有bug
		$userTrafficDaily = UserTrafficDaily::query()->where('user_id', Auth::user()->id)->where('node_id', 0)->where('created_at', '<=', date('Y-m-d', time()))->orderBy('created_at', 'asc')->pluck('total')->toArray();

		$dailyTotal = date('d', time())-1; // 今天不算，减一
		$dailyCount = count($userTrafficDaily);
		for($x = 0; $x < $dailyTotal-$dailyCount; $x++){
			$dailyData[$x] = 0;
		}
		for($x = $dailyTotal-$dailyCount; $x < $dailyTotal; $x++){
			$dailyData[$x] = round($userTrafficDaily[$x-($dailyTotal-$dailyCount)]/(1024*1024*1024), 3);
		}

		// 节点一天内的流量
		$userTrafficHourly = UserTrafficHourly::query()->where('user_id', Auth::user()->id)->where('node_id', 0)->where('created_at', '>=', date('Y-m-d', time()))->orderBy('created_at', 'asc')->pluck('total')->toArray();
		$hourlyTotal = date('H');
		$hourlyCount = count($userTrafficHourly);
		for($x = 0; $x < $hourlyTotal-$hourlyCount; $x++){
			$hourlyData[$x] = 0;
		}
		for($x = ($hourlyTotal-$hourlyCount); $x < $hourlyTotal; $x++){
			$hourlyData[$x] = round($userTrafficHourly[$x-($hourlyTotal-$hourlyCount)]/(1024*1024*1024), 3);
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

		$view['trafficDaily'] = "'".implode("','", $dailyData)."'";
		$view['trafficHourly'] = "'".implode("','", $hourlyData)."'";
		$view['monthDays'] = "'".implode("','", $monthDays)."'";
		$view['dayHours'] = "'".implode("','", $dayHours)."'";

		return Response::view('user.index', $view);
	}

	// 签到
	public function checkIn()
	{
		// 系统开启登录加积分功能才可以签到
		if(!self::$systemConfig['is_checkin']){
			return Response::json(['status' => 'fail', 'message' => '系统未开启签到功能']);
		}

		// 已签到过，验证是否有效
		if(Cache::has('userCheckIn_'.Auth::user()->id)){
			return Response::json(['status' => 'fail', 'message' => '已经签到过了，明天再来吧']);
		}

		$traffic = mt_rand((int)self::$systemConfig['min_rand_traffic'], (int)self::$systemConfig['max_rand_traffic'])*1048576;
		$ret = User::uid()->increment('transfer_enable', $traffic);
		if(!$ret){
			return Response::json(['status' => 'fail', 'message' => '签到失败，系统异常']);
		}

		// 写入用户流量变动记录
		Helpers::addUserTrafficModifyLog(Auth::user()->id, 0, Auth::user()->transfer_enable, Auth::user()->transfer_enable+$traffic, '[签到]');

		// 多久后可以再签到
		$ttl = self::$systemConfig['traffic_limit_time']? self::$systemConfig['traffic_limit_time']*60 : 86400;
		Cache::put('userCheckIn_'.Auth::user()->id, '1', $ttl);

		return Response::json(['status' => 'success', 'message' => '签到成功，系统送您 '.flowAutoShow($traffic).'流量']);
	}

	// 节点列表
	public function nodeList(Request $request)
	{
		if($request->isMethod('POST')){
			$node_id = $request->input('id');
			$infoType = $request->input('type');

			$node = SsNode::query()->whereKey($node_id)->first();
			// 生成节点信息
			$proxyType = $node->type == 1? ($node->compatible? 'SS' : 'SSR') : 'V2Ray';
			$data = $this->getNodeInfo(Auth::user()->id, $node->id, $infoType != 'text'? 0 : 1);

			return Response::json(['status' => 'success', 'data' => $data, 'title' => $proxyType]);
		}else{
			// 获取当前用户标签
			$userLabelIds = UserLabel::uid()->pluck('label_id');
			// 获取当前用户可用节点
			$nodeList = SsNode::query()->selectRaw('ss_node.*')->leftJoin('ss_node_label', 'ss_node.id', '=', 'ss_node_label.node_id')->whereIn('ss_node_label.label_id', $userLabelIds)->where('ss_node.status', 1)->groupBy('ss_node.id')->orderBy('ss_node.sort', 'desc')->orderBy('ss_node.id', 'asc')->get();

			foreach($nodeList as $node){
				$node->ct = number_format(SsNodePing::query()->where('node_id', $node->id)->where('ct', '>', '0')->avg('ct'), 1, '.', '');
				$node->cu = number_format(SsNodePing::query()->where('node_id', $node->id)->where('cu', '>', '0')->avg('cu'), 1, '.', '');
				$node->cm = number_format(SsNodePing::query()->where('node_id', $node->id)->where('cm', '>', '0')->avg('cm'), 1, '.', '');
				$node->hk = number_format(SsNodePing::query()->where('node_id', $node->id)->where('hk', '>', '0')->avg('hk'), 1, '.', '');

				// 节点在线状态
				$node->offline = SsNodeInfo::query()->where('node_id', $node->id)->where('log_time', '>=', strtotime("-10 minutes"))->orderBy('id', 'desc')->doesntExist();
				// 节点标签
				$node->labels = SsNodeLabel::query()->where('node_id', $node->id)->first();
			}
			$view['nodeList'] = $nodeList? : [];
		}


		return Response::view('user.nodeList', $view);
	}

	// 公告详情
	public function article(Request $request)
	{
		$view['info'] = Article::query()->findOrFail($request->input('id'));

		return Response::view('user.article', $view);
	}

	// 修改个人资料
	public function profile(Request $request)
	{
		if($request->isMethod('POST')){
			$old_password = trim($request->input('old_password'));
			$new_password = trim($request->input('new_password'));
			$username = trim($request->input('username'));
			$wechat = trim($request->input('wechat'));
			$qq = trim($request->input('qq'));
			$passwd = trim($request->input('passwd'));

			// 修改密码
			if($old_password && $new_password){
				if(!Hash::check($old_password, Auth::user()->password)){
					return Redirect::to('profile#tab_1')->withErrors('旧密码错误，请重新输入');
				}elseif(Hash::check($new_password, Auth::user()->password)){
					return Redirect::to('profile#tab_1')->withErrors('新密码不可与旧密码一样，请重新输入');
				}

				// 演示环境禁止改管理员密码
				if(env('APP_DEMO') && Auth::user()->id == 1){
					return Redirect::to('profile#tab_1')->withErrors('演示环境禁止修改管理员密码');
				}

				$ret = User::uid()->update(['password' => Hash::make($new_password)]);
				if(!$ret){
					return Redirect::to('profile#tab_1')->withErrors('修改失败');
				}else{
					return Redirect::to('profile#tab_1')->with('successMsg', '修改成功');
				}
				// 修改代理密码
			}elseif($passwd){
				$ret = User::uid()->update(['passwd' => $passwd]);
				if(!$ret){
					return Redirect::to('profile#tab_3')->withErrors('修改失败');
				}else{
					return Redirect::to('profile#tab_3')->with('successMsg', '修改成功');
				}
			}else{
				// 修改联系方式
				if(empty($username)){
					return Redirect::to('profile#tab_2')->withErrors('修改失败,昵称不能为空值');
				}

				$ret = User::uid()->update(['username' => $username, 'wechat' => $wechat, 'qq' => $qq]);
				if(!$ret){
					return Redirect::to('profile#tab_2')->withErrors('修改失败');
				}else{
					return Redirect::to('profile#tab_2')->with('successMsg', '修改成功');
				}
			}
		}else{
			return Response::view('user.profile');
		}
	}

	// 商品列表
	public function services(Request $request)
	{
		// 余额充值商品，只取10个
		$view['chargeGoodsList'] = Goods::type(3)->where('status', 1)->orderBy('price', 'asc')->orderBy('price', 'asc')->limit(10)->get();
		$view['goodsList'] = Goods::query()->where('status', 1)->where('type', '<=', '2')->orderBy('type', 'desc')->orderBy('sort', 'desc')->paginate(10)->appends($request->except('page'));
		$renewOrder = Order::query()->with(['goods'])->where('user_id', Auth::user()->id)->where('status', 2)->where('is_expire', 0)->whereHas('goods', function($q){ $q->where('type', 2); })->first();
		$renewPrice = $renewOrder? Goods::query()->where('id', $renewOrder->goods_id)->first() : 0;
		$view['renewTraffic'] = $renewPrice? $renewPrice->renew : 0;
		// 有重置日时按照重置日为标准，否者就以过期日为标准
		$dataPlusDays = Auth::user()->reset_time? Auth::user()->reset_time : Auth::user()->expire_time;
		$view['dataPlusDays'] = $dataPlusDays > date('Y-m-d')? round((strtotime($dataPlusDays)-strtotime(date('Y-m-d')))/86400) : 0;

		return Response::view('user.services', $view);
	}

	//重置流量
	public function resetUserTraffic()
	{
		$temp = Order::uid()->where('status', 2)->where('is_expire', 0)->first();
		$renewCost = Goods::query()->where('id', $temp->goods_id)->first()->renew;
		if(Auth::user()->balance < $renewCost){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '余额不足，请充值余额']);
		}else{
			User::uid()->update(['u' => 0, 'd' => 0]);

			// 扣余额
			User::query()->where('id', Auth::user()->id)->decrement('balance', $renewCost*100);

			// 记录余额操作日志
			$this->addUserBalanceLog(Auth::user()->id, '', Auth::user()->balance, Auth::user()->balance-$renewCost, -1*$renewCost, '用户自行重置流量');

			return Response::json(['status' => 'success', 'data' => '', 'message' => '重置成功']);
		}
	}

	// 工单
	public function ticketList(Request $request)
	{
		$view['ticketList'] = Ticket::uid()->orderBy('id', 'desc')->paginate(10)->appends($request->except('page'));

		return Response::view('user.ticketList', $view);
	}

	// 订单
	public function invoices(Request $request)
	{
		$view['orderList'] = Order::uid()->with(['user', 'goods', 'coupon', 'payment'])->orderBy('oid', 'desc')->paginate(10)->appends($request->except('page'));

		return Response::view('user.invoices', $view);
	}

	public function activeOrder(Request $request)
	{
		$oid = $request->input('oid');
		$prepaidOrder = Order::query()->where('oid', $oid)->first();
		if(!$prepaidOrder){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '查无此单！']);
		}elseif($prepaidOrder->status != 3){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '非预支付订单，无需再次启动！']);
		}else{
			$this->activePrepaidOrder($oid);
		}

		return Response::json(['status' => 'success', 'data' => '', 'message' => '激活成功']);
	}

	// 订单明细
	public function invoiceDetail($sn)
	{
		$view['order'] = Order::uid()->with(['goods', 'coupon', 'payment'])->where('order_sn', $sn)->firstOrFail();

		return Response::view('user.invoiceDetail', $view);
	}

	// 添加工单
	public function addTicket(Request $request)
	{
		$title = $request->input('title');
		$content = clean($request->input('content'));
		$content = str_replace("eval", "", str_replace("atob", "", $content));

		if(empty($title) || empty($content)){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '请输入标题和内容']);
		}

		$obj = new Ticket();
		$obj->user_id = Auth::user()->id;
		$obj->title = $title;
		$obj->content = $content;
		$obj->status = 0;
		$obj->save();

		if($obj->id){
			$emailTitle = "新工单提醒";
			$content = "标题：【".$title."】<br>用户：".Auth::user()->email."<br>内容：".$content;

			// 发邮件通知管理员
			if(self::$systemConfig['webmaster_email']){
				$logId = Helpers::addNotificationLog($emailTitle, $content, 1, self::$systemConfig['webmaster_email']);
				Mail::to(self::$systemConfig['webmaster_email'])->send(new newTicket($logId, $emailTitle, $content));
			}

			PushNotification::send($emailTitle, $content);

			return Response::json(['status' => 'success', 'data' => '', 'message' => '提交成功']);
		}else{
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '提交失败']);
		}
	}

	// 回复工单
	public function replyTicket(Request $request)
	{
		$id = $request->input('id');

		$ticket = Ticket::uid()->with('user')->where('id', $id)->firstOrFail();

		if($request->isMethod('POST')){
			$content = clean($request->input('content'));
			$content = str_replace("eval", "", str_replace("atob", "", $content));
			$content = substr($content, 0, 300);

			if(empty($content)){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '回复内容不能为空']);
			}

			if($ticket->status == 2){
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '错误：该工单已关闭']);
			}

			$obj = new TicketReply();
			$obj->ticket_id = $id;
			$obj->user_id = Auth::user()->id;
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

				return Response::json(['status' => 'success', 'data' => '', 'message' => '回复成功']);
			}else{
				return Response::json(['status' => 'fail', 'data' => '', 'message' => '回复失败']);
			}
		}else{
			$view['ticket'] = $ticket;
			$view['replyList'] = TicketReply::query()->where('ticket_id', $id)->with('user')->orderBy('id', 'asc')->get();

			return Response::view('user.replyTicket', $view);
		}
	}

	// 关闭工单
	public function closeTicket(Request $request)
	{
		$id = $request->input('id');

		$ret = Ticket::uid()->where('id', $id)->update(['status' => 2]);
		if($ret){
			PushNotification::send('工单关闭提醒', '工单：ID'.$id.'用户已手动关闭');

			return Response::json(['status' => 'success', 'data' => '', 'message' => '关闭成功']);
		}else{
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '关闭失败']);
		}
	}

	// 邀请码
	public function invite()
	{
		if(Order::uid()->where('status', 2)->where('is_expire', 0)->where('origin_amount', '>', 0)->doesntExist()){
			return Response::view('auth.error', ['message' => '本功能对非付费用户禁用！请 <a class="btn btn-sm btn-danger" href="/">返 回</a>']);
		}

		$view['num'] = Auth::user()->invite_num; // 还可以生成的邀请码数量
		$view['inviteList'] = Invite::uid()->with(['generator', 'user'])->paginate(10); // 邀请码列表
		$view['referral_traffic'] = flowAutoShow(self::$systemConfig['referral_traffic']*1048576);
		$view['referral_percent'] = self::$systemConfig['referral_percent'];

		return Response::view('user.invite', $view);
	}

	// 生成邀请码
	public function makeInvite()
	{
		if(Auth::user()->invite_num <= 0){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '生成失败：已无邀请码生成名额']);
		}

		$obj = new Invite();
		$obj->uid = Auth::user()->id;
		$obj->fuid = 0;
		$obj->code = strtoupper(mb_substr(md5(microtime().makeRandStr()), 8, 12));
		$obj->status = 0;
		$obj->dateline = date('Y-m-d H:i:s', strtotime("+".self::$systemConfig['user_invite_days']." days"));
		$obj->save();

		User::uid()->decrement('invite_num', 1);

		return Response::json(['status' => 'success', 'data' => '', 'message' => '生成成功']);
	}

	// 使用优惠券
	public function redeemCoupon(Request $request)
	{
		$coupon_sn = $request->input('coupon_sn');
		$good_price = $request->input('price');

		if(empty($coupon_sn)){
			return Response::json(['status' => 'fail', 'title' => '使用失败', 'message' => '请输入您的优惠劵！']);
		}

		$coupon = Coupon::query()->where('sn', $coupon_sn)->whereIn('type', [1, 2])->first();
		if(!$coupon){
			return Response::json(['status' => 'fail', 'title' => '优惠券不存在', 'message' => '请确认优惠券是否输入正确！']);
		}elseif($coupon->status == 1){
			return Response::json(['status' => 'fail', 'title' => '抱歉', 'message' => '优惠券已被使用！']);
		}elseif($coupon->status == 2){
			return Response::json(['status' => 'fail', 'title' => '抱歉', 'message' => '优惠券已失效！']);
		}elseif($coupon->available_end < time()){
			$coupon->status = 2;
			$coupon->save();

			return Response::json(['status' => 'fail', 'title' => '抱歉', 'message' => '优惠券已失效！']);
		}elseif($coupon->available_start > time()){
			return Response::json(['status' => 'fail', 'title' => '优惠券尚未生效', 'message' => '请等待活动正式开启']);
		}elseif($good_price < $coupon->rule){
			return Response::json(['status' => 'fail', 'title' => '使用条件未满足', 'message' => '请购买价格更高的套餐']);
		}

		$data = ['name' => $coupon->name, 'type' => $coupon->type, 'amount' => $coupon->amount, 'discount' => $coupon->discount];

		return Response::json(['status' => 'success', 'data' => $data, 'message' => '优惠券有效']);
	}

	// 购买服务
	public function buy($goods_id)
	{
		$goods = Goods::query()->where('id', $goods_id)->where('status', 1)->first();
		if(empty($goods)){
			return Redirect::to('services');
		}
		// 有重置日时按照重置日为标准，否者就以过期日为标准
		$dataPlusDays = Auth::user()->reset_time? Auth::user()->reset_time : Auth::user()->expire_time;
		$view['dataPlusDays'] = $dataPlusDays > date('Y-m-d')? round((strtotime($dataPlusDays)-strtotime(date('Y-m-d')))/86400) : 0;
		$view['activePlan'] = Order::uid()->with(['goods'])->where('is_expire', 0)->where('status', 2)->whereHas('goods', function($q){ $q->where('type', 2); })->exists();

		$view['goods'] = $goods;

		return Response::view('user.buy', $view);
	}

	// 推广返利
	public function referral()
	{
		if(Order::uid()->where('status', 2)->where('is_expire', 0)->where('origin_amount', '>', 0)->doesntExist()){
			return Response::view('auth.error', ['message' => '本功能对非付费用户禁用！请 <a class="btn btn-sm btn-danger" href="/">返 回</a>']);
		}
		$view['referral_traffic'] = flowAutoShow(self::$systemConfig['referral_traffic']*1048576);
		$view['referral_percent'] = self::$systemConfig['referral_percent'];
		$view['referral_money'] = self::$systemConfig['referral_money'];
		$view['totalAmount'] = ReferralLog::uid()->sum('ref_amount')/100;
		$view['canAmount'] = ReferralLog::uid()->where('status', 0)->sum('ref_amount')/100;
		$view['link'] = self::$systemConfig['website_url'].'/register?aff='.Auth::user()->id;
		$view['referralLogList'] = ReferralLog::uid()->with('user')->orderBy('id', 'desc')->paginate(10, ['*'], 'log_page');
		$view['referralApplyList'] = ReferralApply::uid()->with('user')->orderBy('id', 'desc')->paginate(10, ['*'], 'apply_page');
		$view['referralUserList'] = User::query()->select(['email', 'created_at'])->where('referral_uid', Auth::user()->id)->orderBy('id', 'desc')->paginate(10, ['*'], 'user_page');

		return Response::view('user.referral', $view);
	}

	// 申请提现
	public function extractMoney()
	{
		// 判断账户是否过期
		if(Auth::user()->expire_time < date('Y-m-d')){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '申请失败：账号已过期，请先购买服务吧']);
		}

		// 判断是否已存在申请
		$referralApply = ReferralApply::uid()->whereIn('status', [0, 1])->first();
		if($referralApply){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '申请失败：已存在申请，请等待之前的申请处理完']);
		}

		// 校验可以提现金额是否超过系统设置的阀值
		$ref_amount = ReferralLog::uid()->where('status', 0)->sum('ref_amount');
		$ref_amount = $ref_amount/100;
		if($ref_amount < self::$systemConfig['referral_money']){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '申请失败：满'.self::$systemConfig['referral_money'].'元才可以提现，继续努力吧']);
		}

		// 取出本次申请关联返利日志ID
		$link_logs = '';
		$referralLog = ReferralLog::uid()->where('status', 0)->get();
		foreach($referralLog as $log){
			$link_logs .= $log->id.',';
		}
		$link_logs = rtrim($link_logs, ',');

		$obj = new ReferralApply();
		$obj->user_id = Auth::user()->id;
		$obj->before = $ref_amount;
		$obj->after = 0;
		$obj->amount = $ref_amount;
		$obj->link_logs = $link_logs;
		$obj->status = 0;
		$obj->save();

		return Response::json(['status' => 'success', 'data' => '', 'message' => '申请成功，请等待管理员审核']);
	}

	// 帮助中心
	public function help()
	{
		$view['articleList'] = Article::type(1)->orderBy('sort', 'desc')->orderBy('id', 'desc')->limit(10)->paginate(5);

		//付费用户判断
		$view['not_paying_user'] = Order::uid()->where('status', 2)->where('is_expire', 0)->where('origin_amount', '>', 0)->doesntExist();
		//客户端安装
		$view['Shadowrocket_install'] = 'itms-services://?action=download-manifest&url='.self::$systemConfig['website_url'].'/clients/Shadowrocket.plist';
		$view['Quantumult_install'] = 'itms-services://?action=download-manifest&url='.self::$systemConfig['website_url'].'/clients/Quantumult.plist';
		// 订阅连接
		$subscribe = UserSubscribe::query()->where('user_id', Auth::user()->id)->first();
		$view['subscribe_status'] = $subscribe->status;
		$subscribe_link = (self::$systemConfig['subscribe_domain']? self::$systemConfig['subscribe_domain'] : self::$systemConfig['website_url']).'/s/'.$subscribe->code;
		$view['link'] = $subscribe_link;
		$view['subscribe_link'] = 'sub://'.base64url_encode($subscribe_link);
		$view['Shadowrocket_link'] = 'shadowrocket://add/sub://'.base64url_encode($subscribe_link).'?remarks='.(self::$systemConfig['website_name'].'-'.self::$systemConfig['website_url']);
		$view['Shadowrocket_linkQrcode'] = 'sub://'.base64url_encode($subscribe_link).'#'.base64url_encode(self::$systemConfig['website_name']);
		$view['Quantumult_linkOut'] = 'quantumult://configuration?server='.base64url_encode($subscribe_link).'&filter='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Pro.conf').'&rejection='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Rejection.conf');
		$view['Quantumult_linkIn'] = 'quantumult://configuration?server='.base64url_encode($subscribe_link).'&filter='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/BacktoCN.conf').'&rejection='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Rejection.conf');

		return Response::view('user.help', $view);
	}

	// 更换订阅地址
	public function exchangeSubscribe()
	{
		DB::beginTransaction();
		try{
			// 更换订阅码
			UserSubscribe::uid()->update(['code' => Helpers::makeSubscribeCode()]);

			// 更换连接密码
			User::uid()->update(['passwd' => makeRandStr()]);

			DB::commit();

			return Response::json(['status' => 'success', 'data' => '', 'message' => '更换成功']);
		} catch(Exception $e){
			DB::rollBack();

			Log::info("更换订阅地址异常：".$e->getMessage());

			return Response::json(['status' => 'fail', 'data' => '', 'message' => '更换失败'.$e->getMessage()]);
		}
	}

	// 转换成管理员的身份
	public function switchToAdmin()
	{
		if(!Session::has('admin')){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => '非法请求']);
		}

		// 管理员信息重新写入user
		Auth::loginUsingId(Session::get('admin'));
		Session::forget('admin');

		return Response::json(['status' => 'success', 'data' => '', 'message' => "身份切换成功"]);
	}

	// 卡券余额充值
	public function charge(Request $request)
	{
		$validator = Validator::make($request->all(), ['coupon_sn' => ['required', Rule::exists('coupon', 'sn')->where(function($query){
			$query->where('type', 3)->where('status', 0);
		}),]], ['coupon_sn.required' => '券码不能为空', 'coupon_sn.exists' => '该券不可用']);

		if($validator->fails()){
			return Response::json(['status' => 'fail', 'data' => '', 'message' => $validator->getMessageBag()->first()]);
		}

		$coupon = Coupon::query()->where('sn', $request->input('coupon_sn'))->first();

		try{
			DB::beginTransaction();
			// 写入日志
			$this->addUserBalanceLog(Auth::user()->id, 0, Auth::user()->balance, Auth::user()->balance+$coupon->amount, $coupon->amount, '用户手动充值 - [充值券：'.$request->input('coupon_sn').']');

			// 余额充值
			User::uid()->increment('balance', $coupon->amount*100);

			// 更改卡券状态
			$coupon->status = 1;
			$coupon->save();

			// 写入卡券日志
			Helpers::addCouponLog($coupon->id, 0, 0, '账户余额充值使用');

			DB::commit();

			return Response::json(['status' => 'success', 'data' => '', 'message' => '充值成功']);
		} catch(Exception $e){
			Log::error($e);
			DB::rollBack();

			return Response::json(['status' => 'fail', 'data' => '', 'message' => '充值失败']);
		}
	}
}
