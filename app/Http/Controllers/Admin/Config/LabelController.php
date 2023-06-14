<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\Label;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use Validator;

class LabelController extends Controller
{
    // 添加标签
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:label,name',
            'sort' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        if (Label::create($validator->validated())) {
            return Response::json(['status' => 'success', 'message' => '添加成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '添加失败']);
    }

    // 编辑标签
    public function update(Request $request, Label $label): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:label,name,'.$label->id,
            'sort' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        if ($label->update($validator->validated())) {
            return Response::json(['status' => 'success', 'message' => '编辑成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '编辑失败']);
    }

    // 删除标签
    public function destroy(Label $label): ?JsonResponse
    {
        try {
            $label->delete();

            return Response::json(['status' => 'success', 'message' => '删除成功']);
        } catch (Exception $e) {
            Log::error('删除标签失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '删除失败：'.$e->getMessage()]);
        }
    }
}
