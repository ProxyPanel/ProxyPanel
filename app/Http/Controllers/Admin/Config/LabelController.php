<?php

namespace App\Http\Controllers\Admin\Config;

use App\Helpers\ActionResponse;
use App\Http\Controllers\Controller;
use App\Models\Label;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class LabelController extends Controller
{
    use ActionResponse;

    public function store(Request $request): JsonResponse
    { // 添加标签
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:label,name',
            'sort' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        return $this->actionResponse('common.add', 'model.node.label', fn () => Label::create($validator->validated()));
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

        return $this->actionResponse('common.edit', 'model.node.label', fn () => $label->update($validator->validated()));
    }

    public function destroy(Label $label): JsonResponse
    { // 删除标签
        return $this->actionResponse('common.delete', 'model.node.label', function () use ($label) {
            // 先从所有节点中移除该标签
            $label->nodes()->detach();

            // 然后删除标签
            return $label->delete();
        });
    }
}
