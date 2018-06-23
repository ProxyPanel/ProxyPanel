<?php

namespace App\Http\Controllers\Muv2;
use App\Http\Controllers\Controller;
use App\Http\Models\SsNode;
use App\Http\Models\User;
use App\Http\Models\UserTrafficLog;
use Illuminate\Http\Request;

class UserController extends Controller
{
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