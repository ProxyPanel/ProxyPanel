<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Node;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;

class CountryController extends Controller
{

    // 添加国家/地区
    public function store(Request $request): JsonResponse
    {
        $code = $request->input('code');
        $name = $request->input('name');

        if (empty($code)) {
            return Response::json(['status' => 'fail', 'message' => '国家/地区代码不能为空']);
        }

        if (empty($name)) {
            return Response::json(['status' => 'fail', 'message' => '国家/地区名称不能为空']);
        }

        $exists = Country::find($code);
        if ($exists) {
            return Response::json(['status' => 'fail', 'message' => '该国家/地区名称已存在，请勿重复添加']);
        }

        $obj = new Country();
        $obj->code = $code;
        $obj->name = $name;

        if ($obj->save()) {
            return Response::json(['status' => 'success', 'message' => '提交成功']);
        }

        return Response::json(['status' => 'fail', 'message' => '操作失败']);
    }

    // 编辑国家/地区
    public function update(Request $request, $code): JsonResponse
    {
        $name = $request->input('name');

        if (empty($name)) {
            return Response::json(['status' => 'fail', 'message' => '国家/地区名称不能为空']);
        }


        $country = Country::find($code);
        if (!$country) {
            return Response::json(['status' => 'fail', 'message' => '国家/地区不存在']);
        }

        // 校验该国家/地区下是否存在关联节点
        if (Node::whereCountryCode($country->code)->exists()) {
            return Response::json(['status' => 'fail', 'message' => '该国家/地区下存在关联节点，请先取消关联']);
        }

        try {
            $country->name = $name;
            if ($country->save()) {
                return Response::json(['status' => 'success', 'message' => '编辑成功']);
            }
        } catch (Exception $e) {
            Log::error('编辑国家/地区时失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '编辑失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '编辑失败']);
    }

    // 删除国家/地区
    public function destroy($code): ?JsonResponse
    {
        $country = Country::find($code);
        if (!$country) {
            return Response::json(['status' => 'fail', 'message' => '国家/地区不存在']);
        }

        // 校验该国家/地区下是否存在关联节点
        if (Node::whereCountryCode($country->code)->exists()) {
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
