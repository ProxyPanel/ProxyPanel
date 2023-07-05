<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * 营销
 */
class Marketing extends Model
{
    protected $table = 'marketing';

    protected $guarded = [];

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                -1 => '失败',
                0 => '待推送',
                1 => '成功',
                default => '',
            },
        );
    }
}
