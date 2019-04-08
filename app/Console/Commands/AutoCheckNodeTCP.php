<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use Illuminate\Console\Command;
use App\Components\ServerChan;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeInfo;
use App\Mail\nodeCrashWarning;
use Cache;
use Mail;
use Log;

class AutoCheckNodeTCP extends Command
{
    protected $signature = 'autoCheckNodeTCP';
    protected $description = '自动检测节点是否被TCP阻断';
    protected static $systemConfig;

    public function __construct()
    {
        parent::__construct();
        self::$systemConfig = Helpers::systemConfig();
    }

    public function handle()
    {
        $jobStartTime = microtime(true);

        if (self::$systemConfig['is_tcp_check']) {
            if (!Cache::has('tcp_check_time')) {
                $this->checkNodes();
            } elseif (Cache::get('tcp_check_time') <= time()) {
                $this->checkNodes();
            } else {
                Log::info('下次节点TCP阻断检测时间：' . date('Y-m-d H:i:s', Cache::get('tcp_check_time')));
            }
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('执行定时任务【' . $this->description . '】，耗时' . $jobUsedTime . '秒');
    }

    // 监测节点状态
    private function checkNodes()
    {
        $title = "节点异常警告";

        $nodeList = SsNode::query()->where('is_transit', 0)->where('is_nat', 0)->where('status', 1)->where('is_tcp_check', 1)->get();
        foreach ($nodeList as $node) {
            $tcpCheck = $this->tcpCheck($node->ip);
            if (false !== $tcpCheck) {
                switch ($tcpCheck) {
                    case 1:
                        $text = '服务器宕机';
                        break;
                    case 2:
                        $text = '海外不通';
                        break;
                    case 3:
                        $text = 'TCP阻断';
                        break;
                    case 0:
                    default:
                        $text = '正常';
                }

                // 异常才发通知消息
                if ($tcpCheck) {
                    if (self::$systemConfig['tcp_check_warning_times']) {
                        // 已通知次数
                        $cacheKey = 'tcp_check_warning_times_' . $node->id;
                        if (Cache::has($cacheKey)) {
                            $times = Cache::get($cacheKey);
                        } else {
                            Cache::put($cacheKey, 1, 725); // 最多设置提醒12次，12*60=720分钟缓存时效，多5分钟防止异常
                            $times = 1;
                        }

                        if ($times < self::$systemConfig['tcp_check_warning_times']) {
                            Cache::increment($cacheKey);

                            $this->notifyMaster($title, "节点**{$node->name}【{$node->ip}】**：**" . $text . "**", $node->name, $node->server);
                        } elseif ($times >= self::$systemConfig['tcp_check_warning_times']) {
                            Cache::forget($cacheKey);
                            SsNode::query()->where('id', $node->id)->update(['status' => 0]);

                            $this->notifyMaster($title, "节点**{$node->name}【{$node->ip}】**：**" . $text . "**，节点自动进入维护状态", $node->name, $node->server);
                        }
                    } else {
                        $this->notifyMaster($title, "节点**{$node->name}【{$node->ip}】**：**" . $text . "**", $node->name, $node->server);
                    }
                }

                Log::info("【TCP阻断检测】" . $node->name . ' - ' . $node->ip . ' - ' . $text);
            }
        }

        // 随机生成下次检测时间
        $nextCheckTime = time() + mt_rand(1800, 3600);
        Cache::put('tcp_check_time', $nextCheckTime, 60);
    }

    /**
     * 用ipcheck.need.sh进行TCP阻断检测
     *
     * @param string $ip 被检测的IP
     *
     * @return bool|int
     */
    private function tcpCheck($ip)
    {
        try {
            $url = 'https://ipcheck.need.sh/api_v2.php?ip=' . $ip;
            $ret = $this->curlRequest($url);
            $ret = json_decode($ret);
            if (!$ret || $ret->result != 'success') {
                Log::warning("【TCP阻断检测】检测" . $ip . "时，接口返回异常");

                return false;
            }
        } catch (\Exception $e) {
            Log::warning("【TCP阻断检测】检测" . $ip . "时，接口请求超时");

            return false;
        }

        if (!$ret->data->inside_gfw->tcp->alive && !$ret->data->outside_gfw->tcp->alive) {
            return 1; // 服务器宕机或者检测接口挂了
        } elseif ($ret->data->inside_gfw->tcp->alive && !$ret->data->outside_gfw->tcp->alive) {
            return 2; // 国外访问异常
        } elseif (!$ret->data->inside_gfw->tcp->alive && $ret->data->outside_gfw->tcp->alive) {
            return 3; // 被墙
        } else {
            return 0; // 正常
        }
    }

    /**
     * 通知管理员
     *
     * @param string $title      消息标题
     * @param string $content    消息内容
     * @param string $nodeName   节点名称
     * @param string $nodeServer 节点域名
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function notifyMaster($title, $content, $nodeName, $nodeServer)
    {
        $this->notifyMasterByEmail($title, $content, $nodeName, $nodeServer);
        ServerChan::send($title, $content);
    }

    /**
     * 发邮件通知管理员
     *
     * @param string $title      消息标题
     * @param string $content    消息内容
     * @param string $nodeName   节点名称
     * @param string $nodeServer 节点域名
     */
    private function notifyMasterByEmail($title, $content, $nodeName, $nodeServer)
    {
        if (self::$systemConfig['crash_warning_email']) {
            $logId = Helpers::addEmailLog(self::$systemConfig['crash_warning_email'], $title, $content);
            Mail::to(self::$systemConfig['crash_warning_email'])->send(new nodeCrashWarning($logId, $nodeName, $nodeServer));
        }
    }

    /**
     * 发起一个CURL请求
     *
     * @param string $url  请求地址
     * @param array  $data POST数据，留空则为GET
     *
     * @return mixed
     */
    private function curlRequest($url, $data = [])
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, [
//            'Accept: application/json', // 请求报头
//            'Content-Type: application/json', // 实体报头
//            'Content-Length: ' . strlen($data)
//        ]);

        // 如果data有数据，则用POST请求
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
