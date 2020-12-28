<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NodeAuth;
use Exception;
use Illuminate\Http\Request;

class NodeAuthController extends Controller
{
    // 节点授权列表
    public function index()
    {
        return view('admin.node.auth', ['authorizations' => NodeAuth::orderBy('node_id')->paginate()->appends(request('page'))]);
    }

    // 添加节点授权
    public function store(Request $request)
    {
        $nodeArray = Node::whereStatus(1)->orderBy('id')->pluck('id')->toArray();
        $authArray = NodeAuth::orderBy('node_id')->pluck('node_id')->toArray();

        $arrayDifferent = array_diff($nodeArray, $authArray);

        if (empty($arrayDifferent)) {
            return Response::json(['status' => 'success', 'message' => '没有需要生成授权的节点']);
        }

        foreach ($arrayDifferent as $nodeId) {
            $obj = new NodeAuth();
            $obj->node_id = $nodeId;
            $obj->key = Str::random();
            $obj->secret = Str::random(8);
            $obj->save();
        }

        return Response::json(['status' => 'success', 'message' => '生成成功']);
    }

    // 重置节点授权
    public function update(NodeAuth $auth)
    {
        if ($auth->update(['key' => Str::random(), 'secret' => Str::random(8)])) {
            return Response::json(['status' => 'success', 'message' => '操作成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '操作失败']);
    }

    // 删除节点授权
    public function destroy(NodeAuth $auth)
    {
        try {
            $auth->delete();
        } catch (Exception $e) {
            return Response::json(['status' => 'fail', 'message' => '错误：'.var_export($e, true)]);
        }

        return Response::json(['status' => 'success', 'message' => '操作成功']);
    }
}
