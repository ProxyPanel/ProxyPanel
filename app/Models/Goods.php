<?php

namespace App\Models;

use App\Components\Helpers;
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

    protected $dates = ['deleted_at'];

    protected $guarded = [];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeType($query, $type)
    {
        return $query->whereType($type)->whereStatus(1)->orderByDesc('sort');
    }

    public function getPriceAttribute($value)
    {
        return $value / 100;
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value * 100;
    }

    public function getPriceTagAttribute(): string
    {
        return Helpers::getPriceTag($this->price);
    }

    public function getRenewAttribute($value)
    {
        return $value / 100;
    }

    public function setRenewAttribute($value)
    {
        $this->attributes['renew'] = $value * 100;
    }

    public function getRenewTagAttribute(): string
    {
        return Helpers::getPriceTag($this->renew);
    }

    public function getTrafficLabelAttribute()
    {
        return flowAutoShow($this->attributes['traffic'] * MB);
    }

    public function setSpeedLimitAttribute($value)
    {
        return $this->attributes['speed_limit'] = $value * Mbps;
    }

    public function getSpeedLimitAttribute($value)
    {
        return $value / Mbps;
    }
}
