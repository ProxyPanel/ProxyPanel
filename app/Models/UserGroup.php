<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户分组控制.
 */
class UserGroup extends Model
{
    public $timestamps = false;
    protected $table = 'user_group';
    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function nodes()
    {
        return $this->belongsToMany(Node::class);
    }
}
