<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 注册时的激活验证码
 */
class VerifyCode extends Model
{
    protected $table = 'verify_code';

    protected $guarded = [];

    public function scopeRecentUnused(Builder $query): Builder
    {
        return $query->whereStatus(0)->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-'.config('tasks.close.verify').' minutes')));
    }
}
