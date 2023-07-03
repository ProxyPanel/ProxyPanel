<?php

namespace App\Models;

use App\Casts\data_rate;
use App\Casts\money;
use App\Utils\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 商品
 */
class Goods extends Model
{
    use SoftDeletes;

    protected $table = 'goods';

    protected $casts = ['price' => money::class, 'renew' => money::class, 'speed_limit' => data_rate::class, 'deleted_at' => 'datetime'];

    protected $guarded = [];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeType(Builder $query, int $type): Builder
    {
        return $query->whereType($type)->whereStatus(1)->orderByDesc('sort');
    }

    public function getPriceTagAttribute(): string
    {
        return Helpers::getPriceTag($this->price);
    }

    public function getRenewTagAttribute(): string
    {
        return Helpers::getPriceTag($this->renew);
    }

    public function getTrafficLabelAttribute(): string
    {
        return formatBytes($this->attributes['traffic'] * MB);
    }
}
