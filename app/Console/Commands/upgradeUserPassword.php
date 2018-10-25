<?php

namespace App\Console\Commands;

use App\Http\Models\User;
use Illuminate\Console\Command;
use Hash;
use Log;

class upgradeUserPassword extends Command
{
    protected $signature = 'upgradeUserPassword';
    protected $description = '用户密码升级(MD5->HASH)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('----------------------------【升级用户登录密码】开始----------------------------');
        
        // 将用户的登录密码由原有的md5升级为hash，统一升级为与用户名相同的密码
        $userList = User::query()->get();
        foreach ($userList as $user) {
            User::query()->where('id', $user->id)->update(['password' => Hash::make($user->username)]);
            Log::info('----------------------------升级用户[' . $user->username . ']的登录密码----------------------------');
        }

        Log::info('----------------------------【升级用户登录密码】结束----------------------------');
    }
}
