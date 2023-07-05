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

    public function used(): bool
    {
        $this->status = 1;

        return $this->save();
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
