<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 配置信息
 * Class SsConfig
 *
 * @package App\Http\Models
 */
class SsConfig extends Model
{
    protected $table = 'ss_config';
    protected $primaryKey = 'id';
    public $timestamps = false;
}