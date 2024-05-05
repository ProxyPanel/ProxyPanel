<?php

namespace App\Models;

use App\Observers\UserGroupObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 用户分组控制.
 */
#[ObservedBy([UserGroupObserver::class])]
class UserGroup extends Model
{
    public $timestamps = false;

    protected $table = 'user_group';

    protected $guarded = [];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function nodes(): BelongsToMany
    {
        return $this->belongsToMany(Node::class);
    }
}
