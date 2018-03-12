<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\User;
use App\Http\Models\Config;
use Log;

class AutoReleasePortJob extends Command
{
    protected $signature = 'autoReleasePortJob';
    protected $description = '自动释放端口';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $config = $this->systemConfig();

        if ($config['auto_release_port']) {
            $userList = User::query()->where('status', '<', 0)->get();
            if (!$userList->isEmpty()) {
                foreach ($userList as $user) {
                    if ($user->port) {
                        User::query()->where('id', $user->id)->update(['port' => 0]);
                    }
                }
            }
        }

        Log::info('定时任务：' . $this->description);
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
}
