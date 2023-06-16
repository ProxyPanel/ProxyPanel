<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\SsConfig;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use Validator;

class SsConfigController extends Controller
{
    // 添加SS配置
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:ss_config,name',
            'type' => 'required|numeric|between:1,3',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        if (SsConfig::create($validator->validated())) {
            return Response::json(['status' => 'success', 'message' => '添加成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '添加失败']);
    }

    // 设置SS默认配置
    public function update(SsConfig $ss): JsonResponse
    {
        $ss->setDefault();

        return Response::json(['status' => 'success', 'message' => '操作成功']);
    }

    // 删除SS配置
    public function destroy(SsConfig $ss): JsonResponse
    {
        try {
            if ($ss->delete()) {
                return Response::json(['status' => 'success', 'message' => '删除成功']);
            }
        } catch (Exception $e) {
            Log::error('删除SS配置时失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '删除失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '删除失败']);
    }
}
