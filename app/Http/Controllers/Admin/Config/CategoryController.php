<?php

namespace App\Http\Controllers\Admin\Config;

use App\Helpers\ActionResponse;
use App\Http\Controllers\Controller;
use App\Models\GoodsCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class CategoryController extends Controller
{
    use ActionResponse;

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

        return $this->actionResponse('common.add', 'model.goods.category', fn () => GoodsCategory::create($data));
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

        return $this->actionResponse('common.edit', 'model.goods.category', fn () => $category->update($validator->validated()));
    }

    public function destroy(GoodsCategory $category): JsonResponse
    { // 删除分类
        // 校验该分类下是否存在关联商品
        if ($category->goods()->exists()) {
            return response()->json(['status' => 'fail', 'message' => trans('common.exists_error', ['attribute' => trans('model.goods.category')])]);
        }

        return $this->actionResponse('common.delete', 'model.goods.category', fn () => $category->delete());
    }
}
