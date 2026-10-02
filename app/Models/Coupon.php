<?php

namespace App\Models;

use App\Casts\datestamp;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 优惠券.
 */
class Coupon extends Model
{
    use SoftDeletes;

    protected $table = 'coupon';

    protected $casts = ['limit' => 'array', 'start_time' => datestamp::class, 'end_time' => datestamp::class, 'deleted_at' => 'datetime'];

    protected $guarded = [];

    // 筛选类型
    public function scopeType(Builder $query, int $type): Builder
    {
        return $query->whereType($type);
    }

    /**
     * 原子认领这张券，与 Verify::claim() 同一约定；只有先抢到的一次返回 true.
     */
    public function claim(): bool
    {
        return static::where('id', $this->id)->where('status', 0)->update(['status' => 1]) > 0;
    }

    public function expired(): bool
    {
        $this->status = 2;

        return $this->save();
    }

    public function isExpired(): bool
    {
        return $this->end_time < time() || $this->status === 2;
    }
}
