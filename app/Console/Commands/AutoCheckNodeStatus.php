<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Components\ServerChan;
use App\Http\Models\Config;
use App\Http\Models\EmailLog;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeInfo;
use App\Mail\nodeCrashWarning;
use Cache;
use Mail;
use Log;

class AutoCheckNodeStatus extends Command
{
    protected $signature = 'autoCheckNodeStatus';
    protected $description = '自动检测节点状态';
    protected static $config;

    public function __construct()
    {
        parent::__construct();
        self::$config = $this->systemConfig();
    }

    public function handle()
    {
        $jobStartTime = microtime(true);

        // 监测节点状态
        if (self::$config['is_tcp_check']) {
            $this->checkNodeStatus();
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('执行定时任务【' . $this->description . '】，耗时' . $jobUsedTime . '秒');
    }

    // 监测节点状态
    private function checkNodeStatus()
    {
        $title = "节点异常警告";

        $nodeList = SsNode::query()->where('status', 1)->get();
        foreach ($nodeList as $node) {
            // TCP检测
            $tcpCheck = $this->tcpCheck($node->ip, $node->ssh_port);
            if (false !== $tcpCheck && $tcpCheck) {
                if ($tcpCheck === 1) {
                    // 可能是宕机时再检测一次，check-host有时候会抽风
                    $tcpCheckByNeed = $this->tcpCheckByNeed($node->ip, 'us');
                    if ($tcpCheckByNeed !== false && $tcpCheckByNeed === 0) {
                        $this->notifyMaster($title, "节点**{$node->name}【{$node->ip}】**异常：**服务器宕机**", $node->name, $node->server);
                    }
                } else if ($tcpCheck === 2) {
                    $this->notifyMaster($title, "节点**{$node->name}【{$node->ip}】**异常：**海外不通**", $node->name, $node->server);
                } else if ($tcpCheck === 3) {
                    // 可能是阻断时再检测一次，因为我捐给check-host的服务器性能不太好，有时候check-host连不上，尤其是晚高峰
                    $tcpCheckByNeed = $this->tcpCheckByNeed($node->ip);
                    if ($tcpCheckByNeed !== false && $tcpCheckByNeed === 0) {
                        $this->notifyMaster($title, "节点**{$node->name}【{$node->ip}】**异常：**TCP阻断**", $node->name, $node->server);
                    }
                }
            }

            // 10分钟内无节点负载信息且TCP检测认为不是宕机则认为是SSR(R)后端炸了
            $node_info = SsNodeInfo::query()->where('node_id', $node->id)->where('log_time', '>=', strtotime("-10 minutes"))->orderBy('id', 'desc')->first();
            if ($tcpCheck !== 1 && (empty($node_info) || empty($node_info->load))) {
                $this->notifyMaster($title, "节点**{$node->name}【{$node->ip}】**异常：**心跳异常**", $node->name, $node->server);
            }

            Log::info("节点【" . $node->name . "】（" . $node->ip . "）TCP阻断检测完毕");

            // 天若有情天亦老，我为长者续一秒
            sleep(1);
        }
    }

    // 获取check-host的节点列表
    private function getServers()
    {
        $cacheKey = 'check_host_servers';
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $servers = $this->curlRequest("https://check-host.net/servers");
        $servers = json_decode($servers, JSON_OBJECT_AS_ARRAY);
        if (!$servers) {
            // 删除这个缓存，防止异常
            Cache::forget($cacheKey);

            return [];
        }

        // 每7天更新一次check-host的节点列表
        Cache::put($cacheKey, $servers['servers'], 10080);

        return $servers;
    }

    // 随机获取一个check-host的海外检测节点
    private function getOverseasNode()
    {
        $servers = $this->getServers();
        if (!$servers) {
            return 'us1.node.check-host.net'; // 没有数据时返回us1节点，防止异常
        }

        $offset = array_rand($servers);
        $server = $servers[$offset];
        if ($server == 'cn1.node.check-host.net') { // 排除cn1节点
            return $this->getOverseasNode();
        }

        return $server;
    }

    // TCP检测
    private function tcpCheck($ip, $port)
    {
        try {
            $overseasNode = $this->getOverseasNode();
            $result = $this->curlRequest("https://check-host.net/check-tcp?host={$ip}:{$port}&node=cn1.node.check-host.net&node=" . $overseasNode);
            $result = json_decode($result, JSON_OBJECT_AS_ARRAY);
            if ($result['ok'] != 1) {
                throw new \Exception("节点探测接口请求失败");
            }

            // 获得检测结果
            $result = $this->curlRequest("https://check-host.net/check-result/" . $result['request_id']);
            Log::info($result);
            $result = json_decode($result, JSON_OBJECT_AS_ARRAY);
            if (!$result['cn1.node.check-host.net'] && !$result[$overseasNode]) {
                return 1; // 中美都不通，服务器宕机
            } else if ($result['cn1.node.check-host.net'] && !$result[$overseasNode]) {
                return 2; // 中通美不通，无法出国，可能是安全组策略限制（例如：阿里云、腾讯云）
            } else if (!$result['cn1.node.check-host.net'] && $result[$overseasNode]) {
                return 3; // 美通中不通，说明被墙进行TCP阻断
            } else {
                return 0; // 正常
            }
        } catch (\Exception $e) {
            Log::error('节点监测请求失败：' . $e);

            return false;
        }
    }

    /**
     * 用ipcheck.need.sh进行TCP阻断检测
     *
     * @param string $ip       被检测的IP地址
     * @param string $location 节点：cn-大陆节点、us-美国节点
     *
     * @return bool|int
     */
    private function tcpCheckByNeed($ip, $location = 'cn')
    {
        $result = $this->curlRequest("https://ipcheck.need.sh/api.php?location={$location}&ip={$ip}&type=tcp");
        $result = json_decode($result, JSON_OBJECT_AS_ARRAY);
        if (!$result || $result['result'] != 'success') {
            \Log::info("注意：ipcheck.need.sh 的TCP阻断检测接口挂了");

            return false;
        }

        return $result['alive'] == true ? 1 : 0;
    }

    /**
     * 通知管理员
     *
     * @param string $title      消息标题
     * @param string $content    消息内容
     * @param string $nodeName   节点名称
     * @param string $nodeServer 节点域名
     */
    private function notifyMaster($title, $content, $nodeName, $nodeServer)
    {
        $this->notifyMasterByEmail($title, $content, $nodeName, $nodeServer);
        $this->notifyMasterByServerchan($title, $content);
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
        if (self::$config['is_node_crash_warning'] && self::$config['crash_warning_email']) {
            try {
                Mail::to(self::$config['crash_warning_email'])->send(new nodeCrashWarning(self::$config['website_name'], $nodeName, $nodeServer));
                $this->addEmailLog(1, $title, $content);
            } catch (\Exception $e) {
                $this->addEmailLog(1, $title, $content, 0, $e->getMessage());
            }
        }
    }

    /**
     * 通过ServerChan发微信消息提醒管理员
     *
     * @param string $title   消息标题
     * @param string $content 消息内容
     */
    private function notifyMasterByServerchan($title, $content)
    {
        if (self::$config['is_server_chan'] && self::$config['server_chan_key']) {
            $serverChan = new ServerChan();
            $serverChan->send($title, $content);
        }
    }

    /**
     * 添加邮件发送日志
     *
     * @param int    $userId  接收者用户ID
     * @param string $title   标题
     * @param string $content 内容
     * @param int    $status  投递状态
     * @param string $error   投递失败时记录的异常信息
     */
    private function addEmailLog($userId, $title, $content, $status = 1, $error = '')
    {
        $emailLogObj = new EmailLog();
        $emailLogObj->user_id = $userId;
        $emailLogObj->title = $title;
        $emailLogObj->content = $content;
        $emailLogObj->status = $status;
        $emailLogObj->error = $error;
        $emailLogObj->created_at = date('Y-m-d H:i:s');
        $emailLogObj->save();
    }

    // 系统配置
    private function systemConfig()
    {
        $config = Config::query()->get();
        $data = [];
        foreach ($config as $vo) {
            $data[$vo->name] = $vo->value;
        }

        return $data;
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json', // 请求报头
            'Content-Type: application/json', // 实体报头
            'Content-Length: ' . strlen($data)
        ]);

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
