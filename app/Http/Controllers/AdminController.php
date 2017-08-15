<?php

namespace App\Http\Controllers;

use App\Http\Models\Config;
use App\Http\Models\SsConfig;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeInfo;
use App\Http\Models\SsNodeOnlineLog;
use App\Http\Models\User;
use App\Http\Models\UserTrafficLog;
use Illuminate\Http\Request;
use Redirect;
use Response;

class AdminController extends BaseController
{
    public function index(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

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
        $view['expireWarningUserCount'] = User::where('expire_time', '<=', date('Y-m-d', strtotime("+15 days")))->count();

        // 到期账号禁用
        User::where('enable', 1)->where('expire_time', '<=', date('Y-m-d'))->update(['enable' => 0]);

        return Response::view('admin/index', $view);
    }

    // 用户列表
    public function userList(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

        $username = $request->get('username');
        $wechat = $request->get('wechat');
        $qq = $request->get('qq');
        $port = $request->get('port');
        $pay_way = $request->get('pay_way');
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

        if ($enable != '') {
            $query->where('enable', intval($enable));
        }

        // 临近过期提醒
        if ($expireWarning) {
            $query->where('expire_time', '<=', date('Y-m-d', strtotime("+15 days")));
        }

        $userList = $query->orderBy('id', 'desc')->paginate(10);
        foreach ($userList as &$user) {
            $user->transfer_enable = $this->flowAutoShow($user->transfer_enable);
            $user->used_flow = $this->flowAutoShow($user->u + $user->d);
            $user->expireWarning = $user->expire_time <= date('Y-m-d', strtotime("+ 30 days")) ? 1 : 0;
        }

        $view['userList'] = $userList;

        return Response::view('admin/userList', $view);
    }

    // 添加账号
    public function addUser(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

        if ($request->method() == 'POST') {
            $username = $request->get('username');
            $password = $request->get('password');
            $port = $request->get('port');
            $passwd = $request->get('passwd');
            $transfer_enable = $request->get('transfer_enable');
            $enable = $request->get('enable');
            $method = $request->get('method');
            $custom_method = $request->get('custom_method');
            $protocol = $request->get('protocol');
            $protocol_param = $request->get('protocol_param');
            $obfs = $request->get('obfs');
            $obfs_param = $request->get('obfs_param');
            $wechat = $request->get('wechat');
            $qq = $request->get('qq');
            $usage = $request->get('usage');
            $pay_way = $request->get('pay_way');
            $balance = $request->get('balance');
            $enable_time = $request->get('enable_time');
            $expire_time = $request->get('expire_time');
            $remark = $request->get('remark');
            $is_admin = $request->get('is_admin');

            // 密码为空时生成默认密码
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
                'custom_method' => $custom_method,
                'protocol' => $protocol,
                'protocol_param' => $protocol_param,
                'obfs' => $obfs,
                'obfs_param' => $obfs_param,
                'wechat' => $wechat,
                'qq' => $qq,
                'usage' => $usage,
                'pay_way' => $pay_way,
                'balance' => $balance,
                'enable_time' => empty($enable_time) ? date('Y-m-d') : $enable_time,
                'expire_time' => empty($expire_time) ? date('Y-m-d', strtotime("+365 days")) : $expire_time,
                'remark' => $remark,
                'is_admin' => $is_admin,
                'reg_ip' => $request->getClientIp()
            ]);

            if ($ret) {
                return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
            } else {
                return Response::json(['status' => 'fail', 'data' => '', 'message' => '添加失败']);
            }
        } else {
            $config = $this->systemConfig();

            // 最后一个可用端口
            $last_user = User::orderBy('id', 'desc')->first();
            $view['last_port'] = $config['is_rand_port'] ? $this->getRandPort() : $last_user->port + 1;

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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

        $id = $request->get('id');
        if ($request->method() == 'POST') {
            $username = $request->get('username');
            $password = $request->get('password');
            $port = $request->get('port');
            $passwd = $request->get('passwd');
            $transfer_enable = $request->get('transfer_enable');
            $enable = $request->get('enable');
            $method = $request->get('method');
            $custom_method = $request->get('custom_method');
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
            $enable_time = $request->get('enable_time');
            $expire_time = $request->get('expire_time');
            $remark = $request->get('remark');
            $is_admin = $request->get('is_admin');

            $data = [
                'username' => $username,
                'port' => $port,
                'passwd' => $passwd,
                'transfer_enable' => $this->toGB($transfer_enable),
                'enable' => $enable,
                'method' => $method,
                'custom_method' => $custom_method,
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
                'enable_time' => empty($enable_time) ? date('Y-m-d') : $enable_time,
                'expire_time' => empty($expire_time) ? date('Y-m-d', strtotime("+365 days")) : $expire_time,
                'remark' => $remark,
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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

        $nodeList = SsNode::paginate(10);
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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

        if ($request->method() == 'POST') {
            $name = $request->get('name');
            $server = $request->get('server');
            $method = $request->get('method');
            $custom_method = $request->get('custom_method');
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

            SsNode::create([
                'name' => $name,
                'server' => $server,
                'method' => $method,
                'custom_method' => $custom_method,
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

            return Response::json(['status' => 'success', 'data' => '', 'message' => '添加成功']);
        } else {
            // 加密方式、协议、混淆
            $view['method_list'] =  $this->methodList();
            $view['protocol_list'] =  $this->protocolList();
            $view['obfs_list'] =  $this->obfsList();

            return Response::view('admin/addNode', $view);
        }
    }

    // 编辑节点
    public function editNode(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

        $id = $request->get('id');
        if ($request->method() == 'POST') {
            $name = $request->get('name');
            $server = $request->get('server');
            $method = $request->get('method');
            $custom_method = $request->get('custom_method');
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
                'server' => $server,
                'method' => $method,
                'custom_method' => $custom_method,
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

            return Response::view('admin/editNode', $view);
        }
    }

    // 删除节点
    public function delNode(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

        $id = $request->get('id');
        $user = SsNode::where('id', $id)->delete();
        if ($user) {
            return Response::json(['status' => 'success', 'data' => '', 'message' => '删除成功']);
        } else {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '删除失败']);
        }
    }

    // 流量日志
    public function trafficLog(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

        $trafficLogList = UserTrafficLog::with(['User', 'SsNode'])->orderBy('id', 'desc')->paginate(20);
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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

        if (!file_exists(public_path('downloads/convert.json'))) {
            exit('文件不存在');
        }

        return Response::download(public_path('downloads/convert.json'));
    }

    // 数据导入
    public function import(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

        $id = $request->get('id');
        if (empty($id)) {
            return Redirect::to('admin/userList');
        }

        $user = User::where('id', $id)->first();
        if (empty($user)) {
            return Redirect::to('admin/userList');
        }

        $nodeList = SsNode::paginate(10);
        foreach ($nodeList as &$node) {
            // 生成ssr scheme
            $ssr_str = '';
            $ssr_str .= $node->server . ':' . $user->port;
            $ssr_str .= ':' . $user->protocol . ':' . $user->method;
            $ssr_str .= ':' . $user->obfs . ':' . base64_encode($user->passwd);
            $ssr_str .= '/?obfsparam=' . $user->obfs_param;
            $ssr_str .= '&=protoparam' . $user->protocol_param;
            $ssr_str .= '&remarks=' . base64_encode('VPN');
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
端口：{$user->port}
密码：{$user->passwd}
加密方式：{$user->method}
协议：{$user->protocol}
协议参数：{$user->protocol_param}
混淆：{$user->obfs}
混淆参数：{$user->obfs_param}
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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

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
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        if (!$request->session()->get('user')['is_admin']) {
            return Redirect::to('login');
        }

        $view = $this->systemConfig();

        return Response::view('admin/system', $view);
    }

    // 启用、禁用随机端口
    public function enableRandPort(Request $request)
    {
        $value = intval($request->get('value'));

        Config::where('id', 1)->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
    }

    // 启用、禁用自定义端口
    public function enableUserRandPort(Request $request)
    {
        $value = intval($request->get('value'));

        Config::where('id', 2)->update(['value' => $value]);

        return Response::json(['status' => 'success', 'data' => '', 'message' => '操作成功']);
    }
}