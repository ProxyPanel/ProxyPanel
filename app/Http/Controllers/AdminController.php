<?php

namespace App\Http\Controllers;

use App\Components\Helpers;
use App\Components\IPIP;
use App\Components\QQWry;
use App\Http\Models\Article;
use App\Http\Models\Config;
use App\Http\Models\Country;
use App\Http\Models\EmailLog;
use App\Http\Models\Invite;
use App\Http\Models\Label;
use App\Http\Models\Level;
use App\Http\Models\Order;
use App\Http\Models\ReferralApply;
use App\Http\Models\ReferralLog;
use App\Http\Models\SsConfig;
use App\Http\Models\SsGroup;
use App\Http\Models\SsGroupNode;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeInfo;
use App\Http\Models\SsNodeIp;
use App\Http\Models\SsNodeLabel;
use App\Http\Models\SsNodeOnlineLog;
use App\Http\Models\SsNodeTrafficDaily;
use App\Http\Models\SsNodeTrafficHourly;
use App\Http\Models\User;
use App\Http\Models\UserBalanceLog;
use App\Http\Models\UserBanLog;
use App\Http\Models\UserLabel;
use App\Http\Models\UserLoginLog;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserTrafficDaily;
use App\Http\Models\UserTrafficHourly;
use App\Http\Models\UserTrafficLog;
use App\Http\Models\UserTrafficModifyLog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Http\Request;
use Redirect;
use Response;
use Session;
use Log;
use DB;
use Auth;
use Hash;

/**
 * 管理员控制器
 *
 * Class AdminController
 *
 * @package App\Http\Controllers
 */
class AdminController extends Controller
{
    protected static $systemConfig;

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
    }

    public function index(Request $request)
    {
        $past = strtotime(date('Y-m-d', strtotime("-" . self::$systemConfig['expire_days'] . " days")));

        $view['expireDays'] = self::$systemConfig['expire_days'];
        $view['totalUserCount'] = User::query()->count(); // 总用户数
        $view['enableUserCount'] = User::query()->where('enable', 1)->count(); // 有效用户数
        $view['activeUserCount'] = User::query()->where('t', '>=', $past)->count(); // 活跃用户数
        $view['unActiveUserCount'] = User::query()->where('t', '<=', $past)->where('enable', 1)->where('t', '>', 0)->count(); // 不活跃用户数
        $view['onlineUserCount'] = User::query()->where('t', '>=', time() - 600)->count(); // 10分钟内在线用户数
        $view['expireWarningUserCount'] = User::query()->where('expire_time', '>=', date('Y-m-d', strtotime("now")))->where('expire_time', '<=', date('Y-m-d', strtotime("+" . self::$systemConfig['expire_days'] . " days")))->count(); // 临近过期用户数
        $view['largeTrafficUserCount'] = User::query()->whereRaw('(u + d) >= 107374182400')->whereIn('status', [0, 1])->count(); // 流量超过100G的用户

        // 1小时内流量异常用户
        $tempUsers = [];
        $userTotalTrafficList = UserTrafficHourly::query()->where('node_id', 0)->where('total', '>', 104857600)->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900))->groupBy('user_id')->selectRaw("user_id, sum(total) as totalTraffic")->get(); // 只统计100M以上的记录，加快速度
        if (!$userTotalTrafficList->isEmpty()) {
            foreach ($userTotalTrafficList as $vo) {
                if ($vo->totalTraffic > (self::$systemConfig['traffic_ban_value'] * 1024 * 1024 * 1024)) {
                    $tempUsers[] = $vo->user_id;
                }
            }
        }
        $view['flowAbnormalUserCount'] = User::query()->whereIn('id', $tempUsers)->count();


        $view['nodeCount'] = SsNode::query()->count();
        $view['unnormalNodeCount'] = SsNode::query()->where('status', 0)->count();

        $flowCount = SsNodeTrafficDaily::query()->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime("-30 days")))->sum('total');
        $view['flowCount'] = flowAutoShow($flowCount);
        $totalFlowCount = SsNodeTrafficDaily::query()->sum('total');
        $view['totalFlowCount'] = flowAutoShow($totalFlowCount);

        $view['totalBalance'] = User::query()->sum('balance') / 100;
        $view['totalWaitRefAmount'] = ReferralLog::query()->whereIn('status', [0, 1])->sum('ref_amount') / 100;
        $view['totalRefAmount'] = ReferralApply::query()->where('status', 2)->sum('amount') / 100;

        $view['totalOrder'] = Order::query()->count();
        $view['totalOnlinePayOrder'] = Order::query()->where('pay_way', 2)->count();
        $view['totalSuccessOrder'] = Order::query()->where('status', 2)->count();
        $view['todaySuccessOrder'] = Order::query()->where('status', 2)->where('created_at', '>=', date('Y-m-d 00:00:00'))->where('created_at', '<=', date('Y-m-d 23:59:59'))->count();

        return Response::view('admin.index', $view);
    }

    // 用户列表
    public function userList(Request $request)
    {
        $id = intval($request->get('id'));
        $username = trim($request->get('username'));
        $wechat = trim($request->get('wechat'));
        $qq = trim($request->get('qq'));
        $port = intval($request->get('port'));
        $pay_way = intval($request->get('pay_way'));
        $status = $request->get('status');
        $enable = $request->get('enable');
        $online = $request->get('online');
        $unActive = $request->get('unActive');
        $flowAbnormal = $request->get('flowAbnormal');
        $expireWarning = $request->get('expireWarning');
        $largeTraffic = $request->get('largeTraffic');

        $query = User::query()->with(['subscribe']);
        if (!empty($id)) {
            $query->where('id', $id);
        }

        if (!empty($username)) {
            $query->where('username', 'like', '%' . $username . '%');
        }

        if (!empty($wechat)) {
            $query->where('wechat', 'like', '%' . $wechat . '%');
        }

        if (!empty($qq)) {
            $query->where('qq', 'like', '%' . $qq . '%');
        }

        if ($port != '') {
            $query->where('port', $port);
        }

        if ($pay_way != '') {
            $query->where('pay_way', $pay_way);
        }

        if ($status != '') {
            $query->where('status', intval($status));
        }

        if ($enable != '') {
            $query->where('enable', intval($enable));
        }

        // 流量超过100G的
        if ($largeTraffic) {
            $query->whereIn('status', [0, 1])->whereRaw('(u + d) >= 107374182400');
        }

        // 临近过期提醒
        if ($expireWarning) {
            $query->where('expire_time', '>=', date('Y-m-d', strtotime("now")))->where('expire_time', '<=', date('Y-m-d', strtotime("+" . self::$systemConfig['expire_days'] . " days")));
        }

        // 当前在线
        if ($online) {
            $query->where('t', '>=', time() - 600);
        }

        // 不活跃用户
        if ($unActive) {
            $query->where('t', '>', 0)->where('t', '<=', strtotime(date('Y-m-d', strtotime("-" . self::$systemConfig['expire_days'] . " days"))))->where('enable', 1);
        }

        // 1小时内流量异常用户
        if ($flowAbnormal) {
            $tempUsers = [];
            $userTotalTrafficList = UserTrafficHourly::query()->where('node_id', 0)->where('total', '>', 104857600)->where('created_at', '>=', date('Y-m-d H:i:s', time() - 3900))->groupBy('user_id')->selectRaw("user_id, sum(total) as totalTraffic")->get(); // 只统计100M以上的记录，加快速度
            if (!$userTotalTrafficList->isEmpty()) {
                foreach ($userTotalTrafficList as $vo) {
                    if ($vo->totalTraffic > (self::$systemConfig['traffic_ban_value'] * 1024 * 1024 * 1024)) {
                        $tempUsers[] = $vo->user_id;
                    }
                }
            }
            $query->whereIn('id', $tempUsers);
        }

        $userList = $query->orderBy('id', 'desc')->paginate(15)->appends($request->except('page'));
        foreach ($userList as &$user) {
            $user->transfer_enable = flowAutoShow($user->transfer_enable);
            $user->used_flow = flowAutoShow($user->u + $user->d);
            if ($user->expire_time < date('Y-m-d', strtotime("now"))) {
                $user->expireWarning = -1; // 已过期
            } elseif ($user->expire_time == date('Y-m-d', strtotime("now"))) {
                $user->expireWarning = 0; // 今天过期
            } elseif ($user->expire_time > date('Y-m-d', strtotime("now")) && $user->expire_time <= date('Y-m-d', strtotime("+30 days"))) {
                $user->expireWarning = 1; // 最近一个月过期
            } else {
                $user->expireWarning = 2; // 大于一个月过期
            }

            // 流量异常警告
            $time = date('Y-m-d H:i:s', time() - 3900);
            $totalTraffic = UserTrafficHourly::query()->where('user_id', $user->id)->where('node_id', 0)->where('created_at', '>=', $time)->sum('total');
            $user->trafficWarning = $totalTraffic > (self::$systemConfig['traffic_ban_value'] * 1024 * 1024 * 1024) ? 1 : 0;

            // 订阅地址
            $user->link = (self::$systemConfig['subscribe_domain'] ? self::$systemConfig['subscribe_domain'] : self::$systemConfig['website_url']) . '/s/' . $user->subscribe->code;
        }

        $view['userList'] = $userList;

        return Response::view('admin.userList', $view);
    }

    // 添加账号
    public function addUser(Request $request)
    {
        if ($request->isMethod('POST')) {
            // 校验username是否已存在
            $exists = User::query()->where('username', $request->get('username'))->first();
            if ($exists) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '用户名已存在，请重新输入']);
            }

            if (!$request->get('usage')) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '请至少选择一种用途']);
            }

            $user = new User();
            $user->username = trim($request->get('username'));
            $user->password = trim($request->get('password')) ? Hash::make(trim($request->get('password'))) : Hash::make(makeRandStr());
            $user->port = $request->get('port');
            $user->passwd = empty($request->get('passwd')) ? makeRandStr() : $request->get('passwd');
            $user->vmess_id = $request->get('vmess_id') ? trim($request->get('vmess_id')) : createGuid();
            $user->transfer_enable = toGB($request->get('transfer_enable', 0));
            $user->enable = intval($request->get('enable', 0));
            $user->method = $request->get('method');
            $user->protocol = $request->get('protocol');
            $user->protocol_param = $request->get('protocol_param') ? $request->get('protocol_param') : '';
            $user->obfs = $request->get('obfs');
            $user->obfs_param = $request->get('obfs_param') ? $request->get('obfs_param') : '';
            $user->speed_limit_per_con = intval($request->get('speed_limit_per_con'));
            $user->speed_limit_per_user = intval($request->get('speed_limit_per_user'));
            $user->gender = intval($request->get('gender'));
            $user->wechat = $request->get('wechat') ? $request->get('wechat') : '';
            $user->qq = $request->get('qq') ? $request->get('qq') : '';
            $user->usage = $request->get('usage');
            $user->pay_way = $request->get('pay_way');
            $user->balance = 0;
            $user->enable_time = empty($request->get('enable_time')) ? date('Y-m-d') : $request->get('enable_time');
            $user->expire_time = empty($request->get('expire_time')) ? date('Y-m-d', strtotime("+365 days")) : $request->get('expire_time');
            $user->remark = str_replace("eval", "", str_replace("atob", "", $request->get('remark')));
            $user->level = $request->get('level') ? $request->get('level') : 1;
            $user->is_admin = 0;
            $user->reg_ip = getClientIp();
            $user->referral_uid = 0;
            $user->traffic_reset_day = 0;
            $user->status = $request->get('status') ? $request->get('status') : 1;
            $user->save();

            if ($user->id) {
                // 生成订阅码
                $subscribe = new UserSubscribe();
                $subscribe->user_id = $user->id;
                $subscribe->code = Helpers::makeSubscribeCode();
                $subscribe->times = 0;
                $subscribe->save();

                // 生成用户标签
                $this->makeUserLabels($user->id, $request->get('labels'));

                // 写入用户流量变动记录
                Helpers::addUserTrafficModifyLog($user->id, 0, 0, toGB($request->get('transfer_enable', 0)), '后台手动添加用户');

                return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
            } else {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '添加失败']);
            }
        } else {
            // 生成一个可用端口
            $view['last_port'] = self::$systemConfig['is_rand_port'] ? Helpers::getRandPort() : Helpers::getOnlyPort();
            $view['method_list'] = Helpers::methodList();
            $view['protocol_list'] = Helpers::protocolList();
            $view['obfs_list'] = Helpers::obfsList();
            $view['level_list'] = Helpers::levelList();
            $view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();
            $view['initial_labels'] = explode(",", Helpers::systemConfig()['initial_labels_for_user']);

            return Response::view('admin.addUser', $view);
        }
    }

    // 批量生成账号
    public function batchAddUsers(Request $request)
    {
        DB::beginTransaction();
        try {
            for ($i = 0; $i < 5; $i++) {
                // 生成一个可用端口
                $port = self::$systemConfig['is_rand_port'] ? Helpers::getRandPort() : Helpers::getOnlyPort();

                $user = new User();
                $user->username = '批量生成-' . makeRandStr();
                $user->password = Hash::make(makeRandStr());
                $user->port = $port;
                $user->passwd = makeRandStr();
                $user->vmess_id = createGuid();
                $user->enable = 1;
                $user->method = Helpers::getDefaultMethod();
                $user->protocol = Helpers::getDefaultProtocol();
                $user->protocol_param = '';
                $user->obfs = Helpers::getDefaultObfs();
                $user->obfs_param = '';
                $user->usage = 1;
                $user->transfer_enable = toGB(1024);
                $user->enable_time = date('Y-m-d');
                $user->expire_time = date('Y-m-d', strtotime("+365 days"));
                $user->remark = '';
                $user->reg_ip = getClientIp();
                $user->referral_uid = 0;
                $user->traffic_reset_day = 0;
                $user->status = 1;
                $user->save();

                if ($user->id) {
                    // 生成订阅码
                    $subscribe = new UserSubscribe();
                    $subscribe->user_id = $user->id;
                    $subscribe->code = Helpers::makeSubscribeCode();
                    $subscribe->times = 0;
                    $subscribe->save();

                    // 初始化默认标签
                    if (!empty(self::$systemConfig['initial_labels_for_user'])) {
                        $labels = explode(',', self::$systemConfig['initial_labels_for_user']);
                        $this->makeUserLabels($user->id, $labels);
                    }

                    // 写入用户流量变动记录
                    Helpers::addUserTrafficModifyLog($user->id, 0, 0, toGB(1024), '后台批量生成用户');
                }
            }

            DB::commit();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '批量生成账号成功']);
        } catch (\Exception $e) {
            DB::rollBack();

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '批量生成账号失败：' . $e->getMessage()]);
        }
    }

    // 编辑账号
    public function editUser(Request $request)
    {
        $id = intval($request->get('id'));

        if ($request->isMethod('POST')) {
            $username = trim($request->get('username'));
            $password = $request->get('password');
            $port = intval($request->get('port'));
            $passwd = $request->get('passwd');
            $vmess_id = $request->get('vmess_id') ? trim($request->get('vmess_id')) : createGuid();
            $transfer_enable = $request->get('transfer_enable');
            $enable = intval($request->get('enable'));
            $method = $request->get('method');
            $protocol = $request->get('protocol');
            $protocol_param = $request->get('protocol_param');
            $obfs = $request->get('obfs');
            $obfs_param = $request->get('obfs_param');
            $speed_limit_per_con = intval($request->get('speed_limit_per_con'));
            $speed_limit_per_user = intval($request->get('speed_limit_per_user'));
            $gender = intval($request->get('gender'));
            $wechat = $request->get('wechat');
            $qq = $request->get('qq');
            $usage = $request->get('usage');
            $pay_way = $request->get('pay_way');
            $status = $request->get('status');
            $labels = $request->get('labels');
            $enable_time = $request->get('enable_time');
            $expire_time = $request->get('expire_time');
            $remark = str_replace("eval", "", str_replace("atob", "", $request->get('remark')));
            $level = $request->get('level');
            $is_admin = $request->get('is_admin');

            // 校验username是否已存在
            $exists = User::query()->where('id', '<>', $id)->where('username', $username)->first();
            if ($exists) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '用户名已存在，请重新输入']);
            }

            // 校验端口是否已存在
            $exists = User::query()->where('id', '<>', $id)->where('port', '>', 0)->where('port', $port)->first();
            if ($exists) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '端口已存在，请重新输入']);
            }

            // 禁止取消默认管理员
            if ($id == 1 && $is_admin == 0) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '系统默认管理员不可取消']);
            }

            if (!$request->get('usage')) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '请至少选择一种用途']);
            }

            // 用户编辑前的信息
            $user = User::query()->where('id', $id)->first();

            DB::beginTransaction();
            try {
                $data = [
                    'username'             => $username,
                    'port'                 => $port,
                    'passwd'               => $passwd,
                    'vmess_id'             => $vmess_id,
                    'transfer_enable'      => toGB($transfer_enable),
                    'enable'               => $status < 0 ? 0 : $enable, // 如果禁止登陆则同时禁用代理
                    'method'               => $method,
                    'protocol'             => $protocol,
                    'protocol_param'       => $protocol_param,
                    'obfs'                 => $obfs,
                    'obfs_param'           => $obfs_param,
                    'speed_limit_per_con'  => $speed_limit_per_con,
                    'speed_limit_per_user' => $speed_limit_per_user,
                    'gender'               => $gender,
                    'wechat'               => $wechat,
                    'qq'                   => $qq,
                    'usage'                => $usage,
                    'pay_way'              => $pay_way,
                    'status'               => $status,
                    'enable_time'          => empty($enable_time) ? date('Y-m-d') : $enable_time,
                    'expire_time'          => empty($expire_time) ? date('Y-m-d', strtotime("+365 days")) : $expire_time,
                    'remark'               => $remark,
                    'level'                => $level,
                ];

                // 只有admin才有权限操作管理员属性
                if (Auth::user()->id == 1) {
                    $data['is_admin'] = $is_admin;
                }

                if (!empty($password)) {
                    if (!(env('APP_DEMO') && $id == 1)) { // 演示环境禁止修改管理员密码
                        $data['password'] = Hash::make($password);
                    }
                }

                User::query()->where('id', $id)->update($data);

                // 重新生成用户标签
                $this->makeUserLabels($id, $labels);

                // 写入用户流量变动记录
                if ($user->transfer_enable != toGB($transfer_enable)) {
                    Helpers::addUserTrafficModifyLog($id, 0, $user->transfer_enable, toGB($transfer_enable), '后台手动编辑用户');
                }

                DB::commit();

                return Response::json(['status' => 'success', 'data' => '', 'message' => '编辑成功']);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('编辑用户信息异常：' . $e->getMessage());

                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败']);
            }
        } else {
            $user = User::query()->with(['label', 'referral'])->where('id', $id)->first();
            if ($user) {
                $user->transfer_enable = flowToGB($user->transfer_enable);

                // 处理标签
                $label = [];
                foreach ($user->label as $vo) {
                    $label[] = $vo->label_id;
                }
                $user->labels = $label;

                // 处理用途
                $user->usage = explode(',', $user->usage);
            }

            $view['user'] = $user;
            $view['method_list'] = Helpers::methodList();
            $view['protocol_list'] = Helpers::protocolList();
            $view['obfs_list'] = Helpers::obfsList();
            $view['level_list'] = Helpers::levelList();
            $view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

            return Response::view('admin.editUser', $view);
        }
    }

    // 删除用户
    public function delUser(Request $request)
    {
        $id = $request->get('id');

        if ($id <= 1) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '系统管理员不可删除']);
        }

        DB::beginTransaction();
        try {
            User::query()->where('id', $id)->delete();
            UserSubscribe::query()->where('user_id', $id)->delete();
            UserBanLog::query()->where('user_id', $id)->delete();
            UserLabel::query()->where('user_id', $id)->delete();
            UserBalanceLog::query()->where('user_id', $id)->delete();
            UserTrafficModifyLog::query()->where('user_id', $id)->delete();
            UserLoginLog::query()->where('user_id', $id)->delete();

            DB::commit();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
        }
    }

    // 节点列表
    public function nodeList(Request $request)
    {
        $status = $request->input('status');

        $query = SsNode::query();

        if ($status != '') {
            $query->where('status', intval($status));
        }

        $nodeList = $query->orderBy('status', 'desc')->orderBy('id', 'asc')->paginate(15)->appends($request->except('page'));
        foreach ($nodeList as &$node) {
            // 在线人数
            $online_log = SsNodeOnlineLog::query()->where('node_id', $node->id)->where('log_time', '>=', strtotime("-5 minutes"))->orderBy('id', 'desc')->first();
            $node->online_users = empty($online_log) ? 0 : $online_log->online_user;

            // 已产生流量
            $totalTraffic = SsNodeTrafficDaily::query()->where('node_id', $node->id)->sum('total');
            $node->transfer = flowAutoShow($totalTraffic);

            // 负载（10分钟以内）
            $node_info = SsNodeInfo::query()->where('node_id', $node->id)->where('log_time', '>=', strtotime("-10 minutes"))->orderBy('id', 'desc')->first();
            $node->load = $node->is_transit ? '' : (empty($node_info) || empty($node_info->load) ? '离线' : $node_info->load);
            $node->uptime = empty($node_info) ? 0 : seconds2time($node_info->uptime);
        }

        $view['nodeList'] = $nodeList;

        return Response::view('admin.nodeList', $view);
    }

    // 添加节点
    public function addNode(Request $request)
    {
        if ($request->isMethod('POST')) {
            if ($request->get('ssh_port') <= 0 || $request->get('ssh_port') >= 65535) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '添加失败：SSH端口不合法']);
            }

            if (false === filter_var($request->get('ip'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '添加失败：IPv4地址不合法']);
            }

            if ($request->get('ipv6') && false === filter_var($request->get('ipv6'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '添加失败：IPv6地址不合法']);
            }

            if ($request->get('server')) {
                $domain = $request->get('server');
                $domain = explode('.', $domain);
                $domainSuffix = end($domain); // 取得域名后缀

                if (!in_array($domainSuffix, \config('domains'))) {
                    return Response::json(['status' => 'fail', 'data' => '', 'message' => '绑定域名不合法']);
                }
            }

            // TODO：判断是否已存在绑定了相同域名的节点，提示是否要强制替换，或者不提示之前强制将其他节点的绑定域名置为空，然后发起域名绑定请求，或者请求进入队列

            DB::beginTransaction();
            try {
                $ssNode = new SsNode();
                $ssNode->type = intval($request->get('type'));
                $ssNode->name = $request->get('name');
                $ssNode->group_id = $request->get('group_id') ? intval($request->get('group_id')) : 0;
                $ssNode->country_code = $request->get('country_code') ? $request->get('country_code') : 'un';
                $ssNode->server = $request->get('server') ? $request->get('server') : '';
                $ssNode->ip = $request->get('ip');
                $ssNode->ipv6 = $request->get('ipv6');
                $ssNode->desc = $request->get('desc') ? $request->get('desc') : '';
                $ssNode->method = $request->get('method');
                $ssNode->protocol = $request->get('protocol');
                $ssNode->protocol_param = $request->get('protocol_param') ? $request->get('protocol_param') : '';
                $ssNode->obfs = $request->get('obfs') ? $request->get('obfs') : '';
                $ssNode->obfs_param = $request->get('obfs_param') ? $request->get('obfs_param') : '';
                $ssNode->traffic_rate = $request->get('traffic_rate') ? $request->get('traffic_rate') : 1;
                $ssNode->bandwidth = $request->get('bandwidth') ? $request->get('bandwidth') : 1000;
                $ssNode->traffic = $request->get('traffic') ? $request->get('traffic') : 1000;
                $ssNode->monitor_url = $request->get('monitor_url') ? $request->get('monitor_url') : '';
                $ssNode->is_subscribe = intval($request->get('is_subscribe'));
                $ssNode->is_nat = intval($request->get('is_nat'));
                $ssNode->is_transit = intval($request->get('is_transit'));
                $ssNode->ssh_port = $request->get('ssh_port') ? intval($request->get('ssh_port')) : 22;
                $ssNode->is_tcp_check = intval($request->get('is_tcp_check'));
                $ssNode->compatible = intval($request->get('type')) == 2 ? 0 : (intval($request->get('is_nat')) ? 0 : intval($request->get('compatible')));
                $ssNode->single = intval($request->get('single'));
                $ssNode->single_force = intval($request->get('single')) ? intval($request->get('single_force')) : 0;
                $ssNode->single_port = intval($request->get('single')) ? ($request->get('single_port') ? $request->get('single_port') : 443) : '';
                $ssNode->single_passwd = intval($request->get('single')) ? ($request->get('single_passwd') ? $request->get('single_passwd') : 'password') : '';
                $ssNode->single_method = intval($request->get('single')) ? $request->get('single_method') : '';
                $ssNode->single_protocol = intval($request->get('single')) ? $request->get('single_protocol') : '';
                $ssNode->single_obfs = intval($request->get('single')) ? $request->get('single_obfs') : '';
                $ssNode->sort = $request->get('sort') ? intval($request->get('sort')) : 0;
                $ssNode->status = $request->get('status') ? intval($request->get('status')) : 1;
                $ssNode->v2_alter_id = $request->get('v2_alter_id') ? intval($request->get('v2_alter_id')) : 16;
                $ssNode->v2_port = $request->get('v2_port') ? intval($request->get('v2_port')) : 10087;
                $ssNode->v2_method = $request->get('v2_method') ? $request->get('v2_method') : 'aes-128-gcm';
                $ssNode->v2_net = $request->get('v2_net') ? $request->get('v2_net') : 'tcp';
                $ssNode->v2_type = $request->get('v2_type') ? $request->get('v2_type') : 'none';
                $ssNode->v2_host = $request->get('v2_host') ? $request->get('v2_host') : '';
                $ssNode->v2_path = $request->get('v2_path') ? $request->get('v2_path') : '';
                $ssNode->v2_tls = $request->get('v2_tls') ? intval($request->get('v2_tls')) : 0;
                $ssNode->v2_insider_port = $request->get('v2_insider_port') ? intval($request->get('v2_insider_port')) : 10550;
                $ssNode->v2_outsider_port = $request->get('v2_outsider_port') ? intval($request->get('v2_outsider_port')) : 443;
                $ssNode->save();

                // 建立分组关联
                if ($ssNode->id && $request->get('group_id', 0)) {
                    $ssGroupNode = new SsGroupNode();
                    $ssGroupNode->group_id = $request->get('group_id', 0);
                    $ssGroupNode->node_id = $ssNode->id;
                    $ssGroupNode->save();
                }

                // 生成节点标签
                $labels = $request->get('labels');
                if ($ssNode->id && !empty($labels)) {
                    foreach ($labels as $label) {
                        $ssNodeLabel = new SsNodeLabel();
                        $ssNodeLabel->node_id = $ssNode->id;
                        $ssNodeLabel->label_id = $label;
                        $ssNodeLabel->save();
                    }
                }

                DB::commit();

                return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('添加节点信息异常：' . $e->getMessage());

                return Response::json(['status' => 'fail', 'data' => '', 'message' => '添加失败：' . $e->getMessage()]);
            }
        } else {
            $view['method_list'] = Helpers::methodList();
            $view['protocol_list'] = Helpers::protocolList();
            $view['obfs_list'] = Helpers::obfsList();
            $view['level_list'] = Helpers::levelList();
            $view['group_list'] = SsGroup::query()->get();
            $view['country_list'] = Country::query()->orderBy('country_code', 'asc')->get();
            $view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

            return Response::view('admin.addNode', $view);
        }
    }

    // 编辑节点
    public function editNode(Request $request)
    {
        $id = $request->get('id');

        if ($request->isMethod('POST')) {
            if ($request->get('ssh_port') <= 0 || $request->get('ssh_port') >= 65535) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败：SSH端口不合法']);
            }

            if (false === filter_var($request->get('ip'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败：IPv4地址不合法']);
            }

            if ($request->get('ipv6') && false === filter_var($request->get('ipv6'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败：IPv6地址不合法']);
            }

            if ($request->get('server')) {
                $domain = $request->get('server');
                $domain = explode('.', $domain);
                $domainSuffix = end($domain); // 取得域名后缀

                if (!in_array($domainSuffix, \config('domains'))) {
                    return Response::json(['status' => 'fail', 'data' => '', 'message' => '绑定域名不合法']);
                }
            }

            if ($request->get('v2_alter_id') <= 0 || $request->get('v2_alter_id') >= 65535) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败：AlterId不合法']);
            }

            DB::beginTransaction();
            try {
                $data = [
                    'type'             => intval($request->get('type')),
                    'name'             => $request->get('name'),
                    'group_id'         => $request->get('group_id') ? $request->get('group_id') : 0,
                    'country_code'     => $request->get('country_code'),
                    'server'           => $request->get('server'),
                    'ip'               => $request->get('ip'),
                    'ipv6'             => $request->get('ipv6'),
                    'desc'             => $request->get('desc'),
                    'method'           => $request->get('method'),
                    'protocol'         => $request->get('protocol'),
                    'protocol_param'   => $request->get('protocol_param'),
                    'obfs'             => $request->get('obfs'),
                    'obfs_param'       => $request->get('obfs_param'),
                    'traffic_rate'     => $request->get('traffic_rate'),
                    'bandwidth'        => $request->get('bandwidth') ? $request->get('bandwidth') : 1000,
                    'traffic'          => $request->get('traffic') ? $request->get('traffic') : 1000,
                    'monitor_url'      => $request->get('monitor_url') ? $request->get('monitor_url') : '',
                    'is_subscribe'     => intval($request->get('is_subscribe')),
                    'is_nat'           => intval($request->get('is_nat')),
                    'is_transit'       => intval($request->get('is_transit')),
                    'ssh_port'         => intval($request->get('ssh_port')),
                    'is_tcp_check'     => intval($request->get('is_tcp_check')),
                    'compatible'       => intval($request->get('type')) == 2 ? 0 : (intval($request->get('is_nat')) ? 0 : intval($request->get('compatible'))),
                    'single'           => intval($request->get('single')),
                    'single_force'     => intval($request->get('single')) ? intval($request->get('single_force')) : 0,
                    'single_port'      => intval($request->get('single')) ? ($request->get('single_port') ? $request->get('single_port') : 443) : '',
                    'single_passwd'    => intval($request->get('single')) ? ($request->get('single_passwd') ? $request->get('single_passwd') : 'password') : '',
                    'single_method'    => intval($request->get('single')) ? $request->get('single_method') : '',
                    'single_protocol'  => intval($request->get('single')) ? $request->get('single_protocol') : '',
                    'single_obfs'      => intval($request->get('single')) ? $request->get('single_obfs') : '',
                    'sort'             => intval($request->get('sort')),
                    'status'           => intval($request->get('status')),
                    'v2_alter_id'      => $request->get('v2_alter_id') ? intval($request->get('v2_alter_id')) : 16,
                    'v2_port'          => $request->get('v2_port') ? intval($request->get('v2_port')) : 10087,
                    'v2_method'        => $request->get('v2_method') ? $request->get('v2_method') : 'aes-128-gcm',
                    'v2_net'           => $request->get('v2_net'),
                    'v2_type'          => $request->get('v2_type'),
                    'v2_host'          => $request->get('v2_host'),
                    'v2_path'          => $request->get('v2_path'),
                    'v2_tls'           => intval($request->get('v2_tls')),
                    'v2_insider_port'  => intval($request->get('v2_insider_port', 10550)),
                    'v2_outsider_port' => intval($request->get('v2_outsider_port', 443))
                ];

                SsNode::query()->where('id', $id)->update($data);

                // 建立分组关联
                if ($request->get('group_id')) {
                    // 先删除该节点所有关联
                    SsGroupNode::query()->where('node_id', $id)->delete();

                    // 建立关联
                    $ssGroupNode = new SsGroupNode();
                    $ssGroupNode->group_id = $request->get('group_id');
                    $ssGroupNode->node_id = $id;
                    $ssGroupNode->save();
                }

                // 生成节点标签
                $this->makeNodeLabels($id, $request->get('labels'));

                // TODO:更新节点绑定的域名DNS（将节点IP更新到域名DNS 的A记录）

                DB::commit();

                return Response::json(['status' => 'success', 'data' => '', 'message' => '编辑成功']);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('编辑节点信息异常：' . $e->getMessage());

                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败：' . $e->getMessage()]);
            }
        } else {
            $node = SsNode::query()->with(['label'])->where('id', $id)->first();
            if ($node) {
                $labels = [];
                foreach ($node->label as $vo) {
                    $labels[] = $vo->label_id;
                }
                $node->labels = $labels;
            }

            $view['node'] = $node;
            $view['method_list'] = Helpers::methodList();
            $view['protocol_list'] = Helpers::protocolList();
            $view['obfs_list'] = Helpers::obfsList();
            $view['level_list'] = Helpers::levelList();
            $view['group_list'] = SsGroup::query()->get();
            $view['country_list'] = Country::query()->orderBy('country_code', 'asc')->get();
            $view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

            return Response::view('admin.editNode', $view);
        }
    }

    // 删除节点
    public function delNode(Request $request)
    {
        $id = $request->get('id');

        $node = SsNode::query()->where('id', $id)->first();
        if (!$node) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '节点不存在，请重试']);
        }

        DB::beginTransaction();
        try {
            // 删除分组关联、节点标签、节点相关日志
            SsNode::query()->where('id', $id)->delete();
            SsGroupNode::query()->where('node_id', $id)->delete();
            SsNodeLabel::query()->where('node_id', $id)->delete();
            SsNodeInfo::query()->where('node_id', $id)->delete();
            SsNodeOnlineLog::query()->where('node_id', $id)->delete();
            SsNodeTrafficDaily::query()->where('node_id', $id)->delete();
            SsNodeTrafficHourly::query()->where('node_id', $id)->delete();
            UserTrafficDaily::query()->where('node_id', $id)->delete();
            UserTrafficHourly::query()->where('node_id', $id)->delete();
            UserTrafficLog::query()->where('node_id', $id)->delete();

            DB::commit();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('删除节点信息异常：' . $e->getMessage());

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败：' . $e->getMessage()]);
        }
    }

    // 节点流量监控
    public function nodeMonitor(Request $request)
    {
        $node_id = $request->get('id');

        $node = SsNode::query()->where('id', $node_id)->orderBy('sort', 'desc')->first();
        if (!$node) {
            Session::flash('errorMsg', '节点不存在，请重试');

            return Redirect::back();
        }

        // 查看流量
        $dailyData = [];
        $hourlyData = [];

        // 节点一个月内的流量
        $nodeTrafficDaily = SsNodeTrafficDaily::query()->with(['info'])->where('node_id', $node->id)->where('created_at', '>=', date('Y-m', time()))->orderBy('created_at', 'asc')->pluck('total')->toArray();
        $dailyTotal = date('d', time()) - 1;//今天不算，减一
        $dailyCount = count($nodeTrafficDaily);
        for ($x = 0; $x < ($dailyTotal - $dailyCount); $x++) {
            $dailyData[$x] = 0;
        }
        for ($x = ($dailyTotal - $dailyCount); $x < $dailyTotal; $x++) {
            $dailyData[$x] = round($nodeTrafficDaily[$x - ($dailyTotal - $dailyCount)] / (1024 * 1024 * 1024), 3);
        }

        // 节点一天内的流量
        $nodeTrafficHourly = SsNodeTrafficHourly::query()->with(['info'])->where('node_id', $node->id)->where('created_at', '>=', date('Y-m-d', time()))->orderBy('created_at', 'asc')->pluck('total')->toArray();
        $hourlyTotal = date('H', time());
        $hourlyCount = count($nodeTrafficHourly);
        for ($x = 0; $x < ($hourlyTotal - $hourlyCount); $x++) {
            $hourlyData[$x] = 0;
        }
        for ($x = ($hourlyTotal - $hourlyCount); $x < $hourlyTotal; $x++) {
            $hourlyData[$x] = round($nodeTrafficHourly[$x - ($hourlyTotal - $hourlyCount)] / (1024 * 1024 * 1024), 3);
        }

        $view['trafficDaily'] = [
            'nodeName'  => $node->name,
            'dailyData' => "'" . implode("','", $dailyData) . "'"
        ];

        $view['trafficHourly'] = [
            'nodeName'   => $node->name,
            'hourlyData' => "'" . implode("','", $hourlyData) . "'"
        ];

        // 本月天数数据
        $monthDays = [];
        $monthHasDays = date("t");
        for ($i = 1; $i <= $monthHasDays; $i++) {
            $monthDays[] = $i;
        }

        $view['nodeName'] = $node->name;
        $view['nodeServer'] = $node->server;
        $view['monthDays'] = "'" . implode("','", $monthDays) . "'";

        return Response::view('admin.nodeMonitor', $view);
    }

    // 文章列表
    public function articleList(Request $request)
    {
        $view['list'] = Article::query()->orderBy('sort', 'desc')->paginate(15)->appends($request->except('page'));

        return Response::view('admin.articleList', $view);
    }

    // 添加文章
    public function addArticle(Request $request)
    {
        if ($request->isMethod('POST')) {
            // LOGO
            $logo = '';
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileType = $file->getClientOriginalExtension();

                // 验证文件合法性
                if (!in_array($fileType, ['jpg', 'png', 'jpeg', 'bmp'])) {
                    Session::flash('errorMsg', 'LOGO不合法');

                    return Redirect::back()->withInput();
                }

                $logoName = date('YmdHis') . mt_rand(1000, 2000) . '.' . $fileType;
                $move = $file->move(base_path() . '/public/upload/image/', $logoName);
                $logo = $move ? '/upload/image/' . $logoName : '';
            }

            $article = new Article();
            $article->title = $request->get('title');
            $article->type = $request->get('type', 1);
            $article->author = '管理员';
            $article->summary = $request->get('summary');
            $article->logo = $logo;
            $article->content = $request->get('editorValue');
            $article->sort = $request->get('sort', 0);
            $article->save();

            if ($article->id) {
                Session::flash('successMsg', '添加成功');
            } else {
                Session::flash('errorMsg', '添加失败');
            }

            return Redirect::to('admin/articleList');
        } else {
            return Response::view('admin.addArticle');
        }
    }

    // 编辑文章
    public function editArticle(Request $request)
    {
        $id = $request->get('id');

        if ($request->isMethod('POST')) {
            $title = $request->get('title');
            $type = $request->get('type');
            $summary = $request->get('summary');
            $content = $request->get('editorValue');
            $sort = $request->get('sort');

            // 商品LOGO
            $logo = '';
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileType = $file->getClientOriginalExtension();

                // 验证文件合法性
                if (!in_array($fileType, ['jpg', 'png', 'jpeg', 'bmp'])) {
                    Session::flash('errorMsg', 'LOGO不合法');

                    return Redirect::back()->withInput();
                }

                $logoName = date('YmdHis') . mt_rand(1000, 2000) . '.' . $fileType;
                $move = $file->move(base_path() . '/public/upload/image/', $logoName);
                $logo = $move ? '/upload/image/' . $logoName : '';
            }

            $data = [
                'type'    => $type,
                'title'   => $title,
                'summary' => $summary,
                'content' => $content,
                'sort'    => $sort
            ];

            if ($logo) {
                $data['logo'] = $logo;
            }

            $ret = Article::query()->where('id', $id)->update($data);
            if ($ret) {
                Session::flash('successMsg', '编辑成功');
            } else {
                Session::flash('errorMsg', '编辑失败');
            }

            return Redirect::to('admin/editArticle?id=' . $id);
        } else {
            $view['article'] = Article::query()->where('id', $id)->first();

            return Response::view('admin.editArticle', $view);
        }
    }

    // 删除文章
    public function delArticle(Request $request)
    {
        $id = $request->get('id');

        $ret = Article::query()->where('id', $id)->delete();
        if ($ret) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
        }
    }

    // 节点分组列表
    public function groupList(Request $request)
    {
        $view['groupList'] = SsGroup::query()->paginate(15)->appends($request->except('page'));

        $levelList = Helpers::levelList();
        $levelMap = [];
        foreach ($levelList as $vo) {
            $levelMap[$vo['level']] = $vo['level_name'];
        }
        $view['levelMap'] = $levelMap;

        return Response::view('admin.groupList', $view);
    }

    // 添加节点分组
    public function addGroup(Request $request)
    {
        if ($request->isMethod('POST')) {
            $ssGroup = new SsGroup();
            $ssGroup->name = $request->get('name');
            $ssGroup->level = $request->get('level');
            $ssGroup->save();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            $view['levelList'] = Helpers::levelList();

            return Response::view('admin.addGroup', $view);
        }
    }

    // 编辑节点分组
    public function editGroup(Request $request)
    {
        $id = $request->get('id');

        if ($request->isMethod('POST')) {
            $name = $request->get('name');
            $level = $request->get('level');

            $data = [
                'name'  => $name,
                'level' => $level
            ];

            $ret = SsGroup::query()->where('id', $id)->update($data);
            if ($ret) {
                return Response::json(['status' => 'success', 'data' => '', 'message' => '编辑成功']);
            } else {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败']);
            }
        } else {
            $view['group'] = SsGroup::query()->where('id', $id)->first();
            $view['levelList'] = Helpers::levelList();

            return Response::view('admin.editGroup', $view);
        }
    }

    // 删除节点分组
    public function delGroup(Request $request)
    {
        $id = $request->get('id');

        // 检查是否该分组下是否有节点
        $ssGroupNodeCount = SsGroupNode::query()->where('group_id', $id)->count();
        if ($ssGroupNodeCount) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败：该分组下有节点关联，请先解除关联']);
        }

        $ret = SsGroup::query()->where('id', $id)->delete();
        if ($ret) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
        }
    }

    // 流量日志
    public function trafficLog(Request $request)
    {
        $port = $request->get('port');
        $user_id = $request->get('user_id');
        $username = trim($request->get('username'));
        $nodeId = intval($request->get('nodeId'));

        $query = UserTrafficLog::query()->with(['user', 'node']);

        if ($port) {
            $query->whereHas('user', function ($q) use ($port) {
                $q->where('port', $port);
            });
        }

        if ($user_id) {
            $query->where('user_id', intval($user_id));
        }

        if ($username) {
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', 'like', '%' . $username . '%');
            });
        }

        if ($nodeId) {
            $query->where('node_id', $nodeId);
        }

        // 已使用流量
        $view['totalTraffic'] = flowAutoShow($query->sum('u') + $query->sum('d'));

        $list = $query->orderBy('id', 'desc')->paginate(20)->appends($request->except('page'));
        foreach ($list as &$vo) {
            $vo->u = flowAutoShow($vo->u);
            $vo->d = flowAutoShow($vo->d);
            $vo->log_time = date('Y-m-d H:i:s', $vo->log_time);
        }

        $view['list'] = $list;
        $view['nodeList'] = SsNode::query()->where('status', 1)->orderBy('sort', 'desc')->orderBy('id', 'desc')->get();

        return Response::view('admin.trafficLog', $view);
    }

    // SS(R)链接反解析
    public function decompile(Request $request)
    {
        if ($request->isMethod('POST')) {
            $content = $request->get('content');

            if (empty($content)) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '请在左侧填入要反解析的SS(R)链接']);
            }

            // 反解析处理
            $content = str_replace("\n", ",", $content);
            $content = explode(',', $content);
            $txt = '';
            foreach ($content as $item) {
                // 判断是SS还是SSR链接
                $str = '';
                if (false !== strpos($item, 'ssr://')) {
                    $str = mb_substr($item, 6);
                } elseif (false !== strpos($item, 'ss://')) {
                    $str = mb_substr($item, 5);
                }

                $txt .= "\r\n" . base64url_decode($str);
            }

            // 生成转换好的JSON文件
            file_put_contents(public_path('downloads/decompile.json'), $txt);

            return Response::json(['status' => 'success', 'data' => $txt, 'message' => '反解析成功']);
        } else {
            return Response::view('admin.decompile');
        }
    }

    // 格式转换(SS转SSR)
    public function convert(Request $request)
    {
        if ($request->isMethod('POST')) {
            $method = $request->get('method');
            $transfer_enable = $request->get('transfer_enable');
            $protocol = $request->get('protocol');
            $protocol_param = $request->get('protocol_param');
            $obfs = $request->get('obfs');
            $obfs_param = $request->get('obfs_param');
            $content = $request->get('content');

            if (empty($content)) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '请在左侧填入要转换的内容']);
            }

            // 校验格式
            $content = json_decode($content);
            if (empty($content->port_password)) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '转换失败：配置信息里缺少【port_password】字段，或者该字段为空']);
            }

            // 转换成SSR格式JSON
            $data = [];
            foreach ($content->port_password as $port => $passwd) {
                $data[] = [
                    'd'               => 0,
                    'enable'          => 1,
                    'method'          => $method,
                    'obfs'            => $obfs,
                    'obfs_param'      => empty($obfs_param) ? "" : $obfs_param,
                    'passwd'          => $passwd,
                    'port'            => $port,
                    'protocol'        => $protocol,
                    'protocol_param'  => empty($protocol_param) ? "" : $protocol_param,
                    'transfer_enable' => toGB($transfer_enable),
                    'u'               => 0,
                    'user'            => date('Ymd') . '_IMPORT_' . $port,
                ];
            }

            $json = json_encode($data);

            // 生成转换好的JSON文件
            file_put_contents(public_path('downloads/convert.json'), $json);

            return Response::json(['status' => 'success', 'data' => $json, 'message' => '转换成功']);
        } else {
            // 加密方式、协议、混淆
            $view['method_list'] = Helpers::methodList();
            $view['protocol_list'] = Helpers::protocolList();
            $view['obfs_list'] = Helpers::obfsList();

            return Response::view('admin.convert', $view);
        }
    }

    // 下载转换好的JSON文件
    public function download(Request $request)
    {
        $type = $request->get('type');
        if (empty($type)) {
            exit('参数异常');
        }

        if ($type == '1') {
            $filePath = public_path('downloads/convert.json');
        } else {
            $filePath = public_path('downloads/decompile.json');
        }

        if (!file_exists($filePath)) {
            exit('文件不存在，请检查目录权限');
        }

        return Response::download($filePath);
    }

    // 数据导入
    public function import(Request $request)
    {
        if ($request->isMethod('POST')) {
            if (!$request->hasFile('uploadFile')) {
                Session::flash('errorMsg', '请选择要上传的文件');

                return Redirect::back();
            }

            $file = $request->file('uploadFile');

            // 只能上传JSON文件
            if ($file->getClientMimeType() != 'application/json' || $file->getClientOriginalExtension() != 'json') {
                Session::flash('errorMsg', '只允许上传JSON文件');

                return Redirect::back();
            }

            if (!$file->isValid()) {
                Session::flash('errorMsg', '产生未知错误，请重新上传');

                return Redirect::back();
            }

            $save_path = realpath(storage_path('uploads'));
            $new_name = md5($file->getClientOriginalExtension()) . '.json';
            $file->move($save_path, $new_name);

            // 读取文件内容
            $data = file_get_contents($save_path . '/' . $new_name);
            $data = json_decode($data);
            if (!$data) {
                Session::flash('errorMsg', '内容格式解析异常，请上传符合SSR(R)配置规范的JSON文件');

                return Redirect::back();
            }

            DB::beginTransaction();
            try {
                foreach ($data as $user) {
                    $obj = new User();
                    $obj->username = $user->user;
                    $obj->password = Hash::make('123456');
                    $obj->port = $user->port;
                    $obj->passwd = $user->passwd;
                    $obj->vmess_id = $user->vmess_id;
                    $obj->transfer_enable = $user->transfer_enable;
                    $obj->u = 0;
                    $obj->d = 0;
                    $obj->t = 0;
                    $obj->enable = 1;
                    $obj->method = $user->method;
                    $obj->protocol = $user->protocol;
                    $obj->protocol_param = $user->protocol_param;
                    $obj->obfs = $user->obfs;
                    $obj->obfs_param = $user->obfs_param;
                    $obj->speed_limit_per_con = 204800;
                    $obj->speed_limit_per_user = 204800;
                    $obj->wechat = '';
                    $obj->qq = '';
                    $obj->usage = 1;
                    $obj->pay_way = 3;
                    $obj->balance = 0;
                    $obj->enable_time = date('Y-m-d');
                    $obj->expire_time = '2099-01-01';
                    $obj->remark = '';
                    $obj->is_admin = 0;
                    $obj->reg_ip = getClientIp();
                    $obj->created_at = date('Y-m-d H:i:s');
                    $obj->updated_at = date('Y-m-d H:i:s');
                    $obj->save();
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                Session::flash('errorMsg', '出错了，可能是导入的配置中有端口已经存在了');

                return Redirect::back();
            }

            Session::flash('successMsg', '导入成功');

            return Redirect::back();
        } else {
            return Response::view('admin.import');
        }
    }

    // 导出配置信息
    public function export(Request $request)
    {
        $id = $request->get('id');

        if (empty($id)) {
            return Redirect::to('admin/userList');
        }

        $user = User::query()->where('id', $id)->first();
        if (empty($user)) {
            return Redirect::to('admin/userList');
        }

        $nodeList = SsNode::query()->where('status', 1)->orderBy('sort', 'desc')->orderBy('id', 'asc')->paginate(15)->appends($request->except('page'));
        foreach ($nodeList as &$node) {
            // 获取分组名称
            $group = SsGroup::query()->where('id', $node->group_id)->first();

            if ($node->type == 1) {
                // 生成ssr scheme
                $obfs_param = $user->obfs_param ? $user->obfs_param : $node->obfs_param;
                $protocol_param = $node->single ? $user->port . ':' . $user->passwd : $user->protocol_param;

                $ssr_str = ($node->server ? $node->server : $node->ip) . ':' . ($node->single ? $node->single_port : $user->port);
                $ssr_str .= ':' . ($node->single ? $node->single_protocol : $user->protocol) . ':' . ($node->single ? $node->single_method : $user->method);
                $ssr_str .= ':' . ($node->single ? $node->single_obfs : $user->obfs) . ':' . ($node->single ? base64url_encode($node->single_passwd) : base64url_encode($user->passwd));
                $ssr_str .= '/?obfsparam=' . base64url_encode($obfs_param);
                $ssr_str .= '&protoparam=' . ($node->single ? base64url_encode($user->port . ':' . $user->passwd) : base64url_encode($protocol_param));
                $ssr_str .= '&remarks=' . base64url_encode($node->name);
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

                // 生成配置信息
                $txt = "服务器：" . ($node->server ? $node->server : $node->ip) . "\r\n";
                if ($node->ipv6) {
                    $txt .= "IPv6：" . $node->ipv6 . "\r\n";
                }
                $txt .= "远程端口：" . ($node->single ? $node->single_port : $user->port) . "\r\n";
                $txt .= "密码：" . ($node->single ? $node->single_passwd : $user->passwd) . "\r\n";
                $txt .= "加密方法：" . ($node->single ? $node->single_method : $user->method) . "\r\n";
                $txt .= "路由：绕过局域网及中国大陆地址\r\n\r\n";
                $txt .= "协议：" . ($node->single ? $node->single_protocol : $user->protocol) . "\r\n";
                $txt .= "协议参数：" . ($node->single ? $user->port . ':' . $user->passwd : $user->protocol_param) . "\r\n";
                $txt .= "混淆方式：" . ($node->single ? $node->single_obfs : $user->obfs) . "\r\n";
                $txt .= "混淆参数：" . ($user->obfs_param ? $user->obfs_param : $node->obfs_param) . "\r\n";
                $txt .= "本地端口：1080\r\n";

                $node->txt = $txt;
                $node->ssr_scheme = $ssr_scheme;
                $node->ss_scheme = $node->compatible ? $ss_scheme : ''; // 节点兼容原版才显示
            } else {
                // 生成v2ray scheme
                $v2_json = [
                    "v"    => "2",
                    "ps"   => $node->name,
                    "add"  => $node->server ? $node->server : $node->ip,
                    "port" => $node->v2_port,
                    "id"   => $user->vmess_id,
                    "aid"  => $node->v2_alter_id,
                    "net"  => $node->v2_net,
                    "type" => $node->v2_type,
                    "host" => $node->v2_host,
                    "path" => $node->v2_path,
                    "tls"  => $node->v2_tls ? "tls" : ""
                ];
                $v2_scheme = 'vmess://' . base64url_encode(json_encode($v2_json, JSON_PRETTY_PRINT));

                // 生成文本配置信息
                $txt = "服务器：" . ($node->server ? $node->server : $node->ip) . "\r\n";
                if ($node->ipv6) {
                    $txt .= "IPv6：" . $node->ipv6 . "\r\n";
                }
                $txt .= "端口：" . $node->v2_port . "\r\n";
                $txt .= "加密方式：" . $node->v2_method . "\r\n";
                $txt .= "用户ID：" . $user->vmess_id . "\r\n";
                $txt .= "额外ID：" . $node->v2_alter_id . "\r\n";
                $txt .= "传输协议：" . $node->v2_net . "\r\n";
                $txt .= "伪装类型：" . $node->v2_type . "\r\n";
                $txt .= $node->v2_host ? "伪装域名：" . $node->v2_host . "\r\n" : "";
                $txt .= $node->v2_path ? "路径：" . $node->v2_path . "\r\n" : "";
                $txt .= $node->v2_tls == 1 ? "TLS：tls\r\n" : "";

                $node->txt = $txt;
                $node->v2_scheme = $v2_scheme;
            }
        }

        $view['nodeList'] = $nodeList;
        $view['user'] = $user;

        return Response::view('admin.export', $view);
    }

    // 导出原版SS用户配置信息
    public function exportSSJson(Request $request)
    {
        $userList = User::query()->where('port', '>', 0)->get();
        $defaultMethod = Helpers::getDefaultMethod();

        $json = '';
        if (!$userList->isEmpty()) {
            $tmp = [];
            foreach ($userList as $key => $user) {
                $tmp[] = '"' . $user->port . '":"' . $user->passwd . '"';
            }

            $userPassword = implode(",\n\t\t", $tmp);
            $json = <<<EOF
{
	"server":"0.0.0.0",
    "local_address":"127.0.0.1",
    "local_port":1080,
    "port_password":{
        {$userPassword}
    },
    "timeout":300,
    "method":"{$defaultMethod}",
    "fast_open":false
}
EOF;
        }

        // 生成JSON文件
        $fileName = makeRandStr('16') . '_shadowsocks.json';
        $filePath = public_path('downloads/' . $fileName);
        file_put_contents($filePath, $json);

        if (!file_exists($filePath)) {
            exit('文件生成失败，请检查目录权限');
        }

        return Response::download($filePath);
    }

    // 修改个人资料
    public function profile(Request $request)
    {
        if ($request->isMethod('POST')) {
            $old_password = trim($request->get('old_password'));
            $new_password = trim($request->get('new_password'));

            if (!Hash::check($old_password, Auth::user()->password)) {
                return Redirect::back()->withErrors('旧密码错误，请重新输入');
            } elseif (Hash::check($new_password, Auth::user()->password)) {
                return Redirect::back()->withErrors('新密码不可与旧密码一样，请重新输入');
            }

            $ret = User::uid()->update(['password' => Hash::make($new_password)]);
            if (!$ret) {
                return Redirect::back()->withErrors('修改失败');
            } else {
                return Redirect::back()->with('successMsg', '修改成功');
            }
        } else {
            return Response::view('admin.profile');
        }
    }

    // 用户流量监控
    public function userMonitor(Request $request)
    {
        $id = $request->get('id');

        if (empty($id)) {
            return Redirect::to('admin/userList');
        }

        $user = User::query()->where('id', $id)->first();
        if (empty($user)) {
            return Redirect::to('admin/userList');
        }

        // 30天内的流量
        $trafficDaily = [];
        $trafficHourly = [];
        $nodeList = SsNode::query()->where('status', 1)->orderBy('sort', 'desc')->get();
        foreach ($nodeList as $node) {
            $dailyData = [];
            $hourlyData = [];


            // 节点一个月内的流量
            $userTrafficDaily = UserTrafficDaily::query()->with(['info'])->where('user_id', $user->id)->where('node_id', $node->id)->where('created_at', '>=', date('Y-m', time()))->orderBy('created_at', 'asc')->pluck('total')->toArray();
            $dailyTotal = date('d', time()) - 1;//今天不算，减一
            $dailyCount = count($userTrafficDaily);
            for ($x = 0; $x < ($dailyTotal - $dailyCount); $x++) {
                $dailyData[$x] = 0;
            }
            for ($x = ($dailyTotal - $dailyCount); $x < $dailyTotal; $x++) {
                $dailyData[$x] = round($userTrafficDaily[$x - ($dailyTotal - $dailyCount)] / (1024 * 1024 * 1024), 3);
            }

            // 节点一天内的流量
            $userTrafficHourly = UserTrafficHourly::query()->with(['info'])->where('user_id', $user->id)->where('node_id', $node->id)->where('created_at', '>=', date('Y-m-d', time()))->orderBy('created_at', 'asc')->pluck('total')->toArray();
            $hourlyTotal = date('H', time());
            $hourlyCount = count($userTrafficHourly);
            for ($x = 0; $x < ($hourlyTotal - $hourlyCount); $x++) {
                $hourlyData[$x] = 0;
            }
            for ($x = ($hourlyTotal - $hourlyCount); $x < $hourlyTotal; $x++) {
                $hourlyData[$x] = round($userTrafficHourly[$x - ($hourlyTotal - $hourlyCount)] / (1024 * 1024 * 1024), 3);
            }

            $trafficDaily[$node->id] = [
                'nodeName'  => $node->name,
                'dailyData' => "'" . implode("','", $dailyData) . "'"
            ];

            $trafficHourly[$node->id] = [
                'nodeName'   => $node->name,
                'hourlyData' => "'" . implode("','", $hourlyData) . "'"
            ];
        }

        // 本月天数数据
        $monthDays = [];
        $monthHasDays = date("t");
        for ($i = 1; $i <= $monthHasDays; $i++) {
            $monthDays[] = $i;
        }

        $view['trafficDaily'] = $trafficDaily;
        $view['trafficHourly'] = $trafficHourly;
        $view['username'] = $user->username;
        $view['monthDays'] = "'" . implode("','", $monthDays) . "'";

        return Response::view('admin.userMonitor', $view);
    }

    // 生成端口
    public function makePort(Request $request)
    {
        $new_port = self::$systemConfig['is_rand_port'] ? Helpers::getRandPort() : Helpers::getOnlyPort();
        echo $new_port;
        exit;
    }

    // 加密方式、混淆、协议、等级、国家地区
    public function config(Request $request)
    {
        if ($request->isMethod('POST')) {
            $name = $request->get('name');
            $type = $request->get('type', 1); // 类型：1-加密方式（method）、2-协议（protocol）、3-混淆（obfs）
            $is_default = $request->get('is_default', 0);
            $sort = $request->get('sort', 0);

            if (empty($name)) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '配置名称不能为空']);
            }

            // 校验是否已存在
            $config = SsConfig::type($type)->where('name', $name)->first();
            if ($config) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '配置已经存在，请勿重复添加']);
            }

            $ssConfig = new SsConfig();
            $ssConfig->name = $name;
            $ssConfig->type = $type;
            $ssConfig->is_default = $is_default;
            $ssConfig->sort = $sort;
            $ssConfig->save();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            $view['method_list'] = SsConfig::type(1)->get();
            $view['protocol_list'] = SsConfig::type(2)->get();
            $view['obfs_list'] = SsConfig::type(3)->get();
            $view['level_list'] = Helpers::levelList();
            $view['country_list'] = Country::query()->get();

            return Response::view('admin.config', $view);
        }
    }

    // 删除配置
    public function delConfig(Request $request)
    {
        $id = $request->get('id');

        $ret = SsConfig::query()->where('id', $id)->delete();
        if ($ret) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
        }
    }

    // 设置默认配置
    public function setDefaultConfig(Request $request)
    {
        $id = $request->get('id');

        if (empty($id)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '非法请求']);
        }

        $config = SsConfig::query()->where('id', $id)->first();
        if (!$config) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '配置不存在']);
        }

        // 去除该配置所属类型的默认值
        SsConfig::default()->type($config->type)->update(['is_default' => 0]);

        // 将该ID对应记录值置为默认值
        SsConfig::query()->where('id', $id)->update(['is_default' => 1]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
    }

    // 设置系统扩展信息，例如客服、统计代码
    public function setExtend(Request $request)
    {
        $websiteAnalytics = $request->get('website_analytics');
        $websiteCustomerService = $request->get('website_customer_service');

        DB::beginTransaction();
        try {
            // 首页LOGO
            if ($request->hasFile('website_home_logo')) {
                $file = $request->file('website_home_logo');
                $fileType = $file->getClientOriginalExtension();

                // 验证文件合法性
                if (!in_array($fileType, ['jpg', 'png', 'jpeg', 'bmp'])) {
                    Session::flash('errorMsg', 'LOGO不合法');

                    return Redirect::back();
                }

                $logoName = date('YmdHis') . mt_rand(1000, 2000) . '.' . $fileType;
                $move = $file->move(base_path() . '/public/upload/image/', $logoName);
                $websiteHomeLogo = $move ? '/upload/image/' . $logoName : '';

                Config::query()->where('name', 'website_home_logo')->update(['value' => $websiteHomeLogo]);
            }

            // 站内LOGO
            if ($request->hasFile('website_logo')) {
                $file = $request->file('website_logo');
                $fileType = $file->getClientOriginalExtension();

                // 验证文件合法性
                if (!in_array($fileType, ['jpg', 'png', 'jpeg', 'bmp'])) {
                    Session::flash('errorMsg', 'LOGO不合法');

                    return Redirect::back();
                }

                $logoName = date('YmdHis') . mt_rand(1000, 2000) . '.' . $fileType;
                $move = $file->move(base_path() . '/public/upload/image/', $logoName);
                $websiteLogo = $move ? '/upload/image/' . $logoName : '';

                Config::query()->where('name', 'website_logo')->update(['value' => $websiteLogo]);
            }

            Config::query()->where('name', 'website_analytics')->update(['value' => $websiteAnalytics]);
            Config::query()->where('name', 'website_customer_service')->update(['value' => $websiteCustomerService]);

            Session::flash('successMsg', '更新成功');

            DB::commit();

            return Redirect::back();
        } catch (\Exception $e) {
            DB::rollBack();

            Session::flash('errorMsg', '更新失败');

            return Redirect::back();
        }
    }

    // 日志分析
    public function analysis(Request $request)
    {
        $file = storage_path('app/ssserver.log');
        if (!file_exists($file)) {
            Session::flash('analysisErrorMsg', $file . ' 不存在，请先创建文件');

            return Response::view('admin.analysis');
        }

        $logs = $this->tail($file, 10000);
        if (false === $logs) {
            $view['urlList'] = [];
        } else {
            $url = [];
            foreach ($logs as $log) {
                if (strpos($log, 'TCP connecting')) {
                    continue;
                }

                preg_match('/TCP request (\w+\.){2}\w+/', $log, $tcp_matches);
                if (!empty($tcp_matches)) {
                    $url[] = str_replace('TCP request ', '[TCP] ', $tcp_matches[0]);
                } else {
                    preg_match('/UDP data to (25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)/', $log, $udp_matches);
                    if (!empty($udp_matches)) {
                        $url[] = str_replace('UDP data to ', '[UDP] ', $udp_matches[0]);
                    }
                }
            }

            $view['urlList'] = array_unique($url);
        }

        return Response::view('admin.analysis', $view);
    }

    // 添加等级
    public function addLevel(Request $request)
    {
        $level = intval($request->get('level'));
        $level_name = trim($request->get('level_name'));

        if (empty($level)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '等级不能为空']);
        }

        if (empty($level_name)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '等级名称不能为空']);
        }

        $exists = Level::query()->where('level', $level)->first();
        if ($exists) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该等级已存在，请勿重复添加']);
        }

        $obj = new Level();
        $obj->level = $level;
        $obj->level_name = $level_name;
        $obj->save();

        if ($obj->id) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '提交成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
        }
    }

    // 编辑等级
    public function updateLevel(Request $request)
    {
        $id = $request->get('id');
        $level = $request->get('level');
        $level_name = $request->get('level_name');

        if (!$id) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => 'ID不能为空']);
        }

        if (!$level) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '等级不能为空']);
        }

        if (!$level_name) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '等级名称不能为空']);
        }

        $le = Level::query()->where('id', $id)->first();
        if (!$le) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '等级不存在']);
        }

        // 校验该等级下是否存在关联分组
        $ssGroupCount = SsGroup::query()->where('level', $le->level)->count();
        if ($ssGroupCount) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该等级下存在关联分组，请先取消关联']);
        }

        // 校验该等级下是否存在关联账号
        $userCount = User::query()->where('level', $le->level)->count();
        if ($userCount) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该等级下存在关联账号，请先取消关联']);
        }

        Level::query()->where('id', $id)->update(['level' => $level, 'level_name' => $level_name]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
    }

    // 删除等级
    public function delLevel(Request $request)
    {
        $id = $request->get('id');

        if (empty($id)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => 'ID不能为空']);
        }

        $level = Level::query()->where('id', $id)->first();
        if (empty($level)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '等级不存在']);
        }

        // 校验该等级下是否存在关联分组
        $existGroups = SsGroup::query()->where('level', $level->level)->get();
        if (!$existGroups->isEmpty()) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该等级下存在关联分组，请先取消关联']);
        }

        // 校验该等级下是否存在关联账号
        $existUsers = User::query()->where('level', $level->level)->get();
        if (!$existUsers->isEmpty()) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该等级下存在关联账号，请先取消关联']);
        }

        $ret = Level::query()->where('id', $id)->delete();
        if ($ret) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
        }
    }

    // 添加国家/地区
    public function addCountry(Request $request)
    {
        $country_name = $request->get('country_name');
        $country_code = $request->get('country_code');

        if (empty($country_name)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '国家/地区名称不能为空']);
        }

        if (empty($country_code)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '国家/地区代码不能为空']);
        }

        $exists = Country::query()->where('country_name', $country_name)->first();
        if ($exists) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该国家/地区名称已存在，请勿重复添加']);
        }

        $obj = new Country();
        $obj->country_name = $country_name;
        $obj->country_code = $country_code;
        $obj->save();

        if ($obj->id) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '提交成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
        }
    }

    // 编辑国家/地区
    public function updateCountry(Request $request)
    {
        $id = $request->get('id');
        $country_name = $request->get('country_name');
        $country_code = $request->get('country_code');

        if (empty($id)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => 'ID不能为空']);
        }

        if (empty($country_name)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '国家/地区名称不能为空']);
        }

        if (empty($country_code)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '国家/地区代码不能为空']);
        }

        $country = Country::query()->where('id', $id)->first();
        if (empty($country)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '国家/地区不存在']);
        }

        // 校验该国家/地区下是否存在关联节点
        $existNode = SsNode::query()->where('country_code', $country->country_code)->get();
        if (!$existNode->isEmpty()) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该国家/地区下存在关联节点，请先取消关联']);
        }

        $ret = Country::query()->where('id', $id)->update(['country_name' => $country_name, 'country_code' => $country_code]);
        if ($ret) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
        }
    }

    // 删除国家/地区
    public function delCountry(Request $request)
    {
        $id = $request->get('id');

        if (empty($id)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => 'ID不能为空']);
        }

        $country = Country::query()->where('id', $id)->first();
        if (empty($country)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '国家/地区不存在']);
        }

        // 校验该国家/地区下是否存在关联节点
        $existNode = SsNode::query()->where('country_code', $country->country_code)->get();
        if (!$existNode->isEmpty()) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该国家/地区下存在关联节点，请先取消关联']);
        }

        $ret = Country::query()->where('id', $id)->delete();
        if ($ret) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
        }
    }

    // 系统设置
    public function system(Request $request)
    {
        $view = Helpers::systemConfig();
        $view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

        return Response::view('admin.system', $view);
    }

    // 设置某个配置项
    public function setConfig(Request $request)
    {
        $name = trim($request->get('name'));
        $value = trim($request->get('value'));

        if (!$name) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '设置失败：请求参数异常']);
        }

        // 屏蔽异常配置
        if (!array_key_exists($name, self::$systemConfig)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '设置失败：配置不存在']);
        }

        // 如果开启用户邮件重置密码，则先设置网站名称和网址
        if (in_array($name, ['is_reset_password', 'is_active_register']) && $value == '1') {
            $config = Config::query()->where('name', 'website_name')->first();
            if ($config->value == '') {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '设置失败：启用该配置需要先设置【网站名称】']);
            }

            $config = Config::query()->where('name', 'website_url')->first();
            if ($config->value == '') {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '设置失败：启用该配置需要先设置【网站地址】']);
            }
        }

        // 演示环境禁止修改特定配置项
        if (env('APP_DEMO')) {
            $denyConfig = [
                'website_url',
                'min_rand_traffic',
                'max_rand_traffic',
                'push_bear_send_key',
                'push_bear_qrcode',
                'youzan_client_id',
                'youzan_client_secret',
                'kdt_id',
                'is_forbid_china',
                'alipay_partner',
                'alipay_key',
                'alipay_transport',
                'alipay_sign_type',
                'alipay_private_key',
                'alipay_public_key',
                'website_security_code'
            ];

            if (in_array($name, $denyConfig)) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '演示环境禁止修改该配置']);
            }
        }

        // 如果更改了有赞云任何一个配置，则删除有赞云的授权缓存，防止出现client_id错误
        if (in_array($name, ['youzan_client_id', 'youzan_client_secret', 'kdt_id'])) {
            \Cache::forget('YZY_TOKEN');
        }

        // 如果是返利比例，则需要除100
        if (in_array($name, ['referral_percent'])) {
            $value = intval($value) / 100;
        }

        // 用有赞云支付则不可用支付宝国际和支付宝当面付
        if (in_array($name, ['is_youzan'])) {
            $is_alipay = Config::query()->where('name', 'is_alipay')->first();
            if ($is_alipay->value) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '已经在使用【支付宝国际支付】']);
            }

            $is_f2fpay = Config::query()->where('name', 'is_f2fpay')->first();
            if ($is_f2fpay->value) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '已经在使用【支付宝当面付】']);
            }
        }

        // 用支付国际则不可用有赞云支付和支付宝当面付
        if (in_array($name, ['is_alipay'])) {
            $is_youzan = Config::query()->where('name', 'is_youzan')->first();
            if ($is_youzan->value) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '已经在使用【有赞云支付】']);
            }

            $is_f2fpay = Config::query()->where('name', 'is_f2fpay')->first();
            if ($is_f2fpay->value) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '已经在使用【支付宝当面付】']);
            }
        }

        // 用支付宝当面则不可用有赞云支付和支付宝国际
        if (in_array($name, ['is_f2fpay'])) {
            $is_youzan = Config::query()->where('name', 'is_youzan')->first();
            if ($is_youzan->value) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '已经在使用【有赞云支付】']);
            }

            $is_alipay = Config::query()->where('name', 'is_alipay')->first();
            if ($is_alipay->value) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '已经在使用【支付宝国际支付】']);
            }
        }

        // 更新配置
        Config::query()->where('name', $name)->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
    }

    // 邀请码列表
    public function inviteList(Request $request)
    {
        $view['inviteList'] = Invite::query()->with(['generator', 'user'])->orderBy('status', 'asc')->orderBy('id', 'desc')->paginate(15)->appends($request->except('page'));

        return Response::view('admin.inviteList', $view);
    }

    // 生成邀请码
    public function makeInvite(Request $request)
    {
        for ($i = 0; $i < 10; $i++) {
            $obj = new Invite();
            $obj->uid = 0;
            $obj->fuid = 0;
            $obj->code = strtoupper(substr(md5(microtime() . makeRandStr()), 8, 12));
            $obj->status = 0;
            $obj->dateline = date('Y-m-d H:i:s', strtotime("+" . self::$systemConfig['admin_invite_days'] . " days"));
            $obj->save();
        }

        return Response::json(['status' => 'success', 'data' => '', 'message' => '生成成功']);
    }

    // 导出邀请码
    public function exportInvite(Request $request)
    {
        $inviteList = Invite::query()->where('status', 0)->orderBy('id', 'asc')->get();

        $filename = '邀请码' . date('Ymd') . '.xlsx';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('SSRPanel')->setLastModifiedBy('SSRPanel')->setTitle('邀请码')->setSubject('邀请码')->setDescription('')->setKeywords('')->setCategory('');

        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('邀请码');
        $sheet->fromArray(['邀请码', '有效期'], null);

        foreach ($inviteList as $k => $vo) {
            $sheet->fromArray([$vo->code, $vo->dateline], null, 'A' . ($k + 2));
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // 输出07Excel文件
        //header('Content-Type:application/vnd.ms-excel'); // 输出Excel03版本文件
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    // 提现申请列表
    public function applyList(Request $request)
    {
        $username = $request->get('username');
        $status = $request->get('status');

        $query = ReferralApply::with('user');
        if ($username) {
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', 'like', '%' . $username . '%');
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $view['applyList'] = $query->orderBy('id', 'desc')->paginate(15)->appends($request->except('page'));

        return Response::view('admin.applyList', $view);
    }

    // 提现申请详情
    public function applyDetail(Request $request)
    {
        $id = $request->get('id');

        $list = new \stdClass();
        $apply = ReferralApply::query()->with(['user'])->where('id', $id)->first();
        if ($apply && $apply->link_logs) {
            $link_logs = explode(',', $apply->link_logs);
            $list = ReferralLog::query()->with(['user', 'order.goods'])->whereIn('id', $link_logs)->paginate(15)->appends($request->except('page'));
        }

        $view['info'] = $apply;
        $view['list'] = $list;

        return Response::view('admin.applyDetail', $view);
    }

    // 订单列表
    public function orderList(Request $request)
    {
        $username = trim($request->get('username'));
        $is_coupon = $request->get('is_coupon');
        $is_expire = $request->get('is_expire');
        $pay_way = $request->get('pay_way');
        $status = intval($request->get('status'));
        $range_time = $request->get('range_time');
        $sort = intval($request->get('sort')); // 0-按创建时间降序、1-按创建时间升序

        $query = Order::query()->with(['user', 'goods', 'coupon']);

        if ($username) {
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', 'like', '%' . $username . '%');
            });
        }

        if ($is_coupon != '') {
            if ($is_coupon) {
                $query->where('coupon_id', '<>', 0);
            } else {
                $query->where('coupon_id', 0);
            }
        }

        if ($is_expire != '') {
            $query->where('is_expire', $is_expire);
        }

        if ($pay_way != '') {
            $query->where('pay_way', $pay_way);
        }

        if ($status != '') {
            $query->where('status', $status);
        }

        if ($range_time) {
            $range_time = explode('至', $range_time);
            $query->where('created_at', '>=', trim($range_time['0']))->where('created_at', '<=', trim($range_time[1]));
        }

        if ($sort) {
            $query->orderBy('oid', 'asc');
        } else {
            $query->orderBy('oid', 'desc');
        }

        $view['orderList'] = $query->paginate(15)->appends($request->except('page'));

        return Response::view('admin.orderList', $view);
    }

    // 设置提现申请状态
    public function setApplyStatus(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status');

        $ret = ReferralApply::query()->where('id', $id)->update(['status' => $status]);
        if ($ret) {
            // 审核申请的时候将关联的
            $referralApply = ReferralApply::query()->where('id', $id)->first();
            $log_ids = explode(',', $referralApply->link_logs);
            if ($referralApply && $status == 1) {
                ReferralLog::query()->whereIn('id', $log_ids)->update(['status' => 1]);
            } elseif ($referralApply && $status == 2) {
                ReferralLog::query()->whereIn('id', $log_ids)->update(['status' => 2]);
            }
        }

        return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
    }

    // 重置用户流量
    public function resetUserTraffic(Request $request)
    {
        $id = $request->get('id');

        User::query()->where('id', $id)->update(['u' => 0, 'd' => 0]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
    }

    // 操作用户余额
    public function handleUserBalance(Request $request)
    {
        if ($request->isMethod('POST')) {
            $userId = $request->get('user_id');
            $amount = $request->get('amount');

            if (empty($userId) || empty($amount)) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '充值异常']);
            }

            DB::beginTransaction();
            try {
                $user = User::query()->where('id', $userId)->first();

                // 写入余额变动日志
                $this->addUserBalanceLog($userId, 0, $user->balance, $user->balance + $amount, $amount, '后台手动充值');

                // 加减余额
                if ($amount < 0) {
                    $user->decrement('balance', abs($amount) * 100);
                } else {
                    $user->increment('balance', abs($amount) * 100);
                }

                DB::commit();

                return Response::json(['status' => 'success', 'data' => '', 'message' => '充值成功']);
            } catch (\Exception $e) {
                DB::rollBack();

                return Response::json(['status' => 'fail', 'data' => '', 'message' => '充值失败：' . $e->getMessage()]);
            }
        } else {
            return Response::view('admin.handleUserBalance');
        }
    }

    // 用户余额变动记录
    public function userBalanceLogList(Request $request)
    {
        $username = trim($request->get('username'));

        $query = UserBalanceLog::query()->with(['user'])->orderBy('id', 'desc');

        if ($username) {
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', 'like', '%' . $username . '%');
            });
        }

        $view['list'] = $query->paginate(15)->appends($request->except('page'));

        return Response::view('admin.userBalanceLogList', $view);
    }

    // 用户封禁记录
    public function userBanLogList(Request $request)
    {
        $username = trim($request->get('username'));

        $query = UserBanLog::query()->with(['user'])->orderBy('id', 'desc');

        if ($username) {
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', 'like', '%' . $username . '%');
            });
        }

        $view['list'] = $query->paginate(15)->appends($request->except('page'));

        return Response::view('admin.userBanLogList', $view);
    }

    // 用户流量变动记录
    public function userTrafficLogList(Request $request)
    {
        $username = trim($request->get('username'));

        $query = UserTrafficModifyLog::query()->with(['user', 'order', 'order.goods'])->orderBy('id', 'desc');

        if ($username) {
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', 'like', '%' . $username . '%');
            });
        }

        $view['list'] = $query->paginate(15)->appends($request->except('page'));

        return Response::view('admin.userTrafficLogList', $view);
    }

    // 用户返利流水记录
    public function userRebateList(Request $request)
    {
        $username = trim($request->get('username'));
        $ref_username = trim($request->get('ref_username'));
        $status = $request->get('status');

        $query = ReferralLog::query()->with(['user', 'order'])->orderBy('status', 'asc')->orderBy('id', 'desc');

        if ($username) {
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', 'like', '%' . $username . '%');
            });
        }

        if ($ref_username) {
            $query->whereHas('ref_user', function ($q) use ($ref_username) {
                $q->where('username', 'like', '%' . $ref_username . '%');
            });
        }

        if ($status != '') {
            $query->where('status', intval($status));
        }

        $view['list'] = $query->paginate(15)->appends($request->except('page'));

        return Response::view('admin.userRebateList', $view);
    }

    // 用户在线IP记录
    public function userOnlineIPList(Request $request)
    {
        $username = trim($request->get('username'));
        $port = intval($request->get('port'));
        $wechat = trim($request->get('wechat'));
        $qq = trim($request->get('qq'));

        $query = User::query()->where('status', '>=', 0)->where('enable', 1);

        if ($username) {
            $query->where('username', 'like', '%' . $username . '%');
        }

        if (!empty($wechat)) {
            $query->where('wechat', 'like', '%' . $wechat . '%');
        }

        if (!empty($qq)) {
            $query->where('qq', 'like', '%' . $qq . '%');
        }

        if ($port) {
            $query->where('port', $port);
        }

        $userList = $query->paginate(15)->appends($request->except('page'));
        if (!$userList->isEmpty()) {
            foreach ($userList as &$user) {
                // 最近5条在线IP记录，如果后端设置为60秒上报一次，则为10分钟内的在线IP
                $user->onlineIPList = SsNodeIp::query()->with(['node'])->where('type', 'tcp')->where('port', $user->port)->where('created_at', '>=', strtotime("-10 minutes"))->orderBy('id', 'desc')->limit(5)->get();
            }
        }

        $view['userList'] = $userList;

        return Response::view('admin.userOnlineIPList', $view);
    }

    // 转换成某个用户的身份
    public function switchToUser(Request $request)
    {
        $id = $request->get('user_id');

        $user = User::query()->find($id);
        if (!$user) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => "用户不存在"]);
        }

        // 存储当前管理员ID，并将当前登录信息改成要切换的用户的身份信息
        Session::put('admin', Auth::user()->id);
        Auth::login($user);

        return Response::json(['status' => 'success', 'data' => '', 'message' => "身份切换成功"]);
    }

    // 标签列表
    public function labelList(Request $request)
    {
        $labelList = Label::query()->paginate(15)->appends($request->except('page'));
        foreach ($labelList as $label) {
            $label->userCount = UserLabel::query()->where('label_id', $label->id)->groupBy('label_id')->count();
            $label->nodeCount = SsNodeLabel::query()->where('label_id', $label->id)->groupBy('label_id')->count();
        }

        $view['labelList'] = $labelList;

        return Response::view('admin.labelList', $view);
    }

    // 添加标签
    public function addLabel(Request $request)
    {
        if ($request->isMethod('POST')) {
            $name = $request->get('name');
            $sort = $request->get('sort');

            $label = new Label();
            $label->name = $name;
            $label->sort = $sort;
            $label->save();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            return Response::view('admin.addLabel');
        }
    }

    // 编辑标签
    public function editLabel(Request $request)
    {
        if ($request->isMethod('POST')) {
            $id = $request->get('id');
            $name = $request->get('name');
            $sort = $request->get('sort');

            Label::query()->where('id', $id)->update(['name' => $name, 'sort' => $sort]);

            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            $id = $request->get('id');
            $view['label'] = Label::query()->where('id', $id)->first();

            return Response::view('admin.editLabel', $view);
        }
    }

    // 删除标签
    public function delLabel(Request $request)
    {
        $id = $request->get('id');

        DB::beginTransaction();
        try {
            Label::query()->where('id', $id)->delete();
            UserLabel::query()->where('label_id', $id)->delete(); // 删除用户关联
            SsNodeLabel::query()->where('label_id', $id)->delete(); // 删除节点关联

            DB::commit();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } catch (\Exception $e) {
            DB::rollBack();

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败：' . $e->getMessage()]);
        }
    }

    // 邮件发送日志列表
    public function emailLog(Request $request)
    {
        $view['list'] = EmailLog::query()->orderBy('id', 'desc')->paginate(15)->appends($request->except('page'));

        return Response::view('admin.emailLog', $view);
    }

    // 在线IP监控（实时）
    public function onlineIPMonitor(Request $request)
    {
        $ip = trim($request->get('ip'));
        $username = trim($request->get('username'));
        $port = intval($request->get('port'));
        $nodeId = intval($request->get('nodeId'));
        $userId = intval($request->get('id'));

        $query = SsNodeIp::query()->with(['node', 'user'])->where('type', 'tcp')->where('created_at', '>=', strtotime("-120 seconds"));

        if ($ip) {
            $query->where('ip', $ip);
        }

        if ($username) {
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', 'like', '%' . $username . '%');
            });
        }

        if ($port) {
            $query->whereHas('user', function ($q) use ($port) {
                $q->where('port', $port);
            });
        }

        if ($nodeId) {
            $query->whereHas('node', function ($q) use ($nodeId) {
                $q->where('id', $nodeId);
            });
        }

        if ($userId) {
            $query->whereHas('user', function ($q) use ($userId) {
                $q->where('id', $userId);
            });
        }

        $list = $query->groupBy('port')->orderBy('id', 'desc')->paginate(20)->appends($request->except('page'));

        foreach ($list as $vo) {
            // 跳过上报多IP的
            if (strpos($vo->ip, ',') !== false) {
                continue;
            }

            $ipInfo = QQWry::ip($vo->ip);
            if (isset($ipInfo['error'])) {
                // 用IPIP的库再试一下
                $ipip = IPIP::ip($vo->ip);
                $ipInfo = [
                    'country'  => $ipip['country_name'],
                    'province' => $ipip['region_name'],
                    'city'     => $ipip['city_name']
                ];
            }

            $vo->ipInfo = $ipInfo['country'] . ' ' . $ipInfo['province'] . ' ' . $ipInfo['city'];
        }

        $view['list'] = $list;
        $view['nodeList'] = SsNode::query()->where('status', 1)->orderBy('sort', 'desc')->orderBy('id', 'desc')->get();

        return Response::view('admin.onlineIPMonitor', $view);
    }

    // 生成用户标签
    private function makeUserLabels($userId, $labels)
    {
        // 先删除该用户所有的标签
        UserLabel::query()->where('user_id', $userId)->delete();

        if (!empty($labels) && is_array($labels)) {
            foreach ($labels as $label) {
                $userLabel = new UserLabel();
                $userLabel->user_id = $userId;
                $userLabel->label_id = $label;
                $userLabel->save();
            }
        }
    }

    // 生成节点标签
    private function makeNodeLabels($nodeId, $labels)
    {
        // 先删除所有该节点的标签
        SsNodeLabel::query()->where('node_id', $nodeId)->delete();

        if (!empty($labels) && is_array($labels)) {
            foreach ($labels as $label) {
                $ssNodeLabel = new SsNodeLabel();
                $ssNodeLabel->node_id = $nodeId;
                $ssNodeLabel->label_id = $label;
                $ssNodeLabel->save();
            }
        }
    }
}
