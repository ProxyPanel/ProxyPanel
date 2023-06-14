<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use Validator;

class LevelController extends Controller
{
    // 添加等级
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'level' => 'required|numeric|unique:level,level',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        if (Level::create($validator->validated())) {
            return Response::json(['status' => 'success', 'message' => '提交成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '操作失败']);
    }

    // 编辑等级
    public function update(Request $request, Level $level): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'level' => 'required|numeric|unique:level,level,'.$level->id,
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        if ($level->update($validator->validated())) {
            return Response::json(['status' => 'success', 'message' => '操作成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '操作失败']);
    }

    // 删除等级
    public function destroy(Level $level): JsonResponse
    {
        // 校验该等级下是否存在关联账号
        if ($level->users()->exists()) {
            return Response::json(['status' => 'fail', 'message' => '该等级下存在关联账号，请先取消关联']);
        }

        try {
            if ($level->delete()) {
                return Response::json(['status' => 'success', 'message' => '删除成功']);
            }
        } catch (Exception $e) {
            Log::error('删除等级时报错：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '删除失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '删除失败']);
    }
}
