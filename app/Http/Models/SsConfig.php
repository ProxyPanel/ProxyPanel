<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 配置信息
 * Class SsConfig
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class SsConfig extends Model
{
    protected $table = 'ss_config';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // 筛选默认
    function scopeDefault($query)
    {
        $query->where('is_default', 1);
    }

    // 筛选类型
    function scopeType($query, $type)
    {
        $query->where('type', $type);
    }
}