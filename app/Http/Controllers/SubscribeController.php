<?php

namespace App\Http\Controllers;

use App\Http\Models\User;
use App\Http\Models\UserLabel;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserSubscribeLog;
use Illuminate\Http\Request;
use Redirect;
use DB;

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
        $subscribe = UserSubscribe::query()->with('user')->where('code', $code)->where('status', 1)->first();
        if (empty($subscribe)) {
            exit($this->noneNode());
        }

        $user = User::query()->where('id', $subscribe->user_id)->whereIn('status', [0, 1])->where('enable', 1)->first();
        if (empty($user)) {
            exit($this->noneNode());
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
        $userLabelIds = UserLabel::query()->where('user_id', $user->id)->pluck('label_id');
        if (empty($userLabelIds)) {
            exit($this->noneNode());
        }

        $nodeList = DB::table('ss_node')
            ->leftJoin('ss_node_label', 'ss_node.id', '=', 'ss_node_label.node_id')
            ->whereIn('ss_node_label.label_id', $userLabelIds)
            ->where('ss_node.status', 1)
            ->groupBy('ss_node.id')
            ->get();

        if ($nodeList->isEmpty()) {
            exit($this->noneNode());
        }

        // 控制客户端最多获取节点数
        $scheme = self::$config['subscribe_max'] > 0 ? 'MAX=' . self::$config['subscribe_max'] . "\n" : '';
        foreach ($nodeList as $key => $node) {
            if (self::$config['subscribe_max'] && $key >= self::$config['subscribe_max']) { // 控制显示的节点数
                break;
            }

            $obfs_param = $node->single ? '' : $user->obfs_param;
            $protocol_param = $node->single ? $user->port . ':' . $user->passwd : $user->protocol_param;

            // 生成ssr scheme
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
            $scheme .= 'ssr://' . $ssr_str . "\n";
        }

        exit(base64url_encode($scheme));
    }

    // 抛出无可用的节点信息，用于兼容防止客户端订阅失败
    private function noneNode()
    {
        return base64url_encode('ssr://' . base64url_encode('8.8.8.8:8888:origin:none:plain:' . base64url_encode('0000') . '/?obfsparam=&protoparam=&remarks=' . base64url_encode('无可用节点或账号被封禁') . '&group=' . base64url_encode('VPN') . '&udpport=0&uot=0') . "\n");
    }
}
