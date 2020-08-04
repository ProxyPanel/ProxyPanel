<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 系统配置
 * Class Config
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class Config extends Model
{
    protected $table = 'config';
    protected $primaryKey = 'id';
    public $timestamps = false;

}