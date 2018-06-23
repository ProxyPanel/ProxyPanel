<?php

namespace App\Http\Controllers\Api;

use App\Components\Yzy;
use App\Http\Controllers\Controller;
use App\Http\Models\Coupon;
use App\Http\Models\CouponLog;
use App\Http\Models\Goods;
use App\Http\Models\GoodsLabel;
use App\Http\Models\Order;
use App\Http\Models\Payment;
use App\Http\Models\PaymentCallback;
use App\Http\Models\ReferralLog;
use App\Http\Models\SsNode;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use Illuminate\Http\Request;
use Log;
use DB;

class V2rayController extends Controller
{
    protected static $config;

    function __construct()
    {
        self::$config = $this->systemConfig();
    }

    //V2ray 用户
    public function users(Request $request)
    {
        $node_id = $request->route('id');
        $ssr_node = SsNode::query()->where('id', $node_id)->first(); // 节点是否存在
        if ($ssr_node == null) {
            $res = [
                "ret" => 0
            ];

            return Response::json($res, 400);
        }

        // 找出该节点的标签id
        $ssr_node_label = SsNodeLabel::query()->where('node_id', $node_id)->pluck('label_id');

        //找出有这个标签的用户
        $user_with_label = UserLabel::query()->whereIn('label_id', $ssr_node_label)->pluck('user_id');

        //提取用户信息
        $userids = User::query()->whereIn('id', $user_with_label)->where('enable', 1)->where('id', '<>', self::$config['free_node_users_id'])->pluck('id')->toArray();
        $users = User::query()->where('id', '<>', self::$config['free_node_users_id'])->select(
            "id", "username", "passwd", "t", "u", "d", "transfer_enable",
            "port", "protocol", "obfs", "enable", "expire_time as expire_time_d", "method",
            "v2ray_uuid", "v2ray_level", "v2ray_alter_id")->get();

        $data = [];
        foreach ($users as $user) {
            //datetime 转timestamp
            $user['switch'] = 1;
            $user['email'] = $user['username'];
            $user['expire_time'] = strval((new \DateTime($user['expire_time_d']))->getTimestamp());
            if (in_array($user->id, $userids)) {
                $user->enable = 1;
            } else {
                $user->enable = 0;
            }

            //v2ray用户信息
            $user->v2ray_user = [
                "uuid"     => $user->v2ray_uuid,
                "email"    => sprintf("%s@sspanel.xyz", $user->v2ray_uuid),
                "alter_id" => $user->v2ray_alter_id,
                "level"    => $user->v2ray_level,
            ];

            array_push($data, $user);
        }

        if (self::$config['is_free_node']) {
            if (self::$config['free_node_id'] == $node_id) {
                $user = User::query()->whereIn('id', $user_with_label)->where('id', self::$config['free_node_users_id'])->select(
                    "id", "enable", "username", "passwd", "t", "u", "d", "transfer_enable",
                    "port", "protocol", "obfs", "enable", "expire_time as expire_time_d", "method",
                    "v2ray_uuid", "v2ray_level", "v2ray_alter_id")->first();

                //datetime 转timestamp
                $user['switch'] = 1;
                $user['email'] = $user['username'];
                $user['expire_time'] = strval((new \DateTime($user['expire_time_d']))->getTimestamp());

                //v2ray用户信息
                $user->v2ray_user = [
                    "uuid"     => $user->v2ray_uuid,
                    "email"    => sprintf("%s@sspanel.xyz", $user->v2ray_uuid),
                    "alter_id" => $user->v2ray_alter_id,
                    "level"    => $user->v2ray_level,
                ];

                array_push($data, $user);
            }
        }

        $load = '1';
        $uptime = time();

        $log = new SsNodeInfo();
        $log->node_id = $node_id;
        $log->load = $load;
        $log->uptime = $uptime;
        $log->log_time = time();
        $log->save();

        $res = [
            'msg'  => 'ok',
            'data' => $data,
        ];

        return Response::json($res);
    }

    //写在线用户日志
    public function onlineUserLog(Request $request)
    {
        $node_id = $request->route('id');
        $count = $request->get('count');
        $log = new SsNodeOnlineLog();
        $log->node_id = $node_id;
        $log->online_user = $count;
        $log->log_time = time();

        if (!$log->save()) {
            $res = [
                "ret" => 0,
                "msg" => "update failed",
            ];

            return response()->json($res);
        }

        $res = [
            "ret" => 1,
            "msg" => "ok",
        ];

        return response()->json($res);
    }

    //节点信息
    public function info(Request $request)
    {
        $node_id = $request->route('id');
        $load = $request->get('load');
        $uptime = $request->get('uptime');

        $log = new SsNodeInfo();
        $log->node_id = $node_id;
        $log->load = $load;
        $log->uptime = $uptime;
        $log->log_time = time();

        if (!$log->save()) {
            $res = [
                "ret" => 0,
                "msg" => "update failed",
            ];

            return response()->json($res);
        }

        $res = [
            "ret" => 1,
            "msg" => "ok",
        ];

        return response()->json($res);
    }

    //PostTraffic
    public function postTraffic(Request $request)
    {
        $nodeId = $request->route('id');
        $node = SsNode::query()->where('id', $nodeId)->first();
        $rate = $node->traffic_rate;
        $input = $request->getContent();
        $datas = json_decode($input, true);

        foreach ($datas as $data) {
            $user = User::query()->where('id', $data['user_id'])->first();
            if (!$user) {
                continue;
            }

            $user->t = time();
            $user->u = $user->u + ($data['u'] * $rate);
            $user->d = $user->d + ($data['d'] * $rate);
            $user->save();

            // 写usertrafficlog
            $totalTraffic = flowAutoShow(($data['u'] + $data['d']) * $rate);
            $traffic = new UserTrafficLog();
            $traffic->user_id = $data['user_id'];
            $traffic->u = $data['u'];
            $traffic->d = $data['d'];
            $traffic->node_id = $nodeId;
            $traffic->rate = $rate;
            $traffic->traffic = $totalTraffic;
            $traffic->log_time = time();
            $traffic->save();
        }

        $res = [
            'ret' => 1,
            "msg" => "ok",
        ];

        return response()->json($res);
    }

    //V2ray Users
    public function v2rayUsers(Request $request)
    {
        $node = SsNode::query()->where('id', $request->route('id'))->first();
        $users = User::query()->where('enable', 1)->where('id', '<>', self::$config['free_node_users_id'])->get();

        $v = new V2rayGenerator();
        $v->setPort($node->v2ray_port);

        foreach ($users as $user) {
            $email = sprintf("%s@sspanel.io", $user->v2ray_uuid);
            $v->addUser($user->v2ray_uuid, $user->v2ray_level, $user->v2ray_alter_id, $email);
        }

        if (self::$config['is_free_node']) {
            if ($request->route('id') == self::$config['free_node_id']) {
                $freeuser = User::query()->where('enable', 1)->where('id', self::$config['free_node_users_id'])->first();
                $email = sprintf("%s@sspanel.io", $freeuser->v2ray_uuid);
                $v->addUser($freeuser->v2ray_uuid, $freeuser->v2ray_level, $freeuser->v2ray_alter_id, $email);
            }
        }

        return Response::json($v->getArr());
    }

    //用户列表
    public function index()
    {
        $users= User::query()->where('enable',1)->select(
            "id","username","passwd","t","u","d","transfer_enable",
            "port","protocol","obfs","enable","expire_time as expire_time_d","method",
            "v2ray_uuid","v2ray_level","v2ray_alter_id")->get();

        foreach($users as $user){
            //datetime 转timestamp
            $user['switch']=1;
            $user['email']=$user['username'];
            $user['expire_time']=strval((new \DateTime($user['expire_time_d']))->getTimestamp());
        }

        $res = [
            "data" => $users
        ];

        return response()->json($res);
    }

    //更新流量到user表
    public function addTraffic(Request $request, $response, $args)
    {
        $id = $request->route('id');
        $u = $request->get('u');
        $d = $request->get('d');
        $nodeId = $request->get('node_id');

        $node = SsNode::query()->find($nodeId)->get();
        $rate = $node->traffic_rate;

        $user = User::query()->find($id)->get();

        $user->t = time();
        $user->u = $user->u + ($u * $rate);
        $user->d = $user->d + ($d * $rate);

        if (!$user->save()) {
            $res = [
                "msg" => "update failed",
            ];

            return response()->json($res,400);
        }

        // 写usertrafficlog
        $totalTraffic = flowAutoShow(($u + $d) * $rate);
        $traffic = new UserTrafficLog();
        $traffic->user_id = $id;
        $traffic->u = $u;
        $traffic->d = $d;
        $traffic->node_id = $nodeId;
        $traffic->rate = $rate;
        $traffic->traffic = $totalTraffic;
        $traffic->log_time = time();
        $traffic->save();

        $res = [
            'ret' => 1,
            "msg" => "ok",
        ];

        return response()->json($res);
    }
}