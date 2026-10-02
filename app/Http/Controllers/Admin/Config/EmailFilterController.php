<?php

namespace App\Http\Controllers\Admin\Config;

use App\Helpers\ActionResponse;
use App\Http\Controllers\Controller;
use App\Models\EmailFilter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class EmailFilterController extends Controller
{
    use ActionResponse;

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

        return $this->actionResponse('common.add', 'admin.setting.email.tail', fn () => EmailFilter::create($validator->validated()));
    }

    public function destroy(EmailFilter $filter): JsonResponse
    { // 删除邮箱后缀
        return $this->actionResponse('common.delete', 'admin.setting.email.tail', fn () => $filter->delete());
    }
}
