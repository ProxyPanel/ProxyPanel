<?php

namespace App\Http\Controllers;

use App\Http\Models\Article;
use App\Http\Models\Config;
use App\Http\Models\Country;
use App\Http\Models\Invite;
use App\Http\Models\Label;
use App\Http\Models\Level;
use App\Http\Models\Order;
use App\Http\Models\OrderGoods;
use App\Http\Models\ReferralApply;
use App\Http\Models\ReferralLog;
use App\Http\Models\SsConfig;
use App\Http\Models\SsGroup;
use App\Http\Models\SsGroupNode;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeInfo;
use App\Http\Models\SsNodeLabel;
use App\Http\Models\SsNodeOnlineLog;
use App\Http\Models\SsNodeTrafficDaily;
use App\Http\Models\SsNodeTrafficHourly;
use App\Http\Models\User;
use App\Http\Models\UserBalanceLog;
use App\Http\Models\UserBanLog;
use App\Http\Models\UserLabel;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserSubscribeLog;
use App\Http\Models\UserTrafficDaily;
use App\Http\Models\UserTrafficHourly;
use App\Http\Models\UserTrafficLog;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Redirect;
use Response;
use Log;
use DB;

class AdminController extends Controller
{
    protected static $config;

    function __construct()
    {
        self::$config = $this->systemConfig();
    }

    public function index(Request $request)
    {
        $past = strtotime(date('Y-m-d', strtotime("-" . self::$config['expire_days'] . " days")));
        $online = time() - 600;

        $view['userCount'] = User::query()->count();
        $view['activeUserCount'] = User::query()->where('t', '>=', $past)->count();
        $view['onlineUserCount'] = User::query()->where('t', '>=', $online)->count();
        $view['nodeCount'] = SsNode::query()->count();
        $flowCount = SsNodeTrafficDaily::query()->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime("-30 days")))->sum('total');
        $flowCount = flowAutoShow($flowCount);
        $view['flowCount'] = $flowCount;
        $view['totalBalance'] = User::query()->sum('balance') / 100;
        $view['totalWaitRefAmount'] = ReferralLog::query()->whereIn('status', [0, 1])->sum('ref_amount') / 100;
        $view['totalRefAmount'] = ReferralApply::query()->where('status', 2)->sum('amount') / 100;
        $view['expireWarningUserCount'] = User::query()->where('expire_time', '<=', date('Y-m-d', strtotime("+" . self::$config['expire_days'] . " days")))->where('enable', 1)->count();

        return Response::view('admin/index', $view);
    }

    // 用户列表
    public function userList(Request $request)
    {
        $username = $request->get('username');
        $wechat = $request->get('wechat');
        $qq = $request->get('qq');
        $port = $request->get('port');
        $pay_way = $request->get('pay_way');
        $status = $request->get('status');
        $enable = $request->get('enable');
        $expireWarning = $request->get('expireWarning');

        $query = User::query();
        if (!empty($username)) {
            $query->where('username', 'like', '%' . $username . '%');
        }

        if (!empty($wechat)) {
            $query->where('wechat', 'like', '%' . $wechat . '%');
        }

        if (!empty($qq)) {
            $query->where('qq', 'like', '%' . $qq . '%');
        }

        if (!empty($port)) {
            $query->where('port', intval($port));
        }

        if ($pay_way != '') {
            $query->where('pay_way', intval($pay_way));
        }

        if ($status != '') {
            $query->where('status', intval($status));
        }

        if ($enable != '') {
            $query->where('enable', intval($enable));
        }

        // 临近过期提醒
        if ($expireWarning) {
            $query->where('expire_time', '<=', date('Y-m-d', strtotime("+15 days")));
        }

        $userList = $query->orderBy('enable', 'desc')->orderBy('status', 'desc')->orderBy('id', 'desc')->paginate(10)->appends($request->except('page'));
        foreach ($userList as &$user) {
            $user->transfer_enable = flowAutoShow($user->transfer_enable);
            $user->used_flow = flowAutoShow($user->u + $user->d);
            $user->expireWarning = $user->expire_time <= date('Y-m-d', strtotime("+ 30 days")) ? 1 : 0; // 临近过期提醒

            // 流量异常警告
            $time = date('Y-m-d H:i:s', time() - 24 * 60 * 60);
            $totalTraffic = UserTrafficHourly::query()->where('user_id', $user->id)->where('node_id', 0)->where('created_at', '>=', $time)->sum('total');
            $user->trafficWarning = $totalTraffic > (self::$config['traffic_ban_value'] * 1024 * 1024 * 1024) ? 1 : 0;
        }

        $view['userList'] = $userList;

        return Response::view('admin/userList', $view);
    }

    // 添加账号
    public function addUser(Request $request)
    {
        if ($request->method() == 'POST') {
            // 校验username是否已存在
            $exists = User::query()->where('username', $request->get('username'))->first();
            if ($exists) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '用户名已存在，请重新输入']);
            }

            // 密码为空时则生成随机密码
            $password = $request->get('password');
            if (empty($password)) {
                $str = makeRandStr();
                $password = md5($str);
            } else {
                $password = md5($password);
            }

            $user = new User();
            $user->username = $request->get('username');
            $user->password = $password;
            $user->port = $request->get('port');
            $user->passwd = empty($request->get('passwd')) ? makeRandStr() : $request->get('passwd'); // SS密码为空时生成默认密码
            $user->transfer_enable = toGB($request->get('transfer_enable', 0));
            $user->enable = $request->get('enable', 0);
            $user->method = $request->get('method');
            $user->protocol = $request->get('protocol', '');
            $user->protocol_param = $request->get('protocol_param', '');
            $user->obfs = $request->get('obfs', '');
            $user->obfs_param = $request->get('obfs_param', '');
            $user->gender = $request->get('gender', 1);
            $user->wechat = $request->get('wechat', '');
            $user->qq = $request->get('qq', '');
            $user->usage = $request->get('usage', 1);
            $user->pay_way = $request->get('pay_way', 1);
            $user->balance = 0;
            $user->score = 0;
            $user->enable_time = empty($request->get('enable_time')) ? date('Y-m-d') : $request->get('enable_time');
            $user->expire_time = empty($request->get('expire_time')) ? date('Y-m-d', strtotime("+365 days")) : $request->get('expire_time');
            $user->remark = $request->get('remark', '');
            $user->level = $request->get('level', 1);
            $user->is_admin = $request->get('is_admin', 0);
            $user->reg_ip = $request->getClientIp();
            $user->save();

            if ($user->id) {
                // 生成用户标签
                $labels = $request->get('labels');
                if (!empty($labels)) {
                    foreach ($labels as $label) {
                        $userLabel = new UserLabel();
                        $userLabel->user_id = $user->id;
                        $userLabel->label_id = $label;
                        $userLabel->save();
                    }
                }

                return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
            } else {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '添加失败']);
            }
        } else {
            // 生成一个可用端口
            $last_user = User::query()->orderBy('id', 'desc')->first();
            $view['last_port'] = self::$config['is_rand_port'] ? $this->getRandPort() : $last_user->port + 1;
            $view['is_rand_port'] = self::$config['is_rand_port'];
            $view['method_list'] = $this->methodList();
            $view['protocol_list'] = $this->protocolList();
            $view['obfs_list'] = $this->obfsList();
            $view['level_list'] = $this->levelList();
            $view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

            return Response::view('admin/addUser', $view);
        }
    }

    // 批量生成账号
    public function batchAddUsers(Request $request)
    {
        DB::beginTransaction();
        try {
            for ($i = 0; $i < 5; $i++) {
                // 生成一个可用端口
                $last_user = User::query()->orderBy('id', 'desc')->first();
                $port = self::$config['is_rand_port'] ? $this->getRandPort() : $last_user->port + 1;

                $user = new User();
                $user->username = '批量生成-' . makeRandStr();
                $user->password = md5(makeRandStr());
                $user->enable = 1;
                $user->port = $port;
                $user->passwd = makeRandStr();
                $user->transfer_enable = toGB(1000);
                $user->enable_time = date('Y-m-d');
                $user->expire_time = date('Y-m-d', strtotime("+365 days"));
                $user->reg_ip = $request->getClientIp();
                $user->status = 0;
                $user->save();
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
        $id = $request->get('id');

        if ($request->method() == 'POST') {
            $username = $request->get('username');
            $password = $request->get('password');
            $port = $request->get('port');
            $passwd = $request->get('passwd');
            $transfer_enable = $request->get('transfer_enable');
            $enable = $request->get('enable');
            $method = $request->get('method');
            $protocol = $request->get('protocol');
            $protocol_param = $request->get('protocol_param', '');
            $obfs = $request->get('obfs');
            $obfs_param = $request->get('obfs_param', '');
            $speed_limit_per_con = $request->get('speed_limit_per_con');
            $speed_limit_per_user = $request->get('speed_limit_per_user');
            $gender = $request->get('gender');
            $wechat = $request->get('wechat');
            $qq = $request->get('qq');
            $usage = $request->get('usage');
            $pay_way = $request->get('pay_way');
            $status = $request->get('status');
            $labels = $request->get('labels');
            $enable_time = $request->get('enable_time');
            $expire_time = $request->get('expire_time');
            $remark = $request->get('remark');
            $level = $request->get('level');
            $is_admin = $request->get('is_admin');

            DB::beginTransaction();
            try {
                $data = [
                    'username'             => $username,
                    'port'                 => $port,
                    'passwd'               => $passwd,
                    'transfer_enable'      => toGB($transfer_enable),
                    'enable'               => $status < 0 ? 0 : $enable, // 如果禁止登陆则同时禁用SSR
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
                    'is_admin'             => $is_admin
                ];

                if (!empty($password)) {
                    $data['password'] = md5($password);
                }

                User::query()->where('id', $id)->update($data);

                // 先删除所有该用户的标签
                UserLabel::query()->where('user_id', $id)->delete();

                // 生成用户标签
                if (!empty($labels)) {
                    foreach ($labels as $label) {
                        $userLabel = new UserLabel();
                        $userLabel->user_id = $id;
                        $userLabel->label_id = $label;
                        $userLabel->save();
                    }
                }

                DB::commit();

                return Response::json(['status' => 'success', 'data' => '', 'message' => '编辑成功']);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('编辑用户信息异常：' . $e->getMessage());

                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败']);
            }
        } else {
            $user = User::query()->with(['label'])->where('id', $id)->first();
            if ($user) {
                $user->transfer_enable = flowToGB($user->transfer_enable);
                $user->balance = $user->balance / 100;

                $label = [];
                foreach ($user->label as $vo) {
                    $label[] = $vo->label_id;
                }
                $user->labels = $label;
            }

            $view['user'] = $user;
            $view['method_list'] = $this->methodList();
            $view['protocol_list'] = $this->protocolList();
            $view['obfs_list'] = $this->obfsList();
            $view['level_list'] = $this->levelList();
            $view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

            return Response::view('admin/editUser', $view);
        }
    }

    // 删除用户
    public function delUser(Request $request)
    {
        $id = $request->get('id');

        if ($id == 1) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '系统管理员不可删除']);
        }

        $user = User::query()->where('id', $id)->delete();
        if ($user) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
        }
    }

    // 节点列表
    public function nodeList(Request $request)
    {
        $nodeList = SsNode::query()->orderBy('status', 'desc')->orderBy('id', 'asc')->paginate(10)->appends($request->except('page'));
        foreach ($nodeList as &$node) {
            // 在线人数
            $last_log_time = time() - 600; // 10分钟内
            $online_log = SsNodeOnlineLog::query()->where('node_id', $node->id)->where('log_time', '>=', $last_log_time)->orderBy('id', 'desc')->first();
            $node->online_users = empty($online_log) ? 0 : $online_log->online_user;

            // 已产生流量
            $totalTraffic = SsNodeTrafficDaily::query()->where('node_id', $node->id)->sum('total');
            $node->transfer = flowAutoShow($totalTraffic);

            // 负载（10分钟以内）
            $node_info = SsNodeInfo::query()->where('node_id', $node->id)->where('log_time', '>=', strtotime("-10 minutes"))->orderBy('id', 'desc')->first();
            $node->load = empty($node_info) || empty($node_info->load) ? '宕机' : $node_info->load;
        }

        $view['nodeList'] = $nodeList;

        return Response::view('admin/nodeList', $view);
    }

    // 添加节点
    public function addNode(Request $request)
    {
        if ($request->method() == 'POST') {
            $ssNode = new SsNode();
            $ssNode->name = $request->get('name');
            $ssNode->group_id = $request->get('group_id', 0);
            $ssNode->country_code = $request->get('country_code', 'un');
            $ssNode->server = $request->get('server', '');
            $ssNode->ip = $request->get('ip');
            $ssNode->desc = $request->get('desc', '');
            $ssNode->method = $request->get('method');
            $ssNode->protocol = $request->get('protocol');
            $ssNode->protocol_param = $request->get('protocol_param');
            $ssNode->obfs = $request->get('obfs', '');
            $ssNode->obfs_param = $request->get('obfs_param', '');
            $ssNode->traffic_rate = $request->get('traffic_rate', 1);
            $ssNode->bandwidth = $request->get('bandwidth', 100);
            $ssNode->traffic = $request->get('traffic', 1000);
            $ssNode->monitor_url = $request->get('monitor_url', '');
            $ssNode->compatible = $request->get('compatible', 0);
            $ssNode->single = $request->get('single', 0);
            $ssNode->single_force = $request->get('single') ? $request->get('single_force') : 0;
            $ssNode->single_port = $request->get('single') ? $request->get('single_port') : '';
            $ssNode->single_passwd = $request->get('single') ? $request->get('single_passwd') : '';
            $ssNode->single_method = $request->get('single') ? $request->get('single_method') : '';
            $ssNode->single_protocol = $request->get('single') ? $request->get('single_protocol') : '';
            $ssNode->single_obfs = $request->get('single') ? $request->get('single_obfs') : '';
            $ssNode->sort = $request->get('sort', 0);
            $ssNode->status = $request->get('status', 1);
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

            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            $view['method_list'] = $this->methodList();
            $view['protocol_list'] = $this->protocolList();
            $view['obfs_list'] = $this->obfsList();
            $view['level_list'] = $this->levelList();
            $view['group_list'] = SsGroup::query()->get();
            $view['country_list'] = Country::query()->orderBy('country_code', 'asc')->get();
            $view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

            return Response::view('admin/addNode', $view);
        }
    }

    // 编辑节点
    public function editNode(Request $request)
    {
        $id = $request->get('id');

        if ($request->method() == 'POST') {
            $name = $request->get('name');
            $labels = $request->get('labels');
            $group_id = $request->get('group_id', 0);
            $country_code = $request->get('country_code', 'un');
            $server = $request->get('server', '');
            $ip = $request->get('ip');
            $desc = $request->get('desc', '');
            $method = $request->get('method');
            $protocol = $request->get('protocol');
            $protocol_param = $request->get('protocol_param');
            $obfs = $request->get('obfs');
            $obfs_param = $request->get('obfs_param');
            $traffic_rate = $request->get('traffic_rate');
            $bandwidth = $request->get('bandwidth');
            $traffic = $request->get('traffic');
            $monitor_url = $request->get('monitor_url');
            $compatible = $request->get('compatible');
            $single = $request->get('single');
            $single_force = $request->get('single_force');
            $single_port = $request->get('single_port');
            $single_passwd = $request->get('single_passwd');
            $single_method = $request->get('single_method');
            $single_protocol = $request->get('single_protocol');
            $single_obfs = $request->get('single_obfs');
            $sort = $request->get('sort');
            $status = $request->get('status');

            DB::beginTransaction();
            try {
                $data = [
                    'name'            => $name,
                    'group_id'        => $group_id,
                    'country_code'    => $country_code,
                    'server'          => $server,
                    'ip'              => $ip,
                    'desc'            => $desc,
                    'method'          => $method,
                    'protocol'        => $protocol,
                    'protocol_param'  => $protocol_param,
                    'obfs'            => $obfs,
                    'obfs_param'      => $obfs_param,
                    'traffic_rate'    => $traffic_rate,
                    'bandwidth'       => $bandwidth,
                    'traffic'         => $traffic,
                    'monitor_url'     => $monitor_url,
                    'compatible'      => $compatible,
                    'single'          => $single,
                    'single_force'    => $single ? $single_force : 0,
                    'single_port'     => $single ? $single_port : '',
                    'single_passwd'   => $single ? $single_passwd : '',
                    'single_method'   => $single ? $single_method : '',
                    'single_protocol' => $single ? $single_protocol : '',
                    'single_obfs'     => $single ? $single_obfs : '',
                    'sort'            => $sort,
                    'status'          => $status
                ];

                SsNode::query()->where('id', $id)->update($data);

                // 建立分组关联
                if ($group_id) {
                    // 先删除该节点所有关联
                    SsGroupNode::query()->where('node_id', $id)->delete();

                    // 建立关联
                    $ssGroupNode = new SsGroupNode();
                    $ssGroupNode->group_id = $group_id;
                    $ssGroupNode->node_id = $id;
                    $ssGroupNode->save();
                }

                // 生成节点标签
                if (!empty($labels)) {
                    // 先删除所有该用户的标签
                    SsNodeLabel::query()->where('node_id', $id)->delete();

                    foreach ($labels as $label) {
                        $ssNodeLabel = new SsNodeLabel();
                        $ssNodeLabel->node_id = $id;
                        $ssNodeLabel->label_id = $label;
                        $ssNodeLabel->save();
                    }
                }

                DB::commit();

                return Response::json(['status' => 'success', 'data' => '', 'message' => '编辑成功']);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('编辑节点信息异常：' . $e->getMessage());

                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败']);
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
            $view['method_list'] = $this->methodList();
            $view['protocol_list'] = $this->protocolList();
            $view['obfs_list'] = $this->obfsList();
            $view['level_list'] = $this->levelList();
            $view['group_list'] = SsGroup::query()->get();
            $view['country_list'] = Country::query()->orderBy('country_code', 'asc')->get();
            $view['label_list'] = Label::query()->orderBy('sort', 'desc')->orderBy('id', 'asc')->get();

            return Response::view('admin/editNode', $view);
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

        try {
            // 删除分组关联
            SsGroupNode::query()->where('node_id', $id)->delete();
            SsNode::query()->where('id', $id)->delete();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } catch (\Exception $e) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败：' . $e->getMessage()]);
        }
    }

    // 节点流量监控
    public function nodeMonitor(Request $request)
    {
        $node_id = $request->get('id');

        $node = SsNode::query()->where('id', $node_id)->orderBy('sort', 'desc')->first();
        if (!$node) {
            $request->session()->flash('errorMsg', '节点不存在，请重试');

            return Redirect::back();
        }

        // 30天内的流量
        $dailyData = [];
        $hourlyData = [];

        // 节点30日内每天的流量
        $nodeTrafficDaily = SsNodeTrafficDaily::query()->with(['info'])->where('node_id', $node->id)->orderBy('id', 'desc')->limit(30)->get();
        foreach ($nodeTrafficDaily as $daily) {
            $dailyData[] = round($daily->total / (1024 * 1024), 2);
        }

        // 节点24小时内每小时的流量
        $nodeTrafficHourly = SsNodeTrafficHourly::query()->with(['info'])->where('node_id', $node->id)->orderBy('id', 'desc')->limit(24)->get();
        foreach ($nodeTrafficHourly as $hourly) {
            $hourlyData[] = round($hourly->total / (1024 * 1024), 2);
        }

        $view['trafficDaily'] = [
            'nodeName'  => $node->name,
            'dailyData' => "'" . implode("','", $dailyData) . "'"
        ];

        $view['trafficHourly'] = [
            'nodeName'   => $node->name,
            'hourlyData' => "'" . implode("','", $hourlyData) . "'"
        ];

        $view['nodeName'] = $node->name;
        $view['nodeServer'] = $node->server;

        return Response::view('admin/nodeMonitor', $view);
    }

    // 文章列表
    public function articleList(Request $request)
    {
        $view['articleList'] = Article::query()->where('is_del', 0)->orderBy('sort', 'desc')->paginate(10)->appends($request->except('page'));

        return Response::view('admin/articleList', $view);
    }

    // 添加文章
    public function addArticle(Request $request)
    {
        if ($request->method() == 'POST') {
            $article = new Article();
            $article->title = $request->get('title');
            $article->type = $request->get('type', 1);
            $article->author = $request->get('author');
            $article->content = $request->get('content');
            $article->is_del = 0;
            $article->sort = $request->get('sort', 0);
            $article->save();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            return Response::view('admin/addArticle');
        }
    }

    // 编辑文章
    public function editArticle(Request $request)
    {
        $id = $request->get('id');

        if ($request->method() == 'POST') {
            $title = $request->get('title');
            $type = $request->get('type');
            $author = $request->get('author');
            $sort = $request->get('sort');
            $content = $request->get('content');

            $data = [
                'title'   => $title,
                'type'    => $type,
                'author'  => $author,
                'content' => $content,
                'sort'    => $sort
            ];

            $ret = Article::query()->where('id', $id)->update($data);
            if ($ret) {
                return Response::json(['status' => 'success', 'data' => '', 'message' => '编辑成功']);
            } else {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败']);
            }
        } else {
            $view['article'] = Article::query()->where('id', $id)->first();

            return Response::view('admin/editArticle', $view);
        }
    }

    // 删除文章
    public function delArticle(Request $request)
    {
        $id = $request->get('id');

        $user = Article::query()->where('id', $id)->update(['is_del' => 1]);
        if ($user) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
        }
    }

    // 节点分组列表
    public function groupList(Request $request)
    {
        $view['groupList'] = SsGroup::query()->paginate(10)->appends($request->except('page'));

        $level_list = $this->levelList();
        $level_dict = array();
        foreach ($level_list as $level) {
            $level_dict[$level['level']] = $level['level_name'];
        }
        $view['level_dict'] = $level_dict;

        return Response::view('admin/groupList', $view);
    }

    // 添加节点分组
    public function addGroup(Request $request)
    {
        if ($request->method() == 'POST') {
            $ssGroup = new SsGroup();
            $ssGroup->name = $request->get('name');
            $ssGroup->level = $request->get('level');
            $ssGroup->save();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            $view['level_list'] = $this->levelList();

            return Response::view('admin/addGroup', $view);
        }
    }

    // 编辑节点分组
    public function editGroup(Request $request)
    {
        $id = $request->get('id');

        if ($request->method() == 'POST') {
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
            $view['level_list'] = $this->levelList();

            return Response::view('admin/editGroup', $view);
        }
    }

    // 删除节点分组
    public function delGroup(Request $request)
    {
        $id = $request->get('id');

        // 检查是否该分组下是否有节点
        $group_node = SsGroupNode::query()->where('group_id', $id)->get();
        if (!$group_node->isEmpty()) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败：该分组下有节点关联，请先解除关联']);
        }

        $user = SsGroup::query()->where('id', $id)->delete();
        if ($user) {
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
        $username = $request->get('username');

        $query = UserTrafficLog::with(['User', 'SsNode']);

        if (!empty($port)) {
            $query->whereHas('user', function ($q) use ($port) {
                $q->where('port', $port);
            });
        }

        if (!empty($user_id)) {
            $query->where('user_id', $user_id);
        }

        if (!empty($username)) {
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', 'like', '%' . $username . '%');
            });
        }

        // 已使用流量
        $view['totalTraffic'] = flowAutoShow($query->sum('u') + $query->sum('d'));

        $trafficLogList = $query->orderBy('id', 'desc')->paginate(20)->appends($request->except('page'));
        foreach ($trafficLogList as &$trafficLog) {
            $trafficLog->u = flowAutoShow($trafficLog->u);
            $trafficLog->d = flowAutoShow($trafficLog->d);
            $trafficLog->log_time = date('Y-m-d H:i:s', $trafficLog->log_time);
        }

        $view['trafficLogList'] = $trafficLogList;

        return Response::view('admin/trafficLog', $view);
    }

    // 订阅请求日志
    public function subscribeLog(Request $request)
    {
        $user_id = $request->get('user_id');
        $username = $request->get('username');

        $query = UserSubscribe::with(['User']);

        if (!empty($user_id)) {
            $query->where('user_id', $user_id);
        }

        if (!empty($username)) {
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', 'like', '%' . $username . '%');
            });
        }

        $view['subscribeList'] = $query->orderBy('id', 'desc')->paginate(20)->appends($request->except('page'));

        return Response::view('admin/subscribeLog', $view);
    }

    public function setSubscribeStatus(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status', 0);

        if (empty($id)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作异常']);
        }

        if ($status) {
            UserSubscribe::query()->where('id', $id)->update(['status' => 1, 'ban_time' => 0, 'ban_desc' => '']);
        } else {
            UserSubscribe::query()->where('id', $id)->update(['status' => 0, 'ban_time' => time(), 'ban_desc' => '后台手动封禁']);
        }

        return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
    }

    // SS(R)链接反解析
    public function decompile(Request $request)
    {
        if ($request->method() == 'POST') {
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
                } else if (false !== strpos($item, 'ss://')) {
                    $str = mb_substr($item, 5);
                }

                $txt .= "\r\n" . $this->base64url_decode($str);
            }

            // 生成转换好的JSON文件
            file_put_contents(public_path('downloads/decompile.json'), $txt);

            return Response::json(['status' => 'success', 'data' => $txt, 'message' => '反解析成功']);
        } else {
            return Response::view('admin/decompile');
        }
    }

    // 格式转换(SS转SSR)
    public function convert(Request $request)
    {
        if ($request->method() == 'POST') {
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
            $view['method_list'] = $this->methodList();
            $view['protocol_list'] = $this->protocolList();
            $view['obfs_list'] = $this->obfsList();

            return Response::view('admin/convert', $view);
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
            exit('文件不存在');
        }

        return Response::download(public_path('downloads/convert.json'));
    }

    // 数据导入
    public function import(Request $request)
    {
        if ($request->method() == 'POST') {
            if (!$request->hasFile('uploadFile')) {
                $request->session()->flash('errorMsg', '请选择要上传的文件');

                return Redirect::back();
            }

            $file = $request->file('uploadFile');

            // 只能上传JSON文件
            if ($file->getClientMimeType() != 'application/json' || $file->getClientOriginalExtension() != 'json') {
                $request->session()->flash('errorMsg', '只允许上传JSON文件');

                return Redirect::back();
            }

            if (!$file->isValid()) {
                $request->session()->flash('errorMsg', '产生未知错误，请重新上传');

                return Redirect::back();
            }

            $save_path = realpath(storage_path('uploads'));
            $new_name = md5($file->getClientOriginalExtension()) . 'json';
            $file->move($save_path, $new_name);

            // 读取文件内容
            $data = file_get_contents($save_path . '/' . $new_name);
            $data = json_decode($data);
            if (!$data) {
                $request->session()->flash('errorMsg', '内容格式解析异常，请上传符合SSR配置规范的JSON文件');

                return Redirect::back();
            }

            DB::beginTransaction();
            try {
                foreach ($data as $user) {
                    $obj = new User();
                    $obj->username = $user->user;
                    $obj->password = md5('123456');
                    $obj->port = $user->port;
                    $obj->passwd = $user->passwd;
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
                    $obj->reg_ip = $request->getClientIp();
                    $obj->created_at = date('Y-m-d H:i:s');
                    $obj->updated_at = date('Y-m-d H:i:s');
                    $obj->save();
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                $request->session()->flash('errorMsg', '出错了，可能是导入的配置中有端口已经存在了');

                return Redirect::back();
            }

            $request->session()->flash('successMsg', '导入成功');

            return Redirect::back();
        } else {
            return Response::view('admin/import');
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

        $nodeList = SsNode::query()->where('status', 1)->paginate(10)->appends($request->except('page'));
        foreach ($nodeList as &$node) {
            // 生成ssr scheme
            $obfs_param = $node->single ? '' : $user->obfs_param;
            $protocol_param = $node->single ? $user->port . ':' . $user->passwd : $user->protocol_param;

            $ssr_str = '';
            $ssr_str .= ($node->server ? $node->server : $node->ip) . ':' . ($node->single ? $node->single_port : $user->port);
            $ssr_str .= ':' . ($node->single ? $node->single_protocol : $user->protocol) . ':' . ($node->single ? $node->single_method : $user->method);
            $ssr_str .= ':' . ($node->single ? $node->single_obfs : $user->obfs) . ':' . ($node->single ? base64url_encode($node->single_passwd) : base64url_encode($user->passwd));
            $ssr_str .= '/?obfsparam=' . ($node->single ? '' : base64url_encode($obfs_param));
            $ssr_str .= '&protoparam=' . ($node->single ? base64url_encode($user->port . ':' . $user->passwd) : base64url_encode($protocol_param));
            $ssr_str .= '&remarks=' . base64url_encode($node->name);
            $ssr_str .= '&group=' . base64url_encode('VPN');
            $ssr_str .= '&udpport=0';
            $ssr_str .= '&uot=0';
            $ssr_str = base64url_encode($ssr_str);
            $ssr_scheme = 'ssr://' . $ssr_str;

            // 生成ss scheme
            $ss_str = '';
            $ss_str .= $user->method . ':' . $user->passwd . '@';
            $ss_str .= $node->server . ':' . $user->port;
            $ss_str = base64url_encode($ss_str) . '#' . 'VPN';
            $ss_scheme = 'ss://' . $ss_str;

            // 生成文本配置信息
            $txt = "服务器：" . ($node->server ? $node->server : $node->ip) . "\r\n";
            $txt .= "远程端口：" . ($node->single ? $node->single_port : $user->port) . "\r\n";
            $txt .= "密码：" . ($node->single ? $node->single_passwd : $user->passwd) . "\r\n";
            $txt .= "加密方法：" . ($node->single ? $node->single_method : $user->method) . "\r\n";
            $txt .= "协议：" . ($node->single ? $node->single_protocol : $user->protocol) . "\r\n";
            $txt .= "协议参数：" . ($node->single ? $user->port . ':' . $user->passwd : $user->protocol_param) . "\r\n";
            $txt .= "混淆方式：" . ($node->single ? $node->single_obfs : $user->obfs) . "\r\n";
            $txt .= "混淆参数：" . ($node->single ? '' : $user->obfs_param) . "\r\n";
            $txt .= "本地端口：1080\r\n路由：绕过局域网及中国大陆地址";

            $node->txt = $txt;
            $node->ssr_scheme = $ssr_scheme;
            $node->ss_scheme = $node->compatible ? $ss_scheme : ''; // 节点兼容原版才显示
        }

        $view['nodeList'] = $nodeList;

        return Response::view('admin/export', $view);
    }

    // 修改个人资料
    public function profile(Request $request)
    {
        $user = $request->session()->get('user');

        if ($request->method() == 'POST') {
            $old_password = $request->get('old_password');
            $new_password = $request->get('new_password');
            $old_password = md5(trim($old_password));
            $new_password = md5(trim($new_password));

            $user = User::query()->where('id', $user['id'])->first();
            if ($user->password != $old_password) {
                $request->session()->flash('errorMsg', '旧密码错误，请重新输入');

                return Redirect::back();
            } else if ($user->password == $new_password) {
                $request->session()->flash('errorMsg', '新密码不可与旧密码一样，请重新输入');

                return Redirect::back();
            }

            $ret = User::query()->where('id', $user['id'])->update(['password' => $new_password]);
            if (!$ret) {
                $request->session()->flash('errorMsg', '修改失败');

                return Redirect::back();
            } else {
                $request->session()->flash('successMsg', '修改成功');

                return Redirect::back();
            }
        } else {
            return Response::view('admin/profile');
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

            // 每个节点30日内每天的流量
            $userTrafficDaily = UserTrafficDaily::query()->with(['node'])->where('user_id', $user->id)->where('node_id', $node->id)->orderBy('id', 'desc')->limit(30)->get();
            foreach ($userTrafficDaily as $daily) {
                $dailyData[] = round($daily->total / (1024 * 1024), 2);
            }

            // 每个节点24小时内每小时的流量
            $userTrafficHourly = UserTrafficHourly::query()->with(['node'])->where('user_id', $user->id)->where('node_id', $node->id)->orderBy('id', 'desc')->limit(24)->get();
            foreach ($userTrafficHourly as $hourly) {
                $hourlyData[] = round($hourly->total / (1024 * 1024), 2);
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

        $view['trafficDaily'] = $trafficDaily;
        $view['trafficHourly'] = $trafficHourly;
        $view['username'] = $user->username;

        return Response::view('admin/userMonitor', $view);
    }

    // 生成SS端口
    public function makePort(Request $request)
    {
        $last_user = User::query()->orderBy('id', 'desc')->first();
        $last_port = self::$config['is_rand_port'] ? $this->getRandPort() : $last_user->port + 1;
        echo $last_port;
        exit;
    }

    // 生成SS密码
    public function makePasswd(Request $request)
    {
        exit(makeRandStr());
    }

    // 加密方式、混淆、协议、等级、国家地区
    public function config(Request $request)
    {
        if ($request->method() == 'POST') {
            $name = $request->get('name');
            $type = $request->get('type', 1); // 类型：1-加密方式（method）、2-协议（protocol）、3-混淆（obfs）
            $is_default = $request->get('is_default', 0);
            $sort = $request->get('sort', 0);

            if (empty($name)) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '配置名称不能为空']);
            }

            // 校验是否已存在
            $config = SsConfig::query()->where('name', $name)->where('type', $type)->first();
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
            $view['method_list'] = SsConfig::query()->where('type', 1)->get();
            $view['protocol_list'] = SsConfig::query()->where('type', 2)->get();
            $view['obfs_list'] = SsConfig::query()->where('type', 3)->get();
            $view['level_list'] = $this->levelList();
            $view['country_list'] = Country::query()->get();

            return Response::view('admin/config', $view);
        }
    }

    // 删除配置
    public function delConfig(Request $request)
    {
        $id = $request->get('id');

        $config = SsConfig::query()->where('id', $id)->delete();
        if ($config) {
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
        if (empty($config)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '配置不存在']);
        }

        // 去除该配置所属类型的默认值
        SsConfig::query()->where('type', $config->type)->update(['is_default' => 0]);

        // 将该ID对应记录值置为默认值
        SsConfig::query()->where('id', $id)->update(['is_default' => 1]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
    }

    // 日志分析
    public function analysis(Request $request)
    {
        $file = storage_path('app/ssserver.log');
        if (!file_exists($file)) {
            $request->session()->flash('analysisErrorMsg', $file . ' 不存在，请先创建文件');

            return Response::view('admin/analysis');
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

        return Response::view('admin/analysis', $view);
    }

    // 添加等级
    public function addLevel(Request $request)
    {
        $level = $request->get('level');
        $level_name = $request->get('level_name');

        if (empty($level)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '等级不能为空']);
        }

        if (empty($level_name)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '等级名称不能为空']);
        }

        try {
            $exists = Level::query()->where('level', $level)->first();
            if ($exists) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '该等级已存在，请勿重复添加']);
            }

            $level = new Level();
            $level->level = $level;
            $level->level_name = $level_name;
            $level->save();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '提交成功']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
        }
    }

    // 编辑等级
    public function updateLevel(Request $request)
    {
        $id = $request->get('id');
        $level = $request->get('level');
        $level_name = $request->get('level_name');

        if (empty($id)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => 'ID不能为空']);
        }

        if (empty($level)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '等级不能为空']);
        }

        if (empty($level_name)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '等级名称不能为空']);
        }

        $le = Level::query()->where('id', $id)->first();
        if (empty($le)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '等级不存在']);
        }

        // 校验该等级下是否存在关联分组
        $existGroups = SsGroup::query()->where('level', $le->level)->get();
        if (!$existGroups->isEmpty()) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该等级下存在关联分组，请先取消关联']);
        }

        // 校验该等级下是否存在关联账号
        $existUsers = User::query()->where('level', $le->level)->get();
        if (!$existUsers->isEmpty()) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '该等级下存在关联账号，请先取消关联']);
        }

        try {
            Level::query()->where('id', $id)->update(['level' => $level, 'level_name' => $level_name]);

            return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
        }
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

        try {
            Level::query()->where('id', $id)->delete();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

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

        try {
            $exists = Country::query()->where('country_name', $country_name)->first();
            if ($exists) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '该国家/地区名称已存在，请勿重复添加']);
            }

            $country = new Country();
            $country->country_name = $country_name;
            $country->country_code = $country_code;
            $country->save();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '提交成功']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

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

        try {
            Country::query()->where('id', $id)->update(['country_name' => $country_name, 'country_code' => $country_code]);

            return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

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

        try {
            Country::query()->where('id', $id)->delete();

            return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return Response::json(['status' => 'fail', 'data' => '', 'message' => '操作失败']);
        }
    }

    // 系统设置
    public function system(Request $request)
    {
        $view = $this->systemConfig();

        return Response::view('admin/system', $view);
    }

    // 设置某个配置项
    public function setConfig(Request $request)
    {
        $name = trim($request->get('name'));
        $value = trim($request->get('value'));

        if ($name == '') {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '设置失败：请求参数异常']);
        }

        // 屏蔽异常配置
        if (!array_key_exists($name, self::$config)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '设置失败：配置不存在']);
        }

        // 如果开启用户邮件重置密码，则先设置网站名称和网址
        if (($name == 'is_reset_password' || $name == 'is_active_register') && $value == '1') {
            $config = Config::query()->where('name', 'website_name')->first();
            if ($config->value == '') {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '设置失败：开启重置密码需要先设置【网站名称】']);
            }

            $config = Config::query()->where('name', 'website_url')->first();
            if ($config->value == '') {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '设置失败：开启重置密码需要先设置【网站地址】']);
            }
        }

        // 更新配置
        Config::query()->where('name', $name)->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
    }

    // 设置返利比例
    public function setReferralPercent(Request $request)
    {
        $value = intval($request->get('value'));
        $value = $value / 100;

        Config::query()->where('name', 'referral_percent')->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '设置成功']);
    }

    // 设置微信、支付宝二维码
    public function setQrcode(Request $request)
    {
        // 微信二维码
        if ($request->hasFile('wechat_qrcode')) {
            $file = $request->file('wechat_qrcode');
            $type = $file->getClientOriginalExtension();
            $name = date('YmdHis') . mt_rand(1000, 2000) . '.' . $type;
            $move = $file->move(base_path() . '/public/upload/image/qrcode/', $name);
            $wechat_qrcode = $move ? '/upload/image/qrcode/' . $name : '';

            Config::query()->where('name', 'wechat_qrcode')->update(['value' => $wechat_qrcode]);
        }

        // 支付宝二维码
        if ($request->hasFile('alipay_qrcode')) {
            $file = $request->file('alipay_qrcode');
            $type = $file->getClientOriginalExtension();
            $name = date('YmdHis') . mt_rand(1000, 2000) . '.' . $type;
            $move = $file->move(base_path() . '/public/upload/image/qrcode/', $name);
            $alipay_qrcode = $move ? '/upload/image/qrcode/' . $name : '';

            Config::query()->where('name', 'alipay_qrcode')->update(['value' => $alipay_qrcode]);
        }

        return Redirect::back();
    }

    // 邀请码列表
    public function inviteList(Request $request)
    {
        $view['inviteList'] = Invite::query()->with(['generator', 'user'])->orderBy('status', 'asc')->orderBy('id', 'desc')->paginate(10)->appends($request->except('page'));

        return Response::view('admin/inviteList', $view);
    }

    // 生成邀请码
    public function makeInvite(Request $request)
    {
        $user = $request->session()->get('user');

        for ($i = 0; $i < 5; $i++) {
            $obj = new Invite();
            $obj->uid = $user['id'];
            $obj->fuid = 0;
            $obj->code = strtoupper(substr(md5(microtime() . makeRandStr()), 8, 12));
            $obj->status = 0;
            $obj->dateline = date('Y-m-d H:i:s', strtotime("+ 7days"));
            $obj->save();
        }

        return Response::json(['status' => 'success', 'data' => '', 'message' => '生成成功']);
    }

    // 导出邀请码
    public function exportInvite(Request $request)
    {
        $inviteList = Invite::query()->where('status', 0)->orderBy('id', 'asc')->get();

        $filename = '邀请码' . date('Ymd');
        Excel::create($filename, function ($excel) use ($inviteList) {
            $excel->sheet('邀请码', function ($sheet) use ($inviteList) {
                $sheet->row(1, array(
                    '邀请码', '有效期'
                ));

                if (!$inviteList->isEmpty()) {
                    foreach ($inviteList as $k => $vo) {
                        $sheet->row($k + 2, array(
                            $vo->code, $vo->dateline
                        ));
                    }
                }
            });
        })->export('xls');
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

        $list = $query->orderBy('id', 'desc')->paginate(10)->appends($request->except('page'));
        if (!empty($list)) {
            foreach ($list as $vo) {
                $vo->amount = $vo->amount / 100;
            }
        }

        $view['applyList'] = $list;

        return Response::view('admin/applyList', $view);
    }

    // 提现申请详情
    public function applyDetail(Request $request)
    {
        $id = $request->get('id');

        $list = [];
        $apply = ReferralApply::query()->where('id', $id)->with('user')->first();
        if ($apply && $apply->link_logs) {
            $apply->amount = $apply->amount / 100;
            $link_logs = explode(',', $apply->link_logs);
            $list = ReferralLog::query()->whereIn('id', $link_logs)->with('user')->paginate(10);
        }

        foreach ($list as &$vo) {
            $vo->goods = OrderGoods::query()->where('oid', $vo->order_id)->with('goods')->first();
            $vo->amount = $vo->amount / 100;
            $vo->ref_amount = $vo->ref_amount / 100;
        }

        $view['info'] = $apply;
        $view['list'] = $list;

        return Response::view('admin/applyDetail', $view);
    }

    // 订单列表
    public function orderList(Request $request)
    {
        $username = $request->get('username');
        $status = $request->get('status');

        $orderList = Order::query()->with(['user', 'goods', 'coupon'])->orderBy('oid', 'desc')->paginate(10);
        foreach ($orderList as $order) {
            $order->totalOriginalPrice = $order->totalOriginalPrice / 100;
            $order->totalPrice = $order->totalPrice / 100;
        }

        $view['orderList'] = $orderList;

        return Response::view('admin/orderList', $view);
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
            } else if ($referralApply && $status == 2) {
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
        if ($request->method() == 'POST') {
            $user_id = $request->get('user_id');
            $amount = $request->get('amount');

            if (empty($user_id) || empty($amount)) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '充值异常']);
            }

            try {
                $user = User::query()->where('id', $user_id)->first();
                $amount = $amount * 100;

                // 写入余额变动日志
                $userBalanceLog = new UserBalanceLog();
                $userBalanceLog->user_id = $user_id;
                $userBalanceLog->order_id = 0;
                $userBalanceLog->before = $user->balance;
                $userBalanceLog->after = $user->balance + $amount;
                $userBalanceLog->amount = $amount;
                $userBalanceLog->desc = '后台手动充值';
                $userBalanceLog->created_at = date('Y-m-d H:i:s');
                $userBalanceLog->save();

                // 加减余额
                if ($amount < 0) {
                    $user->decrement('balance', abs($amount));
                } else {
                    $user->increment('balance', abs($amount));
                }

                return Response::json(['status' => 'success', 'data' => '', 'message' => '充值成功']);
            } catch (\Exception $e) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '充值失败：' . $e->getMessage()]);
            }
        } else {
            return Response::view('admin/handleUserBalance');
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

        $list = $query->paginate(10);
        if (!$list->isEmpty()) {
            foreach ($list as &$vo) {
                $vo->before = $vo->before / 100;
                $vo->after = $vo->after / 100;
                $vo->amount = $vo->amount / 100;
            }
        }

        $view['list'] = $list;

        return Response::view('admin/userBalanceLogList', $view);
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

        $view['list'] = $query->paginate(10);

        return Response::view('admin/userBanLogList', $view);
    }

    // 用户消费记录
    public function userOrderList(Request $request)
    {
        $username = trim($request->get('username'));
        $is_expire = $request->get('is_expire');
        $is_coupon = $request->get('is_coupon');

        $query = Order::query()->with(['user', 'goods', 'coupon'])->orderBy('oid', 'desc');

        if ($username) {
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', 'like', '%' . $username . '%');
            });
        }

        if ($is_expire != '') {
            $query->where('is_expire', $is_expire);
        }

        if ($is_coupon != '') {
            if ($is_coupon) {
                $query->where('coupon_id', '<>', 0);
            } else {
                $query->where('coupon_id', 0);
            }
        }

        $list = $query->paginate(10);
        if (!$list->isEmpty()) {
            foreach ($list as &$vo) {
                $vo->totalOriginalPrice = $vo->totalOriginalPrice / 100;
                $vo->totalPrice = $vo->totalPrice / 100;
            }
        }

        $view['list'] = $list;

        return Response::view('admin/userOrderList', $view);
    }

    // 转换成某个用户的身份
    public function switchToUser(Request $request)
    {
        $id = $request->get('user_id');

        $user = User::query()->find($id);
        if (!$user) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => "用户不存在"]);
        }

        // 存储当前管理员身份信息，并将当前登录信息改成要切换的用户的身份信息
        $request->session()->put('admin', $request->session()->get("user"));
        $request->session()->put('user', $user->toArray());

        return Response::json(['status' => 'success', 'data' => '', 'message' => "身份切换成功"]);
    }

    // 标签列表
    public function labelList(Request $request)
    {
        $labelList = Label::query()->paginate(10);
        foreach ($labelList as $label) {
            $label->userCount = UserLabel::query()->where('label_id', $label->id)->groupBy('label_id')->count();
            $label->nodeCount = SsNodeLabel::query()->where('label_id', $label->id)->groupBy('label_id')->count();
        }

        $view['labelList'] = $labelList;

        return Response::view('admin/labelList', $view);
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
            return Response::view('admin/addLabel');
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

            return Response::view('admin/editLabel', $view);
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
}
