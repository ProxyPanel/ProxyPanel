<?php

namespace App\Console\Commands;

use App\Http\Models\User;
use Illuminate\Console\Command;
use Log;

class DisableExpireUserJob extends Command
{
    protected $signature = 'command:disableExpireUserJob';
    protected $description = '禁用到期账号';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // 到期账号禁用
        User::where('enable', 1)->where('expire_time', '<=', date('Y-m-d'))->update(['enable' => 0]);

        Log::info('定时任务：' . $this->description);
    }
}
