<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 注册时的验证激活地址
 */
class Verify extends Model
{
    protected $table = 'verify';

    protected $guarded = [];

    public const STATUS_UNUSED = 0;

    public const STATUS_USED = 1;

    public const STATUS_INVALID = 2;

    // 链接有效期（秒），激活与重设密码共用
    public const TTL = 1800;

    // 不可用行的保留期，只供排查
    public const RETAIN_DAYS = 7;

    /**
     * 库里存哈希，明文只在邮件链接里出现一次。
     */
    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function expired(): bool
    {
        // 读原始串：$this->created_at 的日期转换会去解析数据库连接
        return Carbon::parse($this->getRawOriginal('created_at'))->diffInSeconds(now()) >= self::TTL;
    }

    /**
     * 链接是否还能用：未使用且没超时。
     */
    public function usable(): bool
    {
        return (int) $this->status === self::STATUS_UNUSED && ! $this->expired();
    }

    /**
     * 原子认领令牌：并发重放只有一个请求拿到 true，且必须在改动之前调用。
     */
    public function claim(): bool
    {
        return static::where('id', $this->id)
            ->where('status', self::STATUS_UNUSED)
            ->update(['status' => self::STATUS_USED]) > 0;
    }

    /**
     * 把超时未用的记录标成已失效，与「已使用」区分开。
     */
    public function invalidate(): void
    {
        static::where('id', $this->id)
            ->where('status', self::STATUS_UNUSED)
            ->update(['status' => self::STATUS_INVALID]);
    }

    /**
     * 定时清理：超时未用的置失效，不可用且过保留期的删除.
     */
    public static function cleanup(): void
    {
        static::where('status', self::STATUS_UNUSED)
            ->where('created_at', '<=', now()->subSeconds(self::TTL))
            ->update(['status' => self::STATUS_INVALID]);

        static::where('status', '!=', self::STATUS_UNUSED)
            ->where('created_at', '<=', now()->subDays(self::RETAIN_DAYS))
            ->delete();
    }

    // 筛选类型
    public function scopeType(Builder $query, int $type): Builder
    {
        return $query->whereType($type);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
