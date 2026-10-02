<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserLoginLog;
use App\Utils\IP;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

/**
 * 记录用户登录日志.
 *
 * 归属地要请求外部 IP 库（即便并发发出，也要等最慢的那一个，还可能撞上超时），
 * 放在登录请求里就是让用户跟着一起等。所以整件事挪到队列：登录接口只负责派发，
 * 日志行与「最近登录时间」由 worker 写。
 *
 * 只传用户ID 而不是模型：等 worker 跑到的时候，模型状态可能已经变了。
 */
class RecordUserLogin implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $userId, private readonly string $ip)
    {
    }

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (! $user) { // 首次登录后账号被删掉了
            return;
        }

        $ipLocation = IP::getIPInfo($this->ip);
        if (! $ipLocation) {
            Log::warning(trans('errors.get_ip').'：'.$this->ip);
        }

        UserLoginLog::create([
            'user_id' => $user->id,
            'ip' => $this->ip,
            'country' => $ipLocation['country'] ?? '',
            'province' => $ipLocation['region'] ?? '',
            'city' => $ipLocation['city'] ?? '',
            'county' => '', // 未使用的字段
            'isp' => $ipLocation['isp'] ?? '',
            'area' => $ipLocation['area'] ?? '',
        ]);

        // updateQuietly：只是记个时间戳，没必要触发 UserObserver 与 updated_at 的变更
        $user->updateQuietly(['last_login' => time()]);
    }
}
