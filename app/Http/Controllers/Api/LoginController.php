<?php

namespace App\Http\Controllers\Api;

use App\Components\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserSubscribeLog;
use Illuminate\Http\Request;
use Response;
use Cache;
use Hash;
use DB;

/**
 * 登录接口
 *
 * Class LoginController
 *
 * @package App\Http\Controllers
 */
class LoginController extends Controller
{
    protected static $systemConfig;

    function __construct()
    {
        self::$systemConfig = Helpers::systemConfig();
    }

    // 登录返回订阅信息
    public function login(Request $request)
    {
        $username = trim($request->get('username'));
        $password = trim($request->get('password'));
        $cacheKey = 'request_times_' . md5(getClientIp());

        if (!$username || !$password) {
            Cache::increment($cacheKey);

            return Response::json(['status' => 'fail', 'data' => [], 'message' => '请输入用户名和密码']);
        }

        // 连续请求失败15次，则封IP一小时
        if (Cache::has($cacheKey)) {
            if (Cache::get($cacheKey) >= 15) {
                return Response::json(['status' => 'fail', 'data' => [], 'message' => '请求失败超限，禁止访问1小时']);
            }
        } else {
            Cache::put($cacheKey, 1, 60);
        }

        $user = User::query()->where('username', $username)->where('status', '>=', 0)->first();
        if (!$user) {
            Cache::increment($cacheKey);

            return Response::json(['status' => 'fail', 'data' => [], 'message' => '账号不存在或已被禁用']);
        } elseif (!Hash::check($password, $user->password)) {
            return Response::json(['status' => 'fail', 'data' => [], 'message' => '用户名或密码错误']);
        }

        DB::beginTransaction();
        try {
            // 如果未生成过订阅链接则生成一个
            $subscribe = UserSubscribe::query()->where('user_id', $user->id)->first();

            // 更新订阅链接访问次数
            $subscribe->increment('times', 1);

            // 记录每次请求
            $this->log($subscribe->id, getClientIp(), 'API访问');

            // 订阅链接
            $url = self::$systemConfig['subscribe_domain'] ? self::$systemConfig['subscribe_domain'] : self::$systemConfig['website_url'];

            // 节点列表
            $userLabelIds = UserLabel::query()->where('user_id', $user->id)->pluck('label_id');
            if (empty($userLabelIds)) {
                return Response::json(['status' => 'fail', 'message' => '', 'data' => []]);
            }

            $nodeList = DB::table('ss_node')
                ->selectRaw('ss_node.*')
                ->leftJoin('ss_node_label', 'ss_node.id', '=', 'ss_node_label.node_id')
                ->whereIn('ss_node_label.label_id', $userLabelIds)
                ->where('ss_node.status', 1)
                ->groupBy('ss_node.id')
                ->orderBy('ss_node.sort', 'desc')
                ->orderBy('ss_node.id', 'asc')
                ->get();

            $c_nodes = collect();
            foreach ($nodeList as $node) {
                $temp_node = [
                    'name'          => $node->name,
                    'server'        => $node->server,
                    'server_port'   => $user->port,
                    'method'        => $user->method,
                    'obfs'          => $user->obfs,
                    'flags'         => $url . '/assets/images/country/' . $node->country_code . '.png',
                    'obfsparam'     => '',
                    'password'      => $user->passwd,
                    'group'         => '',
                    'protocol'      => $user->protocol,
                    'protoparam'    => '',
                    'protocolparam' => ''
                ];
                $c_nodes = $c_nodes->push($temp_node);
            }

            $data = [
                'status'       => 1,
                'class'        => 0,
                'level'        => 2,
                'expire_in'    => $user->expire_time,
                'text'         => '',
                'buy_link'     => '',
                'money'        => '0.00',
                'sspannelName' => 'ssrpanel',
                'usedTraffic'  => flowAutoShow($user->u + $user->d),
                'Traffic'      => flowAutoShow($user->transfer_enable),
                'all'          => 1,
                'residue'      => '',
                'nodes'        => $c_nodes,
                'link'         => $url . '/s/' . $subscribe->code
            ];

            DB::commit();

            return Response::json(['status' => 'success', 'data' => $data, 'message' => '登录成功']);
        } catch (\Exception $e) {
            DB::rollBack();

            return Response::json(['status' => 'success', 'data' => [], 'message' => '登录失败']);
        }
    }

    // 写入订阅访问日志
    private function log($subscribeId, $ip, $headers)
    {
        $log = new UserSubscribeLog();
        $log->sid = $subscribeId;
        $log->request_ip = $ip;
        $log->request_time = date('Y-m-d H:i:s');
        $log->request_header = $headers;
        $log->save();
    }
}