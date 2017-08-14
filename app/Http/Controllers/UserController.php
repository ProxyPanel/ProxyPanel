<?php

namespace App\Http\Controllers;

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
        return Response::view('user/index');
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
            return Response::view('user/profile');
        }
    }

    // 节点列表
    public function nodeList(Request $request)
    {
        if (!$request->session()->has('user')) {
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

        return Response::view('user/nodeList', $view);
    }

    // 流量日志
    public function trafficLog(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        $user = $request->session()->get('user');

        $trafficLogList = UserTrafficLog::with(['User', 'SsNode'])->where('user_id', $user['id'])->orderBy('id', 'desc')->paginate(20);
        foreach ($trafficLogList as &$trafficLog) {
            $trafficLog->u = $this->flowAutoShow($trafficLog->u);
            $trafficLog->d = $this->flowAutoShow($trafficLog->d);
            $trafficLog->log_time = date('Y-m-d H:i:s', $trafficLog->log_time);
        }

        $view['trafficLogList'] = $trafficLogList;

        return Response::view('user/trafficLog', $view);
    }

    // 邀请码
    public function invite(Request $request)
    {
        if (!$request->session()->has('user')) {
            return Redirect::to('login');
        }

        $user = $request->session()->get('user');



        $view = [];

        return Response::view('user/invite', $view);
    }

}