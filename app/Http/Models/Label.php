<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 标签
 * Class Label
 *
 * @package App\Http\Models
 * @mixin \Eloquent
 */
class Label extends Model
{
    protected $table = 'label';
    protected $primaryKey = 'id';
    public $timestamps = false;
}