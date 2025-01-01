<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\NodeAuth;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Log;
use Str;

class NodeAuthController extends Controller
{
    public function index(): View
    { // 节点授权列表
        return view('admin.node.auth', ['authorizations' => NodeAuth::with('node:id,name,type,server,ip,ipv6')->has('node')->orderBy('node_id')->paginate()->appends(request('page'))]);
    }

    public function store(): JsonResponse
    { // 添加节点授权
        $nodes = Node::whereStatus(1)->doesntHave('auth')->orderBy('id')->get();

        if ($nodes->isEmpty()) {
            return response()->json(['status' => 'success', 'message' => trans('admin.node.auth.empty')]);
        }
        $nodes->each(function ($node) {
            $node->auth()->create(['key' => Str::random(), 'secret' => Str::random(8)]);
        });

        return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.generate')])]);
    }

    public function update(NodeAuth $auth): JsonResponse
    { // 重置节点授权
        if ($auth->update(['key' => Str::random(), 'secret' => Str::random(8)])) {
            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.reset')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.reset')])]);
    }

    public function destroy(NodeAuth $auth): JsonResponse
    { // 删除节点授权
        try {
            if ($auth->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('admin.menu.node.auth')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('admin.menu.node.auth')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
