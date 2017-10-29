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
        $articleLogList = ArticleLog::where('is_pull', 0)->get();
        foreach ($articleLogList as $articleLog) {
            $url = "http://apis.map.qq.com/ws/geocoder/v1/?location=" . $articleLog->lat . ',' . $articleLog->lng . '&key=XXXXX';
            $ret = file_get_contents($url);
            $result = json_decode($ret, true);
            //Log::info(var_export($result, true));

            if ($result['status']) {
                continue;
                Log::error('文章日志通过API获取坐标对应的地址信息失败.');
            }

            // 更新日志信息
            $data = [
                'nation' => $result['result']['address_component']['nation'],
                'province' => $result['result']['address_component']['province'],
                'city' => $result['result']['address_component']['city'],
                'district' => $result['result']['address_component']['district'],
                'street' => $result['result']['address_component']['street'],
                'street_number' => $result['result']['address_component']['street_number'],
                'address' => $result['result']['address'],
                'full' => $ret,
                'is_pull' => 1
            ];

            ArticleLog::where('id', $articleLog->id)->update($data);

            // 暂停一秒，防止QPS超限导致返回错误
            sleep(1);
        }
    }
}
