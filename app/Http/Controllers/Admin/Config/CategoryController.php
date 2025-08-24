<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\GoodsCategory;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Validator;

class CategoryController extends Controller
{
    public function store(Request $request): JsonResponse
    { // 添加分类
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'sort' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        $data = $validator->validated();
        // 如果没有提供sort值，则设为0
        if (! isset($data['sort'])) {
            $data['sort'] = 0;
        }

        if (GoodsCategory::create($data)) {
            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.add')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')])]);
    }

    public function update(Request $request, GoodsCategory $category): JsonResponse
    { // 编辑分类
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'sort' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        if ($category->update($validator->validated())) {
            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.edit')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')])]);
    }

    public function destroy(GoodsCategory $category): JsonResponse
    { // 删除分类
        // 校验该分类下是否存在关联商品
        if ($category->goods()->exists()) {
            return response()->json(['status' => 'fail', 'message' => trans('common.exists_error', ['attribute' => trans('model.goods.category')])]);
        }

        try {
            if ($category->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.goods.category')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
