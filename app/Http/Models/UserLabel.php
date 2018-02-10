<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户标签
 * Class UserLabel
 * @package App\Http\Models
 */
class UserLabel extends Model
{
    protected $table = 'user_label';
    protected $primaryKey = 'id';
    public $timestamps = false;
}