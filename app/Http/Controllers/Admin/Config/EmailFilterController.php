<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\EmailFilter;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Validator;

class EmailFilterController extends Controller
{
    public function index(): View
    { // 邮箱过滤列表
        return view('admin.config.emailFilter', ['filters' => EmailFilter::select(['id', 'type', 'words'])->orderByDesc('id')->paginate()]);
    }

    public function store(Request $request): JsonResponse
    { // 添加邮箱后缀
        $validator = Validator::make($request->all(), [
            'type' => 'required|numeric|between:1,2',
            'words' => 'required|unique:email_filter',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        try {
            if (EmailFilter::create($validator->validated())) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.add')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.add'), 'attribute' => trans('admin.setting.email.tail')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.add')])]);
    }

    public function destroy(EmailFilter $filter): JsonResponse
    { // 删除邮箱后缀
        try {
            if ($filter->delete()) {
                return response()->json(['status' => 'success', 'message' => trans('common.success_item', ['attribute' => trans('common.delete')])]);
            }
        } catch (Exception $e) {
            Log::error(trans('common.error_action_item', ['action' => trans('common.delete'), 'attribute' => trans('admin.setting.email.tail')]).': '.$e->getMessage());

            return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')]).', '.$e->getMessage()]);
        }

        return response()->json(['status' => 'fail', 'message' => trans('common.failed_item', ['attribute' => trans('common.delete')])]);
    }
}
