<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\Label;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Validator;

class LabelController extends Controller
{
    public function store(Request $request): JsonResponse
    { // 添加标签
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:label,name',
            'sort' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        try {
            if (Label::create($validator->validated())) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.add')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.add'), 'attribute' => trans('model.node.label')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')])]);
    }

    public function update(Request $request, Label $label): JsonResponse
    { // 编辑标签
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:label,name,'.$label->id,
            'sort' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        try {
            if ($label->update($validator->validated())) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.edit')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.edit'), 'attribute' => trans('model.node.label')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')])]);
    }

    public function destroy(Label $label): JsonResponse
    { // 删除标签
        try {
            // 先从所有节点中移除该标签
            $label->nodes()->detach();

            // 然后删除标签
            if ($label->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.node.label')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
