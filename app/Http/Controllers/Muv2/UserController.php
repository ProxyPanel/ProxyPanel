<?php

namespace App\Http\Controllers\Muv2;

use App\Http\Controllers\Controller;
use App\Http\Models\SsNode;
use App\Http\Models\User;
use App\Http\Models\UserTrafficLog;
use Illuminate\Http\Request;
use Response;

class UserController extends Controller
{
    // 用户列表
    public function index()
    {
        $users = User::query()->where('enable', 1)->select(
            "id", "username", "passwd", "t", "u", "d", "transfer_enable",
            "port", "protocol", "obfs", "enable", "expire_time as expire_time_d", "method",
            "v2ray_uuid", "v2ray_level", "v2ray_alter_id")->get();

        foreach ($users as $user) {
            $user['switch'] = 1;
            $user['email'] = $user['username'];
            $user['expire_time'] = strval((new \DateTime($user['expire_time_d']))->getTimestamp()); // datetime 转timestamp
        }

        return Response::json(["data" => $users]);
    }

    // 更新流量到user表
    public function addTraffic(Request $request)
    {
        $userId = $request->route('id');
        $u = $request->get('u');
        $d = $request->get('d');
        $nodeId = $request->get('node_id');

        $node = SsNode::query()->where('id', $nodeId)->first();
        $user = User::query()->where('id', $userId)->first();

        $user->t = time();
        $user->u = $user->u + ($u * $node->traffic_rate);
        $user->d = $user->d + ($d * $node->traffic_rate);

        if (!$user->save()) {
            return Response::json(["msg" => "update failed",], 400);
        }

        // 记录流量日志
        $this->addUserTrafficLog($userId, $nodeId, $u, $d, $node->traffic_rate);

        return Response::json(['ret' => 1, "msg" => "ok",]);
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