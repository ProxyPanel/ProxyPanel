<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOauth extends Model
{
    protected $table = 'user_oauth';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
