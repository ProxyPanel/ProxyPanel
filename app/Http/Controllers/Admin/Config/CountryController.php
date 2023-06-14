<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use Validator;

class CountryController extends Controller
{
    // 添加国家/地区
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:country,code',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        if (Country::create($validator->validated())) {
            return Response::json(['status' => 'success', 'message' => trans('common.generate_item', ['attribute' => trans('common.success')])]);
        }

        return Response::json(['status' => 'fail', 'message' => '生成失败']);
    }

    // 编辑国家/地区
    public function update(Request $request, Country $country): JsonResponse
    {
        $validator = Validator::make($request->all(), ['name' => 'required']);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        try {
            if ($country->update($validator->validated())) {
                return Response::json(['status' => 'success', 'message' => '编辑成功']);
            }
        } catch (Exception $e) {
            Log::error('编辑国家/地区时失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '编辑失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '编辑失败']);
    }

    // 删除国家/地区
    public function destroy(Country $country): JsonResponse
    {
        // 校验该国家/地区下是否存在关联节点
        if ($country->nodes()->exists()) {
            return Response::json(['status' => 'fail', 'message' => '该国家/地区下存在关联节点，请先取消关联']);
        }

        try {
            if ($country->delete()) {
                return Response::json(['status' => 'success', 'message' => '操作成功']);
            }
        } catch (Exception $e) {
            Log::error('删除国家/地区失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '删除失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '删除失败']);
    }
}
