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
            $this->checkNodes();
        }

        $jobEndTime = microtime(true);
        $jobUsedTime = round(($jobEndTime - $jobStartTime), 4);

        Log::info('执行定时任务【' . $this->description . '】，耗时' . $jobUsedTime . '秒');
    }

    // 监测节点状态
    private function checkNodes()
    {
        $title = "节点异常警告";

        $nodeList = SsNode::query()->where('status', 1)->get();
        foreach ($nodeList as $node) {
            $tcpCheck = $this->tcpCheck($node->ip);
            if (false !== $tcpCheck && $tcpCheck > 0) {
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

                $this->notifyMaster($title, "节点**{$node->name}【{$node->ip}】**：**" . $text . "**", $node->name, $node->server);
            }

            // 10分钟内无节点负载信息且TCP检测认为不是宕机则认为是SSR(R)后端炸了
            $nodeTTL = SsNodeInfo::query()->where('node_id', $node->id)->where('log_time', '>=', strtotime("-10 minutes"))->orderBy('id', 'desc')->first();
            if ($tcpCheck !== 1 && !$nodeTTL) {
                $this->notifyMaster($title, "节点**{$node->name}【{$node->ip}】**异常：**心跳异常**", $node->name, $node->server);
            }

            Log::info("节点【" . $node->name . "】（" . $node->ip . "）TCP阻断检测完毕");

            // 天若有情天亦老，我为长者续一秒
            sleep(1);
        }
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
        $url = 'https://ipcheck.need.sh/api_v2.php?ip=' . $ip;
        $result = $this->curlRequest($url);
        $result = json_decode($result);
        if (!$result || $result->result != 'success') {
            \Log::info("【TCP阻断检测】ipcheck.need.sh的TCP阻断检测接口挂了");

            return false;
        }

        if (!$result->data->inside_gfw->alive && !$result->data->outside_gfw->alive) {
            return 1; // 服务器宕机或者检测接口挂了
        } elseif ($result->data->inside_gfw->alive && !$result->data->outside_gfw->alive) {
            return 2; // 国外访问异常
        } elseif (!$result->data->inside_gfw->alive && $result->data->outside_gfw->alive) {
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
