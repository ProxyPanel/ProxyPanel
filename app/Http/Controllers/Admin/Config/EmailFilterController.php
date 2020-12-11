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
        $view['list'] = EmailFilter::orderByDesc('id')->paginate(15);

        return view('admin.config.emailFilter', $view);
    }

    // 添加邮箱后缀
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'words' => 'required|unique:email_filter',
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'message' => $validator->errors()->all()]);
        }

        try {
            $obj = new EmailFilter();
            $obj->type = $request->input('type');
            $obj->words = strtolower($request->input('words'));

            if ($obj->save()) {
                return Response::json(['status' => 'success', 'message' => '添加成功']);
            }
        } catch (Exception $e) {
            Log::error('添加邮箱后缀时失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '添加失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '添加失败']);
    }

    // 删除邮箱后缀
    public function destroy($id): JsonResponse
    {
        try {
            $result = EmailFilter::whereId($id)->delete();
            if ($result) {
                return Response::json(['status' => 'success', 'message' => '删除成功']);
            }
        } catch (Exception $e) {
            Log::error('删除邮箱后缀失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '删除失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '删除失败']);
    }
}
