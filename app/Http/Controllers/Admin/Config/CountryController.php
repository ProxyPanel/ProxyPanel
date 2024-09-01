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
            return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.add')])]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')])]);
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
                return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.edit')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.edit'), 'attribute' => trans('model.node.country')]).': '.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')]).', '.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.edit')])]);
    }

    // 删除国家/地区
    public function destroy(Country $country): JsonResponse
    {
        // 校验该国家/地区下是否存在关联节点
        if ($country->nodes()->exists()) {
            return Response::json(['status' => 'fail', 'message' => trans('common.exists_error', ['attribute' => trans('model.node.country')])]);
        }

        try {
            if ($country->delete()) {
                return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('model.node.country')]).': '.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
