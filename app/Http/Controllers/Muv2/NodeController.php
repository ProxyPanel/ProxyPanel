<?php

namespace App\Http\Controllers\Muv2;

use App\Http\Controllers\Controller;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeInfo;
use App\Http\Models\SsNodeLabel;
use App\Http\Models\SsNodeOnlineLog;
use App\Http\Models\User;
use App\Http\Models\UserLabel;
use App\Http\Models\UserTrafficLog;
use App\Http\V2Ray\Generator;
use Illuminate\Http\Request;
use Response;

class NodeController extends Controller
{
    protected static $userLevel;

    // 获取节点用户列表
    public function users(Request $request)
    {
        $nodeId = $request->route('id');

        $node = SsNode::query()->where('id', $nodeId)->first(); // 节点是否存在
        if (!$node) {
            return Response::json(["ret" => 0], 400);
        }

        // 找出该节点的标签
        $nodeLabels = SsNodeLabel::query()->where('node_id', $nodeId)->pluck('label_id');

        // 找出有相同标签的用户
        $userLabels = UserLabel::query()->whereIn('label_id', $nodeLabels)->pluck('user_id');

        // 提取用户信息
        $userIds = User::query()->whereIn('status', [0, 1])->where('enable', 1)->whereIn('id', $userLabels)->where('id', '<>', $this->systemConfig['free_node_users_id'])->pluck('id')->toArray();
        $users = User::query()->where('id', '<>', $this->systemConfig['free_node_users_id'])->select(
            "id", "username", "passwd", "t", "u", "d", "transfer_enable",
            "port", "protocol", "obfs", "enable", "expire_time as expire_time_d", "method",
            "v2ray_uuid", "v2ray_level", "v2ray_alter_id")->get();

        $data = [];
        foreach ($users as $user) {
            $user['switch'] = 1;
            $user['email'] = $user['username'];
            $user['expire_time'] = strval((new \DateTime($user['expire_time_d']))->getTimestamp()); // datetime 转timestamp

            if (in_array($user->id, $userIds)) {
                $user->enable = 1;
            } else {
                $user->enable = 0;
            }

            // 用户信息
            $user->v2ray_user = [
                "uuid"     => $user->v2ray_uuid,
                "email"    => sprintf("%s@sspanel.xyz", $user->v2ray_uuid),
                "alter_id" => $user->v2ray_alter_id,
                "level"    => $user->v2ray_level,
            ];

            array_push($data, $user);
        }

        if ($this->systemConfig['is_free_node']) {
            if ($this->systemConfig['free_node_id'] == $nodeId) {
                $user = User::query()->whereIn('id', $userLabels)->where('id', $this->systemConfig['free_node_users_id'])->select(
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
        $log->node_id = $nodeId;
        $log->load = $load;
        $log->uptime = $uptime;
        $log->log_time = time();
        $log->save();

        return Response::json([
            'msg'  => 'ok',
            'data' => $data,
        ]);
    }

    // 写在线用户日志
    public function onlineUserLog(Request $request)
    {
        $nodeId = $request->route('id');
        $count = $request->get('count');
        $log = new SsNodeOnlineLog();
        $log->node_id = $nodeId;
        $log->online_user = $count;
        $log->log_time = time();

        if (!$log->save()) {
            return Response::json([
                "ret" => 0,
                "msg" => "update failed"
            ]);
        }

        return Response::json([
            "ret" => 1,
            "msg" => "ok",
        ]);
    }

    // 节点信息
    public function info(Request $request)
    {
        $nodeId = $request->route('id');
        $load = $request->get('load');
        $uptime = $request->get('uptime');

        $log = new SsNodeInfo();
        $log->node_id = $nodeId;
        $log->load = $load;
        $log->uptime = $uptime;
        $log->log_time = time();

        if (!$log->save()) {
            return Response::json([
                "ret" => 0,
                "msg" => "update failed",
            ]);
        }

        return Response::json([
            "ret" => 1,
            "msg" => "ok",
        ]);
    }

    // PostTraffic
    public function postTraffic(Request $request)
    {
        $nodeId = $request->route('id');
        $input = $request->getContent();
        $data = json_decode($input, true);

        $node = SsNode::query()->where('id', $nodeId)->first();

        foreach ($data as $vo) {
            $user = User::query()->where('id', $vo['user_id'])->first();
            if (!$user) {
                continue;
            }

            $user->t = time();
            $user->u = $user->u + ($vo['u'] * $node->traffic_rate);
            $user->d = $user->d + ($vo['d'] * $node->traffic_rate);
            $user->save();

            // 记录流量日志
            $this->addUserTrafficLog($vo['user_id'], $nodeId, $vo['u'], $vo['d'], $node->traffic_rate);
        }

        return Response::json([
            'ret' => 1,
            "msg" => "ok",
        ]);
    }

    // V2ray Users
    public function v2rayUsers(Request $request)
    {
        $nodeId = $request->route('id');

        $node = SsNode::query()->where('id', $nodeId)->first();
        $users = User::query()->whereIn('status', [0, 1])->where('enable', 1)->where('id', '<>', $this->systemConfig['free_node_users_id'])->get();

        $v2ray = new Generator();
        $v2ray->setPort($node->v2ray_port);

        foreach ($users as $user) {
            $email = sprintf("%s@sspanel.io", $user->v2ray_uuid);
            $v2ray->addUser($user->v2ray_uuid, $user->v2ray_level, $user->v2ray_alter_id, $email);
        }

        if ($this->systemConfig['is_free_node']) {
            if ($request->route('id') == $this->systemConfig['free_node_id']) {
                $freeuser = User::query()->where('enable', 1)->where('id', $this->systemConfig['free_node_users_id'])->first();
                $email = sprintf("%s@sspanel.io", $freeuser->v2ray_uuid);
                $v2ray->addUser($freeuser->v2ray_uuid, $freeuser->v2ray_level, $freeuser->v2ray_alter_id, $email);
            }
        }

        return Response::json($v2ray->getArr());
    }

    // 写入流量日志
    private function addUserTrafficLog($userId, $nodeId, $u, $d, $rate)
    {
        $totalTraffic = flowAutoShow(($u + $d) * $rate);
        $traffic = new UserTrafficLog();
        $traffic->user_id = $userId;
        $traffic->u = $u;
        $traffic->d = $d;
        $traffic->node_id = $nodeId;
        $traffic->rate = $rate;
        $traffic->traffic = $totalTraffic;
        $traffic->log_time = time();

        return $traffic->save();
    }
}