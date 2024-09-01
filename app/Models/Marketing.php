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
                -1 => trans('common.failed'),
                0 => trans('common.status.pending_dispatch'),
                1 => trans('common.success'),
                default => trans('common.status.unknown'),
            },
        );
    }
}
