<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\User;
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
            'level_name' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->first()]);
        }

        $obj = new Level();
        $obj->level = $request->input('level');
        $obj->name = $request->input('level_name');
        $obj->save();

        if ($obj->id) {
            return Response::json(['status' => 'success', 'message' => '提交成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '操作失败']);
    }

    // 编辑等级
    public function update(Request $request, $id): JsonResponse
    {
        $level = $request->input('level');

        $validator = Validator::make($request->all(), [
            'level' => 'required|numeric',
            'level_name' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->first()]);
        }
        // 校验该等级下是否存在关联账号
        $levelCheck = Level::where('id', '<>', $id)->whereLevel($level)->exists();
        if ($levelCheck) {
            return Response::json(['status' => 'fail', 'message' => '该等级已存在！']);
        }

        // 校验该等级下是否存在关联账号
        $userCount = User::whereLevel($level)->count();
        if ($userCount) {
            return Response::json(['status' => 'fail', 'message' => '该等级下存在关联账号，请先取消关联！']);
        }

        Level::whereId($id)->update(['level' => $level, 'name' => $request->input('level_name')]);

        return Response::json(['status' => 'success', 'message' => '操作成功']);
    }

    // 删除等级
    public function destroy($id): JsonResponse
    {
        $level = Level::find($id);

        // 校验该等级下是否存在关联账号
        $userCount = User::whereLevel($level->level)->count();
        if ($userCount) {
            return Response::json(['status' => 'fail', 'message' => '该等级下存在关联账号，请先取消关联']);
        }

        try {
            if (Level::whereId($id)->delete()) {
                return Response::json(['status' => 'success', 'message' => '删除成功']);
            }
        } catch (Exception $e) {
            Log::error('删除等级时报错：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '删除失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '删除失败']);
    }
}
