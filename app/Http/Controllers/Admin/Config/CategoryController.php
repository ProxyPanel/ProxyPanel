<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\GoodsCategory;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use Validator;

class CategoryController extends Controller
{
    // 添加等级
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['name' => 'required']);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        if (GoodsCategory::create($validator->validated())) {
            return Response::json(['status' => 'success', 'message' => '提交成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '操作失败']);
    }

    // 编辑等级
    public function update(Request $request, GoodsCategory $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'sort' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }
        if ($category->update($validator->validated())) {
            return Response::json(['status' => 'success', 'message' => '操作成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '操作失败']);
    }

    // 删除等级
    public function destroy(GoodsCategory $category): JsonResponse
    {
        // 校验该等级下是否存在关联账号
        if ($category->goods()->exists()) {
            return Response::json(['status' => 'fail', 'message' => '该分类下存在关联账号，请先取消关联']);
        }

        try {
            if ($category->delete()) {
                return Response::json(['status' => 'success', 'message' => '删除成功']);
            }
        } catch (Exception $e) {
            Log::error('删除时报错：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '删除失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '删除失败']);
    }
}
