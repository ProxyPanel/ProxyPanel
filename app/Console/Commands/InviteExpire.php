<?php

namespace App\Console\Commands;

use App\Http\Models\Invite;
use Illuminate\Console\Command;
use Log;

class InviteExpire extends Command
{
    protected $signature = 'command:inviteExpire';
    protected $description = '邀请码过期废除';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $inviteList = Invite::where('status', 0)->where('dateline', '<=', date('Y-m-d H:i:s'))->get();
        if ($inviteList->isEmpty()) {
            foreach ($inviteList as $invite) {
                Invite::where('id', $invite->id)->update(['status' => 2]);
            }
        }

        Log::info('定时任务：' . $this->description);
    }
}
