<?php

namespace App\Http\Controllers\Admin\Config;

use App\Helpers\ActionResponse;
use App\Http\Controllers\Controller;
use App\Models\SsConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class SsConfigController extends Controller
{
    use ActionResponse;

    public function store(Request $request): JsonResponse
    { // 添加SS配置
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:ss_config,name',
            'type' => 'required|numeric|between:1,3',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        return $this->actionResponse('common.add', 'user.node.info', fn () => SsConfig::create($validator->validated()));
    }

    public function update(SsConfig $ss): JsonResponse
    { // 设置SS默认配置
        return $this->actionResponse('common.edit', 'user.node.info', fn () => $ss->setDefault());
    }

    public function destroy(SsConfig $ss): JsonResponse
    { // 删除SS配置
        // 检查是否为默认配置
        if ($ss->is_default) {
            return response()->json(['status' => 'fail', 'message' => trans('admin.setting.common.config_default_cannot_delete')]);
        }

        return $this->actionResponse('common.delete', 'user.node.info', fn () => $ss->delete());
    }
}
