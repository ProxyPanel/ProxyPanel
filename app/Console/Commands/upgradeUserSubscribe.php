<?php

namespace App\Console\Commands;

use App\Components\Helpers;
use App\Http\Models\User;
use App\Http\Models\UserSubscribe;
use Illuminate\Console\Command;
use Log;

class upgradeUserSubscribe extends Command
{
    protected $signature = 'upgradeUserSubscribe';
    protected $description = '生成用户的订阅码';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('----------------------------【生成用户订阅码】开始----------------------------');

        $userList = User::query()->get();
        foreach ($userList as $user) {
            // 如果未生成过订阅码则生成一个
            $subscribe = UserSubscribe::query()->where('user_id', $user->id)->first();
            if (!$subscribe) {
                $subscribe = new UserSubscribe();
                $subscribe->user_id = $user->id;
                $subscribe->code = Helpers::makeSubscribeCode();
                $subscribe->times = 0;
                $subscribe->save();

                Log::info('---生成用户[ID：' . $user->id . ' - ' . $user->username . ']的订阅码---');
            }
        }

        Log::info('----------------------------【生成用户订阅码】结束----------------------------');
    }
}
