<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\EmailFilter;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;
use Validator;

class EmailFilterController extends Controller
{
    // 邮箱过滤列表
    public function index()
    {
        return view('admin.config.emailFilter', ['filters' => EmailFilter::orderByDesc('id')->paginate()]);
    }

    // 添加邮箱后缀
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|numeric|between:1,2',
            'words' => 'required|unique:email_filter',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        try {
            if (EmailFilter::create($validator->validated())) {
                return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.add')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.add'), 'attribute' => trans('admin.setting.email.tail')]).': '.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')]).', '.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')])]);
    }

    // 删除邮箱后缀
    public function destroy(EmailFilter $filter): JsonResponse
    {
        try {
            if ($filter->delete()) {
                return Response::json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('admin.setting.email.tail')]).': '.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
