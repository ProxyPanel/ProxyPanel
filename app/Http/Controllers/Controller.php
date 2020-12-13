<?php

namespace App\Http\Controllers;

use App\Models\EmailFilter;
use App\Models\Node;
use App\Models\NodeDailyDataFlow;
use App\Models\NodeHourlyDataFlow;
use App\Models\User;
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

    /**
     * 节点信息.
     *
     * @param  int  $uid  用户ID
     * @param  int  $nodeId  节点ID
     * @param  int  $infoType  信息类型：0为链接，1为文字
     *
     * @return string
     */
    public function getUserNodeInfo(int $uid, int $nodeId, int $infoType): string
    {
        $user = User::find($uid);
        $node = Node::find($nodeId);
        $scheme = null;
        $group = sysConfig('website_name'); // 分组名称
        $host = $node->is_relay ? $node->relay_server : ($node->server ?: $node->ip);
        $data = null;
        switch ($node->type) {
            case 2:
                // 生成v2ray scheme
                if ($infoType !== 1) {
                    // 生成v2ray scheme
                    $data = $this->v2raySubUrl(
                        $node->name,
                        $host,
                        $node->v2_port,
                        $user->vmess_id,
                        $node->v2_alter_id,
                        $node->v2_net,
                        $node->v2_type,
                        $node->v2_host,
                        $node->v2_path,
                        $node->v2_tls ? 'tls' : ''
                    );
                } else {
                    $data = '服务器：'.$host.PHP_EOL.'IPv6：'.($node->ipv6 ?: '').PHP_EOL.'端口：'.$node->v2_port.PHP_EOL.'加密方式：'.$node->v2_method.PHP_EOL.'用户ID：'.$user->vmess_id.PHP_EOL.'额外ID：'.$node->v2_alter_id.PHP_EOL.'传输协议：'.$node->v2_net.PHP_EOL.'伪装类型：'.$node->v2_type.PHP_EOL.'伪装域名：'.($node->v2_host ?: '').PHP_EOL.'路径：'.($node->v2_path ?: '').PHP_EOL.'TLS：'.($node->v2_tls ? 'tls' : '').PHP_EOL;
                }
                break;
            case 3:
                if ($infoType !== 1) {
                    $data = $this->trojanSubUrl($user->passwd, $host, $node->port, $node->name);
                } else {
                    $data = '备注：'.$node->name.PHP_EOL.'服务器：'.$host.PHP_EOL.'密码：'.$user->passwd.PHP_EOL.'端口：'.$node->port.PHP_EOL;
                }
                break;
            case 1:
            case 4:
                $protocol = $node->protocol;
                $method = $node->method;
                $obfs = $node->obfs;
                if ($node->single) {
                    //单端口使用中转的端口
                    $port = $node->is_relay ? $node->relay_port : $node->port;
                    $passwd = $node->passwd;
                    $protocol_param = $user->port.':'.$user->passwd;
                } else {
                    $port = $user->port;
                    $passwd = $user->passwd;
                    $protocol_param = $node->protocol_param;
                    if ($node->type === 1) {
                        $protocol = $user->protocol;
                        $method = $user->method;
                        $obfs = $user->obfs;
                    }
                }

                if ($infoType !== 1) {
                    // 生成ss/ssr scheme
                    $data = $node->compatible ? $this->ssSubUrl($host, $port, $method, $passwd, $group) :
                        $this->ssrSubUrl($host, $port, $protocol, $method, $obfs, $passwd, $node->obfs_param, $protocol_param, $node->name, $group, $node->is_udp);
                } else {
                    // 生成文本配置信息
                    $data = '服务器：'.$host.PHP_EOL.'IPv6：'.$node->ipv6.PHP_EOL.'服务器端口：'.$port.PHP_EOL.'密码：'.$passwd.PHP_EOL.'加密：'.$method.PHP_EOL.($node->compatible ? '' : '协议：'.$protocol.PHP_EOL.'协议参数：'.$protocol_param.PHP_EOL.'混淆：'.$obfs.PHP_EOL.'混淆参数：'.$node->obfs_param.PHP_EOL);
                }
                break;
            default:
        }

        return $data;
    }

    public function v2raySubUrl($name, $host, $port, $uuid, $alter_id, $net, $type, $domain, $path, $tls): string
    {
        return 'vmess://'.base64url_encode(json_encode([
            'v' => '2', 'ps' => $name, 'add' => $host, 'port' => $port, 'id' => $uuid, 'aid' => $alter_id, 'net' => $net,
            'type' => $type, 'host' => $domain, 'path' => $path, 'tls' => $tls ? 'tls' : '',
        ], JSON_PRETTY_PRINT));
    }

    public function trojanSubUrl($password, $domain, $port, $remark): string
    {
        return 'trojan://'.urlencode($password).'@'.$domain.':'.$port.'#'.urlencode($remark);
    }

    public function ssSubUrl($host, $port, $method, $passwd, $group): string
    {
        return 'ss://'.base64url_encode($method.':'.$passwd.'@'.$host.':'.$port).'#'.$group;
    }

    public function ssrSubUrl($host, $port, $protocol, $method, $obfs, $passwd, $obfs_param, $protocol_param, $name, $group, $is_udp): string
    {
        return 'ssr://'.base64url_encode($host.':'.$port.':'.$protocol.':'.$method.':'.$obfs.':'.base64url_encode($passwd).'/?obfsparam='.base64url_encode($obfs_param).'&protoparam='.base64url_encode($protocol_param).'&remarks='.base64url_encode($name).'&group='.base64url_encode($group).'&udpport='.$is_udp.'&uot=0');
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
