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

    function scopeDefault($query)
    {
        $query->where('is_default', 1);
    }

    function scopeType($query, $type)
    {
        $query->where('type', $type);
    }
}