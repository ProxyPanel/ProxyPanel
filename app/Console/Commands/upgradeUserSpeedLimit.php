<?php

namespace App\Console\Commands;

use App\Http\Models\User;
use Illuminate\Console\Command;
use Log;

class upgradeUserSpeedLimit extends Command
{
    protected $signature = 'upgradeUserSpeedLimit';
    protected $description = '升级用户限速字段，重置初始值';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('----------------------------【重置用户限速字段】开始----------------------------');

        $userList = User::query()->get();
        foreach ($userList as $user) {
            $data = [
                'speed_limit_per_con'  => 10737418240,
                'speed_limit_per_user' => 10737418240
            ];

            User::query()->where('id', $user->id)->update($data);
            Log::info('---用户[ID：' . $user->id . ' - ' . $user->username . ']的限速字段值被重置为10G---');
        }

        Log::info('----------------------------【重置用户限速字段】结束----------------------------');
    }
}
