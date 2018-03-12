<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        User::query()->where('enable', 1)->where('expire_time', '<=', date('Y-m-d'))->update(['enable' => 0]);

        Log::info('定时任务：' . $this->description);
    }
}
