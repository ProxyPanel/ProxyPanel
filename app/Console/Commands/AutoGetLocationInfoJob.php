<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\ArticleLog;
use Log;

class AutoGetLocationInfoJob extends Command
{
    protected $signature = 'command:autoGetLocationInfoJob';
    protected $description = '自动获取经纬度对应的地址信息';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $articleLogList = ArticleLog::query()->where('is_pull', 0)->get();
        foreach ($articleLogList as $articleLog) {
            $url = $this->makeUrl($articleLog->lat, $articleLog->lng);
            $ret = file_get_contents($url);
            $result = json_decode($ret, true);
            Log::info(var_export($result, true));

            if ($result['status']) {
                Log::error('文章日志通过API获取坐标对应的地址信息失败.');
                continue;
            }

            // 更新日志信息
            $data = [
                'nation'        => $result['result']['address_component']['nation'],
                'province'      => $result['result']['address_component']['province'],
                'city'          => $result['result']['address_component']['city'],
                'district'      => $result['result']['address_component']['district'],
                'street'        => $result['result']['address_component']['street'],
                'street_number' => $result['result']['address_component']['street_number'],
                'address'       => $result['result']['address'],
                'full'          => $ret,
                'is_pull'       => 1
            ];

            ArticleLog::query()->where('id', $articleLog->id)->update($data);

            // 休眠0.2秒，防止QPS超限导致返回错误
            usleep(200000);
        }

        Log::info('定时任务：' . $this->description);
    }

    // 生成坐标查询URL
    private function makeUrl($lat, $lng)
    {
        $coordinate = $this->translate($lat, $lng);

        $url = "http://apis.map.qq.com/ws/geocoder/v1/?location={$coordinate['lat']},{$coordinate['lng']}&key=XXXX";

        return $url;
    }

    /**
     * 地图坐标系转换
     * 百度地图(BD09)转腾讯地图(GCJ02)坐标系
     * @param double $lat 纬度
     * @param double $lng 经度
     * @return array
     **/
    private function translate($lat, $lng)
    {
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $lng - 0.0065;
        $y = $lat - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $lng = $z * cos($theta);
        $lat = $z * sin($theta);

        return ['lat' => $lat, 'lng' => $lng];
    }
}
