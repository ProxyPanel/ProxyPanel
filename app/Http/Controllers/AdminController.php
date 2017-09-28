<?php

namespace App\Http\Controllers;

use App\Http\Models\Article;
use App\Http\Models\Config;
use App\Http\Models\EmailLog;
use App\Http\Models\Goods;
use App\Http\Models\Invite;
use App\Http\Models\OrderGoods;
use App\Http\Models\ReferralApply;
use App\Http\Models\ReferralLog;
use App\Http\Models\SsConfig;
use App\Http\Models\SsGroup;
use App\Http\Models\SsGroupNode;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeInfo;
use App\Http\Models\SsNodeOnlineLog;
use App\Http\Models\User;
use App\Http\Models\UserTrafficLog;
use App\Mail\userTrafficWarning;
use Illuminate\Http\Request;
use Redirect;
use Response;
use Mail;

class AdminController extends BaseController
{
    protected static $config;

    function __construct()
    {
        self::$config = $this->systemConfig();
    }

    public function index(Request $request)
    {
        $past = strtotime(date('Y-m-d', strtotime("-7 days")));
        $online = time() - 3600;

        $view['userCount'] = User::count();
        $view['activeUserCount'] = User::where('t', '>=', $past)->count();
        $view['onlineUserCount'] = User::where('t', '>=', $online)->count();
        $view['nodeCount'] = SsNode::count();
        $flowCount = UserTrafficLog::sum('u') + UserTrafficLog::sum('d');
        $flowCount = $this->flowAutoShow($flowCount);
        $view['flowCount'] = $flowCount;
        $view['totalBalance'] = User::sum('balance');
        $view['totalWaitRefAmount'] = ReferralLog::whereIn('status', [0,1])->sum('ref_amount');
        $view['totalRefAmount'] = ReferralApply::where('status', 2)->sum('amount');
        $view['expireWarningUserCount'] = User::where('expire_time', '<=', date('Y-m-d', strtotime("+15 days")))->where('enable', 1)->count();

        // 到期账号禁用 TODO：加入定时任务
        User::where('enable', 1)->where('expire_time', '<=', date('Y-m-d'))->update(['enable' => 0]);

        // 商品到期自动扣购买该商品的流量 TODO：加入定时任务
        $goodsList = Goods::where('end_time', '<', date('Y-m-d H:i:s'))->get();
        foreach ($goodsList as $goods) {
            // 所有购买过该商品的用户
            $orderGoods = OrderGoods::where('goods_id', $goods->id)->get();
            foreach ($orderGoods as $og) {
                $u = User::where('id', $og->user_id)->first();
                if (empty($u)) {
                    continue;
                }

                if ($u->transfer_enable - $goods->traffic * 1024 * 1024 < 0) {
                    User::where('id', $og->user_id)->update(['transfer_enable' => 0]);
                } else {
                    User::where('id', $og->user_id)->decrement('transfer_enable', $goods->traffic * 1024 * 1024);
                }
            }
        }

        // 用户流量警告提醒发邮件 TODO：加入定时任务
        if (self::$config['traffic_warning']) {
            $userList = User::get();
            foreach ($userList as $user) {
                if (empty($user->transfer_enable)) {
                    continue;
                }

                $usedPercent = round(($user->d + $user->u) / $user->transfer_enable, 2) * 100; // 已使用流量百分比
                if ($usedPercent >= self::$config['traffic_warning_percent']) {
                    $ret = Mail::to($user->username)->send(new userTrafficWarning(self::$config['website_name'], $usedPercent));

                    // 写入邮件发送日志
                    $emailLogObj = new EmailLog();
                    $emailLogObj->user_id = $user->id;
                    $emailLogObj->title = '用户流量警告';
                    $emailLogObj->content = '流量已使用：' . $usedPercent . '%，超过流量阈值' . self::$config['traffic_warning_percent'];
                    $emailLogObj->status = $ret ? 1 : 0;
                    $emailLogObj->created_at = date('Y-m-d H:i:s');
                    $emailLogObj->save();
                }
            }
        }

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

        if (!empty($pay_way)) {
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

        $userList = $query->orderBy('id', 'desc')->paginate(10)->appends($request->except('page'));
        foreach ($userList as &$user) {
            $user->transfer_enable = $this->flowAutoShow($user->transfer_enable);
            $user->used_flow = $this->flowAutoShow($user->u + $user->d);
            $user->expireWarning = $user->expire_time <= date('Y-m-d', strtotime("+ 30 days")) ? 1 : 0; // 临近过期提醒

            // 流量异常警告
            $time = time() - 24 * 60 * 60;
            $u = UserTrafficLog::where('user_id', $user->id)->where('log_time', '>=', $time)->where('log_time', '<=', time())->sum('u');
            $d = UserTrafficLog::where('user_id', $user->id)->where('log_time', '>=', $time)->where('log_time', '<=', time())->sum('d');

            // 超过24小时内5G流量则认为是异常使用
            $user->trafficWarning = ($u + $d) > 5368709120 ? 1 : 0;
        }

        $view['userList'] = $userList;

        return Response::view('admin/userList', $view);
    }

    // 添加账号
    public function addUser(Request $request)
    {
        if ($request->method() == 'POST') {
            $username = $request->get('username');
            $password = $request->get('password');
            $port = $request->get('port');
            $passwd = $request->get('passwd');
            $transfer_enable = $request->get('transfer_enable');
            $enable = $request->get('enable');
            $method = $request->get('method');
            //$custom_method = $request->get('custom_method');
            $protocol = $request->get('protocol');
            $protocol_param = $request->get('protocol_param');
            $obfs = $request->get('obfs');
            $obfs_param = $request->get('obfs_param');
            $wechat = $request->get('wechat');
            $qq = $request->get('qq');
            $usage = $request->get('usage');
            $pay_way = $request->get('pay_way');
            $balance = $request->get('balance');
            $score = $request->get('score');
            $enable_time = $request->get('enable_time');
            $expire_time = $request->get('expire_time');
            $remark = $request->get('remark');
            $level = $request->get('level');
            $is_admin = $request->get('is_admin');

            // 校验username是否已存在
            $exists = User::where('username', $username)->first();
            if ($exists) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '用户名已存在，请重新输入']);
            }

            // 密码为空时则生成随机密码
            if (empty($password)) {
                $str = $this->makeRandStr();
                $password = md5($str);
            } else {
                $password = md5($password);
            }

            $ret = User::create([
                'username' => $username,
                'password' => $password,
                'port' => $port,
                'passwd' => empty($passwd) ? $this->makeRandStr() : $passwd, // SS密码为空时生成默认密码
                'transfer_enable' => $this->toGB($transfer_enable),
                'enable' => $enable,
                'method' => $method,
                'custom_method' => $method,
                'protocol' => $protocol,
                'protocol_param' => $protocol_param,
                'obfs' => $obfs,
                'obfs_param' => $obfs_param,
                'wechat' => $wechat,
                'qq' => $qq,
                'usage' => $usage,
                'pay_way' => $pay_way,
                'balance' => $balance,
                'score' => $score,
                'enable_time' => empty($enable_time) ? date('Y-m-d') : $enable_time,
                'expire_time' => empty($expire_time) ? date('Y-m-d', strtotime("+365 days")) : $expire_time,
                'remark' => $remark,
                'level' => $level,
                'is_admin' => $is_admin,
                'reg_ip' => $request->getClientIp()
            ]);

            if ($ret) {
                return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
            } else {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '添加失败']);
            }
        } else {
            // 最后一个可用端口
            $last_user = User::orderBy('id', 'desc')->first();
            $view['last_port'] = self::$config['is_rand_port'] ? $this->getRandPort() : $last_user->port + 1;

            // 加密方式、协议、混淆
            $view['method_list'] =  $this->methodList();
            $view['protocol_list'] =  $this->protocolList();
            $view['obfs_list'] =  $this->obfsList();

            return Response::view('admin/addUser', $view);
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
            //$custom_method = $request->get('custom_method');
            $protocol = $request->get('protocol');
            $protocol_param = $request->get('protocol_param');
            $obfs = $request->get('obfs');
            $obfs_param = $request->get('obfs_param');
            $speed_limit_per_con = $request->get('speed_limit_per_con');
            $speed_limit_per_user = $request->get('speed_limit_per_user');
            $wechat = $request->get('wechat');
            $qq = $request->get('qq');
            $usage = $request->get('usage');
            $pay_way = $request->get('pay_way');
            $balance = $request->get('balance');
            $score = $request->get('score');
            $status = $request->get('status');
            $enable_time = $request->get('enable_time');
            $expire_time = $request->get('expire_time');
            $remark = $request->get('remark');
            $level = $request->get('level');
            $is_admin = $request->get('is_admin');

            $data = [
                'username' => $username,
                'port' => $port,
                'passwd' => $passwd,
                'transfer_enable' => $this->toGB($transfer_enable),
                'enable' => $enable,
                'method' => $method,
                'custom_method' => $method,
                'protocol' => $protocol,
                'protocol_param' => $protocol_param,
                'obfs' => $obfs,
                'obfs_param' => $obfs_param,
                'speed_limit_per_con' => $speed_limit_per_con,
                'speed_limit_per_user' => $speed_limit_per_user,
                'wechat' => $wechat,
                'qq' => $qq,
                'usage' => $usage,
                'pay_way' => $pay_way,
                'balance' => $balance,
                'score' => $score,
                'status' => $status,
                'enable_time' => empty($enable_time) ? date('Y-m-d') : $enable_time,
                'expire_time' => empty($expire_time) ? date('Y-m-d', strtotime("+365 days")) : $expire_time,
                'remark' => $remark,
                'level' => $level,
                'is_admin' => $is_admin
            ];

            if (!empty($password)) {
                $data['password'] = md5($password);
            }

            $ret = User::where('id', $id)->update($data);
            if ($ret) {
                return Response::json(['status' => 'success', 'data' => '', 'message' => '编辑成功']);
            } else {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败']);
            }
        } else {
            $user = User::where('id', $id)->first();
            if (!empty($user)) {
                $user->transfer_enable = $this->flowToGB($user->transfer_enable);
            }

            $view['user'] = $user;

            // 加密方式、协议、混淆
            $view['method_list'] =  $this->methodList();
            $view['protocol_list'] =  $this->protocolList();
            $view['obfs_list'] =  $this->obfsList();

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

        $user = User::where('id', $id)->delete();
        if ($user) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
        }
    }

    // 节点列表
    public function nodeList(Request $request)
    {
        $nodeList = SsNode::paginate(10)->appends($request->except('page'));
        foreach ($nodeList as &$node) {
            // 在线人数
            $online_log = SsNodeOnlineLog::where('node_id', $node->id)->orderBy('id', 'desc')->first();
            $node->online_users = empty($online_log) ? 0 : $online_log->online_user;

            // 已产生流量
            $u = UserTrafficLog::where('node_id', $node->id)->sum('u');
            $d = UserTrafficLog::where('node_id', $node->id)->sum('d');
            $node->transfer = $this->flowAutoShow($u + $d);

            // 负载
            $node_info = SsNodeInfo::where('node_id', $node->id)->orderBy('id', 'desc')->first();
            $node->load = empty($node_info->load) ? 0 : $node_info->load;
        }

        $view['nodeList'] = $nodeList;

        return Response::view('admin/nodeList', $view);
    }

    // 添加节点
    public function addNode(Request $request)
    {
        if ($request->method() == 'POST') {
            $name = $request->get('name');
            $group_id = $request->get('group_id');
            $server = $request->get('server');
            $method = $request->get('method');
            //$custom_method = $request->get('custom_method');
            $protocol = $request->get('protocol');
            $protocol_param = $request->get('protocol_param');
            $obfs = $request->get('obfs');
            $obfs_param = $request->get('obfs_param');
            $traffic_rate = $request->get('traffic_rate');
            $bandwidth = $request->get('bandwidth');
            $traffic = $request->get('traffic');
            $monitor_url = $request->get('monitor_url');
            $compatible = $request->get('compatible');
            $sort = $request->get('sort');
            $status = $request->get('status');

            $node = SsNode::create([
                'name' => $name,
                'group_id' => $group_id,
                'server' => $server,
                'method' => $method,
                'custom_method' => $method,
                'protocol' => $protocol,
                'protocol_param' => $protocol_param,
                'obfs' => $obfs,
                'obfs_param' => $obfs_param,
                'traffic_rate' => $traffic_rate,
                'bandwidth' => $bandwidth,
                'traffic' => $traffic,
                'monitor_url' => $monitor_url,
                'compatible' => $compatible,
                'sort' => $sort,
                'status' => $status,
            ]);

            // 建立分组关联
            if ($group_id) {
                SsGroupNode::create([
                    'group_id' => $group_id,
                    'node_id' => $node->id
                ]);
            }

            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            // 加密方式、协议、混淆
            $view['method_list'] =  $this->methodList();
            $view['protocol_list'] =  $this->protocolList();
            $view['obfs_list'] =  $this->obfsList();
            $view['group_list'] =  SsGroup::get();

            return Response::view('admin/addNode', $view);
        }
    }

    // 编辑节点
    public function editNode(Request $request)
    {
        $id = $request->get('id');
        if ($request->method() == 'POST') {
            $name = $request->get('name');
            $group_id = $request->get('group_id');
            $server = $request->get('server');
            $method = $request->get('method');
            //$custom_method = $request->get('custom_method');
            $protocol = $request->get('protocol');
            $protocol_param = $request->get('protocol_param');
            $obfs = $request->get('obfs');
            $obfs_param = $request->get('obfs_param');
            $traffic_rate = $request->get('traffic_rate');
            $bandwidth = $request->get('bandwidth');
            $traffic = $request->get('traffic');
            $monitor_url = $request->get('monitor_url');
            $compatible = $request->get('compatible');
            $sort = $request->get('sort');
            $status = $request->get('status');

            $data = [
                'name' => $name,
                'group_id' => $group_id,
                'server' => $server,
                'method' => $method,
                'custom_method' => $method,
                'protocol' => $protocol,
                'protocol_param' => $protocol_param,
                'obfs' => $obfs,
                'obfs_param' => $obfs_param,
                'traffic_rate' => $traffic_rate,
                'bandwidth' => $bandwidth,
                'traffic' => $traffic,
                'monitor_url' => $monitor_url,
                'compatible' => $compatible,
                'sort' => $sort,
                'status' => $status
            ];

            $ret = SsNode::where('id', $id)->update($data);
            if ($ret) {
                // 建立分组关联
                if ($group_id) {
                    // 先删除该节点所有关联
                    SsGroupNode::where('node_id', $id)->delete();

                    SsGroupNode::create([
                        'group_id' => $group_id,
                        'node_id' => $id
                    ]);
                }

                return Response::json(['status' => 'success', 'data' => '', 'message' => '编辑成功']);
            } else {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败']);
            }
        } else {
            $view['node'] = SsNode::where('id', $id)->first();

            // 加密方式、协议、混淆
            $view['method_list'] =  $this->methodList();
            $view['protocol_list'] =  $this->protocolList();
            $view['obfs_list'] =  $this->obfsList();
            $view['group_list'] =  SsGroup::get();

            return Response::view('admin/editNode', $view);
        }
    }

    // 删除节点
    public function delNode(Request $request)
    {
        $id = $request->get('id');
        $user = SsNode::where('id', $id)->delete();
        if ($user) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
        }
    }

    // 文章列表
    public function articleList(Request $request)
    {
        $view['articleList'] = Article::where('is_del', 0)->orderBy('sort', 'desc')->paginate(10)->appends($request->except('page'));

        return Response::view('admin/articleList', $view);
    }

    // 添加文章
    public function addArticle(Request $request)
    {
        if ($request->method() == 'POST') {
            $title = $request->get('title');
            $content = $request->get('content');
            $sort = $request->get('sort');

            Article::create([
                'title' => $title,
                'content' => $content,
                'is_del' => 0,
                'sort' => $sort
            ]);

            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            return Response::view('admin/addArticle');
        }
    }

    // 编辑节点
    public function editArticle(Request $request)
    {
        $id = $request->get('id');
        if ($request->method() == 'POST') {
            $title = $request->get('title');
            $sort = $request->get('sort');
            $content = $request->get('content');

            $data = [
                'title' => $title,
                'content' => $content,
                'sort' => $sort
            ];

            $ret = Article::where('id', $id)->update($data);
            if ($ret) {
                return Response::json(['status' => 'success', 'data' => '', 'message' => '编辑成功']);
            } else {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败']);
            }
        } else {
            $view['article'] = Article::where('id', $id)->first();

            return Response::view('admin/editArticle', $view);
        }
    }

    // 删除文章
    public function delArticle(Request $request)
    {
        $id = $request->get('id');
        $user = Article::where('id', $id)->update(['is_del' => 1]);
        if ($user) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
        }
    }

    // 节点分组列表
    public function groupList(Request $request)
    {
        $view['groupList'] = SsGroup::paginate(10)->appends($request->except('page'));

        return Response::view('admin/groupList', $view);
    }

    // 添加节点分组
    public function addGroup(Request $request)
    {
        if ($request->method() == 'POST') {
            $name = $request->get('name');
            $level = $request->get('level');

            SsGroup::create([
                'name' => $name,
                'level' => $level
            ]);

            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            return Response::view('admin/addGroup');
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
                'name' => $name,
                'level' => $level
            ];

            $ret = SsGroup::where('id', $id)->update($data);
            if ($ret) {
                return Response::json(['status' => 'success', 'data' => '', 'message' => '编辑成功']);
            } else {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '编辑失败']);
            }
        } else {
            $view['group'] = SsGroup::where('id', $id)->first();

            return Response::view('admin/editGroup', $view);
        }
    }

    // 删除节点分组
    public function delGroup(Request $request)
    {
        $id = $request->get('id');

        // 检查是否该分组下是否有节点
        $group_node = SsGroupNode::where('group_id', $id)->get();
        if (!$group_node->isEmpty()) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败：该分组下有节点关联，请先解除关联']);
        }

        $user = SsGroup::where('id', $id)->delete();
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
            $query->whereHas('user', function($q) use($port) {
                $q->where('port', $port);
            });
        }

        if (!empty($user_id)) {
            $query->where('user_id', $user_id);
        }

        if (!empty($username)) {
            $query->whereHas('user', function($q) use($username) {
                $q->where('username', 'like', '%' . $username . '%');
            });
        }

        // 已使用流量
        $view['totalTraffic'] = $this->flowAutoShow($query->sum('u') + $query->sum('d'));

        $trafficLogList = $query->orderBy('id', 'desc')->paginate(20)->appends($request->except('page'));
        foreach ($trafficLogList as &$trafficLog) {
            $trafficLog->u = $this->flowAutoShow($trafficLog->u);
            $trafficLog->d = $this->flowAutoShow($trafficLog->d);
            $trafficLog->log_time = date('Y-m-d H:i:s', $trafficLog->log_time);
        }

        $view['trafficLogList'] = $trafficLogList;

        return Response::view('admin/trafficLog', $view);
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
                    'd' => 0,
                    'enable' => 1,
                    'method' => $method,
                    'obfs' => $obfs,
                    'obfs_param' => empty($obfs_param) ? "" : $obfs_param,
                    'passwd' => $passwd,
                    'port' => $port,
                    'protocol' => $protocol,
                    'protocol_param' => empty($protocol_param) ? "" : $protocol_param,
                    'transfer_enable' => $this->toGB($transfer_enable),
                    'u' => 0,
                    'user' => date('Ymd') . '_IMPORT_' . $port,
                ];
            }

            $json = json_encode($data);

            // 生成转换好的JSON文件
            file_put_contents(public_path('downloads/convert.json'), $json);

            return Response::json(['status' => 'success', 'data' => $json, 'message' => '转换成功']);
        } else {
            // 加密方式、协议、混淆
            $view['method_list'] =  $this->methodList();
            $view['protocol_list'] =  $this->protocolList();
            $view['obfs_list'] =  $this->obfsList();

            return Response::view('admin/convert', $view);
        }
    }

    // 下载转换好的JSON文件
    public function download(Request $request)
    {
        if (!file_exists(public_path('downloads/convert.json'))) {
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
            $new_name = md5($file->getClientOriginalExtension()).'json';
            $file->move($save_path, $new_name);

            // 读取文件内容
            $data = file_get_contents($save_path.'/'.$new_name);
            $data = json_decode($data);
            if (!$data) {
                $request->session()->flash('errorMsg', '内容格式解析异常，请上传符合SSR配置规范的JSON文件');
                return Redirect::back();
            }

            \DB::beginTransaction();
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
                    $obj->custom_method = $user->method;
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

                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();

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

        $user = User::where('id', $id)->first();
        if (empty($user)) {
            return Redirect::to('admin/userList');
        }

        $nodeList = SsNode::paginate(10)->appends($request->except('page'));
        foreach ($nodeList as &$node) {
            // 生成ssr scheme
            $ssr_str = '';
            $ssr_str .= $node->server . ':' . $user->port;
            $ssr_str .= ':' . $user->protocol . ':' . $user->method;
            $ssr_str .= ':' . $user->obfs . ':' . base64_encode($user->passwd);
            $ssr_str .= '/?obfsparam=' . $user->obfs_param;
            $ssr_str .= '&=protoparam' . $user->protocol_param;
            $ssr_str .= '&remarks=' . base64_encode($node->name);
            $ssr_str = $this->base64url_encode($ssr_str);
            $ssr_scheme = 'ssr://' . $ssr_str;

            // 生成ss scheme
            $ss_str = '';
            $ss_str .= $user->method . ':' . $user->passwd . '@';
            $ss_str .= $node->server . ':' . $user->port;
            $ss_str = $this->base64url_encode($ss_str) . '#' . 'VPN';
            $ss_scheme = 'ss://' . $ss_str;

            // 生成json配置信息
            $config = <<<CONFIG
{
    "remarks" : "{$node->name}",
    "server" : "{$node->server}",
    "server_port" : {$user->port},
    "server_udp_port" : 0,
    "password" : "{$user->passwd}",
    "method" : "{$user->method}",
    "protocol" : "{$user->protocol}",
    "protocolparam" : "{$user->protocol_param}",
    "obfs" : "{$user->obfs}",
    "obfsparam" : "{$user->obfs_param}",
    "remarks_base64" : "",
    "group" : "VPN",
    "enable" : true,
    "udp_over_tcp" : false
}
CONFIG;

            // 生成文本配置信息
            $txt = <<<TXT
服务器：{$node->server}
远程端口：{$user->port}
本地端口：1080
密码：{$user->passwd}
加密方法：{$user->method}
协议：{$user->protocol}
协议参数：{$user->protocol_param}
混淆方式：{$user->obfs}
混淆参数：{$user->obfs_param}
路由：绕过局域网及中国大陆地址
TXT;

            $node->txt = $txt;
            $node->json = $config;
            $node->ssr_scheme = $ssr_scheme;
            $node->ss_scheme = $ss_scheme;
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

            $user = User::where('id', $user['id'])->first();
            if ($user->password != $old_password) {
                $request->session()->flash('errorMsg', '旧密码错误，请重新输入');
                return Redirect::back();
            } else if ($user->password == $new_password) {
                $request->session()->flash('errorMsg', '新密码不可与旧密码一样，请重新输入');
                return Redirect::back();
            }

            $ret = User::where('id', $user['id'])->update(['password' => $new_password]);
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

    // 流量监控
    public function monitor(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Redirect::to('admin/userList');
        }

        $user = User::where('id', $id)->first();
        if (empty($user)) {
            return Redirect::to('admin/userList');
        }

        // 30天内的流量
        $traffic = [];
        $node_list = SsNode::get();
        foreach ($node_list as $node) {
            $trafficList = \DB::select("SELECT date(from_unixtime(log_time)) AS dd, SUM(u) AS u, SUM(d) AS d FROM `user_traffic_log` WHERE `user_id` = {$id} AND `node_id` = {$node->id} GROUP BY `dd`");
            foreach ($trafficList as $key => &$val) {
                $val->total = ($val->u + $val->d) / (1024 * 1024); // 以M为单位
            }

            $traffic[$node->id] = $trafficList;
        }

        $view['traffic'] = $traffic;
        $view['nodeList'] = $node_list;

        return Response::view('admin/monitor', $view);
    }

    // 生成SS密码
    public function makePasswd(Request $request)
    {
        exit($this->makeRandStr());
    }

    // 加密方式、混淆、协议列表
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
            $config = SsConfig::where('name', $name)->where('type', $type)->first();
            if ($config) {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '配置已经存在，请勿重复添加']);
            }

            SsConfig::create([
                'name' => $name,
                'type' => $type,
                'is_default' => $is_default,
                'sort' => $sort
            ]);

            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            $view['method_list'] = SsConfig::where('type', 1)->get();
            $view['protocol_list'] = SsConfig::where('type', 2)->get();
            $view['obfs_list'] = SsConfig::where('type', 3)->get();

            return Response::view('admin/config', $view);
        }
    }

    // 删除配置
    public function delConfig(Request $request)
    {
        $id = $request->get('id');
        $config = SsConfig::where('id', $id)->delete();
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

        $config = SsConfig::where('id', $id)->first();
        if (empty($config)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '配置不存在']);
        }

        // 去除该配置所属类型的默认值
        SsConfig::where('type', $config->type)->update(['is_default' => 0]);

        // 将该ID对应记录值置为默认值
        SsConfig::where('id', $id)->update(['is_default' => 1]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
    }

    // 日志分析
    public function analysis(Request $request)
    {
        $file = storage_path('app/public/ssserver.log');
        if (!file_exists($file)) {
            $request->session()->flash('analysisErrorMsg', $file . ' 不存在，请先创建文件');

            return Response::view('admin/analysis');
        }

        $logs = $this->tail($file, 10000);
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

        return Response::view('admin/analysis', $view);
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

        if ($name == '' || $value == '') {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '设置失败：请求参数异常']);
        }

        // 屏蔽异常配置
        if (!array_key_exists($name, self::$config)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '设置失败：配置不存在']);
        }

        // 如果开启用户邮件重置密码，则先设置网站名称和网址
        if (($name == 'is_reset_password' || $name == 'is_active_register') && $value == '1') {
            $config = Config::where('name', 'website_name')->first();
            if ($config->value == '') {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '设置失败：开启重置密码需要先设置【网站名称】']);
            }

            $config = Config::where('name', 'website_url')->first();
            if ($config->value == '') {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '设置失败：开启重置密码需要先设置【网站地址】']);
            }
        }

        $ret = Config::where('name', $name)->update(['value' => $value]);
        if (!$ret) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '设置失败']);
        }

        return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
    }

    // 设置注册时默认可用流量
    public function setDefaultTraffic(Request $request)
    {
        $value = intval($request->get('value'));

        Config::where('name', 'default_traffic')->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '设置成功']);
    }

    // 设置可生成邀请码数
    public function setInviteNum(Request $request)
    {
        $value = intval($request->get('value'));

        Config::where('name', 'invite_num')->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '设置成功']);
    }

    // 设置网站名称
    public function setWebsiteName(Request $request)
    {
        $value = trim($request->get('value'));

        Config::where('name', 'website_name')->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '设置成功']);
    }

    // 设置网站地址
    public function setWebsiteUrl(Request $request)
    {
        $value = trim($request->get('value'));

        Config::where('name', 'website_url')->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '设置成功']);
    }

    // 设置重置密码次数
    public function setResetPasswordTimes(Request $request)
    {
        $value = intval($request->get('value'));

        Config::where('name', 'reset_password_times')->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '设置成功']);
    }

    // 设置激活账号次数
    public function setActiveTimes(Request $request)
    {
        $value = intval($request->get('value'));

        Config::where('name', 'active_times')->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '设置成功']);
    }

    // 设置激活账号次数
    public function setTrafficWarningPercent(Request $request)
    {
        $value = intval($request->get('value'));

        Config::where('name', 'traffic_warning_percent')->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '设置成功']);
    }

    // 设置激活账号次数
    public function setAddScoreRange(Request $request)
    {
        $value = intval($request->get('value'));

        Config::where('name', 'login_add_score_range')->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '设置成功']);
    }

    // 设置注册送流量值
    public function setReferralTraffic(Request $request)
    {
        $value = intval($request->get('value'));

        Config::where('name', 'referral_traffic')->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '设置成功']);
    }

    // 设置返利比例
    public function setReferralPercent(Request $request)
    {
        $value = intval($request->get('value'));
        $value = $value / 100;

        Config::where('name', 'referral_percent')->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '设置成功']);
    }

    // 设置返利满多少可提现
    public function setReferralMoney(Request $request)
    {
        $value = intval($request->get('value'));

        Config::where('name', 'referral_money')->update(['value' => $value]);

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

            Config::where('name', 'wechat_qrcode')->update(['value' => $wechat_qrcode]);
        }

        // 支付宝二维码
        if ($request->hasFile('alipay_qrcode')) {
            $file = $request->file('alipay_qrcode');
            $type = $file->getClientOriginalExtension();
            $name = date('YmdHis') . mt_rand(1000, 2000) . '.' . $type;
            $move = $file->move(base_path() . '/public/upload/image/qrcode/', $name);
            $alipay_qrcode = $move ? '/upload/image/qrcode/' . $name : '';

            Config::where('name', 'alipay_qrcode')->update(['value' => $alipay_qrcode]);
        }

        return Redirect::back();
    }

    // 邀请码列表
    public function inviteList(Request $request)
    {
        $view['inviteList'] = Invite::with(['generator', 'user'])->paginate(10)->appends($request->except('page'));

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
            $obj->code = strtoupper(substr(md5(microtime() . $this->makeRandStr(6)), 8, 16));
            $obj->status = 0;
            $obj->dateline = date('Y-m-d H:i:s', strtotime("+ 7days"));
            $obj->save();
        }

        return Response::json(['status' => 'success', 'data' => '', 'message' => '生成成功']);
    }

    // 提现申请列表
    public function applyList(Request $request)
    {
        $username = $request->get('username');
        $status = $request->get('status');

        $query = ReferralApply::with('user');
        if ($username) {
            $query->whereHas('user', function($q) use($username) {
                $q->where('username', 'like', '%' . $username . '%');
            });
        }
        if ($status) {
            $query->where('status', $status);
        }

        $list = $query->paginate(10)->appends($request->except('page'));
        $view['applyList'] = $list;

        return Response::view('admin/applyList', $view);
    }

    // 提现申请详情
    public function applyDetail(Request $request)
    {
        $id = $request->get('id');


        $list = [];
        $apply = ReferralApply::where('id', $id)->with('user')->first();
        if (!empty($apply) && !empty($apply->link_logs)) {
            $link_logs = explode(',', $apply->link_logs);
            $list = ReferralLog::whereIn('id', $link_logs)->with('user')->paginate(10);
        }

        $view['info'] = $apply;
        $view['list'] = $list;

        return Response::view('admin/applyDetail', $view);
    }

    // 设置提现申请状态
    public function setApplyStatus(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status');

        $ret = ReferralApply::where('id', $id)->update(['status' => $status]);
        if ($ret) {
            // 审核申请的时候将关联的
            $referralApply = ReferralApply::where('id', $id)->first();
            $log_ids = explode(',', $referralApply->link_logs);
            if ($referralApply && $status == 1) {
                ReferralLog::whereIn('id', $log_ids)->update(['status' => 1]);
            } else if ($referralApply && $status == 2) {
                ReferralLog::whereIn('id', $log_ids)->update(['status' => 2]);
            }
        }

        return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
    }
}