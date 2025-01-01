<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\NodeHeartbeat;
use App\Services\ProxyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class NodeController extends Controller
{
    public function index(): \Illuminate\Http\Response
    { // 节点列表
        $user = auth()->user();

        // 获取当前用户可用节点
        $nodeList = $user->nodes()->whereIn('is_display', [1, 3])->with(['labels', 'level_table'])->get();
        $onlineNode = NodeHeartbeat::recently()->distinct()->pluck('node_id')->toArray();
        foreach ($nodeList as $node) {
            // 节点在线状态
            $node->offline = ! in_array($node->id, $onlineNode, true);
        }

        return Response::view('user.nodeList', [
            'nodesGeo' => $nodeList->pluck('name', 'geo')->toArray(),
            'nodeList' => $nodeList,
        ]);
    }

    public function show(Request $request, Node $node): JsonResponse
    { // 节点详细
        $proxyServer = new ProxyService;
        $server = $proxyServer->getProxyConfig($node);

        return Response::json(['status' => 'success', 'data' => $proxyServer->getUserProxyConfig($server, $request->input('type') !== 'text'), 'title' => $server['type']]);
    }
}
