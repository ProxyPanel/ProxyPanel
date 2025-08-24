<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Services\ProxyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    public function index(): View
    { // 节点列表
        $nodes = auth()->user()->nodes()->whereIn('is_display', [1, 3])->with(['labels', 'level_table:level,name', 'latestHeartbeat'])->orderByDesc('sort')->orderBy('id')->get(); // 获取当前用户可用节点

        // 直接在节点集合上标记在线状态和标签名称
        $nodes->each(function ($node) {
            $node->offline = is_null($node->latestHeartbeat);
            $node->label_names = $node->labels->sortByDesc('sort')->sortBy('id')->pluck('name');
        });

        // 提取节点地理位置信息用于地图显示
        $nodesGeo = $nodes->pluck('name', 'geo');

        return view('user.nodeList', compact('nodesGeo', 'nodes'));
    }

    public function show(Request $request, Node $node, ProxyService $proxyServer): JsonResponse
    { // 节点详细信息
        $server = $proxyServer->getProxyConfig($node);

        return response()->json(['status' => 'success', 'data' => $proxyServer->getUserProxyConfig($server, $request->input('type') !== 'text'), 'title' => $server['type']]);
    }
}
