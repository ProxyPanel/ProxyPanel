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
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use RuntimeException;
use Str;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    // 类似Linux中的tail命令
    public function tail($file, $n, $base = 5)
    {
        $fileLines = $this->countLine($file);
        if ($fileLines < 15000) {
            return false;
        }

        $fp = fopen($file, 'rb+');
        assert($n > 0);
        $pos = $n + 1;
        $lines = [];
        while (count($lines) <= $n) {
            try {
                fseek($fp, -$pos, SEEK_END);
            } catch (Exception $e) {
                break;
            }

            $pos *= $base;
            while (! feof($fp)) {
                array_unshift($lines, fgets($fp));
            }
        }

        return array_slice($lines, 0, $n);
    }

    /**
     * 计算文件行数.
     *
     * @param $file
     *
     * @return int
     */
    public function countLine($file): int
    {
        $fp = fopen($file, 'rb');
        $i = 0;
        while (! feof($fp)) {
            //每次读取2M
            if ($data = fread($fp, 1024 * 1024 * 2)) {
                //计算读取到的行数
                $num = substr_count($data, "\n");
                $i += $num;
            }
        }

        fclose($fp);

        return $i;
    }

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

    // 节点信息
    public function getUserNodeInfo(array $server, bool $is_url): ?string
    {
        switch ($server['type']) {
            case'shadowsocks':
                $data = $is_url ? URLSchemes::buildShadowsocks($server) : Text::buildShadowsocks($server);
                break;
            case 'shadowsocksr':
                $data = $is_url ? URLSchemes::buildShadowsocksr($server) : Text::buildShadowsocksr($server);
                break;
            case 'v2ray':
                $data = $is_url ? URLSchemes::buildVmess($server) : Text::buildVmess($server);
                break;
            case 'trojan':
                $data = $is_url ? URLSchemes::buildTrojan($server) : Text::buildTrojan($server);
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
            $hourlyFlow = NodeHourlyDataFlow::whereNodeId($id);
            $dailyFlow = NodeDailyDataFlow::whereNodeId($id);
        } else {
            $currentFlow = UserDataFlowLog::whereUserId($id);
            $hourlyFlow = UserHourlyDataFlow::userHourly($id);
            $dailyFlow = UserDailyDataFlow::userDaily($id);
        }
        $currentFlow = $currentFlow->where('log_time', '>=', strtotime(date('Y-m-d H:00')))->sum(DB::raw('u + d'));
        $hourlyFlow = $hourlyFlow->whereDate('created_at', date('Y-m-d'))->pluck('total', 'created_at')->toArray();
        $dailyFlow = $dailyFlow->whereMonth('created_at', date('n'))->pluck('total', 'created_at')->toArray();

        // 节点一天内的流量
        $hourlyData = array_fill(0, date('G') + 1, 0);
        foreach ($hourlyFlow as $date => $dataFlow) {
            $hourlyData[date('G', strtotime($date))] = round($dataFlow / GB, 3);
        }
        $hourlyData[date('G') + 1] = round($currentFlow / GB, 3);

        // 节点一个月内的流量
        $dailyData = array_fill(0, date('j'), 0);
        foreach ($dailyFlow as $date => $dataFlow) {
            $dailyData[date('j', strtotime($date)) - 1] = round($dataFlow / GB, 3);
        }
        $dailyData[date('j', strtotime(now())) - 1] = round((array_sum($hourlyFlow) + $currentFlow) / GB, 3);

        return [
            'trafficDaily' => json_encode($dailyData),
            'trafficHourly' => json_encode($hourlyData),
            'monthDays' => json_encode(range(1, date('j'), 1)), // 本月天数
            'dayHours' => json_encode(range(0, date('G') + 1, 1)), // 本日小时
        ];
    }
}
