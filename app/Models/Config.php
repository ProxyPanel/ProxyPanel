<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 系统配置
 */
class Config extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'config';
    protected $primaryKey = 'name';
    protected $keyType = 'string';
    protected $fillable = ['value'];
}
