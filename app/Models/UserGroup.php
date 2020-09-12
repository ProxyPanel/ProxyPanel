<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户分组控制
 */
class UserGroup extends Model
{

    public $timestamps = false;
    protected $table = 'user_group';
    protected $casts = [
        'nodes' => 'array',
    ];

}
