<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\NodeHeartbeat;
use App\Services\ProxyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    public function index(): View
    { // 节点列表
        $nodeList = auth()->user()->nodes()->whereIn('is_display', [1, 3])->with(['labels', 'level_table'])->get(); // 获取当前用户可用节点
        $onlineNode = NodeHeartbeat::recently()->distinct()->pluck('node_id')->toArray();
        foreach ($nodeList as $node) {
            $node->offline = ! in_array($node->id, $onlineNode, true); // 节点在线状态
        }

        return view('user.nodeList', [
            'nodesGeo' => $nodeList->pluck('name', 'geo')->toArray(),
            'nodeList' => $nodeList,
        ]);
    }

    public function show(Request $request, Node $node, ProxyService $proxyServer): JsonResponse
    { // 节点详细
        $server = $proxyServer->getProxyConfig($node);

        return response()->json(['status' => 'success', 'data' => $proxyServer->getUserProxyConfig($server, $request->input('type') !== 'text'), 'title' => $server['type']]);
    }
}
