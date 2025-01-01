<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\SsConfig;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Validator;

class SsConfigController extends Controller
{
    public function store(Request $request): JsonResponse
    { // 添加SS配置
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:ss_config,name',
            'type' => 'required|numeric|between:1,3',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        if (SsConfig::create($validator->validated())) {
            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.add')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')])]);
    }

    public function update(SsConfig $ss): JsonResponse
    { // 设置SS默认配置
        if ($ss->setDefault()) {
            return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.edit')])]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')])]);
    }

    public function destroy(SsConfig $ss): JsonResponse
    { // 删除SS配置
        try {
            if ($ss->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('user.node.info')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
