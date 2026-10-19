<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ActionResponse;
use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\NodeAuth;
use App\Services\NodeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Str;

class NodeAuthController extends Controller
{
    use ActionResponse;

    public function index(): View
    { // 节点授权列表
        $authorizations = NodeAuth::with(['node:id,name,type,server,ip,ipv6'])
            ->orderBy('node_id')
            ->paginate()
            ->appends(request('page'));

        return view('admin.node.auth', compact('authorizations'));
    }

    public function store(): JsonResponse
    { // 添加节点授权
        $nodes = Node::whereStatus(1)->doesntHave('auth')->pluck('id');

        if ($nodes->isEmpty()) {
            return response()->json(['status' => 'success', 'message' => trans('admin.node.auth.empty')]);
        }

        $authData = [];
        foreach ($nodes as $node_id) {
            $authData[] = [
                'node_id' => $node_id,
                'key' => Str::random(),
                'secret' => Str::random(8),
            ];
        }

        NodeAuth::insert($authData);

        return $this->successItem('common.generate');
    }

    public function update(NodeAuth $auth): JsonResponse
    { // 重置节点授权
        if ($auth->update(['key' => Str::random(), 'secret' => Str::random(8)])) {
            return $this->successItem('common.reset');
        }

        return $this->failItem('common.reset');
    }

    public function destroy(NodeAuth $auth): JsonResponse
    { // 删除节点授权
        if ($auth->delete()) {
            return $this->successItem('common.delete');
        }

        return $this->failItem('common.delete');
    }

    public function getDeploymentConfig(Node $node): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => (new NodeService)->getNodeDeploymentConfig($node)]);
    }
}
