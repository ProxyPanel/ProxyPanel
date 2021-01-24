<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 营销
 */
class Marketing extends Model
{
    protected $table = 'marketing';

    public function getStatusLabelAttribute(): string
    {
        return [
            -1 => '失败',
            0  => '待推送',
            1  => '成功',
        ][$this->attributes['status']] ?? '';
    }
}
