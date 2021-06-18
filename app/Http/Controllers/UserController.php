<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Models\Article;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\Invite;
use App\Models\Node;
use App\Models\NodeHeartbeat;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreated;
use App\Notifications\TicketReplied;
use Cache;
use DB;
use Exception;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;
use Notification;
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
            'remainDays'       => $expireTime < date('Y-m-d') ? -1 : Helpers::daysToNow($expireTime),
            'resetDays'        => $user->reset_time ? Helpers::daysToNow($user->reset_time) : 0,
            'unusedTraffic'    => flowAutoShow($unusedTraffic),
            'expireTime'       => $expireTime,
            'banedTime'        => $user->ban_time,
            'unusedPercent'    => $totalTransfer > 0 ? round($unusedTraffic / $totalTransfer, 2) * 100 : 0,
            'announcements'    => Article::type(2)->take(5)->latest()->Paginate(1), // 公告
            'isTrafficWarning' => $user->isTrafficWarning(), // 流量异常判断
            'paying_user'      => $user->activePayingUser(), // 付费用户判断
            'userLoginLog'     => $user->loginLogs()->latest()->first(), // 近期登录日志
            'subscribe_status' => $user->subscribe->status,
            'subType'          => $subType,
            'subUrl'           => route('sub', $user->subscribe->code),
        ], $this->dataFlowChart($user->id)));
    }

    // 签到
    public function checkIn(): JsonResponse
    {
        $user = auth()->user();
        // 系统开启登录加积分功能才可以签到
        if (! sysConfig('is_checkin')) {
            return Response::json(['status' => 'fail', 'title' => trans('common.failed'), 'message' => trans('user.home.attendance.disable')]);
        }

        // 已签到过，验证是否有效
        if (Cache::has('userCheckIn_'.$user->id)) {
            return Response::json(['status' => 'success', 'title' => trans('common.success'), 'message' => trans('user.home.attendance.done')]);
        }

        $traffic = random_int((int) sysConfig('min_rand_traffic'), (int) sysConfig('max_rand_traffic')) * MB;

        if (! $user->incrementData($traffic)) {
            return Response::json(['status' => 'fail', 'title' => trans('common.failed'), 'message' => trans('user.home.attendance.failed')]);
        }

        // 写入用户流量变动记录
        Helpers::addUserTrafficModifyLog($user->id, null, $user->transfer_enable, $user->transfer_enable + $traffic, trans('user.home.attendance.attribute'));

        // 多久后可以再签到
        $ttl = sysConfig('traffic_limit_time') ? sysConfig('traffic_limit_time') * Minute : Day;
        Cache::put('userCheckIn_'.$user->id, '1', $ttl);

        return Response::json(['status' => 'success', 'message' => trans('user.home.attendance.success', ['data' => flowAutoShow($traffic)])]);
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
        $onlineNode = NodeHeartbeat::recently()->distinct()->pluck('node_id')->toArray();
        foreach ($nodeList as $node) {
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
        return view('user.article', compact('article'));
    }

    // 修改个人资料
    public function profile(Request $request)
    {
        $user = auth()->user();
        if ($request->isMethod('POST')) {
            // 修改密码
            if ($request->has(['password', 'new_password'])) {
                $data = $request->only(['password', 'new_password']);

                if (! Hash::check($data['password'], $user->password)) {
                    return Redirect::back()->withErrors(trans('auth.password.reset.error.wrong'));
                }

                if (Hash::check($data['new_password'], $user->password)) {
                    return Redirect::back()->withErrors(trans('auth.password.reset.error.same'));
                }

                // 演示环境禁止改管理员密码
                if ($user->id === 1 && config('app.demo')) {
                    return Redirect::back()->withErrors(trans('auth.password.reset.error.demo'));
                }

                if (! $user->update(['password' => $data['new_password']])) {
                    return Redirect::back()->withErrors(trans('common.update_action', ['action' => trans('common.failed')]));
                }

                return Redirect::back()->with('successMsg', trans('common.update_action', ['action' => trans('common.success')]));
                // 修改代理密码
            }

            if ($request->has('passwd')) {
                $passwd = $request->input('passwd');
                if (! $user->update(['passwd' => $passwd])) {
                    return Redirect::back()->withErrors(trans('common.update_action', ['action' => trans('common.failed')]));
                }

                return Redirect::back()->with('successMsg', trans('common.update_action', ['action' => trans('common.success')]));
            }

            // 修改联系方式
            if ($request->has(['username', 'wechat', 'qq'])) {
                $data = $request->only(['username', 'wechat', 'qq']);
                if (empty($data['username'])) {
                    return Redirect::back()->withErrors(trans('validation.required', ['attribute' => trans('validation.attributes.username')]));
                }

                if (! $user->update($data)) {
                    return Redirect::back()->withErrors(trans('common.update_action', ['action' => trans('common.failed')]));
                }
            }

            return Redirect::back()->with('successMsg', trans('common.update_action', ['action' => trans('common.success')]));
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
        // 有重置日时按照重置日为标准，否则就以过期日为标准
        $dataPlusDays = $user->reset_time ?? $user->expired_at;

        return view('user.services', [
            'chargeGoodsList' => Goods::type(3)->whereStatus(1)->orderBy('price')->limit(10)->get(),
            'goodsList'       => Goods::whereStatus(1)->where('type', '<=', '2')->orderByDesc('type')->orderByDesc('sort')->paginate(10)->appends($request->except('page')),
            'renewTraffic'    => $renewPrice->renew ?? 0,
            'dataPlusDays'    => $dataPlusDays > date('Y-m-d') ? Helpers::daysToNow($dataPlusDays) : 0,
        ]);
    }

    //重置流量
    public function resetUserTraffic(): ?JsonResponse
    {
        $user = auth()->user();
        $order = Order::userActivePlan()->firstOrFail();
        $renewCost = $order->goods->renew;
        if ($user->credit < $renewCost) {
            return Response::json(['status' => 'fail', 'message' => trans('user.reset_data.lack')]);
        }

        $user->update(['u' => 0, 'd' => 0]);

        // 扣余额
        $user->updateCredit(-$renewCost);

        // 记录余额操作日志
        Helpers::addUserCreditLog($user->id, null, $user->credit, $user->credit - $renewCost, -1 * $renewCost, trans('user.reset_data.logs'));

        return Response::json(['status' => 'success', 'message' => trans('user.reset_data.success')]);
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
            'orderList'   => auth()->user()->orders()->with(['goods', 'payment'])->orderByDesc('id')->paginate(10)->appends($request->except('page')),
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
                return Response::json(['status' => 'success', 'message' => trans('common.active_item', ['attribute' => trans('common.success')])]);
            }

            return Response::json(['status' => 'success', 'message' => trans('common.close')]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.close_item', ['attribute' => trans('common.failed')])]);
    }

    // 订单明细
    public function invoiceDetail($sn)
    {
        return view('user.invoiceDetail', ['order' => Order::uid()->whereSn($sn)->with(['goods', 'coupon', 'payment'])->firstOrFail()]);
    }

    // 添加工单
    public function createTicket(Request $request): ?JsonResponse
    {
        $user = auth()->user();
        $title = $request->input('title');
        $content = substr(str_replace(['atob', 'eval'], '', clean($request->input('content'))), 0, 300);

        if (empty($title) || empty($content)) {
            return Response::json([
                'status' => 'fail', 'message' => trans('validation.required', ['attribute' => trans('validation.attributes.title').'&'.trans('validation.attributes.content')]),
            ]);
        }

        if ($ticket = $user->tickets()->create(compact('title', 'content'))) {
            // 通知相关管理员
            Notification::send(User::find(1), new TicketCreated($ticket->title, $ticket->content, route('admin.ticket.edit', $ticket)));

            return Response::json(['status' => 'success', 'message' => trans('common.submit_item', ['attribute' => trans('common.success')])]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.submit_item', ['attribute' => trans('common.failed')])]);
    }

    // 回复工单
    public function replyTicket(Request $request)
    {
        $id = $request->input('id');

        $ticket = Ticket::uid()->with('user')->whereId($id)->firstOrFail();

        if ($request->isMethod('POST')) {
            $content = substr(str_replace(['atob', 'eval'], '', clean($request->input('content'))), 0, 300);

            if (empty($content)) {
                return Response::json([
                    'status' => 'fail', 'message' => trans('validation.required', ['attribute' => trans('validation.attributes.title').'&'.trans('validation.attributes.content')]),
                ]);
            }

            if ($ticket->status === 2) {
                return Response::json(['status' => 'fail', 'message' => trans('user.ticket.failed_closed')]);
            }

            if ($ticket->reply()->create(['user_id' => auth()->id(), 'content' => $content])) {
                // 重新打开工单
                $ticket->status = 0;
                $ticket->save();

                // 通知相关管理员
                Notification::send(User::find(1), new TicketReplied($ticket->title, $content, route('admin.ticket.edit', $ticket)));

                return Response::json(['status' => 'success', 'message' => trans('user.ticket.reply').trans('common.success')]);
            }

            return Response::json(['status' => 'fail', 'message' => trans('user.ticket.reply').trans('common.failed')]);
        }

        return view('user.replyTicket', [
            'ticket'    => $ticket,
            'replyList' => $ticket->reply()->with('user')->oldest()->get(),
        ]);
    }

    // 关闭工单
    public function closeTicket(Request $request): ?JsonResponse
    {
        $id = $request->input('id');

        if (Ticket::uid()->whereId($id)->firstOrFail()->close()) {
            return Response::json(['status' => 'success', 'message' => trans('common.close_item', ['attribute' => trans('common.success')])]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.close_item', ['attribute' => trans('common.failed')])]);
    }

    // 邀请码
    public function invite()
    {
        if (Order::uid()->active()->where('origin_amount', '>', 0)->doesntExist()) {
            return Response::view(
                'auth.error',
                ['message' => trans('user.purchase_required').' <a class="btn btn-sm btn-danger" href="/">'.trans('common.back').'</a>'],
                402
            );
        }

        return view('user.invite', [
            'num'              => auth()->user()->invite_num, // 还可以生成的邀请码数量
            'inviteList'       => Invite::uid()->with(['invitee', 'inviter'])->paginate(10), // 邀请码列表
            'referral_traffic' => flowAutoShow(sysConfig('referral_traffic') * MB),
            'referral_percent' => sysConfig('referral_percent'),
        ]);
    }

    // 生成邀请码
    public function makeInvite(): JsonResponse
    {
        $user = auth()->user();
        if ($user->invite_num <= 0) {
            return Response::json(['status' => 'fail', 'message' => trans('user.invite.generate_failed')]);
        }

        $obj = new Invite();
        $obj->inviter_id = $user->id;
        $obj->code = strtoupper(mb_substr(md5(microtime().Str::random()), 8, 12));
        $obj->dateline = date('Y-m-d H:i:s', strtotime(sysConfig('user_invite_days').' days'));
        $obj->save();
        if ($obj) {
            $user->update(['invite_num' => $user->invite_num - 1]);

            return Response::json(['status' => 'success', 'message' => trans('common.generate_item', ['attribute' => trans('common.success')])]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.generate_item', ['attribute' => trans('common.failed')])]);
    }

    // 使用优惠券
    public function redeemCoupon(Request $request): JsonResponse
    {
        $coupon_sn = $request->input('coupon_sn');
        $good_price = $request->input('price');

        if (empty($coupon_sn)) {
            return Response::json([
                'status' => 'fail', 'title' => trans('common.failed'), 'message' => trans('validation.required', ['attribute' => trans('user.coupon.attribute')]),
            ]);
        }

        $coupon = Coupon::whereSn($coupon_sn)->whereIn('type', [1, 2])->first();
        if (! $coupon) {
            return Response::json(['status' => 'fail', 'title' => trans('common.failed'), 'message' => trans('user.unknown').trans('user.coupon.attribute')]);
        }

        if ($coupon->status === 1) {
            return Response::json(['status' => 'fail', 'title' => trans('common.sorry'), 'message' => trans('user.coupon.attribute').trans('user.status.used')]);
        }

        if ($coupon->status === 2) {
            return Response::json(['status' => 'fail', 'title' => trans('common.sorry'), 'message' => trans('user.coupon.attribute').trans('user.status.expired')]);
        }

        if ($coupon->getRawOriginal('end_time') < time()) {
            $coupon->status = 2;
            $coupon->save();

            return Response::json(['status' => 'fail', 'title' => trans('common.sorry'), 'message' => trans('user.coupon.attribute').trans('user.status.expired')]);
        }

        if ($coupon->start_time > date('Y-m-d H:i:s')) {
            return Response::json(['status' => 'fail', 'title' => trans('user.coupon.inactive'), 'message' => trans('user.coupon.wait_active', ['time' => $coupon->start_time])]);
        }

        if ($good_price < $coupon->rule) {
            return Response::json(['status' => 'fail', 'title' => trans('user.coupon.limit'), 'message' => trans('user.coupon.higher', ['amount' => $coupon->rule])]);
        }

        $data = [
            'name'  => $coupon->name,
            'type'  => $coupon->type,
            'value' => $coupon->value,
        ];

        return Response::json(['status' => 'success', 'data' => $data, 'message' => trans('common.applied', ['attribute' => trans('user.coupon.attribute')])]);
    }

    // 购买服务
    public function buy(Goods $good)
    {
        $user = auth()->user();
        // 有重置日时按照重置日为标准，否则就以过期日为标准
        $dataPlusDays = $user->reset_time ?? $user->expired_at;

        return view('user.buy', [
            'dataPlusDays' => $dataPlusDays > date('Y-m-d') ? Helpers::daysToNow($dataPlusDays) : 0,
            'activePlan'   => Order::userActivePlan()->exists(),
            'goods'        => $good,
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
            'sub'                     => $data,
            'paying_user'             => auth()->user()->activePayingUser(), // 付费用户判断
            'Shadowrocket_install'    => 'itms-services://?action=download-manifest&url='.sysConfig('website_url').'/clients/Shadowrocket.plist', // 客户端安装
            'Quantumult_install'      => 'itms-services://?action=download-manifest&url='.sysConfig('website_url').'/clients/Quantumult.plist', // 客户端安装
            'subscribe_status'        => $subscribe->status, // 订阅连接
            'link'                    => $subscribe_link,
            'subscribe_link'          => 'sub://'.base64url_encode($subscribe_link),
            'Shadowrocket_link'       => 'shadowrocket://add/sub://'.base64url_encode($subscribe_link).'?remarks='.urlencode(sysConfig('website_name').' '.sysConfig('website_url')),
            'Shadowrocket_linkQrcode' => 'sub://'.base64url_encode($subscribe_link).'#'.base64url_encode(sysConfig('website_name')),
            'Clash_link'              => "clash://install-config?url={$subscribe_link}",
            'Surge_link'              => "surge:///install-config?url={$subscribe_link}",
            'Quantumultx'             => 'quantumult-x:///update-configuration?remote-resource='.json_encode([
                'server_remote'  => "{$subscribe_link},  tag=".urlencode(sysConfig('website_name').' '.sysConfig('website_url')),
                'filter_remote'  => '',
                'rewrite_remote' => '',
            ]),
            'Quantumult_linkOut'      => 'quantumult://configuration?server='.base64url_encode($subscribe_link).'&filter='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Pro.conf').'&rejection='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Rejection.conf'),
            'Quantumult_linkIn'       => 'quantumult://configuration?server='.base64url_encode($subscribe_link).'&filter='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/BacktoCN.conf').'&rejection='.base64url_encode('https://raw.githubusercontent.com/ZBrettonYe/VPN-Rules-Collection/master/Profiles/Quantumult/Rejection.conf'),
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

            return Response::json(['status' => 'success', 'message' => trans('common.replace').trans('common.success')]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error(trans('user.subscribe.error').'：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => trans('common.replace').trans('common.failed').$e->getMessage()]);
        }
    }

    // 转换成管理员的身份
    public function switchToAdmin(): JsonResponse
    {
        if (! Session::has('admin')) {
            return Response::json(['status' => 'fail', 'message' => trans('error.unauthorized')]);
        }

        // 管理员信息重新写入user
        $user = auth()->loginUsingId(Session::get('admin'));
        Session::forget('admin');
        if ($user) {
            return Response::json(['status' => 'success', 'message' => trans('common.toggle_action', ['action' => trans('common.success')])]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.toggle_action', ['action' => trans('common.failed')])]);
    }

    public function charge(Request $request): ?JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'coupon_sn' => [
                'required', Rule::exists('coupon', 'sn')->where(static function ($query) {
                    $query->whereType(3)->whereStatus(0);
                }),
            ],
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        $coupon = Coupon::whereSn($request->input('coupon_sn'))->firstOrFail();

        try {
            DB::beginTransaction();
            // 写入日志
            $user = auth()->user();
            Helpers::addUserCreditLog($user->id, null, $user->credit, $user->credit + $coupon->value, $coupon->value,
                trans('user.recharge').' - ['.trans('user.coupon.recharge').'：'.$request->input('coupon_sn').']');

            // 余额充值
            $user->updateCredit($coupon->value);

            // 更改卡券状态
            $coupon->update(['status' => 1]);

            // 写入卡券日志
            Helpers::addCouponLog(trans('user.recharge_credit'), $coupon->id);

            DB::commit();

            return Response::json(['status' => 'success', 'message' => trans('user.recharge').trans('common.success')]);
        } catch (Exception $e) {
            Log::error(trans('user.recharge').trans('common.failed').$e->getMessage());
            DB::rollBack();

            return Response::json(['status' => 'fail', 'message' => trans('user.recharge').trans('common.failed')]);
        }
    }
}
