<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\Config;
use App\Http\Models\User;
use Log;

class autoDisableExpireUserJob extends Command
{
    protected $signature = 'autoDisableExpireUserJob';
    protected $description = '自动禁用到期用户';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // 到期账号禁用
      $config = $this->systemConfig();
      if ($config['is_ban_status']) {
        User::query()->where('enable', 1)->where('expire_time', '<=', date('Y-m-d'))->update(['enable' => 0, 'status' => -1]);
      }
      else{
        User::query()->where('enable', 1)->where('expire_time', '<=', date('Y-m-d'))->update(['enable' => 0]);
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
