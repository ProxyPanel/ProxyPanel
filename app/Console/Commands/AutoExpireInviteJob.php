<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Models\Invite;
use Log;

class AutoExpireInviteJob extends Command
{
    protected $signature = 'autoExpireInviteJob';
    protected $description = '邀请码过期自动置无效';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $inviteList = Invite::query()->where('status', 0)->where('dateline', '<=', date('Y-m-d H:i:s'))->get();
        if (!$inviteList->isEmpty()) {
            foreach ($inviteList as $invite) {
                Invite::query()->where('id', $invite->id)->update(['status' => 2]);
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
