<?php

namespace App\Http\Controllers\Admin\Config;

use App\Helpers\ActionResponse;
use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class LevelController extends Controller
{
    use ActionResponse;

    public function store(Request $request): JsonResponse
    { // 添加等级
        $validator = Validator::make($request->all(), [
            'level' => 'required|numeric|unique:level,level',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        return $this->actionResponse('common.add', 'model.common.level', fn () => Level::create($validator->validated()));
    }

    public function update(Request $request, Level $level): JsonResponse
    { // 编辑等级
        $validator = Validator::make($request->all(), [
            'level' => 'required|numeric|unique:level,level,'.$level->id,
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        return $this->actionResponse('common.edit', 'model.common.level', fn () => $level->update($validator->validated()));
    }

    public function destroy(Level $level): JsonResponse
    { // 删除等级
        // 校验该等级下是否存在关联账号
        if ($level->users()->exists()) {
            return response()->json(['status' => 'fail', 'message' => trans('common.exists_error', ['attribute' => trans('model.common.level')])]);
        }

        return $this->actionResponse('common.delete', 'model.common.level', fn () => $level->delete());
    }
}
