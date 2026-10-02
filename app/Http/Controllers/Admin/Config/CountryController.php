<?php

namespace App\Http\Controllers\Admin\Config;

use App\Helpers\ActionResponse;
use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class CountryController extends Controller
{
    use ActionResponse;

    public function store(Request $request): JsonResponse
    { // 添加国家/地区
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:country,code',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        return $this->actionResponse('common.add', 'model.node.country', fn () => Country::create($validator->validated()));
    }

    public function update(Request $request, Country $country): JsonResponse
    { // 编辑国家/地区
        $validator = Validator::make($request->all(), ['name' => 'required']);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        return $this->actionResponse('common.edit', 'model.node.country', fn () => $country->update($validator->validated()));
    }

    public function destroy(Country $country): JsonResponse
    { // 删除国家/地区
        // 校验该国家/地区下是否存在关联节点
        if ($country->nodes()->exists()) {
            return response()->json(['status' => 'fail', 'message' => trans('common.exists_error', ['attribute' => trans('model.node.country')])]);
        }

        return $this->actionResponse('common.delete', 'model.node.country', fn () => $country->delete());
    }
}
