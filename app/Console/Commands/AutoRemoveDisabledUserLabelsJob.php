<?php

namespace App\Console\Commands;

use App\Http\Models\User;
use App\Http\Models\UserLabel;
use Illuminate\Console\Command;
use Log;

class AutoRemoveDisabledUserLabelsJob extends Command
{
    protected $signature = 'autoRemoveDisabledUserLabelsJob';
    protected $description = '自动移除被禁用用户的标签';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // 账号被禁用，则删除所有标签
        $userList = User::query()->where('enable', 0)->where('ban_time', 0)->get();
        if (!$userList->isEmpty()) {
            foreach ($userList as $user) {
                UserLabel::query()->where('user_id', $user->id)->delete();
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
