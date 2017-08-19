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
use App\Http\Models\Verify;
use App\Mail\resetPassword;
use Illuminate\Http\Request;
use Redirect;
use Response;
use Cache;
use Mail;

class UserController extends BaseController
{
    protected static $config;

    function __construct()
    {
        self::$config = $this->systemConfig();
    }

    public function index(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        $view['articleList'] = Article::orderBy('sort', 'desc')->orderBy('id', 'desc')->paginate(5);
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
远程端口：{$user['port']}
本地端口：1080
密码：{$user['passwd']}
加密方法：{$user['method']}
协议：{$user['protocol']}
协议参数：{$user['protocol_param']}
混淆方式：{$user['obfs']}
混淆参数：{$user['obfs_param']}
路由：绕过局域网及中国大陆地址
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

        $view['num'] = self::$config['invite_num'] - $num <= 0 ? 0 : self::$config['invite_num'] - $num; // 还可以生成的邀请码数量
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
        if ($num >= self::$config['invite_num']) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '生成失败：最多只能生成' . self::$config['invite_num'] . '个邀请码']);
        }

        $obj = new Invite();
        $obj->uid = $user['id'];
        $obj->fuid = 0;
        $obj->code = strtoupper(mb_substr(md5(microtime() . $this->makeRandStr(6)), 8, 16));
        $obj->status = 0;
        $obj->dateline = date('Y-m-d H:i:s', strtotime("+7 days"));
        $obj->save();

        return Response::json(['status' => 'success', 'data' => '', 'message' => '生成成功']);
    }

    // 重设密码
    public function resetPassword(Request $request)
    {
        if ($request->method() == 'POST') {
            $username = trim($request->get('username'));

            // 是否开启重设密码
            if (!self::$config['is_reset_password']) {
                $request->session()->flash('errorMsg', '系统未开启重置密码功能，请联系管理员');

                return Redirect::back()->withInput();
            }

            // 查找账号
            $user = User::where('username', $username)->first();
            if (!$user) {
                $request->session()->flash('errorMsg', '账号不存在，请重试');

                return Redirect::back();
            }

            // 24小时内重设密码次数限制
            $resetTimes = 0;
            if (Cache::has('resetPassword_' . md5($username))) {
                $resetTimes = Cache::get('resetPassword_' . md5($username));
                if ($resetTimes >= self::$config['reset_password_times']) {
                    $request->session()->flash('errorMsg', '同一个账号24小时内只能重设密码' . self::$config['reset_password_times'] . '次，请勿频繁操作');

                    return Redirect::back();
                }
            }

            // 生成取回密码的地址
            $token = md5(self::$config['website_name'] . $username . microtime());
            $verify = new Verify();
            $verify->user_id = $user->id;
            $verify->username = $username;
            $verify->token = $token;
            $verify->status = 0;
            $verify->save();

            // 发送邮件
            $resetPasswordUrl = self::$config['website_url'] . '/reset/' . $token;
            Mail::to($user->username)->send(new resetPassword(self::$config['website_name'], $resetPasswordUrl));

            Cache::put('resetPassword_' . md5($username), $resetTimes + 1, 1440);
            $request->session()->flash('successMsg', '重置成功，请查看邮箱');

            return Redirect::back();
        } else {
            $view['is_reset_password'] = self::$config['is_reset_password'];

            return Response::view('user/resetPassword', $view);
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
            } else if (empty($password) || empty($repassword)) {
                $request->session()->flash('errorMsg', '密码不能为空');

                return Redirect::back();
            } else if (md5($password) != md5($repassword)) {
                $request->session()->flash('errorMsg', '两次输入密码不一致，请重新输入');

                return Redirect::back();
            }

            // 校验账号
            $verify = Verify::where('token', $token)->with('User')->first();
            if (empty($verify)) {
                return Redirect::to('login');
            } else if ($verify->status == 1) {
                $request->session()->flash('errorMsg', '该链接已失效');

                return Redirect::back();
            } else if (!$verify->user->enable) {
                $request->session()->flash('errorMsg', '账号已被禁用');

                return Redirect::back();
            } else if (md5($password) == $verify->user->password) {
                $request->session()->flash('errorMsg', '新旧密码一样，请重新输入');

                return Redirect::back();
            }

            // 更新密码
            $ret = User::where('id', $verify->user_id)->update(['password' => md5($password)]);
            if (!$ret) {
                $request->session()->flash('errorMsg', '重设密码失败');

                return Redirect::back();
            }

            // 更新verify
            $verify->status = 1;
            $verify->save();

            $request->session()->flash('successMsg', '新密码设置成功，请自行登录');

            return Redirect::back();
        } else {
            if (empty($token)) {
                return Redirect::to('login');
            }

            $verify = Verify::where('token', $token)->with('user')->first();
            if (empty($verify)) {
                return Redirect::to('login');
            }

            $view['verify'] = $verify;

            return Response::view('user/reset', $view);
        }
    }
}