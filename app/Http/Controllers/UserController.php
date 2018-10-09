<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\ServerChan;
use App\Http\Models\Article;
use App\Http\Models\Coupon;
use App\Http\Models\Goods;
use App\Http\Models\GoodsLabel;
use App\Http\Models\Invite;
use App\Http\Models\Level;
use App\Http\Models\Order;
use App\Http\Models\ReferralApply;
use App\Http\Models\ReferralLog;
use App\Http\Models\SsConfig;
use App\Http\Models\SsGroup;
use App\Http\Models\SsNodeInfo;
use App\Http\Models\SsNodeLabel;
use App\Http\Models\Ticket;
use App\Http\Models\TicketReply;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use App\Http\Models\UserLoginLog;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserTrafficDaily;
use App\Http\Models\UserTrafficHourly;
use App\Http\Models\Verify;
use App\Mail\activeUser;
use App\Mail\newTicket;
use App\Mail\replyTicket;
use App\Mail\resetPassword;
use Illuminate\Http\Request;
use Redirect;
use Response;
use Session;
use Cache;
use Mail;
use Log;
use DB;

class UserController extends Controller
{
    protected static $systemConfig;

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
    }

    public function index(Request $request)
    {
        $user = Session::get('user');

        $user = User::query()->where('id', $user['id'])->first();
        $user->totalTransfer = flowAutoShow($user->transfer_enable);
        $user->usedTransfer = flowAutoShow($user->u + $user->d);
        $user->usedPercent = $user->transfer_enable > 0 ? round(($user->u + $user->d) / $user->transfer_enable, 2) : 1;
        $user->levelName = Level::query()->where('level', $user['level'])->first()['level_name'];

        $view['info'] = $user->toArray();
        $view['notice'] = Article::query()->where('type', 2)->where('is_del', 0)->orderBy('id', 'desc')->first();
        $view['wechat_qrcode'] = self::$systemConfig['wechat_qrcode'];
        $view['alipay_qrcode'] = self::$systemConfig['alipay_qrcode'];
        $view['login_add_score'] = self::$systemConfig['login_add_score'];
        $view['website_logo'] = self::$systemConfig['website_logo'];
        $view['website_analytics'] = self::$systemConfig['website_analytics'];
        $view['website_customer_service'] = self::$systemConfig['website_customer_service'];
        $view['is_push_bear'] = self::$systemConfig['is_push_bear'];
        $view['push_bear_qrcode'] = self::$systemConfig['push_bear_qrcode'];
        $view['goodsList'] = Goods::query()->where('type', 3)->where('status', 1)->where('is_del', 0)->orderBy('sort', 'desc')->orderBy('price', 'asc')->limit(10)->get(); // 余额充值商品，只取10个

        // 推广返利是否可见
        if (!Session::has('referral_status')) {
            Session::put('referral_status', self::$systemConfig['referral_status']);
        }

        // 如果没有唯一码则生成一个
        $subscribe = UserSubscribe::query()->where('user_id', $user['id'])->first();
        if (!$subscribe) {
            $code = $this->makeSubscribeCode();

            $obj = new UserSubscribe();
            $obj->user_id = $user['id'];
            $obj->code = $code;
            $obj->times = 0;
            $obj->save();
        } else {
            $code = $subscribe->code;
        }

        $view['subscribe_status'] = !$subscribe ? 1 : $subscribe->status;
        $view['link'] = self::$systemConfig['subscribe_domain'] ? self::$systemConfig['subscribe_domain'] . '/s/' . $code : self::$systemConfig['website_url'] . '/s/' . $code;

        // 近期登录日志
        $view['userLoginLog'] = UserLoginLog::query()->where('user_id', $user['id'])->orderBy('id', 'desc')->limit(10)->get();

        // 节点列表
        $userLabelIds = UserLabel::query()->where('user_id', $user['id'])->pluck('label_id');
        if (empty($userLabelIds)) {
            $view['nodeList'] = [];

            return Response::view('user.index', $view);
        }

        $nodeList = DB::table('ss_node')
            ->leftJoin('ss_node_label', 'ss_node.id', '=', 'ss_node_label.node_id')
            ->whereIn('ss_node_label.label_id', $userLabelIds)
            ->where('ss_node.status', 1)
            ->groupBy('ss_node.id')
            ->orderBy('sort', 'desc')
            ->get();

        foreach ($nodeList as &$node) {
            // 获取分组名称
            $group = SsGroup::query()->where('id', $node->group_id)->first();

            // 生成ssr scheme
            $obfs_param = $user->obfs_param ? $user->obfs_param : $node->obfs_param;
            $protocol_param = $node->single ? $user->port . ':' . $user->passwd : $user->protocol_param;

            $ssr_str = ($node->server ? $node->server : $node->ip) . ':' . ($node->single ? $node->single_port : $user->port);
            $ssr_str .= ':' . ($node->single ? $node->single_protocol : $user->protocol) . ':' . ($node->single ? $node->single_method : $user->method);
            $ssr_str .= ':' . ($node->single ? $node->single_obfs : $user->obfs) . ':' . ($node->single ? base64url_encode($node->single_passwd) : base64url_encode($user->passwd));
            $ssr_str .= '/?obfsparam=' . base64url_encode($obfs_param);
            $ssr_str .= '&protoparam=' . ($node->single ? base64url_encode($user->port . ':' . $user->passwd) : base64url_encode($protocol_param));
            $ssr_str .= '&remarks=' . base64url_encode($node->name . "|" . $node->traffic_rate . "倍流量消耗");
            $ssr_str .= '&group=' . base64url_encode(empty($group) ? '' : $group->name);
            $ssr_str .= '&udpport=0';
            $ssr_str .= '&uot=0';
            $ssr_str = base64url_encode($ssr_str);
            $ssr_scheme = 'ssr://' . $ssr_str;

            // 生成ss scheme
            $ss_str = $user->method . ':' . $user->passwd . '@';
            $ss_str .= ($node->server ? $node->server : $node->ip) . ':' . $user->port;
            $ss_str = base64url_encode($ss_str) . '#' . 'VPN';
            $ss_scheme = 'ss://' . $ss_str;

            // 生成文本配置信息
            $txt = "服务器：" . ($node->server ? $node->server : $node->ip) . "\r\n";
            if ($node->ipv6) {
                $txt .= "IPv6：" . $node->ipv6 . "\r\n";
            }
            $txt .= "远程端口：" . ($node->single ? $node->single_port : $user->port) . "\r\n";
            $txt .= "密码：" . ($node->single ? $node->single_passwd : $user->passwd) . "\r\n";
            $txt .= "加密方法：" . ($node->single ? $node->single_method : $user->method) . "\r\n";
            $txt .= "路由：绕过局域网及中国大陆地址" . "\r\n\r\n";
            $txt .= "协议：" . ($node->single ? $node->single_protocol : $user->protocol) . "\r\n";
            $txt .= "协议参数：" . ($node->single ? $user->port . ':' . $user->passwd : $user->protocol_param) . "\r\n";
            $txt .= "混淆方式：" . ($node->single ? $node->single_obfs : $user->obfs) . "\r\n";
            $txt .= "混淆参数：" . ($user->obfs_param ? $user->obfs_param : $node->obfs_param) . "\r\n";
            $txt .= "本地端口：1080" . "\r\n";

            $node->txt = $txt;
            $node->ssr_scheme = $ssr_scheme;
            $node->ss_scheme = $node->compatible ? $ss_scheme : ''; // 节点兼容原版才显示

            // 节点在线状态
            $nodeInfo = SsNodeInfo::query()->where('node_id', $node->node_id)->where('log_time', '>=', strtotime("-10 minutes"))->orderBy('id', 'desc')->first();
            $node->online_status = empty($nodeInfo) || empty($nodeInfo->load) ? 0 : 1;

            // 节点标签
            $node->labels = SsNodeLabel::query()->with('labelInfo')->where('node_id', $node->id)->get();
        }

        $view['nodeList'] = $nodeList;

        return Response::view('user.index', $view);
    }

    // 公告详情
    public function article(Request $request)
    {
        $id = $request->get('id');

        $view['info'] = Article::query()->where('is_del', 0)->where('id', $id)->first();
        if (empty($view['info'])) {
            return Redirect::to('/');
        }

        $view['website_logo'] = self::$systemConfig['website_logo'];
        $view['website_analytics'] = self::$systemConfig['website_analytics'];
        $view['website_customer_service'] = self::$systemConfig['website_customer_service'];

        return Response::view('user.article', $view);
    }

    // 修改个人资料
    public function profile(Request $request)
    {
        $user = Session::get('user');

        if ($request->method() == 'POST') {
            $old_password = $request->get('old_password');
            $new_password = $request->get('new_password');
            $wechat = $request->get('wechat');
            $qq = $request->get('qq');
            $passwd = trim($request->get('passwd'));
            $method = $request->get('method');
            $protocol = $request->get('protocol');
            $obfs = $request->get('obfs');

            // 修改密码
            if ($old_password && $new_password) {
                $old_password = md5(trim($old_password));
                $new_password = md5(trim($new_password));

                $user = User::query()->where('id', $user['id'])->first();
                if ($user->password != $old_password) {
                    Session::flash('errorMsg', '旧密码错误，请重新输入');

                    return Redirect::to('profile#tab_1');
                } elseif ($user->password == $new_password) {
                    Session::flash('errorMsg', '新密码不可与旧密码一样，请重新输入');

                    return Redirect::to('profile#tab_1');
                }

                // 演示环境禁止改管理员密码
                if (env('APP_DEMO') && $user['id'] == 1) {
                    Session::flash('errorMsg', '演示环境禁止修改管理员密码');

                    return Redirect::to('profile#tab_1');
                }

                $ret = User::query()->where('id', $user['id'])->update(['password' => $new_password]);
                if (!$ret) {
                    Session::flash('errorMsg', '修改失败');

                    return Redirect::to('profile#tab_1');
                } else {
                    Session::flash('successMsg', '修改成功');

                    return Redirect::to('profile#tab_1');
                }
            }

            // 修改联系方式
            if ($wechat || $qq) {
                if (empty(clean($wechat)) && empty(clean($qq))) {
                    Session::flash('errorMsg', '修改失败');

                    return Redirect::to('profile#tab_2');
                }

                $ret = User::query()->where('id', $user['id'])->update(['wechat' => $wechat, 'qq' => $qq]);
                if (!$ret) {
                    Session::flash('errorMsg', '修改失败');

                    return Redirect::to('profile#tab_2');
                } else {
                    Session::flash('successMsg', '修改成功');

                    return Redirect::to('profile#tab_2');
                }
            }

            // 修改SSR(R)设置
            if ($method || $protocol || $obfs) {
                if (empty($passwd)) {
                    Session::flash('errorMsg', '密码不能为空');

                    return Redirect::to('profile#tab_3');
                }

                // 加密方式、协议、混淆必须存在
                $existMethod = SsConfig::query()->where('type', 1)->where('name', $method)->first();
                $existProtocol = SsConfig::query()->where('type', 2)->where('name', $protocol)->first();
                $existObfs = SsConfig::query()->where('type', 3)->where('name', $obfs)->first();
                if (!$existMethod || !$existProtocol || !$existObfs) {
                    Session::flash('errorMsg', '非法请求');

                    return Redirect::to('profile#tab_3');
                }

                $data = [
                    'passwd'   => $passwd,
                    'method'   => $method,
                    'protocol' => $protocol,
                    'obfs'     => $obfs
                ];

                $ret = User::query()->where('id', $user['id'])->update($data);
                if (!$ret) {
                    Session::flash('errorMsg', '修改失败');

                    return Redirect::to('profile#tab_3');
                } else {
                    // 更新session
                    $user = User::query()->where('id', $user['id'])->first()->toArray();
                    Session::remove('user');
                    Session::put('user', $user);

                    Session::flash('successMsg', '修改成功');

                    return Redirect::to('profile#tab_3');
                }
            }
        } else {
            // 加密方式、协议、混淆
            $view['method_list'] = Helpers::methodList();
            $view['protocol_list'] = Helpers::protocolList();
            $view['obfs_list'] = Helpers::obfsList();
            $view['info'] = User::query()->where('id', $user['id'])->first();
            $view['website_logo'] = self::$systemConfig['website_logo'];
            $view['website_analytics'] = self::$systemConfig['website_analytics'];
            $view['website_customer_service'] = self::$systemConfig['website_customer_service'];

            return Response::view('user.profile', $view);
        }
    }

    // 流量日志
    public function trafficLog(Request $request)
    {
        $user = Session::get('user');

        $dailyData = [];
        $hourlyData = [];

        // 节点一个月内的流量
        $userTrafficDaily = UserTrafficDaily::query()->where('user_id', $user['id'])->where('node_id', 0)->where('created_at', '>=', date('Y-m', time()))->orderBy('created_at', 'asc')->pluck('total')->toArray();

        $dailyTotal = date('d', time()) - 1; // 今天不算，减一
        $dailyCount = count($userTrafficDaily);
        for ($x = 0; $x < ($dailyTotal - $dailyCount); $x++) {
            $dailyData[$x] = 0;
        }
        for ($x = ($dailyTotal - $dailyCount); $x < $dailyTotal; $x++) {
            $dailyData[$x] = round($userTrafficDaily[$x - ($dailyTotal - $dailyCount)] / (1024 * 1024 * 1024), 3);
        }

        // 节点一天内的流量
        $userTrafficHourly = UserTrafficHourly::query()->where('user_id', $user['id'])->where('node_id', 0)->where('created_at', '>=', date('Y-m-d', time()))->orderBy('created_at', 'asc')->pluck('total')->toArray();
        $hourlyTotal = date('H', time());
        $hourlyCount = count($userTrafficHourly);
        for ($x = 0; $x < ($hourlyTotal - $hourlyCount); $x++) {
            $hourlyData[$x] = 0;
        }
        for ($x = ($hourlyTotal - $hourlyCount); $x < $hourlyTotal; $x++) {
            $hourlyData[$x] = round($userTrafficHourly[$x - ($hourlyTotal - $hourlyCount)] / (1024 * 1024 * 1024), 3);
        }

        // 本月天数数据
        $monthDays = [];
        $monthHasDays = date("t");
        for ($i = 1; $i <= $monthHasDays; $i++) {
            $monthDays[] = $i;
        }

        $view['trafficDaily'] = "'" . implode("','", $dailyData) . "'";
        $view['trafficHourly'] = "'" . implode("','", $hourlyData) . "'";
        $view['monthDays'] = "'" . implode("','", $monthDays) . "'";

        $view['website_logo'] = self::$systemConfig['website_logo'];
        $view['website_analytics'] = self::$systemConfig['website_analytics'];
        $view['website_customer_service'] = self::$systemConfig['website_customer_service'];

        return Response::view('user.trafficLog', $view);
    }

    // 商品列表
    public function goodsList(Request $request)
    {
        $view['goodsList'] = Goods::query()->where('status', 1)->where('is_del', 0)->where('type', '<=', '2')->orderBy('type', 'desc')->orderBy('sort', 'desc')->paginate(10)->appends($request->except('page'));
        $view['website_logo'] = self::$systemConfig['website_logo'];
        $view['website_analytics'] = self::$systemConfig['website_analytics'];
        $view['website_customer_service'] = self::$systemConfig['website_customer_service'];

        return Response::view('user.goodsList', $view);
    }

    // 工单
    public function ticketList(Request $request)
    {
        $user = Session::get('user');

        $view['website_logo'] = self::$systemConfig['website_logo'];
        $view['website_analytics'] = self::$systemConfig['website_analytics'];
        $view['website_customer_service'] = self::$systemConfig['website_customer_service'];

        $view['ticketList'] = Ticket::query()->where('user_id', $user['id'])->orderBy('id', 'desc')->paginate(10)->appends($request->except('page'));

        return Response::view('user.ticketList', $view);
    }

    // 订单
    public function orderList(Request $request)
    {
        $user = Session::get('user');

        $view['orderList'] = Order::query()->with(['user', 'goods', 'coupon', 'payment'])->where('user_id', $user['id'])->orderBy('oid', 'desc')->paginate(10)->appends($request->except('page'));

        $view['website_logo'] = self::$systemConfig['website_logo'];
        $view['website_analytics'] = self::$systemConfig['website_analytics'];
        $view['website_customer_service'] = self::$systemConfig['website_customer_service'];

        return Response::view('user.orderList', $view);
    }

    // 订单明细
    public function orderDetail(Request $request, $sn)
    {
        $view['website_logo'] = self::$systemConfig['website_logo'];
        $view['website_analytics'] = self::$systemConfig['website_analytics'];
        $view['website_customer_service'] = self::$systemConfig['website_customer_service'];
        $view['order'] = Order::query()->with(['goods', 'coupon', 'payment'])->where('order_sn', $sn)->firstOrFail();

        return Response::view('user.orderDetail', $view);
    }

    // 添加工单
    public function addTicket(Request $request)
    {
        $title = $request->get('title');
        $content = clean($request->get('content'));
        $content = str_replace("eval", "", str_replace("atob", "", $content));

        $user = Session::get('user');

        if (empty($title) || empty($content)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '请输入标题和内容']);
        }

        $obj = new Ticket();
        $obj->user_id = $user['id'];
        $obj->title = $title;
        $obj->content = $content;
        $obj->status = 0;
        $obj->created_at = date('Y-m-d H:i:s');
        $obj->save();

        if ($obj->id) {
            $emailTitle = "新工单提醒";
            $content = "标题：【" . $title . "】<br>内容：" . $content;

            // 发邮件通知管理员
            if (self::$systemConfig['crash_warning_email']) {
                try {
                    Mail::to(self::$systemConfig['crash_warning_email'])->send(new newTicket(self::$systemConfig['website_name'], $emailTitle, $content));
                    $this->sendEmailLog(1, $emailTitle, $content);
                } catch (\Exception $e) {
                    $this->sendEmailLog(1, $emailTitle, $content, 0, $e->getMessage());
                }
            }

            // 通过ServerChan发微信消息提醒管理员
            if (self::$systemConfig['is_server_chan'] && self::$systemConfig['server_chan_key']) {
                $serverChan = new ServerChan();
                $serverChan->send($emailTitle, $content);
            }

            return Response::json(['status' => 'success', 'data' => '', 'message' => '提交成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '提交失败']);
        }
    }

    // 回复工单
    public function replyTicket(Request $request)
    {
        $id = intval($request->get('id'));

        $user = Session::get('user');

        if ($request->method() == 'POST') {
            $content = clean($request->get('content'));
            $content = str_replace("eval", "", str_replace("atob", "", $content));
            $content = substr($content, 0, 300);

            if (empty($content)) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '回复内容不能为空']);
            }

            $obj = new TicketReply();
            $obj->ticket_id = $id;
            $obj->user_id = $user['id'];
            $obj->content = $content;
            $obj->created_at = date('Y-m-d H:i:s');
            $obj->save();

            if ($obj->id) {
                $ticket = Ticket::query()->where('id', $id)->first();

                $title = "工单回复提醒";
                $content = "标题：【" . $ticket->title . "】<br>用户回复：" . $content;

                // 发邮件通知管理员
                try {
                    if (self::$systemConfig['crash_warning_email']) {
                        Mail::to(self::$systemConfig['crash_warning_email'])->send(new replyTicket(self::$systemConfig['website_name'], $title, $content));
                        $this->sendEmailLog(1, $title, $content);
                    }
                } catch (\Exception $e) {
                    $this->sendEmailLog(1, $title, $content, 0, $e->getMessage());
                }

                // 通过ServerChan发微信消息提醒管理员
                if (self::$systemConfig['is_server_chan'] && self::$systemConfig['server_chan_key']) {
                    $serverChan = new ServerChan();
                    $serverChan->send($title, $content);
                }

                return Response::json(['status' => 'success', 'data' => '', 'message' => '回复成功']);
            } else {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '回复失败']);
            }
        } else {
            $ticket = Ticket::query()->where('id', $id)->with('user')->first();
            if (empty($ticket) || $ticket->user_id != $user['id']) {
                return Redirect::to('tickets');
            }

            $view['ticket'] = $ticket;
            $view['replyList'] = TicketReply::query()->where('ticket_id', $id)->with('user')->orderBy('id', 'asc')->get();

            $view['website_logo'] = self::$systemConfig['website_logo'];
            $view['website_analytics'] = self::$systemConfig['website_analytics'];
            $view['website_customer_service'] = self::$systemConfig['website_customer_service'];

            return Response::view('user.replyTicket', $view);
        }
    }

    // 关闭工单
    public function closeTicket(Request $request)
    {
        $id = $request->get('id');

        $user = Session::get('user');

        $ret = Ticket::query()->where('id', $id)->where('user_id', $user['id'])->update(['status' => 2]);
        if ($ret) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '关闭成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '关闭失败']);
        }
    }

    // 邀请码
    public function invite(Request $request)
    {
        $user = Session::get('user');

        // 已生成的邀请码数量
        $num = Invite::query()->where('uid', $user['id'])->count();

        $view['website_logo'] = self::$systemConfig['website_logo'];
        $view['website_analytics'] = self::$systemConfig['website_analytics'];
        $view['website_customer_service'] = self::$systemConfig['website_customer_service'];
        $view['num'] = self::$systemConfig['invite_num'] - $num <= 0 ? 0 : self::$systemConfig['invite_num'] - $num; // 还可以生成的邀请码数量
        $view['inviteList'] = Invite::query()->where('uid', $user['id'])->with(['generator', 'user'])->paginate(10); // 邀请码列表
        $view['referral_traffic'] = flowAutoShow(self::$systemConfig['referral_traffic'] * 1048576);
        $view['referral_percent'] = self::$systemConfig['referral_percent'];

        return Response::view('user.invite', $view);
    }

    // 公开的邀请码列表
    public function free(Request $request)
    {
        $view['website_logo'] = self::$systemConfig['website_logo'];
        $view['website_analytics'] = self::$systemConfig['website_analytics'];
        $view['website_customer_service'] = self::$systemConfig['website_customer_service'];
        $view['is_invite_register'] = self::$systemConfig['is_invite_register'];
        $view['is_free_code'] = self::$systemConfig['is_free_code'];
        $view['inviteList'] = Invite::query()->where('uid', 0)->where('status', 0)->paginate();

        return Response::view('user.free', $view);
    }

    // 生成邀请码
    public function makeInvite(Request $request)
    {
        $user = Session::get('user');

        // 已生成的邀请码数量
        $num = Invite::query()->where('uid', $user['id'])->count();
        if ($num >= self::$systemConfig['invite_num']) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '生成失败：最多只能生成' . self::$systemConfig['invite_num'] . '个邀请码']);
        }

        $obj = new Invite();
        $obj->uid = $user['id'];
        $obj->fuid = 0;
        $obj->code = strtoupper(mb_substr(md5(microtime() . makeRandStr()), 8, 12));
        $obj->status = 0;
        $obj->dateline = date('Y-m-d H:i:s', strtotime("+7 days"));
        $obj->save();

        return Response::json(['status' => 'success', 'data' => '', 'message' => '生成成功']);
    }

    // 激活账号页
    public function activeUser(Request $request)
    {
        if ($request->method() == 'POST') {
            $username = trim($request->get('username'));

            // 是否开启账号激活
            if (!self::$systemConfig['is_active_register']) {
                Session::flash('errorMsg', '系统未开启账号激活功能，请联系管理员');

                return Redirect::back()->withInput();
            }

            // 查找账号
            $user = User::query()->where('username', $username)->first();
            if (!$user) {
                Session::flash('errorMsg', '账号不存在，请重试');

                return Redirect::back();
            } elseif ($user->status < 0) {
                Session::flash('errorMsg', '账号已禁止登陆，无需激活');

                return Redirect::back();
            } elseif ($user->status > 0) {
                Session::flash('errorMsg', '账号无需激活');

                return Redirect::back();
            }

            // 24小时内激活次数限制
            $activeTimes = 0;
            if (Cache::has('activeUser_' . md5($username))) {
                $activeTimes = Cache::get('activeUser_' . md5($username));
                if ($activeTimes >= self::$systemConfig['active_times']) {
                    Session::flash('errorMsg', '同一个账号24小时内只能请求激活' . self::$systemConfig['active_times'] . '次，请勿频繁操作');

                    return Redirect::back();
                }
            }

            // 生成激活账号的地址
            $token = md5(self::$systemConfig['website_name'] . $username . microtime());
            $verify = new Verify();
            $verify->user_id = $user->id;
            $verify->username = $username;
            $verify->token = $token;
            $verify->status = 0;
            $verify->save();

            // 发送邮件
            $activeUserUrl = self::$systemConfig['website_url'] . '/active/' . $token;
            $title = '重新激活账号';
            $content = '请求地址：' . $activeUserUrl;

            try {
                Mail::to($user->username)->send(new activeUser(self::$systemConfig['website_name'], $activeUserUrl));
                $this->sendEmailLog($user->id, $title, $content);
            } catch (\Exception $e) {
                $this->sendEmailLog($user->id, $title, $content, 0, $e->getMessage());
            }

            Cache::put('activeUser_' . md5($username), $activeTimes + 1, 1440);
            Session::flash('successMsg', '激活邮件已发送，如未收到，请查看垃圾邮箱');

            return Redirect::back();
        } else {
            $view['is_active_register'] = self::$systemConfig['is_active_register'];

            return Response::view('user.activeUser', $view);
        }
    }

    // 激活账号
    public function active(Request $request, $token)
    {
        if (empty($token)) {
            return Redirect::to('login');
        }

        $verify = Verify::query()->where('token', $token)->with('user')->first();
        if (empty($verify)) {
            return Redirect::to('login');
        } elseif (empty($verify->user)) {
            Session::flash('errorMsg', '该链接已失效');

            return Response::view('user.active');
        } elseif ($verify->status == 1) {
            Session::flash('errorMsg', '该链接已失效');

            return Response::view('user.active');
        } elseif ($verify->user->status != 0) {
            Session::flash('errorMsg', '该账号无需激活.');

            return Response::view('user.active');
        } elseif (time() - strtotime($verify->created_at) >= 1800) {
            Session::flash('errorMsg', '该链接已过期');

            // 置为已失效
            $verify->status = 2;
            $verify->save();

            return Response::view('user.active');
        }

        // 更新账号状态
        $ret = User::query()->where('id', $verify->user_id)->update(['status' => 1]);
        if (!$ret) {
            Session::flash('errorMsg', '账号激活失败');

            return Redirect::back();
        }

        // 置为已使用
        $verify->status = 1;
        $verify->save();

        // 账号激活后给邀请人送流量
        if ($verify->user->referral_uid) {
            $transfer_enable = self::$systemConfig['referral_traffic'] * 1048576;

            User::query()->where('id', $verify->user->referral_uid)->increment('transfer_enable', $transfer_enable);
            User::query()->where('id', $verify->user->referral_uid)->update(['enable' => 1]);
        }

        Session::flash('successMsg', '账号激活成功');

        return Response::view('user.active');
    }

    // 重设密码页
    public function resetPassword(Request $request)
    {
        if ($request->method() == 'POST') {
            $username = trim($request->get('username'));

            // 是否开启重设密码
            if (!self::$systemConfig['is_reset_password']) {
                Session::flash('errorMsg', '系统未开启重置密码功能，请联系管理员');

                return Redirect::back()->withInput();
            }

            // 查找账号
            $user = User::query()->where('username', $username)->first();
            if (!$user) {
                Session::flash('errorMsg', '账号不存在，请重试');

                return Redirect::back();
            }

            // 24小时内重设密码次数限制
            $resetTimes = 0;
            if (Cache::has('resetPassword_' . md5($username))) {
                $resetTimes = Cache::get('resetPassword_' . md5($username));
                if ($resetTimes >= self::$systemConfig['reset_password_times']) {
                    Session::flash('errorMsg', '同一个账号24小时内只能重设密码' . self::$systemConfig['reset_password_times'] . '次，请勿频繁操作');

                    return Redirect::back();
                }
            }

            // 生成取回密码的地址
            $token = md5(self::$systemConfig['website_name'] . $username . microtime());
            $verify = new Verify();
            $verify->user_id = $user->id;
            $verify->username = $username;
            $verify->token = $token;
            $verify->status = 0;
            $verify->save();

            // 发送邮件
            $resetPasswordUrl = self::$systemConfig['website_url'] . '/reset/' . $token;
            $title = '重置密码';
            $content = '请求地址：' . $resetPasswordUrl;

            try {
                Mail::to($user->username)->send(new resetPassword(self::$systemConfig['website_name'], $resetPasswordUrl));
                $this->sendEmailLog($user->id, $title, $content);
            } catch (\Exception $e) {
                $this->sendEmailLog($user->id, $title, $content, 0, $e->getMessage());
            }

            Cache::put('resetPassword_' . md5($username), $resetTimes + 1, 1440);
            Session::flash('successMsg', '重置成功，请查看邮箱');

            return Redirect::back();
        } else {
            $view['website_home_logo'] = self::$systemConfig['website_home_logo'];
            $view['website_analytics'] = self::$systemConfig['website_analytics'];
            $view['website_customer_service'] = self::$systemConfig['website_customer_service'];
            $view['is_reset_password'] = self::$systemConfig['is_reset_password'];

            return Response::view('user.resetPassword', $view);
        }
    }

    // 重设密码
    public function reset(Request $request, $token)
    {
        if ($request->method() == 'POST') {
            $password = trim($request->get('password'));
            $repassword = trim($request->get('repassword'));

            if (empty($token)) {
                return Redirect::to('login');
            } elseif (empty($password) || empty($repassword)) {
                Session::flash('errorMsg', '密码不能为空');

                return Redirect::back();
            } elseif (md5($password) != md5($repassword)) {
                Session::flash('errorMsg', '两次输入密码不一致，请重新输入');

                return Redirect::back();
            }

            // 校验账号
            $verify = Verify::query()->where('token', $token)->with('User')->first();
            if (empty($verify)) {
                return Redirect::to('login');
            } elseif ($verify->status == 1) {
                Session::flash('errorMsg', '该链接已失效');

                return Redirect::back();
            } elseif ($verify->user->status < 0) {
                Session::flash('errorMsg', '账号已被禁用');

                return Redirect::back();
            } elseif (md5($password) == $verify->user->password) {
                Session::flash('errorMsg', '新旧密码一样，请重新输入');

                return Redirect::back();
            }

            // 更新密码
            $ret = User::query()->where('id', $verify->user_id)->update(['password' => md5($password)]);
            if (!$ret) {
                Session::flash('errorMsg', '重设密码失败');

                return Redirect::back();
            }

            // 置为已使用
            $verify->status = 1;
            $verify->save();

            Session::flash('successMsg', '新密码设置成功，请自行登录');

            return Redirect::back();
        } else {
            if (empty($token)) {
                return Redirect::to('login');
            }

            $verify = Verify::query()->where('token', $token)->with('user')->first();
            if (empty($verify)) {
                return Redirect::to('login');
            } elseif (time() - strtotime($verify->created_at) >= 1800) {
                Session::flash('errorMsg', '该链接已过期');

                // 置为已失效
                $verify->status = 2;
                $verify->save();

                // 重新获取一遍verify
                $view['verify'] = Verify::query()->where('token', $token)->with('user')->first();

                return Response::view('user.reset', $view);
            }

            $view['website_home_logo'] = self::$systemConfig['website_home_logo'];
            $view['verify'] = $verify;

            return Response::view('user.reset', $view);
        }
    }

    // 使用优惠券
    public function redeemCoupon(Request $request)
    {
        $coupon_sn = $request->get('coupon_sn');

        if (empty($coupon_sn)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '优惠券不能为空']);
        }

        $coupon = Coupon::query()->where('sn', $coupon_sn)->whereIn('type', [1, 2])->where('is_del', 0)->first();
        if (!$coupon) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该优惠券不存在']);
        } elseif ($coupon->status == 1) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该优惠券已使用，请换一个试试']);
        } elseif ($coupon->status == 2) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该优惠券已失效，请换一个试试']);
        } elseif ($coupon->available_start > time() || $coupon->available_end < time()) {
            $coupon->status = 2;
            $coupon->save();

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该优惠券已失效，请换一个试试']);
        }

        $data = [
            'type'     => $coupon->type,
            'amount'   => $coupon->amount,
            'discount' => $coupon->discount
        ];

        return Response::json(['status' => 'success', 'data' => $data, 'message' => '该优惠券有效']);
    }

    // 购买服务
    public function buy(Request $request, $id)
    {
        $goods_id = intval($id);
        $coupon_sn = $request->get('coupon_sn');

        $user = Session::get('user');

        if ($request->method() == 'POST') {
            $goods = Goods::query()->with(['label'])->where('is_del', 0)->where('status', 1)->where('id', $goods_id)->first();
            if (!$goods) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '支付失败：商品或服务已下架']);
            }

            // 限购控制：all-所有商品限购, free-价格为0的商品限购, none-不限购（默认）
            $strategy = self::$systemConfig['goods_purchase_limit_strategy'];
            if ($strategy == 'all' || ($strategy == 'package' && $goods->type == 2) || ($strategy == 'free' && $goods->price == 0) || ($strategy == 'package&free' && ($goods->type == 2 || $goods->price == 0))) {
                $noneExpireGoodExist = Order::query()->where('status', '>=', 0)->where('is_expire', 0)->where('user_id', $user['id'])->where('goods_id', $goods_id)->exists();
                if ($noneExpireGoodExist) {
                    return Response::json(['status' => 'fail', 'data' => '', 'message' => '支付失败：商品不可重复购买']);
                }
            }

            // 使用优惠券
            if (!empty($coupon_sn)) {
                $coupon = Coupon::query()->where('status', 0)->where('is_del', 0)->whereIn('type', [1, 2])->where('sn', $coupon_sn)->first();
                if (empty($coupon)) {
                    return Response::json(['status' => 'fail', 'data' => '', 'message' => '支付失败：优惠券不存在']);
                }

                // 计算实际应支付总价
                $amount = $coupon->type == 2 ? $goods->price * $coupon->discount / 10 : $goods->price - $coupon->amount;
                $amount = $amount > 0 ? $amount : 0;
            } else {
                $amount = $goods->price;
            }

            // 价格异常判断
            if ($amount < 0) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '支付失败：订单总价异常']);
            }

            // 验证账号余额是否充足
            $user = User::query()->where('id', $user['id'])->first();
            if ($user->balance < $amount) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '支付失败：您的余额不足，请先充值']);
            }

            DB::beginTransaction();
            try {
                // 生成订单
                $order = new Order();
                $order->order_sn = date('ymdHis') . mt_rand(100000, 999999);
                $order->user_id = $user->id;
                $order->goods_id = $goods_id;
                $order->coupon_id = !empty($coupon) ? $coupon->id : 0;
                $order->origin_amount = $goods->price;
                $order->amount = $amount;
                $order->expire_at = date("Y-m-d H:i:s", strtotime("+" . $goods->days . " days"));
                $order->is_expire = 0;
                $order->pay_way = 1;
                $order->status = 2;
                $order->save();

                // 扣余额
                User::query()->where('id', $user->id)->decrement('balance', $amount * 100);

                // 记录余额操作日志
                $this->addUserBalanceLog($user->id, $order->oid, $user->balance, $user->balance - $amount, -1 * $amount, '购买服务：' . $goods->name);

                // 优惠券置为已使用
                if (!empty($coupon)) {
                    if ($coupon->usage == 1) {
                        $coupon->status = 1;
                        $coupon->save();
                    }

                    // 写入日志
                    $this->addCouponLog($coupon->id, $goods_id, $order->oid, '余额支付订单使用');
                }

                // 如果买的是套餐，则先将之前购买的所有套餐置都无效，并扣掉之前所有套餐的流量，并移除之前所有套餐的标签
                if ($goods->type === 2) {
                    $existOrderList = Order::query()->with('goods')->whereHas('goods', function ($q) {
                        $q->where('type', 2);
                    })->where('user_id', $user->id)->where('oid', '<>', $order->oid)->where('is_expire', 0)->where('status', 2)->get();
                    foreach ($existOrderList as $vo) {
                        Order::query()->where('oid', $vo->oid)->update(['is_expire' => 1]);
                        User::query()->where('id', $user->id)->decrement('transfer_enable', $vo->goods->traffic * 1048576);
                    }
                }

                // 把商品的流量加到账号上
                User::query()->where('id', $user->id)->increment('transfer_enable', $goods->traffic * 1048576);

                // 计算账号过期时间
                if ($user->expire_time < date('Y-m-d', strtotime("+" . $goods->days . " days"))) {
                    $expireTime = date('Y-m-d', strtotime("+" . $goods->days . " days"));
                } else {
                    $expireTime = $user->expire_time;
                }

                // 套餐就改流量重置日，流量包不改
                if ($goods->type == 2) {
                    if (date('m') == 2 && date('d') == 29) {
                        $traffic_reset_day = 28;
                    } else {
                        $traffic_reset_day = date('d') == 31 ? 30 : abs(date('d'));
                    }
                    User::query()->where('id', $order->user_id)->update(['traffic_reset_day' => $traffic_reset_day, 'expire_time' => $expireTime, 'enable' => 1]);
                } else {
                    User::query()->where('id', $order->user_id)->update(['expire_time' => $expireTime, 'enable' => 1]);
                }

                // 写入用户标签
                if ($goods->label) {
                    // 用户默认标签
                    $defaultLabels = [];
                    if (self::$systemConfig['initial_labels_for_user']) {
                        $defaultLabels = explode(',', self::$systemConfig['initial_labels_for_user']);
                    }

                    // 取出现有的标签
                    $userLabels = UserLabel::query()->where('user_id', $user->id)->pluck('label_id')->toArray();
                    $goodsLabels = GoodsLabel::query()->where('goods_id', $goods_id)->pluck('label_id')->toArray();

                    // 标签去重
                    $newUserLabels = array_values(array_unique(array_merge($userLabels, $goodsLabels, $defaultLabels)));

                    // 删除用户所有标签
                    UserLabel::query()->where('user_id', $user->id)->delete();

                    // 生成标签
                    foreach ($newUserLabels as $vo) {
                        $obj = new UserLabel();
                        $obj->user_id = $user->id;
                        $obj->label_id = $vo;
                        $obj->save();
                    }
                }

                // 写入返利日志
                if ($user->referral_uid) {
                    $this->addReferralLog($user->id, $user->referral_uid, $order->oid, $amount, $amount * self::$systemConfig['referral_percent']);
                }

                // 取消重复返利
                User::query()->where('id', $order->user_id)->update(['referral_uid' => 0]);

                DB::commit();

                return Response::json(['status' => 'success', 'data' => '', 'message' => '支付成功']);
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('支付订单失败：' . $e->getMessage());

                return Response::json(['status' => 'fail', 'data' => '', 'message' => '支付失败：' . $e->getMessage()]);
            }
        } else {
            $goods = Goods::query()->where('id', $goods_id)->where('is_del', 0)->where('status', 1)->first();
            if (empty($goods)) {
                return Redirect::to('services');
            }

            $view['goods'] = $goods;
            $view['is_youzan'] = self::$systemConfig['is_youzan'];
            $view['website_logo'] = self::$systemConfig['website_logo'];
            $view['website_analytics'] = self::$systemConfig['website_analytics'];
            $view['website_customer_service'] = self::$systemConfig['website_customer_service'];

            return Response::view('user.buy', $view);
        }
    }

    // 积分兑换流量
    public function exchange(Request $request)
    {
        $user = Session::get('user');

        // 积分满100才可以兑换
        if ($user['score'] < 100) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '兑换失败：满100才可以兑换，请继续累计吧']);
        }

        // 账号过期不允许兑换
        if ($user['expire_time'] < date('Y-m-d')) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '兑换失败：账号已过期，请先购买服务吧']);
        }

        DB::beginTransaction();
        try {
            // 写入积分操作日志
            $ret = $this->addUserScoreLog($user['id'], $user['score'], 0, -1 * $user['score'], '积分兑换流量');

            // 扣积分加流量
            if ($ret) {
                User::query()->where('id', $user['id'])->update(['score' => 0]);
                User::query()->where('id', $user['id'])->increment('transfer_enable', $user['score'] * 1048576);
            }

            DB::commit();

            // 更新session
            $user = User::query()->where('id', $user['id'])->first()->toArray();
            Session::remove('user');
            Session::put('user', $user);

            return Response::json(['status' => 'success', 'data' => '', 'message' => '兑换成功']);
        } catch (\Exception $e) {
            DB::rollBack();

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '兑换失败：' . $e->getMessage()]);
        }
    }

    // 推广返利
    public function referral(Request $request)
    {
        // 生成个人推广链接
        $user = Session::get('user');

        $view['website_logo'] = self::$systemConfig['website_logo'];
        $view['website_analytics'] = self::$systemConfig['website_analytics'];
        $view['website_customer_service'] = self::$systemConfig['website_customer_service'];
        $view['referral_traffic'] = flowAutoShow(self::$systemConfig['referral_traffic'] * 1048576);
        $view['referral_percent'] = self::$systemConfig['referral_percent'];
        $view['referral_money'] = self::$systemConfig['referral_money'];
        $view['totalAmount'] = ReferralLog::query()->where('ref_user_id', $user['id'])->sum('ref_amount') / 100;
        $view['canAmount'] = ReferralLog::query()->where('ref_user_id', $user['id'])->where('status', 0)->sum('ref_amount') / 100;
        $view['link'] = self::$systemConfig['website_url'] . '/register?aff=' . $user['id'];
        $view['referralLogList'] = ReferralLog::query()->where('ref_user_id', $user['id'])->with('user')->orderBy('id', 'desc')->paginate(10);
        $view['referralApplyList'] = ReferralApply::query()->where('user_id', $user['id'])->with('user')->orderBy('id', 'desc')->paginate(10);
        $view['referralUserList'] = User::query()->select(['username', 'created_at'])->where('referral_uid', $user['id'])->orderBy('id', 'desc')->paginate(10);

        return Response::view('user.referral', $view);
    }

    // 申请提现
    public function extractMoney(Request $request)
    {
        $user = Session::get('user');

        // 判断账户是否过期
        if ($user['expire_time'] < date('Y-m-d')) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '申请失败：账号已过期，请先购买服务吧']);
        }

        // 判断是否已存在申请
        $referralApply = ReferralApply::query()->where('user_id', $user['id'])->whereIn('status', [0, 1])->first();
        if ($referralApply) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '申请失败：已存在申请，请等待之前的申请处理完']);
        }

        // 校验可以提现金额是否超过系统设置的阀值
        $ref_amount = ReferralLog::query()->where('ref_user_id', $user['id'])->where('status', 0)->sum('ref_amount');
        $ref_amount = $ref_amount / 100;
        if ($ref_amount < self::$systemConfig['referral_money']) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '申请失败：满' . self::$systemConfig['referral_money'] . '元才可以提现，继续努力吧']);
        }

        // 取出本次申请关联返利日志ID
        $link_logs = '';
        $referralLog = ReferralLog::query()->where('ref_user_id', $user['id'])->where('status', 0)->get();
        foreach ($referralLog as $log) {
            $link_logs .= $log->id . ',';
        }
        $link_logs = rtrim($link_logs, ',');

        $obj = new ReferralApply();
        $obj->user_id = $user['id'];
        $obj->before = $ref_amount;
        $obj->after = 0;
        $obj->amount = $ref_amount;
        $obj->link_logs = $link_logs;
        $obj->status = 0;
        $obj->save();

        return Response::json(['status' => 'success', 'data' => '', 'message' => '申请成功，请等待管理员审核']);
    }

    // 帮助中心
    public function help(Request $request)
    {
        $view['website_logo'] = self::$systemConfig['website_logo'];
        $view['website_analytics'] = self::$systemConfig['website_analytics'];
        $view['website_customer_service'] = self::$systemConfig['website_customer_service'];
        $view['articleList'] = Article::query()->where('type', 1)->where('is_del', 0)->orderBy('sort', 'desc')->orderBy('id', 'desc')->limit(10)->paginate(15);

        return Response::view('user.help', $view);
    }

    // 更换订阅地址
    public function exchangeSubscribe(Request $request)
    {
        $user = Session::get('user');

        DB::beginTransaction();
        try {
            // 更换订阅地址
            $code = $this->makeSubscribeCode();
            UserSubscribe::query()->where('user_id', $user['id'])->update(['code' => $code]);

            // 更换连接密码
            User::query()->where('id', $user['id'])->update(['passwd' => makeRandStr()]);

            DB::commit();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '更换成功']);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::info("更换订阅地址异常：" . $e->getMessage());

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '更换失败' . $e->getMessage()]);
        }
    }

    // 转换成管理员的身份
    public function switchToAdmin(Request $request)
    {
        if (!Session::has('admin') || !Session::has('user')) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '非法请求']);
        }

        $admin = Session::get('admin');
        $user = User::query()->where('id', $admin['id'])->first();
        if (!$user) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => "非法请求"]);
        }

        // 管理员信息重新写入user
        Session::put('user', Session::get('admin'));
        Session::forget('admin');

        return Response::json(['status' => 'success', 'data' => '', 'message' => "身份切换成功"]);
    }

    // 卡券余额充值
    public function charge(Request $request)
    {
        $user = Session::get('user');

        $coupon_sn = trim($request->get('coupon_sn'));
        if (empty($coupon_sn)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '券码不能为空']);
        }

        $coupon = Coupon::query()->where('sn', $coupon_sn)->where('type', 3)->where('is_del', 0)->where('status', 0)->first();
        if (!$coupon) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该券不可用']);
        }

        DB::beginTransaction();
        try {
            $user = User::query()->where('id', $user['id'])->first();

            // 写入日志
            $this->addUserBalanceLog($user->id, 0, $user->balance, $user->balance + $coupon->amount, $coupon->amount, '用户手动充值 - [充值券：' . $coupon_sn . ']');

            // 余额充值
            $user->balance = $user->balance + $coupon->amount;
            $user->save();

            // 更改卡券状态
            $coupon->status = 1;
            $coupon->save();

            // 写入卡券日志
            $this->addCouponLog($coupon->id, 0, 0, '账户余额充值使用');

            DB::commit();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '充值成功']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '充值失败']);
        }
    }

    // 切换语言
    public function switchLang(Request $request, $locale)
    {
        Session::put("locale", $locale);

        return Redirect::back();
    }
}
