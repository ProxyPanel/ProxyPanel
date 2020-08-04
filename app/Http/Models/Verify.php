<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 注册时的验证激活地址
 * Class Verify
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class Verify extends Model
{
    protected $table = 'verify';
    protected $primaryKey = 'id';

    // 筛选类型
    function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

}