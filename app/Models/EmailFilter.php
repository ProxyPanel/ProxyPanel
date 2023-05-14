<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 邮箱后缀过滤.
 */
class EmailFilter extends Model
{
    public $timestamps = false;

    protected $table = 'email_filter';

    protected $guarded = [];
}
