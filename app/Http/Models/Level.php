<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 等级
 * Class Level
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class Level extends Model
{
    protected $table = 'level';
    protected $primaryKey = 'id';
    public $timestamps = false;
}