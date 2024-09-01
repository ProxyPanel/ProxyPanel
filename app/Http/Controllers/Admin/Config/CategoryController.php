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
            return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.add')])]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')])]);
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
            return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.edit')])]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')])]);
    }

    // 删除等级
    public function destroy(GoodsCategory $category): JsonResponse
    {
        // 校验该等级下是否存在关联账号
        if ($category->goods()->exists()) {
            return Response::json(['status' => 'fail', 'message' => trans('common.exists_error', ['attribute' => trans('model.goods.category')])]);
        }

        try {
            if ($category->delete()) {
                return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.common.level')]).': '.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
