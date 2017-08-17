<?php

namespace App\Http\Controllers;

use App\Http\Models\Article;
use App\Http\Models\Config;
use App\Http\Models\Invite;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeInfo;
use App\Http\Models\SsNodeOnlineLog;
use App\Http\Models\User;
use App\Http\Models\UserTrafficLog;
use Illuminate\Http\Request;
use Redirect;
use Response;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        $view['articleList'] = Article::orderBy('sort', 'desc')->orderBy('id', 'desc')->limit(5)->get();
        $view['info'] = $request->session()->get('user');

        return Response::view('user/index', $view);
    }

    // 公告详情
    public function article(Request $request)
    {
        $id = $request->get('id');

        $view['info'] = Article::where('id', $id)->first();

        return Response::view('user/article', $view);
    }

    // 修改个人资料
    public function profile(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        $user = $request->session()->get('user');

        if ($request->method() == 'POST') {
            $old_password = $request->get('old_password');
            $new_password = $request->get('new_password');
            $port = trim($request->get('port'));
            $passwd = trim($request->get('passwd'));
            $method = $request->get('method');
            $protocol = $request->get('protocol');
            $obfs = $request->get('obfs');

            // 修改密码
            if (!empty($old_password) && !empty($new_password)) {
                $old_password = md5(trim($old_password));
                $new_password = md5(trim($new_password));

                $user = User::where('id', $user['id'])->first();
                if ($user->password != $old_password) {
                    $request->session()->flash('errorMsg', '旧密码错误，请重新输入');

                    return Redirect::to('user/profile#tab_1');
                } else if ($user->password == $new_password) {
                    $request->session()->flash('errorMsg', '新密码不可与旧密码一样，请重新输入');

                    return Redirect::to('user/profile#tab_1');
                }

                $ret = User::where('id', $user['id'])->update(['password' => $new_password]);
                if (!$ret) {
                    $request->session()->flash('errorMsg', '修改失败');

                    return Redirect::to('user/profile#tab_1');
                } else {
                    $request->session()->flash('successMsg', '修改成功');

                    return Redirect::to('user/profile#tab_1');
                }
            }

            // 修改SS信息
            if (empty($port)) {
                $request->session()->flash('errorMsg', '端口不能为空');

                return Redirect::to('user/profile#tab_2');
            }

            if (empty($passwd)) {
                $request->session()->flash('errorMsg', '密码不能为空');

                return Redirect::to('user/profile#tab_2');
            }

            $data = [
                //'port' => $port,
                'passwd'   => $passwd,
                'method'   => $method,
                'protocol' => $protocol,
                'obfs'     => $obfs
            ];

            $ret = User::where('id', $user['id'])->update($data);
            if (!$ret) {
                $request->session()->flash('errorMsg', '修改失败');

                return Redirect::to('user/profile#tab_2');
            } else {
                // 更新session
                $user = User::where('id', $user['id'])->first()->toArray();
                $request->session()->remove('user');
                $request->session()->put('user', $user);

                $request->session()->flash('successMsg', '修改成功');

                return Redirect::to('user/profile#tab_2');
            }
        } else {
            // 加密方式、协议、混淆
            $view['method_list'] = $this->methodList();
            $view['protocol_list'] = $this->protocolList();
            $view['obfs_list'] = $this->obfsList();
            $view['info'] = User::where('id', $user['id'])->first();

            return Response::view('user/profile', $view);
        }
    }

    // 节点列表
    public function nodeList(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        $user = $request->session()->get('user');

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

            // 生成ssr scheme
            $ssr_str = '';
            $ssr_str .= $node->server . ':' . $user['port'];
            $ssr_str .= ':' . $user['protocol'] . ':' . $user['method'];
            $ssr_str .= ':' . $user['obfs'] . ':' . base64_encode($user['passwd']);
            $ssr_str .= '/?obfsparam=' . $user['obfs_param'];
            $ssr_str .= '&=protoparam' . $user['protocol_param'];
            $ssr_str .= '&remarks=' . base64_encode('VPN');
            $ssr_str = $this->base64url_encode($ssr_str);
            $ssr_scheme = 'ssr://' . $ssr_str;

            // 生成ss scheme
            $ss_str = '';
            $ss_str .= $user['method'] . ':' . $user['passwd'] . '@';
            $ss_str .= $node->server . ':' . $user['port'];
            $ss_str = $this->base64url_encode($ss_str) . '#' . 'VPN';
            $ss_scheme = 'ss://' . $ss_str;

            // 生成文本配置信息
            $txt = <<<TXT
服务器：{$node->server}
端口：{$user['port']}
密码：{$user['passwd']}
加密方式：{$user['method']}
协议：{$user['protocol']}
协议参数：{$user['protocol_param']}
混淆：{$user['obfs']}
混淆参数：{$user['obfs_param']}
TXT;

            $node->txt = $txt;
            $node->ssr_scheme = $ssr_scheme;
            $node->ss_scheme = $ss_scheme;
        }

        $view['nodeList'] = $nodeList;

        return Response::view('user/nodeList', $view);
    }

    // 流量日志
    public function trafficLog(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        $user = $request->session()->get('user');

        // 30天内的流量
        $trafficList = \DB::select("SELECT date(from_unixtime(log_time)) AS dd, SUM(u) AS u, SUM(d) AS d FROM `user_traffic_log` WHERE `user_id` = {$user['id']} GROUP BY `dd`");
        foreach ($trafficList as $key => &$val) {
            $val->total = ($val->u + $val->d) / (1024 * 1024); // 以M为单位
        }

        $view['trafficList'] = $trafficList;

        return Response::view('user/trafficLog', $view);
    }

    // 邀请码
    public function invite(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        $user = $request->session()->get('user');

        // 已生成的邀请码数量
        $num = Invite::where('uid', $user['id'])->count();
        $inviteNum = Config::where('id', 3)->pluck('value');

        $view['num'] = $inviteNum[0] - $num; // 还可以生成的邀请码数量
        $view['inviteList'] = Invite::where('uid', $user['id'])->with(['generator', 'user'])->paginate(10); // 邀请码列表

        return Response::view('user/invite', $view);
    }

    // 生成邀请码
    public function makeInvite(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        $user = $request->session()->get('user');

        // 已生成的邀请码数量
        $num = Invite::where('uid', $user['id'])->count();
        $inviteNum = Config::where('id', 3)->pluck('value');
        if ($num >= $inviteNum[0]) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '生成失败：最多只能生成' . $inviteNum[0] . '个邀请码']);
        }

        $obj = new Invite();
        $obj->uid = $user['id'];
        $obj->fuid = 0;
        $obj->code = strtoupper(md5(microtime() . $this->makeRandStr(6)));
        $obj->status = 0;
        $obj->dateline = date('Y-m-d H:i:s', strtotime("+ 7days"));
        $obj->save();

        return Response::json(['status' => 'success', 'data' => '', 'message' => '生成成功']);
    }

}