<?php

namespace App\Models;

use App\Casts\data_rate;
use App\Casts\money;
use App\Utils\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(GoodsCategory::class, 'category_id');
    }

    public function scopeType(Builder $query, int $type): Builder
    {
        return $query->whereType($type)->whereStatus(1)->orderByDesc('sort');
    }

    protected function priceTag(): Attribute
    {
        return Attribute::make(
            get: fn () => Helpers::getPriceTag($this->price),
        );
    }

    protected function renewTag(): Attribute
    {
        return Attribute::make(
            get: fn () => Helpers::getPriceTag($this->renew),
        );
    }

    protected function trafficLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => formatBytes($this->traffic, 'MiB'),
        );
    }
}
