<?php

namespace App\Http\Controllers;

use App\Components\Client\Text;
use App\Components\Client\URLSchemes;
use App\Models\NodeDailyDataFlow;
use App\Models\NodeHourlyDataFlow;
use App\Models\UserDailyDataFlow;
use App\Models\UserDataFlowLog;
use App\Models\UserHourlyDataFlow;
use DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    // 节点信息
    public function getUserNodeInfo(array $server, bool $is_url): ?string
    {
        $type = $is_url ? new URLSchemes() : new Text();
        switch ($server['type']) {
            case'shadowsocks':
                $data = $type->buildShadowsocks($server);
                break;
            case 'shadowsocksr':
                $data = $type->buildShadowsocksr($server);
                break;
            case 'v2ray':
                $data = $type->buildVmess($server);
                break;
            case 'trojan':
                $data = $type->buildTrojan($server);
                break;
            default:
        }

        return $data ?? null;
    }

    // 流量使用图表
    public function dataFlowChart($id, $is_node = false): array
    {
        if ($is_node) {
            $currentFlow = UserDataFlowLog::whereNodeId($id);
            $hourlyFlow = NodeHourlyDataFlow::whereNodeId($id)->whereDate('created_at', date('Y-m-d'))->selectRaw('(DATE_FORMAT(node_hourly_data_flow.created_at, "%k")) as date, total')->pluck('total', 'date');
            $dailyFlow = NodeDailyDataFlow::whereNodeId($id)->whereMonth('created_at', date('n'))->selectRaw('(DATE_FORMAT(node_daily_data_flow.created_at, "%e")) as date, total')->pluck('total', 'date');
        } else {
            $currentFlow = UserDataFlowLog::whereUserId($id);
            $hourlyFlow = UserHourlyDataFlow::userHourly($id)->whereDate('created_at', date('Y-m-d'))->selectRaw('(DATE_FORMAT(user_hourly_data_flow.created_at, "%k")) as date, total')->pluck('total', 'date');
            $dailyFlow = UserDailyDataFlow::userDaily($id)->whereMonth('created_at', date('n'))->selectRaw('(DATE_FORMAT(user_daily_data_flow.created_at, "%e")) as date, total')->pluck('total', 'date');
        }
        $currentFlow = $currentFlow->where('log_time', '>=', strtotime(date('Y-m-d H:0')))->sum(DB::raw('u + d'));

        // 节点一天内的流量
        $hourlyData = array_fill(0, date('G') + 1, 0);
        foreach ($hourlyFlow as $date => $dataFlow) {
            $hourlyData[$date] = round($dataFlow / GB, 3);
        }
        $hourlyData[date('G') + 1] = round($currentFlow / GB, 3);

        // 节点一个月内的流量
        $dailyData = array_fill(0, date('j') - 1, 0);

        foreach ($dailyFlow as $date => $dataFlow) {
            $dailyData[$date - 1] = round($dataFlow / GB, 3);
        }

        $dailyData[date('j', strtotime(now())) - 1] = round(array_sum($hourlyData) + $currentFlow / GB, 3);

        return [
            'trafficDaily'  => $dailyData,
            'trafficHourly' => $hourlyData,
            'monthDays'     => range(1, date('j')), // 本月天数
            'dayHours'      => range(0, date('G') + 1), // 本日小时
        ];
    }

    /*
        // 将Base64图片转换为本地图片并保存
        public function base64ImageSaver($base64_image_content): ?string
        {
            // 匹配出图片的格式
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
                $type = $result[2];

                $directory = date('Ymd');
                $path = '/assets/images/qrcode/'.$directory.'/';
                // 检查是否有该文件夹，如果没有就创建，并给予最高权限
                if (! file_exists(public_path($path))
                    && ! mkdir($concurrentDirectory = public_path($path), 0755, true)
                    && ! is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }

                $fileName = Str::random(18).".{$type}";
                if (file_put_contents(public_path($path.$fileName), base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                    chmod(public_path($path.$fileName), 0744);

                    return $path.$fileName;
                }
            }

            return '';
        }
     */
}
