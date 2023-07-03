<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kyslik\ColumnSortable\Sortable;

/**
 * 用户订阅地址
 */
class UserSubscribe extends Model
{
    use Sortable;

    public array $sortable = ['id', 'times'];

    protected $table = 'user_subscribe';

    protected $guarded = [];

    public function scopeUid(Builder $query): Builder
    {
        return $query->whereUserId(Auth::id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userSubscribeLogs(): HasMany
    {
        return $this->hasMany(UserSubscribeLog::class);
    }
}
