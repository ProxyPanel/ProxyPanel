<?php

namespace App\Http\Controllers;

use App\Http\Models\ArticleLog;
use Illuminate\Http\Request;
use Response;

/**
 * 定位控制器
 * Class LocateController
 * @package App\Http\Controllers
 */
class LocateController extends BaseController
{
    // 接收打开文章时上报的定位坐标信息
    public function locate(Request $request)
    {
        $aid = $request->get('aid');
        $lat = $request->get('lat');
        $lng = $request->get('lng');

        if (empty($lat) || empty($lng)) {
            return Response::json(['status' => 'fail', 'data' => '', 'message' => '经纬度不能为空']);
        }

        // 将坐标写入文章打开记录中
        $articleLog = new ArticleLog();
        $articleLog->aid = $aid;
        $articleLog->lat = $lat;
        $articleLog->lng = $lng;
        $articleLog->ip = $request->getClientIp();
        $articleLog->headers = $request->header('User-Agent');
        $articleLog->created_at = date('Y-m-d H:i:s');
        $articleLog->save();

        return Response::json(['status' => 'success', 'data' => '', 'message' => '坐标上报成功']);
    }

}
