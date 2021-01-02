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

class UserController extends Controller
{
    public function index()
    {
        // 用户转换
        if (Session::has('user')) {
            auth()->loginUsingId(Session::get('user'));
            Session::forget('user');
        }
        $user = auth()->user();
        $totalTransfer = $user->transfer_enable;
        $usedTransfer = $user->usedTraffic();
        $unusedTraffic = $totalTransfer - $usedTransfer > 0 ? $totalTransfer - $usedTransfer : 0;
        $expireTime = $user->expired_at;

        $nodes = $user->nodes()->get();
        $subType = [];
        if ($nodes->whereIn('type', [1, 4])->isNotEmpty()) {
            $subType[] = 'ss';
        }
        if ($nodes->where('type', 2)->isNotEmpty()) {
            $subType[] = 'v2';
        }
        if ($nodes->where('type', 3)->isNotEmpty()) {
            $subType[] = 'trojan';
        }

        return view('user.index', array_merge([
            'remainDays' => $expireTime < date('Y-m-d') ? -1 : Helpers::daysToNow($expireTime),
            'resetDays' => $user->reset_time ? Helpers::daysToNow($user->reset_time) : 0,
            'unusedTraffic' => flowAutoShow($unusedTraffic),
            'expireTime' => $expireTime,
            'banedTime' => $user->ban_time ?: 0,
            'unusedPercent' => $totalTransfer > 0 ? round($unusedTraffic / $totalTransfer, 2) * 100 : 0,
            'announcements' => Article::type(2)->take(5)->latest()->Paginate(1), // 公告
            'isTrafficWarning' => $user->isTrafficWarning(), // 流量异常判断
            'paying_user' => $user->activePayingUser(), // 付费用户判断
            'userLoginLog' => $user->loginLogs()->latest()->first(), // 近期登录日志
            'subscribe_status' => $user->subscribe->status,
            'subType' => $subType,
            'subUrl' => route('sub', $user->subscribe->code),
        ], $this->dataFlowChart($user->id)));
    }

    // 签到
    public function checkIn(): JsonResponse
    {
        $user = auth()->user();
        // 系统开启登录加积分功能才可以签到
        if (! sysConfig('is_checkin')) {
            return Response::json(['status' => 'fail', 'message' => '系统未开启签到功能']);
        }

        // 已签到过，验证是否有效
        if (Cache::has('userCheckIn_'.$user->id)) {
            return Response::json(['status' => 'fail', 'message' => '已经签到过了，明天再来吧']);
        }

        $traffic = random_int((int) sysConfig('min_rand_traffic'), (int) sysConfig('max_rand_traffic')) * MB;

        if (! $user->incrementData($traffic)) {
            return Response::json(['status' => 'fail', 'message' => '签到失败，系统异常']);
        }

        // 写入用户流量变动记录
        Helpers::addUserTrafficModifyLog($user->id, null, $user->transfer_enable, $user->transfer_enable + $traffic, '[签到]');

        // 多久后可以再签到
        $ttl = sysConfig('traffic_limit_time') ? sysConfig('traffic_limit_time') * Minute : Day;
        Cache::put('userCheckIn_'.$user->id, '1', $ttl);

        return Response::json(['status' => 'success', 'message' => '签到成功，系统送您 '.flowAutoShow($traffic).'流量']);
    }

    // 节点列表
    public function nodeList(Request $request)
    {
        $user = auth()->user();
        if ($request->isMethod('POST')) {
            $server = Node::findOrFail($request->input('id'))->config($user); // 提取节点信息

            return Response::json(['status' => 'success', 'data' => $this->getUserNodeInfo($server, $request->input('type') !== 'text'), 'title' => $server['type']]);
        }

        // 获取当前用户可用节点
        $nodeList = $user->nodes()->with(['labels', 'level_table'])->get();
        $onlineNode = NodeHeartBeat::recently()->distinct()->pluck('node_id')->toArray();
        $pingNodeLogs = NodePing::whereMonth('created_at', date('m'))->get(['node_id', 'ct', 'cu', 'cm', 'hk']);
        foreach ($nodeList as $node) {
            $data = $pingNodeLogs->where('node_id', $node->id);
            $node->ct = round($data->pluck('ct')->filter()->avg(), 2);
            $node->cu = round($data->pluck('cu')->filter()->avg(), 2);
            $node->cm = round($data->pluck('cm')->filter()->avg(), 2);
            $node->hk = round($data->pluck('hk')->filter()->avg(), 2);

            // 节点在线状态
            $node->offline = ! in_array($node->id, $onlineNode, true);
        }

        return view('user.nodeList', [
            'nodesGeo' => $nodeList->pluck('name', 'geo')->toArray(),
            'nodeList' => $nodeList,
        ]);
    }

    // 公告详情
    public function article(Article $article)
    {
        return view('user.article', compact($article));
    }

    // 修改个人资料
    public function profile(Request $request)
    {
        $user = auth()->user();
        if ($request->isMethod('POST')) {
            $old_password = $request->input('old_password');
            $new_password = $request->input('new_password');
            $username = $request->input('username');
            $wechat = $request->input('wechat');
            $qq = $request->input('qq');
            $passwd = $request->input('passwd');

            // 修改密码
            if ($old_password && $new_password) {
                if (! Hash::check($old_password, $user->password)) {
                    return Redirect::back()->withErrors('旧密码错误，请重新输入');
                }

                if (Hash::check($new_password, $user->password)) {
                    return Redirect::back()->withErrors('新密码不可与旧密码一样，请重新输入');
                }

                // 演示环境禁止改管理员密码
                if ($user->id === 1 && config('app.demo')) {
                    return Redirect::back()->withErrors('演示环境禁止修改管理员密码');
                }

                if (! $user->update(['password' => $new_password])) {
                    return Redirect::back()->withErrors('修改失败');
                }

                return Redirect::back()->with('successMsg', '修改成功');
                // 修改代理密码
            }

            if ($passwd) {
                if (! $user->update(['passwd' => $passwd])) {
                    return Redirect::back()->withErrors('修改失败');
                }

                return Redirect::back()->with('successMsg', '修改成功');
            }

            // 修改联系方式
            if (empty($username)) {
                return Redirect::back()->withErrors('修改失败,昵称不能为空值');
            }

            if (! $user->update(['username' => $username, 'wechat' => $wechat, 'qq' => $qq])) {
                return Redirect::back()->withErrors('修改失败');
            }

            return Redirect::back()->with('successMsg', '修改成功');
        }

        return view('user.profile');
    }

    // 商品列表
    public function services(Request $request)
    {
        $user = auth()->user();
        // 余额充值商品，只取10个
        $renewOrder = Order::userActivePlan($user->id)->first();
        $renewPrice = $renewOrder->goods ?? 0;
        // 有重置日时按照重置日为标准，否者就以过期日为标准
        $dataPlusDays = $user->reset_time ?? $user->expired_at;

        return view('user.services', [
            'chargeGoodsList' => Goods::type(3)->whereStatus(1)->orderBy('price')->limit(10)->get(),
            'goodsList' => Goods::whereStatus(1)->where('type', '<=', '2')->orderByDesc('type')->orderByDesc('sort')->paginate(10)->appends($request->except('page')),
            'renewTraffic' => $renewPrice->renew ?? 0,
            'dataPlusDays' => $dataPlusDays > date('Y-m-d') ? Helpers::daysToNow($dataPlusDays) : 0,
        ]);
    }

    //重置流量
    public function resetUserTraffic(): ?JsonResponse
    {
        $user = auth()->user();
        $order = Order::userActivePlan()->firstOrFail();
        $renewCost = $order->goods->renew;
        if ($user->credit < $renewCost) {
            return Response::json(['status' => 'fail', 'message' => '余额不足，请充值余额']);
        }

        $user->update(['u' => 0, 'd' => 0]);

        // 扣余额
        $user->updateCredit(-$renewCost);

        // 记录余额操作日志
        Helpers::addUserCreditLog($user->id, null, $user->credit, $user->credit - $renewCost, -1 * $renewCost, '用户自行重置流量');

        return Response::json(['status' => 'success', 'message' => '重置成功']);
    }

    // 工单
    public function ticketList(Request $request)
    {
        return view('user.ticketList', [
            'tickets' => auth()->user()->tickets()->latest()->paginate(10)->appends($request->except('page')),
        ]);
    }

    // 订单
    public function invoices(Request $request)
    {
        return view('user.invoices', [
            'orderList' => auth()->user()->orders()->with(['goods', 'payment'])->orderByDesc('id')->paginate(10)->appends($request->except('page')),
            'prepaidPlan' => Order::userPrepay()->exists(),
        ]);
    }

    public function closePlan(): JsonResponse
    {
        $activePlan = Order::userActivePlan()->firstOrFail();
        $activePlan->is_expire = 1;

        if ($activePlan->save()) {
            // 关闭先前套餐后，新套餐自动运行
            if (Order::userActivePlan()->exists()) {
                return Response::json(['status' => 'success', 'message' => '激活成功']);
            }

            return Response::json(['status' => 'success', 'message' => '关闭']);
        }

        return Response::json(['status' => 'fail', 'message' => '关闭失败']);
    }

    // 订单明细
    public function invoiceDetail($sn)
    {
        return view('user.invoiceDetail', ['order' => Order::uid()->whereOrderSn($sn)->with(['goods', 'coupon', 'payment'])->firstOrFail()]);
    }

    // 添加工单
    public function createTicket(Request $request): ?JsonResponse
    {
        $user = auth()->user();
        $title = $request->input('title');
        $content = clean($request->input('content'));
        $content = str_replace(['atob', 'eval'], '', $content);

        if (empty($title) || empty($content)) {
            return Response::json(['status' => 'fail', 'message' => '请输入标题和内容']);
        }

        $obj = new Ticket();
        $obj->user_id = $user->id;
        $obj->title = $title;
        $obj->content = $content;

        if ($obj->save()) {
            $emailTitle = '新工单提醒';
            $content = '标题：【'.$title.'】<br>用户：'.$user->email.'<br>内容：'.$content;

            // 发邮件通知管理员
            if (sysConfig('webmaster_email')) {
                $logId = Helpers::addNotificationLog($emailTitle, $content, 1, sysConfig('webmaster_email'));
                Mail::to(sysConfig('webmaster_email'))->send(new newTicket($logId, $emailTitle, $content));
            }

            PushNotification::send($emailTitle, $content);

            return Response::json(['status' => 'success', 'message' => '提交成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '提交失败']);
    }

    // 回复工单
    public function replyTicket(Request $request)
    {
        $id = $request->input('id');

        $ticket = Ticket::uid()->with('user')->whereId($id)->firstOrFail();

        if ($request->isMethod('POST')) {
            $content = substr(str_replace(['atob', 'eval'], '', clean($request->input('content'))), 0, 300);

            if (empty($content)) {
                return Response::json(['status' => 'fail', 'message' => '回复内容不能为空']);
            }

            if ($ticket->status === 2) {
                return Response::json(['status' => 'fail', 'message' => '错误：该工单已关闭']);
            }

            $obj = new TicketReply();
            $obj->ticket_id = $id;
            $obj->user_id = auth()->user()->id;
            $obj->content = $content;

            if ($obj->save()) {
                // 重新打开工单
                $ticket->status = 0;
                $ticket->save();

                $title = '工单回复提醒';
                $content = '标题：【'.$ticket->title.'】<br>用户回复：'.$content;

                // 发邮件通知管理员
                if (sysConfig('webmaster_email')) {
                    $logId = Helpers::addNotificationLog($title, $content, 1, sysConfig('webmaster_email'));
                    Mail::to(sysConfig('webmaster_email'))->send(new replyTicket($logId, $title, $content));
                }

                PushNotification::send($title, $content);

                return Response::json(['status' => 'success', 'message' => '回复成功']);
            }

            return Response::json(['status' => 'fail', 'message' => '回复失败']);
        }

        return view('user.replyTicket', [
            'ticket' => $ticket,
            'replyList' => $ticket->reply()->with('user')->oldest()->get(),
        ]);
    }

    // 关闭工单
    public function closeTicket(Request $request): ?JsonResponse
    {
        $id = $request->input('id');

        if (Ticket::uid()->whereId($id)->close()) {
            PushNotification::send('工单关闭提醒', '工单：ID'.$id.'用户已手动关闭');

            return Response::json(['status' => 'success', 'message' => '关闭成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '关闭失败']);
    }

    // 邀请码
    public function invite()
    {
        if (Order::uid()->active()->where('origin_amount', '>', 0)->doesntExist()) {
            return Response::view(
                'auth.error',
                ['message' => '本功能对非付费用户禁用！请 <a class="btn btn-sm btn-danger" href="/">返 回</a>'],
                402
            );
        }

        return view('user.invite', [
            'num' => auth()->user()->invite_num, // 还可以生成的邀请码数量
            'inviteList' => Invite::uid()->with(['invitee', 'inviter'])->paginate(10), // 邀请码列表
            'referral_traffic' => flowAutoShow(sysConfig('referral_traffic') * MB),
            'referral_percent' => sysConfig('referral_percent'),
        ]);
    }

    // 生成邀请码
    public function makeInvite(): JsonResponse
    {
        $user = auth()->user();
        if ($user->invite_num <= 0) {
            return Response::json(['status' => 'fail', 'message' => '生成失败：已无邀请码生成名额']);
        }

        $obj = new Invite();
        $obj->inviter_id = $user->id;
        $obj->code = strtoupper(mb_substr(md5(microtime().Str::random()), 8, 12));
        $obj->dateline = date('Y-m-d H:i:s', strtotime('+'.sysConfig('user_invite_days').' days'));
        $obj->save();
        if ($obj) {
            $user->update(['invite_num' => $user->invite_num - 1]);

            return Response::json(['status' => 'success', 'message' => '生成成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '生成邀请码失败']);
    }

    // 使用优惠券
    public function redeemCoupon(Request $request): JsonResponse
    {
        $coupon_sn = $request->input('coupon_sn');
        $good_price = $request->input('price');

        if (empty($coupon_sn)) {
            return Response::json(['status' => 'fail', 'title' => '使用失败', 'message' => '请输入您的优惠劵！']);
        }

        $coupon = Coupon::whereSn($coupon_sn)->whereIn('type', [1, 2])->first();
        if (! $coupon) {
            return Response::json(['status' => 'fail', 'title' => '优惠券不存在', 'message' => '请确认优惠券是否输入正确！']);
        }

        if ($coupon->status === 1) {
            return Response::json(['status' => 'fail', 'title' => '抱歉', 'message' => '优惠券已被使用！']);
        }

        if ($coupon->status === 2) {
            return Response::json(['status' => 'fail', 'title' => '抱歉', 'message' => '优惠券已失效！']);
        }

        if ($coupon->end_time < time()) {
            $coupon->status = 2;
            $coupon->save();

            return Response::json(['status' => 'fail', 'title' => '抱歉', 'message' => '优惠券已失效！']);
        }

        if ($coupon->start_time > date('Y-m-d H:i:s')) {
            return Response::json(['status' => 'fail', 'title' => '优惠券尚未生效', 'message' => '请等待活动正式开启']);
        }

        if ($good_price < $coupon->rule) {
            return Response::json(['status' => 'fail', 'title' => '使用条件未满足', 'message' => '请购买价格更高的套餐']);
        }

        $data = [
            'name' => $coupon->name,
            'type' => $coupon->type,
            'value' => $coupon->value,
        ];

        return Response::json(['status' => 'success', 'data' => $data, 'message' => '优惠券有效']);
    }

    // 购买服务
    public function buy(Goods $good)
    {
        $user = auth()->user();
        // 有重置日时按照重置日为标准，否者就以过期日为标准
        $dataPlusDays = $user->reset_time ?? $user->expired_at;

        return view('user.buy', [
            'dataPlusDays' => $dataPlusDays > date('Y-m-d') ? Helpers::daysToNow($dataPlusDays) : 0,
            'activePlan' => Order::userActivePlan()->exists(),
            'goods' => $good,
        ]);
    }

    // 帮助中心
    public function help()
    {
        //$view['articleList'] = Article::type(1)->orderByDesc('sort')->latest()->limit(10)->paginate(5);
        $data = [];
        if (Node::whereIn('type', [1, 4])->whereStatus(1)->exists()) {
            $data[] = 'ss';
            //array_push
        }
        if (Node::whereType(2)->whereStatus(1)->exists()) {
            $data[] = 'v2';
        }
        if (Node::whereType(3)->whereStatus(1)->exists()) {
            $data[] = 'trojan';
        }

        $subscribe = auth()->user()->subscribe;
        $subscribe_link = route('sub', $subscribe->code);

        return view('user.help', [
            'sub' => $data,
            'paying_user' => auth()->user()->activePayingUser(), // 付费用户判断
            'Shadowrocket_install' => 'itms-services://?action=download-manifest&url='.sysConfig('website_url').'/clients/Shadowrocket.plist', // 客户端安装
            'Quantumult_install' => 'itms-services://?action=download-manifest&url='.sysConfig('website_url').'/clients/Quantumult.plist', // 客户端安装
            'subscribe_status' => $subscribe->status, // 订阅连接
            'link' => $subscribe_link,
            'subscribe_link' => 'sub://'.base64url_encode($subscribe_link),
            'Shadowrocket_link' => 'shadowrocket://add/sub://'.base64url_encode($subscribe_link).'?remarks='.urlencode(sysConfig('website_name').' '.sysConfig('website_url')),
            'Shadowrocket_linkQrcode' => 'sub://'.base64url_encode($subscribe_link).'#'.base64url_encode(sysConfig('website_name')),
            'Clash_link' => "clash://install-config?url={$subscribe_link}",
            'Surge_link' => "surge:///install-config?url={$subscribe_link}",
            'Quantumultx' => 'quantumult-x:///update-configuration?remote-resource='.json_encode([
                'server_remote' => "{$subscribe_link},  tag=".urlencode(sysConfig('website_name').' '.sysConfig('website_url')),
                'filter_remote' => '',
                'rewrite_remote' => '',
            ]),
            'Quantumult_linkOut' => 'quantumult://configuration?server='.base64url_encode($subscribe_link).'&filter='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Pro.conf').'&rejection='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Rejection.conf'),
            'Quantumult_linkIn' => 'quantumult://configuration?server='.base64url_encode($subscribe_link).'&filter='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/BacktoCN.conf').'&rejection='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Rejection.conf'),
        ]);
    }

    // 更换订阅地址
    public function exchangeSubscribe(): ?JsonResponse
    {
        try {
            DB::beginTransaction();

            // 更换订阅码
            auth()->user()->subscribe->update(['code' => Helpers::makeSubscribeCode()]);

            // 更换连接信息
            auth()->user()->update(['passwd' => Str::random(), 'vmess_id' => Str::uuid()]);

            DB::commit();

            return Response::json(['status' => 'success', 'message' => '更换成功']);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('更换订阅地址异常：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '更换失败'.$e->getMessage()]);
        }
    }

    // 转换成管理员的身份
    public function switchToAdmin(): JsonResponse
    {
        if (! Session::has('admin')) {
            return Response::json(['status' => 'fail', 'message' => '非法请求']);
        }

        // 管理员信息重新写入user
        $user = auth()->loginUsingId(Session::get('admin'));
        Session::forget('admin');
        if ($user) {
            return Response::json(['status' => 'success', 'message' => '身份切换成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '身份切换失败']);
    }

    public function charge(Request $request): ?JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'coupon_sn' => [
                'required', Rule::exists('coupon', 'sn')->where(static function ($query) {
                    $query->whereType(3)->whereStatus(0);
                }),
            ],
        ], ['coupon_sn.required' => '券码不能为空', 'coupon_sn.exists' => '该券不可用']);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        $coupon = Coupon::whereSn($request->input('coupon_sn'))->firstOrFail();

        try {
            DB::beginTransaction();
            // 写入日志
            $user = auth()->user();
            Helpers::addUserCreditLog($user->id, null, $user->credit, $user->credit + $coupon->value, $coupon->value, '用户手动充值 - [充值券：'.$request->input('coupon_sn').']');

            // 余额充值
            $user->updateCredit($coupon->value);

            // 更改卡券状态
            $coupon->update(['status' => 1]);

            // 写入卡券日志
            Helpers::addCouponLog('账户余额充值使用', $coupon->id);

            DB::commit();

            return Response::json(['status' => 'success', 'message' => '充值成功']);
        } catch (Exception $e) {
            Log::error('卡劵充值错误：'.$e->getMessage());
            DB::rollBack();

            return Response::json(['status' => 'fail', 'message' => '充值失败']);
        }
    }
}
