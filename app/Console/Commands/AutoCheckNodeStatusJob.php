<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\Config;
use App\Http\Models\SsNode;
use App\Http\Models\SsNodeInfo;
use App\Http\Models\EmailLog;
use App\Mail\nodeCrashWarning;
use Mail;
use Log;

class AutoCheckNodeStatusJob extends Command
{
    protected $signature = 'command:autoCheckNodeStatusJob';
    protected $description = '自动监测节点是否宕机';

    protected static $config;

    public function __construct()
    {
        parent::__construct();

        $config = Config::query()->get();
        $data = [];
        foreach ($config as $vo) {
            $data[$vo->name] = $vo->value;
        }

        self::$config = $data;
    }

    public function handle()
    {
        $nodeList = SsNode::query()->where('status', 1)->get();
        foreach ($nodeList as $node) {
            // 10分钟内无节点信息则认为是宕机，因为每个节点的负载信息最多保存10分钟
            $node_info = SsNodeInfo::query()->where('node_id', $node->id)->where('log_time', '>=', strtotime("-10 minutes"))->orderBy('id', 'desc')->first();
            if (empty($node_info) || empty($node_info->load)) {
                $title = "节点宕机警告";
                $content = "系统监测到节点【$node->name】($node->server)可能宕机了，请及时检查。";

                // 发邮件通知管理员
                if (self::$config['is_node_crash_warning'] && self::$config['crash_warning_email']) {
                    try {
                        Mail::to(self::$config['crash_warning_email'])->send(new nodeCrashWarning(self::$config['website_name'], $node->name, $node->server));
                        $this->sendEmailLog(1, $title, $content);
                    } catch (\Exception $e) {
                        $this->sendEmailLog(1, $title, $content, 0, $e->getMessage());
                    }
                }
            }
        }

        Log::info('定时任务：' . $this->description);
    }

    /**
     * 写入邮件发送日志
     * @param int $user_id 用户ID
     * @param string $title 投递类型（投递标题）
     * @param string $content 投递内容（简要概述）
     * @param int $status 投递状态
     * @param string $error 投递失败时记录的异常信息
     */
    private function sendEmailLog($user_id, $title, $content, $status = 1, $error = '')
    {
        $emailLogObj = new EmailLog();
        $emailLogObj->user_id = $user_id;
        $emailLogObj->title = $title;
        $emailLogObj->content = $content;
        $emailLogObj->status = $status;
        $emailLogObj->error = $error;
        $emailLogObj->created_at = date('Y-m-d H:i:s');
        $emailLogObj->save();
    }
}
