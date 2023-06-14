<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 等级.
 */
class Level extends Model
{
    public $timestamps = false;

    protected $table = 'level';

    protected $guarded = [];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'level');
    }
}
