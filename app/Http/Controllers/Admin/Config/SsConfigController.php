<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\SsConfig;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Response;

class SsConfigController extends Controller
{
    // 添加SS配置
    public function store(Request $request): JsonResponse
    {
        $name = $request->input('name');
        $type = $request->input('type', 1); // 类型：1-加密方式（method）、2-协议（protocol）、3-混淆（obfs）
        $is_default = $request->input('is_default', 0);
        $sort = $request->input('sort', 0);

        if (empty($name)) {
            return Response::json(['status' => 'fail', 'message' => '配置名称不能为空']);
        }

        // 校验是否已存在
        $config = SsConfig::type($type)->whereName($name)->first();
        if ($config) {
            return Response::json(['status' => 'fail', 'message' => '配置已经存在，请勿重复添加']);
        }

        $ssConfig = new SsConfig();
        $ssConfig->name = $name;
        $ssConfig->type = $type;
        $ssConfig->is_default = $is_default;
        $ssConfig->sort = $sort;
        $ssConfig->save();

        return Response::json(['status' => 'success', 'message' => '添加成功']);
    }

    // 设置SS默认配置
    public function update($id): JsonResponse
    {
        if (empty($id)) {
            return Response::json(['status' => 'fail', 'message' => '非法请求']);
        }

        $config = SsConfig::find($id);
        if (!$config) {
            return Response::json(['status' => 'fail', 'message' => '配置不存在']);
        }

        // 去除该配置所属类型的默认值
        SsConfig::default()->type($config->type)->update(['is_default' => 0]);

        // 将该ID对应记录值置为默认值
        SsConfig::whereId($id)->update(['is_default' => 1]);

        return Response::json(['status' => 'success', 'message' => '操作成功']);
    }

    // 删除SS配置
    public function destroy($id): JsonResponse
    {
        try {
            if (SsConfig::whereId($id)->delete()) {
                return Response::json(['status' => 'success', 'message' => '删除成功']);
            }
        } catch (Exception $e) {
            Log::error('删除SS配置时失败：'.$e->getMessage());

            return Response::json(['status' => 'fail', 'message' => '删除失败：'.$e->getMessage()]);
        }

        return Response::json(['status' => 'fail', 'message' => '删除失败']);
    }
}
