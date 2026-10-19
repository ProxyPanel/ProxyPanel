<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class money implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): float
    {
        return round($value / 100, 2);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        // 先按十进制语义舍到两位小数再乘 100，之后再 round 一次：直接乘会踩二进制表示的运气——
        // 9.995（奇数分订单 × 0.5 的佣金、汇率换算都是这个形状）乘 100 得 999.4999…，半分被丢掉
        return (int) round(round((float) $value, 2, PHP_ROUND_HALF_UP) * 100);
    }
}
