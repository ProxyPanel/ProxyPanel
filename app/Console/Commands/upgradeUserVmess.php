<?php

namespace App\Console\Commands;

use App\Http\Models\User;
use Illuminate\Console\Command;

class upgradeUserVmess extends Command
{
    protected $signature = 'upgradeUserVmess';
    protected $description = '更新用户的Vmess';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $userList = User::query()->get();
        foreach ($userList as $user) {
            if (!isset($user->vmess_id)) {
                \Log::error("USER表缺失vmess_id字段，请先维护数据库字典");
                break;
            }

            if (!$user->vmess_id) {
                User::query()->where('id', $user->id)->update(['vmess_id' => createGuid()]);
            }
        }
    }
}
