<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\NodeAuth;
use Exception;
use Illuminate\Http\JsonResponse;
use Log;
use Response;
use Str;

class NodeAuthController extends Controller
{
    // 节点授权列表
    public function index()
    {
        return view('admin.node.auth', ['authorizations' => NodeAuth::with('node:id,name,type,server,ip,ipv6')->has('node')->orderBy('node_id')->paginate()->appends(request('page'))]);
    }

    // 添加节点授权
    public function store(): JsonResponse
    {
        $nodes = Node::whereStatus(1)->doesntHave('auth')->orderBy('id')->get();

        if ($nodes->isEmpty()) {
            return Response::json(['status' => 'success', 'message' => trans('admin.node.auth.empty')]);
        }
        $nodes->each(function ($node) {
            $node->auth()->create(['key' => Str::random(), 'secret' => Str::random(8)]);
        });

        return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.generate')])]);
    }

    // 重置节点授权
    public function update(NodeAuth $auth): JsonResponse
    {
        if ($auth->update(['key' => Str::random(), 'secret' => Str::random(8)])) {
            return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.reset')])]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.reset')])]);
    }

    // 删除节点授权
    public function destroy(NodeAuth $auth): JsonResponse
    {
        try {
            if ($auth->delete()) {
                return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('admin.menu.node.auth')]).': '.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('admin.menu.node.auth')]).', '.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
