<?php

namespace App\Http\Controllers;

use App\Http\Models\SsGroup;
use App\Http\Models\SsGroupNode;
use App\Http\Models\SsNode;
use App\Http\Models\User;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserSubscribeLog;
use Illuminate\Http\Request;
use Redirect;

/**
 * 订阅控制器
 * Class SubscribeController
 * @package App\Http\Controllers
 */
class SubscribeController extends Controller
{
    protected static $config;

    function __construct()
    {
        self::$config = $this->systemConfig();
    }

    // 登录页
    public function index(Request $request, $code)
    {
        if (empty($code)) {
            return Redirect::to('login');
        }

        // 校验合法性
        $subscribe = UserSubscribe::query()->where('code', $code)->where('status', 1)->with('user')->first();
        if (empty($subscribe)) {
            exit('非法请求或者被禁用，请联系管理员');
        }

        $user = User::query()->where('id', $subscribe->user_id)->whereIn('status', [0, 1])->where('enable', 1)->first();
        if (empty($user)) {
            exit('非法请求或者被禁用，请联系管理员');
        }

        // 更新访问次数
        $subscribe->increment('times', 1);

        // 记录每次请求
        $log = new UserSubscribeLog();
        $log->sid = $subscribe->id;
        $log->request_ip = $request->getClientIp();
        $log->request_time = date('Y-m-d H:i:s');
        $log->request_header = $request->headers;
        $log->save();

        // 获取这个账号可用节点
        $group_ids = SsGroup::query()->where('level', '<=', $user->level)->select(['id'])->get();
        if (empty($group_ids)) {
            exit();
        }

        $node_ids = SsGroupNode::query()->whereIn('group_id', $group_ids)->select(['node_id'])->get();
        $nodeList = SsNode::query()->where('status', 1)->whereIn('id', $node_ids)->get();
        $scheme = self::$config['subscribe_max'] > 0 ? 'MAX=' . self::$config['subscribe_max'] . "\n" : '';
        foreach ($nodeList as $node) {
            $obfs_param = $node->single ? '' : $user->obfs_param;
            $protocol_param = $node->single ? $user->port . ':' . $user->passwd : $user->protocol_param;

            // 生成ssr scheme
            $ssr_str = '';
            $ssr_str .= $node->server . ':' . ($node->single ? $node->single_port : $user->port);
            $ssr_str .= ':' . ($node->single ? $node->single_protocol : $user->protocol) . ':' . ($node->single ? $node->single_method : $user->method);
            $ssr_str .= ':' . ($node->single ? 'tls1.2_ticket_auth' : $user->obfs) . ':' . ($node->single ? $this->base64url_encode($node->single_passwd) : $this->base64url_encode($user->passwd));
            $ssr_str .= '/?obfsparam=' . ($node->single ? '' : $this->base64url_encode($obfs_param));
            $ssr_str .= '&protoparam=' . ($node->single ? $this->base64url_encode($user->port . ':' . $user->passwd) : $this->base64url_encode($protocol_param));
            $ssr_str .= '&remarks=' . $this->base64url_encode($node->name);
            $ssr_str .= '&group=' . $this->base64url_encode('VPN');
            $ssr_str .= '&udpport=0';
            $ssr_str .= '&uot=0';
            $ssr_str = $this->base64url_encode($ssr_str);
            $scheme .= 'ssr://' . $ssr_str . "\n";
        }

        exit($this->base64url_encode($scheme));
    }

}
