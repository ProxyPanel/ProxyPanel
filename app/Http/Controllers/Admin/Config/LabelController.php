<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Models\NodeLabel;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;

class LabelController extends Controller
{
    // 添加标签
    public function store(Request $request): JsonResponse
    {
        $label = new Label();
        $label->name = $request->input('name');
        $label->sort = $request->input('sort');

        if ($label->save()) {
            return Response::json(['status' => 'success', 'message' => '添加成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '添加失败']);
    }

    // 编辑标签
    public function update(Request $request, $id): JsonResponse
    {
        if (Label::whereId($id)->update(['name' => $request->input('name'), 'sort' => $request->input('sort')])) {
            return Response::json(['status' => 'success', 'message' => '编辑成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '编辑失败']);
    }

    // 删除标签
    public function destroy($id): ?JsonResponse
    {
        try {
            Label::whereId($id)->delete();

            return Response::json(['status' => 'success', 'message' => '删除成功']);
        } catch (Exception $e) {
            Log::error('删除标签失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '删除失败：'.$e->getMessage()]);
        }
    }
}
